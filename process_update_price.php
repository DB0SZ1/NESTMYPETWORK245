<?php
// process_update_price.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// --- Security Checks ---
if (!isset($_SESSION['user_id'])) { // Check if user is logged in
    header('Location: index.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Check if it's a POST request
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'] ?? null;
$new_price = $_POST['price_per_night'] ?? null;

// --- Validation ---
if (empty($service_id) || !is_numeric($new_price) || $new_price < 0) {
    $_SESSION['error_message'] = "Invalid service ID or price format.";
    header('Location: dashboard.php'); // Redirect back to dashboard
    exit();
}

try {
    // --- Check Sitter Status and Ownership ---
    // Make sure the logged-in user is an approved sitter or normal sitter AND owns this service
    $stmt_check = $pdo->prepare("
        SELECT ss.id
        FROM sitter_services ss
        JOIN users u ON ss.user_id = u.id
        WHERE ss.id = ?
        AND ss.user_id = ?
        AND u.is_sitter = 1
        AND u.sitter_status IN ('approved', 'not_sitter')
    ");
    $stmt_check->execute([$service_id, $user_id]);

    if (!$stmt_check->fetch()) {
        // User is not an active sitter or does not own this service
        $_SESSION['error_message'] = "You do not have permission to update this service.";
        header('Location: dashboard.php');
        exit();
    }

    // --- Update the Price ---
    $stmt_update = $pdo->prepare("UPDATE sitter_services SET price_per_night = ? WHERE id = ? AND user_id = ?");
    $stmt_update->execute([$new_price, $service_id, $user_id]);

    if ($stmt_update->rowCount() > 0) {
        $_SESSION['success_message'] = "Service price updated successfully.";
    } else {
        // This case should ideally not happen due to the check above, but good to have
        $_SESSION['error_message'] = "Failed to update price. Please try again.";
    }

} catch (PDOException $e) {
    error_log("DB Error updating price: " . $e->getMessage());
    $_SESSION['error_message'] = "A database error occurred while updating the price.";
}

// Redirect back to the dashboard after processing
header('Location: dashboard.php');
exit();
?>