<?php
include '../config/database.php';

if (isset($_POST['query'])) {
    $search = "%{$_POST['query']}%";

    $stmt = $pdo->prepare("SELECT productId, productName, productImage FROM products WHERE productName LIKE :search LIMIT 5");
    $stmt->execute(['search' => $search]);

    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '
            <div class="search-result" onclick="redirectToProduct(' . htmlspecialchars($row['productId']) . ')">
                <img src="' . htmlspecialchars($row['productImage']) . '" alt="' . htmlspecialchars($row['productName']) . '">
                <span>' . htmlspecialchars($row['productName']) . '</span>
            </div>';
        }
    } else {
        echo '<p>No results found</p>';
    }
}
?>
