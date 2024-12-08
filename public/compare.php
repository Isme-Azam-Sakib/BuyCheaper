<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Comparison</title>
    <link rel="stylesheet" href="/buyCheaper/css/style.css">
    <style>
        .comparison-container {
            padding: 20px;
            overflow-x: auto;
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .comparison-table th, .comparison-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .comparison-table th {
            background-color: #f2f2f2;
        }
        .comparison-table td:first-child {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .comparison-table input {
            width: 100%;
            padding: 5px;
        }
        .comparison-table img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="comparison-container">
        <h1>Compare Products</h1>
        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product 1</th>
                    <th>Product 2</th>
                    <th>Product 3</th>
                </tr>
            </thead>
            <tbody>
                <!-- Search bar row -->
                <tr>
                    <td>Search</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td>
                            <input type="text" class="product-search" placeholder="Search for a product..." data-column="<?= $i ?>">
                        </td>
                    <?php endfor; ?>
                </tr>

                <!-- Dynamic rows -->
                <tr>
                    <td>Image</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td id="image-<?= $i ?>"></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td>Name</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td id="name-<?= $i ?>"></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td>Vendor</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td id="vendor-<?= $i ?>"></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td>Lowest Price</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td id="price-<?= $i ?>"></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td>Model</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td id="model-<?= $i ?>"></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td>Brand</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td id="brand-<?= $i ?>"></td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td>Summary</td>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <td id="summary-<?= $i ?>"></td>
                    <?php endfor; ?>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>
