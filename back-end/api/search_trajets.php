<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_sql.php';

// Récupération des paramètres de recherche
$depart  = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date    = $_GET['date'] ?? '';
$eco     = $_GET['eco'] ?? 'false'; // Filtre voiture électrique

try {
    // Construction de la requête SQL avec jointure
    // On lie 'covoiturages' à 'utilisateurs' (chauffeur) et 'voitures'
    $sql = "SELECT c.*, u.prenom, u.photo_profil, v.modele, v.energie 
            FROM covoiturages c
            JOIN utilisateurs u ON c.id_chauffeur = u.id_utilisateur
            JOIN voitures v ON u.id_utilisateur = v.id_utilisateur
            WHERE c.lieu_depart LIKE :depart 
            AND c.lieu_arrivee LIKE :arrivee 
            AND c.statut = 'en_cours'";

    // Si le filtre écologique est coché
    if ($eco === 'true') {
        $sql .= " AND v.energie = 'electrique'";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'depart'  => "%$depart%",
        'arrivee' => "%$arrivee%"
    ]);

    $resultats = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "count"  => count($resultats),
        "results" => $resultats
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Erreur lors de la recherche : " . $e->getMessage()
    ]);
}
?>