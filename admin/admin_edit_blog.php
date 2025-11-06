<?php
require_once 'admin_check.php';
require_once '../db.php';

$post = null;
$edit_mode = false;
$pageTitle = "Add New Blog Post";

// Check if editing an existing post
if (isset($_GET['id'])) {
    $edit_mode = true;
    $post_id = $_GET['id'];
    $pageTitle = "Edit Blog Post";
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) {
            // Post not found, redirect
            $_SESSION['admin_message'] = "Blog post not found.";
            $_SESSION['admin_message_type'] = 'error';
            header('Location: admin_blog.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        // Handle error
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="style.css">
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
                <h1><?php echo $pageTitle; ?></h1>
            </header>

            <section class="main-area">
                <div class="content-panel form-panel">
                    <form action="admin_process_save_blog.php" method="POST">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="title">Post Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="image_url">Image URL (Optional)</label>
                            <input type="url" id="image_url" name="image_url" class="form-control" placeholder="https://placehold.co/800x400" value="<?php echo htmlspecialchars($post['image_url'] ?? ''); ?>">
                            <small>Enter the full URL (e.g., https://...) of an image for the post.</small>
                        </div>

                        <div class="form-group">
                            <label for="content">Content (HTML allowed)</label>
                            <textarea id="content" name="content" class="form-control" rows="15" required><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                            <small>You can use basic HTML tags like &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;.</small>
                        </div>

                        <div class="form-group">
                            <label for="snippet">Snippet (Optional)</label>
                            <textarea id="snippet" name="snippet" class="form-control" rows="3"><?php echo htmlspecialchars($post['snippet'] ?? ''); ?></textarea>
                            <small>A short summary for the blog listing page. If left blank, the first 150 characters of the content will be used.</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-action approve">Save Post</button>
                            <a href="admin_blog.php" class="btn-action reject" style="background-color: #6B7280;">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>