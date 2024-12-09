<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: /buyCheaper/public/login.php');
        exit();
    }
}

function loginAdmin($username, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT adminId, password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && $admin['password'] === $password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['adminId'];
        return true;
    }
    return false;
}

function logoutAdmin() {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    session_destroy();
} 