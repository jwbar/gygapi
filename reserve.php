<?php
// reserve.php

header('Content-Type: application/json');

// Define valid credentials (you can store these securely elsewhere)
$valid_username = 'your_username';
$valid_password = 'your_password';

// Function to send authentication error response
function send_auth_error() {
    header('WWW-Authenticate: Basic realm="Protected Area"');
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Unauthorized. Invalid credentials.']);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check for Basic Authentication headers
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        send_auth_error(); // No credentials provided
    }

    // Check if the credentials are correct
    if ($_SERVER['PHP_AUTH_USER'] !== $valid_username || $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
        send_auth_error(); // Incorrect credentials
    }

    // Retrieve the incoming JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Successfully received JSON, process the booking data
        $reservations_file = 'reservations.json';

        // Load existing reservations if the file exists
        if (file_exists($reservations_file)) {
            $reservations = json_decode(file_get_contents($reservations_file), true);
        } else {
            $reservations = [];
        }

        // Append the new reservation data
        $reservations[] = $data['data'];

        // Save the updated reservations back to the file
        file_put_contents($reservations_file, json_encode($reservations, JSON_PRETTY_PRINT));

        // Respond with a success message
        echo json_encode(['success' => true, 'message' => 'Reservation received successfully']);
    } else {
        // Invalid JSON data received
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Invalid JSON received']);
    }
} else {
    // Respond with an error if the method is not POST
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
}
?>
