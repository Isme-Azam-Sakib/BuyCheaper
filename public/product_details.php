<?php
include '../config/database.php';

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    $stmt = $pdo->prepare("SELECT productName, productImage FROM products WHERE productId = :productId");
    $stmt->execute(['productId' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $priceStmt = $pdo->prepare("SELECT vendors.vendorName, vendor_prices.price FROM vendor_prices JOIN vendors ON vendor_prices.vendorId = vendors.vendorId WHERE vendor_prices.productId = :productId");
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

        <h2>Vendor Prices</h2>
        <ul>
            <?php foreach ($prices as $price) { ?>
                <li><?php echo htmlspecialchars($price['vendorName']) . ': ' . htmlspecialchars($price['price']); ?> BDT</li>
            <?php } ?>
        </ul>
    </div>
</body>
</html>
