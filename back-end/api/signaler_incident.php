<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée. Requête POST attendue."]);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';
require_once __DIR__ . '/../config/db_nosql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    !$data ||
    empty($data['id_covoiturage']) ||
    empty($data['id_utilisateur']) ||
    empty(trim($data['description'] ?? ''))
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Données incomplètes (id_covoiturage, id_utilisateur et description requis)."
    ]);
    exit;
}

$id_covoiturage = (int) $data['id_covoiturage'];
$id_passager = (int) $data['id_utilisateur'];
$type_incident = trim($data['type_incident'] ?? 'Autre problème');
$description = trim($data['description']);

try {
    // 1. Récupérer les détails du trajet, du chauffeur et du passager dans SQL (US 12)
    $sql = "
        SELECT 
            c.id_covoiturage,
            c.date_depart,
            c.heure_depart,
            c.lieu_depart,
            c.date_arrivee,
            c.heure_arrivee,
            c.lieu_arrivee,
            chauffeur.id_utilisateur AS chauffeur_id,
            chauffeur.pseudo AS chauffeur_pseudo,
            chauffeur.email AS chauffeur_email,
            passager.id_utilisateur AS passager_id,
            passager.pseudo AS passager_pseudo,
            passager.email AS passager_email
        FROM covoiturages c
        JOIN utilisateurs chauffeur ON c.id_chauffeur = chauffeur.id_utilisateur
        JOIN reservations r ON c.id_covoiturage = r.id_covoiturage
        JOIN utilisateurs passager ON r.id_utilisateur = passager.id_utilisateur
        WHERE c.id_covoiturage = ? AND r.id_utilisateur = ?
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_covoiturage, $id_passager]);
    $infos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$infos) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Réservation ou covoiturage introuvable."]);
        exit;
    }

    // 2. Mettre à jour le statut dans la base relationnelle pour bloquer le paiement automatique (US 11)
    $updRes = $pdo->prepare("UPDATE reservations SET statut = 'litige' WHERE id_covoiturage = ? AND id_utilisateur = ?");
    $updRes->execute([$id_covoiturage, $id_passager]);

    // 3. Insérer le document complet et structuré dans MongoDB (US 12)
    if (!isset($db_nosql['manager']) || $db_nosql['manager'] === null) {
        throw new Exception("La connexion à MongoDB n'est pas disponible.");
    }

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'id_covoiturage' => $id_covoiturage,
        'type_incident' => $type_incident,
        'description' => $description,
        'statut' => 'en_cours',
        'date' => date('Y-m-d H:i:s'),
        'passager_id' => (int) $infos['passager_id'],
        'passager_pseudo' => $infos['passager_pseudo'],
        'passager_email' => $infos['passager_email'],
        'chauffeur_id' => (int) $infos['chauffeur_id'],
        'chauffeur_pseudo' => $infos['chauffeur_pseudo'],
        'chauffeur_email' => $infos['chauffeur_email'],
        'lieu_depart' => $infos['lieu_depart'],
        'lieu_arrivee' => $infos['lieu_arrivee'],
        'date_depart' => $infos['date_depart'],
        'heure_depart' => $infos['heure_depart'],
        'date_arrivee' => $infos['date_arrivee'],
        'heure_arrivee' => $infos['heure_arrivee']
    ]);

    $db_nosql['manager']->executeBulkWrite($db_nosql['dbname'] . ".incidents", $bulk);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "L'incident a été enregistré. Un employé examinera votre signalement avant tout transfert de crédits."
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur lors du signalement de l'incident."]);
}