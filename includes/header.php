<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="container">
        <div class="logo">
            <h1>काम<span>Buddy</span></h1>
        </div>
        <button class="mobile-menu-btn" id="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <nav>
            <ul id="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Find Services</a></li>
                <?php if(isset($_SESSION['provider_id'])): ?>
                    <li><a href="provider_dashboard.php">My Dashboard</a></li>
                    <li><a href="logout.php" class="btn-secondary">Logout</a></li>
                <?php else: ?>
                    <li><a href="provider_login.php">Provider Login</a></li>
                    <li><a href="register.php" class="btn-primary">Register as Provider</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header> 