<?php 
include '../config/database.php';
require '../includes/simple_html_dom.php'; 

// Categories and their corresponding URLs for Ryans
$categories = [
    'cpu' => 'https://www.ryans.com/category/desktop-component-processor',
    'gpu' => 'https://www.ryans.com/category/desktop-component-graphics-card',
    'ram' => 'https://www.ryans.com/category/desktop-component-desktop-ram',
    'power_supply' => 'https://www.ryans.com/category/desktop-component-power-supply',
    'casing' => 'https://www.ryans.com/category/desktop-component-casing',
    'cpu_cooler' => 'https://www.ryans.com/category/desktop-component-cpu-cooler',
    'motherboard' => 'https://www.ryans.com/category/desktop-component-motherboard',
    'ssd' => 'https://www.ryans.com/category/internal-ssd'
];

// Map category names to category IDs
$categoryIds = [
    'cpu' => 1,
    'gpu' => 2,
    'ram' => 3,
    'power_supply' => 4,
    'casing' => 5,
    'cpu_cooler' => 6,
    'motherboard' => 7,
    'ssd' => 8
];

// Generate a universal identifier for the product
function generateUniversalIdentifier($productName, $category) {
    $cleanedProductName = strtolower(preg_replace('/\s+/', '-', $productName));
    $cleanedCategory = strtolower($category);

    return substr(md5($cleanedProductName . '-' . $cleanedCategory), 0, 12);
}

// Function to handle database operations
function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId, $categoryId) {
    // Generate the universal identifier
    $universalIdentifier = generateUniversalIdentifier($productName, $category);

    // Check if the product already exists in the products table
    $stmt = $pdo->prepare("SELECT productId FROM products WHERE universalIdentifier = :universalIdentifier");
    $stmt->execute([':universalIdentifier' => $universalIdentifier]);
    $existingProduct = $stmt->fetch();

    if ($existingProduct) {
        // Product exists, update the vendor prices if needed
        $productId = $existingProduct['productId'];

        $priceCheckStmt = $pdo->prepare("SELECT * FROM vendor_prices WHERE productId = :productId AND vendorId = :vendorId");
        $priceCheckStmt->execute([':productId' => $productId, ':vendorId' => $vendorId]);
        $existingPrice = $priceCheckStmt->fetch();

        if ($existingPrice) {
            // Update the existing price
            $updateStmt = $pdo->prepare("UPDATE vendor_prices SET price = :price WHERE productId = :productId AND vendorId = :vendorId");
            $updateStmt->execute([':price' => $productPrice, ':productId' => $productId, ':vendorId' => $vendorId]);
        } else {
            // Insert new price entry for this vendor
            $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price) VALUES (:productId, :vendorId, :price)");
            $insertPriceStmt->execute([':productId' => $productId, ':vendorId' => $vendorId, ':price' => $productPrice]);
        }
    } else {
        // Insert new product with categoryId
        $insertProductStmt = $pdo->prepare("INSERT INTO products (productName, productImage, categoryId, universalIdentifier) VALUES (:productName, :productImage, :categoryId, :universalIdentifier)");
        $insertProductStmt->execute([':productName' => $productName, ':productImage' => $productImage, ':categoryId' => $categoryId, ':universalIdentifier' => $universalIdentifier]);

        // Get the newly inserted productId
        $newProductId = $pdo->lastInsertId();

        // Insert new vendor price
        $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price) VALUES (:productId, :vendorId, :price)");
        $insertPriceStmt->execute([':productId' => $newProductId, ':vendorId' => $vendorId, ':price' => $productPrice]);
    }

    // Output to confirm the product has been scraped
    echo "Scraped Product: $productName | Price: $productPrice | Image: $productImage | CategoryId: $categoryId <br>";
}

// Scrape the category pages
function scrapeCategory($url, $pdo, $category, $categoryId) {
    $page = 1;
    $vendorId = 2; // Assuming 2 is the vendorId for Ryans

    do {
        // Get the HTML content for the current page
        $html = file_get_html($url . "?page=" . $page);
        if (!$html) {
            echo "Failed to retrieve the webpage for category: $category <br>";
            break;
        }

        $productsFound = false;

        // Loop through the products on the current page
        foreach ($html->find('.category-single-product') as $product) {
            // Safely extract product name
            $productNameElement = $product->find('.grid-view-text a', 0);
            $productName = $productNameElement ? trim($productNameElement->plaintext) : null;
            
            // Safely extract product price
            $productPriceElement = $product->find('.pr-text', 0);
            $productPrice = $productPriceElement ? preg_replace('/[^0-9]/', '', $productPriceElement->plaintext) : null;

            // Safely extract product image
            $productImageElement = $product->find('.image-box img', 0);
            $productImage = $productImageElement ? $productImageElement->src : null;

            // If essential data is missing, skip this product
            if (!$productName || !$productPrice || !$productImage) {
                continue;
            }

            // Call the function to handle database operations
            handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId, $categoryId);

            $productsFound = true;
        }

        // Check if the next page link exists to avoid scraping the same page
        $nextPageElement = $html->find('.pagination a.next', 0);
        $hasNextPage = $nextPageElement ? true : false;

        $page++;
    } while ($productsFound && $hasNextPage); // Continue to the next page if products are found and next page exists
}

// Loop through each category and scrape it
foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    echo "Scraping $category... <br>";
    scrapeCategory($url, $pdo, $category, $categoryId);
}
?>
