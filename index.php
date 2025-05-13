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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>कामBuddy - Your Partner in Getting Things Done.</title>
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

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Find Skilled Service Providers Near You</h1>
                <p>Connect with trusted professionals for all your needs - from plumbing to tutoring.</p>
                
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="user-location">Detecting your location...</span>
                </div>
                
                <div class="search-container">
                    <form action="search.php" method="GET">
                        <div class="search-box">
                            <input type="text" id="service-search" name="service" placeholder="What service do you need? (e.g. Plumber, Electrician)" required>
                            <button type="submit"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="service-categories">
        <div class="container">
            <h2>Popular Service Categories</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="search.php?category=<?php echo urlencode($category['name']); ?>" class="category-card">
                        <i class="fas <?php echo htmlspecialchars($category['icon']); ?>"></i>
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div id="search-results" class="search-results-container"></div>

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

    <script src="js/location.js"></script>
    <style>
        .location-info {
            margin: 20px 0;
            font-size: 1.1em;
            color: var(--dark);
        }
        
        .location-info i {
            color: var(--primary-color);
            margin-right: 8px;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .category-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            text-decoration: none;
            color: var(--dark);
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .category-card i {
            font-size: 2em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .category-card h3 {
            margin: 10px 0;
            color: var(--dark);
        }
        
        .category-card p {
            font-size: 0.9em;
            color: var(--gray);
        }
        
        .search-results-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .search-result-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .search-result-item h3 {
            margin: 0 0 10px 0;
            color: var(--dark);
        }
        
        .search-result-item p {
            margin: 5px 0;
            color: var(--gray);
        }
    </style>
</body>
</html> 