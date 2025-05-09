<?php
include_once 'includes/functions.php';

// Get search parameters
$service = isset($_GET['service']) ? sanitizeInput($_GET['service']) : '';
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$city = isset($_GET['city']) ? sanitizeInput($_GET['city']) : '';
$area = isset($_GET['area']) ? sanitizeInput($_GET['area']) : '';
$pincode = isset($_GET['pincode']) ? sanitizeInput($_GET['pincode']) : '';

// Default empty array for results
$providers = [];

// Search for providers if parameters are set
if (!empty($service) || !empty($category)) {
    // Construct where clause
    $whereClause = [];
    
    if (!empty($service)) {
        $whereClause[] = "(sc.name LIKE '$service%' OR sp.name LIKE '%$service%' OR sp.custom_category LIKE '%$service%')";
    }
    
    if (!empty($category)) {
        $whereClause[] = "sc.name = '$category'";
    }
    
    // If we have coordinates, add distance calculation
    if ($lat !== null && $lng !== null) {
        $query = "SELECT sp.*, sc.name as category_name 
                  FROM service_providers sp 
                  LEFT JOIN service_categories sc ON sp.category_id = sc.id";
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $query .= " ORDER BY (
            6371 * acos(
                cos(radians($lat)) * 
                cos(radians(sp.latitude)) * 
                cos(radians(sp.longitude) - radians($lng)) + 
                sin(radians($lat)) * 
                sin(radians(sp.latitude))
            )
        ) ASC";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $providers[] = $row;
            }
        }
    } else {
        // If lat/lng is not provided, add to the where clause
        $locationWhere = [];
        if (!empty($city)) $locationWhere[] = "sp.city LIKE '%$city%'";
        if (!empty($area)) $locationWhere[] = "sp.area LIKE '%$area%'";
        if (!empty($pincode)) $locationWhere[] = "sp.pincode LIKE '%$pincode%'";
        if (!empty($locationWhere)) $whereClause[] = '(' . implode(' OR ', $locationWhere) . ')';
        
        // Base query
        $query = "SELECT sp.*, sc.name as category_name 
                  FROM service_providers sp 
                  LEFT JOIN service_categories sc ON sp.category_id = sc.id";
    
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
    }
    
        $query .= " ORDER BY sp.created_at DESC";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $providers[] = $row;
        }
    }
    }
}

// If this is an AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($providers);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Service Providers - KaamBuddy</title>
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

    <section class="search-results">
        <div class="container">
            <div class="search-filters">
                <form action="search.php" method="GET" class="filter-form">
                    <input type="text" id="service-search" name="service" placeholder="Search for services..." value="<?php echo htmlspecialchars($service); ?>">
                    <input type="text" id="city-search" name="city" placeholder="City" value="<?php echo isset($_GET['city']) ? htmlspecialchars($_GET['city']) : ''; ?>">
                    <input type="text" id="area-search" name="area" placeholder="Area" value="<?php echo isset($_GET['area']) ? htmlspecialchars($_GET['area']) : ''; ?>">
                    <input type="text" id="pincode-search" name="pincode" placeholder="Pincode" value="<?php echo isset($_GET['pincode']) ? htmlspecialchars($_GET['pincode']) : ''; ?>">
                    <button type="submit" class="btn-primary">Search</button>
                </form>
            </div>

            <?php if (!empty($service) || !empty($category)): ?>
                <div class="results-info">
                    <div class="results-count">
                        <?php if (count($providers) > 0): ?>
                            Found <?php echo count($providers); ?> service provider(s) 
                            <?php if (!empty($service)): ?>
                                for "<?php echo htmlspecialchars($service); ?>"
                            <?php endif; ?>
                            <?php if (!empty($category)): ?>
                                in category "<?php echo htmlspecialchars($category); ?>"
                            <?php endif; ?>
                        <?php else: ?>
                            No service providers found for your search. Try different keywords or category.
                        <?php endif; ?>
                    </div>
                </div>
            
                <?php if (count($providers) > 0): ?>
                    <div class="results-grid">
                        <?php foreach ($providers as $provider): ?>
                            <div class="provider-card">
                                <div class="card-image">
                                    <?php if (!empty($provider['profile_image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($provider['profile_image_url']); ?>" alt="<?php echo htmlspecialchars($provider['name']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/300x200?text=No+Image" alt="No Image">
                                    <?php endif; ?>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-name"><?php echo htmlspecialchars($provider['name']); ?></h3>
                                    <div class="card-category">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($provider['custom_category'] ? $provider['custom_category'] : $provider['category_name']); ?>
                                    </div>
                                    <div class="card-location">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($provider['address']); ?>
                                    </div>
                                    <?php if ($lat !== null && $lng !== null): ?>
                                        <div class="card-distance">
                                            <i class="fas fa-route"></i> 
                                            <?php
                                            $distance = calculateDistance(
                                                $lat, 
                                                $lng, 
                                                $provider['latitude'], 
                                                $provider['longitude']
                                            );
                                            echo number_format($distance, 1) . ' km away';
                                            ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="card-rating">
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
                                    <div class="card-footer">
                                        <a href="provider.php?id=<?php echo $provider['id']; ?>" class="btn-secondary">View Profile</a>
                                        <div class="card-contact">
                                            <a href="https://wa.me/<?php echo urlencode(preg_replace('/[^0-9]/', '', $provider['whatsapp'])); ?>" class="whatsapp" target="_blank" title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                            <a href="tel:<?php echo urlencode($provider['phone']); ?>" class="call" title="Call">
                                                <i class="fas fa-phone-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="search-prompt">
                    <h2>Find Skilled Service Providers Near You</h2>
                    <p>Use the search bar above to find service providers in your area.</p>
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
                <p>&copy; 2025 Kaambuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/location.js"></script>
    <style>
        .card-category {
            margin: 5px 0;
            color: var(--primary-color);
        }
        
        .card-distance {
            margin: 5px 0;
            color: var(--gray);
        }
        
        .search-prompt {
            text-align: center;
            padding: 50px 0;
        }
    </style>
</body>
</html> 