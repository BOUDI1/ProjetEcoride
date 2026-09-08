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
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée."]);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validation des champs obligatoires selon US 8
if (
    !$data ||
    empty($data['id_utilisateur']) ||
    empty(trim($data['marque'] ?? '')) ||
    empty(trim($data['modele'] ?? '')) ||
    empty(trim($data['immatriculation'] ?? '')) ||
    empty(trim($data['energie'] ?? '')) ||
    empty(trim($data['date_premiere_immat'] ?? '')) ||
    empty($data['nb_places'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Données incomplètes : utilisateur, marque, modèle, immatriculation, énergie, date de 1ère immatriculation et nombre de places requis."
    ]);
    exit;
}

$id_utilisateur = (int) $data['id_utilisateur'];
$marque = trim($data['marque']);
$modele = trim($data['modele']);
$immatriculation = strtoupper(trim($data['immatriculation']));
$energie = strtolower(trim($data['energie']));
$couleur = trim($data['couleur'] ?? '');
$date_premiere_immat = trim($data['date_premiere_immat']);
$nb_places = (int) $data['nb_places'];

// Préférences US 8 (booléens ou tableaux d'options)
$fumeur = !empty($data['pref_fumeur']) ? 1 : 0;
$animaux = !empty($data['pref_animaux']) ? 1 : 0;
$pref_custom = trim($data['pref_custom'] ?? '');

// Validation énergie
$energies_valides = ['electrique', 'thermique', 'hybride', 'essence', 'diesel'];
if (!in_array($energie, $energies_valides, true)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Type d'énergie non valide."]);
    exit;
}

if ($nb_places <= 0 || $nb_places > 9) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Le nombre de places doit être compris entre 1 et 9."]);
    exit;
}

try {
    // 1. Vérifier si l'utilisateur existe
    $userStmt = $pdo->prepare("SELECT id_utilisateur, id_role FROM utilisateurs WHERE id_utilisateur = ?");
    $userStmt->execute([$id_utilisateur]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Utilisateur introuvable."]);
        exit;
    }

    // 2. Vérifier l'unicité de la plaque
    $checkImmat = $pdo->prepare("SELECT id_voiture FROM voitures WHERE immatriculation = ?");
    $checkImmat->execute([$immatriculation]);
    if ($checkImmat->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Cette plaque d'immatriculation est déjà enregistrée."]);
        exit;
    }

    // 3. Insertion du véhicule
    $sql = "INSERT INTO voitures (
                id_utilisateur, marque, modele, immatriculation, 
                energie, couleur, date_premiere_immat, nb_places,
                pref_fumeur, pref_animaux, pref_custom
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $id_utilisateur,
        $marque,
        $modele,
        $immatriculation,
        $energie,
        $couleur,
        $date_premiere_immat,
        $nb_places,
        $fumeur,
        $animaux,
        $pref_custom
    ]);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Véhicule et préférences enregistrés avec succès.",
        "id_voiture" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne lors de l'enregistrement."]);
}