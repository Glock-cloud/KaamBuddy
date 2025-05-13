<?php
include_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - KaamBuddy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .about-section {
            padding: 60px 0;
        }
        .about-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: center;
            margin-bottom: 50px;
        }
        .about-image {
            flex: 1;
            min-width: 300px;
        }
        .about-image img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .about-content {
            flex: 2;
            min-width: 300px;
        }
        .about-content h2 {
            color: #4a6ee0;
            margin-bottom: 20px;
            font-size: 32px;
        }
        .about-content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .vision-section {
            background-color: #f9f9f9;
            padding: 60px 0;
        }
        .vision-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .vision-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .vision-card:hover {
            transform: translateY(-5px);
        }
        .vision-card i {
            font-size: 40px;
            color: #4a6ee0;
            margin-bottom: 20px;
        }
        .vision-card h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .team-section {
            padding: 60px 0;
        }
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        .section-title h2 {
            color: #4a6ee0;
            font-size: 32px;
            margin-bottom: 15px;
        }
        .section-title p {
            max-width: 700px;
            margin: 0 auto;
            color: #666;
        }
    </style>
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
                    <li><a href="register.php" class="btn-primary">Register as Provider</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="about-section">
        <div class="container">
            <div class="about-container">
                <div class="about-image">
                    <img src="images/about-image.jpg" alt="KaamBuddy Team" onerror="this.src='https://via.placeholder.com/600x400?text=KaamBuddy'">
                </div>
                <div class="about-content">
                    <h2>Our Story</h2>
                    <p>KaamBuddy was born out of a simple observation: finding reliable service providers for everyday tasks is often challenging, time-consuming, and frustrating. In a rapidly digitalizing India, we saw an opportunity to bridge this gap.</p>
                    <p>Founded in 2023, KaamBuddy aims to revolutionize how people connect with local service providers. Our platform brings together skilled professionals and customers in a seamless, transparent, and efficient manner.</p>
                    <p>The name "KaamBuddy" combines the Hindi word "Kaam" (work) with "Buddy," reflecting our mission to be your trusted companion in getting things done. We believe that everyone deserves access to quality services without the hassle of endless searching and uncertainty.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="vision-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Vision & Mission</h2>
                <p>We're on a mission to transform how services are discovered, booked, and delivered across India.</p>
            </div>
            <div class="vision-cards">
                <div class="vision-card">
                    <i class="fas fa-handshake"></i>
                    <h3>Empowering Service Providers</h3>
                    <p>We help skilled professionals showcase their expertise, find consistent work, and earn fair compensation. By providing a digital platform, we enable service providers to expand their reach beyond traditional word-of-mouth networks.</p>
                </div>
                <div class="vision-card">
                    <i class="fas fa-users"></i>
                    <h3>Connecting Communities</h3>
                    <p>KaamBuddy strengthens local economies by facilitating connections between service providers and customers within the same community. This creates a network of trust and reliability that benefits everyone involved.</p>
                </div>
                <div class="vision-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Ensuring Quality & Trust</h3>
                    <p>We're committed to maintaining high standards through our verification process, ratings system, and customer feedback. This transparency helps customers make informed decisions and rewards providers who deliver excellent service.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <div class="container">
            <div class="section-title">
                <h2>Problems We're Solving</h2>
                <p>KaamBuddy addresses several key challenges in the service industry:</p>
            </div>
            <div class="vision-cards">
                <div class="vision-card">
                    <i class="fas fa-search"></i>
                    <h3>Fragmented Service Discovery</h3>
                    <p>Finding the right service provider often involves asking friends, searching through multiple platforms, or taking chances with unknown providers. KaamBuddy centralizes this process, making it simple to find verified professionals for any job.</p>
                </div>
                <div class="vision-card">
                    <i class="fas fa-rupee-sign"></i>
                    <h3>Price Transparency</h3>
                    <p>Unclear pricing leads to misunderstandings and disputes. Our platform encourages upfront pricing information, helping customers budget appropriately and ensuring providers are fairly compensated for their work.</p>
                </div>
                <div class="vision-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Digital Divide</h3>
                    <p>Many skilled service providers lack digital presence despite having excellent skills. KaamBuddy bridges this gap by offering an easy-to-use platform that helps even tech-novice providers establish their online presence.</p>
                </div>
                <div class="vision-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Scheduling Inefficiencies</h3>
                    <p>Coordinating availability between customers and service providers can be frustrating. Our platform streamlines this process, reducing the back-and-forth communication and helping both parties find mutually convenient times.</p>
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
                        <li><a href="register.php">Register as Provider</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-envelope"></i> <a href="mailto:KaamBuddy.info@gmail.com">KaamBuddy.info@gmail.com</a></p>
                    <p><i class="fas fa-phone"></i> <a href="tel:+919019304426">+91 9019304426</a></p>
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

    <script src="js/script.js"></script>
</body>
</html>