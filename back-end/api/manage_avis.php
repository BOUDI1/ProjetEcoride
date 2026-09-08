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

// On lit le corps JSON si présent
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$id = $data['id'] ?? $_GET['id'] ?? null;
$action = $data['action'] ?? $_GET['action'] ?? null;

if (!$id || !$action) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ID ou Action manquante"]);
    exit;
}

// Normalisation de l'action delete/supprimer et approve/valider
$action = strtolower(trim($action));
if ($action === 'delete') {
    $action = 'supprimer';
}
if ($action === 'approve') {
    $action = 'valider';
}

try {
    if (!isset($db_nosql['manager']) || $db_nosql['manager'] === null) {
        throw new Exception("Connexion MongoDB non configurée.");
    }

    $manager = $db_nosql['manager'];
    $bulk = new MongoDB\Driver\BulkWrite;

    if ($action === 'valider') {
        $bulk->update(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => ['statut' => 'valide']]
        );
        $msg = "L'avis a été approuvé et est désormais visible publiquement.";
    } elseif ($action === 'supprimer' || $action === 'refuser') {
        $bulk->delete(['_id' => new MongoDB\BSON\ObjectId($id)]);
        $msg = "L'avis a été supprimé / refusé.";
    } else {
        throw new Exception("Action non valide : " . $action);
    }

    $manager->executeBulkWrite($db_nosql['dbname'] . ".avis", $bulk);

    echo json_encode(["status" => "success", "message" => $msg]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>