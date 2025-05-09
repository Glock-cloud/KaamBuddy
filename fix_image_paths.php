<?php
include_once 'includes/functions.php';

// Ensure upload directories exist
ensureUploadDirectoriesExist();

// Initialize counters
$fixedCount = 0;
$errorCount = 0;
$skippedCount = 0;
$results = [];

// Function to fix image path
function fixImagePath($originalPath) {
    // Strip any leading slashes if they exist
    $path = ltrim($originalPath, '/');
    
    // Check if the file exists with the current path
    if (file_exists($path)) {
        return $path;
    }
    
    // Try with uploads/ prefix if it's missing
    if (strpos($path, 'uploads/') !== 0) {
        $newPath = 'uploads/' . $path;
        if (file_exists($newPath)) {
            return $newPath;
        }
    }
    
    // If nothing works, return the original path
    return $originalPath;
}

// Process work_images table
$query = "SELECT id, provider_id, image_url FROM work_images";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $imageId = $row['id'];
        $providerId = $row['provider_id'];
        $originalPath = $row['image_url'];
        
        // Try to fix the path
        $fixedPath = fixImagePath($originalPath);
        
        // If the path changed, update the database
        if ($fixedPath !== $originalPath) {
            $updateQuery = "UPDATE work_images SET image_url = '$fixedPath' WHERE id = $imageId";
            if ($conn->query($updateQuery)) {
                $fixedCount++;
                $results[] = [
                    'type' => 'work_image',
                    'id' => $imageId,
                    'provider_id' => $providerId,
                    'original_path' => $originalPath,
                    'fixed_path' => $fixedPath,
                    'status' => 'fixed'
                ];
            } else {
                $errorCount++;
                $results[] = [
                    'type' => 'work_image',
                    'id' => $imageId,
                    'provider_id' => $providerId,
                    'original_path' => $originalPath,
                    'fixed_path' => $fixedPath,
                    'status' => 'error',
                    'error' => $conn->error
                ];
            }
        } else {
            $skippedCount++;
            $results[] = [
                'type' => 'work_image',
                'id' => $imageId,
                'provider_id' => $providerId,
                'original_path' => $originalPath,
                'status' => 'skipped',
                'reason' => 'Path already correct or file not found'
            ];
        }
    }
}

// Process profile_image_url in service_providers table
$query = "SELECT id, profile_image_url FROM service_providers WHERE profile_image_url IS NOT NULL AND profile_image_url != ''";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $providerId = $row['id'];
        $originalPath = $row['profile_image_url'];
        
        // Try to fix the path
        $fixedPath = fixImagePath($originalPath);
        
        // If the path changed, update the database
        if ($fixedPath !== $originalPath) {
            $updateQuery = "UPDATE service_providers SET profile_image_url = '$fixedPath' WHERE id = $providerId";
            if ($conn->query($updateQuery)) {
                $fixedCount++;
                $results[] = [
                    'type' => 'profile_image',
                    'provider_id' => $providerId,
                    'original_path' => $originalPath,
                    'fixed_path' => $fixedPath,
                    'status' => 'fixed'
                ];
            } else {
                $errorCount++;
                $results[] = [
                    'type' => 'profile_image',
                    'provider_id' => $providerId,
                    'original_path' => $originalPath,
                    'fixed_path' => $fixedPath,
                    'status' => 'error',
                    'error' => $conn->error
                ];
            }
        } else {
            $skippedCount++;
            $results[] = [
                'type' => 'profile_image',
                'provider_id' => $providerId,
                'original_path' => $originalPath,
                'status' => 'skipped',
                'reason' => 'Path already correct or file not found'
            ];
        }
    }
}

// Process review_image in reviews table
$query = "SELECT id, provider_id, review_image FROM reviews WHERE review_image IS NOT NULL AND review_image != ''";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reviewId = $row['id'];
        $providerId = $row['provider_id'];
        $originalPath = $row['review_image'];
        
        // Try to fix the path
        $fixedPath = fixImagePath($originalPath);
        
        // If the path changed, update the database
        if ($fixedPath !== $originalPath) {
            $updateQuery = "UPDATE reviews SET review_image = '$fixedPath' WHERE id = $reviewId";
            if ($conn->query($updateQuery)) {
                $fixedCount++;
                $results[] = [
                    'type' => 'review_image',
                    'id' => $reviewId,
                    'provider_id' => $providerId,
                    'original_path' => $originalPath,
                    'fixed_path' => $fixedPath,
                    'status' => 'fixed'
                ];
            } else {
                $errorCount++;
                $results[] = [
                    'type' => 'review_image',
                    'id' => $reviewId,
                    'provider_id' => $providerId,
                    'original_path' => $originalPath,
                    'fixed_path' => $fixedPath,
                    'status' => 'error',
                    'error' => $conn->error
                ];
            }
        } else {
            $skippedCount++;
            $results[] = [
                'type' => 'review_image',
                'id' => $reviewId,
                'provider_id' => $providerId,
                'original_path' => $originalPath,
                'status' => 'skipped',
                'reason' => 'Path already correct or file not found'
            ];
        }
    }
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Image Paths - KaamChaahiye</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .summary-item {
            flex: 1;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 0 10px;
            color: white;
        }
        .fixed {
            background-color: #28a745;
        }
        .skipped {
            background-color: #ffc107;
        }
        .error {
            background-color: #dc3545;
        }
        .summary-count {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            color: white;
        }
        .tag-fixed {
            background-color: #28a745;
        }
        .tag-skipped {
            background-color: #ffc107;
            color: #212529;
        }
        .tag-error {
            background-color: #dc3545;
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
    <h1>Fix Image Paths</h1>
    
    <div class="section">
        <h2>Summary</h2>
        
        <div class="summary">
            <div class="summary-item fixed">
                <div class="summary-count"><?php echo $fixedCount; ?></div>
                <div>Fixed</div>
            </div>
            <div class="summary-item skipped">
                <div class="summary-count"><?php echo $skippedCount; ?></div>
                <div>Skipped</div>
            </div>
            <div class="summary-item error">
                <div class="summary-count"><?php echo $errorCount; ?></div>
                <div>Errors</div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($results)): ?>
    <div class="section">
        <h2>Detailed Results</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Provider ID</th>
                    <th>Original Path</th>
                    <th>Fixed Path</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo ucfirst(str_replace('_', ' ', $result['type'])); ?></td>
                    <td><?php echo isset($result['id']) ? $result['id'] : 'N/A'; ?></td>
                    <td><?php echo $result['provider_id']; ?></td>
                    <td><?php echo $result['original_path']; ?></td>
                    <td><?php echo isset($result['fixed_path']) ? $result['fixed_path'] : 'N/A'; ?></td>
                    <td>
                        <span class="status-tag tag-<?php echo $result['status']; ?>">
                            <?php echo ucfirst($result['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <a href="debug_system.php" class="btn">Run System Diagnostics</a>
    <a href="index.php" class="btn">Back to Home</a>
</body>
</html> 