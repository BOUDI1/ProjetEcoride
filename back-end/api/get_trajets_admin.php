<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_sql.php';

try {
    // On cherche UNIQUEMENT les trajets qui attendent une validation
    $sql = "SELECT c.*, u.prenom 
            FROM covoiturages c 
            JOIN utilisateurs u ON c.id_chauffeur = u.id_utilisateur 
            WHERE c.statut = 'en_cours' 
            ORDER BY c.date_depart ASC";
    
    $stmt = $pdo->query($sql);
    $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // On renvoie "results" car c'est ce que ton JS dans moderation.html utilise
    echo json_encode(["status" => "success", "results" => $trajets]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}