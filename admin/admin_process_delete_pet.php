<?php
require_once 'admin_check.php';
require_once '../db.php';

if (!isset($_GET['id'])) {
    header('Location: admin_pets.php');
    exit();
}

$pet_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
    $stmt->execute([$pet_id]);
    
    $_SESSION['admin_message'] = "Pet has been successfully deleted.";
    $_SESSION['admin_message_type'] = 'success';
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['admin_message'] = "A database error occurred. The pet could not be deleted.";
    $_SESSION['admin_message_type'] = 'error';
}

header('Location: admin_pets.php');
exit();