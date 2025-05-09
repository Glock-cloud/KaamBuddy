<?php
include_once 'includes/functions.php';

// Get provider ID from URL
$providerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$forceDelete = isset($_GET['force']) && $_GET['force'] == 1;
$deleteMessage = '';
$errorMessage = '';

// Process deletion if ID is provided
if ($providerId > 0 && isset($_GET['confirm'])) {
    // Get provider info before deletion
    $providerQuery = "SELECT * FROM service_providers WHERE id = $providerId";
    $providerResult = $conn->query($providerQuery);
    $providerInfo = ($providerResult && $providerResult->num_rows > 0) ? $providerResult->fetch_assoc() : null;
    
    if (!$providerInfo) {
        $errorMessage = "Provider with ID $providerId not found";
    } else {
        // Check for foreign key constraints and attempt to resolve
        if ($forceDelete) {
            // First, delete related records that might cause constraint errors
            
            // Delete work images
            $deleteWorkImages = "DELETE FROM work_images WHERE provider_id = $providerId";
            $conn->query($deleteWorkImages);
            
            // Delete reviews
            $deleteReviews = "DELETE FROM reviews WHERE provider_id = $providerId";
            $conn->query($deleteReviews);
            
            // Delete password reset tokens
            $deleteTokens = "DELETE FROM password_resets WHERE provider_id = $providerId";
            $conn->query($deleteTokens);
        }
        
        // Now try to delete the provider
        $deleteQuery = "DELETE FROM service_providers WHERE id = $providerId";
        if ($conn->query($deleteQuery)) {
            $deleteMessage = "Provider '{$providerInfo['name']}' (ID: $providerId) was successfully deleted";
        } else {
            $errorMessage = "Error deleting provider: " . $conn->error;
        }
    }
}

// Get all providers for listing
$listQuery = "SELECT id, name, email, location FROM service_providers ORDER BY id";
$listResult = $conn->query($listQuery);
$providers = [];

if ($listResult && $listResult->num_rows > 0) {
    while ($row = $listResult->fetch_assoc()) {
        $providers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Provider - KaamBuddy</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h2 { color: #4a6ee0; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #f2f2f2; }
        .delete-btn { background-color: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        .force-btn { background-color: #f0ad4e; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        .back-btn { background-color: #4a6ee0; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 20px; }
        .confirmation { background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .debug-info { background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin: 20px 0; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h2>Delete Service Provider</h2>
    
    <?php if (!empty($deleteMessage)): ?>
        <div class="message success">
            <?php echo $deleteMessage; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="message error">
            <?php echo $errorMessage; ?>
            
            <?php if (strpos($errorMessage, 'foreign key constraint fails') !== false): ?>
                <p>This error occurs because there are related records (like work images or reviews) linked to this provider.</p>
                <p><a href="delete-provider.php?id=<?php echo $providerId; ?>&force=1&confirm=1" class="force-btn">Force Delete (Remove All Related Records)</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($providerId > 0 && !isset($_GET['confirm'])): ?>
        <?php
        // Get provider details for confirmation
        $confirmQuery = "SELECT * FROM service_providers WHERE id = $providerId";
        $confirmResult = $conn->query($confirmQuery);
        $provider = ($confirmResult && $confirmResult->num_rows > 0) ? $confirmResult->fetch_assoc() : null;
        
        if ($provider):
        ?>
            <div class="confirmation">
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to delete this provider?</p>
                <p><strong>ID:</strong> <?php echo $provider['id']; ?></p>
                <p><strong>Name:</strong> <?php echo $provider['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $provider['email']; ?></p>
                <p><strong>Location:</strong> <?php echo $provider['location']; ?></p>
                
                <p><strong>Warning:</strong> This action cannot be undone!</p>
                
                <a href="delete-provider.php?id=<?php echo $providerId; ?>&confirm=1" class="delete-btn">Delete Provider</a>
                <a href="delete-provider.php?id=<?php echo $providerId; ?>&force=1&confirm=1" class="force-btn">Force Delete (Remove All Related Records)</a>
                <a href="delete-provider.php" class="back-btn">Cancel</a>
            </div>
        <?php else: ?>
            <div class="message error">Provider with ID <?php echo $providerId; ?> not found</div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!empty($providers)): ?>
        <h3>Available Providers</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Location</th>
                <th>Action</th>
            </tr>
            <?php foreach ($providers as $provider): ?>
                <tr>
                    <td><?php echo $provider['id']; ?></td>
                    <td><?php echo $provider['name']; ?></td>
                    <td><?php echo $provider['email']; ?></td>
                    <td><?php echo $provider['location']; ?></td>
                    <td>
                        <a href="delete-provider.php?id=<?php echo $provider['id']; ?>" class="delete-btn">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No providers found.</p>
    <?php endif; ?>
    
    <div class="debug-info">
        <h3>Debug Information</h3>
        <p>Check database foreign key constraints:</p>
        <?php
        // Display foreign key constraints for service_providers
        $constraintsQuery = "
            SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME = 'service_providers'
            AND TABLE_SCHEMA = DATABASE()
        ";
        $constraintsResult = $conn->query($constraintsQuery);
        
        if ($constraintsResult && $constraintsResult->num_rows > 0) {
            echo "<p>Tables with foreign key references to service_providers:</p>";
            echo "<ul>";
            while ($row = $constraintsResult->fetch_assoc()) {
                echo "<li>Table: {$row['TABLE_NAME']}, Column: {$row['COLUMN_NAME']}, Constraint: {$row['CONSTRAINT_NAME']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No foreign key constraints found referencing service_providers table.</p>";
        }
        ?>
    </div>
    
    <a href="check-providers.php" class="back-btn">Back to Provider Check</a>
</body>
</html> 