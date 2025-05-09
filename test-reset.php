<?php
include_once 'includes/functions.php';

// Ensure password_resets table exists
ensurePasswordResetTable();

// Check if the table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'password_resets'")->num_rows > 0;

// Get all service providers
$query = "SELECT id, name, email, password_hash FROM service_providers";
$providers = [];
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $providers[] = $row;
    }
}

// Get all reset tokens
$query = "SELECT * FROM password_resets";
$tokens = [];
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tokens[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Test - KaamBuddy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            color: #4a6ee0;
        }
        .section {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #4a6ee0;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Password Reset Test</h1>
    
    <div class="section">
        <h2>Database Check</h2>
        <p>Password reset table exists: <span class="<?php echo $tableExists ? 'success' : 'error'; ?>"><?php echo $tableExists ? 'Yes' : 'No'; ?></span></p>
        
        <?php if (!$tableExists): ?>
            <p class="error">The password_resets table does not exist. Creating it now...</p>
            <?php 
                $created = ensurePasswordResetTable();
                echo '<p>Table creation ' . ($created ? 'succeeded' : 'failed') . '</p>';
            ?>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Service Providers</h2>
        <?php if (!empty($providers)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Password Hash</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($providers as $provider): ?>
                        <tr>
                            <td><?php echo $provider['id']; ?></td>
                            <td><?php echo htmlspecialchars($provider['name']); ?></td>
                            <td><?php echo htmlspecialchars($provider['email']); ?></td>
                            <td><?php echo substr($provider['password_hash'], 0, 20) . '...'; ?></td>
                            <td>
                                <a href="forgot_password.php?prefill=<?php echo urlencode($provider['email']); ?>" class="btn">Test Reset</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No service providers found in the database.</p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Reset Tokens</h2>
        <?php if (!empty($tokens)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Provider ID</th>
                        <th>Token (first 10 chars)</th>
                        <th>Created At</th>
                        <th>Expires At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tokens as $tokenData): ?>
                        <?php $isExpired = strtotime($tokenData['expires_at']) < time(); ?>
                        <tr>
                            <td><?php echo $tokenData['id']; ?></td>
                            <td><?php echo $tokenData['provider_id']; ?></td>
                            <td><?php echo substr($tokenData['token'], 0, 10) . '...'; ?></td>
                            <td><?php echo $tokenData['created_at']; ?></td>
                            <td><?php echo $tokenData['expires_at']; ?></td>
                            <td class="<?php echo $isExpired ? 'error' : 'success'; ?>">
                                <?php echo $isExpired ? 'Expired' : 'Valid'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No reset tokens found in the database.</p>
        <?php endif; ?>
    </div>
    
    <div>
        <a href="index.php" class="btn">Back to Home</a>
    </div>
</body>
</html> 