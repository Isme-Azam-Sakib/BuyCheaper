<?php
include 'includes/simple_html_dom.php';

// Function to scrape Startech
function scrape_startech() {
    $html = file_get_html('https://www.startech.com.bd/component/processor');
    $products = [];

    foreach ($html->find('.p-item') as $product) {
        $name = $product->find('.p-item-name a', 0)->plaintext;
        $price = $product->find('.p-item-price', 0)->plaintext;
        $image = $product->find('img', 0)->src;
        $products[] = [
            'name' => trim($name),
            'price' => preg_replace('/[^0-9]/', '', $price),
            'image' => 'https://www.startech.com.bd/' . $image,
        ];
    }
    return $products;
}

// Function to scrape Ryans
function scrape_ryans() {
    $html = file_get_html('https://www.ryanscomputers.com/category/processor');
    $products = [];

    foreach ($html->find('.product-thumb') as $product) {
        $name = $product->find('.product-title a', 0)->plaintext;
        $price = $product->find('.price-new', 0)->plaintext;
        $image = $product->find('.image img', 0)->src;
        $products[] = [
            'name' => trim($name),
            'price' => preg_replace('/[^0-9]/', '', $price),
            'image' => $image,
        ];
    }
    return $products;
}

// Function to scrape Techland
function scrape_techland() {
    $html = file_get_html('https://www.techlandbd.com/processor');
    $products = [];

    foreach ($html->find('.product-thumb') as $product) {
        $name = $product->find('.caption h4 a', 0)->plaintext;
        $price = $product->find('.price', 0)->plaintext;
        $image = $product->find('img', 0)->src;
        $products[] = [
            'name' => trim($name),
            'price' => preg_replace('/[^0-9]/', '', $price),
            'image' => $image,
        ];
    }
    return $products;
}

// Function to scrape Skyland
function scrape_skyland() {
    $html = file_get_html('https://www.skyland.com.bd/category/processor');
    $products = [];

    foreach ($html->find('.product-thumb') as $product) {
        $name = $product->find('.caption h4 a', 0)->plaintext;
        $price = $product->find('.price', 0)->plaintext;
        $image = $product->find('img', 0)->src;
        $products[] = [
            'name' => trim($name),
            'price' => preg_replace('/[^0-9]/', '', $price),
            'image' => $image,
        ];
    }
    return $products;
}

// Function to scrape Ultratech
function scrape_ultratech() {
    $html = file_get_html('https://www.ultratech.com.bd/processor');
    $products = [];

    foreach ($html->find('.product-thumb') as $product) {
        $name = $product->find('.caption h4 a', 0)->plaintext;
        $price = $product->find('.price', 0)->plaintext;
        $image = $product->find('img', 0)->src;
        $products[] = [
            'name' => trim($name),
            'price' => preg_replace('/[^0-9]/', '', $price),
            'image' => $image,
        ];
    }
    return $products;
}

// Function to scrape PCHouse
function scrape_pchouse() {
    $html = file_get_html('https://www.pchouse.com.bd/processor');
    $products = [];

    foreach ($html->find('.product-thumb') as $product) {
        $name = $product->find('.caption h4 a', 0)->plaintext;
        $price = $product->find('.price', 0)->plaintext;
        $image = $product->find('img', 0)->src;
        $products[] = [
            'name' => trim($name),
            'price' => preg_replace('/[^0-9]/', '', $price),
            'image' => $image,
        ];
    }
    return $products;
}

// Main function to scrape based on vendor name
function scrape_vendor($vendor) {
    switch ($vendor) {
        case 'startech':
            return scrape_startech();
        case 'ryans':
            return scrape_ryans();
        case 'techland':
            return scrape_techland();
        case 'skyland':
            return scrape_skyland();
        case 'ultratech':
            return scrape_ultratech();
        case 'pchouse':
            return scrape_pchouse();
        default:
            return [];
    }
}

// Example of how to use the function
$vendor = 'skyland'; 
$products = scrape_vendor($vendor);

// Output the scraped data
foreach ($products as $product) {
    echo "Product: " . $product['name'] . " | Price: " . $product['price'] . " | Image: " . $product['image'] . "\n";
}
?>
