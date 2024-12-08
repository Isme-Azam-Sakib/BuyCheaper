<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Comparison</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@100..900&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/buyCheaper/css/style.css">
</head>
<body style="background-color: #ffffff !important; font-family: 'Poppins', sans-serif;">
    <div class="comparison-container">
        <h1>Product Comparison</h1>
        <p class="comparison-subtitle">Find and select products to see the differences and similarities between them and grab the best one at the best price!</p>
        
        <div class="comparison-table">
            <!-- Header Row (Search) -->
            <div class="table-row header-row">
                <div class="table-cell header-cell">Compare Products</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell">
                        <div class="search-container">
                            <input type="text" class="product-search" placeholder="Search and Select Product" data-column="<?= $i ?>">
                            <div class="search-results-container" id="search-results-<?= $i ?>"></div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Product Image Row -->
            <div class="table-row">
                <div class="table-cell header-cell">Product Image</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell" id="image-<?= $i ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Product Name Row -->
            <div class="table-row">
                <div class="table-cell header-cell">Product Name</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell" id="name-<?= $i ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Vendor Row -->
            <div class="table-row">
                <div class="table-cell header-cell">Vendor</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell" id="vendor-<?= $i ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Lowest Price Row -->
            <div class="table-row">
                <div class="table-cell header-cell">Lowest Price</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell">
                        <div id="price-<?= $i ?>"></div>
                        <div id="store-button-<?= $i ?>"></div>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Model Row -->
            <div class="table-row">
                <div class="table-cell header-cell">Model</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell" id="model-<?= $i ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Brand Row -->
            <div class="table-row">
                <div class="table-cell header-cell">Brand</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell" id="brand-<?= $i ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Summary Row -->
            <div class="table-row">
                <div class="table-cell header-cell">Summary</div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="table-cell" id="summary-<?= $i ?>"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <script src="../js/app.js"></script>
</body>
</html>
