<?php
/**
 * Configuration de la connexion à la base de données MariaDB (SQL)
 * Adaptée pour l'environnement Docker EcoRide
 */

// 1. Paramètres de connexion (Correspondent à ton docker-compose.yml)
$host = 'ecoride_db_sql'; // Nom exact du conteneur dans Docker
$db   = 'ecoride';        // Nom de la base de données
$user = 'root';           // Utilisateur configuré
$pass = 'root';           // Mot de passe configuré
$charset = 'utf8mb4';     // Encodage pour supporter tous les caractères (accents, emojis)

// 2. Construction du DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. Options de sécurité et de performance pour PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active les erreurs détaillées
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les données en tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Utilise les vraies requêtes préparées (sécurité contre injection SQL)
];

try {
    // 4. Tentative de connexion
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Optionnel : décommenter pour tester la connexion dans le navigateur
    // echo "Connexion SQL réussie !"; 
    
} catch (\PDOException $e) {
    // 5. Gestion d'erreur propre
    // On ne montre pas le mot de passe dans l'erreur pour la sécurité
    error_log("Erreur de connexion SQL : " . $e->getMessage());
    die("Désolé, une erreur technique est survenue. Veuillez réessayer plus tard.");
}