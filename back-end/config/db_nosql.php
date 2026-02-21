<?php
/**
 * COMPOSANT D'ACCÈS AUX DONNÉES NOSQL (MongoDB)
 */

// 1. On va chercher le fichier de configuration qui lit le .env
// On utilise ../config/ pour remonter d'un dossier et entrer dans config
require_once dirname(__DIR__) . '/config/config.php';

try {
    // 2. On utilise l'URI définie dans le .env (ex: mongodb://root:root@localhost:27017)
    $manager = new MongoDB\Driver\Manager(MONGO_URI);
    
    // 3. On définit le nom de la base (vous pouvez aussi le mettre dans le .env plus tard)
    $dbname = "ecoride_nosql";

    // 4. On prépare la variable de connexion pour le reste de l'application
    $db_nosql = [
        'manager' => $manager,
        'dbname' => $dbname
    ];

} catch (Exception $e) {
    // Si la base Docker n'est pas lancée, on enregistre l'erreur
    error_log("Erreur de connexion MongoDB : " . $e->getMessage());
    $db_nosql = null;
}
?>