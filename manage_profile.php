<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user = null;
try {
    // Fetch all the new user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching user for edit: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not load profile data.";
    header('Location: dashboard.php');
    exit();
}

if (!$user) {
    header('Location: logout.php');
    exit();
}

$pageTitle = "Edit Your Profile";
include 'header.php';
?>

<main class="profile-edit-page">
    <div class="container">
        <nav class="profile-tabs">
            <a href="#" class="tab-link active">Basic Info</a>
            <a href="#" class="tab-link">Phone Numbers</a>
            <a href="#" class="tab-link">Payment Methods</a>
        </nav>

        <div class="tab-content">
            <div class="form-box">
                <div class="form-header" style="text-align: left; margin-bottom: 2rem;">
                    <h2>Let's start with the basics</h2>
                </div>
                
                <form action="process_update_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <h3 class="section-title">Add your address</h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="address_street">Street Name and Number</label>
                                <input type="text" id="address_street" name="address_street" value="<?php echo htmlspecialchars($user['address_street'] ?? ''); ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="address_details">Additional Address Details (optional)</label>
                                <input type="text" id="address_details" name="address_details" value="<?php echo htmlspecialchars($user['address_details'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="address_city">City *</label>
                                <input type="text" id="address_city" name="address_city" value="<?php echo htmlspecialchars($user['address_city'] ?? ''); ?>" required>
                            </div>
                             <div class="form-group">
                                <label for="address_postcode">Postcode *</label>
                                <input type="text" id="address_postcode" name="address_postcode" value="<?php echo htmlspecialchars($user['address_postcode'] ?? ''); ?>" required>
                            </div>
                             <div class="form-group full-width">
                                <label for="address_country">Country</label>
                                <input type="text" id="address_country" name="address_country" value="<?php echo htmlspecialchars($user['address_country'] ?? 'United Kingdom'); ?>">
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider">

                     <div class="form-section">
                        <h3 class="section-title">Profile Photo</h3>
                        <div class="photo-upload-area">
                            <div class="photo-preview">
                                <?php if (!empty($user['profile_photo'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="photo-upload-text">
                                <p>This is the first photo pet sitters will see. We recommend using a well-lit, clear photo of your face (without sunglasses).</p>
                                <label for="profile_photo" class="btn btn-secondary"><i class="fas fa-upload"></i> Upload Your Photo</label>
                                <input type="file" id="profile_photo" name="profile_photo" class="hidden-file-input">
                            </div>
                        </div>
                    </div>
                    
                    <hr class="form-divider">

                    <div class="form-actions">
                         <button type="submit" class="btn btn-primary btn-full-green">Save & Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>