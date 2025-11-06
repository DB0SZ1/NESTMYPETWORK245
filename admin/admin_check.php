<?php
// admin/admin_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// This file will be included in all admin pages
// If admin is not logged in, redirect to admin login
if (!isset($_SESSION['admin_id'])) {
    // We are in /admin, so index.php is in the same folder
    header('Location: index.php');
    exit();
}
?>