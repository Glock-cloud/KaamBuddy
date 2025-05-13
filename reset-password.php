
<?php
include_once 'includes/functions.php';

// Get the provider ID from URL parameter
$providerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$newPassword = isset($_GET['password']) ? $_GET['password'] : 'password123';
$resetDone = false;
$errorMsg = '';
$providerInfo = null;

// If form is submitted or ID is provided in URL
if ($providerId > 0) {
    // Check if provider exists
    $query = "SELECT id, name, email FROM service_providers WHERE id = $providerId";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $providerInfo = $result->fetch_assoc();
        
        // Reset the password
        if (updateProviderPassword($providerId, $newPassword)) {
            $resetDone = true;
        } else {
            $errorMsg = "Failed to update password: " . $conn->error;
        }
    } else {
        $errorMsg = "Provider with ID $providerId not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Provider Password - KaamBuddy</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .result { background: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        form { margin: 20px 0; padding: 15px; background: #f8f8f8; border-radius: 5px; }
        label, input { display: block; margin-bottom: 10px; }
        input[type="number"], input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #4a6ee0; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        .back { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #4a6ee0; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <h2>Reset Provider Password</h2>
    
    <?php if ($resetDone && $providerInfo): ?>
        <div class="result">
            <p class="success">Password has been reset successfully for:</p>
            <p><strong>Provider ID:</strong> <?php echo $providerInfo['id']; ?></p>
            <p><strong>Name:</strong> <?php echo $providerInfo['name']; ?></p>
            <p><strong>Email:</strong> <?php echo $providerInfo['email']; ?></p>
            <p><strong>New Password:</strong> <?php echo htmlspecialchars($newPassword); ?></p>
        </div>
    <?php elseif ($errorMsg): ?>
        <div class="result">
            <p class="error"><?php echo $errorMsg; ?></p>
        </div>
    <?php endif; ?>
    
    <form method="get" action="reset-password.php">
        <div>
            <label for="id">Provider ID:</label>
            <input type="number" id="id" name="id" value="<?php echo $providerId; ?>" required>
        </div>
        <div>
            <label for="password">New Password:</label>
            <input type="text" id="password" name="password" value="password123">
        </div>
        <button type="submit">Reset Password</button>
    </form>
    
    <a href="check-providers.php" class="back">View All Providers</a>
</body>
</html> 