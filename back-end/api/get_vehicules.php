<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée. Requête GET attendue."]);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';

$id_utilisateur = isset($_GET['id_utilisateur']) ? (int) $_GET['id_utilisateur'] : null;

if (!$id_utilisateur) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "L'identifiant de l'utilisateur (id_utilisateur) est requis."]);
    exit;
}

try {
    // Récupérer l'ensemble des véhicules déclarés par le chauffeur
    $sql = "
        SELECT 
            id_voiture,
            marque,
            modele,
            immatriculation,
            energie,
            couleur,
            date_premiere_immat,
            nb_places,
            pref_fumeur,
            pref_animaux,
            pref_custom
        FROM voitures 
        WHERE id_utilisateur = ?
        ORDER BY id_voiture DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utilisateur]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatage propre pour le front-end
    $vehicules = [];
    foreach ($rows as $row) {
        $vehicules[] = [
            "id_voiture" => (int) $row['id_voiture'],
            "marque" => $row['marque'] ?? '',
            "modele" => $row['modele'],
            "immatriculation" => $row['immatriculation'],
            "energie" => $row['energie'],
            "couleur" => $row['couleur'],
            "date_premiere_immat" => $row['date_premiere_immat'],
            "nb_places" => (int) $row['nb_places'],
            "est_ecologique" => (strtolower($row['energie']) === 'electrique'),
            "preferences" => [
                "fumeur" => (bool) ($row['pref_fumeur'] ?? 0),
                "animaux" => (bool) ($row['pref_animaux'] ?? 0),
                "custom" => $row['pref_custom'] ?? ''
            ]
        ];
    }

    echo json_encode([
        "status" => "success",
        "count" => count($vehicules),
        "vehicules" => $vehicules
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne lors de la récupération des véhicules."]);
}