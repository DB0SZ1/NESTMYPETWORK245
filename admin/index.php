<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #F9FAFB; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: #fff; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); width: 100%; max-width: 400px; }
        .login-card h1 { font-family: 'Poppins', sans-serif; text-align: center; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 0.5rem; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid #E5E7EB; border-radius: 8px; box-sizing: border-box; }
        .btn { width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 8px; background: #8B5CF6; color: white; font-weight: 600; font-size: 1rem; cursor: pointer; }
        .notification { padding: 1rem; background: #FEE2E2; color: #991B1B; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Admin Panel Login</h1>
        
        <?php 
        if (isset($_SESSION['admin_error'])) {
            echo '<div class="notification">' . $_SESSION['admin_error'] . '</div>';
            unset($_SESSION['admin_error']);
        }
        ?>

        <form action="process_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>