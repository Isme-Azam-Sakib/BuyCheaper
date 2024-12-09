<?php
include '../../config/database.php';
include '../includes/auth.php';

try {
    $productId = $_POST['productId'] ?? null;
    
    if (!$productId) {
        throw new Exception('Product ID is required');
    }

    // Get the first vendor URL for the product
    $query = "
        SELECT vp.productUrl
        FROM vendor_prices vp
        WHERE vp.productId = :productId
        ORDER BY vp.price ASC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':productId' => $productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result || !$result['productUrl']) {
        throw new Exception('No vendor URL found for this product');
    }

    // Get the webpage content
    $html = file_get_contents($result['productUrl']);
    if (!$html) {
        throw new Exception('Failed to fetch vendor page');
    }

    // Create a DOM parser
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);

    // Try different common image selectors
    $imageSelectors = [
        '//meta[@property="og:image"]/@content',
        '//img[@id="product-image"]/@src',
        '//img[@class="product-image"]/@src',
        '//div[contains(@class, "product-image")]//img/@src',
        '//div[contains(@class, "main-image")]//img/@src'
    ];

    $imageUrl = null;
    foreach ($imageSelectors as $selector) {
        $nodes = $xpath->query($selector);
        if ($nodes->length > 0) {
            $imageUrl = $nodes->item(0)->nodeValue;
            break;
        }
    }

    if (!$imageUrl) {
        throw new Exception('No suitable image found on vendor page');
    }

    // Make sure we have an absolute URL
    if (strpos($imageUrl, 'http') !== 0) {
        $parsedUrl = parse_url($result['productUrl']);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        $imageUrl = $baseUrl . ($imageUrl[0] === '/' ? '' : '/') . $imageUrl;
    }

    // Download and save the image
    $imageData = file_get_contents($imageUrl);
    if (!$imageData) {
        throw new Exception('Failed to download image');
    }

    $uploadDir = '../../images/products/';
    $fileName = uniqid() . '_' . basename($imageUrl);
    $uploadFile = $uploadDir . $fileName;

    if (!file_put_contents($uploadFile, $imageData)) {
        throw new Exception('Failed to save image');
    }

    // Update the product's image in the database
    $updateQuery = "
        UPDATE products 
        SET productImage = :productImage 
        WHERE productId = :productId
    ";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        ':productId' => $productId,
        ':productImage' => '/buyCheaper/images/products/' . $fileName
    ]);

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'error' => false,
        'message' => 'Image updated successfully',
        'imagePath' => '/buyCheaper/images/products/' . $fileName
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 