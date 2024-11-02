<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
include '../includes/navbar.php'; 
require '../includes/simple_html_dom.php';

// Categories and URLs for Techland
$categories = [
    'cpu' => 'https://www.techlandbd.com/pc-components/processor',
    'gpu' => 'https://www.techlandbd.com/pc-components/graphics-card',
    'ram' => 'https://www.techlandbd.com/pc-components/shop-desktop-ram',
    'power_supply' => 'https://www.techlandbd.com/pc-components/power-supply',
    'casing' => 'https://www.techlandbd.com/pc-components/computer-case',
    'cpu_cooler' => 'https://www.techlandbd.com/pc-components/cpu-cooler',
    'motherboard' => 'https://www.techlandbd.com/pc-components/motherboard',
    'ssd' => 'https://www.techlandbd.com/pc-components/solid-state-drive'
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

// Fetch content using cURL
function fetch_html_content($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");

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

// Generate universal identifier
function generateUniversalIdentifier($productName, $categoryId) {
    // List of common words to exclude
    $excludeWords = ['processor', 'graphics', 'card', 'desktop', 'edition', 'cooler', 'ram', 'motherboard', 'power', 'supply', 'case', 'casing', 'ssd', 'socket', 'am4', 'gb', 'mhz', 'module', 'memory', 'pci', 'express'];
    $cleanedProductName = strtolower(preg_replace('/[^a-z0-9\s-]/', '', $productName));
    $words = array_diff(explode(' ', $cleanedProductName), $excludeWords);
    $brand = $words[0] ?? 'generic';
    $model = $words[1] ?? '';
    return "{$categoryId}-{$brand}-{$model}";
}

// Database operations
function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $category, $vendorId, $categoryId, $description){
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
            // Update the existing price and URL for this vendor
            $updateStmt = $pdo->prepare("UPDATE vendor_prices SET price = :price, productUrl = :productUrl WHERE productId = :productId AND vendorId = :vendorId");
            $updateStmt->execute([':price' => $productPrice, ':productId' => $productId, ':vendorId' => $vendorId, ':productUrl' => $productUrl]);
        } else {
            // Insert new price entry and URL for this vendor
            $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price, productUrl) VALUES (:productId, :vendorId, :price, :productUrl)");
            $insertPriceStmt->execute([':productId' => $productId, ':vendorId' => $vendorId, ':price' => $productPrice, ':productUrl' => $productUrl]);
        }
    } else {
        $insertProductStmt = $pdo->prepare("INSERT INTO products (productName, productImage, categoryId, universalIdentifier, description) VALUES (:productName, :productImage, :categoryId, :universalIdentifier, :description)");
        $insertProductStmt->execute([':productName' => $productName, ':productImage' => $productImage, ':categoryId' => $categoryId, ':universalIdentifier' => $universalIdentifier, ':description' => $description]);

        // Get the newly inserted productId
        $newProductId = $pdo->lastInsertId();

        // Insert new vendor price with URL
        $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price, productUrl) VALUES (:productId, :vendorId, :price, :productUrl)");
        $insertPriceStmt->execute([':productId' => $newProductId, ':vendorId' => $vendorId, ':price' => $productPrice, ':productUrl' => $productUrl]);
    }
    echo "Scraped Product: $productName | Price: $productPrice | URL: $productUrl <br>";
}


// Scrape categories
function scrapeCategory($url, $pdo, $category, $categoryId)
{
    $page = 1;
    $vendorId = 3;

    do {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
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
            $productPrice = floatval(str_replace(',', '', $newPrice));
            
            if ($productPrice == 0 || stripos($description, 'Upcoming') !== false || stripos($description, 'Out of Stock') !== false) {
                continue;
            }

            handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $category, $vendorId, $categoryId, $description);
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
