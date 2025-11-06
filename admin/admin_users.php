<?php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch all users from the database
$users = [];
try {
    $stmt = $pdo->query("SELECT id, fullname, email, created_at, sitter_status FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Failed to load users.";
}

$pageTitle = "Manage Users";
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <h1>Manage All Users</h1>
                <p>View, edit, or delete registered users.</p>
            </header>

            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>All Users (<?php echo count($users); ?>)</h2>
                    </div>
                    
                    <?php if (isset($_SESSION['admin_message'])): ?>
                        <div class="notification-admin <?php echo $_SESSION['admin_message_type']; ?>">
                            <?php echo $_SESSION['admin_message']; unset($_SESSION['admin_message'], $_SESSION['admin_message_type']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['sitter_status'] != 'not_sitter'): ?>
                                                <span class="status-badge <?php echo htmlspecialchars($user['sitter_status']); ?>">Sitter</span>
                                            <?php else: ?>
                                                <span class="status-badge user">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn-action edit">Edit</a>
                                            <a href="admin_process_delete_user.php?id=<?php echo $user['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure you want to permanently delete this user and all their data? This cannot be undone.');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>