<?php
require_once 'admin_check.php';
require_once '../db.php';

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header('Location: admin_verifications.php');
    exit();
}

$user_id = $_GET['id'];
$action = $_GET['action'];
$new_status = '';

if ($action == 'approve') {
    $new_status = 'approved';
} elseif ($action == 'reject') {
    $new_status = 'rejected';
} else {
    // Invalid action
    header('Location: admin_verifications.php');
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE users SET sitter_status = ? WHERE id = ?");
    $stmt->execute([$new_status, $user_id]);

    $_SESSION['admin_message'] = "Sitter status successfully updated to '" . ucfirst($new_status) . "'.";
    $_SESSION['admin_message_type'] = 'success';

} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['admin_message'] = "A database error occurred.";
    $_SESSION['admin_message_type'] = 'error';
}

header('Location: admin_verifications.php');
exit();