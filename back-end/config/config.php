<?php
// 1. Détection de l'environnement (Recherche du nom de domaine Alwaysdata)
$isProduction = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'alwaysdata.net') !== false);

if ($isProduction) {
    // --- CONFIGURATION PRODUCTION (ALWAYSDATA) ---
    // On définit les constantes en "dur" pour la production
    define('DB_HOST', 'mysql-ecoridefrance.alwaysdata.net'); 
    define('DB_NAME', 'ecoridefrance_db'); // Mettez ici le nom exact créé sur Alwaysdata
    define('DB_USER', 'ecoridefrance');    // Votre utilisateur Alwaysdata
    define('DB_PASS', 'VOTRE_MOT_DE_PASSE_ALWAYSDATA');

    // Pour MongoDB (Si vous utilisez Atlas en ligne, sinon laissez vide)
    define('MONGO_URI', 'mongodb+srv://votre_uri_atlas_ici');
} else {
    // --- CONFIGURATION DÉVELOPPEMENT (DOCKER) ---
    // On garde votre logique actuelle de lecture du fichier .env local
    $env = parse_ini_file(__DIR__ . '/.env'); 

    define('DB_HOST', $env['DB_HOST']);
    define('DB_NAME', $env['DB_NAME']);
    define('DB_USER', $env['DB_USER']);
    define('DB_PASS', $env['DB_PASS']);
    define('DB_MONGO', $env['MONGO_URI']);
}
?>