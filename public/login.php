<?php
// Start a session to manage logged-in state
session_start();

// Include the database configuration file
require_once '../config/database.php';

// Check if the login form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve submitted username and password
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the SQL query to fetch admin details
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    // Verify password if an admin was found
    if ($admin && $admin['password'] === $password) {
        // Password is correct; set session and redirect to the admin page
        $_SESSION['admin_logged_in'] = true;
        header("Location: /buyCheaper/index.php"); 
        exit();
    } else {
        // Invalid login credentials
        $error = "Invalid username or password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <img src="../assets/login.png" alt="Login Side Image">
        </div>
        <div class="login-form">
            <h2>Welcome, Admin!</h2>
            <p>Log in to access the dashboard</p>
            
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

            <form method="POST" action="">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
                
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>