<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../config/db_sql.php';
require_once __DIR__ . '/../config/db_nosql.php';

// Récupération des paramètres
$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';
$eco = isset($_GET['eco']) && ($_GET['eco'] === 'true' || $_GET['eco'] === '1');
$max_prix = isset($_GET['max_prix']) && $_GET['max_prix'] !== '' ? (float)$_GET['max_prix'] : null;
$min_note = isset($_GET['min_note']) && $_GET['min_note'] !== '' ? (float)$_GET['min_note'] : null;

try {
    // 1. Requête SQL de base : trajets validés et disponibles
    $query = "SELECT c.*, u.prenom, u.pseudo, u.photo_profil, v.energie, v.modele, v.marque 
              FROM covoiturages c
              JOIN utilisateurs u ON c.id_chauffeur = u.id_utilisateur
              LEFT JOIN voitures v ON c.id_voiture = v.id_voiture
              WHERE c.places_disponibles > 0 AND c.statut = 'valide'";

    $params = [];

    if (!empty($depart)) {
        $query .= " AND c.lieu_depart LIKE ?";
        $params[] = "%$depart%";
    }
    if (!empty($arrivee)) {
        $query .= " AND c.lieu_arrivee LIKE ?";
        $params[] = "%$arrivee%";
    }
    if (!empty($date)) {
        $query .= " AND c.date_depart = ?";
        $params[] = $date;
    }
    if ($eco) {
        $query .= " AND v.energie = 'electrique'";
    }
    if ($max_prix !== null) {
        $query .= " AND c.prix_personne <= ?";
        $params[] = $max_prix;
    }

    $query .= " ORDER BY c.date_depart ASC, c.heure_depart ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];

    // 2. Traitement NoSQL (MongoDB) pour chaque chauffeur (Calcul de la note moyenne)
    foreach ($rows as $row) {
        $chauffeur_id = (int)$row['id_chauffeur'];
        $note_moyenne = 0.0;
        $total_avis = 0;

        if (isset($db_nosql['manager'])) {
            $filter = ['id_chauffeur' => $chauffeur_id, 'statut' => 'valide'];
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

        // Filtre par note minimale (US 4)
        if ($min_note !== null && $note_moyenne < $min_note) {
            continue;
        }

        $row['note_chauffeur'] = $note_moyenne;
        $row['total_avis'] = $total_avis;
        $row['est_ecologique'] = (strtolower($row['energie'] ?? '') === 'electrique');
        $results[] = $row;
    }

    // 3. Logique de date alternative (US 3)
    // Si aucun trajet n'est disponible pour la date demandée, on cherche la date la plus proche
    $closest_date = null;
    if (count($results) === 0 && !empty($date) && !empty($depart) && !empty($arrivee)) {
        $altQuery = "SELECT MIN(c.date_depart) as date_proche 
                     FROM covoiturages c
                     LEFT JOIN voitures v ON c.id_voiture = v.id_voiture
                     WHERE c.lieu_depart LIKE ? AND c.lieu_arrivee LIKE ? 
                       AND c.date_depart >= CURDATE() AND c.places_disponibles > 0 AND c.statut = 'valide'";
        
        $altParams = ["%$depart%", "%$arrivee%"];
        if ($eco) {
            $altQuery .= " AND v.energie = 'electrique'";
        }
        if ($max_prix !== null) {
            $altQuery .= " AND c.prix_personne <= ?";
            $altParams[] = $max_prix;
        }

        $altStmt = $pdo->prepare($altQuery);
        $altStmt->execute($altParams);
        $resAlt = $altStmt->fetch();
        if ($resAlt && !empty($resAlt['date_proche'])) {
            $closest_date = $resAlt['date_proche'];
        }
    }

    echo json_encode([
        "status" => "success",
        "results" => $results,
        "closest_date" => $closest_date
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}