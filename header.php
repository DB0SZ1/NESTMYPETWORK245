<?php 
// Start session only if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
require_once 'config.php';
require_once 'db.php';

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
    <script>
        // Add this script to your website (nestmypet.unaux.com)
// Place it in the <head> or at the end of <body> in your HTML

(function() {
    'use strict';
    
    // Function to notify the app about session changes
    function notifyAppSession() {
        if (window.ReactNativeWebView) {
            const sessionData = {
                type: 'session',
                value: localStorage.getItem('user_session') || 
                       sessionStorage.getItem('user_session') ||
                       localStorage.getItem('userId') ||
                       sessionStorage.getItem('userId') ||
                       getCookie('user_session') ||
                       getCookie('userId')
            };
            
            window.ReactNativeWebView.postMessage(JSON.stringify(sessionData));
        }
    }
    
    // Helper function to get cookies
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    // Override localStorage.setItem to detect session saves
    const originalSetItem = localStorage.setItem;
    localStorage.setItem = function(key, value) {
        originalSetItem.apply(this, arguments);
        if (key.includes('session') || key.includes('user') || key.includes('User')) {
            notifyAppSession();
        }
    };
    
    // Override sessionStorage.setItem
    const originalSessionSetItem = sessionStorage.setItem;
    sessionStorage.setItem = function(key, value) {
        originalSessionSetItem.apply(this, arguments);
        if (key.includes('session') || key.includes('user') || key.includes('User')) {
            notifyAppSession();
        }
    };
    
    // Listen for storage events from other tabs/windows
    window.addEventListener('storage', function(e) {
        if (e.key && (e.key.includes('session') || e.key.includes('user') || e.key.includes('User'))) {
            notifyAppSession();
        }
    });
    
    // Notify on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', notifyAppSession);
    } else {
        notifyAppSession();
    }
    
    // Also notify after any form submission (login/signup)
    document.addEventListener('submit', function(e) {
        setTimeout(notifyAppSession, 500);
    });
    
    // Watch for modal close (user completed auth)
    const modalObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.id === 'auth-modal-overlay') {
                if (mutation.target.style.display === 'none') {
                    setTimeout(notifyAppSession, 300);
                }
            }
        });
    });
    
    // Start observing the modal
    const modal = document.getElementById('auth-modal-overlay');
    if (modal) {
        modalObserver.observe(modal, {
            attributes: true,
            attributeFilter: ['style']
        });
    }
    
})();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
<style>
/* =========================================
   REDESIGNED UNIFIED HEADER
   Clean, Modern, Cohesive Design
   ========================================= */

/* Reset previous header styles */
.main-header,
.pre-header {
    display: none !important;
}

/* New Unified Header */
.unified-header {
    background-color: #ffffff;
    padding: 0;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.unified-header .container {
    max-width: 1600px;
    padding: 0 1.5rem;
    margin: 0 auto;
}

.header-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
    gap: 3rem;
}

/* ===================================
   LOGO
   =================================== */
.header-logo {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.header-logo img {
    height: 75px;
    width: auto;
    transition: transform 0.2s ease;
}

.header-logo:hover img {
    transform: scale(1.02);
}

/* ===================================
   CENTERED NAVIGATION
   =================================== */
.header-nav {
    display: flex;
    align-items: center;
    gap: 2.5rem;
    flex: 1;
    justify-content: center;
}

.header-nav a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #4b5563;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.5rem 0;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.header-nav a:hover {
    color: #F7941E;
    border-bottom-color: #F7941E;
}

.header-nav a i {
    font-size: 1.1rem;
}

/* ===================================
   RIGHT SIDE ACTIONS
   =================================== */
.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

/* Messages Icon with Badge */
.icon-link {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border-radius: 8px;
    transition: background-color 0.2s ease;
    text-decoration: none;
    color: #4b5563;
}

.icon-link:hover {
    background-color: #f3f4f6;
}

.icon-link i {
    font-size: 1.3rem;
}

.badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #dc2626;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 0.65rem;
    font-weight: 700;
    line-height: 1;
    min-width: 18px;
    text-align: center;
}

/* Help Link */
.help-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #4b5563;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.help-link:hover {
    background-color: #f3f4f6;
    color: #111827;
}

/* Buttons */
.btn {
    padding: 0.6rem 1.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
    white-space: nowrap;
    text-decoration: none;
    display: inline-block;
}

.btn-login {
    background: transparent;
    border: 2px solid #d1d5db;
    color: #4b5563;
}

.btn-login:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #111827;
}

.btn-join {
    background-color: #F7941E;
    color: white;
    border: 2px solid #F7941E;
    font-weight: 700;
}

.btn-join:hover {
    background-color: #e0811b;
    border-color: #e0811b;
}

/* ===================================
   USER MENU DROPDOWN
   =================================== */
