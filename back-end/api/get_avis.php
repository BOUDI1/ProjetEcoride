<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// On appelle la nouvelle config native (sans vendor)
require_once __DIR__ . '/../config/db_nosql.php';

try {
    if (isset($db_nosql['manager'])) {
        $manager = $db_nosql['manager'];
        $dbname = $db_nosql['dbname'];

        // On crée la requête pour récupérer tous les documents de la collection "avis"
        $query = new MongoDB\Driver\Query([]); 
        $cursor = $manager->executeQuery("$dbname.avis", $query);

        $listeAvis = [];
        foreach ($cursor as $document) {
            $listeAvis[] = [
                "id"          => (string)$document->_id,
                "pseudo"      => $document->pseudo ?? "Anonyme",
                "commentaire" => $document->commentaire ?? "",
                "note"        => $document->note ?? 0,
                "statut"      => $document->statut ?? "en attente"
            ];
        }

        echo json_encode([
            "status" => "success",
            "data"   => $listeAvis
        ]);
    } else {
        throw new Exception("Connexion NoSQL non configurée.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}