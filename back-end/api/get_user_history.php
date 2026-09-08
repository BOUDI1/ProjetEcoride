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

$id_utilisateur = isset($_GET['id_utilisateur']) ? (int) $_GET['id_utilisateur'] : null;

if (!$id_utilisateur) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "L'identifiant de l'utilisateur (id_utilisateur) est requis."]);
    exit;
}

try {
    // 1. Trajets proposés (en tant que chauffeur) + infos véhicule + liste des passagers
    $sqlProposes = "
        SELECT 
            c.id_covoiturage,
            c.date_depart,
            c.heure_depart,
            c.lieu_depart,
            c.date_arrivee,
            c.heure_arrivee,
            c.lieu_arrivee,
            c.places_disponibles,
            c.prix_personne,
            c.statut AS statut_trajet,
            v.id_voiture,
            v.marque,
            v.modele,
            v.immatriculation,
            v.energie
        FROM covoiturages c
        LEFT JOIN voitures v ON c.id_voiture = v.id_voiture
        WHERE c.id_chauffeur = ?
        ORDER BY c.date_depart DESC, c.heure_depart DESC
    ";
    $stmtProp = $pdo->prepare($sqlProposes);
    $stmtProp->execute([$id_utilisateur]);
    $proposes = $stmtProp->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les passagers pour chaque trajet proposé
    $stmtPassagers = $pdo->prepare("
        SELECT 
            r.id_reservation,
            r.id_covoiturage,
            r.nb_places,
            r.statut AS statut_reservation,
            u.id_utilisateur,
            u.pseudo,
            u.email,
            u.photo_profil
        FROM reservations r
        JOIN utilisateurs u ON r.id_utilisateur = u.id_utilisateur
        WHERE r.id_covoiturage = ?
    ");

    foreach ($proposes as &$trajet) {
        $stmtPassagers->execute([$trajet['id_covoiturage']]);
        $trajet['participants'] = $stmtPassagers->fetchAll(PDO::FETCH_ASSOC);
        $trajet['est_ecologique'] = (strtolower($trajet['energie'] ?? '') === 'electrique');
    }
    unset($trajet);

    // 2. Trajets réservés (en tant que passager) + infos chauffeur et véhicule
    $sqlReserves = "
        SELECT 
            r.id_reservation,
            r.nb_places,
            r.statut AS statut_reservation,
            r.created_at AS date_reservation,
            c.id_covoiturage,
            c.date_depart,
            c.heure_depart,
            c.lieu_depart,
            c.date_arrivee,
            c.heure_arrivee,
            c.lieu_arrivee,
            c.prix_personne,
            c.statut AS statut_trajet,
            chauffeur.id_utilisateur AS chauffeur_id,
            chauffeur.pseudo AS chauffeur_pseudo,
            chauffeur.email AS chauffeur_email,
            chauffeur.photo_profil AS chauffeur_photo,
            v.marque,
            v.modele,
            v.energie
        FROM reservations r
        JOIN covoiturages c ON r.id_covoiturage = c.id_covoiturage
        JOIN utilisateurs chauffeur ON c.id_chauffeur = chauffeur.id_utilisateur
        LEFT JOIN voitures v ON c.id_voiture = v.id_voiture
        WHERE r.id_utilisateur = ?
        ORDER BY c.date_depart DESC, c.heure_depart DESC
    ";
    $stmtRes = $pdo->prepare($sqlReserves);
    $stmtRes->execute([$id_utilisateur]);
    $reserves = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reserves as &$reservation) {
        $reservation['est_ecologique'] = (strtolower($reservation['energie'] ?? '') === 'electrique');
        $reservation['total_credits_payes'] = (float) $reservation['prix_personne'] * (int) ($reservation['nb_places'] ?? 1);
    }
    unset($reservation);

    echo json_encode([
        "status" => "success",
        "proposes" => $proposes,
        "reserves" => $reserves
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne lors de la récupération de l'historique."]);
}