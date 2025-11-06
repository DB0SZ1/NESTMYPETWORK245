<?php
require_once 'admin_check.php';
require_once '../db.php';

// Fetch all blog posts
$posts = [];
try {
    $stmt = $pdo->query("SELECT id, title, snippet, created_at FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Failed to load blog posts.";
}

$pageTitle = "Manage Blog Posts";
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
                <a href="admin_pets.php" class="nav-item"><i class="fas fa-paw"></i><span>Manage Pets</span></a>
                <a href="admin_blog.php" class="nav-item active"><i class="fas fa-blog"></i><span>Manage Blog</span></a>
                <a href="admin_bookings.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>Bookings</span></a>
<a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a></div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h1>Manage Blog Posts</h1>
                <p>Create, edit, or delete blog articles.</p>
            </header>

            <section class="main-area">
                <div class="content-panel">
                    <div class="panel-header">
                        <h2>All Posts (<?php echo count($posts); ?>)</h2>
                        <a href="admin_edit_blog.php" class="btn-action approve">Add New Post</a>
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
                                    <th>Title</th>
                                    <th>Snippet</th>
                                    <th>Created On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($posts)): ?>
                                    <tr><td colspan="4" style="text-align: center;">No blog posts found.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($post['snippet'] ?? '', 0, 100)) . '...'; ?></td>
                                        <td><?php echo date('d M Y', strtotime($post['created_at'])); ?></td>
                                        <td class="action-buttons">
                                            <a href="admin_edit_blog.php?id=<?php echo $post['id']; ?>" class="btn-action edit">Edit</a>
                                            <a href="admin_process_delete_blog.php?id=<?php echo $post['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
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