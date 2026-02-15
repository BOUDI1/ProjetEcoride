<?php
// Utilisation de l'extension MongoDB installée via le Dockerfile
require_once __DIR__ . '/../../vendor/autoload.php'; 

try {
    // Connexion au service "mongodb" défini dans le docker-compose.yml
    // Le port par défaut est 27017
    $host = "mongodb";
    $port = "27017";
    $dbname = "ecoride_nosql";

    // Chaîne de connexion MongoDB
    $client = new MongoDB\Client("mongodb://$host:$port");

    // Sélection de la base de données
    $db_nosql = $client->$dbname;

    // Test de connexion silencieux
    // Les collections (ex: avis, logs) seront créées automatiquement à l'insertion
} catch (Exception $e) {
    // En cas d'erreur, on logue le message pour l'administrateur
    error_log("Erreur de connexion MongoDB : " . $e->getMessage());
    $db_nosql = null;
}
?>