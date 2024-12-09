<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/database.php';
include '../includes/navbar.php'; 
require '../includes/simple_html_dom.php';
require '../includes/scraper_functions.php';

// Categories and URLs for Startech
$categories = [
    'cpu' => 'https://www.startech.com.bd/component/processor',
    'gpu' => 'https://www.startech.com.bd/component/graphics-card',
    'ram' => 'https://www.startech.com.bd/component/ram',
    'power_supply' => 'https://www.startech.com.bd/component/power-supply',
    'casing' => 'https://www.startech.com.bd/component/casing',
    'cpu_cooler' => 'https://www.startech.com.bd/component/CPU-Cooler',
    'motherboard' => 'https://www.startech.com.bd/component/motherboard',
    'ssd' => 'https://www.startech.com.bd/component/SSD-Hard-Disk'
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



function getCategoryName($categoryId) {
    $categoryNames = [
        1 => 'CPU',
        2 => 'GPU',
        3 => 'RAM',
        4 => 'Power Supply',
        5 => 'Casing',
        6 => 'CPU Cooler',
        7 => 'Motherboard',
        8 => 'SSD'
    ];
    
    return $categoryNames[$categoryId] ?? 'Unknown Category';
}

function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $productUrl, $categoryId, $vendorId, $description, $brand) {
    // Step 1: Generate a standard name for the product
    $scrapedStandardName = generateStandardName($productName);

    // Step 2: Check if the product exists in the `all_products` table
    $stmt = $pdo->prepare("SELECT id FROM all_products WHERE standard_name = :standard_name AND categoryId = :categoryId");
    $stmt->execute([
        ':standard_name' => $scrapedStandardName,
        ':categoryId' => $categoryId
    ]);
    $existingAllProduct = $stmt->fetch();

    if (!$existingAllProduct) {
        // Insert into `all_products` if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO all_products (standard_name, categoryId, brand) VALUES (:standard_name, :categoryId, :brand)");
        $stmt->execute([
            ':standard_name' => $scrapedStandardName,
            ':categoryId' => $categoryId,
            ':brand' => $brand
        ]);
        $allProductId = $pdo->lastInsertId();
    } else {
        $allProductId = $existingAllProduct['id'];
    }

    // Step 3: Check if the product exists in the `products` table
    $stmt = $pdo->prepare("SELECT productId FROM products WHERE productName = :productName AND categoryId = :categoryId");
    $stmt->execute([
        ':productName' => $productName,
        ':categoryId' => $categoryId
    ]);
    $existingProduct = $stmt->fetch();

    if (!$existingProduct) {
        // Insert into `products` if it doesn't exist
        $stmt = $pdo->prepare("
            INSERT INTO products (productName, description, categoryId, productImage, category)
            VALUES (:productName, :description, :categoryId, :productImage, :category)
        ");
        $stmt->execute([
            ':productName' => $productName,
            ':description' => $description,
            ':categoryId' => $categoryId,
            ':productImage' => $productImage,
            ':category' => getCategoryName($categoryId) // Assume a helper function for category name
        ]);
        $productId = $pdo->lastInsertId();
    } else {
        // Get the existing `productId`
        $productId = $existingProduct['productId'];
    }

    // Step 4: Check if the price already exists in `vendor_prices` for the same vendor and product
    $stmt = $pdo->prepare("
        SELECT priceId FROM vendor_prices 
        WHERE productId = :productId AND vendorId = :vendorId AND price = :price
    ");
    $stmt->execute([
        ':productId' => $productId,
        ':vendorId' => $vendorId,
        ':price' => $productPrice
    ]);
    $existingPrice = $stmt->fetch();

    if (!$existingPrice) {
        // Insert into `vendor_prices` if the price doesn't exist
        $stmt = $pdo->prepare("
            INSERT INTO vendor_prices (productId, vendorId, price, productUrl, lastUpdated)
            VALUES (:productId, :vendorId, :price, :productUrl, NOW())
        ");
        $stmt->execute([
            ':productId' => $productId,
            ':vendorId' => $vendorId,
            ':price' => $productPrice,
            ':productUrl' => $productUrl
        ]);
    } else {
        // Update the existing price's lastUpdated timestamp
        $stmt = $pdo->prepare("
            UPDATE vendor_prices 
            SET lastUpdated = NOW() 
            WHERE priceId = :priceId
        ");
        $stmt->execute([':priceId' => $existingPrice['priceId']]);
    }
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
        $productCards = $xpath->query("//div[contains(@class, 'p-item')]");

        $productsFound = false;
        foreach ($productCards as $product) {
            $productNameNode = $xpath->query(".//h4[@class='p-item-name']/a", $product)->item(0);
            $productName = $productNameNode ? $productNameNode->nodeValue : 'N/A';
        
            $brand = strtok($productName, ' ');
        
            $productUrlNode = $xpath->query(".//h4[@class='p-item-name']/a", $product)->item(0);
            $productUrl = $productUrlNode ? $productUrlNode->getAttribute('href') : '#';
        
            $productImageNode = $xpath->query(".//div[contains(@class, 'p-item-img')]/a/img", $product)->item(0);
            $productImage = $productImageNode ? $productImageNode->getAttribute('src') : 'no-image.jpg';
        
            $descriptionNode = $xpath->query(".//div[@class='short-description']", $product)->item(0);
            $description = $descriptionNode ? $descriptionNode->nodeValue : 'No description';
        
            $priceNode = $xpath->query(".//div[@class='p-item-price']/span", $product)->item(0);
            $productPrice = $priceNode ? floatval(str_replace(',', '', preg_replace('/[^\d.]/', '', $priceNode->nodeValue))) : 0;
        
            // Only process products with a valid price
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
    scrapeCategory($url, $pdo, $categoryId, 1);
}
