<?php
include_once 'includes/functions.php';

$error = '';
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email = sanitizeInput($_POST['email']);
    
    // Validate email
    if (empty($email)) {
        $error = "Email address is required!";
    } else {
        // Check if email exists in the database
        $provider = getProviderByEmail($email);
        
        if ($provider) {
            // Create reset token
            $token = createPasswordResetToken($provider['id']);
            
            // Create reset link
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/kaamchaahiye/reset_password.php?id=" . $provider['id'] . "&token=" . $token;
            
            // Send password reset email
            if (sendPasswordResetEmail($email, $resetLink) || ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1')) {
                $success = true;
                
                // In development environment, show the reset link directly
                if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
                    $debugMessage = "<div style='background:#e8f5e9; padding:15px; margin-top:15px; border-radius:5px;'>";
                    $debugMessage .= "<strong>Debug Information (Development Only):</strong><br>";
                    $debugMessage .= "Email sending is bypassed in local environment.<br>";
                    $debugMessage .= "<strong>Reset Link:</strong> <a href='$resetLink'>$resetLink</a>";
                    $debugMessage .= "</div>";
                }
            } else {
                $error = "Failed to send password reset email. Please try again later.";
            }
        } else {
            // To prevent email enumeration, don't indicate that email doesn't exist
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - KaamBuddy</title>
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
                    <li><a href="register.php" class="btn-primary">Register as Provider</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="reset-section">
        <div class="container">
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h2>Password Reset Link Sent</h2>
                    <p>If an account with that email exists, a password reset link has been sent.</p>
                    <p>Please check your email and follow the instructions to reset your password.</p>
                    <p>The link will expire in 24 hours.</p>
                    <?php if (isset($debugMessage)) echo $debugMessage; ?>
                    <a href="provider_login.php" class="btn-primary">Return to Login</a>
                </div>
            <?php else: ?>
                <div class="reset-form">
                    <div class="form-title">
                        <h2>Forgot Password</h2>
                        <p>Enter your email to receive a password reset link</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-box">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="forgot_password.php" method="POST">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-footer">
                            <button type="submit" class="submit-btn">Send Reset Link</button>
                            <div class="form-links">
                                <a href="provider_login.php">Back to Login</a>
                            </div>
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
                <p>&copy; <?php echo date('Y'); ?> KaamBuddy. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html> 