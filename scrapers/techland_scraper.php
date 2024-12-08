<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// include '../config/database.php';
// include '../includes/navbar.php'; 
// require '../includes/simple_html_dom.php';

// $categories = [
//     'cpu' => 'https://www.techlandbd.com/pc-components/processor',
//     'gpu' => 'https://www.techlandbd.com/pc-components/graphics-card',
//     'ram' => 'https://www.techlandbd.com/pc-components/shop-desktop-ram',
//     'power_supply' => 'https://www.techlandbd.com/pc-components/power-supply',
//     'casing' => 'https://www.techlandbd.com/pc-components/computer-case',
//     'cpu_cooler' => 'https://www.techlandbd.com/pc-components/cpu-cooler',
//     'motherboard' => 'https://www.techlandbd.com/pc-components/motherboard',
//     'ssd' => 'https://www.techlandbd.com/pc-components/solid-state-drive'
// ];

// $categoryIds = [
//     'cpu' => 1,
//     'gpu' => 2,
//     'ram' => 3,
//     'power_supply' => 4,
//     'casing' => 5,
//     'cpu_cooler' => 6,
//     'motherboard' => 7,
//     'ssd' => 8
// ];

// function fetch_html_content($url){
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 60);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
//     $htmlContent = curl_exec($ch);
//     if (curl_errno($ch)) {
//         echo "cURL Error: " . curl_error($ch);
//         return false;
//     } else {
//         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//         if ($httpCode == 200) {
//             return $htmlContent;
//         } else {
//             echo "Failed to fetch content. HTTP Status Code: $httpCode<br>";
//             return false;
//         }
//     }
//     curl_close($ch);
// }

// function generateStandardName($productName) {
//     $standard_name = strtolower($productName);
//     $standard_name = preg_replace('/[^a-z0-9\s\-\/\.]/', '', $standard_name);
//     $standard_name = str_replace(['-', '/', '|'], ' ', $standard_name);
//     $keywords = [
//         'gaming', 'processor', 'gen', 'series', 'edition', 'liquid', 
//         'cooler', 'new', 'latest', 'ultra', 'pro', 'max', 'rgb', 'mm', 'aio', 
//         'desktop', 'laptop', 'graphics', 'card', 'cool', 'power', 'supply', 'ram', 
//         'ssd', 'fps'
//     ];
//     foreach ($keywords as $word) {
//         $standard_name = preg_replace('/\b' . preg_quote($word, '/') . '\b/', '', $standard_name);
//     }

//     $standard_name = preg_replace('/(\d+\s?(gb|tb|hz|mhz))/', ' $1 ', $standard_name);
//     $standard_name = trim(preg_replace('/\s+/', ' ', $standard_name));

//     return $standard_name;
// }



// function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand) {
//     // Generate the standard name for the scraped product
//     $scrapedStandardName = generateStandardName($productName);

//     // Step 1: Search for potential matches in all_products
//     $stmt = $pdo->prepare("SELECT id, standard_name FROM all_products WHERE categoryId = :categoryId");
//     $stmt->execute([':categoryId' => $categoryId]);
//     $allProducts = $stmt->fetchAll();

//     $matchedProductId = null;
//     foreach ($allProducts as $product) {
//         $existingStandardName = $product['standard_name'];
//         $existingId = $product['id'];

//         // Check if the scraped standard name contains terms from the existing standard name
//         if (isMatch($scrapedStandardName, $existingStandardName)) {
//             $matchedProductId = $existingId;
//             break;
//         }
//     }

//     // Step 2: Handle products in all_products and products tables
//     if ($matchedProductId) {
//         $productId = $matchedProductId;
//     } else {
//         // If no match, insert new product into all_products
//         $stmt = $pdo->prepare("INSERT INTO all_products (standard_name, categoryId, brand) VALUES (:standard_name, :categoryId, :brand)");
//         $stmt->execute([
//             ':standard_name' => $scrapedStandardName,
//             ':categoryId' => $categoryId,
//             ':brand' => $brand
//         ]);
//         $productId = $pdo->lastInsertId();
//     }

//     // Step 3: Check or add/update in products table
//     $stmt = $pdo->prepare("SELECT productId FROM products WHERE productId = :productId");
//     $stmt->execute([':productId' => $productId]);
//     $existingProduct = $stmt->fetch();

