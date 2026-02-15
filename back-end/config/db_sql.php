<?php
// Configuration de la connexion à la base de données (Paramètres Docker)
$host = 'db'; // Nom du service dans ton docker-compose.yml
$db   = 'ecoride_db';
$user = 'user';
$pass = 'password';
$charset = 'utf8mb4';

// Data Source Name : définit le type de base, l'hôte et l'encodage
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options de sécurité et de performance pour PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lance une erreur en cas de problème SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les données sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactive l'émulation pour utiliser les vraies requêtes préparées
];

try {
    // Tentative de connexion
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En cas d'erreur de connexion, on arrête tout et on affiche le message
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>