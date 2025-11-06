<?php
// admin/admin_dashboard.php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch key metrics for NESTMYPET
$users_count_stmt = $pdo->query("SELECT COUNT(*) AS count FROM users");
$users_count = $users_count_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$sitters_count_stmt = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE is_sitter = 1");
$sitters_count = $sitters_count_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$pending_count_stmt = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE sitter_status = 'pending'");
$pending_count = $pending_count_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

$pets_count_stmt = $pdo->query("SELECT COUNT(*) AS count FROM pets");
$pets_count = $pets_count_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Fetch recent pending sitters
$recent_pending_stmt = $pdo->query("SELECT id, fullname, email, created_at FROM users WHERE sitter_status = 'pending' ORDER BY created_at DESC LIMIT 5");
$recent_pending = $recent_pending_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="admin_dashboard.php" class="logo">Admin Panel</a>
            </div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="admin_dashboard.php" class="nav-item active"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_sitters.php" class="nav-item"><i class="fas fa-hand-paper"></i><span>Manage Sitters</span></a>
                <a href="admin_verifications.php" class="nav-item"><i class="fas fa-check-circle"></i><span>Verifications</span></a>
                <a href="admin_pets.php" class="nav-item"><i class="fas fa-paw"></i><span>Manage Pets</span></a>
                <a href="admin_blog.php" class="nav-item"><i class="fas fa-blog"></i><span>Manage Blog</span></a>
                <a href="admin_bookings.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Bookings</span></a>
<a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting">
                    <h1>Admin Overview</h1>
                    <p>Global statistics for NestMyPet.</p>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="card-icon users"><i class="fas fa-users"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Users</span>
                        <span class="card-value"><?php echo $users_count; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon sitters"><i class="fas fa-briefcase"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Sitters</span>
                        <span class="card-value"><?php echo $sitters_count; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon pending"><i class="fas fa-file-alt"></i></div>
                    <div class="card-info">
                        <span class="card-title">Pending Approvals</span>
                        <span class="card-value"><?php echo $pending_count; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-icon pets"><i class="fas fa-paw"></i></div>
                    <div class="card-info">
                        <span class="card-title">Total Pets</span>
                        <span class="card-value"><?php echo $pets_count; ?></span>
                    </div>
                </div>
            </section>
            
            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>Pending Sitter Approvals</h2>
                         <a href="admin_verifications.php" class="view-all-link">Manage All</a>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($recent_pending)): ?>
                            <div style="padding: 1.5rem; text-align: center; color: var(--text-secondary);">No pending approvals.</div>
                        <?php endif; ?>
                        
                         <?php foreach ($recent_pending as $sitter): ?>
                        <div class="activity-item">
                            <div class="item-icon public"><i class="fas fa-user-clock"></i></div>
                            <div class="item-details">
                                <p><strong><?php echo htmlspecialchars($sitter['fullname']); ?></strong></p>
                                <span><?php echo htmlspecialchars($sitter['email']); ?></span>
                            </div>
                            <span class="status-badge pending">Pending</span>
                            <a href="admin_verifications.php" class="item-action"><i class="fas fa-chevron-right"></i></a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>