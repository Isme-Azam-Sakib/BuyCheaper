<?php
require_once '../../config/database.php';
include 'auth.php';

try {
    $productId = $_POST['productId'] ?? null;
    
    if (!$productId) {
        throw new Exception('Product ID is required');
    }

    // Get product details with vendor prices
    $query = "
        SELECT 
            p.productId,
            p.productName,
            p.productImage,
            p.description,
            p.categoryId,
            vp.vendorId,
            vp.price,
            vp.productUrl,
            vp.lastUpdated,
            v.vendorName
        FROM products p
        LEFT JOIN vendor_prices vp ON p.productId = vp.productId
        LEFT JOIN vendors v ON vp.vendorId = v.vendorId
        WHERE p.productId = :productId
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':productId' => $productId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        throw new Exception('Product not found');
    }

    // Format response
    $product = [
        'productId' => $rows[0]['productId'],
        'productName' => $rows[0]['productName'],
        'productImage' => $rows[0]['productImage'],
        'description' => $rows[0]['description'],
        'categoryId' => $rows[0]['categoryId'],
        'vendorPrices' => []
    ];

    // Add vendor prices
    foreach ($rows as $row) {
        if ($row['vendorId']) {
            $product['vendorPrices'][] = [
                'vendorName' => $row['vendorName'],
                'price' => $row['price'],
                'productUrl' => $row['productUrl'],
                'lastUpdated' => $row['lastUpdated']
            ];
        }
    }

    echo json_encode([
        'error' => false,
        'data' => $product
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}