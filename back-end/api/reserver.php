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

$data = json_decode(file_get_contents("php://input"), true);
$id_trajet = isset($data['id_covoiturage']) ? (int)$data['id_covoiturage'] : null;
$id_passager = isset($data['id_utilisateur']) ? (int)$data['id_utilisateur'] : null;

if (!$id_trajet || !$id_passager) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Données incomplètes (id_covoiturage et id_utilisateur requis)."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Récupérer et verrouiller le passager pour vérifier son crédit
    $stmtUser = $pdo->prepare("SELECT id_utilisateur, credits FROM utilisateurs WHERE id_utilisateur = ? FOR UPDATE");
    $stmtUser->execute([$id_passager]);
    $passenger = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$passenger) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Utilisateur passager introuvable."]);
        exit;
    }

    $passenger_credits = (float)$passenger['credits'];

    // 2. Récupérer et verrouiller le trajet
    $stmtTrajet = $pdo->prepare("SELECT id_covoiturage, id_chauffeur, places_disponibles, prix_personne, statut FROM covoiturages WHERE id_covoiturage = ? FOR UPDATE");
    $stmtTrajet->execute([$id_trajet]);
    $trajet = $stmtTrajet->fetch(PDO::FETCH_ASSOC);

    if (!$trajet) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Covoiturage introuvable."]);
        exit;
    }

    if ($trajet['statut'] !== 'valide') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Ce trajet n'est plus disponible à la réservation (statut: " . $trajet['statut'] . ")."]);
        exit;
    }

    if ((int)$trajet['id_chauffeur'] === $id_passager) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Impossible de réserver votre propre trajet."]);
        exit;
    }

    $places_disponibles = (int)$trajet['places_disponibles'];
    $prix_credits = (float)$trajet['prix_personne'];

    if ($places_disponibles <= 0) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Désolé, ce trajet est complet."]);
        exit;
    }

    // 3. Vérifier le solde de crédits (US 6)
    if ($passenger_credits < $prix_credits) {
        $pdo->rollBack();
        http_response_code(402); // Payment Required
        echo json_encode(["status" => "error", "message" => "Crédits insuffisants. Il vous faut " . $prix_credits . " crédits (solde actuel : " . $passenger_credits . ")."]);
        exit;
    }

    // 4. Créer la réservation
    $insRes = $pdo->prepare("INSERT INTO reservations (id_covoiturage, id_utilisateur, nb_places, statut) VALUES (?, ?, 1, 'confirmé')");
    $insRes->execute([$id_trajet, $id_passager]);

    // 5. Déduire les crédits du passager immédiatement (US 6)
    $updCredits = $pdo->prepare("UPDATE utilisateurs SET credits = credits - ? WHERE id_utilisateur = ?");
    $updCredits->execute([$prix_credits, $id_passager]);

    // 6. Déduire une place disponible sur le trajet
    $updPlaces = $pdo->prepare("UPDATE covoiturages SET places_disponibles = places_disponibles - 1 WHERE id_covoiturage = ?");
    $updPlaces->execute([$id_trajet]);

    $pdo->commit();
    echo json_encode([
        "status" => "success",
        "message" => "Réservation effectuée avec succès ! " . $prix_credits . " crédits ont été débités.",
        "credits_restants" => ($passenger_credits - $prix_credits)
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur technique : " . $e->getMessage()]);
}
?>