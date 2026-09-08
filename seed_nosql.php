<?php
require_once __DIR__ . '/back-end/config/db_nosql.php';

if (isset($db_nosql['manager']) && $db_nosql['manager'] !== null) {
    $manager = $db_nosql['manager'];
    $dbname = $db_nosql['dbname'];

    // 1. Insertion d'avis de test
    $bulkAvis = new MongoDB\Driver\BulkWrite;
    $bulkAvis->insert([
        'pseudo' => 'Alice',
        'commentaire' => 'Super trajet avec Jean ! Conduite très souple et voiture électrique très propre.',
        'note' => 5,
        'statut' => 'en attente',
        'date' => date('Y-m-d H:i:s')
    ]);
    $bulkAvis->insert([
        'pseudo' => 'Marc',
        'commentaire' => 'Bon voyage dans l\'ensemble, départ ponctuel.',
        'note' => 4,
        'statut' => 'en attente',
        'date' => date('Y-m-d H:i:s')
    ]);
    $manager->executeBulkWrite("$dbname.avis", $bulkAvis);

    // 2. Insertion d'un incident de test (US 12)
    $bulkIncidents = new MongoDB\Driver\BulkWrite;
    $bulkIncidents->insert([
        'id_covoiturage' => 1,
        'type_incident' => 'Retard important',
        'description' => 'Le chauffeur est arrivé avec 45 minutes de retard sans prévenir.',
        'statut' => 'en_attente',
        'date' => date('Y-m-d H:i:s'),
        'passager_id' => 3,
        'passager_pseudo' => 'alice_user',
        'passager_email' => 'alice@ecoride.fr',
        'chauffeur_id' => 2,
        'chauffeur_pseudo' => 'jean_driver',
        'chauffeur_email' => 'jean@ecoride.fr',
        'lieu_depart' => 'Paris',
        'lieu_arrivee' => 'Lyon',
        'date_depart' => date('Y-m-d'),
        'heure_depart' => '08:00:00',
        'date_arrivee' => date('Y-m-d'),
        'heure_arrivee' => '12:00:00'
    ]);
    $manager->executeBulkWrite("$dbname.incidents", $bulkIncidents);

    echo "MongoDB test seed successfully added!\n";
} else {
    echo "NoSQL connection failed.\n";
}
