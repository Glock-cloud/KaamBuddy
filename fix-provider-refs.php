<?php
include_once 'includes/functions.php';

// Ensure upload directories exist
ensureUploadDirectoriesExist();

echo "<h2>Fix Provider References</h2>";

// First, check for valid provider IDs
$validQuery = "SELECT id FROM service_providers ORDER BY id";
$validResult = $conn->query($validQuery);
$validProviderIds = [];

if ($validResult && $validResult->num_rows > 0) {
    while ($row = $validResult->fetch_assoc()) {
        $validProviderIds[] = $row['id'];
    }
    echo "<p>Found " . count($validProviderIds) . " valid provider IDs: " . implode(', ', $validProviderIds) . "</p>";
} else {
    echo "<p>No valid provider IDs found. Please create a provider first.</p>";
    echo "<p><a href='check-providers.php'>Check Providers</a></p>";
    exit;
}

// Check for work images with invalid provider IDs
$invalidQuery = "SELECT wi.id, wi.provider_id, wi.image_url 
                FROM work_images wi 
                LEFT JOIN service_providers sp ON wi.provider_id = sp.id 
                WHERE sp.id IS NULL";
$invalidResult = $conn->query($invalidQuery);

$fixedCount = 0;
$errorCount = 0;

if ($invalidResult && $invalidResult->num_rows > 0) {
    echo "<h3>Found " . $invalidResult->num_rows . " work images with invalid provider IDs</h3>";
    
    // Fix invalid references by assigning to the first valid provider
    $defaultProviderId = $validProviderIds[0];
    
    while ($row = $invalidResult->fetch_assoc()) {
        $imageId = $row['id'];
        $currentProviderId = $row['provider_id'];
        
        echo "<p>Fixing work image #$imageId (current provider_id: $currentProviderId) -> new provider_id: $defaultProviderId</p>";
        
        $updateQuery = "UPDATE work_images SET provider_id = $defaultProviderId WHERE id = $imageId";
        
        if ($conn->query($updateQuery)) {
            $fixedCount++;
        } else {
            echo "<p>Error updating work image #$imageId: " . $conn->error . "</p>";
            $errorCount++;
        }
    }
    
    echo "<h3>Results:</h3>";
    echo "<p>Fixed: $fixedCount</p>";
    echo "<p>Errors: $errorCount</p>";
} else {
    echo "<p>No invalid provider references found in work_images table.</p>";
}

echo "<p><a href='index.php'>Back to Home</a> | <a href='debug_system.php'>System Diagnostics</a> | <a href='upload-test.php'>Upload Test</a></p>";
?> 