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

$id_covoiturage = isset($_GET['id_covoiturage']) ? (int) $_GET['id_covoiturage'] : null;

if (!$id_covoiturage) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "L'identifiant du covoiturage (id_covoiturage) est requis."]);
    exit;
}

try {
    // 1. Récupérer le trajet, le chauffeur et la voiture associée (Correction join c.id_chauffeur)
    $sql = "
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
            c.statut,
            -- Infos Chauffeur
            u.id_utilisateur AS chauffeur_id,
            u.pseudo AS chauffeur_pseudo,
            u.photo_profil AS chauffeur_photo,
            -- Infos Voiture (US 5)
            v.id_voiture,
            v.marque,
            v.modele,
            v.immatriculation,
            v.energie,
            v.couleur,
            v.pref_fumeur,
            v.pref_animaux,
            v.pref_custom
        FROM covoiturages c 
        JOIN utilisateurs u ON c.id_chauffeur = u.id_utilisateur 
        LEFT JOIN voitures v ON c.id_voiture = v.id_voiture
        WHERE c.id_covoiturage = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_covoiturage]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trajet) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Covoiturage introuvable."]);
        exit;
    }

    $chauffeur_id = (int) $trajet['chauffeur_id'];
    $note_moyenne = 0.0;
    $total_avis = 0;
    $avisList = [];

    // 2. Récupérer les avis validés pour ce chauffeur dans MongoDB (NoSQL)
    if (isset($db_nosql['manager'])) {
        $filter = ['id_chauffeur' => $chauffeur_id, 'statut' => 'valide'];
        $qMongo = new MongoDB\Driver\Query($filter, ['sort' => ['date' => -1]]);
        $cursor = $db_nosql['manager']->executeQuery($db_nosql['dbname'] . ".avis", $qMongo);
        
        $notes = [];
        foreach ($cursor as $doc) {
            $notes[] = (int)($doc->note ?? 0);
            $avisList[] = [
                "id_avis" => (string)($doc->_id),
                "note" => (int)($doc->note ?? 0),
                "commentaire" => $doc->commentaire ?? "",
                "date_publication" => $doc->date ?? "",
                "passager_pseudo" => $doc->pseudo ?? "Anonyme",
                "passager_photo" => "default_avatar.png"
            ];
        }
        $total_avis = count($notes);
        if ($total_avis > 0) {
            $note_moyenne = round(array_sum($notes) / $total_avis, 1);
        }
    }

    // 3. Calcul de l'indicateur écologique (US 3 & US 5 : électrique = écologique)
    $isEcologique = (strtolower($trajet['energie'] ?? '') === 'electrique');

    echo json_encode([
        "status" => "success",
        "data" => [
            "id_covoiturage" => (int) $trajet['id_covoiturage'],
            "date_depart" => $trajet['date_depart'],
            "heure_depart" => $trajet['heure_depart'],
            "lieu_depart" => $trajet['lieu_depart'],
            "date_arrivee" => $trajet['date_arrivee'],
            "heure_arrivee" => $trajet['heure_arrivee'],
            "lieu_arrivee" => $trajet['lieu_arrivee'],
            "places_disponibles" => (int) $trajet['places_disponibles'],
            "prix_personne" => (float) $trajet['prix_personne'],
            "statut" => $trajet['statut'],
            "est_ecologique" => $isEcologique,
            "chauffeur" => [
                "id" => $chauffeur_id,
                "pseudo" => $trajet['chauffeur_pseudo'] ?? 'Anonyme',
                "photo_profil" => $trajet['chauffeur_photo'] ?? 'default_avatar.png',
                "note_moyenne" => $note_moyenne,
                "total_avis" => $total_avis
            ],
            "vehicule" => $trajet['id_voiture'] ? [
                "marque" => $trajet['marque'],
                "modele" => $trajet['modele'],
                "immatriculation" => $trajet['immatriculation'],
                "energie" => $trajet['energie'],
                "couleur" => $trajet['couleur'],
                "preferences" => [
                    "fumeur" => (bool) $trajet['pref_fumeur'],
                    "animaux" => (bool) $trajet['pref_animaux'],
                    "custom" => $trajet['pref_custom'] ?? ''
                ]
            ] : null,
            "avis" => $avisList
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erreur interne lors de la récupération des détails : " . $e->getMessage()]);
}