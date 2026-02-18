<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_covoiturage']) || !isset($data['action'])) {
    echo json_encode(["status" => "error", "message" => "Données incomplètes"]);
    exit;
}

$id = $data['id_covoiturage'];
$action = $data['action']; // 'valider' ou 'refuser'

try {
    if ($action === 'valider') {
        $sql = "UPDATE covoiturages SET statut = 'valide' WHERE id_covoiturage = ?";
        $msg = "Le trajet est désormais visible par tous !";
    } else {
        $sql = "UPDATE covoiturages SET statut = 'refuse' WHERE id_covoiturage = ?";
        $msg = "Le trajet a été refusé.";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    echo json_encode(["status" => "success", "message" => $msg]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}