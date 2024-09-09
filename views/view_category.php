<?php
include_once '../includes/db_connect.php';

if (isset($_GET['category'])) {
    $category = $_GET['category'];

    // Fetch all products from the chosen category
    $stmt = $pdo->prepare("SELECT * FROM $category");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Category</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <h1>View Products in Category</h1>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-3">
                <div class="card">
                    <img src="<?= $product['productImage'] ?>" class="card-img-top" alt="<?= $product['productName'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $product['productName'] ?></h5>
                        <p class="card-text"><?= $product['description'] ?></p>
                        <p class="card-text">Vendor 1 Price: <?= $product['vendor1Price'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
