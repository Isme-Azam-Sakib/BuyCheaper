<?php
include '../config/database.php';

if (isset($_POST['query']) && !empty($_POST['query'])) {
    $search = "%" . $_POST['query'] . "%";
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            p.productId, 
            p.productName AS name, 
            p.productImage AS image,
            p.description,
            MIN(vp.price) AS lowestPrice,
            v.vendorName,
            v.vendorLogo
        FROM products p
        LEFT JOIN vendor_prices vp ON p.productId = vp.productId
        LEFT JOIN vendors v ON vp.vendorId = v.vendorId
        WHERE p.productName LIKE :search
        GROUP BY p.productId
        LIMIT 10
    ");
    
    $stmt->execute([':search' => $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        header('Content-Type: application/json');
        echo json_encode($results);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'No results found']);
    }
} else {
    echo json_encode(['message' => 'Invalid request']);
}
?>