//     if ($existingProduct) {
//         $stmt = $pdo->prepare("UPDATE products SET productName = :productName, productImage = :productImage, description = :description WHERE productId = :productId");
//         $stmt->execute([
//             ':productName' => $productName,
//             ':productImage' => $productImage,
//             ':description' => $description,
//             ':productId' => $productId
//         ]);
//     } else {
//         $stmt = $pdo->prepare("INSERT INTO products (productId, productName, productImage, categoryId, description) VALUES (:productId, :productName, :productImage, :categoryId, :description)");
//         $stmt->execute([
//             ':productId' => $productId,
//             ':productName' => $productName,
//             ':productImage' => $productImage,
//             ':categoryId' => $categoryId,
//             ':description' => $description
//         ]);
//     }

//     // Step 4: Handle vendor prices
//     $stmt = $pdo->prepare("SELECT * FROM vendor_prices WHERE productId = :productId AND vendorId = :vendorId");
//     $stmt->execute([':productId' => $productId, ':vendorId' => $vendorId]);
//     $existingPrice = $stmt->fetch();

//     if ($existingPrice) {
//         $stmt = $pdo->prepare("UPDATE vendor_prices SET price = :price, productUrl = :productUrl, lastUpdated = NOW() WHERE productId = :productId AND vendorId = :vendorId");
//         $stmt->execute([
//             ':price' => $productPrice,
//             ':productUrl' => $productUrl,
//             ':productId' => $productId,
//             ':vendorId' => $vendorId
//         ]);
//     } else {
//         $stmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price, productUrl, lastUpdated) VALUES (:productId, :vendorId, :price, :productUrl, NOW())");
//         $stmt->execute([
//             ':productId' => $productId,
//             ':vendorId' => $vendorId,
//             ':price' => $productPrice,
//             ':productUrl' => $productUrl
//         ]);
//     }
// }

// function isMatch($scrapedStandardName, $existingStandardName) {
//     $scrapedKeywords = explode(' ', $scrapedStandardName);
//     $existingKeywords = explode(' ', $existingStandardName);

//     // Check if at least 75% of the existing keywords appear in the scraped product
//     $matchedCount = 0;
//     foreach ($existingKeywords as $word) {
//         if (in_array($word, $scrapedKeywords)) {
//             $matchedCount++;
//         }
//     }

//     $threshold = 0.9;
//     return ($matchedCount / count($existingKeywords)) >= $threshold;
// }



// // Scrape categories
// function scrapeCategory($url, $pdo, $categoryId, $vendorId) {
//     $page = 1;
//     do {
//         $htmlContent = fetch_html_content($url . "?page=" . $page);
//         if (!$htmlContent) break;

//         $dom = new DOMDocument();
//         libxml_use_internal_errors(true);
//         $dom->loadHTML($htmlContent);
//         $xpath = new DOMXPath($dom);
//         $products = $xpath->query("//div[contains(@class, 'product-layout')]");

//         $productsFound = false;
//         foreach ($products as $product) {
//             if (strpos($product->getAttribute('class'), 'out-of-stock') !== false) continue;

//             $productName = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->nodeValue ?? 'N/A';
//             $brand = strtok($productName, ' ');
//             $productUrl = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->getAttribute('href') ?? '';
//             $productImage = $xpath->query(".//div[contains(@class, 'image')]//img", $product)->item(0)->getAttribute('src') ?? '';
//             $description = $xpath->query(".//div[contains(@class, 'description')]", $product)->item(0)->nodeValue ?? 'No description';
//             $newPrice = $xpath->query(".//div[contains(@class, 'price')]//span[contains(@class, 'price-new')]", $product)->item(0)->nodeValue ?? 'N/A';
//             $productPrice = floatval(str_replace(',', '', $newPrice));

//             if ($productPrice > 0) {
//                 handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand);
//                 $productsFound = true;
//             }
//         }
//         $page++;
//     } while ($productsFound);
// }

// foreach ($categories as $category => $url) {
//     $categoryId = $categoryIds[$category];
//     scrapeCategory($url, $pdo, $categoryId, 3);
// }

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../config/database.php';
include '../includes/navbar.php';
require '../includes/simple_html_dom.php';

// Category URLs
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
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode == 200) {
        return $htmlContent;
    } else {
        echo "Failed to fetch content. HTTP Status Code: $httpCode<br>";
        return false;
    }
}

function normalizeProductName($name)
{
    // Remove unnecessary keywords and normalize spacing
    $patterns = [
        '/\bwith\b/i',
        '/\bfor\b/i',
        '/\bthe\b/i',
        '/[^\w\s]/' // Remove special characters
    ];
    $name = preg_replace($patterns, '', $name);
    return strtolower(trim($name));
}


