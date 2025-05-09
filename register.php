<?php
include_once 'includes/functions.php';

// Fetch service categories
$categories = [];
$query = "SELECT * FROM service_categories ORDER BY name";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Ensure upload directories exist
ensureUploadDirectoriesExist();

$success = false;
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = sanitizeInput($_POST['name']);
    $phone = sanitizeInput($_POST['phone']);
    $whatsapp = sanitizeInput($_POST['whatsapp']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $category_id = $_POST['category_id'];
    $other_category = isset($_POST['other_category']) ? sanitizeInput($_POST['other_category']) : '';
    $service_description = isset($_POST['service_description']) ? sanitizeInput($_POST['service_description']) : '';
    $address = sanitizeInput($_POST['address']);
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;
    $state = isset($_POST['state']) ? sanitizeInput($_POST['state']) : '';
    $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
    $district = isset($_POST['district']) ? sanitizeInput($_POST['district']) : '';
    $area = isset($_POST['area']) ? sanitizeInput($_POST['area']) : '';
    $pincode = isset($_POST['pincode']) ? sanitizeInput($_POST['pincode']) : '';
    
    // If 'Other' is selected, set category_id to 0 and use other_category
    $final_category_id = is_numeric($category_id) ? intval($category_id) : 0;
    $final_category_name = ($category_id === 'other' && $other_category) ? $other_category : null;
    
    // Validate required fields
    if (empty($name) || empty($phone) || empty($whatsapp) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } elseif (getProviderByEmail($email)) {
        $error = "Email address is already registered!";
    } else {
        // Handle profile image upload
        $profileImageUrl = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profileImageUrl = $upload_path;
            }
        }
        
        if (empty($error)) {
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Add service_description column if it doesn't exist
            $conn->query("ALTER TABLE service_providers ADD COLUMN service_description TEXT NULL");
            $conn->query("ALTER TABLE service_providers ADD COLUMN custom_category VARCHAR(100) NULL");
            
            // Insert provider data into database
            $query = "INSERT INTO service_providers (name, phone, whatsapp, email, password_hash, category_id, custom_category, address, latitude, longitude, profile_image_url, service_description, state, city, district, area, pincode, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param(
                "ssssisssddsssssss",
                $name, $phone, $whatsapp, $email, $passwordHash, $final_category_id, $final_category_name, $address, $latitude, $longitude, $profileImageUrl, $service_description, $state, $city, $district, $area, $pincode
            );
            if ($stmt->execute()) {
                $providerId = $conn->insert_id;
                
                // Handle work images upload
                if (isset($_FILES['work_images']) && !empty($_FILES['work_images']['name'][0])) {
                    $workImagesCount = count($_FILES['work_images']['name']);
                    
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
                        }
                    }
                }
                
                $success = true;
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Service Provider - KaamBuddy</title>
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
                    <li><a href="provider_login.php">Provider Login</a></li>
                    <li><a href="register.php" class="btn-primary">Register as Provider</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="register-section">
        <div class="container">
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h2>Registration Successful!</h2>
                    <p>Your profile has been created. Customers can now find your services.</p>
                    <a href="index.php" class="btn-primary">Back to Home</a>
                </div>
            <?php else: ?>
                <div class="registration-form">
                    <div class="form-title">
                        <h2>Register as a Service Provider</h2>
                        <p>Create your profile and let customers find your services</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-box">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="registration-form" action="register.php" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-row">
                            <div class="form-group">
                                    <label for="category_id">Service Category *</label>
                                    <select id="category_id" name="category_id" required onchange="toggleOtherCategory(this.value)">
                                        <option value="">Select your service category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="other">Other</option>
                                </select>
                                </div>
                                <div class="form-group" id="other-category-group" style="display:none;">
                                    <label for="other_category">Other Service Category</label>
                                    <input type="text" id="other_category" name="other_category" maxlength="100" placeholder="Enter your service category">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="service_description">Service Description (max 3 lines)</label>
                            <textarea id="service_description" name="service_description" rows="3" maxlength="300" placeholder="Describe your services (max 3 lines)" required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                            <div class="form-group">
                                <label for="whatsapp">WhatsApp Number *</label>
                                <input type="tel" id="whatsapp" name="whatsapp" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required minlength="8">
                                <small>Password must be at least 8 characters long</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Full Address *</label>
                            <textarea id="address" name="address" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="state">State *</label>
                                <input type="text" id="state" name="state">
                            </div>
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city">
                            </div>
                            <div class="form-group">
                                <label for="district">District *</label>
                                <input type="text" id="district" name="district">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="area">Area *</label>
                                <input type="text" id="area" name="area">
                            </div>
                            <div class="form-group">
                                <label for="pincode">Pincode *</label>
                                <input type="text" id="pincode" name="pincode">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="button" id="use-location" class="btn-secondary">
                                <i class="fas fa-map-marker-alt"></i> Use My Location
                            </button>
                        </div>
                        
                            <div class="form-group">
                                <label>Profile Photo</label>
                                <div class="file-upload">
                                <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                <label for="profile_image">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload profile photo</p>
                                        <small>JPEG, PNG or GIF (Max. 5MB)</small>
                                    </label>
                                </div>
                                <div id="profile-image-preview" class="image-preview"></div>
                            </div>
                            
                            <div class="form-group">
                                <label>Work Portfolio Images</label>
                                <div class="file-upload">
                                    <input type="file" id="work-images" name="work_images[]" accept="image/*" multiple>
                                    <label for="work-images">
                                        <i class="fas fa-images"></i>
                                        <p>Click to upload work photos</p>
                                        <small>Upload multiple images of your past work</small>
                                    </label>
                                </div>
                                <div id="work-images-preview" class="images-preview"></div>
                        </div>
                        
                        <div class="form-footer">
                            <button type="submit" class="submit-btn">Register Now</button>
                            <p class="form-note">By registering, you agree to our terms and conditions.</p>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
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
                        <li><a href="register.php">Register as Provider</a></li>
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
                <p>&copy; 2025 KaamBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
    <script src="js/location.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const useLocationBtn = document.getElementById('use-location');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const addressInput = document.getElementById('address');
        const stateInput = document.getElementById('state');
        const cityInput = document.getElementById('city');
        const districtInput = document.getElementById('district');
        const areaInput = document.getElementById('area');
        const pincodeInput = document.getElementById('pincode');

        if (useLocationBtn) {
            useLocationBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        latitudeInput.value = position.coords.latitude;
                        longitudeInput.value = position.coords.longitude;

                        // Reverse geocode to get address
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.display_name) {
                                    addressInput.value = data.display_name;
                                } else {
                                    addressInput.value = '';
                                }
                                // Try to auto-fill state, city, district, area, pincode
                                if (data.address) {
                                    stateInput.value = data.address.state || '';
                                    cityInput.value = data.address.city || data.address.town || data.address.village || '';
                                    districtInput.value = data.address.county || data.address.state_district || '';
                                    areaInput.value = data.address.suburb || data.address.neighbourhood || data.address.hamlet || '';
                                    pincodeInput.value = data.address.postcode || '';
                                }
                            })
                            .catch(() => {
                                addressInput.value = '';
                            });
                    }, function(error) {
                        alert('Unable to retrieve your location.');
                    });
                } else {
                    alert('Geolocation is not supported by your browser.');
                }
            });
        }

        // Make manual address fields required only if lat/lng is not filled
        const form = document.getElementById('registration-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!latitudeInput.value || !longitudeInput.value) {
                    // Require manual fields
                    stateInput.required = true;
                    cityInput.required = true;
                    districtInput.required = true;
                    areaInput.required = true;
                    pincodeInput.required = true;
                } else {
                    // Not required if location is used
                    stateInput.required = false;
                    cityInput.required = false;
                    districtInput.required = false;
                    areaInput.required = false;
                    pincodeInput.required = false;
                }
            });
        }
    });
    </script>
    <style>
        .success-message {
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin: 50px auto;
        }
        
        .success-message i {
            font-size: 60px;
            color: var(--success);
            margin-bottom: 20px;
        }
        
        .error-box {
            background-color: #ffebee;
            border-left: 4px solid var(--danger);
            color: var(--danger);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .image-preview {
            margin-top: 15px;
            display: none;
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .images-preview {
            margin-top: 15px;
            display: none;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .preview-item {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .register-section {
            padding: 60px 0;
            background-color: var(--light);
        }
        
        input[type="file"] {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
        
        .file-upload label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 20px;
            height: 100%;
            width: 100%;
        }
    </style>
    <script>
    function toggleOtherCategory(val) {
        var group = document.getElementById('other-category-group');
        if (val === 'other') {
            group.style.display = 'block';
            document.getElementById('other_category').required = true;
        } else {
            group.style.display = 'none';
            document.getElementById('other_category').required = false;
        }
    }
    </script>
</body>
</html> 