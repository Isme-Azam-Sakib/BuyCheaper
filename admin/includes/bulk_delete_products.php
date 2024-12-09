<?php
require_once '../../config/database.php';
include 'auth.php';

try {
    $productIds = $_POST['productIds'] ?? [];
    
    if (empty($productIds)) {
        throw new Exception('No products selected');
    }

    // Convert array to string for SQL IN clause
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    
    $pdo->beginTransaction();

    // Delete from vendor_prices first (foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM vendor_prices WHERE productId IN ($placeholders)");
    $stmt->execute($productIds);

    // Then delete from products
    $stmt = $pdo->prepare("DELETE FROM products WHERE productId IN ($placeholders)");
    $stmt->execute($productIds);

    $pdo->commit();

    echo json_encode([
        'error' => false,
        'message' => 'Products deleted successfully'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 