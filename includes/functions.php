<?php
// Include database connection
require_once 'db_connect.php';

/**
 * Sanitize input data
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Upload image file and return the path
 * @param array $file $_FILES array item
 * @param string $directory Directory to store uploads
 * @return string|bool Path to uploaded file or false on failure
 */
function uploadImage($file, $directory = 'uploads/') {
    // Create directory if it doesn't exist
    ensureUploadDirectoriesExist();
    
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    // Check if file is an actual image
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return false;
    }
    
    // Allow certain file formats
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return false;
    }
    
    // Generate unique filename
    $newFilename = uniqid() . '.' . $fileExtension;
    $targetFile = $directory . $newFilename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $targetFile;
    } else {
        return false;
    }
}

/**
 * Get average rating for a service provider
 * @param int $providerId Provider ID
 * @return float Average rating
 */
function getAverageRating($providerId) {
    global $conn;
    $providerId = (int)$providerId;
    $query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE provider_id = $providerId";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        return round($row['avg_rating'], 1) ?: 0;
    }
    
    return 0;
}

/**
 * Get review count for a service provider
 * @param int $providerId Provider ID
 * @return int Number of reviews
 */
function getReviewCount($providerId) {
    global $conn;
    $providerId = (int)$providerId;
    $query = "SELECT COUNT(*) as count FROM reviews WHERE provider_id = $providerId";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['count'];
    }
    
    return 0;
}

/**
 * Get work images for a service provider
 * @param int $providerId Provider ID
 * @return array Array of image URLs
 */
function getWorkImages($providerId) {
    global $conn;
    $providerId = (int)$providerId;
    $images = [];
    
    // Ensure the work_images table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'work_images'";
    $tableExists = $conn->query($tableCheckQuery)->num_rows > 0;
    
    if (!$tableExists) {
        // Create work_images table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS work_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            provider_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            FOREIGN KEY (provider_id) REFERENCES service_providers(id) ON DELETE CASCADE
        )";
        $conn->query($createTableQuery);
    }
    
    $query = "SELECT image_url FROM work_images WHERE provider_id = $providerId";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Ensure the image path is correct
            $imagePath = $row['image_url'];
            
            // Fix common path issues
            // Remove any leading slashes
            $imagePath = ltrim($imagePath, '/');
            
            // Make sure relative paths work correctly
            if (!file_exists($imagePath) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $imagePath)) {
                $imagePath = '/' . $imagePath;
            }
            
            // Add to image array
            $images[] = $imagePath;
            
            // Log for debugging
            error_log("Work image path: $imagePath, Exists: " . (file_exists($imagePath) ? 'Yes' : 'No'));
        }
    }
    
    return $images;
}

/**
 * Generate a secure random token for password reset
 * @param int $length Token length
 * @return string Random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Ensure the password_resets table exists
 * Creates the table if it doesn't exist
 * @return bool True on success, false on failure
 */
function ensurePasswordResetTable() {
    global $conn;
    
    // Check if the table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'password_resets'")->num_rows > 0;
    
    if (!$tableExists) {
        // Create the table
        $createTableQuery = "CREATE TABLE password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            provider_id INT NOT NULL,
            token VARCHAR(64) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (provider_id) REFERENCES service_providers(id) ON DELETE CASCADE
        )";
        
        // Execute the query
        $result = $conn->query($createTableQuery);
        
        // Debug logging on localhost
        if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
            if (!$result) {
                error_log("Error creating password_resets table: " . $conn->error);
                error_log("Query: $createTableQuery");
            } else {
                error_log("Successfully created password_resets table");
            }
        }
        
        return $result;
    }
    
    return true;
}

/**
 * Create a password reset token for a provider
 * @param int $providerId Provider ID
 * @return string|bool Token on success, false on failure
 */
function createPasswordResetToken($providerId) {
    global $conn;
    
    // Ensure the table exists
    ensurePasswordResetTable();
    
    // Sanitize the providerId
    $providerId = (int)$providerId;
    
    // Delete any existing tokens for this provider
    $deleteQuery = "DELETE FROM password_resets WHERE provider_id = $providerId";
    $conn->query($deleteQuery);
    
    // Generate a random token
    $token = bin2hex(random_bytes(32));
    
    // Set expiry time (24 hours from now)
    $expiresAt = date('Y-m-d H:i:s', time() + 86400);
    
    // Insert new token into database
    $query = "INSERT INTO password_resets (provider_id, token, expires_at) 
              VALUES ($providerId, '$token', '$expiresAt')";
    
    if ($conn->query($query)) {
        return $token;
    } else {
        // Debug logging on localhost
        if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
            error_log("Error creating password reset token: " . $conn->error);
            error_log("Query: $query");
        }
        return false;
    }
}

/**
 * Validate a password reset token
 * @param int $providerId Provider ID
 * @param string $token Token to validate
 * @return bool True if token is valid, false otherwise
 */
