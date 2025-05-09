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
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'distance'; // Default sort by distance if coordinates are available

// Default empty array for results
$providers = [];

// Search for providers if parameters are set
if (!empty($service) || !empty($category)) {
    // Construct where clause
    $whereClause = [];
    
    if (!empty($service)) {
        $whereClause[] = "(sc.name LIKE '$service%' OR sp.name LIKE '%$service%' OR sp.custom_category LIKE '%$service%' OR sp.service_description LIKE '%$service%')";
    }
    
    if (!empty($category)) {
        $whereClause[] = "sc.name = '$category'";
    }
    
    // Location filters
    $locationWhere = [];
    if (!empty($city)) $locationWhere[] = "sp.city LIKE '%$city%'";
    if (!empty($area)) $locationWhere[] = "sp.area LIKE '%$area%'";
    if (!empty($pincode)) $locationWhere[] = "sp.pincode LIKE '%$pincode%'";
    if (!empty($locationWhere)) $whereClause[] = '(' . implode(' OR ', $locationWhere) . ')';
    
    // Base query
    $query = "SELECT sp.*, sc.name as category_name,
              (SELECT AVG(rating) FROM reviews WHERE provider_id = sp.id) as avg_rating,
              (SELECT COUNT(*) FROM reviews WHERE provider_id = sp.id) as review_count";
              
    // If we have coordinates, add distance calculation
    if ($lat !== null && $lng !== null) {
        $query .= ", (
            6371 * acos(
                cos(radians($lat)) * 
                cos(radians(sp.latitude)) * 
                cos(radians(sp.longitude) - radians($lng)) + 
                sin(radians($lat)) * 
                sin(radians(sp.latitude))
            )
        ) as distance";
    } else {
        $query .= ", NULL as distance";
    }
    
    $query .= " FROM service_providers sp 
               LEFT JOIN service_categories sc ON sp.category_id = sc.id";
    
    if (!empty($whereClause)) {
        $query .= " WHERE " . implode(' AND ', $whereClause);
    }
    
    // Sort order based on user selection
    switch ($sort) {
        case 'rating':
            $query .= " ORDER BY avg_rating DESC NULLS LAST, review_count DESC";
            break;
        case 'distance':
            if ($lat !== null && $lng !== null) {
                $query .= " ORDER BY distance ASC";
            } else {
                $query .= " ORDER BY sp.created_at DESC";
            }
            break;
        case 'recent':
        default:
            $query .= " ORDER BY sp.created_at DESC";
            break;
    }
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Add formatted distance if available
            if ($lat !== null && $lng !== null && isset($row['distance'])) {
                $row['formatted_distance'] = number_format($row['distance'], 1) . ' km';
            } else {
                $row['formatted_distance'] = 'Distance unknown';
            }
            
            // Format rating for display
            $row['rating_display'] = isset($row['avg_rating']) ? number_format($row['avg_rating'], 1) : 'No ratings';
            
            $providers[] = $row;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include_once 'includes/header.php'; ?>

    <section class="search-results">
        <div class="container">
            <div class="search-filters">
                <form action="search.php" method="GET" class="filter-form">
                    <div class="search-main-row">
                        <input type="text" id="service-search" name="service" placeholder="Search for services..." value="<?php echo htmlspecialchars($service); ?>">
                        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                    </div>
                    
                    <!-- Advanced Filters Toggle -->
                    <div class="advanced-filters-toggle">
                        <a href="#" id="show-filters"><i class="fas fa-filter"></i> Advanced Filters</a>
                    </div>
                    
                    <div class="advanced-filters" id="advanced-filters">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="city-search"><i class="fas fa-city"></i> City</label>
                                <input type="text" id="city-search" name="city" placeholder="Enter city" value="<?php echo isset($_GET['city']) ? htmlspecialchars($_GET['city']) : ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label for="area-search"><i class="fas fa-map-marker-alt"></i> Area</label>
                                <input type="text" id="area-search" name="area" placeholder="Enter area/locality" value="<?php echo isset($_GET['area']) ? htmlspecialchars($_GET['area']) : ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label for="pincode-search"><i class="fas fa-map-pin"></i> Pincode</label>
                                <input type="text" id="pincode-search" name="pincode" placeholder="Enter pincode" value="<?php echo isset($_GET['pincode']) ? htmlspecialchars($_GET['pincode']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="sort-by"><i class="fas fa-sort"></i> Sort By</label>
                                <select id="sort-by" name="sort">
                                    <option value="distance" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'distance') ? 'selected' : ''; ?>>Distance (Nearest First)</option>
                                    <option value="rating" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'rating') ? 'selected' : ''; ?>>Rating (Highest First)</option>
                                    <option value="recent" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'recent') ? 'selected' : ''; ?>>Recently Added</option>
                                </select>
                            </div>
                            
                            <!-- Hidden location inputs populated by JavaScript -->
                            <input type="hidden" id="lat" name="lat" value="<?php echo $lat; ?>">
                            <input type="hidden" id="lng" name="lng" value="<?php echo $lng; ?>">
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn-primary">Apply Filters</button>
                            <button type="button" class="btn-secondary" id="reset-filters">Reset</button>
                        </div>
                    </div>
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
                                            <?php echo htmlspecialchars($provider['formatted_distance']); ?>
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
                                        <span class="review-count">(<?php echo htmlspecialchars($provider['review_count']); ?> reviews)</span>
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
            
            // Advanced filters toggle
            const showFiltersBtn = document.getElementById('show-filters');
            const advancedFilters = document.getElementById('advanced-filters');
            
            if (showFiltersBtn && advancedFilters) {
                showFiltersBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    advancedFilters.classList.toggle('active');
                    showFiltersBtn.innerHTML = advancedFilters.classList.contains('active') ? 
                        '<i class="fas fa-times"></i> Hide Filters' : '<i class="fas fa-filter"></i> Advanced Filters';
                });
            }
            
            // Reset filters
            const resetBtn = document.getElementById('reset-filters');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    const inputs = document.querySelectorAll('.filter-group input, .filter-group select');
                    inputs.forEach(input => {
                        if (input.type !== 'hidden') {
                            input.value = '';
                        }
                    });
                    // Keep the service search value
                    document.getElementById('service-search').value = "<?php echo htmlspecialchars($service); ?>";
                });
            }
        });
    </script>
    <style>
        /* Search Filters Styles */
        .search-filters {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .search-main-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .search-main-row input {
            flex: 1;
            min-width: 200px;
            padding: 12px 15px;
            border-radius: 5px;
            border: 1px solid var(--light-gray);
            font-size: 16px;
        }
        
        .advanced-filters-toggle {
            text-align: center;
            margin: 10px 0;
        }
        
        .advanced-filters-toggle a {
            color: var(--secondary-color);
            font-size: 14px;
            text-decoration: none;
        }
        
        .advanced-filters {
            display: none;
            padding-top: 15px;
            border-top: 1px solid var(--light-gray);
        }
        
        .advanced-filters.active {
            display: block;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--light-gray);
            font-size: 14px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
        }
        
        .filter-actions button {
            padding: 10px 20px;
            font-size: 14px;
        }
        
        .btn-secondary {
            background-color: #e0e0e0;
            color: var(--dark);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #d0d0d0;
        }
        
        @media (max-width: 768px) {
            .filter-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-main-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-actions {
                flex-direction: column;
            }
        }
        
        /* Existing styles */
        .card-category {
            margin: 5px 0;
            color: var(--gray);
        }
        
        .card-category i {
            color: var(--primary-color);
            margin-right: 5px;
        }
        
        .card-distance {
            margin: 5px 0;
            color: var(--gray);
        }
        
        .card-distance i {
            color: var(--secondary-color);
            margin-right: 5px;
        }
        
        .search-prompt {
            text-align: center;
            padding: 40px 0;
        }
        
        .search-prompt h2 {
            color: var(--dark);
            margin-bottom: 15px;
        }
    </style>
</body>
</html> 