<?php
include_once 'includes/functions.php';

$error = '';
$success = false;
$validToken = false;
$providerId = 0;
$token = '';

// Check if provider ID and token are set
if (isset($_GET['id']) && isset($_GET['token'])) {
    $providerId = (int)$_GET['id'];
    $token = $_GET['token'];
    
    // Validate token
    if (validateResetToken($providerId, $token)) {
        $validToken = true;
    } else {
        $error = "Invalid or expired reset link. Please request a new one.";
    }
} else {
    $error = "Invalid reset link. Please request a new one.";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    // Get form data
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "Both password fields are required!";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        // Update password
        if (updateProviderPassword($providerId, $newPassword)) {
            // Delete reset token
            deleteResetToken($providerId, $token);
            $success = true;
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KaamBuddy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <h2>Password Reset Successful</h2>
                    <p>Your password has been successfully reset.</p>
                    <p>You can now log in with your new password.</p>
                    <a href="provider_login.php" class="btn-primary">Login Now</a>
                </div>
            <?php elseif ($validToken): ?>
                <div class="reset-form">
                    <div class="form-title">
                        <h2>Reset Your Password</h2>
                        <p>Please enter your new password below</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-box">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="reset_password.php?id=<?php echo $providerId; ?>&token=<?php echo $token; ?>" method="POST">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required minlength="8">
                            <small>Password must be at least 8 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                        
                        <div class="form-footer">
                            <button type="submit" class="submit-btn">Reset Password</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <h2>Invalid Reset Link</h2>
                    <p><?php echo $error; ?></p>
                    <a href="forgot_password.php" class="btn-primary">Request New Reset Link</a>
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
                    <p><i class="fas fa-envelope"></i> <a href="mailto:info@kaambuddy.com">info@kaambuddy.com</a></p>
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