<?php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch all bookings, joining with users table twice to get owner and sitter names
$bookings = [];
try {
    $sql = "
        SELECT 
            b.id, b.start_date, b.end_date, b.total_price, b.booking_status,
            owner.fullname AS owner_name,
            sitter.fullname AS sitter_name
        FROM bookings AS b
        JOIN users AS owner ON b.user_id = owner.id
        JOIN users AS sitter ON b.sitter_id = sitter.id
        ORDER BY b.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin booking fetch error: " . $e->getMessage());
    $error = "Failed to load bookings.";
}

$pageTitle = "Manage Bookings";
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header"><a href="admin_dashboard.php" class="logo">Admin Panel</a></div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_sitters.php" class="nav-item"><i class="fas fa-hand-paper"></i><span>Manage Sitters</span></a>
                <a href="admin_verifications.php" class="nav-item"><i class="fas fa-check-circle"></i><span>Verifications</span></a>
                <a href="admin_pets.php" class="nav-item"><i class="fas fa-paw"></i><span>Manage Pets</span></a>
                <a href="admin_blog.php" class="nav-item"><i class="fas fa-blog"></i><span>Manage Blog</span></a>
                <a href="admin_bookings.php" class="nav-item active"><i class="fas fa-calendar-alt"></i><span>Bookings</span></a>
                <a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a></div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1>Manage Bookings</h1>
                <p>View all booking activity on the platform.</p>
            </header>

            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>All Bookings (<?php echo count($bookings); ?>)</h2>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pet Owner</th>
                                    <th>Sitter</th>
                                    <th>Dates</th>
                                    <th>Total (£)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bookings)): ?>
                                    <tr><td colspan="6" style="text-align: center;">No bookings have been made yet.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['id']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['owner_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['sitter_name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($booking['start_date'])) . ' to ' . date('d M Y', strtotime($booking['end_date'])); ?></td>
                                        <td><?php echo number_format($booking['total_price'], 2); ?></td>
                                        <td><span class="status-badge <?php echo htmlspecialchars($booking['booking_status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?></span></td>
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
