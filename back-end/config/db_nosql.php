<?php
// On n'utilise PLUS le dossier vendor ici pour éviter l'erreur de compatibilité
try {
    $host = "ecoride_db_nosql";
    $port = "27017";
    $dbname = "ecoride_nosql";

    // Utilisation de la classe native Manager (toujours disponible si l'extension est là)
    $manager = new MongoDB\Driver\Manager("mongodb://$host:$port");
    
    // On stocke le manager dans une variable globale pour l'utiliser ailleurs
    $db_nosql = [
        'manager' => $manager,
        'dbname' => $dbname
    ];

} catch (Exception $e) {
    error_log("Erreur MongoDB Native : " . $e->getMessage());
    $db_nosql = null;
}
?>