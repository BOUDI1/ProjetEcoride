<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

// Vérification de la présence de toutes les données
if (empty($data['depart']) || empty($data['arrivee']) || empty($data['date']) || 
    empty($data['heure']) || empty($data['prix']) || empty($data['places']) || empty($data['id_chauffeur'])) {
    echo json_encode(["status" => "error", "message" => "Données incomplètes"]);
    exit;
}

try {
    $sql = "INSERT INTO covoiturages (lieu_depart, lieu_arrivee, date_depart, heure_depart, prix_personne, places_disponibles, id_chauffeur, statut) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'en_cours')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['depart'],
        $data['arrivee'],
        $data['date'],
        $data['heure'],
        $data['prix'], 
        $data['places'],
        $data['id_chauffeur']
    ]);

    echo json_encode(["status" => "success", "message" => "Trajet publié avec succès !"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur SQL : " . $e->getMessage()]);
}