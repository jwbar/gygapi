<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Load events, reservations, and bookings
$events_file = 'events.json';
$reservations_file = 'reservations.json';
$bookings_file = 'bookings.json';

$events = file_exists($events_file) ? json_decode(file_get_contents($events_file), true) : [];
$reservations = file_exists($reservations_file) ? json_decode(file_get_contents($reservations_file), true) : [];
$bookings = file_exists($bookings_file) ? json_decode(file_get_contents($bookings_file), true) : [];

// Handle notifications
if (isset($_GET['notify']) && isset($events[$_GET['notify']])) {
    $event = $events[$_GET['notify']];

    // Convert the event dateTime to ISO 8601 format with timezone offset
    $eventDateTime = new DateTime($event['dateTime']);
    $formattedDateTime = $eventDateTime->format(DateTime::ATOM); // Example: "2022-12-01T10:00:00+02:00"

    // Prepare the notification data in the expected format
    $notification = [
        'data' => [
            'productId' => $event['productId'], // Place productId at the root level
            'availabilities' => [
                [
                    'dateTime' => $formattedDateTime, // ISO 8601 formatted dateTime
                    'vacancies' => $event['vacancies'],
                    'cutoffSeconds' => $event['cutoffSeconds'],
                    'currency' => 'EUR',
                    'pricesByCategory' => [
                        'retailPrices' => [
                            [
                                'category' => 'ADULT',
                                'price' => $event['pricesByCategory']['retailPrices'][0]['price']
                            ],
                            [
                                'category' => 'CHILD',
                                'price' => $event['pricesByCategory']['retailPrices'][1]['price']
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    // API endpoint URL
    $url = 'https://supplier-api.getyourguide.com/sandbox/1/notify-availability-update';
    
    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options for Basic Authentication
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set Basic Authentication (username: KatariFarms, password: 7308ad5823701be7d2566fd2c5977529)
    curl_setopt($ch, CURLOPT_USERPWD, "KatariFarms:7308ad5823701be7d2566fd2c5977529");

    // Execute the POST request and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close the cURL session
    curl_close($ch);

    // Handle the response
    if ($httpCode == 200) {
        echo "<p style='color: green;'>Notification sent successfully for Event ID: " . $event['productId'] . "</p>";
    } else {
        echo "<p style='color: red;'>Failed to send notification. Error: " . $response . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!--div class="container">
        <h2>Admin Dashboard</h2>

        <div class="logout-button">
            <a href="admin.php?logout=true">Logout</a>
        </div-->

        <div class="dashboard-container">
            <!-- Create Event Form -->
            <div class="event-form">
                <h3>Create New Event</h3>
                <form method="post" action="post-event.php">
                    <label for="dateTime">Date & Time:</label>
                    <input type="datetime-local" name="dateTime" required><br>

                    <label for="productId">Product ID:</label>
                    <input type="text" name="productId" required><br>

                    <label for="cutoffMinutes">Cutoff Time (minutes):</label>
                    <input type="number" name="cutoffMinutes" required><br>

                    <label for="vacancies">Vacancies:</label>
                    <input type="number" name="vacancies" required><br>

                    <!-- The currency is set to EUR automatically in the backend -->
                    <input type="hidden" name="currency" value="EUR">

                    <h4>Prices</h4>
                    <label for="adultPrice">Adult Price:</label>
                    <input type="number" name="prices[ADULT]" required><br>

                    <label for="childPrice">Child Price:</label>
                    <input type="number" name="prices[CHILD]" required><br>

                    <button type="submit">Create Event</button>
                </form>
            </div>

            <!-- List of Events, Reservations, and Bookings -->
            <div class="event-list">
            <h2>Admin Dashboard</h2>

<div class="logout-button">
    <a href="admin.php?logout=true">Logout</a>
                <h3>Manage Events</h3>
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $index => $event): ?>
                        <div class="event-item">
                            <p>
                                <strong>Product ID:</strong> <?php echo $event['productId']; ?> <br>
                                <strong>Date & Time:</strong> <?php echo $event['dateTime']; ?> <br>
                                <strong>Vacancies:</strong> <?php echo $event['vacancies']; ?>
                            </p>
                            <a href="edit-event.php?index=<?php echo $index; ?>">Edit</a>
                            <a href="delete-event.php?index=<?php echo $index; ?>">Delete</a>
                            <a href="admin.php?notify=<?php echo $index; ?>">Send Notification</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No events found.</p>
                <?php endif; ?>

                <!-- View Reservations -->
                <h3>View Reservations</h3>
                <?php if (!empty($reservations)): ?>
                    <ul>
                        <?php foreach ($reservations as $reservation): ?>
                            <li>
                                Reservation: <?php echo $reservation['gygBookingReference']; ?> - 
                                <?php echo $reservation['reservationReference']; ?>
                                <a href="cancel-reservation.php?gygBookingReference=<?php echo $reservation['gygBookingReference']; ?>">Cancel</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No reservations found.</p>
                <?php endif; ?>

                <!-- View Bookings -->
                <h3>View Bookings</h3>
                <?php if (!empty($bookings)): ?>
                    <ul>
                        <?php foreach ($bookings as $booking): ?>
                            <li>
                                Booking: <?php echo $booking['gygBookingReference']; ?> - 
                                <?php echo $booking['reservationReference']; ?>
                                <a href="cancel-booking.php?gygBookingReference=<?php echo $booking['gygBookingReference']; ?>">Cancel</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No bookings found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
