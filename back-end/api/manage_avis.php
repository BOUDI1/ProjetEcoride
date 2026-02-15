<?php
header('Content-Type: application/json');

// Inclusion de la connexion MongoDB
require_once __DIR__ . '/../config/db_nosql.php';

// On récupère les données envoyées en JSON par le front-end
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sélection de la collection "avis" (elle se crée automatiquement)
        $collection = $db_nosql->avis;

        // Préparation du document à insérer
        $nouvelAvis = [
            'id_covoiturage' => (int)$data['id_covoiturage'],
            'id_passager'    => (int)$data['id_passager'],
            'id_chauffeur'   => (int)$data['id_chauffeur'],
            'note'           => (int)$data['note'],
            'commentaire'    => htmlspecialchars($data['commentaire']),
            'statut'         => 'en_attente', // Pour la modération de l'employé (US 12)
            'date_creation'  => new MongoDB\BSON\UTCDateTime()
        ];

        // Insertion dans MongoDB
        $result = $collection->insertOne($nouvelAvis);

        echo json_encode([
            "status" => "success",
            "message" => "Avis enregistré et en attente de modération",
            "id" => $result->getInsertedId()
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Erreur NoSQL : " . $e->getMessage()
        ]);
    }
} else {
    // Si c'est une requête GET, on récupère les avis validés
    try {
        $collection = $db_nosql->avis;
        $avisValides = $collection->find(['statut' => 'valide'])->toArray();
        echo json_encode($avisValides);
    } catch (Exception $e) {
        echo json_encode([]);
    }
}
?>