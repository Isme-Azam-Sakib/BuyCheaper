<?php
include '../config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuyCheaper - Price Comparison</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>BuyCheaper</h1>
            <p>Search for the best deals on your favorite products!</p>
        </header>

        <div class="search-container">
        <input type="text" id="search" placeholder="Search for products..." autocomplete="off">
        <div id="results"></div>
        </div>

        <div class="product-container">
            <h2>Featured Products</h2>
            <div class="products">

            </div>
        </div>
    </div>
    <script src="../js/app.js"></script>

</html>