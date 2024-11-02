<?php
include '../config/database.php';
include '../includes/navbar.php';

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

$cpuCategoryId = 1; // Assuming CPUs have a categoryId of 1
$stmt = $pdo->prepare("SELECT p.productId, p.productName, p.description, p.productImage
                       FROM products p
                       JOIN vendor_prices vp ON p.productId = vp.productId
                       WHERE p.categoryId = :cpuCategoryId AND vp.price > 0.00
                       ORDER BY vp.lastUpdated DESC
                       LIMIT 8");
$stmt->execute(['cpuCategoryId' => $cpuCategoryId]);
$cpuProducts = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuyCheaper - Price Comparison</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>BuyCheaper</h1>
            <p>Search for the best deals on your favorite products!</p>
        </header>
        <!---------------------------------------- search ---------------------------------------->
        <div class="search-container">
            <input type="text" id="search" placeholder="Search for products..." autocomplete="off">
            <div id="results"></div> <!-- This div will display the search results -->
        </div>
        <!---------------------------------------- recently added ---------------------------------------->
        <section class="recently-added-section">
            <h2>Recently Added Products</h2>
            <div class="recent-products-carousel">
                <button class="carousel-arrow left-arrow">&#10094;</button>
                <div class="carousel-track">
                    <?php foreach ($recentProducts as $product): ?>
                        <div class="carousel-item">
                            <img src="<?= $product['productImage']; ?>" alt="<?= $product['productName']; ?>" />
                            <h3><?= $product['productName']; ?></h3>
                            <p><?= $product['description']; ?></p>
                            <span class="price">Price: ৳<?= $product['price']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-arrow right-arrow">&#10095;</button>
            </div>
        </section>

    <!---------------------------------------- cpu carousel ---------------------------------------->
    <section class="product-container">
        <h2>CPUs</h2>
        <div class="search-results">
            <?php foreach ($cpuProducts as $product): ?>
                <div class="search-result-item">
                    <img src="<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                    <h2><?php echo htmlspecialchars($product['productName']); ?></h2>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <a href="product_details.php?id=<?php echo $product['productId']; ?>" class="compare-price-button">Compare Price</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>



    <!---------------------------------------- others ---------------------------------------->
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

    <script src="../js/app.js"></script>
</body>

</html>