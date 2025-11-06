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
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

if (empty($start_date) || empty($end_date)) {
    echo json_encode(['success' => false, 'message' => 'Both dates are required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO sitter_availability (user_id, start_date, end_date, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    
    echo json_encode(['success' => true, 'message' => 'Availability saved']);
} catch (PDOException $e) {
    error_log("Save availability error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>