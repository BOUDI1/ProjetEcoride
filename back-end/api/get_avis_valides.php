<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db_nosql.php';

try {
    if (isset($db_nosql['manager'])) {
        // On ne sélectionne que les avis validés
        $filter = ['statut' => 'valide'];
        $options = ['sort' => ['date' => -1], 'limit' => 3]; // On affiche les 3 derniers

        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $db_nosql['manager']->executeQuery($db_nosql['dbname'] . ".avis", $query);

        $avis = iterator_to_array($cursor);

        echo json_encode(["status" => "success", "data" => $avis]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}