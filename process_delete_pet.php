<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $pet_id_to_delete = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("SELECT name FROM pets WHERE id = ? AND user_id = ?");
        $stmt->execute([$pet_id_to_delete, $user_id]);
        $pet = $stmt->fetch();

        if ($pet) {
            $delete_stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
            if ($delete_stmt->execute([$pet_id_to_delete])) {
                $_SESSION['success_message'] = htmlspecialchars($pet['name']) . " was removed.";
            } else {
                $_SESSION['error_message'] = "Could not delete the pet.";
            }
        } else {
            $_SESSION['error_message'] = "You are not authorized to perform this action.";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "A database error occurred.";
    }
}

header('Location: dashboard.php');
exit();
