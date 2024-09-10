<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$index = isset($_GET['index']) ? (int)$_GET['index'] : -1;
$events_file = 'events.json';
$events = file_exists($events_file) ? json_decode(file_get_contents($events_file), true) : [];

if ($index >= 0 && isset($events[$index])) {
    $event = $events[$index];
} else {
    header('Location: admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update the event
    $events[$index] = [
        'dateTime' => $_POST['dateTime'],
        'productId' => $_POST['productId'],
        'cutoffSeconds' => (int)$_POST['cutoffSeconds'],
        'vacancies' => (int)$_POST['vacancies'],
        'currency' => $_POST['currency'],
        'pricesByCategory' => [
            'retailPrices' => [
                ['category' => 'ADULT', 'price' => (int)$_POST['prices']['ADULT']],
                ['category' => 'CHILD', 'price' => (int)$_POST['prices']['CHILD']]
            ]
        ]
    ];

    // Save the updated events list
    file_put_contents($events_file, json_encode($events, JSON_PRETTY_PRINT));
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
</head>
<body>
    <h2>Edit Event</h2>
    <form method="post" action="">
        <label for="dateTime">Date & Time:</label>
        <input type="datetime-local" name="dateTime" value="<?php echo $event['dateTime']; ?>" required><br><br>

        <label for="productId">Product ID:</label>
        <input type="text" name="productId" value="<?php echo $event['productId']; ?>" required><br><br>

        <label for="cutoffSeconds">Cutoff Seconds:</label>
        <input type="number" name="cutoffSeconds" value="<?php echo $event['cutoffSeconds']; ?>" required><br><br>

        <label for="vacancies">Vacancies:</label>
        <input type="number" name="vacancies" value="<?php echo $event['vacancies']; ?>" required><br><br>

        <label for="currency">Currency:</label>
        <input type="text" name="currency" value="<?php echo $event['currency']; ?>" required><br><br>

        <h4>Prices</h4>
        <label for="adultPrice">Adult Price:</label>
        <input type="number" name="prices[ADULT]" value="<?php echo $event['pricesByCategory']['retailPrices'][0]['price']; ?>" required><br><br>

        <label for="childPrice">Child Price:</label>
        <input type="number" name="prices[CHILD]" value="<?php echo $event['pricesByCategory']['retailPrices'][1]['price']; ?>" required><br><br>

        <button type="submit">Update Event</button>
    </form>
</body>
</html>
