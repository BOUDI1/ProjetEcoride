<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_sql.php';

// Récupération des paramètres
$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';

try {
    // Requête simplifiée : on prend tout ce qui a des places
    $query = "SELECT c.*, u.prenom 
              FROM covoiturages c
              JOIN utilisateurs u ON c.id_chauffeur = u.id_utilisateur
              WHERE c.places_disponibles > 0";

    $params = [];

    if (!empty($depart)) {
        $query .= " AND c.lieu_depart LIKE ?";
        $params[] = "%$depart%";
    }
    if (!empty($arrivee)) {
        $query .= " AND c.lieu_arrivee LIKE ?";
        $params[] = "%$arrivee%";
    }
    if (!empty($date)) {
        $query .= " AND c.date_depart = ?";
        $params[] = $date;
    }

    $query .= " ORDER BY c.date_depart ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "results" => $results]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}