<?php
// Include the database configuration
include '../assets/config/database.php';

if (isset($_GET['categoryId'])) {
    $categoryId = intval($_GET['categoryId']);

    $stmt = $pdo->prepare("
        SELECT p.productId, p.productName, v.price 
        FROM products p
        JOIN vendor_prices v ON p.productId = v.productId
        WHERE p.categoryId = :categoryId
        ORDER BY v.price ASC
    ");

    $stmt->execute([':categoryId' => $categoryId]);
    echo json_encode($stmt->fetchAll());
}
?>
