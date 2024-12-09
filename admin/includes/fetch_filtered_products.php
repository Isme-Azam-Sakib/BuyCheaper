<?php
include '../../config/database.php';
include '../includes/auth.php';

// Get filters
$search = $_POST['search'] ?? '';
$category = $_POST['category'] ?? '';
$vendor = $_POST['vendor'] ?? '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$perPage = isset($_POST['perPage']) && $_POST['perPage'] !== 'all' 
    ? (int)$_POST['perPage'] 
    : 50; // Default to 50 if not specified
$offset = ($page - 1) * $perPage;

try {
    // Base query
    $query = "
        SELECT 
            p.productId,
            p.productName,
            p.productImage,
            p.description,
            c.categoryName,
            COUNT(DISTINCT vp.vendorId) as vendorCount,
            MIN(vp.price) as minPrice,
            MAX(vp.price) as maxPrice
        FROM products p
        LEFT JOIN categories c ON p.categoryId = c.categoryId
        LEFT JOIN vendor_prices vp ON p.productId = vp.productId
        WHERE 1=1
    ";

    $params = [];

    // Add filters
    if (!empty($search)) {
        $query .= " AND (p.productName LIKE :search OR c.categoryName LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if (!empty($category)) {
        $query .= " AND p.categoryId = :category";
        $params[':category'] = $category;
    }

    if (!empty($vendor)) {
        $query .= " AND vp.vendorId = :vendor";
        $params[':vendor'] = $vendor;
    }

    // Add grouping
    $query .= " GROUP BY p.productId";

    // Get total count for pagination
    $countQuery = $query;
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM ($countQuery) as count_table");
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $perPage);

    // Modify the query pagination based on perPage value
    if ($_POST['perPage'] === 'all') {
        // No LIMIT clause for 'all' option
        $query .= " ORDER BY p.productId DESC";
    } else {
        // Add pagination for specific page sizes
        $query .= " ORDER BY p.productId DESC LIMIT $perPage OFFSET $offset";
    }

    // Execute main query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the products data
    $formattedProducts = array_map(function($product) {
        return [
            'productId' => $product['productId'],
            'productName' => htmlspecialchars($product['productName']),
            'productImage' => $product['productImage'] ?? '/buyCheaper/images/no-image.png',
            'description' => htmlspecialchars($product['description'] ?? ''),
            'categoryName' => htmlspecialchars($product['categoryName'] ?? ''),
            'vendorCount' => (int)$product['vendorCount'],
            'minPrice' => $product['minPrice'] ? number_format($product['minPrice'], 2) : '0.00',
            'maxPrice' => $product['maxPrice'] ? number_format($product['maxPrice'], 2) : '0.00'
        ];
    }, $products);

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'error' => false,
        'products' => $formattedProducts,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'totalProducts' => $totalProducts
    ]);

} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}