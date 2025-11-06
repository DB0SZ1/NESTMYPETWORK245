<?php
require_once 'admin_check.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_users.php');
    exit();
}

$user_id = $_POST['user_id'];
$fullname = $_POST['fullname'];
$email = $_POST['email'];
$sitter_status = $_POST['sitter_status'];

try {
    // Also update is_sitter field for consistency
    $is_sitter = ($sitter_status !== 'not_sitter') ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, sitter_status = ?, is_sitter = ? WHERE id = ?");
    $stmt->execute([$fullname, $email, $sitter_status, $is_sitter, $user_id]);

    $_SESSION['admin_message'] = "User details updated successfully.";
    $_SESSION['admin_message_type'] = 'success';
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['admin_message'] = "A database error occurred while updating the user.";
    $_SESSION['admin_message_type'] = 'error';
}

header('Location: admin_users.php');
exit();