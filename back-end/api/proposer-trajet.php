<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validation des données obligatoires
if (
    empty($data['depart']) || empty($data['arrivee']) || empty($data['date']) || 
    empty($data['heure']) || empty($data['prix']) || empty($data['places']) || 
    empty($data['id_chauffeur'])
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Données incomplètes (départ, arrivée, date, heure, prix, places et chauffeur requis)."]);
    exit;
}

$id_chauffeur = (int)$data['id_chauffeur'];
$id_voiture = !empty($data['id_voiture']) ? (int)$data['id_voiture'] : null;
$date_arrivee = !empty($data['date_arrivee']) ? trim($data['date_arrivee']) : trim($data['date']);
$heure_arrivee = !empty($data['heure_arrivee']) ? trim($data['heure_arrivee']) : null;

try {
    // Insérer le trajet avec statut 'en_cours' par défaut (en attente de modération)
    $sql = "INSERT INTO covoiturages (
                lieu_depart, lieu_arrivee, date_depart, heure_depart, 
                date_arrivee, heure_arrivee, prix_personne, places_disponibles, 
                id_chauffeur, id_voiture, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_cours')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        trim($data['depart']),
        trim($data['arrivee']),
        trim($data['date']),
        trim($data['heure']),
        $date_arrivee,
        $heure_arrivee,
        (float)$data['prix'], 
        (int)$data['places'],
        $id_chauffeur,
        $id_voiture
    ]);

    echo json_encode(["status" => "success", "message" => "Trajet proposé avec succès ! En attente de validation par un modérateur."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur de base de données : " . $e->getMessage()]);
}
?>