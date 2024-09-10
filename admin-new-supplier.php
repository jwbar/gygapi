<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Load suppliers
$suppliers_file = 'suppliers.json';
$suppliers = file_exists($suppliers_file) ? json_decode(file_get_contents($suppliers_file), true) : [];

// Handle supplier creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createSupplier'])) {
    // Prepare the supplier data
    $new_supplier = [
        'externalSupplierId' => $_POST['externalSupplierId'],  // User-provided externalSupplierId
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'legalCompanyName' => $_POST['legalCompanyName'],
        'websiteUrl' => $_POST['websiteUrl'],
        'country' => $_POST['country'],
        'currency' => $_POST['currency'],
        'email' => $_POST['email'],
        'legalStatus' => $_POST['legalStatus'],
        'mobileNumber' => $_POST['mobileNumber'],
        'city' => $_POST['city'],
        'postalCode' => $_POST['postalCode'],
        'stateOrRegion' => $_POST['stateOrRegion']
    ];

    // Save the new supplier in the JSON file
    $suppliers[] = ['data' => $new_supplier];
    file_put_contents($suppliers_file, json_encode($suppliers, JSON_PRETTY_PRINT));
}

// Handle supplier posting to API
if (isset($_GET['post'])) {
    $index = $_GET['post'];
    if (isset($suppliers[$index])) {
        $supplier = $suppliers[$index]['data'];
        $apiResult = postSupplierToAPI($supplier);

        // Display API result message
        if ($apiResult['httpCode'] == 200 || $apiResult['httpCode'] == 201) {
            echo "<p style='color: green;'>Supplier posted successfully: " . $supplier['firstName'] . " " . $supplier['lastName'] . "</p>";
        } else {
            echo "<p style='color: red;'>Failed to post supplier. Error: " . json_encode($apiResult['response']) . "</p>";
        }
    }
}

// Function to send the supplier data to the API
function postSupplierToAPI($supplier) {
    $url = 'https://supplier-api.getyourguide.com/sandbox/1/suppliers/';
    $ch = curl_init($url);
    
    // Set cURL options for Basic Authentication and POST request
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['data' => $supplier], JSON_UNESCAPED_SLASHES));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   // curl_setopt($ch, CURLOPT_USERPWD, "KatariFarms:7308ad5823701be7d2566fd2c5977529");
   curl_setopt($ch, CURLOPT_USERPWD, "escaperoomberlinJay:Ute9zuje!");

    // Execute the POST request and capture the response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Return the API response
    return ['response' => json_decode($response, true), 'httpCode' => $httpCode];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin New Supplier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Admin New Supplier</h2>

        <div class="logout-button">
            <a href="admin-new-supplier.php?logout=true">Logout</a>
        </div>

        <div class="dashboard-container">
            <!-- Create Supplier Form -->
            <div class="supplier-form">
                <h3>Create New Supplier</h3>
                <form method="post" action="admin-new-supplier.php">
                    <label for="externalSupplierId">External Supplier ID:</label>
                    <input type="text" name="externalSupplierId" required><br>

                    <label for="firstName">First Name:</label>
                    <input type="text" name="firstName" required><br>

                    <label for="lastName">Last Name:</label>
                    <input type="text" name="lastName" required><br>

                    <label for="legalCompanyName">Legal Company Name:</label>
                    <input type="text" name="legalCompanyName" required><br>

                    <label for="websiteUrl">Website URL:</label>
                    <input type="url" name="websiteUrl" required><br>

                    <label for="country">Country:</label>
                    <input type="text" name="country" required><br>

                    <label for="currency">Currency:</label>
                    <input type="text" name="currency" required><br>

                    <label for="email">Email:</label>
                    <input type="email" name="email" required><br>

                    <label for="legalStatus">Legal Status:</label>
                    <select name="legalStatus" required>
                        <option value="company">Company</option>
                        <option value="individual">Individual</option>
                    </select><br>

                    <label for="mobileNumber">Mobile Number:</label>
                    <input type="tel" name="mobileNumber" required><br>

                    <label for="city">City:</label>
                    <input type="text" name="city" required><br>

                    <label for="postalCode">Postal Code:</label>
                    <input type="text" name="postalCode" required><br>

                    <label for="stateOrRegion">State/Region:</label>
                    <input type="text" name="stateOrRegion" required><br>

                    <button type="submit" name="createSupplier">Create Supplier</button>
                </form>
            </div>

            <!-- List of Suppliers -->
            <div class="supplier-list">
                <h3>Manage Suppliers</h3>
                <?php if (!empty($suppliers)): ?>
                    <?php foreach ($suppliers as $index => $supplier): ?>
                        <div class="supplier-item">
                            <p>
                                <strong>Supplier ID:</strong> <?php echo $supplier['data']['externalSupplierId']; ?> <br>
                                <strong>First Name:</strong> <?php echo $supplier['data']['firstName']; ?> <br>
                                <strong>Last Name:</strong> <?php echo $supplier['data']['lastName']; ?> <br>
                                <strong>Company Name:</strong> <?php echo $supplier['data']['legalCompanyName']; ?> <br>
                                <strong>Country:</strong> <?php echo $supplier['data']['country']; ?> <br>
                                <strong>Email:</strong> <?php echo $supplier['data']['email']; ?>
                            </p>
                            <a href="admin-new-supplier.php?post=<?php echo $index; ?>">Post</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No suppliers found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
