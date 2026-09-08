<?php
header('Content-Type: application/json; charset=utf-8');
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
    // On récupère l'utilisateur avec son statut de compte
    $stmt = $pdo->prepare("SELECT id_utilisateur, nom, prenom, pseudo, email, mot_de_passe, id_role, credits, statut_compte FROM utilisateurs WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['mot_de_passe'])) {
        if ($user['statut_compte'] === 'suspendu') {
            echo json_encode(["status" => "error", "message" => "Votre compte a été suspendu par un administrateur."]);
            exit;
        }
        echo json_encode([
            "status" => "success", 
            "user" => [
                "id" => (int)$user['id_utilisateur'],
                "nom" => $user['nom'],
                "prenom" => $user['prenom'],
                "pseudo" => $user['pseudo'],
                "id_role" => (int)$user['id_role'],
                "credits" => (float)$user['credits']
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email ou mot de passe incorrect"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur : " . $e->getMessage()]);
}