<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . '/../config/db_nosql.php';

// On récupère l'ID de l'avis et l'action (valider ou supprimer)
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !$action) {
    echo json_encode(["status" => "error", "message" => "ID ou Action manquante"]);
    exit;
}

try {
    $manager = $db_nosql['manager'];
    $bulk = new MongoDB\Driver\BulkWrite;

    if ($action === 'valider') {
        // On change le statut en "valide"
        $bulk->update(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => ['statut' => 'valide']]
        );
    } elseif ($action === 'supprimer') {
        // On supprime l'avis
        $bulk->delete(['_id' => new MongoDB\BSON\ObjectId($id)]);
    }

    $manager->executeBulkWrite($db_nosql['dbname'] . ".avis", $bulk);

    echo json_encode(["status" => "success", "message" => "Avis mis à jour"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>