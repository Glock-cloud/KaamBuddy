<?php
include_once 'includes/functions.php';

// Ensure upload directories exist
ensureUploadDirectoriesExist();

// Get provider ID from URL
$providerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables
$provider = null;
$workImages = [];
$reviews = [];
$reviewSuccess = false;
$reviewError = '';

// Fetch provider data if ID is valid
if ($providerId > 0) {
    $query = "SELECT * FROM service_providers WHERE id = $providerId";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $provider = $result->fetch_assoc();
        
        // Get work images
        $workImages = getWorkImages($providerId);
        
        /* Debug work images - uncomment if needed for troubleshooting
        echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;'>";
        echo "<h3>Work Images Debug Info</h3>";
        echo "<p>Provider ID: $providerId</p>";
        echo "<p>Work Images Count: " . count($workImages) . "</p>";
        echo "<p>Work Images Data:</p>";
        echo "<pre>";
        print_r($workImages);
        echo "</pre>";

        // Check work_images table
        echo "<p>Database entries in work_images table:</p>";
        $checkQuery = "SELECT * FROM work_images WHERE provider_id = $providerId";
        $checkResult = $conn->query($checkQuery);
        if ($checkResult && $checkResult->num_rows > 0) {
            echo "<ul>";
            while ($row = $checkResult->fetch_assoc()) {
                echo "<li>ID: {$row['id']}, Image URL: {$row['image_url']}, File exists: " . (file_exists($row['image_url']) ? "Yes" : "No") . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No work images found in database for this provider.</p>";
        }
        echo "</div>";
        */
        
        // Ensure reviews table exists
        $reviewsTableQuery = "SHOW TABLES LIKE 'reviews'";
        $reviewsTableExists = $conn->query($reviewsTableQuery)->num_rows > 0;
        
        if (!$reviewsTableExists) {
            // Create reviews table
            $createReviewsTable = "CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                provider_id INT NOT NULL,
                rating INT NOT NULL,
                comment TEXT,
                review_image VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (provider_id) REFERENCES service_providers(id) ON DELETE CASCADE
            )";
            $conn->query($createReviewsTable);
        }
        
        // Get reviews
        $reviewQuery = "SELECT * FROM reviews WHERE provider_id = $providerId ORDER BY created_at DESC";
        $reviewResult = $conn->query($reviewQuery);
        
        if ($reviewResult && $reviewResult->num_rows > 0) {
            while ($row = $reviewResult->fetch_assoc()) {
                $reviews[] = $row;
            }
        }
    }
}

