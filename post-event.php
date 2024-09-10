<?php
// post-event.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data and convert minutes to seconds
    $cutoffSeconds = (int)$_POST['cutoffMinutes'] * 60;

    $event = [
        'dateTime' => $_POST['dateTime'],
        'productId' => $_POST['productId'],
        'cutoffSeconds' => $cutoffSeconds, // Converted to seconds
        'vacancies' => (int)$_POST['vacancies'],
        'currency' => $_POST['currency'], // Automatically EUR
        'pricesByCategory' => [
            'retailPrices' => []
        ]
    ];

    // Add prices
    foreach ($_POST['prices'] as $category => $price) {
        $event['pricesByCategory']['retailPrices'][] = [
            'category' => strtoupper($category),
            'price' => (int)$price
        ];
    }

    // Load existing events from the JSON file
    $events_file = 'events.json';
    $events = [];

    if (file_exists($events_file)) {
        $events = json_decode(file_get_contents($events_file), true);
    }

    // Append the new event
    $events[] = $event;

    // Save the events back to the file
    file_put_contents($events_file, json_encode($events, JSON_PRETTY_PRINT));

    // Redirect to the admin page
    header('Location: admin.php');
    exit();
}
?>
