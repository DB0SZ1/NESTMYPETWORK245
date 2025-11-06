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
    $user_id = $_SESSION['user_id'];
    $service_type = trim($_POST['service_type']);
    $price = trim($_POST['price_per_night']);
    $headline = trim($_POST['headline']);
    $about_me = trim($_POST['sitter_about_me']);

    if (empty($service_type) || empty($price) || empty($headline)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: become_sitter.php');
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Add the service
        $sql1 = "INSERT INTO sitter_services (user_id, service_type, price_per_night, headline, sitter_about_me) VALUES (?, ?, ?, ?, ?)";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$user_id, $service_type, $price, $headline, $about_me]);

        // 2. Mark the user as a sitter
        $sql2 = "UPDATE users SET is_sitter = 1 WHERE id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$user_id]);

        $pdo->commit();

        $_SESSION['success_message'] = "Congratulations! You are now listed as a sitter.";
        header('Location: search.php');
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred. Could not become sitter.";
        header('Location: become_sitter.php');
        exit();
    }

} else {
    header('Location: become_sitter.php');
    exit();
}
?>