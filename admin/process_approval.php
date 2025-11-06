<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../db.php';

// Secure this page: redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = $_GET['id'];
    $action = $_GET['action'];

    // Determine the new status
    $new_status = '';
    if ($action == 'approve') {
        $new_status = 'approved';
    } elseif ($action == 'reject') {
        $new_status = 'rejected';
    } else {
        header('Location: dashboard.php'); // Invalid action
        exit();
    }

    try {
        // Update the user's sitter_status
        $stmt = $pdo->prepare("UPDATE users SET sitter_status = ? WHERE id = ? AND sitter_status = 'pending'");
        $stmt->execute([$new_status, $user_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['admin_success'] = "Sitter status updated successfully.";
        } else {
            $_SESSION['admin_error'] = "Could not update status or sitter was not pending.";
        }

    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['admin_error'] = "A database error occurred.";
    }
}

header('Location: dashboard.php');
exit();
?>