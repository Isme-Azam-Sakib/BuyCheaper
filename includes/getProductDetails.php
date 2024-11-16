<?php
require_once '../config/database.php';


$productId = $_GET['productId'] ?? '';
if ($productId) {
    $stmt = $pdo->prepare("
        SELECT productName AS name, productImage AS image, description, 
        (SELECT MIN(price) FROM vendor_prices WHERE productId = :productId) AS lowest_price
        FROM products WHERE productId = :productId
    ");
    $stmt->execute([':productId' => $productId]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}
