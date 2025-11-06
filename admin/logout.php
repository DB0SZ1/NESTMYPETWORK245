<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Unset all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

header('Location: index.php');
exit();
?>