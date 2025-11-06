<?php 
// Start session only if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
require_once 'db.php'; // Changed from require to require_once

// Fetch unread message count for logged-in users
$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt_unread = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt_unread->execute([$_SESSION['user_id']]);
        $unread_count = $stmt_unread->fetch(PDO::FETCH_ASSOC)['unread_count'];
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'NestMyPet'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="search_enhancements.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">

    <!-- Notification Area -->
    <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])) { ?>
    <div id="notification-bar" class="notification-bar <?php echo isset($_SESSION['success_message']) ? 'success' : 'error'; ?>">
        <div class="container">
            <p>
                <?php 
                if (isset($_SESSION['success_message'])) {
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                }
                ?>
            </p>
        </div>
    </div>
    <?php } ?>

    <!-- Pre-header -->
    <div class="pre-header">
        <div class="container">
            <div class="pre-header-links">
                <a href="search.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> Search Sitters</a>
                <a href="contact.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg> Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 13h18"/><path d="m16 8-4 4-4-4"/></svg> Invite a friend</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="logo1.png" alt="NestMyPet Logo" width="32" height="32" />
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="about.php">ABOUT US</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                 <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="header-icon-links">
                        <a href="messages.php" class="header-icon">
                            <i class="fas fa-envelope"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="unread-badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="#" class="header-icon"><i class="fas fa-question-circle"></i> Help</a>
                    </div>
                    <div class="user-menu-container">
                        <button class="user-menu-toggle" id="user-menu-toggle">
                            <span class="welcome-user">
                                <?php echo isset($_SESSION['user_firstname']) ? htmlspecialchars($_SESSION['user_firstname']) : 'Account'; ?>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown" id="user-dropdown">
                            <div class="dropdown-header">My Account</div>
                            <a href="dashboard.php">Dashboard</a>
                            <a href="edit_profile.php">Edit Profile</a>
                             <a href="profile.php">Profile</a>
                            <a href="logout.php" class="logout-link">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="btn btn-outline" id="login-modal-btn">Login</button>
                    <button class="btn btn-primary" id="signup-modal-btn">Join NestMyPet</button>
                <?php endif; ?>
            </div>
            <button class="mobile-nav-toggle" id="mobile-nav-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobile-nav">
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Search Sitters</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="#">Become a Sitter</a></li>
                    <li><a href="#">Invite a Friend</a></li>
                <?php endif; ?>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="mobile-nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-outline">My Dashboard</a>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <button class="btn btn-outline" id="mobile-login-btn">Login</button>
                    <button class="btn btn-primary" id="mobile-signup-btn">Join NestMyPet</button>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <!-- Auth Modal -->
    <div class="modal-overlay" id="auth-modal-overlay" style="display: none;">
        <div class="modal-content" id="auth-modal-content">
            <button class="modal-close-btn" id="modal-close-btn">&times;</button>
            
           <!-- Login View -->
<div id="login-view">
    <div class="form-header">
        <h2>Sign in to NestMyPet</h2>
        <p>Welcome back! Please sign in to continue.</p>
    </div>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="login_email">Email address</label>
            <input type="email" id="login_email" name="email" placeholder="Enter your email address" required>
        </div>
        <div class="form-group">
            <label for="login_password">Password</label>
            <input type="password" id="login_password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full-green">Continue</button>
    </form>
                <div class="form-footer">
                    <p>Don't have an account? <button type="button" class="form-link" id="show-signup-view">Sign up</button></p>
                </div>
            </div>

            <!-- Signup View -->
            <div id="signup-view" style="display: none;">
                 <div class="form-header">
                    <h2>Join NestMyPet</h2>
                    <p>First, tell us who you are.</p>
                </div>
                
                <!-- Role Selection -->
                <div class="role-selection">
                    <a href="signup_owner.php" class="role-box">
                        <i class="fas fa-paw"></i>
                        <div>
                            <h3>I'm a Pet Owner</h3>
                            <p>I want to find trusted care for my pets.</p>
                        </div>
                    </a>
                    <a href="signup_sitter.php" class="role-box">
                        <i class="fas fa-home"></i>
                        <div>
                            <h3>I want to be a Sitter</h3>
                            <p>I want to provide care for pets and earn money.</p>
                        </div>
                    </a>
                </div>

                <div class="form-footer">
                    <p>Already have an account? <button type="button" class="form-link" id="show-login-view">Sign in</button></p>
                </div>
            </div>
        </div>
    </div>