<?php
$host = 'localhost';
$dbname = 'price_comparison';
$user = 'root';
$pass = '';   

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    // Set PDO to throw exceptions when an error occurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
