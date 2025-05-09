<?php
include_once 'includes/functions.php';

// Ensure directories exist
ensureUploadDirectoriesExist();

// Display diagnostic information
echo "<h3>Upload Directory Status</h3>";
$directories = ['uploads', 'uploads/work', 'uploads/profiles', 'uploads/reviews'];
foreach ($directories as $dir) {
    echo "$dir - ";
    if (file_exists($dir)) {
        echo "Exists: Yes, ";
        echo "Writable: " . (is_writable($dir) ? "Yes" : "No");
        echo "<br>";
    } else {
        echo "Exists: No - <strong>Creating now...</strong> ";
        if (mkdir($dir, 0777, true)) {
            echo "Successfully created!";
        } else {
            echo "Failed to create directory";
        }
        echo "<br>";
    }
}

// Display form for testing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-section { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        h2 { color: #4a6ee0; }
        input, button { padding: 10px; margin: 10px 0; }
        button { background: #4a6ee0; color: white; border: none; cursor: pointer; }
        img { border: 1px solid #ddd; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
    
    <h2>Image Upload Diagnostic Tool</h2>
    
    <div class="test-section">
        <h3>Test 1: Single Image Upload</h3>
        <form action="upload-test.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="test_image" accept="image/*">
            <button type="submit" name="test_single_upload">Upload Test Image</button>
        </form>
    </div>
    
    <div class="test-section">
        <h3>Test 2: Multiple Image Upload</h3>
        <form action="upload-test.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="test_images[]" accept="image/*" multiple>
            <button type="submit" name="test_multiple_upload">Upload Multiple Images</button>
        </form>
    </div>
    
    <div class="test-section">
        <h3>Test 3: Database Image Entry</h3>
        <form action="upload-test.php" method="POST">
            <input type="number" name="provider_id" placeholder="Provider ID" required>
            <input type="text" name="image_path" placeholder="Image Path (e.g., uploads/work/123.jpg)" required>
            <button type="submit" name="test_db_entry">Add to Database</button>
        </form>
    </div>
    ';
} else {
    echo '<style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .result { background: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        img { max-width: 300px; border: 1px solid #ddd; margin: 10px 0; }
        pre { background: #eee; padding: 10px; overflow: auto; }
        .back { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #4a6ee0; color: white; text-decoration: none; border-radius: 4px; }
    </style>';
    
    // Process single image upload test
    if (isset($_POST['test_single_upload'])) {
        echo "<div class='result'>";
        echo "<h3>Single Image Upload Test Results</h3>";
        
        if (isset($_FILES['test_image']) && $_FILES['test_image']['error'] === 0) {
            echo "<p>File information:</p>";
            echo "<pre>";
            print_r($_FILES['test_image']);
            echo "</pre>";
            
            $uploadDir = 'uploads/work/';
            $uploadResult = uploadImage($_FILES['test_image'], $uploadDir);
            
            if ($uploadResult) {
                echo "<p class='success'>Success! Image uploaded to: $uploadResult</p>";
                echo "<img src='$uploadResult' alt='Uploaded test image'>";
                
                // Test database insert with provider ID 2 (or any existing ID)
                $demoProviderId = 2; 
                $insertQuery = "INSERT INTO work_images (provider_id, image_url) VALUES ($demoProviderId, '$uploadResult')";
                
                if ($conn->query($insertQuery)) {
                    echo "<p class='success'>Success! Image path saved to database</p>";
                    echo "<p>SQL: $insertQuery</p>";
                } else {
                    echo "<p class='error'>Error saving to database: " . $conn->error . "</p>";
                    echo "<p>SQL: $insertQuery</p>";
                }
            } else {
                echo "<p class='error'>Upload failed! Possible issues:</p>";
                echo "<ul>";
                echo "<li>Check directory permissions for $uploadDir</li>";
                echo "<li>Make sure the image is a valid format (jpg, png, gif)</li>";
                echo "<li>Ensure PHP has write access to the directory</li>";
                echo "</ul>";
            }
        } else {
            echo "<p class='error'>No image selected or upload error</p>";
            if (isset($_FILES['test_image'])) {
                echo "<p>Error code: " . $_FILES['test_image']['error'] . "</p>";
                echo "<p>Possible error meanings:<br>";
                echo "1 = The uploaded file exceeds the upload_max_filesize directive in php.ini<br>";
                echo "2 = The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form<br>";
                echo "3 = The uploaded file was only partially uploaded<br>";
                echo "4 = No file was uploaded<br>";
                echo "6 = Missing a temporary folder<br>";
                echo "7 = Failed to write file to disk<br>";
                echo "8 = A PHP extension stopped the file upload</p>";
            }
        }
        echo "</div>";
    }
    
    // Process multiple image upload test
    if (isset($_POST['test_multiple_upload'])) {
        echo "<div class='result'>";
        echo "<h3>Multiple Image Upload Test Results</h3>";
        
        if (isset($_FILES['test_images']) && !empty($_FILES['test_images']['name'][0])) {
            echo "<p>Files information:</p>";
            echo "<pre>";
            print_r($_FILES['test_images']);
            echo "</pre>";
            
            $uploadDir = 'uploads/work/';
            $successCount = 0;
            $errorCount = 0;
            $workImagesCount = count($_FILES['test_images']['name']);
            
            for ($i = 0; $i < $workImagesCount; $i++) {
                $workImage = [
                    'name' => $_FILES['test_images']['name'][$i],
                    'type' => $_FILES['test_images']['type'][$i],
                    'tmp_name' => $_FILES['test_images']['tmp_name'][$i],
                    'error' => $_FILES['test_images']['error'][$i],
                    'size' => $_FILES['test_images']['size'][$i]
                ];
                
                $uploadResult = uploadImage($workImage, $uploadDir);
                
                if ($uploadResult) {
                    $successCount++;
                    echo "<p class='success'>Image $i uploaded successfully: $uploadResult</p>";
                    echo "<img src='$uploadResult' alt='Uploaded test image $i'>";
                    
                    // Test database insert with provider ID 2 (or any existing ID)
                    $demoProviderId = 2;
                    $insertQuery = "INSERT INTO work_images (provider_id, image_url) VALUES ($demoProviderId, '$uploadResult')";
                    
                    if ($conn->query($insertQuery)) {
                        echo "<p class='success'>Image $i saved to database</p>";
                    } else {
                        echo "<p class='error'>Error saving image $i to database: " . $conn->error . "</p>";
                    }
                } else {
                    $errorCount++;
                    echo "<p class='error'>Failed to upload image $i</p>";
                }
            }
            
            echo "<p>Summary: $successCount images uploaded successfully, $errorCount failed</p>";
        } else {
            echo "<p class='error'>No images selected or upload error</p>";
        }
        echo "</div>";
    }
    
    // Process database entry test
    if (isset($_POST['test_db_entry'])) {
        echo "<div class='result'>";
        echo "<h3>Database Entry Test Results</h3>";
        
        $providerId = (int)$_POST['provider_id'];
        $imagePath = sanitizeInput($_POST['image_path']);
        
        if ($providerId > 0 && !empty($imagePath)) {
            // Check if provider exists
            $checkQuery = "SELECT id FROM service_providers WHERE id = $providerId";
            $result = $conn->query($checkQuery);
            
            if ($result && $result->num_rows > 0) {
                // Check if file exists
                $fileExists = file_exists($imagePath);
                echo "<p>File exists check: " . ($fileExists ? "Yes" : "No") . "</p>";
                
                // Try to insert anyway for testing
                $insertQuery = "INSERT INTO work_images (provider_id, image_url) VALUES ($providerId, '$imagePath')";
                
                if ($conn->query($insertQuery)) {
                    echo "<p class='success'>Success! Entry added to database</p>";
                    echo "<p>SQL: $insertQuery</p>";
                    
                    if ($fileExists) {
                        echo "<p>Image preview:</p>";
                        echo "<img src='$imagePath' alt='Test image'>";
                    } else {
                        echo "<p class='error'>Warning: File doesn't exist, but database entry was created.</p>";
                    }
                } else {
                    echo "<p class='error'>Error adding to database: " . $conn->error . "</p>";
                    echo "<p>SQL: $insertQuery</p>";
                }
            } else {
                echo "<p class='error'>Provider ID $providerId does not exist!</p>";
            }
        } else {
            echo "<p class='error'>Invalid provider ID or image path</p>";
        }
        echo "</div>";
    }
    
    echo '<a href="upload-test.php" class="back">Back to Tests</a>';
}
?> 