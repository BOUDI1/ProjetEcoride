<?php
// 1. Détection de l'environnement
$isProduction = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'alwaysdata.net') !== false);

if ($isProduction) {
    // --- CONFIGURATION PRODUCTION (ALWAYSDATA) ---
    define('DB_HOST', 'mysql-ecoridefrance.alwaysdata.net'); 
    define('DB_NAME', 'ecoridefrance_db'); 
    define('DB_USER', 'ecoridefrance');    
    define('DB_PASS', 'VOTRE_MOT_DE_PASSE_ALWAYSDATA');
    define('MONGO_URI', 'mongodb+srv://votre_uri_atlas_ici');
    define('MONGO_DB_NAME', 'ecoridefrance_nosql');
} else {
    // --- CONFIGURATION DÉVELOPPEMENT (DOCKER) ---
    // On cherche le .env à la racine du projet (2 niveaux au dessus de api/)
    $envPath = __DIR__ . '/../../.env'; 
    
    if (file_exists($envPath)) {
        $env = parse_ini_file($envPath);
        define('DB_HOST', $env['DB_HOST'] ?? 'db_sql');
        define('DB_NAME', $env['DB_NAME'] ?? 'ecoride');
        define('DB_USER', $env['DB_USER'] ?? 'root');
        define('DB_PASS', $env['DB_PASS'] ?? 'root');
        define('MONGO_URI', $env['MONGO_URI'] ?? 'mongodb://root:root@db_nosql:27017');
        define('MONGO_DB_NAME', $env['MONGO_DB_NAME'] ?? 'ecoride_nosql');
    } else {
        // Valeurs de secours si le .env est introuvable
        define('DB_HOST', 'db_sql');
        define('DB_NAME', 'ecoride');
        define('DB_USER', 'root');
        define('DB_PASS', 'root');
        define('MONGO_URI', 'mongodb://root:root@db_nosql:27017');
        define('MONGO_DB_NAME', 'ecoride_nosql');
    }
}