<?php
include_once 'includes/functions.php';

// Display header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>KaamChaahiye Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #4a6ee0;
        }
        h1 span {
            color: #ff6b6b;
        }
        h2 {
            margin-top: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .success {
            color: green;
        }
        .warning {
            color: orange;
        }
        .error {
            color: red;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .result-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #4a6ee0;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn-danger {
            background: #ff6b6b;
        }
        form {
            margin: 15px 0;
        }
        input[type='email'], input[type='text'] {
            padding: 8px;
            width: 300px;
        }
    </style>
</head>
<body>
    <h1>Kaam<span>चाहिए</span> Database Setup</h1>
    <p>This script will check and set up the required database tables for password reset functionality.</p>";

// Check database connection
echo "<h2>Database Connection</h2>";
if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p class='success'>✅ Database connection successful!</p>";
}

// Check if password_resets table exists
echo "<h2>Password Reset Table</h2>";
$tableExists = $conn->query("SHOW TABLES LIKE 'password_resets'")->num_rows > 0;

if ($tableExists) {
    echo "<p class='success'>✅ password_resets table already exists.</p>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE password_resets");
    echo "<h3>Table Structure:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for existing tokens
    $tokenResult = $conn->query("SELECT * FROM password_resets");
    $tokenCount = $tokenResult ? $tokenResult->num_rows : 0;
    echo "<p>Found $tokenCount existing password reset tokens.</p>";
    
    // Option to recreate
    echo "<form method='post'>";
    echo "<input type='submit' name='drop_table' value='Drop password_resets Table' class='btn btn-danger'>";
    echo "</form>";
    
    // Handle drop table request
    if (isset($_POST['drop_table'])) {
        if ($conn->query("DROP TABLE password_resets")) {
            echo "<p class='success'>Table dropped successfully. Refreshing page...</p>";
            echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
        } else {
            echo "<p class='error'>Error dropping table: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p class='warning'>⚠️ password_resets table does not exist.</p>";
    
    // Create the table
    echo "<p>Attempting to create password_resets table...</p>";
    
    $result = ensurePasswordResetTable();
    
    if ($result) {
        echo "<p class='success'>✅ password_resets table created successfully!</p>";
    } else {
        echo "<p class='error'>❌ Failed to create password_resets table: " . $conn->error . "</p>";
    }
}

// Test creating a token
echo "<h2>Token Generation Test</h2>";
echo "<form method='post'>";
echo "<label for='test_email'>Test with Provider Email:</label><br>";
echo "<input type='email' id='test_email' name='test_email' required placeholder='provider@example.com'>";
echo "<input type='submit' value='Generate Test Token' class='btn'>";
echo "</form>";

if (isset($_POST['test_email'])) {
    $testEmail = sanitizeInput($_POST['test_email']);
    echo "<div class='result-box'>";
    echo "<p>Testing with email: " . htmlspecialchars($testEmail) . "</p>";
    
    $provider = getProviderByEmail($testEmail);
    
    if ($provider) {
        echo "<p class='success'>✅ Provider found with ID: " . $provider['id'] . "</p>";
        
        $token = createPasswordResetToken($provider['id']);
        
        if ($token) {
            echo "<p class='success'>✅ Token created successfully!</p>";
            
            // Create reset link
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/kaamchaahiye/reset_password.php?id=" . $provider['id'] . "&token=" . $token;
            
            echo "<div style='background:#e8f5e9; padding:15px; margin:15px 0; border-radius:5px;'>";
            echo "<strong>Test Reset Link:</strong><br>";
            echo "<a href='$resetLink'>$resetLink</a>";
            echo "</div>";
        } else {
            echo "<p class='error'>❌ Failed to create token: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='error'>❌ No provider found with that email.</p>";
    }
    echo "</div>";
}

// Show service_providers table
echo "<h2>Service Providers</h2>";
$providersResult = $conn->query("SELECT id, name, email FROM service_providers");
$providersCount = $providersResult ? $providersResult->num_rows : 0;

if ($providersCount > 0) {
    echo "<p>Found $providersCount registered providers:</p>";
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th></tr>";
    
    while ($row = $providersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No service providers found in the database.</p>";
}

echo "<p><a href='index.php' class='btn'>Return to Homepage</a></p>";
echo "</body></html>";
?> 