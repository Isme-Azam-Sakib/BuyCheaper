<?php
require_once '../config/database.php';

$productId = $_GET['productId'] ?? '';
if ($productId) {
    $stmt = $pdo->prepare("
        SELECT 
            p.productName AS name, 
            p.productImage AS image, 
            p.description,
            vp.price AS lowest_price,
            v.vendorName AS vendor_name,
            v.vendorLogo AS vendor_logo,
            vp.productUrl AS product_url
        FROM products p
        LEFT JOIN vendor_prices vp ON p.productId = vp.productId
        LEFT JOIN vendors v ON vp.vendorId = v.vendorId
        WHERE p.productId = :productId
        AND vp.price = (
            SELECT MIN(price) 
            FROM vendor_prices 
            WHERE productId = :productId
        )
    ");
    $stmt->execute([':productId' => $productId]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}
