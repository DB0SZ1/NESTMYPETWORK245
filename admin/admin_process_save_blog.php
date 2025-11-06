<?php
require_once 'admin_check.php'; // Ensures only logged-in admins can access
require_once '../db.php';     // Database connection

// --- Security: Check if the request method is POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, redirect back to the blog management page
    header('Location: admin_blog.php');
    exit();
}

// --- Get Data from Form ---
$post_id = $_POST['post_id'] ?? null; // Null if adding, has value if editing
$title = trim($_POST['title']);
$content = trim($_POST['content']);
$image_url = trim($_POST['image_url']) ?: null; // Set to null if the field was empty
$snippet = trim($_POST['snippet']) ?: null; // Set to null if the field was empty

// --- Basic Validation ---
if (empty($title) || empty($content)) {
    // If title or content is missing, store an error and redirect back to the form
    $_SESSION['admin_message'] = "Title and Content are required fields.";
    $_SESSION['admin_message_type'] = 'error';
    // Redirect back to the edit page (if editing) or add page (if adding)
    header('Location: ' . ($post_id ? "admin_edit_blog.php?id=$post_id" : 'admin_edit_blog.php'));
    exit();
}

// --- Auto-generate Snippet if Empty ---
if (empty($snippet)) {
    // Remove HTML tags from the content to get plain text
    $plain_content = strip_tags($content);
    // Take the first 150 characters
    $snippet = mb_substr($plain_content, 0, 150);
    // Add ellipsis (...) if the original content was longer
    if (mb_strlen($plain_content) > 150) {
        $snippet .= '...';
    }
}

// --- Save to Database ---
try {
    if ($post_id) {
        // --- Update Existing Post ---
        $sql = "UPDATE blog_posts SET title = ?, content = ?, image_url = ?, snippet = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $image_url, $snippet, $post_id]);
        $_SESSION['admin_message'] = "Blog post updated successfully.";
    } else {
        // --- Insert New Post ---
        $sql = "INSERT INTO blog_posts (title, content, image_url, snippet) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $image_url, $snippet]);
        $_SESSION['admin_message'] = "Blog post created successfully.";
    }
    // Set success message type
    $_SESSION['admin_message_type'] = 'success';

} catch (PDOException $e) {
    // Log the database error for debugging
    error_log("DB Error saving blog post: " . $e->getMessage());
    // Set a user-friendly error message
    $_SESSION['admin_message'] = "A database error occurred while saving the post. Please try again.";
    $_SESSION['admin_message_type'] = 'error';
    // Redirect back to edit form on error to allow user to try again
    header('Location: ' . ($post_id ? "admin_edit_blog.php?id=$post_id" : 'admin_edit_blog.php'));
    exit(); // Stop script execution after redirect
}

// --- Redirect back to the main blog management page on success ---
header('Location: admin_blog.php');
exit(); // Stop script execution after redirect
?>