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

if (!$data || empty($data['id_utilisateur']) || empty($data['action'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Données incomplètes (id_utilisateur et action requises)."]);
    exit;
}

$id_utilisateur = (int) $data['id_utilisateur'];
$action = trim(strtolower($data['action']));
$admin_id = isset($data['admin_id']) ? (int) $data['admin_id'] : 1; // ID de l'admin effectuant l'action

if ($action !== 'suspendre' && $action !== 'activer') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Action non valide ('suspendre' ou 'activer')."]);
    exit;
}

try {
    // 1. Vérifier si l'utilisateur existe
    $check = $pdo->prepare("SELECT id_utilisateur, pseudo, email, id_role FROM utilisateurs WHERE id_utilisateur = ?");
    $check->execute([$id_utilisateur]);
    $user = $check->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Utilisateur introuvable."]);
        exit;
    }

    // Protection : interdiction de suspendre un administrateur (id_role = 1)
    if ((int) $user['id_role'] === 1) { // Rôle Administrateur uniquement
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Impossible de modifier le statut d'un compte administrateur."]);
        exit;
    }

    // 2. Mettre à jour le statut dans la base SQL
    $nouveau_statut = ($action === 'suspendre') ? 'suspendu' : 'actif';
    $is_suspended = ($action === 'suspendre') ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE utilisateurs SET statut_compte = ?, is_suspended = ? WHERE id_utilisateur = ?");
    $stmt->execute([$nouveau_statut, $is_suspended, $id_utilisateur]);

    // 3. Journalisation NoSQL dans MongoDB (Audit Log)
    if (isset($db_nosql['manager']) && $db_nosql['manager'] !== null) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'type_evenement' => 'ADMIN_SUSPEND_ACTION',
            'admin_id' => $admin_id,
            'cible_id' => $id_utilisateur,
            'cible_pseudo' => $user['pseudo'] ?? '',
            'cible_email' => $user['email'] ?? '',
            'action' => $action,
            'statut_final' => $nouveau_statut,
            'date' => date('Y-m-d H:i:s')
        ]);
        $db_nosql['manager']->executeBulkWrite($db_nosql['dbname'] . ".audit_logs", $bulk);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Le statut du compte a été mis à jour avec succès : " . $nouveau_statut,
        "nouveau_statut" => $nouveau_statut
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne lors de la mise à jour du compte."]);
}