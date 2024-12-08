<?php
include '../config/database.php';
include '../includes/navbar.php';

// Get the brand from the query string
$brand = isset($_GET['brand']) ? $_GET['brand'] : null;

if ($brand) {
    // Prepare a statement to fetch products matching the brand
    $stmt = $pdo->prepare("
        SELECT p.productId, p.productName, p.productImage, p.description, MIN(vp.price) AS lowestPrice
        FROM products p
        JOIN vendor_prices vp ON p.productId = vp.productId
        WHERE p.productName LIKE :brand
        GROUP BY p.productId
    ");
    $stmt->execute(['brand' => $brand . '%']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products by Brand - <?= htmlspecialchars($brand); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@100..900&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container mt-5">
        <h2 style="font-weight: 700; font-family:poppins; ">Products for Brand: <?= htmlspecialchars($brand); ?></h2>
        <?php if (count($products) > 0): ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="<?= htmlspecialchars($product['productImage']); ?>" class="card-img-top" alt="<?= htmlspecialchars($product['productName']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['productName']); ?></h5>
                                <p class="card-text"><?= htmlspecialchars($product['description']); ?></p>
                                <p class="card-text" style="margin-bottom: 60px">Lowest Price: ৳<?= number_format($product['lowestPrice'], 2); ?></p>
                                <a href="product_details.php?id=<?= $product['productId']; ?>" class="compare-price-button" style="text-align:center;">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No products found for this brand.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>