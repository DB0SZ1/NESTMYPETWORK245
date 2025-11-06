<?php
require_once 'admin_check.php';
require_once '../db.php';

if (!isset($_GET['id'])) {
    header('Location: admin_blog.php');
    exit();
}

$post_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    
    $_SESSION['admin_message'] = "Blog post deleted successfully.";
    $_SESSION['admin_message_type'] = 'success';
} catch (PDOException $e) {
    error_log("DB Error deleting blog post: " . $e->getMessage());
    $_SESSION['admin_message'] = "A database error occurred. The post could not be deleted.";
    $_SESSION['admin_message_type'] = 'error';
}

header('Location: admin_blog.php');
exit();