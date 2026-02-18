<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../config/db_sql.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Données incomplètes"]);
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

    // 2. Insertion du nouvel utilisateur (id_role et credits inclus)
    $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, id_role, credits) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['nom'],
        $data['prenom'],
        $data['email'],
        $data['password'], 
        $data['id_role'],
        20 // On offre 20 crédits par défaut
    ]);

    echo json_encode(["status" => "success", "message" => "Compte créé avec succès !"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Erreur serveur : " . $e->getMessage()]);
}