.user-menu {
    position: relative;
}

.user-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-family: var(--font-family);
    font-size: 0.95rem;
    font-weight: 600;
    color: #4b5563;
    transition: background-color 0.2s ease;
}

.user-toggle:hover {
    background-color: #f3f4f6;
}

.user-name {
    color: #111827;
}

.user-toggle i.fa-chevron-down {
    font-size: 0.7rem;
    transition: transform 0.2s ease;
}

.user-menu.active .fa-chevron-down {
    transform: rotate(180deg);
}

/* Dropdown Menu */
.user-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    min-width: 220px;
    overflow: hidden;
    z-index: 1001;
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.user-dropdown.active {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.dropdown-header {
    padding: 0.85rem 1.25rem;
    font-weight: 700;
    font-size: 0.8rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.user-dropdown a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.25rem;
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background-color 0.15s ease;
}

.user-dropdown a:hover {
    background-color: #f9fafb;
    color: #111827;
}

.user-dropdown a i {
    width: 18px;
    text-align: center;
    color: #6b7280;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

.logout-link {
    color: #dc2626 !important;
}

.logout-link:hover {
    background-color: #fef2f2 !important;
}

/* ===================================
   MOBILE NAVIGATION
   =================================== */
.mobile-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #374151;
    cursor: pointer;
    padding: 0.5rem;
}

.mobile-nav {
    display: none;
    background: white;
    position: fixed;
    top: 93px;
    left: 0;
    width: 100%;
    height: calc(70vh - 93px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 1.5rem 0;
    z-index: 999;
    overflow-y: auto;
}

.mobile-nav.active {
    display: block;
}

.mobile-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mobile-nav ul li {
    border-bottom: 1px solid #e5e7eb;
}

.mobile-nav ul li a,
.mobile-nav ul li button {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: #374151;
    font-weight: 600;
    font-size: 1rem;
    transition: background-color 0.15s ease;
    width: 100%;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
}

.mobile-nav ul li a:hover,
.mobile-nav ul li button:hover,
.mobile-nav ul li a:active,
.mobile-nav ul li button:active {
    background-color: #f3f4f6;
    color: #111827;
}

.mobile-nav ul li a i,
.mobile-nav ul li button i {
    width: 20px;
    text-align: center;
    color: #6b7280;
}

/* Mobile Action Buttons */
.mobile-actions {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 1rem;
}

.mobile-actions .btn {
    width: 100%;
    text-align: center;
}

/* ===================================
   RESPONSIVE BREAKPOINTS
   =================================== */
@media (max-width: 992px) {
    .header-nav,
    .header-actions .help-link,
    .header-actions .icon-link,
    .header-actions .btn,
    .header-actions .user-menu {
        display: none;
    }
    
    .mobile-toggle {
        display: block;
    }
    
    .header-wrapper {
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .header-logo img {
        height: 65px;
    }
    
    .unified-header .container {
        padding: 0 1rem;
    }
}

@media (max-width: 480px) {
    .header-logo img {
        height: 55px;
    }
}

</style>
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

    <!-- Unified Header -->
    <header class="unified-header">
        <div class="container">
            <div class="header-wrapper">
                
                <!-- Logo -->
                <a href="index.php" class="header-logo">
                    <img src="logo1.png" alt="NestMyPet Logo" />
                </a>

                <!-- Centered Navigation -->
                <nav class="header-nav">
                    <a href="search.php">
                        <i class="fas fa-search"></i>
                        View Sitters
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#">
                        <i class="fas fa-gift"></i>
                        Invite a Friend
                    </a>
                    <?php endif; ?>
                    
                    <a href="contact.php">
                       
                        Contact
                         <i class="fas fa-phone-alt"></i>
                    </a>
                </nav>

                <!-- Right Side Actions -->
                <div class="header-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Messages Icon -->
                        <a href="messages.php" class="icon-link">
                            <i class="fas fa-envelope"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Help Link -->
                        <a href="#" class="help-link">
                            <i class="fas fa-question-circle"></i>
                            Help
                        </a>
                        
                        <!-- User Menu -->
                        <div class="user-menu">
                            <button class="user-toggle" id="user-menu-toggle">
                                <span class="user-name">
                                    <?php echo isset($_SESSION['user_firstname']) ? htmlspecialchars($_SESSION['user_firstname']) : 'Account'; ?>
                                </span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="user-dropdown" id="user-dropdown">
                                <div class="dropdown-header">My Account</div>
                                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                                <a href="edit_profile.php"><i class="fas fa-edit"></i> Edit Profile</a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Not Logged In -->
                        <button class="btn btn-login" id="login-modal-btn">Login</button>
                        <button class="btn btn-join" id="signup-modal-btn">Join</button>
                    <?php endif; ?>
                </div>

                <!-- Mobile Toggle -->
                <button class="mobile-toggle" id="mobile-nav-toggle" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

            </div>
        </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobile-nav">
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> View Sitters</a></li>
                <li><a href="contact.php"><i class="fas fa-phone-alt"></i> Contact</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="messages.php">
                        <i class="fas fa-envelope"></i> 
                        Messages
                        <?php if ($unread_count > 0): ?>
                            <span class="badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="#"><i class="fas fa-gift"></i> Invite a Friend</a></li>
                    <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><button id="mobile-login-btn"><i class="fas fa-sign-in-alt"></i> Login</button></li>
                    <li><button id="mobile-signup-btn"><i class="fas fa-user-plus"></i> Join NestMyPet</button></li>
                <?php endif; ?>
            </ul>
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

    <script>

// Mobile Navigation Toggle
const mobileNavToggle = document.getElementById('mobile-nav-toggle');
const mobileNav = document.getElementById('mobile-nav');
const body = document.body;

// Toggle mobile navigation
mobileNavToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    mobileNav.classList.toggle('active');
    const icon = mobileNavToggle.querySelector('i');
    if (mobileNav.classList.contains('active')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

// Close mobile nav when clicking outside
document.addEventListener('click', (e) => {
    if (mobileNav && mobileNav.classList.contains('active')) {
        if (!mobileNav.contains(e.target) && !mobileNavToggle.contains(e.target)) {
            mobileNav.classList.remove('active');
            const icon = mobileNavToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }
});

// Close mobile nav when clicking a link (except modal triggers)
mobileNav?.querySelectorAll('a, button').forEach(link => {
    link.addEventListener('click', (e) => {
        // Don't close for modal triggers
        if (link.id === 'mobile-login-btn' || link.id === 'mobile-signup-btn') {
            return;
        }
        mobileNav.classList.remove('active');
        const icon = mobileNavToggle.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    });
});

// User Menu Dropdown Toggle
const userMenuToggle = document.getElementById('user-menu-toggle');
const userDropdown = document.getElementById('user-dropdown');
const userMenu = document.querySelector('.user-menu');

userMenuToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    userMenu.classList.toggle('active');
    userDropdown.classList.toggle('active');
});

// Close user dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (userMenu && !userMenu.contains(e.target)) {
        userMenu.classList.remove('active');
        userDropdown?.classList.remove('active');
    }
});

