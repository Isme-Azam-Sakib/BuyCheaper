<?php
include 'config/database.php';
include 'includes/navbar.php';

// Fetch unique brand names from products
$brands = $pdo->query("SELECT DISTINCT SUBSTRING_INDEX(productName, ' ', 1) AS brand FROM products ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);

// Fetch categories and products for each category
$categories = $pdo->query("SELECT DISTINCT categoryId, category FROM products")->fetchAll(PDO::FETCH_ASSOC);

$productsByCategory = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT productId, productName, productImage, description FROM products WHERE categoryId = :categoryId LIMIT 4");
    $stmt->execute(['categoryId' => $category['categoryId']]);
    $productsByCategory[$category['category']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$query = "
    SELECT p.productId, p.productName, p.productImage, p.description, v.price, v.lastUpdated 
    FROM products p
    JOIN vendor_prices v ON p.productId = v.productId
    WHERE v.price > 0
    ORDER BY v.lastUpdated DESC
    LIMIT 10
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$recentProducts = $stmt->fetchAll();

$cpuCategoryId = 1;
$ramCategoryId = 3;
$psuCategoryId = 4;
$stmt = $pdo->prepare("SELECT p.productId, p.productName, p.description, p.productImage
                       FROM products p
                       JOIN vendor_prices vp ON p.productId = vp.productId
                       WHERE p.categoryId = :cpuCategoryId AND vp.price > 0.00
                       ORDER BY vp.lastUpdated DESC
                       LIMIT 8");
$stmt->execute(['cpuCategoryId' => $cpuCategoryId]);
$cpuProducts = $stmt->fetchAll();


$stmt = $pdo->prepare("SELECT p.productId, p.productName, p.description, p.productImage
                       FROM products p
                       JOIN vendor_prices vp ON p.productId = vp.productId
                       WHERE p.categoryId = :ramCategoryId AND vp.price > 0.00
                       ORDER BY vp.lastUpdated DESC
                       LIMIT 8");
$stmt->execute(['ramCategoryId' => $ramCategoryId]);
$ramProducts = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT p.productId, p.productName, p.description, p.productImage
                       FROM products p
                       JOIN vendor_prices vp ON p.productId = vp.productId
                       WHERE p.categoryId = :psuCategoryId AND vp.price > 0.00
                       ORDER BY vp.lastUpdated DESC
                       LIMIT 8");
$stmt->execute(['psuCategoryId' => $psuCategoryId]);
$psuProducts = $stmt->fetchAll();

?>

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
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>Your build, Your decision</h1>
            <p>Find & compare the best deals available</p>
            <a href="#search" class="cta-button">Get Started</a>
        </div>
    </section>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: poppins;
        }

        .hero-section {
            position: relative;
            width: 100%;
            height: 100vh;
            background: url('assets/pcbuild1.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent overlay */
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: #fff;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 4rem;
            font-weight: 700;
            margin: 0;
        }

        .hero-content p {
            font-size: 1.5rem;
            font-weight: 400;
            margin-top: 10px;
        }

        .cta-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: capitalize;
            background-color: #ff5722;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .cta-button:hover {
            background-color: #e64a19;
        }
    </style>

    <!-- Browse Brands Section -->
    <section class="browse-brands-section container mt-5">
        <h2>Browse Brands</h2>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($brands as $brand): ?>
                <a href="/buyCheaper/public/brand_products.php?brand=<?= urlencode($brand); ?>" class="btn btn-outline-primary">
                    <?= htmlspecialchars($brand); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>


    <!-- search -->
    <div class="search-container container">
        <h2>Looking for a product? </h2>
        <input type="text" id="search" placeholder="Search for any product..." autocomplete="off">
        <div id="results"></div> <!-- This div will display the search results -->
    </div>

    <!-- Recently Added Section -->
    <section class="recently-added-section container mt-5">
        <h2>Recently Added Products</h2>
        <div class="recent-products-carousel">
            <button class="carousel-arrow left-arrow">&#10094;</button>
            <div class="carousel-track">
                <?php foreach ($recentProducts as $product): ?>
                    <div class="carousel-item">
                        <img src="<?= $product['productImage']; ?>" alt="<?= $product['productName']; ?>" />
                        <h3><?= $product['productName']; ?></h3>
                        <p style="margin-bottom: 60px"><?= $product['description']; ?></p>
                        <span class="price">Price: ৳<?= $product['price']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-arrow right-arrow">&#10095;</button>
        </div>
    </section>

    <!-- CPU Carousel -->
    <section class="product-container container mt-5">
        <h2>Processors</h2>
        <div class="search-results">
            <?php foreach ($cpuProducts as $product): ?>
                <div class="search-result-item">
                    <img src="<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                    <h2><?php echo htmlspecialchars($product['productName']); ?></h2>
                    <p style="margin-bottom: 60px"><?php echo htmlspecialchars($product['description']); ?></p>
                    <a href="/buyCheaper/public/product_details.php?id=<?php echo $product['productId']; ?>" class="compare-price-button">Compare Price</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ram Carousel -->
    <div class="full-width">
        <section class="product-container container mt-5" >
            <h2>Browse Rams</h2>
            <div class="search-results">
                <?php foreach ($ramProducts as $product): ?>
                    <div class="search-result-item">
                        <img src="<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                        <h2><?php echo htmlspecialchars($product['productName']); ?></h2>
                        <p style="margin-bottom: 60px"><?php echo htmlspecialchars($product['description']); ?></p>
                        <a href="/buyCheaper/public/product_details.php?id=<?php echo $product['productId']; ?>" class="compare-price-button">Compare Price</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>



    <!-- PSU Carousel -->
    <section class="product-container container mt-5">
        <h2>Powersupply</h2>
        <div class="search-results">
            <?php foreach ($psuProducts as $product): ?>
                <div class="search-result-item">
                    <img src="<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                    <h2><?php echo htmlspecialchars($product['productName']); ?></h2>
                    <p style="margin-bottom: 60px"><?php echo htmlspecialchars($product['description']); ?></p>
                    <a href="/buyCheaper/public/product_details.php?id=<?php echo $product['productId']; ?>" class="compare-price-button">Compare Price</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.querySelector('.carousel-track');
            const items = document.querySelectorAll('.carousel-item');
            const leftArrow = document.querySelector('.left-arrow');
            const rightArrow = document.querySelector('.right-arrow');

            let index = 0;
            const totalItems = items.length;

            function updateCarousel() {
                track.style.transform = `translateX(-${index * 100}%)`;
            }

            rightArrow.addEventListener('click', () => {
                index = (index + 1) % totalItems;
                updateCarousel();
            });

            leftArrow.addEventListener('click', () => {
                index = (index - 1 + totalItems) % totalItems;
                updateCarousel();
            });
        });
    </script>
    <script src="js/app.js"></script>
</body>

</html>