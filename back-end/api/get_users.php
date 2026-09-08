<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db_sql.php';

try {
    // Récupérer la liste des utilisateurs, chauffeurs et employés (exclure l'administrateur principal ou tous les lister)
    $sql = "
        SELECT 
            u.id_utilisateur,
            u.nom,
            u.prenom,
            u.pseudo,
            u.email,
            u.telephone,
            u.credits,
            u.id_role,
            r.libelle AS role_libelle,
            u.statut_compte,
            u.is_suspended
        FROM utilisateurs u
        LEFT JOIN roles r ON u.id_role = r.id_role
        ORDER BY u.id_role ASC, u.nom ASC
    ";

    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatage propre
    $data = [];
    foreach ($users as $u) {
        $data[] = [
            "id" => (int)$u['id_utilisateur'],
            "nom" => $u['nom'],
            "prenom" => $u['prenom'],
            "pseudo" => $u['pseudo'],
            "email" => $u['email'],
            "telephone" => $u['telephone'] ?? '',
            "credits" => (float)$u['credits'],
            "id_role" => (int)$u['id_role'],
            "role" => $u['role_libelle'] ?? 'Utilisateur',
            "statut_compte" => $u['statut_compte'],
            "is_suspended" => (bool)$u['is_suspended']
        ];
    }

    echo json_encode(["status" => "success", "users" => $data], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Impossible de charger la liste des utilisateurs : " . $e->getMessage()]);
}
?>
