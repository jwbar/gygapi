<?php
// cancel-booking.php

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the incoming JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // Extract the booking reference and GYG booking reference
        $bookingReference = $data['data']['bookingReference'] ?? null;
        $gygBookingReference = $data['data']['gygBookingReference'] ?? null;

        if ($bookingReference && $gygBookingReference) {
            // Load existing bookings from bookings.json
            $bookings_file = 'bookings.json';
            if (file_exists($bookings_file)) {
                $bookings = json_decode(file_get_contents($bookings_file), true);

                // Find and remove the booking that matches the given bookingReference and gygBookingReference
                $found = false;
                foreach ($bookings as $index => $booking) {
                    if (
                        isset($booking['bookingReference']) && $booking['bookingReference'] === $bookingReference &&
                        isset($booking['gygBookingReference']) && $booking['gygBookingReference'] === $gygBookingReference
                    ) {
                        // Remove the booking from the array
                        unset($bookings[$index]);
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    // Re-index the array to avoid gaps in array keys
                    $bookings = array_values($bookings);

                    // Save the updated bookings back to the file
                    file_put_contents($bookings_file, json_encode($bookings, JSON_PRETTY_PRINT));

                    // Respond with success
                    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
                } else {
                    http_response_code(404); // Not Found
                    echo json_encode(['error' => 'Booking not found']);
                }
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['error' => 'No bookings found']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Invalid booking reference or booking ID']);
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
?>
