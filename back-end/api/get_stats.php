<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';

try {
    // 1. Nombre total de trajets validés
    $stmtTrajets = $pdo->query("SELECT COUNT(*) as total FROM covoiturages WHERE statut IN ('valide', 'termine')");
    $totalTrajets = (int)$stmtTrajets->fetch()['total'];

    // 2. Nombre total d'utilisateurs
    $stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs");
    $totalUsers = (int)$stmtUsers->fetch()['total'];

    // 3. Économie de CO2 estimée (ex: 2.5 kg par trajet validé)
    $co2Economise = round($totalTrajets * 2.5, 1);

    // 4. Crédits gagnés par la plateforme (2 crédits par réservation finalisée) (US 13)
    $stmtCredits = $pdo->query("SELECT COUNT(*) * 2 as total FROM reservations WHERE statut = 'termine'");
    $totalCreditsPlateforme = (float)$stmtCredits->fetch()['total'];

    // 5. Covoiturages par jour (US 13)
    $stmtCovoitJours = $pdo->query("
        SELECT date_depart, COUNT(*) as count 
        FROM covoiturages 
        WHERE statut IN ('valide', 'termine')
        GROUP BY date_depart 
        ORDER BY date_depart ASC 
        LIMIT 10
    ");
    $covoitJours = $stmtCovoitJours->fetchAll(PDO::FETCH_ASSOC);

    // 6. Gain en crédits par jour (US 13)
    $stmtCreditsJours = $pdo->query("
        SELECT DATE(created_at) as date_jour, COUNT(*) * 2 as credits 
        FROM reservations 
        WHERE statut = 'termine'
        GROUP BY DATE(created_at) 
        ORDER BY date_jour ASC 
        LIMIT 10
    ");
    $creditsJours = $stmtCreditsJours->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => [
            "nb_trajets" => $totalTrajets,
            "nb_utilisateurs" => $totalUsers,
            "co2_economise" => $co2Economise,
            "total_credits_plateforme" => $totalCreditsPlateforme,
            "covoiturages_par_jour" => $covoitJours,
            "credits_par_jour" => $creditsJours
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>