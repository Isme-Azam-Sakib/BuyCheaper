<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
include '../includes/navbar.php'; 
require '../includes/simple_html_dom.php';

// Categories and URLs for PCHouse
$categories = [
    'cpu' => 'https://www.pchouse.com.bd/processor',
    'gpu' => 'https://www.pchouse.com.bd/graphics-card',
    'ram' => 'https://www.pchouse.com.bd/desktop-ram',
    'power_supply' => 'https://www.pchouse.com.bd/power-supply',
    'casing' => 'https://www.pchouse.com.bd/computer-case',
    'cpu_cooler' => 'https://www.pchouse.com.bd/cpu-cooler',
    'motherboard' => 'https://www.pchouse.com.bd/motherboard',
    'ssd' => 'https://www.pchouse.com.bd/internal-ssd'
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

function fetch_html_content($url)
{
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
            echo "Failed to fetch content. HTTP Status Code: $httpCode<br>";
            return false;
        }
    }
    curl_close($ch);
}


function generateStandardName($productName)
{
    $standard_name = strtolower($productName);
    $standard_name = preg_replace('/[^a-z0-9\s\-\/\.]/', '', $standard_name);
    $standard_name = str_replace(['-', '/', '|'], ' ', $standard_name);
    $keywords = [
        'gaming', 'processor', 'gen', 'series', 'edition', 'liquid', 
        'cooler', 'new', 'latest', 'ultra', 'pro', 'max', 'rgb', 'mm', 'aio', 
        'desktop', 'laptop', 'graphics', 'card', 'cool', 'power', 'supply', 'ram', 
        'ssd', 'fps'
    ];
    foreach ($keywords as $word) {
        $standard_name = preg_replace('/\b' . preg_quote($word, '/') . '\b/', '', $standard_name);
    }

    $standard_name = preg_replace('/(\d+\s?(gb|tb|hz|mhz))/', ' $1 ', $standard_name);
    $standard_name = trim(preg_replace('/\s+/', ' ', $standard_name));

    return $standard_name;
}

function handleDatabaseOperations($pdo, $productName, $productPrice, $productUrl, $categoryId, $vendorId, $description, $brand, $productImage) {
    // Generate the standard name for the scraped product
    $scrapedStandardName = generateStandardName($productName);

    try {
        // Try to insert new product first
        $stmt = $pdo->prepare("INSERT INTO all_products (standard_name, categoryId, brand) VALUES (:standard_name, :categoryId, :brand)");
        $stmt->execute([
            ':standard_name' => $scrapedStandardName,
            ':categoryId' => $categoryId,
            ':brand' => $brand
        ]);
        $productId = $pdo->lastInsertId();
    } catch (PDOException $e) {
        // If duplicate entry, fetch the existing product ID
        if ($e->getCode() == '23000') { // Integrity constraint violation
            $stmt = $pdo->prepare("SELECT id FROM all_products WHERE standard_name = :standard_name");
            $stmt->execute([':standard_name' => $scrapedStandardName]);
            $productId = $stmt->fetchColumn();
        } else {
            throw $e; // Re-throw if it's a different error
        }
    }

    $stmt = $pdo->prepare("SELECT productId FROM products WHERE productId = :productId");
    $stmt->execute([':productId' => $productId]);
    $existingProduct = $stmt->fetch();

    if ($existingProduct) {
        $stmt = $pdo->prepare("UPDATE products SET productName = :productName, description = :description WHERE productId = :productId");
        $stmt->execute([
            ':productName' => $productName,
            ':description' => $description,
            ':productId' => $productId
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (productId, productName, productImage, categoryId, description) VALUES (:productId, :productName, :productImage, :categoryId, :description)");
        $stmt->execute([
            ':productId' => $productId,
            ':productName' => $productName,
            ':productImage' => $productImage,
            ':categoryId' => $categoryId,
            ':description' => $description
        ]);
    }

    $stmt = $pdo->prepare("SELECT * FROM vendor_prices WHERE productId = :productId AND vendorId = :vendorId");
    $stmt->execute([':productId' => $productId, ':vendorId' => $vendorId]);
    $existingPrice = $stmt->fetch();

    if ($existingPrice) {
        $stmt = $pdo->prepare("UPDATE vendor_prices SET price = :price, productUrl = :productUrl, lastUpdated = NOW() WHERE productId = :productId AND vendorId = :vendorId");
        $stmt->execute([
            ':price' => $productPrice,
            ':productUrl' => $productUrl,
            ':productId' => $productId,
            ':vendorId' => $vendorId
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price, productUrl, lastUpdated) VALUES (:productId, :vendorId, :price, :productUrl, NOW())");
        $stmt->execute([
            ':productId' => $productId,
            ':vendorId' => $vendorId,
            ':price' => $productPrice,
            ':productUrl' => $productUrl
        ]);
    }
}

function isMatch($scrapedStandardName, $existingStandardName) {
    $scrapedKeywords = explode(' ', $scrapedStandardName);
    $existingKeywords = explode(' ', $existingStandardName);

    // Check if at least 75% of the existing keywords appear in the scraped product
    $matchedCount = 0;
    foreach ($existingKeywords as $word) {
        if (in_array($word, $scrapedKeywords)) {
            $matchedCount++;
        }
    }
    
    $threshold = 0.85;
    return ($matchedCount / count($existingKeywords)) >= $threshold;
}


function scrapeCategory($url, $pdo, $categoryId, $vendorId) {
    $page = 1;
    do {
        $htmlContent = fetch_html_content($url . "?page=" . $page);
        if (!$htmlContent) break;

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($htmlContent);
        $xpath = new DOMXPath($dom);
        $products = $xpath->query("//div[contains(@class, 'product-layout')]");

        $productsFound = false;
        foreach ($products as $product) {
            if (strpos($product->getAttribute('class'), 'out-of-stock') !== false) continue;

            $productName = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->nodeValue ?? 'N/A';
            $brand = strtok($productName, ' ');
            $productUrl = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->getAttribute('href');
            $productImage = $xpath->query(".//div[contains(@class, 'image')]//img", $product)->item(0)->getAttribute('src');
            $description = $xpath->query(".//div[contains(@class, 'description')]", $product)->item(0)->nodeValue ?? 'No description';
            $priceNode = $xpath->query(".//div[contains(@class, 'price')]//span[contains(@class, 'price-new')]", $product)->item(0);
            $productPrice = $priceNode ? floatval(str_replace(',', '', preg_replace('/[^\d.]/', '', $priceNode->nodeValue))) : 0;

            if ($productPrice > 0) {
                handleDatabaseOperations($pdo, $productName, $productPrice, $productUrl, $categoryId, $vendorId, $description, $brand, $productImage);
                $productsFound = true;
            }
        }
        $page++;
    } while ($productsFound);
}



foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    scrapeCategory($url, $pdo, $categoryId, 6); 
}
?>

