<?php
include_once 'db_connect.php';

if (isset($_POST['category']) && isset($_POST['searchTerm'])) {
    $category = $_POST['category'];
    $searchTerm = '%' . $_POST['searchTerm'] . '%';

    // Query the respective table, including the productImage column
    $stmt = $pdo->prepare("SELECT productId, productName, description, productImage FROM $category WHERE productName LIKE :searchTerm LIMIT 5");
    $stmt->bindParam(':searchTerm', $searchTerm);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the results as JSON
    echo json_encode($results);
}
?>
