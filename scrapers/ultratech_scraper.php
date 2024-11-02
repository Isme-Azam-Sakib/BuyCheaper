<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
include '../includes/navbar.php'; 
require '../includes/simple_html_dom.php';

// Categories and their corresponding URLs in Ultratech
$categories = [
    'cpu' => 'https://www.ultratech.com.bd/processor',
    'gpu' => 'https://www.ultratech.com.bd/graphics-card',
    'ram' => 'https://www.ultratech.com.bd/ram',
    'power_supply' => 'https://www.ultratech.com.bd/power-supply',
    'casing' => 'https://www.ultratech.com.bd/casing',
    'cpu_cooler' => 'https://www.ultratech.com.bd/cpu-cooler',
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

function generateUniversalIdentifier($productName, $categoryId) {
    // Common words to exclude
    $excludeWords = ['processor', 'graphics', 'card', 'desktop', 'edition', 'cooler', 'ram', 
                     'motherboard', 'power', 'supply', 'case', 'casing', 'ssd', 'socket', 
                     'am4', 'gb', 'mhz', 'module', 'memory', 'pci', 'express'];

    $cleanedProductName = strtolower($productName);
    $cleanedProductName = preg_replace('/[^a-z0-9\s-]/', '', $cleanedProductName);
    $words = explode(' ', $cleanedProductName);

    $filteredWords = array_diff($words, $excludeWords);
    $brand = $filteredWords[0] ?? 'generic';
    $model = $filteredWords[1] ?? '';

    return "{$categoryId}-{$brand}-{$model}";
}

function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $category, $vendorId, $categoryId){
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
            $updateStmt = $pdo->prepare("UPDATE vendor_prices SET price = :price WHERE productId = :productId AND vendorId = :vendorId");
            $updateStmt->execute([':price' => $productPrice, ':productId' => $productId, ':vendorId' => $vendorId]);
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
    echo "Scraped Product: $productName | Price: $productPrice | URL: $productUrl <br>";
}

function scrapeCategory($url, $pdo, $category, $categoryId){
    $page = 1;
    $vendorId = 5;

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
            $productPrice = str_replace(',', '', $newPrice);
            $productPrice = floatval($productPrice);
            if ($productPrice == 0 || stripos($description, 'Upcoming') !== false || stripos($description, 'Out of Stock') !== false) {
                continue;
            }
            handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $category, $vendorId, $categoryId);
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
?>
