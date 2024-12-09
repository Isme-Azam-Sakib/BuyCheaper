<?php
function getTotalProducts($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    return $stmt->fetchColumn();
}

function getTotalVendors($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM vendors");
    return $stmt->fetchColumn();
}

function getTotalCategories($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    return $stmt->fetchColumn();
} 