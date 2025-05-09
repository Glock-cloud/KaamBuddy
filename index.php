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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include_once 'includes/header.php'; ?>

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
                        <div class="popular-services">
                            <span>Popular searches:</span>
                            <a href="search.php?service=Plumber">Plumber</a>
                            <a href="search.php?service=Electrician">Electrician</a>
                            <a href="search.php?service=Carpenter">Carpenter</a>
                            <a href="search.php?service=Tutor">Tutor</a>
                            <a href="search.php?service=Painter">Painter</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Service Providers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Service Categories</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Happy Clients</div>
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

    <section class="faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How does KaamBuddy work?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>KaamBuddy connects skilled service providers with customers who need their services. Simply search for the type of service you need, browse through available providers, view their profiles and work samples, and contact them directly.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Is KaamBuddy free to use?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, KaamBuddy is completely free for customers looking to find service providers. Service providers can register and create a profile at no cost.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How do I contact a service provider?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Each provider profile has direct contact options - you can call them or message them on WhatsApp with a single tap. No intermediary or waiting for responses - connect directly with the service provider.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How do I register as a service provider?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Click on the "Register as Provider" button in the navigation menu. Fill out your details, upload a profile photo and work samples, and submit the form. Your profile will be immediately visible to potential customers.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How are service providers verified?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>We rely on customer reviews and ratings to build trust in our platform. After hiring a service provider, customers can leave reviews and ratings based on their experience. This helps future customers make informed decisions.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What areas does KaamBuddy cover?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>KaamBuddy currently operates in select cities across India. We're continuously expanding our coverage to new areas. Our location detection helps you find service providers in your vicinity.</p>
                    </div>
                </div>
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

    <!-- Feedback Button -->
    <button class="feedback-btn" id="feedback-btn">
        <i class="fas fa-comment-alt"></i> Feedback
    </button>
    
    <!-- Feedback Modal -->
    <div class="feedback-modal" id="feedback-modal">
        <div class="feedback-form">
            <button class="close-modal" id="close-modal">&times;</button>
            <div id="feedback-form-content">
                <h3>We'd Love Your Feedback</h3>
                <div class="feedback-type">
                    <input type="radio" name="feedback-type" id="suggestion" value="suggestion" checked>
                    <label for="suggestion">Suggestion</label>
                    
                    <input type="radio" name="feedback-type" id="bug" value="bug">
                    <label for="bug">Report Issue</label>
                    
                    <input type="radio" name="feedback-type" id="compliment" value="compliment">
                    <label for="compliment">Compliment</label>
                </div>
                <textarea id="feedback-text" placeholder="Please share your feedback with us..."></textarea>
                <button id="submit-feedback">Submit Feedback</button>
            </div>
            <div id="feedback-success" class="feedback-success" style="display:none;">
                <i class="fas fa-check-circle"></i>
                <h3>Thank You!</h3>
                <p>Your feedback has been submitted successfully. We appreciate your input!</p>
                <button id="close-success" class="btn-primary">Close</button>
            </div>
        </div>
    </div>

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
            
            // Feedback Modal
            const feedbackBtn = document.getElementById('feedback-btn');
            const feedbackModal = document.getElementById('feedback-modal');
            const closeModal = document.getElementById('close-modal');
            const submitFeedback = document.getElementById('submit-feedback');
            const feedbackFormContent = document.getElementById('feedback-form-content');
            const feedbackSuccess = document.getElementById('feedback-success');
            const closeSuccess = document.getElementById('close-success');
            
            if (feedbackBtn && feedbackModal) {
                feedbackBtn.addEventListener('click', () => {
                    feedbackModal.classList.add('active');
                });
                
                closeModal.addEventListener('click', () => {
                    feedbackModal.classList.remove('active');
                });
                
                // Close modal when clicking outside
                feedbackModal.addEventListener('click', (e) => {
                    if (e.target === feedbackModal) {
                        feedbackModal.classList.remove('active');
                    }
                });
                
                // Submit feedback (just show success message for now)
                submitFeedback.addEventListener('click', () => {
                    const feedbackText = document.getElementById('feedback-text').value;
                    const feedbackType = document.querySelector('input[name="feedback-type"]:checked').value;
                    
                    if (feedbackText.trim().length < 5) {
                        alert('Please enter more detailed feedback');
                        return;
                    }
                    
                    // Here you could send the feedback to the server
                    // For now, just show success message
                    feedbackFormContent.style.display = 'none';
                    feedbackSuccess.style.display = 'block';
                });
                
                // Close success message and modal
                closeSuccess.addEventListener('click', () => {
                    feedbackModal.classList.remove('active');
                    // Reset form for next time
                    setTimeout(() => {
                        feedbackFormContent.style.display = 'block';
                        feedbackSuccess.style.display = 'none';
                        document.getElementById('feedback-text').value = '';
                        document.getElementById('suggestion').checked = true;
                    }, 300);
                });
            }
            
            // FAQ Toggle
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                const icon = item.querySelector('.faq-toggle i');
                
                question.addEventListener('click', () => {
                    // Close all other answers
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.querySelector('.faq-answer').style.maxHeight = '0px';
                            otherItem.querySelector('.faq-toggle i').className = 'fas fa-plus';
                            otherItem.classList.remove('active');
                        }
                    });
                    
                    // Toggle current answer
                    item.classList.toggle('active');
                    if (item.classList.contains('active')) {
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                        icon.className = 'fas fa-minus';
                    } else {
                        answer.style.maxHeight = '0px';
                        icon.className = 'fas fa-plus';
                    }
                });
            });
        });
    </script>
    <style>
        .location-info {
            margin: 20px 0;
            font-size: 1.1em;
            color: white;
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
        
        .popular-services {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
            align-items: center;
        }
        
        .popular-services span {
            color: rgba(255,255,255,0.8);
            font-size: 0.9em;
        }
        
        .popular-services a {
            color: white;
            background-color: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .popular-services a:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 30px;
        }
        
        .stat-item {
            text-align: center;
            color: white;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }
            
            .stat-item {
                padding: 10px 0;
            }
            
            .popular-services {
                flex-direction: column;
                gap: 5px;
            }
            
            .popular-services a {
                width: 100%;
                text-align: center;
            }
        }
        
        /* FAQ Styles */
        .faq-section {
            background-color: #f9f9f9;
            padding: 60px 0;
        }
        
        .faq-section h2 {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .faq-item {
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            background: white;
        }
        
        .faq-question {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .faq-question:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .faq-question h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .faq-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background-color: #f1f1f1;
            border-radius: 50%;
            color: var(--gray);
            transition: all 0.3s ease;
        }
        
        .faq-item.active .faq-toggle {
            background-color: var(--primary-color);
            color: white;
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .faq-answer p {
            padding: 0 20px 20px;
            margin: 0;
            line-height: 1.7;
            color: var(--gray);
        }
    </style>
</body>
</html> 