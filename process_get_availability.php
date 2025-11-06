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

try {
    $stmt = $pdo->prepare("
        SELECT id, start_date, end_date
        FROM sitter_availability
        WHERE user_id = ?
        ORDER BY start_date ASC
    ");
    $stmt->execute([$user_id]);
    $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'dates' => $dates]);
} catch (PDOException $e) {
    error_log("Get availability error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error', 'dates' => []]);
}
?>