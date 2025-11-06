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
$photo_id = $_POST['photo_id'] ?? null;

if (!$photo_id) {
    echo json_encode(['success' => false, 'message' => 'Photo ID is required']);
    exit();
}

try {
    // Get photo path and verify ownership
    $stmt = $pdo->prepare("SELECT photo_path FROM photo_albums WHERE id = ? AND user_id = ?");
    $stmt->execute([$photo_id, $user_id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$photo) {
        echo json_encode(['success' => false, 'message' => 'Photo not found or unauthorized']);
        exit();
    }
    
    // Delete file from filesystem
    if (file_exists($photo['photo_path'])) {
        unlink($photo['photo_path']);
    }
    
    // Delete from database
    $stmt_delete = $pdo->prepare("DELETE FROM photo_albums WHERE id = ? AND user_id = ?");
    $stmt_delete->execute([$photo_id, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Photo deleted successfully']);
    
} catch (PDOException $e) {
    error_log("Delete Album Photo Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>