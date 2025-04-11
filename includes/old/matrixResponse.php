<?php
header('Content-Type: application/json');

function getDistanceMatrix($origins, $destinations) {
    $apiKey = 'AIzaSyDza7bj4_y4MWDUAgissYW07VV8ytkruc4';
    $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';
    
    $params = [
        'origins' => $origins,
        'destinations' => $destinations,
        'key' => $apiKey,
        'units' => 'metric',
        'mode' => 'driving'
    ];
    
    $url = $baseUrl . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return json_encode(['error' => curl_error($ch), 'url' => $url]);
    }
    
    curl_close($ch);
    return $response; // Retourne directement le JSON
}

// Vérification des données POST
if (isset($_POST['origins']) && isset($_POST['destinations'])) {
    $origins = $_POST['origins'];
    $destinations = $_POST['destinations'];
    
    echo getDistanceMatrix($origins, $destinations);
} else {
    echo json_encode(['error' => 'Paramètres manquants']);
}