function validateResetToken($providerId, $token) {
    global $conn;
    
    // Ensure the table exists
    ensurePasswordResetTable();
    
    // First, make sure the token is properly sanitized to avoid SQL injection
    $providerId = (int)$providerId; // Cast to integer for safety
    $token = $conn->real_escape_string($token);
    
    // Find token in database
    $query = "SELECT * FROM password_resets 
              WHERE provider_id = $providerId 
              AND token = '$token' 
              AND expires_at > NOW()";
    
    $result = $conn->query($query);
    
    // For debugging (only on localhost)
    if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
        error_log("Reset token validation query: $query");
        error_log("Query result: " . ($result ? "Success" : "Failed: " . $conn->error));
        if ($result) {
            error_log("Rows found: " . $result->num_rows);
        }
    }
    
    // If we found a valid token
    return ($result && $result->num_rows > 0);
}

/**
 * Delete a password reset token
 * @param int $providerId Provider ID
 * @param string $token Token to delete
 * @return bool True on success, false on failure
 */
function deleteResetToken($providerId, $token) {
    global $conn;
    
    $query = "DELETE FROM password_resets 
              WHERE provider_id = $providerId 
              AND token = '$token'";
    
    return $conn->query($query);
}

/**
 * Find provider by email
 * @param string $email Provider email
 * @return array|bool Provider data array or false if not found
 */
function getProviderByEmail($email) {
    global $conn;
    
    $email = sanitizeInput($email);
    $query = "SELECT * FROM service_providers WHERE email = '$email'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Authenticate provider login
 * @param string $email Provider email
 * @param string $password Provider password
 * @return array|bool Provider data array or false if authentication fails
 */
function authenticateProvider($email, $password) {
    $provider = getProviderByEmail($email);
    
    if ($provider && password_verify($password, $provider['password_hash'])) {
        return $provider;
    }
    
    return false;
}

/**
 * Update provider password
 * @param int $providerId Provider ID
 * @param string $newPassword New password
 * @return bool True on success, false on failure
 */
function updateProviderPassword($providerId, $newPassword) {
    global $conn;
    
    $providerId = (int)$providerId;
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $query = "UPDATE service_providers 
              SET password_hash = '$passwordHash' 
              WHERE id = $providerId";
    
    return $conn->query($query);
}

/**
 * Send password reset email
 * @param string $email Recipient email
 * @param string $resetLink Password reset link
 * @return bool True on success, false on failure
 */
function sendPasswordResetEmail($email, $resetLink) {
    $subject = "Password Reset - KaamBuddy";
    
    $message = "
    <html>
    <head>
        <title>Password Reset</title>
    </head>
    <body>
        <div style='font-family:Arial,sans-serif; max-width:600px; margin:0 auto; padding:20px; border:1px solid #ddd;'>
            <div style='text-align:center; margin-bottom:20px;'>
                <h1 style='color:#4a6ee0;'>Kaam<span style='color:#ff6b6b;'>Buddy</span></h1>
            </div>
            <h2>Password Reset Request</h2>
            <p>You recently requested to reset your password. Click the button below to reset it:</p>
            <p style='text-align:center;'>
                <a href='$resetLink' style='display:inline-block; padding:12px 24px; background-color:#4a6ee0; color:white; text-decoration:none; border-radius:4px; font-weight:bold;'>
                    Reset Password
                </a>
            </p>
            <p>This link will expire in 24 hours.</p>
            <p>If you did not request a password reset, please ignore this email.</p>
            <hr style='margin:20px 0; border:none; border-top:1px solid #ddd;'>
            <p style='font-size:small; color:#777; text-align:center;'>
                &copy; " . date('Y') . " KaamBuddy. All rights reserved.
            </p>
        </div>
    </body>
    </html>
    ";
    
    // Set content-type header for sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: KaamBuddy <noreply@kaambuddy.com>" . "\r\n";
    
    // Check if we're in development environment (localhost)
    if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
        // In development mode - don't try to send email, just log it
        error_log("DEVELOPMENT MODE: Would send password reset email to $email with link $resetLink");
        
        // For debugging, you can display the reset link directly
        echo "<div style='background:#e8f5e9; padding:15px; margin:15px 0; border-radius:5px;'>";
        echo "<strong>Development Mode:</strong> Email sending is disabled.<br>";
        echo "<strong>Reset Link:</strong> <a href='$resetLink'>$resetLink</a><br>";
        echo "<small>In production, this link would be emailed to: $email</small>";
        echo "</div>";
        
        return true; // Pretend the email was sent successfully
    }
    
    // In production, actually send the email
    return mail($email, $subject, $message, $headers);
}

/**
 * Ensure upload directories exist
 * Creates the necessary directories for uploads if they don't exist
 */
function ensureUploadDirectoriesExist() {
    $directories = [
        'uploads',
        'uploads/profiles',
        'uploads/work'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
?> 