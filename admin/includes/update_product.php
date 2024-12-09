<?php
include '../../config/database.php';
include '../includes/auth.php';

try {
    // Get form data
    $productId = $_POST['productId'] ?? null;
    $productName = $_POST['productName'] ?? '';
    $categoryId = $_POST['categoryId'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!$productId || !$productName || !$categoryId) {
        throw new Exception('Required fields are missing');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Handle image upload if provided
    $imageQuery = '';
    $params = [
        ':productId' => $productId,
        ':productName' => $productName,
        ':categoryId' => $categoryId,
        ':description' => $description
    ];

    if (isset($_FILES['productImage']) && $_FILES['productImage']['size'] > 0) {
        $uploadDir = '../../images/products/';
        $fileName = uniqid() . '_' . basename($_FILES['productImage']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['productImage']['tmp_name'], $uploadFile)) {
            throw new Exception('Failed to upload image');
        }

        $imageQuery = ', productImage = :productImage';
        $params[':productImage'] = '/buyCheaper/images/products/' . $fileName;
    }

    // Update product
    $query = "
        UPDATE products 
        SET productName = :productName,
            categoryId = :categoryId,
            description = :description
            $imageQuery
        WHERE productId = :productId
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Commit transaction
    $pdo->commit();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'error' => false,
        'message' => 'Product updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 