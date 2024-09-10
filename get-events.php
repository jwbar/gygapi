<?php
// get-events.php

header('Content-Type: application/json');

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Load events from the JSON file
    $events_file = 'events.json';

    if (file_exists($events_file)) {
        $events = json_decode(file_get_contents($events_file), true);
        echo json_encode(['data' => $events], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['data' => []], JSON_PRETTY_PRINT);
    }
} else {
    // Respond with an error if the method is not GET
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Use GET.']);
}
?>
