<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to become a sitter.";
    header('Location: index.php');
    exit();
}

// Check if user is ALREADY a sitter
$is_sitter = false;
try {
    $stmt = $pdo->prepare("SELECT is_sitter FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $is_sitter = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$pageTitle = "Become a Sitter";
include 'header.php';
?>

<main class="dashboard-page">
    <div class="container" style="max-width: 700px;">
        <div class="dashboard-card">
            <?php if ($is_sitter): ?>
                <h2>You're already a sitter!</h2>
                <p>You're all set. Your services are visible in search.</p>
                <a href="search.php" class="btn btn-primary">View Search Page</a>
            <?php else: ?>
                <h2>Become a NestMyPet Sitter</h2>
                <p>Start earning by offering pet care services. Just fill out your first service below to get started.</p>
                
                <form action="process_become_sitter.php" method="POST">
                    <div class="form-group">
                        <label for="service_type">Service to offer</label>
                        <select id="service_type" name="service_type" class="form-control">
                            <option value="boarding">Boarding (in your home)</option>
                            <option value="daycare">Day Care (in your home)</option>
                            <option value="walking">Dog Walking</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price_per_night">Price per night (£)</label>
                        <input type="number" step="0.01" id="price_per_night" name="price_per_night" class="form-control" placeholder="e.g., 35.00" required>
                    </div>
                     <div class="form-group">
                        <label for="headline">Your Sitter Headline</label>
                        <input type="text" id="headline" name="headline" class="form-control" placeholder="e.g., Loving dog sitter with a big garden!" required>
                        <small>This is the first thing owners will see in search.</small>
                    </div>
                    <div class="form-group">
                        <label for="sitter_about_me">About You (as a sitter)</label>
                        <textarea id="sitter_about_me" name="sitter_about_me" rows="5" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full-green">Start Earning</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>