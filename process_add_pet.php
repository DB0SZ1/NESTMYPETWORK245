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
    $pet_name = trim($_POST['pet_name']);
    $pet_breed = trim($_POST['pet_breed']);
    $pet_age = trim($_POST['pet_age']);
    $user_id = $_SESSION['user_id'];

    if (empty($pet_name)) {
        $_SESSION['error_message'] = "Pet name is required.";
        header('Location: dashboard.php');
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO pets (user_id, name, breed, age) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $pet_name, $pet_breed, $pet_age])) {
            $_SESSION['success_message'] = htmlspecialchars($pet_name) . " was added successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to add pet.";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred while adding your pet.";
    }

    header('Location: dashboard.php');
    exit();
}
