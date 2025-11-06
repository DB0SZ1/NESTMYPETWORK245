<?php
// Start session only if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// THIS LINE IS THE CRITICAL FIX
require 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            
            // Get and store only the first name
            $fullName = $user['fullname'];
            $nameParts = explode(' ', $fullName);
            $firstName = $nameParts[0]; 

            $_SESSION['user_firstname'] = $firstName; 
            $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($firstName) . "!";
            
            header("Location: dashboard.php");
            exit();
        } else {
            // Login failed
            $_SESSION['error_message'] = "Invalid email or password.";
            header("Location: auth.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "An error occurred. Please try again.";
        header("Location: index.php");
        exit();
    }
} else {
    // Redirect if accessed directly
    header("Location: auth.php");
    exit();
}