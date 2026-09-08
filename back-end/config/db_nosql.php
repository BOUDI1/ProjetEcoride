<?php
require_once __DIR__ . '/config.php';

try {
    // 2. On utilise l'URI définie dans le .env (ex: mongodb://root:root@localhost:27017)
    $manager = new MongoDB\Driver\Manager(MONGO_URI);
    
    // Test de ping pour vérifier la connexion
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $manager->executeCommand('admin', $command);

    // 4. On prépare la variable de connexion pour le reste de l'application
    $db_nosql = [
        'manager' => $manager,
        'dbname' => defined('MONGO_DB_NAME') ? MONGO_DB_NAME : 'ecoride_nosql'
    ];

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Erreur MongoDB : " . $e->getMessage()]);
    exit;
}
?>