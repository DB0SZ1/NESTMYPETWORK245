<?php
require_once 'admin_check.php';
require_once '../db.php';

// Check if an ID is provided
if (!isset($_GET['id'])) {
    header('Location: admin_users.php');
    exit();
}

// Fetch the user's data
$user_id = $_GET['id'];
$user = null;
try {
    $stmt = $pdo->prepare("SELECT fullname, email, sitter_status FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    // Handle error, maybe redirect with a message
}

// If user not found, redirect
if (!$user) {
    $_SESSION['admin_message'] = "User not found.";
    $_SESSION['admin_message_type'] = 'error';
    header('Location: admin_users.php');
    exit();
}

$pageTitle = "Edit User";
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="admin_dashboard.php" class="logo">Admin Panel</a></div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="admin_users.php" class="nav-item active"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_sitters.php" class="nav-item"><i class="fas fa-hand-paper"></i><span>Manage Sitters</span></a>
                <a href="admin_verifications.php" class="nav-item"><i class="fas fa-check-circle"></i><span>Verifications</span></a>
                <a href="admin_pets.php" class="nav-item"><i class="fas fa-paw"></i><span>Manage Pets</span></a>
                <a href="admin_blog.php" class="nav-item"><i class="fas fa-blog"></i><span>Manage Blog</span></a>
                <a href="admin_bookings.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Bookings</span></a>
<a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a></div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Edit User: <?php echo htmlspecialchars($user['fullname']); ?></h1>
            </header>

            <section class="main-area">
                <div class="content-panel form-panel">
                    <form action="admin_process_edit_user.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="sitter_status">Sitter Status</label>
                            <select id="sitter_status" name="sitter_status" class="form-control">
                                <option value="not_sitter" <?php echo ($user['sitter_status'] == 'not_sitter') ? 'selected' : ''; ?>>Not a Sitter</option>
                                <option value="pending" <?php echo ($user['sitter_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo ($user['sitter_status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo ($user['sitter_status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-action approve">Save Changes</button>
                            <a href="admin_users.php" class="btn-action reject" style="background-color: #6B7280;">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>