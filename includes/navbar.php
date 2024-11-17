<?php session_start(); ?>
<nav class="navbar">
    <div class="navbar-logo">
        <a href="/buyCheaper/index.php"><img src="/buyCheaper/assets/buyCheaper.png" alt="Logo"></a>
    </div>
    <ul class="navbar-menu">
        <li><a href="/buyCheaper/index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <li><a href="/buyCheaper/public/scraper.php">Scrapers</a></li>
        <?php endif; ?>
        <li><a href="/buyCheaper/public/compare.php"><i class="fas fa-balance-scale"></i> Compare</a></li>
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <li><a href="/buyCheaper/public/login.php">Login</a></li>
        <?php else: ?>
            <li><a href="/buyCheaper/public/logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>
