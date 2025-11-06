<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_id = $_POST['pet_id'];
    $pet_name = trim($_POST['pet_name']);
    $pet_breed = trim($_POST['pet_breed']);
    $pet_age = trim($_POST['pet_age']);
    $user_id = $_SESSION['user_id'];

    if (empty($pet_name) || empty($pet_id)) {
        $_SESSION['error_message'] = "Invalid data submitted.";
        header('Location: dashboard.php');
        exit();
    }

    try {
        // Check if pet belongs to the user before updating
        $check_stmt = $pdo->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$pet_id, $user_id]);
        if ($check_stmt->fetch()) {
            $update_stmt = $pdo->prepare("UPDATE pets SET name = ?, breed = ?, age = ? WHERE id = ?");
            if ($update_stmt->execute([$pet_name, $pet_breed, $pet_age, $pet_id])) {
                $_SESSION['success_message'] = "Pet details updated successfully.";
            } else {
                $_SESSION['error_message'] = "Could not update pet details.";
            }
        } else {
             $_SESSION['error_message'] = "Authorization failed.";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred.";
    }

    header('Location: dashboard.php');
    exit();
}
