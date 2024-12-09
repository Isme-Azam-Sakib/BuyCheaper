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

// Category IDs
$cpuCategoryId = 1;
$gpuCategoryId = 2;
$ramCategoryId = 3;
$psuCategoryId = 4;
$casingCategoryId = 5;
$coolerCategoryId = 6;
$motherboardCategoryId = 7;
$ssdCategoryId = 8;

$stmt = $pdo->prepare("SELECT 
                        p.productId, 
                        p.productName, 
                        p.description, 
                        p.productImage,
                        MIN(vp.price) as lowestPrice,
                        COUNT(DISTINCT vp.vendorId) as vendorCount
                    FROM products p
                    JOIN vendor_prices vp ON p.productId = vp.productId
                    WHERE p.categoryId = :cpuCategoryId 
                        AND vp.price > 0.00
                    GROUP BY p.productId
                    HAVING vendorCount > 0
                    ORDER BY vendorCount DESC, vp.lastUpdated DESC
                    LIMIT 8");
$stmt->execute(['cpuCategoryId' => $cpuCategoryId]);
$cpuProducts = $stmt->fetchAll();


$stmt = $pdo->prepare("SELECT 
                        p.productId, 
                        p.productName, 
                        p.description, 
                        p.productImage,
                        MIN(vp.price) as lowestPrice,
                        COUNT(DISTINCT vp.vendorId) as vendorCount
                    FROM products p
                    JOIN vendor_prices vp ON p.productId = vp.productId
                    WHERE p.categoryId = :ramCategoryId 
                        AND vp.price > 0.00
                    GROUP BY p.productId
                    HAVING vendorCount > 0
                    ORDER BY vendorCount DESC, vp.lastUpdated DESC
                    LIMIT 8");
$stmt->execute(['ramCategoryId' => $ramCategoryId]);
$ramProducts = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT 
                        p.productId, 
                        p.productName, 
                        p.description, 
                        p.productImage,
                        MIN(vp.price) as lowestPrice,
                        COUNT(DISTINCT vp.vendorId) as vendorCount
                    FROM products p
                    JOIN vendor_prices vp ON p.productId = vp.productId
                    WHERE p.categoryId = :gpuCategoryId 
                        AND vp.price > 0.00
                    GROUP BY p.productId
                    HAVING vendorCount > 0
                    ORDER BY vendorCount DESC, vp.lastUpdated DESC
                    LIMIT 8");
$stmt->execute(['gpuCategoryId' => $gpuCategoryId]);
$gpuProducts = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT 
                        p.productId, 
                        p.productName, 
                        p.description, 
                        p.productImage,
                        MIN(vp.price) as lowestPrice,
                        COUNT(DISTINCT vp.vendorId) as vendorCount
                    FROM products p
                    JOIN vendor_prices vp ON p.productId = vp.productId
                    WHERE p.categoryId = :psuCategoryId 
                        AND vp.price > 0.00
                    GROUP BY p.productId
                    HAVING vendorCount > 0
                    ORDER BY vendorCount DESC, vp.lastUpdated DESC
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
</head>

<body>
    <!-- <div id="preloader">
        <dotlottie-player 
            src="/buyCheaper/assets/preloader.json"
            background="transparent" 
            speed="1" 
            style="width: 300px; height: 300px" 
            loop 
            autoplay>
        </dotlottie-player>
    </div> -->
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>Buy Cheaper! Save Bigger!</h1>
            <p>Find & compare the best deals available</p>
            <!-- search -->
            <div class="search-container container">
                <input type="text" id="search" placeholder="Search something (E.g. ryzen 7800X3D)" autocomplete="off">
                <div id="results"></div> 
            </div>
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
            width: 96%;
            height: 60vh;
            background: url('assets/pcbuild1.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 40px;
            margin: 2% 2% 0 2%;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 40px;
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
            background-color: #7f45f3;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
        }

        .cta-button:hover {
            background-color: #4b2793;
        }
    </style>

    <!-- Browse Brands Section -->
    <section class="browse-brands-section container mt-5">
        <h2>Browse Brands</h2>
        <div id="brand-list" class="d-flex flex-wrap gap-3">
            <?php foreach ($brands as $index => $brand): ?>
                <a href="/buyCheaper/public/brand_products.php?brand=<?= urlencode($brand); ?>"
                   class="brand-btn <?= $index >= 21 ? 'hidden-brand' : ''; ?>">
                    <?= htmlspecialchars($brand); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (count($brands) > 21): ?>
            <button id="show-more-btn" class="mt-3">
                Show More <span>&#9660;</span>
            </button>
        <?php endif; ?>
    </section>





    <!-- Recently Added Section -->
    <section class="recently-added-section container mt-5">
        <h2>Recently Added Products</h2>
        <div class="recent-products-carousel">
            <button class="carousel-arrow left-arrow">&#10094;</button>
            <div class="carousel-track">
                <?php foreach ($recentProducts as $product): ?>
                    <div class="carousel-item">
                        <div class="product-image">
                            <img src="<?= !empty($product['productImage']) ? htmlspecialchars($product['productImage']) : '/buyCheaper/assets/no-image.png' ?>" 
                                 alt="<?= htmlspecialchars($product['productName']) ?>"
                                 loading="lazy">
                        </div>
                        <h3><?= htmlspecialchars($product['productName']); ?></h3>
                        <p><?= htmlspecialchars($product['description']); ?></p>
                        <span class="price">Price: ৳<?= number_format($product['price']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-arrow right-arrow">&#10095;</button>
        </div>
    </section>

    <!-- CPU Section -->
    <section class="product-container">
        <div class="container">
            <h2>Processors</h2>
            <div class="products-grid">
                <?php foreach ($cpuProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= !empty($product['productImage']) ? $product['productImage'] : '/buyCheaper/assets/no-image.png' ?>" 
                                 alt="<?= htmlspecialchars($product['productName']) ?>"
                                 onerror="this.src='/buyCheaper/assets/no-image.png'">
                            <?php if (isset($product['vendorCount']) && $product['vendorCount'] > 1): ?>
                                <span class="vendor-count"><?= $product['vendorCount'] ?> vendors</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['productName']); ?></h3>
                            <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="product-footer">
                                <div class="price">
                                    <span class="label">Starting from</span>
                                    <span class="amount">৳<?= number_format($product['lowestPrice']); ?></span>
                                </div>
                                <a href="/buyCheaper/public/product_details.php?id=<?= $product['productId']; ?>" 
                                   class="compare-prices-btn">Compare Prices</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="product-card view-all-card">
                    <a href="/buyCheaper/public/category_products.php?id=1" class="view-all-link">
                        <i class="fa-solid fa-microchip"></i>
                        <h3>View All Processors</h3>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- RAM Section -->
    <section class="product-container">
        <div class="container">
            <h2>Browse RAMs</h2>
            <div class="products-grid">
                <?php foreach ($ramProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= !empty($product['productImage']) ? $product['productImage'] : '/buyCheaper/assets/no-image.png' ?>" 
                                 alt="<?= htmlspecialchars($product['productName']) ?>"
                                 onerror="this.src='/buyCheaper/assets/no-image.png'">
                            <?php if (isset($product['vendorCount']) && $product['vendorCount'] > 1): ?>
                                <span class="vendor-count"><?= $product['vendorCount'] ?> vendors</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['productName']); ?></h3>
                            <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="product-footer">
                                <div class="price">
                                    <span class="label">Starting from</span>
                                    <span class="amount">৳<?= number_format($product['lowestPrice']); ?></span>
                                </div>
                                <a href="/buyCheaper/public/product_details.php?id=<?= $product['productId']; ?>" 
                                   class="compare-prices-btn">Compare Prices</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="product-card view-all-card">
                    <a href="/buyCheaper/public/category_products.php?id=3" class="view-all-link">
                        <i class="fa-solid fa-memory"></i>
                        <h3>View All RAMs</h3>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- GPU Section -->
    <section class="product-container">
        <div class="container">
            <h2>Graphics Cards</h2>
            <div class="products-grid">
                <?php foreach ($gpuProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= !empty($product['productImage']) ? $product['productImage'] : '/buyCheaper/assets/no-image.png' ?>" 
                                 alt="<?= htmlspecialchars($product['productName']) ?>"
                                 onerror="this.src='/buyCheaper/assets/no-image.png'">
                            <?php if (isset($product['vendorCount']) && $product['vendorCount'] > 1): ?>
                                <span class="vendor-count"><?= $product['vendorCount'] ?> vendors</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['productName']); ?></h3>
                            <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="product-footer">
                                <div class="price">
                                    <span class="label">Starting from</span>
                                    <span class="amount">৳<?= number_format($product['lowestPrice']); ?></span>
                                </div>
                                <a href="/buyCheaper/public/product_details.php?id=<?= $product['productId']; ?>" 
                                   class="compare-prices-btn">Compare Prices</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="product-card view-all-card">
                    <a href="/buyCheaper/public/category_products.php?id=2" class="view-all-link">
                        <i class="fa-solid fa-video"></i>
                        <h3>View All GPUs</h3>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- PSU Section -->
    <section class="product-container">
        <div class="container">
            <h2>Power Supply</h2>
            <div class="products-grid">
                <?php foreach ($psuProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= !empty($product['productImage']) ? $product['productImage'] : '/buyCheaper/assets/no-image.png' ?>" 
                                 alt="<?= htmlspecialchars($product['productName']) ?>"
                                 onerror="this.src='/buyCheaper/assets/no-image.png'">
                            <?php if (isset($product['vendorCount']) && $product['vendorCount'] > 1): ?>
                                <span class="vendor-count"><?= $product['vendorCount'] ?> vendors</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['productName']); ?></h3>
                            <p class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="product-footer">
                                <div class="price">
                                    <span class="label">Starting from</span>
                                    <span class="amount">৳<?= number_format($product['lowestPrice']); ?></span>
                                </div>
                                <a href="/buyCheaper/public/product_details.php?id=<?= $product['productId']; ?>" 
                                   class="compare-prices-btn">Compare Prices</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="product-card view-all-card">
                    <a href="/buyCheaper/public/category_products.php?id=4" class="view-all-link">
                        <i class="fa-solid fa-plug"></i>
                        <h3>View All PSUs</h3>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </div>
            </div>
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
    <?php include 'includes/footer.php'; ?>
</body>

</html>