<?php
include_once 'includes/functions.php';

// Ensure upload directories exist
ensureUploadDirectoriesExist();

// Initialize output
$output = [];

// Check database connection
try {
    $output['database_connection'] = 'Success: Connected to database';
} catch (Exception $e) {
    $output['database_connection'] = 'Error: ' . $e->getMessage();
}

// Check service_providers table
$tableQuery = "SHOW TABLES LIKE 'service_providers'";
$result = $conn->query($tableQuery);
$output['service_providers_table'] = ($result && $result->num_rows > 0) ? 'Success: Table exists' : 'Error: Table does not exist';

// Check if email and password columns exist in service_providers
if ($result && $result->num_rows > 0) {
    $columnQuery = "SHOW COLUMNS FROM service_providers LIKE 'email'";
    $result = $conn->query($columnQuery);
    $output['email_column'] = ($result && $result->num_rows > 0) ? 'Success: Column exists' : 'Error: Column does not exist';
    
    $columnQuery = "SHOW COLUMNS FROM service_providers LIKE 'password_hash'";
    $result = $conn->query($columnQuery);
    $output['password_hash_column'] = ($result && $result->num_rows > 0) ? 'Success: Column exists' : 'Error: Column does not exist';
}

// Check work_images table
$tableQuery = "SHOW TABLES LIKE 'work_images'";
$result = $conn->query($tableQuery);
$output['work_images_table'] = ($result && $result->num_rows > 0) ? 'Success: Table exists' : 'Error: Table does not exist';

// Check password_resets table
$tableQuery = "SHOW TABLES LIKE 'password_resets'";
$result = $conn->query($tableQuery);
$output['password_resets_table'] = ($result && $result->num_rows > 0) ? 'Success: Table exists' : 'Error: Table does not exist';

// Check directory permissions
$directories = [
    'uploads',
    'uploads/profiles',
    'uploads/work',
    'uploads/reviews'
];

foreach ($directories as $dir) {
    if (file_exists($dir)) {
        $output[$dir . '_exists'] = 'Success: Directory exists';
        $output[$dir . '_writable'] = is_writable($dir) ? 'Success: Directory is writable' : 'Error: Directory is not writable';
    } else {
        $output[$dir . '_exists'] = 'Error: Directory does not exist';
    }
}

// Try to create a test file to verify write permissions
$testFile = 'uploads/test.txt';
$testContent = 'This is a test file to verify write permissions. Time: ' . date('Y-m-d H:i:s');
if (file_put_contents($testFile, $testContent) !== false) {
    $output['file_write_test'] = 'Success: Test file created successfully';
    // Clean up test file
    unlink($testFile);
} else {
    $output['file_write_test'] = 'Error: Unable to create test file';
}

// Count records in tables
$query = "SELECT COUNT(*) as count FROM service_providers";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $output['service_providers_count'] = 'Service Providers: ' . $row['count'];
}

$query = "SELECT COUNT(*) as count FROM work_images";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $output['work_images_count'] = 'Work Images: ' . $row['count'];
}

// List recent work images
$query = "SELECT * FROM work_images ORDER BY id DESC LIMIT 5";
$result = $conn->query($query);
$workImages = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $workImages[] = [
            'id' => $row['id'],
            'provider_id' => $row['provider_id'],
            'image_url' => $row['image_url'],
            'file_exists' => file_exists($row['image_url']) ? 'Yes' : 'No',
        ];
    }
}
$output['recent_work_images'] = $workImages;

// Display output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Diagnostic - KaamChaahiye</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #4a6ee0;
            border-bottom: 2px solid #4a6ee0;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .item {
            margin-bottom: 10px;
            padding: 10px;
            background: #fff;
            border-radius: 3px;
            border-left: 5px solid #ccc;
        }
        .success {
            border-left-color: #28a745;
        }
        .error {
            border-left-color: #dc3545;
        }
        .info {
            border-left-color: #17a2b8;
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
        .btn {
            display: inline-block;
            background: #4a6ee0;
            color: #fff;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn:hover {
            background: #3a5cd0;
        }
    </style>
</head>
<body>
    <h1>KaamChaahiye System Diagnostic</h1>
    
    <div class="section">
        <h2>Database Status</h2>
        
        <?php foreach(['database_connection', 'service_providers_table', 'email_column', 'password_hash_column', 'work_images_table', 'password_resets_table'] as $key): ?>
            <?php if(isset($output[$key])): ?>
                <div class="item <?php echo strpos($output[$key], 'Success') !== false ? 'success' : 'error'; ?>">
                    <strong><?php echo str_replace('_', ' ', ucfirst($key)); ?>:</strong> <?php echo $output[$key]; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <div class="section">
        <h2>Directory Status</h2>
        
        <?php foreach($directories as $dir): ?>
            <?php if(isset($output[$dir . '_exists'])): ?>
                <div class="item <?php echo strpos($output[$dir . '_exists'], 'Success') !== false ? 'success' : 'error'; ?>">
                    <strong><?php echo $dir; ?> Directory:</strong> <?php echo $output[$dir . '_exists']; ?>
                    <?php if(isset($output[$dir . '_writable'])): ?>
                        <br><strong>Writable:</strong> <?php echo $output[$dir . '_writable']; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php if(isset($output['file_write_test'])): ?>
            <div class="item <?php echo strpos($output['file_write_test'], 'Success') !== false ? 'success' : 'error'; ?>">
                <strong>File Write Test:</strong> <?php echo $output['file_write_test']; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Record Counts</h2>
        
        <?php foreach(['service_providers_count', 'work_images_count'] as $key): ?>
            <?php if(isset($output[$key])): ?>
                <div class="item info">
                    <strong><?php echo str_replace('_', ' ', ucfirst(str_replace('_count', '', $key))); ?>:</strong> <?php echo $output[$key]; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <?php if (!empty($output['recent_work_images'])): ?>
    <div class="section">
        <h2>Recent Work Images</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Provider ID</th>
                    <th>Image URL</th>
                    <th>File Exists</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($output['recent_work_images'] as $image): ?>
                <tr>
                    <td><?php echo $image['id']; ?></td>
                    <td><?php echo $image['provider_id']; ?></td>
                    <td><?php echo $image['image_url']; ?></td>
                    <td><?php echo $image['file_exists']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <a href="index.php" class="btn">Back to Home</a>
</body>
</html> 