<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';
require_once __DIR__ . '/../config/db_nosql.php';

$data = json_decode(file_get_contents("php://input"), true);
$id_reservation = isset($data['id_reservation']) ? (int)$data['id_reservation'] : null;
$id_passager = isset($data['id_utilisateur']) ? (int)$data['id_utilisateur'] : null;
$action = isset($data['action']) ? trim(strtolower($data['action'])) : null; // 'valider' ou 'litige'

if (!$id_reservation || !$id_passager || !$action) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Données incomplètes (id_reservation, id_utilisateur et action requis)."]);
    exit;
}

if ($action !== 'valider' && $action !== 'litige') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Action non valide ('valider' ou 'litige' attendu)."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Charger la réservation et verrouiller pour mise à jour
    $stmtRes = $pdo->prepare("
        SELECT r.*, c.prix_personne, c.id_chauffeur, c.lieu_depart, c.lieu_arrivee, c.date_depart, c.heure_depart, c.date_arrivee, c.heure_arrivee,
               p.pseudo AS passager_pseudo, p.email AS passager_email,
               d.pseudo AS chauffeur_pseudo, d.email AS chauffeur_email
        FROM reservations r
        JOIN covoiturages c ON r.id_covoiturage = c.id_covoiturage
        JOIN utilisateurs p ON r.id_utilisateur = p.id_utilisateur
        JOIN utilisateurs d ON c.id_chauffeur = d.id_utilisateur
        WHERE r.id_reservation = ? AND r.id_utilisateur = ?
        FOR UPDATE
    ");
    $stmtRes->execute([$id_reservation, $id_passager]);
    $res = $stmtRes->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Réservation introuvable pour cet utilisateur."]);
        exit;
    }

    if ($res['statut'] !== 'confirmé') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Cette réservation a déjà été traitée (statut : " . $res['statut'] . ")."]);
        exit;
    }

    $id_chauffeur = (int)$res['id_chauffeur'];
    $prix_total = (float)$res['prix_personne'] * (int)($res['nb_places'] ?? 1);
    $credit_chauffeur = $prix_total - 2.0; // 2 crédits pris par la plateforme

    if ($action === 'valider') {
        // --- CAS 1 : Tout s'est bien passé ---
        // A. Mettre à jour le statut de la réservation en 'termine'
        $updRes = $pdo->prepare("UPDATE reservations SET statut = 'termine' WHERE id_reservation = ?");
        $updRes->execute([$id_reservation]);

        // B. Transférer les crédits au chauffeur (prix - 2)
        $updDriver = $pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id_utilisateur = ?");
        $updDriver->execute([$credit_chauffeur, $id_chauffeur]);

        // C. Si l'utilisateur a soumis un avis/note, on l'insère dans MongoDB en statut 'en attente'
        if (!empty($data['note'])) {
            if (isset($db_nosql['manager']) && $db_nosql['manager'] !== null) {
                $bulk = new MongoDB\Driver\BulkWrite;
                $bulk->insert([
                    'id_chauffeur' => $id_chauffeur,
                    'id_passager'  => $id_passager,
                    'pseudo'       => $res['passager_pseudo'] ?? 'Anonyme',
                    'commentaire'  => htmlspecialchars($data['commentaire'] ?? ''),
                    'note'         => (int)$data['note'],
                    'statut'       => 'en attente',
                    'date'         => date('Y-m-d H:i:s')
                ]);
                $db_nosql['manager']->executeBulkWrite($db_nosql['dbname'] . ".avis", $bulk);
            }
        }

        $pdo->commit();
        echo json_encode([
            "status" => "success",
            "message" => "Voyage validé avec succès. " . $credit_chauffeur . " crédits ont été transférés au chauffeur (2 crédits de commission plateforme conservés)."
        ], JSON_UNESCAPED_UNICODE);

    } else {
        // --- CAS 2 : Litige / Problème signalé ---
        // A. Mettre à jour le statut en 'litige'
        $updRes = $pdo->prepare("UPDATE reservations SET statut = 'litige' WHERE id_reservation = ?");
        $updRes->execute([$id_reservation]);

        // B. Enregistrer l'incident dans MongoDB (NoSQL)
        if (isset($db_nosql['manager']) && $db_nosql['manager'] !== null) {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert([
                'id_covoiturage'  => (int)$res['id_covoiturage'],
                'id_reservation' => (int)$res['id_reservation'],
                'type_incident'  => htmlspecialchars($data['type_incident'] ?? 'Problème de trajet'),
                'description'    => htmlspecialchars($data['description'] ?? 'Le passager a signalé que le trajet s\'est mal passé.'),
                'statut'         => 'en_cours',
                'date'           => date('Y-m-d H:i:s'),
                'passager_id'    => $id_passager,
                'passager_pseudo'=> $res['passager_pseudo'],
                'passager_email' => $res['passager_email'],
                'chauffeur_id'   => $id_chauffeur,
                'chauffeur_pseudo'=> $res['chauffeur_pseudo'],
                'chauffeur_email'=> $res['chauffeur_email'],
                'lieu_depart'    => $res['lieu_depart'],
                'lieu_arrivee'   => $res['lieu_arrivee'],
                'date_depart'    => $res['date_depart'],
                'heure_depart'   => $res['heure_depart'],
                'date_arrivee'   => $res['date_arrivee'] ?? '',
                'heure_arrivee'  => $res['heure_arrivee'] ?? ''
            ]);
            $db_nosql['manager']->executeBulkWrite($db_nosql['dbname'] . ".incidents", $bulk);
        }

        $pdo->commit();
        echo json_encode([
            "status" => "success",
            "message" => "Litige signalé. Les crédits restent gelés jusqu'à résolution par un employé d'EcoRide."
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur technique : " . $e->getMessage()]);
}
?>
