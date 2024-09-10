<?php
// book.php

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the incoming JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Successfully received JSON, process the booking data
        $bookings_file = 'bookings.json';

        // Load existing bookings if the file exists
        if (file_exists($bookings_file)) {
            $bookings = json_decode(file_get_contents($bookings_file), true);
        } else {
            $bookings = [];
        }

        // Append the new booking data
        $bookings[] = $data['data'];

        // Save the updated bookings back to the file
        file_put_contents($bookings_file, json_encode($bookings, JSON_PRETTY_PRINT));

        // Respond with a success message
        echo json_encode(['success' => true, 'message' => 'Booking received successfully']);
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
