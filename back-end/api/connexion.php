<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs"]);
    exit;
}

try {
    // On récupère l'utilisateur
    $stmt = $pdo->prepare("SELECT id_utilisateur, nom, prenom, email, id_role, credits FROM utilisateurs WHERE email = ? AND mot_de_passe = ?");
    $stmt->execute([$data['email'], $data['password']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            "status" => "success", 
            "user" => [
                "id" => (int)$user['id_utilisateur'],
                "nom" => $user['nom'],
                "prenom" => $user['prenom'],
                "id_role" => (int)$user['id_role'], // Forçage en nombre entier
                "credits" => (int)$user['credits']
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email ou mot de passe incorrect"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur : " . $e->getMessage()]);
}