// Auth Modal Handling
const authModalOverlay = document.getElementById('auth-modal-overlay');
const loginView = document.getElementById('login-view');
const signupView = document.getElementById('signup-view');
const modalCloseBtn = document.getElementById('modal-close-btn');

// Desktop buttons
const loginModalBtn = document.getElementById('login-modal-btn');
const signupModalBtn = document.getElementById('signup-modal-btn');

// Mobile buttons
const mobileLoginBtn = document.getElementById('mobile-login-btn');
const mobileSignupBtn = document.getElementById('mobile-signup-btn');

// Show login modal
function showLoginModal() {
    authModalOverlay.style.display = 'flex';
    loginView.style.display = 'block';
    signupView.style.display = 'none';
    body.style.overflow = 'hidden';
}

// Show signup modal
function showSignupModal() {
    authModalOverlay.style.display = 'flex';
    loginView.style.display = 'none';
    signupView.style.display = 'block';
    body.style.overflow = 'hidden';
}

// Close modal
function closeModal() {
    authModalOverlay.style.display = 'none';
    body.style.overflow = '';
}

// Desktop button handlers
loginModalBtn?.addEventListener('click', showLoginModal);
signupModalBtn?.addEventListener('click', showSignupModal);

// Mobile button handlers
mobileLoginBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    showLoginModal();
    // Close mobile nav
    mobileNav?.classList.remove('active');
    const icon = mobileNavToggle?.querySelector('i');
    if (icon) {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

mobileSignupBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    showSignupModal();
    // Close mobile nav
    mobileNav?.classList.remove('active');
    const icon = mobileNavToggle?.querySelector('i');
    if (icon) {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

// Modal close button
modalCloseBtn?.addEventListener('click', closeModal);

// Close modal when clicking overlay
authModalOverlay?.addEventListener('click', (e) => {
    if (e.target === authModalOverlay) {
        closeModal();
    }
});

// Switch between login and signup views
document.getElementById('show-signup-view')?.addEventListener('click', () => {
    loginView.style.display = 'none';
    signupView.style.display = 'block';
});

document.getElementById('show-login-view')?.addEventListener('click', () => {
    signupView.style.display = 'none';
    loginView.style.display = 'block';
});

// Auto-hide notification bar
const notificationBar = document.getElementById('notification-bar');
if (notificationBar) {
    setTimeout(() => {
        notificationBar.style.opacity = '0';
        setTimeout(() => {
            notificationBar.style.display = 'none';
        }, 500);
    }, 5000);
}

</script>