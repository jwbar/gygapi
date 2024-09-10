<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Load deals
$deals_file = 'deals.json';
$deals = file_exists($deals_file) ? json_decode(file_get_contents($deals_file), true) : [];

// Get the index of the deal to delete
if (isset($_GET['index']) && isset($deals[$_GET['index']])) {
    $index = $_GET['index'];

    // Remove the deal from the array
    unset($deals[$index]);

    // Re-index the array to avoid gaps in the array keys
    $deals = array_values($deals);

    // Save the updated deals array back to the JSON file
    file_put_contents($deals_file, json_encode($deals, JSON_PRETTY_PRINT));

    // Redirect back to admin page
    header('Location: admin-deals.php');
    exit();
} else {
    // If the index is not valid, redirect to the admin page
    header('Location: admin-deals.php');
    exit();
}
