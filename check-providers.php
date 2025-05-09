<?php
include_once 'includes/functions.php';

// Ensure upload directories exist
ensureUploadDirectoriesExist();

echo "<h2>Service Provider Check</h2>";

// Get all service providers
$query = "SELECT id, name, email FROM service_providers ORDER BY id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h3>Available Provider IDs:</h3>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}</li>";
    }
    echo "</ul>";
    
    echo "<p><strong>Total Providers:</strong> " . $result->num_rows . "</p>";
} else {
    echo "<p><strong>Error:</strong> No service providers found in the database!</p>";
    
    // Create a sample provider if none exist
    echo "<h3>Creating a sample provider for testing...</h3>";
    
    $name = "Test Provider";
    $email = "test@example.com";
    $password = password_hash("password123", PASSWORD_DEFAULT);
    $location = "Mumbai";
    $services = "Test Service\nAnother Service";
    $phone = "1234567890";
    $whatsapp = "1234567890";
    
    $insertQuery = "INSERT INTO service_providers (name, email, password_hash, location, services, phone, whatsapp) 
                    VALUES ('$name', '$email', '$password', '$location', '$services', '$phone', '$whatsapp')";
    
    if ($conn->query($insertQuery)) {
        $newId = $conn->insert_id;
        echo "<p><strong>Success!</strong> Created test provider with ID: $newId</p>";
        echo "<p>Use these credentials to test:</p>";
        echo "<ul>";
        echo "<li>Email: test@example.com</li>";
        echo "<li>Password: password123</li>";
        echo "</ul>";
    } else {
        echo "<p><strong>Error creating test provider:</strong> " . $conn->error . "</p>";
    }
}

// Get all work images
$query = "SELECT wi.id, wi.provider_id, wi.image_url, sp.name 
          FROM work_images wi
          LEFT JOIN service_providers sp ON wi.provider_id = sp.id
          ORDER BY wi.id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h3>Work Images:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Provider ID</th><th>Provider Name</th><th>Image URL</th><th>File Exists</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['provider_id']}</td>";
        echo "<td>" . ($row['name'] ?? 'Missing Provider!') . "</td>";
        echo "<td>{$row['image_url']}</td>";
        echo "<td>" . (file_exists($row['image_url']) ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No work images found.</p>";
}

echo "<p><a href='index.php'>Back to Home</a> | <a href='debug_system.php'>System Diagnostics</a> | <a href='upload-test.php'>Upload Test</a></p>";
?> 