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
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée."]);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['id_covoiturage'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "L'identifiant du covoiturage (id_covoiturage) est requis."]);
    exit;
}

$id_covoiturage = (int) $data['id_covoiturage'];
$id_utilisateur = isset($data['id_utilisateur']) ? (int) $data['id_utilisateur'] : null;

try {
    $pdo->beginTransaction();

    if ($id_utilisateur) {
        // --- CAS 1 : Annulation par un passager (US 10) ---
        $checkStmt = $pdo->prepare("
            SELECT r.id_reservation, r.nb_places, c.prix_personne
            FROM reservations r
            JOIN covoiturages c ON r.id_covoiturage = c.id_covoiturage
            WHERE r.id_covoiturage = ? AND r.id_utilisateur = ? AND r.statut = 'confirmé'
            FOR UPDATE
        ");
        $checkStmt->execute([$id_covoiturage, $id_utilisateur]);
        $reservation = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Aucune réservation active trouvée pour ce covoiturage."]);
            exit;
        }

        $nb_places = (int) ($reservation['nb_places'] ?? 1);
        $total_credits = (float) $reservation['prix_personne'] * $nb_places;

        // 1. Annuler la réservation
        $updRes = $pdo->prepare("UPDATE reservations SET statut = 'annulé' WHERE id_covoiturage = ? AND id_utilisateur = ?");
        $updRes->execute([$id_covoiturage, $id_utilisateur]);

        // 2. Rétablir les places disponibles
        $updTrajet = $pdo->prepare("UPDATE covoiturages SET places_disponibles = places_disponibles + ? WHERE id_covoiturage = ?");
        $updTrajet->execute([$nb_places, $id_covoiturage]);

        // 3. Rembourser les crédits au passager
        $updCredits = $pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id_utilisateur = ?");
        $updCredits->execute([$total_credits, $id_utilisateur]);

        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Réservation annulée et crédits remboursés."]);

    } else {
        // --- CAS 2 : Annulation par le chauffeur (US 10) ---
        $checkTrajet = $pdo->prepare("SELECT id_covoiturage, statut, prix_personne, date_depart FROM covoiturages WHERE id_covoiturage = ? FOR UPDATE");
        $checkTrajet->execute([$id_covoiturage]);
        $trajet = $checkTrajet->fetch(PDO::FETCH_ASSOC);

        if (!$trajet) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Covoiturage introuvable."]);
            exit;
        }

        if ($trajet['statut'] === 'annulé' || $trajet['statut'] === 'annule') {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Le trajet est déjà annulé."]);
            exit;
        }

        // 1. Mettre à jour le statut du trajet
        $updTrajet = $pdo->prepare("UPDATE covoiturages SET statut = 'annulé' WHERE id_covoiturage = ?");
        $updTrajet->execute([$id_covoiturage]);

        // 2. Récupérer tous les passagers confirmés pour les rembourser et les notifier par email
        $getPassengers = $pdo->prepare("
            SELECT r.id_utilisateur, r.nb_places, u.email, u.pseudo
            FROM reservations r
            JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
            WHERE r.id_covoiturage = ? AND r.statut = 'confirmé'
        ");
        $getPassengers->execute([$id_covoiturage]);
        $passagers = $getPassengers->fetchAll(PDO::FETCH_ASSOC);

        $updUserCredits = $pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id_utilisateur = ?");
        $price = (float) $trajet['prix_personne'];

        foreach ($passagers as $p) {
            $places = (int) ($p['nb_places'] ?? 1);
            $refund = $price * $places;
            $updUserCredits->execute([$refund, $p['id_utilisateur']]);

            // Notification email demandée dans l'US 10
            @mail(
                $p['email'],
                "Annulation de votre covoiturage EcoRide",
                "Bonjour " . htmlspecialchars($p['pseudo']) . ",\n\nLe chauffeur a annulé le trajet prévu le " . $trajet['date_depart'] . ". Vos crédits (" . $refund . ") vous ont été intégralement remboursés.\n\nL'équipe EcoRide",
                "From: no-reply@ecoride.fr\r\nContent-Type: text/plain; charset=utf-8"
            );
        }

        // 3. Annuler toutes les réservations
        $updAllRes = $pdo->prepare("UPDATE reservations SET statut = 'annulé' WHERE id_covoiturage = ?");
        $updAllRes->execute([$id_covoiturage]);

        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Trajet annulé, participants remboursés et notifiés par email."]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne lors de l'annulation."]);
}