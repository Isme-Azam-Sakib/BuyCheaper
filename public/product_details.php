<?php

include '../config/database.php';
include '../includes/navbar.php'; 

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    // Fetch product details
    $stmt = $pdo->prepare("SELECT productName, productImage, description FROM products WHERE productId = :productId");
    $stmt->execute(['productId' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch vendor prices, URLs, and logos
    $priceStmt = $pdo->prepare("
        SELECT vendors.vendorName, vendors.vendorLogo, vendor_prices.price, vendor_prices.productUrl 
        FROM vendor_prices 
        JOIN vendors ON vendor_prices.vendorId = vendors.vendorId 
        WHERE vendor_prices.productId = :productId
    ");
    $priceStmt->execute(['productId' => $productId]);
    $prices = $priceStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="product-details">
        
        <h1><?php echo htmlspecialchars($product['productName']); ?></h1>
        <img src="<?php echo htmlspecialchars($product['productImage']); ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <h2>Vendor Prices</h2>
        <div class="vendor-list">
            <?php foreach ($prices as $price) { ?>
                <div class="vendor-card">
                    <img src="<?php echo htmlspecialchars($price['vendorLogo']); ?>" alt="<?php echo htmlspecialchars($price['vendorName']); ?>" class="vendor-logo">
                    <div class="vendor-info">
                        <span class="vendor-name"><?php echo htmlspecialchars($price['vendorName']); ?></span>
                        <span class="vendor-price"><?php echo htmlspecialchars($price['price']); ?> BDT</span>
                    </div>
                    <a href="<?php echo htmlspecialchars($price['productUrl']); ?>" target="_blank" class="buy-now-button">Buy Now</a>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
