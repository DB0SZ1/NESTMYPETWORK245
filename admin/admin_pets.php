<?php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch all pets and JOIN with the users table to get owner info
$pets = [];
try {
    $sql = "SELECT pets.id, pets.name, pets.breed, pets.age, users.fullname AS owner_name 
            FROM pets 
            JOIN users ON pets.user_id = users.id 
            ORDER BY pets.id DESC";
    $stmt = $pdo->query($sql);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Failed to load pets.";
}

$pageTitle = "Manage Pets";
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
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_sitters.php" class="nav-item"><i class="fas fa-hand-paper"></i><span>Manage Sitters</span></a>
                <a href="admin_verifications.php" class="nav-item"><i class="fas fa-check-circle"></i><span>Verifications</span></a>
                <a href="admin_pets.php" class="nav-item active"><i class="fas fa-paw"></i><span>Manage Pets</span></a>
                <a href="admin_blog.php" class="nav-item"><i class="fas fa-blog"></i><span>Manage Blog</span></a>
                <a href="admin_bookings.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Bookings</span></a>
<a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a></div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Manage All Pets</h1>
                <p>View or delete pets registered on the platform.</p>
            </header>

            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>All Pets (<?php echo count($pets); ?>)</h2>
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
                                    <th>Pet ID</th>
                                    <th>Name</th>
                                    <th>Breed</th>
                                    <th>Owner</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pets as $pet): ?>
                                    <tr>
                                        <td><?php echo $pet['id']; ?></td>
                                        <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                        <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                                        <td><?php echo htmlspecialchars($pet['owner_name']); ?></td>
                                        <td class="action-buttons">
                                            <a href="admin_process_delete_pet.php?id=<?php echo $pet['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this pet?');">Delete</a>
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