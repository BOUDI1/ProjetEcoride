<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/db_sql.php';

// 1. On récupère les données envoyées par le JavaScript
$data = json_decode(file_get_contents("php://input"), true);
$id_trajet = $data['id_covoiturage'] ?? null;

// Pour le test, on simule l'utilisateur connecté n°1 (id_passager)
$id_passager = 1; 

if (!$id_trajet) {
    echo json_encode(["status" => "error", "message" => "ID du trajet manquant."]);
    exit;
}

try {
    // 2. On démarre une transaction pour sécuriser l'opération
    $pdo->beginTransaction();

    // 3. On vérifie s'il reste vraiment de la place
    $checkStmt = $pdo->prepare("SELECT places_disponibles FROM covoiturages WHERE id_covoiturage = ? FOR UPDATE");
    $checkStmt->execute([$id_trajet]);
    $trajet = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($trajet && $trajet['places_disponibles'] > 0) {
        
        // 4. On crée la réservation dans la table 'reservations'
        $ins = $pdo->prepare("INSERT INTO reservations (id_covoiturage, id_utilisateur, statut) VALUES (?, ?, 'confirmé')");
        $ins->execute([$id_trajet, $id_passager]);

        // 5. On retire 1 place au trajet
        $upd = $pdo->prepare("UPDATE covoiturages SET places_disponibles = places_disponibles - 1 WHERE id_covoiturage = ?");
        $upd->execute([$id_trajet]);

        // 6. Si tout est OK, on valide tout
        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Réservation effectuée avec succès !"]);

    } else {
        // Pas assez de places
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Désolé, ce trajet est complet."]);
    }

} catch (Exception $e) {
    // En cas d'erreur serveur, on annule tout
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}
?>