// Process review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    // Sanitize inputs
    $rating = isset($_POST['rating']) ? (int)sanitizeInput($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? sanitizeInput($_POST['comment']) : '';
    
    // Validate input
    if ($rating < 1 || $rating > 5) {
        $reviewError = "Please select a rating between 1 and 5 stars.";
    } else {
        // Handle review image upload
        $reviewImage = '';
        if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] === 0) {
            $reviewImage = uploadImage($_FILES['review_image'], 'uploads/reviews/');
            if (!$reviewImage) {
                $reviewError = "Failed to upload review image. Please try again.";
            }
        }
        
        if (empty($reviewError)) {
            // Insert review into database
            $insertQuery = "INSERT INTO reviews (provider_id, rating, comment, review_image, created_at) 
                           VALUES ($providerId, $rating, '$comment', '$reviewImage', NOW())";
            
            if ($conn->query($insertQuery)) {
                $reviewSuccess = true;
                
                // Refresh reviews list
                $reviews = [];
                $reviewQuery = "SELECT * FROM reviews WHERE provider_id = $providerId ORDER BY created_at DESC";
                $reviewResult = $conn->query($reviewQuery);
                
                if ($reviewResult && $reviewResult->num_rows > 0) {
                    while ($row = $reviewResult->fetch_assoc()) {
                        $reviews[] = $row;
                    }
                }
            } else {
                $reviewError = "Failed to submit review: " . $conn->error;
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
    <title><?php echo $provider ? htmlspecialchars($provider['name']) : 'Provider Not Found'; ?> - KaamBuddy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- Add lightbox styles -->
    <style>
        .lightbox {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .lightbox.active {
            display: flex;
        }
        
        .lightbox-img {
            max-width: 90%;
            max-height: 80vh;
            object-fit: contain;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .lightbox-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 15px;
        }
        
        .lightbox-nav button {
            background-color: transparent;
            color: white;
            border: none;
            font-size: 24px;
            margin: 0 15px;
            cursor: pointer;
            padding: 5px 15px;
            transition: all 0.3s ease;
        }
        
        .lightbox-nav button:hover {
            color: var(--primary-color);
        }
        
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            color: white;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        
        .gallery-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            background-color: #fff;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .work-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .gallery-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }
    </style>
</head>
<body>
    <?php include_once 'includes/header.php'; ?>

    <section class="provider-profile">
        <div class="container">
            <?php if ($provider): ?>
                <div class="profile-container">
                    <div class="profile-sidebar">
                        <div class="profile-card">
                            <div class="profile-header">
                                <div class="profile-image">
                                    <?php if (!empty($provider['profile_image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($provider['profile_image_url']); ?>" alt="<?php echo htmlspecialchars($provider['name']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/300x300?text=No+Image" alt="No Profile Image">
                                    <?php endif; ?>
                                </div>
                                <h2 class="profile-name"><?php echo htmlspecialchars($provider['name']); ?></h2>
                                <div class="profile-location">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php 
                                        $location_parts = [];
                                        if (!empty($provider['area'])) $location_parts[] = htmlspecialchars($provider['area']);
                                        if (!empty($provider['city'])) $location_parts[] = htmlspecialchars($provider['city']);
                                        if (!empty($provider['state'])) $location_parts[] = htmlspecialchars($provider['state']);
                                        
                                        if (!empty($location_parts)) {
                                            echo implode(', ', $location_parts);
                                        } else {
                                            echo htmlspecialchars($provider['address']);
                                        }
                                    ?>
                                </div>
                                
                                <div class="profile-rating">
                                    <div class="stars">
                                        <?php
                                        $avgRating = getAverageRating($provider['id']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $avgRating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } elseif ($i <= $avgRating + 0.5) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="review-count">(<?php echo getReviewCount($provider['id']); ?> reviews)</span>
                                </div>
                            </div>
                            
                            <div class="contact-buttons">
                                <a href="https://wa.me/<?php echo urlencode(preg_replace('/[^0-9]/', '', $provider['whatsapp'])); ?>" class="whatsapp-btn" target="_blank">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <a href="tel:<?php echo urlencode($provider['phone']); ?>" class="call-btn">
                                    <i class="fas fa-phone-alt"></i> Call Now
                                </a>
                            </div>
                            
                            <div class="profile-section">
                                <h3>Services Offered</h3>
                                <div class="services-list">
                                    <?php if (!empty($provider['service_description'])): ?>
                                        <div><?php echo nl2br(htmlspecialchars($provider['service_description'])); ?></div>
                                    <?php else: ?>
                                    <?php
                                    $services = explode("\n", $provider['services']);
                                    foreach ($services as $service) {
                                        $service = trim($service);
                                        if (!empty($service)) {
                                            echo "<div>$service</div>";
                                        }
                                    }
                                    ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-content">
                        <?php if (!empty($workImages)): ?>
                            <div class="profile-card">
                                <h3>Work gallery</h3>
                                <div class="work-gallery">
                                    <?php foreach ($workImages as $index => $image): ?>
                                        <div class="gallery-item" onclick="openLightbox(<?php echo $index; ?>)">
                                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Work Sample">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="profile-card">
                            <div class="profile-section">
                                <h3>Reviews & Ratings</h3>
                                
                                <?php if ($reviewSuccess): ?>
                                    <div class="success-message review-success">
                                        <i class="fas fa-check-circle"></i> Your review has been submitted successfully!
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($reviews)): ?>
                                    <div class="reviews-list">
                                        <?php foreach ($reviews as $review): ?>
                                            <div class="review-card">
                                                <div class="review-header">
                                                    <div class="review-rating">
                                                        <?php
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $review['rating']) {
                                                                echo '<i class="fas fa-star"></i>';
                                                            } else {
                                                                echo '<i class="far fa-star"></i>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="review-date">
                                                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($review['comment'])): ?>
                                                    <div class="review-text">
                                                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($review['review_image'])): ?>
                                                    <div class="review-image">
                                                        <img src="<?php echo htmlspecialchars($review['review_image']); ?>" alt="Review Image">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-reviews">
                                        <p>No reviews yet. Be the first to review this service provider!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="profile-card">
                            <div class="profile-section">
                                <h3>Write a Review</h3>
                                
                                <?php if (!empty($reviewError)): ?>
                                    <div class="error-box">
                                        <?php echo $reviewError; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form class="review-form" action="provider.php?id=<?php echo $providerId; ?>" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Rating</label>
                                        <div class="rating-input">
                                            <i class="far fa-star" data-value="1"></i>
                                            <i class="far fa-star" data-value="2"></i>
                                            <i class="far fa-star" data-value="3"></i>
                                            <i class="far fa-star" data-value="4"></i>
                                            <i class="far fa-star" data-value="5"></i>
                                        </div>
                                        <input type="hidden" name="rating" value="0" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="comment">Your Review (Optional)</label>
                                        <textarea id="comment" name="comment" placeholder="Share your experience with this service provider..."></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Add Photo (Optional)</label>
                                        <input type="file" id="review-image" name="review_image" accept="image/*" class="file-input">
                                        <div id="review-image-preview" style="display: none; margin-top: 10px;"></div>
                                    </div>
                                    
                                    <button type="submit" name="submit_review" class="btn-primary">Submit Review</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <h2>Provider Not Found</h2>
                    <p>The service provider you are looking for does not exist or has been removed.</p>
                    <a href="search.php" class="btn-primary">Search for Providers</a>
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

    <!-- Lightbox for image gallery -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <img src="" alt="Work Sample" class="lightbox-img" id="lightbox-img">
        <div class="lightbox-nav">
            <button onclick="prevImage()"><i class="fas fa-arrow-left"></i> Previous</button>
            <button onclick="nextImage()">Next <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const navMenu = document.getElementById('nav-menu');
            
            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                    mobileMenuToggle.innerHTML = navMenu.classList.contains('active') ? 
                        '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
                });
            }
            
            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (navMenu && navMenu.classList.contains('active') && 
                    !e.target.closest('nav') && 
                    !e.target.closest('.mobile-menu-btn')) {
                    navMenu.classList.remove('active');
                    if (mobileMenuToggle) {
                        mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }
            });
        });
        
        // Lightbox functionality
        let currentImageIndex = 0;
        const workImages = <?php echo json_encode($workImages); ?>;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        
        function openLightbox(index) {
            currentImageIndex = index;
            lightboxImg.src = workImages[index];
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
        
        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
        }
        
        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % workImages.length;
            lightboxImg.src = workImages[currentImageIndex];
        }
        
        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + workImages.length) % workImages.length;
            lightboxImg.src = workImages[currentImageIndex];
        }
        
        // Close lightbox with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.classList.contains('active')) {
                closeLightbox();
            } else if (e.key === 'ArrowRight' && lightbox.classList.contains('active')) {
                nextImage();
            } else if (e.key === 'ArrowLeft' && lightbox.classList.contains('active')) {
                prevImage();
            }
        });
        
        // Click outside image to close
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
        
        // Star rating functionality
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating-input i');
            const ratingInput = document.querySelector('input[name="rating"]');
            
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.getAttribute('data-value'));
                    highlightStars(rating);
                });
                
                star.addEventListener('mouseout', function() {
                    const currentRating = parseInt(ratingInput.value);
                    highlightStars(currentRating);
                });
                
                star.addEventListener('click', function() {
                    const rating = parseInt(this.getAttribute('data-value'));
                    ratingInput.value = rating;
                    highlightStars(rating);
                });
            });
            
            function highlightStars(rating) {
                stars.forEach(star => {
                    const starValue = parseInt(star.getAttribute('data-value'));
                    if (starValue <= rating) {
                        star.className = 'fas fa-star';
                    } else {
                        star.className = 'far fa-star';
                    }
                });
            }
            
            // Review image preview
            const reviewImageInput = document.getElementById('review-image');
            const reviewImagePreview = document.getElementById('review-image-preview');
            
            if (reviewImageInput && reviewImagePreview) {
                reviewImageInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            reviewImagePreview.innerHTML = `<img src="${e.target.result}" alt="Review Image Preview">`;
                            reviewImagePreview.style.display = 'block';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        });
    </script>
</body>
</html> 