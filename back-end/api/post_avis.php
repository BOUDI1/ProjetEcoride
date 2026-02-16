<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db_nosql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['pseudo']) && !empty($data['commentaire']) && isset($data['note'])) {
    try {
        if (isset($db_nosql['manager'])) {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert([
                'pseudo'      => htmlspecialchars($data['pseudo']),
                'commentaire' => htmlspecialchars($data['commentaire']),
                'note'        => (int)$data['note'],
                'statut'      => 'en attente',
                'date'        => date('Y-m-d H:i:s')
            ]);

            $db_nosql['manager']->executeBulkWrite($db_nosql['dbname'] . ".avis", $bulk);

            echo json_encode(["status" => "success", "message" => "Avis envoyé avec succès !"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Données incomplètes."]);
}