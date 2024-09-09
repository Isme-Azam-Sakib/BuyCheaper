<!DOCTYPE html>
<html lang="en">
<head>
    <title>Compare Prices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="container my-5">
    <?php
    include_once '../includes/db_connect.php';

    if (isset($_GET['productId']) && isset($_GET['category'])) {
        $category = $_GET['category'];
        $productId = $_GET['productId'];

        $stmt = $pdo->prepare("SELECT productName, vendor1Price, vendor2Price, vendor3Price, vendor4Price, vendor5Price, vendor6Price, productImage FROM $category WHERE productId = :productId");
        $stmt->bindParam(':productId', $productId);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            echo '<div class="card mx-auto" style="width: 18rem;">';
            echo '<img src="' . $product['productImage'] . '" class="card-img-top" alt="' . $product['productName'] . '">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title text-center">' . $product['productName'] . '</h5>';
            echo '<p class="card-text">Startech Price: <strong>' . $product['vendor1Price'] . '</strong></p>';
            echo '<p class="card-text">Ryans Price: <strong>' . $product['vendor2Price'] . '</strong></p>';
            echo '<p class="card-text">Techland 3 Price: <strong>' . $product['vendor3Price'] . '</strong></p>';
            echo '<p class="card-text">Skyland Price: <strong>' . $product['vendor4Price'] . '</strong></p>';
            echo '<p class="card-text">Ultratech Price: <strong>' . $product['vendor5Price'] . '</strong></p>';
            echo '<p class="card-text">PC House BD Price: <strong>' . $product['vendor6Price'] . '</strong></p>';
            echo '</div>';
            echo '</div>';
        }
    }
    ?>
</div>

</body>
</html>
