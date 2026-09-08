<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée. Requête POST attendue."]);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['id_covoiturage']) || empty($data['statut'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Les champs id_covoiturage et statut sont obligatoires."]);
    exit;
}

$id_covoiturage = (int) $data['id_covoiturage'];
$statut = trim(strtolower($data['statut']));
$id_chauffeur = isset($data['id_utilisateur']) ? (int) $data['id_utilisateur'] : null;

// Statuts valides pour le cycle de vie du trajet (US 11)
$allowed_statuts = ['en_cours', 'termine', 'annule'];
if (!in_array($statut, $allowed_statuts, true)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Statut non valide. Valeurs autorisées : 'en_cours', 'termine', 'annule'."]);
    exit;
}

try {
    // 1. Vérification de l'existence du trajet et du propriétaire
    $checkSql = "SELECT id_covoiturage, id_chauffeur, lieu_depart, lieu_arrivee, date_depart FROM covoiturages WHERE id_covoiturage = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$id_covoiturage]);
    $trajet = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$trajet) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Covoiturage introuvable."]);
        exit;
    }

    if ($id_chauffeur && (int) $trajet['id_chauffeur'] !== $id_chauffeur) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Action non autorisée : vous n'êtes pas le chauffeur de ce trajet."]);
        exit;
    }

    // 2. Mise à jour du statut du trajet
    $stmt = $pdo->prepare("UPDATE covoiturages SET statut = ? WHERE id_covoiturage = ?");
    $stmt->execute([$statut, $id_covoiturage]);

    // 3. Règle métier US 11 : Si le trajet est marqué 'termine' (Arrivée à destination), notifier les passagers
    if ($statut === 'termine') {
        $stmtPassagers = $pdo->prepare("
            SELECT u.email, u.pseudo 
            FROM reservations r
            JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
            WHERE r.id_covoiturage = ? AND r.statut = 'confirmé'
        ");
        $stmtPassagers->execute([$id_covoiturage]);
        $passagers = $stmtPassagers->fetchAll(PDO::FETCH_ASSOC);

        $sujetMail = "Votre trajet EcoRide est terminé - Confirmez votre voyage";
        foreach ($passagers as $p) {
            $messageMail = "Bonjour " . htmlspecialchars($p['pseudo']) . ",\n\n"
                . "Votre covoiturage de " . $trajet['lieu_depart'] . " à " . $trajet['lieu_arrivee'] . " est arrivé à destination.\n"
                . "Veuillez vous connecter sur votre espace EcoRide pour confirmer que tout s'est bien passé et laisser un avis sur votre chauffeur.\n\n"
                . "L'équipe EcoRide";

            @mail(
                $p['email'],
                $sujetMail,
                $messageMail,
                "From: no-reply@ecoride.fr\r\nContent-Type: text/plain; charset=utf-8"
            );
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Statut du trajet mis à jour avec succès en '$statut'.",
        "statut" => $statut
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne lors de la mise à jour du statut."]);
}