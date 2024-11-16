<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Comparison</title>
    <link rel="stylesheet" href="/buyCheaper/css/style.css">
    
</head>
<body>
    
    <div class="comparison-container">
        <h1>Compare Products</h1>
        <div class="comparison-columns">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="comparison-column" id="column-<?= $i ?>">
                    <input type="text" class="product-search" placeholder="Search for a product..." data-column="<?= $i ?>">
                    <div class="product-details">
                        <!-- Product details will be dynamically loaded here -->
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    
    <script src="../js/app.js"></script>
</body>
</html>