// Extract Brand and Model from Product Name
function extractComponents($productName)
{
    $brands = ['Gigabyte', 'Asus', 'MSI', 'AMD', 'Intel', 'Western Digital', 'Corsair', 'Cooler Master'];
    $modelPatterns = '/\b(RTX\s?\d+|GTX\s?\d+|Ryzen\s?\d+|Core\s?i\d+|SN\d+|RX\s?\d+|Z\d+|B\d+|H\d+)\b/i';

    $brand = '';
    $model = '';

    foreach ($brands as $b) {
        if (stripos($productName, $b) !== false) {
            $brand = $b;
            break;
        }
    }

    if (preg_match($modelPatterns, $productName, $matches)) {
        $model = $matches[0];
    }

    return [
        'brand' => $brand,
        'model' => $model,
    ];
}

// Search for a Standard Name in all_products
function searchStandardName($brand, $model, $pdo)
{
    $normalizedBrand = normalizeProductName($brand);
    $normalizedModel = normalizeProductName($model);

    $query = "
        SELECT id FROM all_products
        WHERE standard_name LIKE :brand
          AND standard_name LIKE :model
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':brand' => '%' . $normalizedBrand . '%',
        ':model' => '%' . $normalizedModel . '%',
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
}


function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand, $model)
{
    $normalizedBrand = normalizeProductName($brand);
    $normalizedModel = normalizeProductName($model);
    $normalizedName = $normalizedBrand . ' ' . $normalizedModel;

    // Step 1: Search for existing product in all_products
    $existingProductId = searchStandardName($normalizedBrand, $normalizedModel, $pdo);

    if (!$existingProductId) {
        // Step 2: Insert into all_products
        $stmt = $pdo->prepare("INSERT INTO all_products (standard_name, categoryId, brand) VALUES (:standard_name, :categoryId, :brand)");
        $stmt->execute([
            ':standard_name' => $normalizedName,
            ':categoryId' => $categoryId,
            ':brand' => $brand,
        ]);
        $existingProductId = $pdo->lastInsertId();
    }

    $productNameNormalized = normalizeProductName($productName);

    $stmt = $pdo->prepare("INSERT INTO products (productId, productName, productImage, categoryId, description) 
    VALUES (:productId, :productName, :productImage, :categoryId, :description) 
    ON DUPLICATE KEY UPDATE productName = :productName, productImage = :productImage, description = :description");
    $stmt->execute([
        ':productId' => $existingProductId,
        ':productName' => $productNameNormalized,
        ':productImage' => $productImage,
        ':categoryId' => $categoryId,
        ':description' => $description
    ]);


    // Step 4: Insert or Update vendor_prices table
    $stmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price, productUrl, lastUpdated) 
        VALUES (:productId, :vendorId, :price, :productUrl, NOW()) 
        ON DUPLICATE KEY UPDATE price = :price, productUrl = :productUrl, lastUpdated = NOW()");
    $stmt->execute([
        ':productId' => $existingProductId,
        ':vendorId' => $vendorId,
        ':price' => $productPrice,
        ':productUrl' => $productUrl
    ]);
}


// Scrape Category
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
            if (strpos($product->getAttribute('class'), 'out-of-stock') !== false) continue;

            $productName = normalizeProductName($xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->nodeValue ?? 'N/A');
            $productUrl = $xpath->query(".//div[contains(@class, 'name')]/a", $product)->item(0)->getAttribute('href') ?? '';
            $productImage = $xpath->query(".//div[contains(@class, 'image')]//img", $product)->item(0)->getAttribute('src') ?? '';
            $description = $xpath->query(".//div[contains(@class, 'description')]", $product)->item(0)->nodeValue ?? 'No description';
            $newPrice = $xpath->query(".//div[contains(@class, 'price')]//span[contains(@class, 'price-new')]", $product)->item(0)->nodeValue ?? 'N/A';
            $productPrice = floatval(str_replace(',', '', $newPrice));

            if ($productPrice > 0) {
                $components = extractComponents($productName);
                $brand = $components['brand'];
                $model = $components['model'];

                handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand, $model);

                $productsFound = true;
            }
        }
        $page++;
    } while ($productsFound);
}

// Loop Through Categories
foreach ($categories as $category => $url) {
    $categoryId = $categoryIds[$category];
    scrapeCategory($url, $pdo, $categoryId, 3);
}
