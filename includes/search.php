<?php
include '../config/database.php';

// Check if the request includes 'query'
if (isset($_POST['query']) && !empty($_POST['query'])) {
    $search = "%" . $_POST['query'] . "%";

    // Determine if the request is for product comparison or regular search
    $isComparison = isset($_POST['comparison']) && $_POST['comparison'] === 'true';

    // Prepare the query
    $stmt = $pdo->prepare("
        SELECT 
            p.productId, 
            p.productName AS name, 
            p.productImage AS image, 
            MIN(vp.price) AS lowestPrice 
        FROM products p
        LEFT JOIN vendor_prices vp ON p.productId = vp.productId
        WHERE p.productName LIKE :search
        GROUP BY p.productId
        LIMIT 10
    ");
    $stmt->execute([':search' => $search]);
    $results = $stmt->fetchAll();

    if ($results) {
        if ($isComparison) {
            // For comparison page, return data as JSON
            header('Content-Type: application/json');
            echo json_encode($results);
        } else {
            // For home page, render HTML for live search results
            foreach ($results as $result) {
                echo '
                <div class="search-result" onclick="redirectToProduct(' . htmlspecialchars($result['productId']) . ')">
                    <img src="' . htmlspecialchars($result['image']) . '" alt="' . htmlspecialchars($result['name']) . '">
                    <span>' . htmlspecialchars($result['name']) . '</span>
                </div>';
            }
        }
    } else {
        if ($isComparison) {
            // JSON response for no results in comparison page
            echo json_encode(['message' => 'No results found']);
        } else {
            // HTML response for no results in home page
            echo '<p>No results found</p>';
        }
    }
} else {
    echo 'Invalid request.';
}
?>
