<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Load deals
$deals_file = 'deals.json';
$deals = file_exists($deals_file) ? json_decode(file_get_contents($deals_file), true) : [];

// Get the index of the deal to edit
if (isset($_GET['index']) && isset($deals[$_GET['index']])) {
    $index = $_GET['index'];
    $deal = $deals[$index]['data'];
} else {
    header('Location: admin-deals.php');
    exit();
}

// Handle deal editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update deal data with form inputs
    $deals[$index]['data'] = [
        'dealId' => $deal['dealId'], // Keep the existing dealId
        'externalProductId' => $_POST['externalProductId'],
        'dealName' => $_POST['dealName'],
        'dateRange' => [
            'start' => $_POST['startDate'],
            'end' => $_POST['endDate'],
        ],
        'dealType' => $_POST['dealType'],
        'maxVacancies' => (int)$_POST['maxVacancies'],
        'discountPercentage' => (float)$_POST['discountPercentage'],
        'noticePeriodDays' => (int)$_POST['noticePeriodDays']
    ];

    // Save updated deals back to the JSON file
    file_put_contents($deals_file, json_encode($deals, JSON_PRETTY_PRINT));

    // Redirect back to admin page
    header('Location: admin-deals.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Deal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Deal</h2>
        <form method="post" action="edit-deal.php?index=<?php echo $index; ?>">
            <label for="externalProductId">External Product ID:</label>
            <input type="text" name="externalProductId" value="<?php echo $deal['externalProductId']; ?>" required><br>

            <label for="dealName">Deal Name:</label>
            <input type="text" name="dealName" value="<?php echo $deal['dealName']; ?>" required><br>

            <label for="startDate">Start Date:</label>
            <input type="date" name="startDate" value="<?php echo $deal['dateRange']['start']; ?>" required><br>

            <label for="endDate">End Date:</label>
            <input type="date" name="endDate" value="<?php echo $deal['dateRange']['end']; ?>" required><br>

            <label for="dealType">Deal Type:</label>
            <select name="dealType" required>
                <option value="last_minute" <?php echo $deal['dealType'] == 'last_minute' ? 'selected' : ''; ?>>Last Minute</option>
                <option value="early_bird" <?php echo $deal['dealType'] == 'early_bird' ? 'selected' : ''; ?>>Early Bird</option>
            </select><br>

            <label for="maxVacancies">Max Vacancies:</label>
            <input type="number" name="maxVacancies" value="<?php echo $deal['maxVacancies']; ?>" required><br>

            <label for="discountPercentage">Discount Percentage:</label>
            <input type="number" step="0.1" name="discountPercentage" value="<?php echo $deal['discountPercentage']; ?>" required><br>

            <label for="noticePeriodDays">Notice Period (days):</label>
            <input type="number" name="noticePeriodDays" value="<?php echo $deal['noticePeriodDays']; ?>" required><br>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
