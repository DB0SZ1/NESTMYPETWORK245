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
    $phone_number = trim($_POST['phone_number']);
    $user_id = $_SESSION['user_id'];

    try {
        $sql = "UPDATE users SET phone_number = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$phone_number, $user_id]);

        $_SESSION['success_message'] = "Phone number updated successfully!";
        
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "An error occurred while updating your phone number.";
    }

    // Redirect back to the phone numbers tab
    header('Location: edit_profile.php#phone-numbers');
    exit();

} else {
    header('Location: edit_profile.php');
    exit();
}
?>