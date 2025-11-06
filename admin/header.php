<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Secure this page: redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - NestMyPet</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="admin-header">
        <h2>NestMyPet Admin</h2>
        <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</a>
    </header>