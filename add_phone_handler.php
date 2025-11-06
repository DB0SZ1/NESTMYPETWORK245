<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['phone_number'])) {
    $phone_number = trim($_POST['phone_number']);
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO user_phone_numbers (user_id, phone_number) VALUES (?, ?)");
        $stmt->execute([$user_id, $phone_number]);
        $_SESSION['success_message'] = "Phone number added successfully.";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred.";
    }
}
header('Location: manage_profile.php');
exit();
