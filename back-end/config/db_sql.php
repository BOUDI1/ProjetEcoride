<?php
/**
 * COMPOSANT D'ACCÈS AUX DONNÉES SQL (MariaDB)
 * Centralisé via les variables d'environnement
 */

// 1. On inclut le fichier de configuration (qui lit le .env)
// On remonte d'un dossier pour sortir de 'db' et on entre dans 'config'
require_once dirname(__DIR__) . '/config/config.php';

// 2. Préparation des paramètres de connexion via les constantes du .env
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

// 3. Options de sécurité et de performance pour PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active les erreurs détaillées
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les données en tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Sécurité maximale contre les injections SQL
];

try {
    // 4. Tentative de connexion utilisant les constantes définies dans config.php
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // echo "Connexion MariaDB réussie !"; // Décommenter pour tester en local
    
} catch (\PDOException $e) {
    // 5. Gestion d'erreur sécurisée
    // On enregistre l'erreur réelle dans les logs du serveur, pas sur l'écran de l'utilisateur
    error_log("Erreur de connexion SQL : " . $e->getMessage());
    
    // Message générique pour ne pas exposer la structure de la base au public
    die("Désolé, une erreur technique est survenue. Veuillez réessayer plus tard.");
}