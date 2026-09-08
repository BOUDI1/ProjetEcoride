<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db_sql.php';

try {
    // On récupère les trajets qui ont des places disponibles
    // Optionnel : ajouter "WHERE statut = 'valide'" si tu as un système de modération
    $sql = "SELECT * FROM covoiturages WHERE places_disponibles > 0 ORDER BY date_depart ASC";
    $stmt = $pdo->query($sql);
    $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "trajets" => $trajets]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}