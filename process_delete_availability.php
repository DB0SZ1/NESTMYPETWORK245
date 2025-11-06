<?php
session_start();
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_POST['id'] ?? 0;

try {
    $stmt = $pdo->prepare("DELETE FROM sitter_availability WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Availability deleted']);
} catch (PDOException $e) {
    error_log("Delete availability error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>