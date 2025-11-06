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
$new_role = trim($_POST['new_role'] ?? '');

// Validate role
if (!in_array($new_role, ['boarder', 'house_sitter'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit();
}

try {
    // Check if user is a sitter
    $stmt_check = $pdo->prepare("SELECT is_sitter FROM users WHERE id = ?");
    $stmt_check->execute([$user_id]);
    $user = $stmt_check->fetch();
    
    if (!$user || !$user['is_sitter']) {
        echo json_encode(['success' => false, 'message' => 'User is not a sitter']);
        exit();
    }
    
    // Update sitter role
    $stmt_update = $pdo->prepare("
        UPDATE host_profiles 
        SET sitter_role = ?,
            offers_home_sitting = ?
        WHERE user_id = ?
    ");
    
    $offers_home_sitting = ($new_role === 'house_sitter') ? 1 : 0;
    $stmt_update->execute([$new_role, $offers_home_sitting, $user_id]);
    
    // Update DBS check requirement
    if ($new_role === 'house_sitter') {
        $stmt_dbs = $pdo->prepare("
            UPDATE host_profiles 
            SET dbs_check_status = 'required' 
            WHERE user_id = ? AND dbs_check_status = 'not_required'
        ");
        $stmt_dbs->execute([$user_id]);
    }
    
    $_SESSION['success_message'] = "Your role has been switched to " . 
        ($new_role === 'house_sitter' ? 'House Sitter' : 'Boarder');
    
    echo json_encode([
        'success' => true, 
        'message' => 'Role switched successfully',
        'new_role' => $new_role
    ]);
    
} catch (PDOException $e) {
    error_log("Role switch error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>