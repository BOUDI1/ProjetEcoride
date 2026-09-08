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

// Optionnel mais recommandé : Vérification de session / token Admin ici (US 13)

$data = json_decode(file_get_contents("php://input"), true);

if (
    !$data ||
    empty(trim($data['nom'] ?? '')) ||
    empty(trim($data['prenom'] ?? '')) ||
    empty(trim($data['email'] ?? '')) ||
    empty(trim($data['password'] ?? ''))
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Tous les champs sont obligatoires (nom, prénom, email, mot de passe)."]);
    exit;
}

$nom = trim($data['nom']);
$prenom = trim($data['prenom']);
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$password = trim($data['password']);

if (!$email) {
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => "Format d'adresse email invalide."]);
    exit;
}

// Hachage sécurisé du mot de passe (BCRYPT)
$password_hash = password_hash($password, PASSWORD_BCRYPT);

try {
    // 1. Vérification de l'unicité de l'email
    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(["status" => "error", "message" => "Cette adresse email est déjà utilisée."]);
        exit;
    }

    // 2. Génération automatique d'un pseudo unique pour l'employé
    $basePseudo = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $prenom . '_' . $nom));
    if (empty($basePseudo)) {
        $basePseudo = 'employe';
    }
    $pseudo = $basePseudo;
    $counter = 1;
    while (true) {
        $checkPseudo = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE pseudo = ?");
        $checkPseudo->execute([$pseudo]);
        if (!$checkPseudo->fetch()) {
            break;
        }
        $pseudo = $basePseudo . $counter;
        $counter++;
    }

    // 3. Insérer le compte employé (Rôle 4 = Employé)
    $sql = "INSERT INTO utilisateurs (nom, prenom, pseudo, email, mot_de_passe, id_role, credits, statut_compte) VALUES (?, ?, ?, ?, ?, 4, 0.00, 'actif')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $prenom, $pseudo, $email, $password_hash]);

    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Compte employé créé avec succès pour {$prenom} {$nom} (Pseudo: {$pseudo})."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Une erreur interne est survenue : " . $e->getMessage()]);
}