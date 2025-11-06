<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to update your profile.";
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ONLY editable fields
    $about_me = trim($_POST['about_me'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Database update - ONLY editable fields
    try {
        $pdo->beginTransaction();
        
        $query = "UPDATE users SET about_me = ?, street = ?, city = ?, postcode = ?, country = ? WHERE id = ?";
        $params = [$about_me, $street, $city, $postcode, $country, $user_id];

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $pdo->commit();
        $_SESSION['success_message'] = "Profile updated successfully!";
        
        // Redirect to profile.php to see changes
        header('Location: profile.php');
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database Error: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred while updating your profile.";
        header('Location: edit_profile.php#basic-info');
        exit();
    }
} else {
    header('Location: edit_profile.php');
    exit();
}
?>