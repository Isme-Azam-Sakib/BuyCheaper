<?php
include '../config/database.php';

if (isset($_GET['productId'])) {
    $productId = $_GET['productId'];
    
    $stmt = $pdo->prepare("
        SELECT 
            p.productId,
            p.productName as name,
            p.productImage as image,
            p.description,
            v.vendorName as vendor_name,
            v.vendorLogo as vendor_logo,
            vp.price as lowest_price,
            vp.productUrl as vendor_url
        FROM products p
        JOIN vendor_prices vp ON p.productId = vp.productId
        JOIN vendors v ON vp.vendorId = v.vendorId
        WHERE p.productId = :productId
        ORDER BY vp.price ASC
        LIMIT 1
    ");
    
    $stmt->execute(['productId' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
}
?>
