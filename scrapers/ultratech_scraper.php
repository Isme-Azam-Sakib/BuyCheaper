<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
include '../includes/navbar.php';
require '../includes/simple_html_dom.php';


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



function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand)
{
    $scrapedStandardName = generateStandardName($productName);

    // First, try to find an exact match by standard_name and categoryId
    $stmt = $pdo->prepare("SELECT id FROM all_products WHERE standard_name = :standard_name AND categoryId = :categoryId");
    $stmt->execute([
        ':standard_name' => $scrapedStandardName,
        ':categoryId' => $categoryId
    ]);
    $exactMatch = $stmt->fetch();

    if ($exactMatch) {
        $productId = $exactMatch['id'];
    } else {
        // If no exact match, look for similar products
        $stmt = $pdo->prepare("SELECT id, standard_name FROM all_products WHERE categoryId = :categoryId");
        $stmt->execute([':categoryId' => $categoryId]);
        $allProducts = $stmt->fetchAll();

        $matchedProductId = null;
        foreach ($allProducts as $product) {
            if (isMatch($scrapedStandardName, $product['standard_name'])) {
                $matchedProductId = $product['id'];
                break;
            }
        }

        if ($matchedProductId) {
            $productId = $matchedProductId;
        } else {
            // Generate a unique identifier using time to ensure uniqueness
            $timestamp = time();
            $uniqueStandardName = $scrapedStandardName . '-' . $brand . '-' . $categoryId . '-' . $timestamp;
            
            // Try to insert with retries in case of collision
            $maxRetries = 3;
            $retry = 0;
            $success = false;
            
            while (!$success && $retry < $maxRetries) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO all_products (standard_name, categoryId, brand) VALUES (:standard_name, :categoryId, :brand)");
                    $stmt->execute([
                        ':standard_name' => $uniqueStandardName,
                        ':categoryId' => $categoryId,
                        ':brand' => $brand
                    ]);
                    $success = true;
                    $productId = $pdo->lastInsertId();
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Duplicate entry
                        $timestamp = time(); // Get new timestamp
                        $uniqueStandardName = $scrapedStandardName . '-' . $brand . '-' . $categoryId . '-' . $timestamp;
                        $retry++;
                    } else {
                        throw $e; // Re-throw if it's not a duplicate entry error
                    }
                }
            }
            
            if (!$success) {
                error_log("Failed to insert product after $maxRetries retries: $productName");
                return; // Skip this product if we can't insert it
            }
        }
    }

    // Step 3: Check or add/update in products table
    $stmt = $pdo->prepare("SELECT productId FROM products WHERE productId = :productId");
    $stmt->execute([':productId' => $productId]);
    $existingProduct = $stmt->fetch();

    if ($existingProduct) {
        $stmt = $pdo->prepare("UPDATE products SET productName = :productName,  description = :description WHERE productId = :productId");
        $stmt->execute([
            ':productName' => $productName,
            ':description' => $description,
            ':productId' => $productId
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (productId, productName, categoryId, description) VALUES (:productId, :productName,  :categoryId, :description)");
        $stmt->execute([
            ':productId' => $productId,
            ':productName' => $productName,
            ':categoryId' => $categoryId,
            ':description' => $description
        ]);
    }

    // Step 4: Handle vendor prices
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

function isMatch($scrapedStandardName, $existingStandardName)
{
    $scrapedKeywords = explode(' ', $scrapedStandardName);
    $existingKeywords = explode(' ', $existingStandardName);

    $matchedCount = 0;
    foreach ($existingKeywords as $word) {
        if (in_array($word, $scrapedKeywords)) {
            $matchedCount++;
        }
    }

    $threshold = 0.85;
    return ($matchedCount / count($existingKeywords)) >= $threshold;
}


function scrapeCategory($url, $pdo, $categoryId, $vendorId)
{
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
            $productName = trim($xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->nodeValue ?? 'N/A');
            $brand = strtok($productName, ' ');
            $productUrl = trim($xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->getAttribute('href') ?? '');
            $productImage = trim($xpath->query(".//div[contains(@class, 'image')]//img", $product)->item(0)->getAttribute('src') ?? '');
            $description = trim($xpath->query(".//div[contains(@class, 'description')]", $product)->item(0)->nodeValue ?? 'No description');
            $newPrice = trim($xpath->query(".//div[contains(@class, 'price')]//span[contains(@class, 'price-new')]", $product)->item(0)->nodeValue ?? 'N/A');
            $productPrice = floatval(str_replace(',', '', $newPrice));

            // Maintain Ultratech's specific checks
            if ($productPrice == 0 || stripos($description, 'Upcoming') !== false || stripos($description, 'Out of Stock') !== false) {
                continue;
            }

            if ($productPrice > 0) {
                handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand);
                $productsFound = true;
            }
        }
        $page++;
    } while ($productsFound);
}

// Update the main loop to use correct vendor ID
foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    scrapeCategory($url, $pdo, $categoryId, 5); // Using vendor ID 5 for Ultratech
}
