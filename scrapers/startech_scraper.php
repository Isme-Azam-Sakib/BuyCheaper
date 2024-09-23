<?php
include '../config/database.php';
require '../includes/simple_html_dom.php'; 

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

function generateUniversalIdentifier($productName, $category) {
    $cleanedProductName = strtolower(preg_replace('/\s+/', '-', $productName));
    $cleanedCategory = strtolower($category);

    $universalIdentifier = substr(md5($cleanedProductName . '-' . $cleanedCategory), 0, 12);

    return $universalIdentifier;
}

function handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId) {
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
            $updateStmt = $pdo->prepare("UPDATE vendor_prices SET price = :price WHERE productId = :productId AND vendorId = :vendorId");
            $updateStmt->execute([':price' => $productPrice, ':productId' => $productId, ':vendorId' => $vendorId]);
        } else {
            $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price) VALUES (:productId, :vendorId, :price)");
            $insertPriceStmt->execute([':productId' => $productId, ':vendorId' => $vendorId, ':price' => $productPrice]);
        }
    } else {
        $insertProductStmt = $pdo->prepare("INSERT INTO products (productName, productImage, category, universalIdentifier) VALUES (:productName, :productImage, :category, :universalIdentifier)");
        $insertProductStmt->execute([':productName' => $productName, ':productImage' => $productImage, ':category' => $category, ':universalIdentifier' => $universalIdentifier]);

        $newProductId = $pdo->lastInsertId();

        $insertPriceStmt = $pdo->prepare("INSERT INTO vendor_prices (productId, vendorId, price) VALUES (:productId, :vendorId, :price)");
        $insertPriceStmt->execute([':productId' => $newProductId, ':vendorId' => $vendorId, ':price' => $productPrice]);
    }

  
    echo "Scraped Product: $productName | Price: $productPrice | Image: $productImage <br>";
}


function scrapeCategory($url, $pdo, $category) {
    $page = 1;
    $vendorId = 1; 

    do {
        $html = file_get_html($url . "?page=" . $page);
        if (!$html) {
            echo "Failed to retrieve the webpage for category: $category <br>";
            break;
        }

        $productsFound = false;
        foreach ($html->find('.p-item') as $product) {
            $productName = trim($product->find('.p-item-name', 0)->plaintext);

            $priceElement = $product->find('.price-new', 0); 
            if (!$priceElement) {
                $priceElement = $product->find('.p-item-price', 0);
            }
            $productPrice = preg_replace('/[^0-9]/', '', $priceElement->plaintext); 
            $productImage = $product->find('img', 0)->src;

            handleDatabaseOperations($pdo, $productName, $productPrice, $productImage, $category, $vendorId);

            $productsFound = true;
        }

        $page++;
    } while ($productsFound);
}

foreach ($categories as $category => $url) {
    echo "Scraping $category... <br>";
    scrapeCategory($url, $pdo, $category);
}

?>
