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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    
    <h1 class="container" style="font-weight: 700;">Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
    <div class="search-results container">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="search-result-item">
                    <img src="<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                    <h2><?php echo htmlspecialchars($product['productName']); ?></h2>
                    <p style="margin-bottom: 60px"><?php echo htmlspecialchars($product['description']); ?></p>
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
<?php include '../includes/footer.php'; ?>
</body>

</html>