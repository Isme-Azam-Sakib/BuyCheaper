<?php
include '../config/database.php';
include '../includes/navbar.php'; 
require '../includes/simple_html_dom.php'; 


// Categories and their corresponding URLs in Startech
$categories = [
    'cpu' => 'https://www.startech.com.bd/component/processor',
    'gpu' => 'https://www.startech.com.bd/component/graphics-card',
    'ram' => 'https://www.startech.com.bd/component/ram',
    'power_supply' => 'https://www.startech.com.bd/component/power-supply',
    'casing' => 'https://www.startech.com.bd/component/casing',
    'cpu_cooler' => 'https://www.startech.com.bd/component/cooling-fan',
    'motherboard' => 'https://www.startech.com.bd/component/motherboard',
    'ssd' => 'https://www.startech.com.bd/component/SSD-Hard-Disk'
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




function generateUniversalIdentifier($productName, $categoryId) {
    // List of common words to exclude
    $excludeWords = ['processor', 'graphics', 'card', 'desktop', 'edition', 'cooler', 'ram', 
                     'motherboard', 'power', 'supply', 'case', 'casing', 'ssd', 'socket', 
                     'am4', 'gb', 'mhz', 'module', 'memory', 'pci', 'express'];

    // Normalize product name: lowercase, remove special characters
    $cleanedProductName = strtolower($productName);
    $cleanedProductName = preg_replace('/[^a-z0-9\s-]/', '', $cleanedProductName);
    $words = explode(' ', $cleanedProductName);

    // Filter out common words
    $filteredWords = array_diff($words, $excludeWords);
    
    // Extract the brand (first significant word) and primary model identifier
    $brand = $filteredWords[0] ?? 'generic'; // Default to 'generic' if no significant words
    $model = $filteredWords[1] ?? ''; // Take the next significant word as model identifier if it exists

    // Combine category ID, brand, and model to form SKU-like identifier
    $generateUniversalIdentifier = "{$categoryId}-{$brand}-{$model}";

    return $generateUniversalIdentifier;
}

// Function to handle database operations
function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId, $categoryId, $productUrl) {
    $universalIdentifier = generateUniversalIdentifier($productName, $category);

    $stmt = $pdo->prepare("SELECT productId FROM products WHERE universalIdentifier = :universalIdentifier");
    $stmt->execute([':universalIdentifier' => $universalIdentifier]);
    $existingProduct = $stmt->fetch();

    if ($existingProduct) {
        $productId = $existingProduct['productId'];

        $priceCheckStmt = $pdo->prepare("SELECT * FROM vendor_prices WHERE productId = :productId AND vendorId = :vendorId");
        $priceCheckStmt->execute([':productId' => $productId, ':vendorId' => $vendorId]);
        $existingPrice = $priceCheckStmt->fetch();

        if ($existingPrice) {
            $updateStmt = $pdo->prepare("UPDATE vendor_prices SET price = :price, productUrl = :productUrl WHERE productId = :productId AND vendorId = :vendorId");
            $updateStmt->execute([':price' => $productPrice, ':productUrl' => $productUrl, ':productId' => $productId, ':vendorId' => $vendorId]);
        } else {
            $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price, productUrl) VALUES (:productId, :vendorId, :price, :productUrl)");
            $insertPriceStmt->execute([':productId' => $productId, ':vendorId' => $vendorId, ':price' => $productPrice, ':productUrl' => $productUrl]);
        }
    } else {
        $insertProductStmt = $pdo->prepare("INSERT INTO products (productName, productImage, categoryId, universalIdentifier) VALUES (:productName, :productImage, :categoryId, :universalIdentifier)");
        $insertProductStmt->execute([':productName' => $productName, ':productImage' => $productImage, ':categoryId' => $categoryId, ':universalIdentifier' => $universalIdentifier]);

        $newProductId = $pdo->lastInsertId();

        $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price, productUrl) VALUES (:productId, :vendorId, :price, :productUrl)");
        $insertPriceStmt->execute([':productId' => $newProductId, ':vendorId' => $vendorId, ':price' => $productPrice, ':productUrl' => $productUrl]);
    }

    echo "Scraped Product: $productName | Price: $productPrice | Image: $productImage | URL: $productUrl | CategoryId: $categoryId <br>";
}


// Scrape the category pages
function scrapeCategory($url, $pdo, $category, $categoryId) {
    $page = 1;
    $vendorId = 1; // Assuming 1 is the vendorId for Startech

    do {
        // Get the HTML content for the current page
        $html = file_get_html($url . "?page=" . $page);
        if (!$html) {
            echo "Failed to retrieve the webpage for category: $category <br>";
            break;
        }

        $productsFound = false;

        // Loop through each product card on the page
        foreach ($html->find('.p-item') as $product) {
            // Extract the product name and URL
            $productNameElement = $product->find('.p-item-name a', 0);
            $productName = $productNameElement ? trim($productNameElement->plaintext) : "Unknown Product";
            $productUrl = $productNameElement ? $productNameElement->href : "No URL";

            // Extract the price, checking for both 'price-new' and 'p-item-price' classes
            $priceElement = $product->find('.price-new span', 0);
            if (!$priceElement) {
                $priceElement = $product->find('.p-item-price span', 0);
            }
            $productPrice = $priceElement ? preg_replace('/[^0-9]/', '', $priceElement->plaintext) : "0";

            // Extract the product image URL
            $productImageElement = $product->find('.p-item-img img', 0);
            $productImage = $productImageElement ? $productImageElement->src : "No Image";

            // Call the function to handle database operations, now including the product URL
            handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId, $categoryId, $productUrl);

            // Set flag to true if at least one product is found
            $productsFound = true;
        }

        $page++;
    } while ($productsFound); // Continue to the next page if products are found
}

// Loop through each category and scrape it
foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    echo "Scraping $category... <br>";
    scrapeCategory($url, $pdo, $category, $categoryId);
}

?>
