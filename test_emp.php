<?php
$data = ['email' => 'employe@ecoride.fr', 'password' => 'employe123'];
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents('http://localhost:8080/back-end/api/connexion.php', false, $context);
echo "RESPONSE:\n" . $result . "\n";
