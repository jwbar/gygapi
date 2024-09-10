<?php
session_start();

// API credentials
$username = 'KatariFarms';
$password = '7308ad5823701be7d2566fd2c5977529';

// API endpoint for deals
$api_url = 'https://supplier-api.getyourguide.com/sandbox/1/deals/';

// Function to create a deal using POST
function postDeal($api_url, $dealData, $username, $password) {
    // Initialize cURL session
    $ch = curl_init($api_url);

    // Set cURL options for Basic Authentication and POST request
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dealData, JSON_UNESCAPED_SLASHES)); // JSON encoding for the deal data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set Basic Authentication (username and password)
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

    // Execute the POST request and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close the cURL session
    curl_close($ch);

    return [
        'response' => json_decode($response, true),
        'httpCode' => $httpCode
    ];
}

// Hardcoded deal data
$dealData = [
    'data' => [
        'externalProductId' => 'PPYM1U',   // Product ID for the deal
        'dealName' => 'Last minute deal',  // Deal name
        'dateRange' => [
            'start' => '2023-08-21',       // Start date of the deal
            'end' => '2023-08-31'          // End date of the deal
        ],
        'dealType' => 'last_minute',       // Deal type: 'last_minute'
        'maxVacancies' => 10,              // Max vacancies for the deal
        'discountPercentage' => 10.5,      // Discount percentage
        'noticePeriodDays' => 3            // Notice period in days
    ]
];

// Handle deal submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Call the function to post the deal
    $result = postDeal($api_url, $dealData, $username, $password);
    $response = $result['response'];
    $httpCode = $result['httpCode'];

    // Handle the response and display feedback
    if ($httpCode == 200 || $httpCode == 201) {
        $message = "<p style='color: green;'>Deal posted successfully!</p>";
    } else {
        $message = "<p style='color: red;'>Failed to post deal. Error: " . json_encode($response) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Fixed Deal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Post a Fixed Deal</h2>

        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <!-- Simple Form to Trigger Deal Post -->
        <form method="post" action="post-fixed-deal.php">
            <button type="submit">Post Deal</button>
        </form>
    </div>
</body>
</html>
