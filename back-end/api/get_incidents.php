<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée. Requête GET attendue."]);
    exit;
}

require_once __DIR__ . '/../config/db_nosql.php';

try {
    if (!isset($db_nosql['manager']) || $db_nosql['manager'] === null) {
        throw new Exception("Connexion MongoDB non configurée.");
    }

    $manager = $db_nosql['manager'];
    $dbname = $db_nosql['dbname'];

    // Récupérer les incidents triés par date décroissante
    $query = new MongoDB\Driver\Query([], ['sort' => ['date' => -1]]);
    $cursor = $manager->executeQuery("$dbname.incidents", $query);

    $incidents = [];
    foreach ($cursor as $doc) {
        $incidents[] = [
            "id" => (string) $doc->_id,
            "id_covoiturage" => $doc->id_covoiturage ?? null,
            "description" => $doc->description ?? ($doc->commentaire ?? ""),
            "statut" => $doc->statut ?? "en_attente",
            "date_signalement" => $doc->date ?? ($doc->created_at ?? ""),
            // Coordonnées passager & chauffeur (US 12)
            "passager" => [
                "id" => $doc->passager_id ?? ($doc->id_utilisateur ?? null),
                "pseudo" => $doc->passager_pseudo ?? "Non renseigné",
                "email" => $doc->passager_email ?? "Non renseigné"
            ],
            "chauffeur" => [
                "id" => $doc->chauffeur_id ?? null,
                "pseudo" => $doc->chauffeur_pseudo ?? "Non renseigné",
                "email" => $doc->chauffeur_email ?? "Non renseigné"
            ],
            // Descriptif du trajet (US 12)
            "trajet" => [
                "lieu_depart" => $doc->lieu_depart ?? "",
                "lieu_arrivee" => $doc->lieu_arrivee ?? "",
                "date_depart" => $doc->date_depart ?? "",
                "heure_depart" => $doc->heure_depart ?? "",
                "date_arrivee" => $doc->date_arrivee ?? "",
                "heure_arrivee" => $doc->heure_arrivee ?? ""
            ]
        ];
    }

    echo json_encode([
        "status" => "success",
        "count" => count($incidents),
        "incidents" => $incidents
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Erreur lors de la récupération NoSQL : " . $e->getMessage()
    ]);
}
