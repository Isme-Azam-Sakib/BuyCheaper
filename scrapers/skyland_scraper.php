<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
include '../includes/navbar.php'; 
require '../includes/simple_html_dom.php';

// Categories and URLs for PCHouse
$categories = [
    'cpu' => 'https://www.skyland.com.bd/components/processor',
    'gpu' => 'https://www.skyland.com.bd/components/graphics-card',
    'ram' => 'https://www.skyland.com.bd/components/ram',
    'power_supply' => 'https://www.skyland.com.bd/components/power-supply',
    'casing' => 'https://www.skyland.com.bd/components/casing',
    'cpu_cooler' => 'https://www.skyland.com.bd/components/cpu-cooler',
    'motherboard' => 'https://www.skyland.com.bd/components/',
    'ssd' => 'https://www.skyland.com.bd/components/ssd'
];

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");

    $htmlContent = curl_exec($ch);
    curl_close($ch);
    return $htmlContent;
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


function scrapeCategory($url, $pdo, $category, $categoryId)
{
    $page = 1;
    $vendorId = 4; // Vendor ID for Skyland

    do {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $htmlContent = fetch_html_content($url . "?page=" . $page);

        if (!$dom->loadHTML($htmlContent)) {
            echo "Failed to parse HTML content on page $page.";
            return;
        }

        $xpath = new DOMXPath($dom);
        $products = $xpath->query("//div[contains(@class, 'product-layout')]");
        $productsFound = false;

        foreach ($products as $product) {
            // Skip out-of-stock products
            if (strpos($product->getAttribute('class'), 'out-of-stock') !== false) {
                continue;
            }

            // Extract product name
            $productNameNode = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0);
            $productName = $productNameNode ? trim($productNameNode->nodeValue) : 'N/A';
            $productUrl = $productNameNode ? $productNameNode->getAttribute('href') : '';

            // Extract product image
            $productImageNode = $xpath->query(".//div[contains(@class, 'image')]//img", $product)->item(0);
            $productImage = $productImageNode ? $productImageNode->getAttribute('src') : '';

            // Extract description
            $descriptionNode = $xpath->query(".//div[contains(@class, 'description')]", $product)->item(0);
            $description = $descriptionNode ? trim($descriptionNode->nodeValue) : 'No description';

            // Extract price
            $newPriceNode = $xpath->query(".//span[contains(@class, 'price-new')]", $product)->item(0);
            $productPrice = $newPriceNode ? floatval(str_replace(',', '', preg_replace('/[^\d.]/', '', $newPriceNode->nodeValue))) : 0;

            // Skip products with price 0 or "Upcoming"/"Out of Stock" keywords
            if ($productPrice == 0 || stripos($description, 'Upcoming') !== false || stripos($description, 'Out of Stock') !== false) {
                continue;
            }

            // Process the product and store in the database
            handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $category, $vendorId, $categoryId, $description);
            $productsFound = true; // At least one product was found and processed
        }

        $page++; // Move to the next page

    } while ($productsFound);
}


foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    echo "Scraping $category... <br>";
    scrapeCategory($url, $pdo, $category, $categoryId);
}
?>
