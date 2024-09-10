<?php
// cancel-reservation.php

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the incoming JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Extract the reservation reference and GYG booking reference
        $reservationReference = $data['data']['reservationReference'] ?? null;
        $gygBookingReference = $data['data']['gygBookingReference'] ?? null;

        if ($reservationReference && $gygBookingReference) {
            // Load existing reservations from reservations.json
            $reservations_file = 'reservations.json';
            if (file_exists($reservations_file)) {
                $reservations = json_decode(file_get_contents($reservations_file), true);

                // Find and remove the reservation that matches the given reservationReference and gygBookingReference
                $found = false;
                foreach ($reservations as $index => $reservation) {
                    if (
                        isset($reservation['reservationReference']) && $reservation['reservationReference'] === $reservationReference &&
                        isset($reservation['gygBookingReference']) && $reservation['gygBookingReference'] === $gygBookingReference
                    ) {
                        // Remove the reservation from the array
                        unset($reservations[$index]);
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    // Re-index the array to avoid gaps in array keys
                    $reservations = array_values($reservations);

                    // Save the updated reservations back to the file
                    file_put_contents($reservations_file, json_encode($reservations, JSON_PRETTY_PRINT));

                    // Save the cancellation in cancellations.json
                    $cancellations_file = 'cancellations.json';
                    if (file_exists($cancellations_file)) {
                        $cancellations = json_decode(file_get_contents($cancellations_file), true);
                    } else {
                        $cancellations = [];
                    }
                    $cancellations[] = $data['data'];
                    file_put_contents($cancellations_file, json_encode($cancellations, JSON_PRETTY_PRINT));

                    // Respond with success
                    echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);
                } else {
                    http_response_code(404); // Not Found
                    echo json_encode(['error' => 'Reservation not found']);
                }
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['error' => 'No reservations found']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Invalid reservation reference or booking reference']);
        }
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
