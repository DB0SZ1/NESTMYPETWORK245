<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$confirmation = trim($_POST['confirmation'] ?? '');

// Require "DELETE" confirmation
if (strtoupper($confirmation) !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Please type DELETE to confirm']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Delete related records (cascading deletes)
    $tables = [
        'photo_albums',
        'pets',
        'owner_profiles',
        'host_profiles',
        'host_services',
        'sitter_services',
        'owner_service_requirements',
        'messages' => ['sender_id', 'receiver_id'], // Two conditions
        'bookings' => ['user_id', 'sitter_id'],
        'transactions',
        'user_payment_methods',
        'user_phone_numbers'
    ];
    
    foreach ($tables as $table => $columns) {
        if (is_array($columns)) {
            // Multiple delete conditions
            foreach ($columns as $col) {
                $stmt = $pdo->prepare("DELETE FROM $table WHERE $col = ?");
                $stmt->execute([$user_id]);
            }
        } else {
            // Single condition (user_id)
            $table_name = is_numeric($table) ? $columns : $table;
            $stmt = $pdo->prepare("DELETE FROM $table_name WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
    }
    
    // Delete user account
    $stmt_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    
    $pdo->commit();
    
    // Destroy session
    session_destroy();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Account deleted successfully',
        'redirect' => 'index.php'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Account deletion error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
}
?>