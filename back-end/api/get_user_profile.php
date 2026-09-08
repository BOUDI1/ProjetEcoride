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
require_once __DIR__ . '/../config/db_nosql.php';

$id_utilisateur = isset($_GET['id_utilisateur']) ? (int) $_GET['id_utilisateur'] : null;

if (!$id_utilisateur) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "L'identifiant de l'utilisateur (id_utilisateur) est requis."]);
    exit;
}

try {
    // 1. Récupération des données utilisateur et de son rôle
    $sqlUser = "
        SELECT 
            u.id_utilisateur,
            u.pseudo,
            u.nom,
            u.prenom,
            u.email,
            u.telephone,
            u.adresse,
            u.date_naissance,
            u.id_role,
            r.libelle AS role_libelle,
            u.credits,
            u.photo_profil,
            u.is_suspended
        FROM utilisateurs u
        LEFT JOIN roles r ON u.id_role = r.id_role
        WHERE u.id_utilisateur = ?
    ";

    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$id_utilisateur]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Utilisateur introuvable."]);
        exit;
    }

    // 2. Récupérer les véhicules enregistrés et leurs préférences associées (US 8)
    $sqlVehicules = "
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
    ";
    $stmtVeh = $pdo->prepare($sqlVehicules);
    $stmtVeh->execute([$id_utilisateur]);
    $vehicules = $stmtVeh->fetchAll(PDO::FETCH_ASSOC);

    // 3. Récupérer la note moyenne et le nombre d'avis validés si l'utilisateur est chauffeur (US 3/5) dans MongoDB
    $note_moyenne = 0.0;
    $total_avis = 0;

    if (isset($db_nosql['manager'])) {
        $filter = ['id_chauffeur' => $id_utilisateur, 'statut' => 'valide'];
        $qMongo = new MongoDB\Driver\Query($filter);
        $cursor = $db_nosql['manager']->executeQuery($db_nosql['dbname'] . ".avis", $qMongo);
        
        $notes = [];
        foreach ($cursor as $doc) {
            $notes[] = (int)($doc->note ?? 0);
        }
        $total_avis = count($notes);
        if ($total_avis > 0) {
            $note_moyenne = round(array_sum($notes) / $total_avis, 1);
        }
    }

    $rating = [
        "note_moyenne" => $note_moyenne,
        "total_avis" => $total_avis
    ];

    echo json_encode([
        "status" => "success",
        "data" => [
            "id" => (int) $user['id_utilisateur'],
            "pseudo" => $user['pseudo'],
            "nom" => $user['nom'],
            "prenom" => $user['prenom'],
            "email" => $user['email'],
            "telephone" => $user['telephone'],
            "adresse" => $user['adresse'],
            "date_naissance" => $user['date_naissance'],
            "id_role" => (int) $user['id_role'],
            "role" => $user['role_libelle'] ?? 'Utilisateur',
            "credits" => (float) $user['credits'],
            "photo_profil" => $user['photo_profil'] ?? 'default_avatar.png',
            "is_suspended" => (bool) $user['is_suspended'],
            "evaluation" => [
                "note_moyenne" => round((float) $rating['note_moyenne'], 1),
                "total_avis" => (int) $rating['total_avis']
            ],
            "vehicules" => $vehicules
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne du serveur."]);
}