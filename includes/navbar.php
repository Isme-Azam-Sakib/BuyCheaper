<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuyCheaper - Price Comparison</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@100..900&family=Poppins:wght@100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/buyCheaper/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="navbar-logo">
            <a href="/buyCheaper/index.php"><img src="/buyCheaper/assets/buyCheaper2.png" alt="Logo"></a>
        </div>
        <ul class="navbar-menu">
            <li><a href="/buyCheaper/index.php">Home</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    Categories <i class="fas fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/buyCheaper/public/category_products.php?id=1">Processors</a></li>
                    <li><a href="/buyCheaper/public/category_products.php?id=2">Graphics Cards</a></li>
                    <li><a href="/buyCheaper/public/category_products.php?id=3">RAM</a></li>
                    <li><a href="/buyCheaper/public/category_products.php?id=4">Power Supply</a></li>
                    <li><a href="/buyCheaper/public/category_products.php?id=5">Casing</a></li>
                    <li><a href="/buyCheaper/public/category_products.php?id=6">Cooler</a></li>
                    <li><a href="/buyCheaper/public/category_products.php?id=7">Motherboard</a></li>
                    <li><a href="/buyCheaper/public/category_products.php?id=8">Storage</a></li>
                </ul>
            </li>
            <li><a href="/buyCheaper/public/about.php">About</a></li>
            <li><a href="/buyCheaper/public/contact.php">Contact</a></li>
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <li><a href="/buyCheaper/public/scraper.php">Scrapers</a></li>
                <li>
                    <a href="/buyCheaper/admin/index.php" title="Dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            <?php endif; ?>
            <li><a href="/buyCheaper/public/compare.php"><i class="fas fa-balance-scale"></i> Compare</a></li>
            <?php if (!isset($_SESSION['admin_logged_in'])): ?>
                <li>
                    <a href="/buyCheaper/public/login.php" title="Login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="nav-text">Login</span>
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="/buyCheaper/public/logout.php" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
</body>
</html>
