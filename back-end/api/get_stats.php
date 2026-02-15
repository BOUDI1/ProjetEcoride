<?php
// On indique au navigateur que l'on renvoie du JSON
header('Content-Type: application/json');

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/../config/db_sql.php';

try {
    // 1. Récupération du nombre total de trajets (US 13)
    $stmtTrajets = $pdo->query("SELECT COUNT(*) as total FROM covoiturages");
    $totalTrajets = $stmtTrajets->fetch()['total'];

    // 2. Récupération du nombre d'utilisateurs inscrits
    $stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs");
    $totalUsers = $stmtUsers->fetch()['total'];

    // 3. Calcul fictif de l'économie de CO2 (Logique métier Ecoride)
    // On estime par exemple 2kg de CO2 économisés par trajet
    $co2Economise = $totalTrajets * 2.5;

    // 4. Préparation de la réponse
    $response = [
        "status" => "success",
        "data" => [
            "nb_trajets" => (int)$totalTrajets,
            "nb_utilisateurs" => (int)$totalUsers,
            "co2_economise" => (float)$co2Economise,
            "labels" => ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"], // Pour ton graphique
            "stats_hebdo" => [5, 12, 8, 15, $totalTrajets] // Données dynamiques mixées
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // En cas d'erreur, on renvoie un message propre au JavaScript
    echo json_encode([
        "status" => "error",
        "message" => "Impossible de récupérer les statistiques : " . $e->getMessage()
    ]);
}
?>