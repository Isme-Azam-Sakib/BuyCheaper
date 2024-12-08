<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
include '../includes/navbar.php'; 
require '../includes/simple_html_dom.php';

// Categories and URLs for Skyland
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

// Fetch HTML content using cURL
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
            echo "Failed to fetch content. HTTP Status Code: $httpCode<br>";
            return false;
        }
    }
    curl_close($ch);
}

// Generate standardized product name
function generateStandardName($productName) {
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



function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand) {
    // Generate the standard name for the scraped product
    $scrapedStandardName = generateStandardName($productName);

    // Step 1: Search for potential matches in all_products
    $stmt = $pdo->prepare("SELECT id, standard_name FROM all_products WHERE categoryId = :categoryId");
    $stmt->execute([':categoryId' => $categoryId]);
    $allProducts = $stmt->fetchAll();

    $matchedProductId = null;
    foreach ($allProducts as $product) {
        $existingStandardName = $product['standard_name'];
        $existingId = $product['id'];

        if (isMatch($scrapedStandardName, $existingStandardName)) {
            $matchedProductId = $existingId;
            break;
        }
    }

    if ($matchedProductId) {
        $productId = $matchedProductId;
    } else {
        $stmt = $pdo->prepare("INSERT INTO all_products (standard_name, categoryId, brand) VALUES (:standard_name, :categoryId, :brand)");
        $stmt->execute([
            ':standard_name' => $scrapedStandardName,
            ':categoryId' => $categoryId,
            ':brand' => $brand
        ]);
        $productId = $pdo->lastInsertId();
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

    // Use a threshold for matching (e.g., 75% of the words must match)
    $threshold = 0.75;
    return ($matchedCount / count($existingKeywords)) >= $threshold;
}

// Scrape category
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
                handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand);
                $productsFound = true;
            }
        }
        $page++;
    } while ($productsFound);
}

// Loop through categories
foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    scrapeCategory($url, $pdo, $categoryId, 4); 
}
?>
