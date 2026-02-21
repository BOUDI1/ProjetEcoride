<?php
// On charge le fichier .env
$env = parse_ini_file('.env');

// On définit des constantes pour MariaDB
define('DB_HOST', $env['DB_HOST']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);

// On définit la constante pour MongoDB
define('MONGO_URI', $env['MONGO_URI']);
?>