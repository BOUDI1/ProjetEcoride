<?php
// Configuration des Headers pour le CORS (indispensable pour Fetch)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Gestion du pré-vol (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db_nosql.php';

// Lecture sécurisée de l'entrée
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!empty($data['pseudo']) && !empty($data['commentaire']) && isset($data['note'])) {
    try {
        // Vérification que la connexion MongoDB existe
        if (isset($db_nosql['manager']) && $db_nosql['manager'] !== null) {
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert([
                'pseudo'      => htmlspecialchars($data['pseudo']),
                'commentaire' => htmlspecialchars($data['commentaire']),
                'note'        => (int)$data['note'],
                'statut'      => 'en attente', // Assure-toi que c'est 'en attente' ou 'en_attente' selon ton SQL
                'date'        => date('Y-m-d H:i:s')
            ]);

            $db_nosql['manager']->executeBulkWrite($db_nosql['dbname'] . ".avis", $bulk);

            // ⚠️ Attention à bien utiliser les mêmes clés que dans ton JavaScript
            echo json_encode(["status" => "success", "message" => "Avis envoyé avec succès !"]);
        } else {
            throw new Exception("La connexion à MongoDB a échoué.");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erreur : " . $e->getMessage()]);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Données incomplètes (pseudo, commentaire ou note manquante)."]);
}