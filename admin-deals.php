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

// Load deals
$deals_file = 'deals.json';
$deals = file_exists($deals_file) ? json_decode(file_get_contents($deals_file), true) : [];

// Handle deal creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createDeal'])) {
    // Prepare the deal data
    $new_deal = [
        'externalProductId' => $_POST['externalProductId'],
        'dealName' => $_POST['dealName'],
        'dateRange' => [
            'start' => $_POST['startDate'],
            'end' => $_POST['endDate'],
        ],
        'dealType' => $_POST['dealType'],
        'maxVacancies' => (int)$_POST['maxVacancies'],
        'discountPercentage' => (float)$_POST['discountPercentage'],
        'noticePeriodDays' => (int)$_POST['noticePeriodDays'],
    ];

    // Save the new deal in the JSON file
    $deals[] = ['data' => $new_deal];
    file_put_contents($deals_file, json_encode($deals, JSON_PRETTY_PRINT));
}

// Handle deal posting to API
if (isset($_GET['post'])) {
    $index = $_GET['post'];
    if (isset($deals[$index])) {
        $deal = $deals[$index]['data'];
        $apiResult = postDealToAPI($deal);

        // Display API result message
        if ($apiResult['httpCode'] == 200 || $apiResult['httpCode'] == 201) {
            echo "<p style='color: green;'>Deal posted successfully for deal: " . $deal['dealName'] . "</p>";
        } else {
            echo "<p style='color: red;'>Failed to post deal. Error: " . json_encode($apiResult['response']) . "</p>";
        }
    }
}

// Function to send the deal to the API
function postDealToAPI($deal) {
    $url = 'https://supplier-api.getyourguide.com/sandbox/1/deals/';
    $ch = curl_init($url);
    
    // Set cURL options for Basic Authentication and POST request
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Encode the deal data correctly (without wrapping in ['data' => $deal])
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($deal)); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "KatariFarms:7308ad5823701be7d2566fd2c5977529");

    // Execute the POST request and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Return the API response
    return ['response' => json_decode($response, true), 'httpCode' => $httpCode];
}

// Fetch published deals from the API
function getPublishedDeals() {
    $url = 'https://supplier-api.getyourguide.com/sandbox/1/deals/';
    $ch = curl_init($url);

    // Set cURL options for Basic Authentication and GET request
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "KatariFarms:7308ad5823701be7d2566fd2c5977529");

    // Execute the GET request and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        return json_decode($response, true)['deals'];
    } else {
        return null; // Return null if there was an issue
    }
}

// Fetch published deals
$publishedDeals = getPublishedDeals();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Deals Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Admin Deals Dashboard</h2>

        <div class="logout-button">
            <a href="admin-deals.php?logout=true">Logout</a>
        </div>

        <div class="dashboard-container">
            <!-- Create Deal Form -->
            <div class="deal-form">
                <h3>Create New Deal</h3>
                <form method="post" action="admin-deals.php">
                    <label for="externalProductId">External Product ID:</label>
                    <input type="text" name="externalProductId" required><br>

                    <label for="dealName">Deal Name:</label>
                    <input type="text" name="dealName" required><br>

                    <label for="startDate">Start Date:</label>
                    <input type="date" name="startDate" required><br>

                    <label for="endDate">End Date:</label>
                    <input type="date" name="endDate" required><br>

                    <label for="dealType">Deal Type:</label>
                    <select name="dealType" required>
                        <option value="last_minute">Last Minute</option>
                        <option value="early_bird">Early Bird</option>
                    </select><br>

                    <label for="maxVacancies">Max Vacancies:</label>
                    <input type="number" name="maxVacancies" required><br>

                    <label for="discountPercentage">Discount Percentage:</label>
                    <input type="number" step="0.1" name="discountPercentage" required><br>

                    <label for="noticePeriodDays">Notice Period (days):</label>
                    <input type="number" name="noticePeriodDays" required><br>

                    <button type="submit" name="createDeal">Create Deal</button>
                </form>
            </div>

            <!-- List of Deals -->
            <div class="deal-list">
                <h3>Manage Deals</h3>
                <?php if (!empty($deals)): ?>
                    <?php foreach ($deals as $index => $deal): ?>
                        <div class="deal-item">
                            <p>
                                <strong>Deal Name:</strong> <?php echo $deal['data']['dealName']; ?> <br>
                                <strong>Product ID:</strong> <?php echo $deal['data']['externalProductId']; ?> <br>
                                <strong>Date Range:</strong> <?php echo $deal['data']['dateRange']['start'] . " to " . $deal['data']['dateRange']['end']; ?> <br>
                                <strong>Max Vacancies:</strong> <?php echo $deal['data']['maxVacancies']; ?> <br>
                                <strong>Discount:</strong> <?php echo $deal['data']['discountPercentage']; ?>% <br>
                                <strong>Notice Period:</strong> <?php echo $deal['data']['noticePeriodDays']; ?> days
                            </p>
                            <a href="edit-deal.php?index=<?php echo $index; ?>">Edit</a>
                            <a href="delete-deal.php?index=<?php echo $index; ?>">Delete</a>
                            <a href="admin-deals.php?post=<?php echo $index; ?>">Post</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No deals found.</p>
                <?php endif; ?>
            </div>

            <!-- Published Deals -->
            <div class="published-deals">
                <h3>Published Deals</h3>
                <?php if (!empty($publishedDeals)): ?>
                    <?php foreach ($publishedDeals as $publishedDeal): ?>
                        <div class="published-deal-item">
                            <p>
                                <strong>Deal Name:</strong> <?php echo $publishedDeal['dealName']; ?> <br>
                                <strong>Product ID:</strong> <?php echo $publishedDeal['externalProductId']; ?> <br>
                                <strong>Date Range:</strong> <?php echo $publishedDeal['dateRange']['start'] . " to " . $publishedDeal['dateRange']['end']; ?> <br>
                                <strong>Max Vacancies:</strong> <?php echo $publishedDeal['maxVacancies']; ?> <br>
                                <strong>Discount:</strong> <?php echo $publishedDeal['discountPercentage']; ?>% <br>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No published deals found or failed to fetch published deals.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
