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
    unset($events[$index]);
    // Re-index array and save back to file
    $events = array_values($events);
    file_put_contents($events_file, json_encode($events, JSON_PRETTY_PRINT));
}

header('Location: admin.php');
exit();
?>
