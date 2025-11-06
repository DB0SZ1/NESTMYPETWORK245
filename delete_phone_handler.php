<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $phone_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM user_phone_numbers WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$phone_id, $user_id])) {
             $_SESSION['success_message'] = "Phone number removed.";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred.";
    }
}
header('Location: manage_profile.php');
exit();
