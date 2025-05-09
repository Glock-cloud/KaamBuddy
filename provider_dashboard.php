<?php
include_once 'includes/functions.php';

// Ensure upload directories exist
ensureUploadDirectoriesExist();

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['provider_id'])) {
    header('Location: provider_login.php');
    exit();
}

// Get provider data
$providerId = (int)$_SESSION['provider_id'];
$provider = null;
$workImages = [];
$success = '';
$error = '';

// Fetch provider data
$query = "SELECT * FROM service_providers WHERE id = $providerId";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $provider = $result->fetch_assoc();
    
    // Get work images
    $workImages = getWorkImages($providerId);
} else {
    // If provider data not found, log out
    session_destroy();
    header('Location: provider_login.php');
    exit();
}

// Handle form submissions for different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Profile update
    if (isset($_POST['update_profile'])) {
        // Sanitize inputs
        $name = sanitizeInput($_POST['name']);
        $location = sanitizeInput($_POST['location']);
        $service_description = sanitizeInput($_POST['service_description']);
        $phone = sanitizeInput($_POST['phone']);
        $whatsapp = sanitizeInput($_POST['whatsapp']);
        
        // Validate required fields
        if (empty($name) || empty($location) || empty($service_description) || empty($phone) || empty($whatsapp)) {
            $error = "All fields are required!";
        } else {
            // Handle profile image upload
            $profileImageUrl = $provider['profile_image_url'];
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                $newProfileImageUrl = uploadImage($_FILES['profile_image'], 'uploads/profiles/');
                if ($newProfileImageUrl) {
                    $profileImageUrl = $newProfileImageUrl;
                }
            }
            
            // Update provider data
            $updateQuery = "UPDATE service_providers SET 
                            name = '$name', 
                            location = '$location', 
                            service_description = '$service_description', 
                            phone = '$phone', 
                            whatsapp = '$whatsapp', 
                            profile_image_url = '$profileImageUrl' 
                            WHERE id = $providerId";
            
            if ($conn->query($updateQuery)) {
                $success = "Profile updated successfully!";
                
                // Refresh provider data
                $result = $conn->query($query);
                if ($result && $result->num_rows > 0) {
                    $provider = $result->fetch_assoc();
                }
            } else {
                $error = "Failed to update profile: " . $conn->error;
            }
        }
    }
    
    // Password change
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate passwords
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "All password fields are required!";
        } elseif (strlen($newPassword) < 8) {
            $error = "New password must be at least 8 characters long!";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match!";
        } elseif (!password_verify($currentPassword, $provider['password_hash'])) {
            $error = "Current password is incorrect!";
        } else {
            // Update password
            if (updateProviderPassword($providerId, $newPassword)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    }
    
    // Upload work images
    if (isset($_POST['upload_work_images'])) {
        if (isset($_FILES['work_images']) && !empty($_FILES['work_images']['name'][0])) {
            $workImagesCount = count($_FILES['work_images']['name']);
            $uploadCount = 0;
            
            for ($i = 0; $i < $workImagesCount; $i++) {
                // Create file array for each work image
                $workImage = [
                    'name' => $_FILES['work_images']['name'][$i],
                    'type' => $_FILES['work_images']['type'][$i],
                    'tmp_name' => $_FILES['work_images']['tmp_name'][$i],
                    'error' => $_FILES['work_images']['error'][$i],
                    'size' => $_FILES['work_images']['size'][$i]
                ];
                
                // Upload the work image
                $workImageUrl = uploadImage($workImage, 'uploads/work/');
                if ($workImageUrl) {
                    // Insert work image into database
                    $conn->query("INSERT INTO work_images (provider_id, image_url) VALUES ($providerId, '$workImageUrl')");
                    $uploadCount++;
                }
            }
            
            if ($uploadCount > 0) {
                $success = "Uploaded $uploadCount work images successfully!";
                
                // Refresh work images
                $workImages = getWorkImages($providerId);
            } else {
                $error = "Failed to upload work images. Please try again.";
            }
        } else {
            $error = "Please select at least one image to upload.";
        }
    }
    
    // Delete work image
    if (isset($_POST['delete_work_image'])) {
        $imageUrl = sanitizeInput($_POST['image_url']);
        
        $deleteQuery = "DELETE FROM work_images WHERE provider_id = $providerId AND image_url = '$imageUrl'";
        if ($conn->query($deleteQuery)) {
            $success = "Image deleted successfully!";
            
            // Refresh work images
            $workImages = getWorkImages($providerId);
        } else {
            $error = "Failed to delete image. Please try again.";
        }
    }
    
    // Delete account
    if (isset($_POST['delete_account'])) {
        $confirmDelete = sanitizeInput($_POST['confirm_delete']);
        
        if ($confirmDelete === 'DELETE') {
            // Delete related records first
            $conn->query("DELETE FROM work_images WHERE provider_id = $providerId");
            $conn->query("DELETE FROM reviews WHERE provider_id = $providerId");
            $conn->query("DELETE FROM password_resets WHERE provider_id = $providerId");
            // Delete provider account
            $deleteQuery = "DELETE FROM service_providers WHERE id = $providerId";
            if ($conn->query($deleteQuery)) {
                // Destroy session
                session_destroy();
                // Redirect to confirmation page
                header('Location: account_deleted.php');
                exit();
            } else {
                $error = "Failed to delete account. Please try again.";
            }
        } else {
            $error = "Please type DELETE to confirm account deletion.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard - KaamBuddy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>काम<span>Buddy</span></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search.php">Find Services</a></li>
                    <li><a href="logout.php" class="btn-secondary">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-header">
                <h2>Welcome, <?php echo htmlspecialchars($provider['name']); ?></h2>
                <p>Manage your service provider profile</p>
            </div>
            
            <?php if (!empty($success)): ?>
                <div class="success-box">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-box">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-tabs">
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="profile">Profile</button>
                    <button class="tab-btn" data-tab="portfolio">Portfolio</button>
                    <button class="tab-btn" data-tab="password">Password</button>
                    <button class="tab-btn" data-tab="account">Account</button>
                </div>
                
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane active" id="profile-tab">
                        <div class="dashboard-card">
                            <h3>Update Profile</h3>
                            <form action="provider_dashboard.php" method="POST" enctype="multipart/form-data">
                                <div class="profile-img-container">
                                    <label>Profile Photo</label>
                                    <div class="profile-img-preview">
                                        <?php if (!empty($provider['profile_image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($provider['profile_image_url']); ?>" alt="Profile Image">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/150?text=No+Image" alt="No Profile Image">
                                        <?php endif; ?>
                                    </div>
                                    <div class="file-upload">
                                        <input type="file" id="profile-image" name="profile_image" accept="image/*">
                                        <label for="profile-image">Change Photo</label>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">Full Name</label>
                                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($provider['name']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="location">Location</label>
                                        <select id="location" name="location" required>
                                            <option value="">Select your location</option>
                                            <option value="Mumbai" <?php echo ($provider['location'] === 'Mumbai') ? 'selected' : ''; ?>>Mumbai</option>
                                            <option value="Delhi" <?php echo ($provider['location'] === 'Delhi') ? 'selected' : ''; ?>>Delhi</option>
                                            <option value="Bangalore" <?php echo ($provider['location'] === 'Bangalore') ? 'selected' : ''; ?>>Bangalore</option>
                                            <option value="Pune" <?php echo ($provider['location'] === 'Pune') ? 'selected' : ''; ?>>Pune</option>
                                            <option value="Chennai" <?php echo ($provider['location'] === 'Chennai') ? 'selected' : ''; ?>>Chennai</option>
                                            <option value="Hyderabad" <?php echo ($provider['location'] === 'Hyderabad') ? 'selected' : ''; ?>>Hyderabad</option>
                                            <option value="Kolkata" <?php echo ($provider['location'] === 'Kolkata') ? 'selected' : ''; ?>>Kolkata</option>
                                            <option value="Ahmedabad" <?php echo ($provider['location'] === 'Ahmedabad') ? 'selected' : ''; ?>>Ahmedabad</option>
                                            <option value="Other" <?php echo ($provider['location'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row contact-info">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <div class="input-with-icon">
                                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($provider['phone']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="whatsapp">WhatsApp Number</label>
                                        <div class="input-with-icon">
                                            <input type="text" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($provider['whatsapp']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="service_description">Services Offered</label>
                                        <textarea id="service_description" name="service_description" required><?php echo htmlspecialchars($provider['service_description']); ?></textarea>
                                        <small>Describe the services you offer in detail (limited to 3 lines)</small>
                                    </div>
                                </div>
                                
                                <div class="form-footer">
                                    <button type="submit" name="update_profile" class="submit-btn">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Portfolio Tab -->
                    <div class="tab-pane" id="portfolio-tab">
                        <div class="dashboard-card">
                            <h3>Work Portfolio</h3>
                            
                            <div class="work-images-container">
                                <?php if (!empty($workImages)): ?>
                                    <div class="work-images-grid">
                                        <?php foreach ($workImages as $image): ?>
                                            <div class="work-image-item">
                                                <img src="<?php echo htmlspecialchars($image); ?>" alt="Work Sample">
                                                <form action="provider_dashboard.php" method="POST" class="delete-image-form">
                                                    <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($image); ?>">
                                                    <button type="submit" name="delete_work_image" class="delete-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-images-message">
                                        <p>You haven't uploaded any work samples yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <form action="provider_dashboard.php" method="POST" enctype="multipart/form-data" class="upload-images-form">
                                <div class="form-group">
                                    <label>Upload New Work Images</label>
                                    <div class="file-upload">
                                        <input type="file" id="work-images" name="work_images[]" accept="image/*" multiple>
                                        <label for="work-images">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Click to upload work photos</p>
                                            <small>Upload multiple images of your past work</small>
                                        </label>
                                    </div>
                                    <div id="work-images-preview" class="images-preview"></div>
                                </div>
                                
                                <div class="form-footer">
                                    <button type="submit" name="upload_work_images" class="submit-btn">Upload Images</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Password Tab -->
                    <div class="tab-pane" id="password-tab">
                        <div class="dashboard-card">
                            <h3>Change Password</h3>
                            <form action="provider_dashboard.php" method="POST">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required minlength="8">
                                    <small>Password must be at least 8 characters long</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                                </div>
                                
                                <div class="form-footer">
                                    <button type="submit" name="change_password" class="submit-btn">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Account Tab -->
                    <div class="tab-pane" id="account-tab">
                        <div class="dashboard-card">
                            <h3>Delete Account</h3>
                            <div class="warning-box">
                                <p><i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone!</p>
                                <p>Deleting your account will permanently remove all your data including profile, work images, and reviews.</p>
                            </div>
                            
                            <form action="provider_dashboard.php" method="POST" class="delete-account-form">
                                <div class="form-group">
                                    <label for="confirm_delete">Type DELETE to confirm</label>
                                    <input type="text" id="confirm_delete" name="confirm_delete" required>
                                </div>
                                
                                <div class="form-footer">
                                    <button type="submit" name="delete_account" class="delete-btn">Delete My Account</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>काम<span>Buddy</span></h2>
                    <p>Your Partner in Getting Things Done.</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="search.php">Find Services</a></li>
                        <li><a href="logout.php">Logout</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-envelope"></i>
                    <a href="mailto:KaamBuddy.info@gmail.com?subject=Enquiry from KaamBuddy.info@gmail.com"> KaamBuddy.info@gmail.com</a>
                    <p><i class="fas fa-phone"></i> 
                    <a href="tel:+919019304426">+91 9019304426</a>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> KaamBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Tab navigation
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and panes
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show corresponding tab pane
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
            
            // File upload preview
            const profileImageInput = document.getElementById('profile-image');
            if (profileImageInput) {
                profileImageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewImg = document.querySelector('.profile-img-preview img');
                            if (previewImg) {
                                previewImg.src = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Work images preview
            const workImagesInput = document.getElementById('work-images');
            const workImagesPreview = document.getElementById('work-images-preview');
            
            if (workImagesInput && workImagesPreview) {
                workImagesInput.addEventListener('change', function() {
                    workImagesPreview.innerHTML = '';
                    
                    for (const file of this.files) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'Work Preview';
                            workImagesPreview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html> 