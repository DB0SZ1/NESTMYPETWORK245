<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// Get user ID from URL, ensure it's a valid number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$profile_user_id = intval($_GET['id']);

// Fetch profile user's data and their pets
$user = null;
$pets = [];
try {
    $stmt_user = $pdo->prepare("SELECT id, fullname, bio, created_at FROM users WHERE id = ?");
    $stmt_user->execute([$profile_user_id]);
    $user = $stmt_user->fetch();

    if ($user) {
        $stmt_pets = $pdo->prepare("SELECT name, breed, age FROM pets WHERE user_id = ? ORDER BY name ASC");
        $stmt_pets->execute([$profile_user_id]);
        $pets = $stmt_pets->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Profile page error: " . $e->getMessage());
    exit('A database error occurred.');
}

if (!$user) {
    $pageTitle = "User Not Found";
    include 'header.php';
    echo "<main class='container' style='text-align:center; padding: 4rem 0;'><p>Sorry, this user could not be found.</p></main>";
    include 'footer.php';
    exit();
}

$pageTitle = htmlspecialchars($user['fullname']) . "'s Profile";
include 'header.php';
?>

<main class="profile-page">
    <div class="container">
        <div class="profile-layout">
            <aside class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1><?php echo htmlspecialchars($user['fullname']); ?></h1>
                    <p class="member-since">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    <a href="#" class="btn btn-primary btn-full-green">Contact <?php echo htmlspecialchars(explode(' ', $user['fullname'])[0]); ?></a>
                </div>
                <div class="profile-card">
                    <h3>About Me</h3>
                    <p class="bio-text">
                        <?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'This user has not written a bio yet.'; ?>
                    </p>
                </div>
            </aside>
            <section class="profile-main">
                <div class="dashboard-card">
                    <h2><?php echo htmlspecialchars(explode(' ', $user['fullname'])[0]); ?>'s Pets</h2>
                    <?php if (empty($pets)): ?>
                        <p><?php echo htmlspecialchars(explode(' ', $user['fullname'])[0]); ?> has not added any pets yet.</p>
                    <?php else: ?>
                        <div class="pet-list">
                            <?php foreach ($pets as $pet): ?>
                                <div class="pet-card">
                                    <div class="pet-info">
                                        <h4><?php echo htmlspecialchars($pet['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($pet['breed']); ?>, Age: <?php echo htmlspecialchars($pet['age']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                 <div class="dashboard-card">
                    <h2>Reviews</h2>
                    <p>No reviews yet. Be the first to leave one!</p>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>