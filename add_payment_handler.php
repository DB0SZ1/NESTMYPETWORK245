<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // WARNING: In a real application, NEVER handle full credit card numbers.
    // This is for demonstration only and uses mock data.
    $last_four = substr(str_replace('-', '', $_POST['card_number']), -4);
    $expiry_date = $_POST['expiry_date'];
    $card_type = "Visa"; // Mock data
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO user_payment_methods (user_id, card_type, last_four, expiry_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $card_type, $last_four, $expiry_date]);
        $_SESSION['success_message'] = "Payment method added successfully.";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred.";
    }
}
header('Location: manage_profile.php');
exit();
