<!-- navbar.php -->
<?php session_start(); ?>
<nav class="navbar">
    <div class="navbar-logo">
        <a href="index.php"><img src="../assets/buyCheaper.png" alt="Logo"></a>
    </div>
    <ul class="navbar-menu">
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <li><a href="../public/scraper.php">Scrapers</a></li>
        <?php endif; ?>
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <li><a href="login.php">Login</a></li>
        <?php else: ?>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>