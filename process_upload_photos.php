<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to upload photos.";
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Define upload error messages
$upload_errors = [
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
    3 => 'The uploaded file was only partially uploaded.',
    4 => 'No file was uploaded.',
    6 => 'Missing a temporary folder.',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
];

// Helper function to upload file
function uploadFile($file, $dir, $prefix) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new Exception("Failed to create or write to directory: $dir. Check server permissions.");
        }
    }

    $valid_types = ['image/jpeg', 'image/png'];
    if (!in_array($file['type'], $valid_types)) {
        throw new Exception("Invalid file type. Please upload JPG or PNG.");
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("File size exceeds 5MB limit.");
    }

    $extension = $file['type'] === 'image/png' ? 'png' : 'jpg';
    // Use timestamp to prevent caching issues
    $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
    $filesystem_path = realpath($dir) . DIRECTORY_SEPARATOR . $filename;
    if (!$filesystem_path) {
        throw new Exception("Failed to resolve absolute path for directory: $dir");
    }
    $database_path = $dir . $filename; // Store relative path for DB

    if (!move_uploaded_file($file['tmp_name'], $filesystem_path)) {
        throw new Exception("Failed to upload file: " . error_get_last()['message'] ?? 'Unknown error.');
    }

    if (!file_exists($filesystem_path)) {
        throw new Exception("Uploaded file not found at destination: $filesystem_path");
    }

    return $database_path; // Return relative path for DB storage
}

// Helper function to delete old file
function deleteOldFile($pdo, $user_id, $column) {
    $stmt = $pdo->prepare("SELECT {$column} FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $old_path = $stmt->fetchColumn();
    
    if ($old_path && file_exists($old_path)) {
        unlink($old_path);
    }
}

try {
    $pdo->beginTransaction();

    // Handle Profile Photo Upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $error_code = $_FILES['profile_photo']['error'];
        throw new Exception($upload_errors[$error_code] ?? 'Unknown upload error for profile photo.');
    }
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        // Delete old profile photo
        deleteOldFile($pdo, $user_id, 'profile_photo_path');
        
        $photo_path = uploadFile($_FILES['profile_photo'], 'uploads/profiles/', 'profile_' . $user_id);
        
        // Update users table
        $stmt = $pdo->prepare("UPDATE users SET profile_photo_path = ? WHERE id = ?");
        $stmt->execute([$photo_path, $user_id]);
        
        $_SESSION['success_message'] = "Profile photo updated successfully!";
    }

    // Handle Cover Photo Upload
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] !== UPLOAD_ERR_OK) {
        $error_code = $_FILES['cover_photo']['error'];
        throw new Exception($upload_errors[$error_code] ?? 'Unknown upload error for cover photo.');
    }
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        // Delete old cover photo
        deleteOldFile($pdo, $user_id, 'cover_photo_path');
        
        $cover_path = uploadFile($_FILES['cover_photo'], 'uploads/covers/', 'cover_' . $user_id);
        
        // Update users table
        $stmt = $pdo->prepare("UPDATE users SET cover_photo_path = ? WHERE id = ?");
        $stmt->execute([$cover_path, $user_id]);
        
        $_SESSION['success_message'] = "Cover photo updated successfully!";
    }

    // Handle Album Photo Upload
    if (isset($_FILES['album_photo']) && $_FILES['album_photo']['error'] !== UPLOAD_ERR_OK) {
        $error_code = $_FILES['album_photo']['error'];
        throw new Exception($upload_errors[$error_code] ?? 'Unknown upload error for album photo.');
    }
    if (isset($_FILES['album_photo']) && $_FILES['album_photo']['error'] === UPLOAD_ERR_OK) {
        // Check current album count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM photo_albums WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $album_count = $stmt->fetchColumn();
        
        if ($album_count >= 15) {
            throw new Exception("You have reached the maximum of 15 photos in your album.");
        }
        
        $album_path = uploadFile($_FILES['album_photo'], 'uploads/albums/', 'album_' . $user_id);
        
        // Insert into photo_albums table
        $stmt = $pdo->prepare("INSERT INTO photo_albums (user_id, photo_path) VALUES (?, ?)");
        $stmt->execute([$user_id, $album_path]);
        
        $_SESSION['success_message'] = "Photo added to your album!";
    }

    $pdo->commit();
    
    // Add cache-busting parameter to force reload
    header('Location: profile.php?refresh=' . time());
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Photo Upload Error: " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: profile.php?refresh=' . time());
    exit();
}
?>