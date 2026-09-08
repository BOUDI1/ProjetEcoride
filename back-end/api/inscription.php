<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['email']) || !isset($data['password']) || !isset($data['pseudo'])) {
    echo json_encode(["status" => "error", "message" => "Données incomplètes (pseudo, email et mot de passe requis)"]);
    exit;
}

try {
    // 1. Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $check->execute([$data['email']]);
    if ($check->fetch()) {
        echo json_encode(["status" => "error", "message" => "Cet email est déjà utilisé"]);
        exit;
    }

    // 2. Vérifier si le pseudo existe déjà
    $checkPseudo = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE pseudo = ?");
    $checkPseudo->execute([$data['pseudo']]);
    if ($checkPseudo->fetch()) {
        echo json_encode(["status" => "error", "message" => "Ce pseudo est déjà utilisé"]);
        exit;
    }

    // 3. Cryptage sécurisé du mot de passe
    $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
    $id_role = isset($data['id_role']) ? (int)$data['id_role'] : 2;

    // 4. Insertion du nouvel utilisateur
    $sql = "INSERT INTO utilisateurs (nom, prenom, pseudo, email, mot_de_passe, id_role, credits) VALUES (?, ?, ?, ?, ?, ?, 20.00)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['nom'] ?? '',
        $data['prenom'] ?? '',
        trim($data['pseudo']),
        trim($data['email']),
        $password_hash, 
        $id_role
    ]);

    echo json_encode(["status" => "success", "message" => "Compte créé avec succès !"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}