<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    // --- Validation ---
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All password fields are required.";
        header('Location: edit_profile.php');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New passwords do not match.";
        header('Location: edit_profile.php');
        exit();
    }

    try {
        // 1. Get the user's current hashed password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
             $_SESSION['error_message'] = "User not found.";
             header('Location: edit_profile.php');
             exit();
        }

        // 2. Verify the current password
        if (password_verify($current_password, $user['password'])) {
            // 3. Hash the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // 4. Update the password in the database
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->execute([$new_hashed_password, $user_id]);

            $_SESSION['success_message'] = "Password changed successfully!";
        } else {
            $_SESSION['error_message'] = "Your current password was incorrect.";
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred.";
    }

    header('Location: edit_profile.php');
    exit();

} else {
    header('Location: edit_profile.php');
    exit();
}
?>