<?php
include '../config/database.php';
include '../includes/navbar.php'; 

$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query) {
    $search = "%{$query}%";
    $stmt = $pdo->prepare("SELECT productId, productName, productImage, description FROM products WHERE productName LIKE :search");
    $stmt->execute(['search' => $search]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for "<?php echo htmlspecialchars($query); ?>"</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>

    <div class="search-results">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="search-result-item">
                <img src="<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                <h2><?php echo htmlspecialchars($product['productName']); ?></h2>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <!-- Updated the button link to redirect to product_details.php -->
                <a href="product_details.php?id=<?php echo $product['productId']; ?>" class="compare-price-button">Compare Price</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No results found for "<?php echo htmlspecialchars($query); ?>"</p>
    <?php endif; ?>
</div>



    <script>
        function redirectToProduct(productId) {
            window.location.href = `product_details.php?id=${productId}`;
        }
    </script>
</body>

</html>