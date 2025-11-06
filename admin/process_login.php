<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Note: We are now in the /admin folder, so db.php is one level up
require '../db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Password is correct!
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // --- THIS IS THE FIX ---
            // It now points to the correct admin dashboard file
            header('Location: admin_dashboard.php'); 
            exit();

        } else {
            // Invalid credentials
            $_SESSION['admin_error'] = "Invalid username or password.";
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['admin_error'] = "A database error occurred.";
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>