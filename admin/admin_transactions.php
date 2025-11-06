<?php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch all transactions, joining with users table to get the payer's name
$transactions = [];
try {
    $sql = "
        SELECT 
            t.id, t.booking_id, t.amount_paid, t.commission_amount, t.stripe_payment_intent_id, t.created_at,
            u.fullname AS user_name
        FROM transactions AS t
        JOIN users AS u ON t.user_id = u.id
        ORDER BY t.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin transaction fetch error: " . $e->getMessage());
    $error = "Failed to load transactions.";
}

$pageTitle = "Manage Transactions";
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
                <a href="admin_bookings.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Bookings</span></a>
                <a href="admin_transactions.php" class="nav-item active"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a></div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1>Manage Transactions</h1>
                <p>View all successful payments processed through Stripe.</p>
            </header>

            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>All Transactions (<?php echo count($transactions); ?>)</h2>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Booking ID</th>
                                    <th>User</th>
                                    <th>Amount Paid (£)</th>
                                    <th>Commission (£)</th>
                                    <th>Date</th>
                                    <th>Stripe ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr><td colspan="7" style="text-align: center;">No transactions found.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($transactions as $txn): ?>
                                    <tr>
                                        <td><?php echo $txn['id']; ?></td>
                                        <td><?php echo $txn['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($txn['user_name']); ?></td>
                                        <td><?php echo number_format($txn['amount_paid'], 2); ?></td>
                                        <td><?php echo number_format($txn['commission_amount'], 2); ?></td>
                                        <td><?php echo date('d M Y, H:i', strtotime($txn['created_at'])); ?></td>
                                        <td>
                                            <a href="https://dashboard.stripe.com/payments/<?php echo htmlspecialchars($txn['stripe_payment_intent_id']); ?>" target="_blank" rel="noopener noreferrer" title="View on Stripe">
                                                <?php echo htmlspecialchars(substr($txn['stripe_payment_intent_id'], 0, 10)) . '...'; ?>
                                            </a>
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
