<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
require '../includes/simple_html_dom.php';

// Categories and their corresponding URLs in techland
$categories = [
    'cpu' => 'https://www.ultratech.com.bd/processor',
    'gpu' => 'https://www.ultratech.com.bd/graphics-card',
    'ram' => 'https://www.ultratech.com.bd/ram',
    'power_supply' => 'https://www.ultratech.com.bd/power-supply',
    'casing' => 'https://www.ultratech.com.bd/casing',
    'cpu_cooler' => 'https://www.ultratech.com.bd//cpu-cooler',
    'motherboard' => 'https://www.ultratech.com.bd/amd-motherboard',
    'ssd' => 'https://www.ultratech.com.bd/ssd'
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

function fetch_html_content($url){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36");

    $htmlContent = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
        return false;
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 200) {
            return $htmlContent;
        } else {
            echo "Failed to fetch content. HTTP Status Code: $httpCode";
            return false;
        }
    }
    curl_close($ch);
}

// function generateUniversalIdentifier($productName, $category) {
//     $cleanedProductName = strtolower(preg_replace('/\s+/', '-', $productName));
//     $cleanedCategory = strtolower($category);

//     $universalIdentifier = substr(md5($cleanedProductName . '-' . $cleanedCategory), 0, 12);

//     return $universalIdentifier;
// }

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



function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId, $categoryId){
    $universalIdentifier = generateUniversalIdentifier($productName, $category);
    $stmt = $pdo->prepare("SELECT productId FROM products WHERE universalIdentifier = :universalIdentifier");
    $stmt->execute([':universalIdentifier' => $universalIdentifier]);
    $existingProduct = $stmt->fetch();

    if ($existingProduct){
        $productId = $existingProduct['productId'];
        $priceCheckStmt = $pdo->prepare("SELECT * FROM vendor_prices WHERE productId = :productId AND vendorId = :vendorId");
        $priceCheckStmt->execute([':productId' => $productId, ':vendorId' => $vendorId]);
        $existingPrice = $priceCheckStmt->fetch();
        if ($existingPrice) {
            // Update the existing price if the product is already listed for this vendor
            $updateStmt = $pdo->prepare("UPDATE vendor_prices SET price = :price WHERE productId = :productId AND vendorId = :vendorId");
            $updateStmt->execute([':price' => $productPrice, ':productId' => $productId, ':vendorId' => $vendorId]);
        } else {
            // Insert new price entry for this vendor
            $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price) VALUES (:productId, :vendorId, :price)");
            $insertPriceStmt->execute([':productId' => $productId, ':vendorId' => $vendorId, ':price' => $productPrice]);
        }
    } else {
        $insertProductStmt = $pdo->prepare("INSERT INTO products (productName, productImage, categoryId, universalIdentifier) VALUES (:productName, :productImage, :categoryId, :universalIdentifier)");
        $insertProductStmt->execute([':productName' => $productName, ':productImage' => $productImage, ':categoryId' => $categoryId, ':universalIdentifier' => $universalIdentifier]);

        // Get the newly inserted productId
        $newProductId = $pdo->lastInsertId();

        // Insert new vendor price
        $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price) VALUES (:productId, :vendorId, :price)");
        $insertPriceStmt->execute([':productId' => $newProductId, ':vendorId' => $vendorId, ':price' => $productPrice]);
    }
    echo "Scraped Product: $productName | Price: $productPrice  <br>";
}

function scrapeCategory($url, $pdo, $category, $categoryId)
{
    $page = 1;
    $vendorId = 5;

    do {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Suppress parsing errors

        $htmlContent = fetch_html_content($url . "?page=" . $page);

        if (!$dom->loadHTML($htmlContent)) {
            echo "Failed to parse HTML content.";
            return;
        }

        $xpath = new DOMXPath($dom);
        $products = $xpath->query("//div[contains(@class, 'product-layout')]");

        $productsFound = false;
        foreach ($products as $product) {
            $productName = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->nodeValue ?? 'N/A';
            $productUrl = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->getAttribute('href') ?? '';
            $productImage = $xpath->query(".//div[contains(@class, 'image')]//img", $product)->item(0)->getAttribute('src') ?? '';
            $description = $xpath->query(".//div[contains(@class, 'description')]", $product)->item(0)->nodeValue ?? 'No description';
            $newPrice = $xpath->query(".//div[contains(@class, 'price')]//span[contains(@class, 'price-new')]", $product)->item(0)->nodeValue ?? 'N/A';
            // $oldPrice = $xpath->query(".//div[contains(@class, 'price')]//span[contains(@class, 'price-old')]", $product)->item(0)->nodeValue ?? 'N/A';
            $productPrice = str_replace(',', '', $newPrice);
            $productPrice = floatval($productPrice);
            if ($productPrice == 0 || stripos($description, 'Upcoming') !== false || stripos($description, 'Out of Stock') !== false) {
                continue; // Skip this product
            }
            handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId, $categoryId);
            $productsFound = true;
        }

        $page++;
    } while ($productsFound);
}


foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    echo "Scraping $category... <br>";
    scrapeCategory($url, $pdo, $category, $categoryId);
}
