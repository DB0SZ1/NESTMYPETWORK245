<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// --- Get Sitter ID from URL ---
if (!isset($_GET['id'])) {
    header('Location: search.php');
    exit();
}
$sitter_id = $_GET['id'];

// --- Fetch Sitter Data ---
$sitter = null;
$profile_data = [];
$services_offered = [];
$sitter_services = [];
$album_photos = [];

// Cache busting timestamp
$cache_bust = time();

try {
    // Fetch user details INCLUDING cover_photo_path
    $stmt_user = $pdo->prepare("
        SELECT id, fullname, city, country, profile_photo_path, cover_photo_path, 
               created_at, role, profile_verified 
        FROM users WHERE id = ? AND is_sitter = 1
    ");
    $stmt_user->execute([$sitter_id]);
    $sitter = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // Redirect if sitter not found
    if (!$sitter) {
        $_SESSION['error_message'] = "Sitter profile not found.";
        header('Location: search.php');
        exit();
    }

    // Fetch photo album (max 15 photos)
    $stmt_album = $pdo->prepare("
        SELECT id, photo_path, caption, uploaded_at 
        FROM photo_albums 
        WHERE user_id = ? 
        ORDER BY uploaded_at DESC 
        LIMIT 15
    ");
    $stmt_album->execute([$sitter_id]);
    $album_photos = $stmt_album->fetchAll(PDO::FETCH_ASSOC);

    // Fetch host profile (Home environment, experience, etc.)
    $stmt_host = $pdo->prepare("
        SELECT sitter_type, date_of_birth, home_type, outdoor_space,
               smokes_indoors, has_children, children_ages, lives_alone, other_adults,
               owns_pets, owned_pet_details, years_experience, animal_background,
               qualifications, availability_notes, max_pets_at_once, breed_size_restrictions,
               can_administer_medication, emergency_transport_available,
               training_video_watched, offers_home_sitting, dbs_check_status,
               profile_photo_living_room, profile_photo_sleeping_area, profile_photo_outdoor
        FROM host_profiles WHERE user_id = ?
    ");
    $stmt_host->execute([$sitter_id]);
    $profile_data = $stmt_host->fetch(PDO::FETCH_ASSOC);

    // Fetch services offered (from host_services for the grid view)
    $stmt_services_offered = $pdo->prepare("
        SELECT service_name, max_pets, breed_size_restrictions,
               can_administer_meds, has_emergency_transport
        FROM host_services WHERE host_user_id = ?
    ");
    $stmt_services_offered->execute([$sitter_id]);
    $services_offered = $stmt_services_offered->fetchAll(PDO::FETCH_ASSOC);

    // Fetch sitter service details (price, headline from sitter_services)
    $stmt_sitter_service = $pdo->prepare("
        SELECT service_type, price_per_night, headline, sitter_about_me
        FROM sitter_services WHERE user_id = ?
    ");
    $stmt_sitter_service->execute([$sitter_id]);
    $sitter_services = $stmt_sitter_service->fetchAll(PDO::FETCH_ASSOC);

    // Redirect if sitter has no services setup
    if (empty($sitter_services)) {
        $_SESSION['error_message'] = "This sitter has not completed their service setup.";
        header('Location: search.php');
        exit();
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error_message'] = "A database error occurred.";
    header('Location: search.php');
    exit();
}

// --- Prepare data for display ---
list($firstname) = explode(' ', $sitter['fullname'], 2);
$display_name = htmlspecialchars($firstname);

$location = (!empty($sitter['city']) ? htmlspecialchars($sitter['city']) . ', ' : '') . (!empty($sitter['country']) ? htmlspecialchars($sitter['country']) : '');
if (empty(trim($location))) {
    $location = "Location not specified";
}
$joined_date = date('F Y', strtotime($sitter['created_at']));
$first_service = $sitter_services[0]; // Get the first service for the booking form and header
include 'header.php';
$pageTitle = "Sitter Profile: " . $display_name;
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="search_enhancements.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">

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


<!-- Mobile Navigation -->
<div class="mobile-nav" id="mobile-nav">
    <nav>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="search.php"><i class="fas fa-search"></i> Search Sitters</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="#" id="mobile-login-btn"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="#" id="mobile-signup-btn"><i class="fas fa-user-plus"></i> Join NestMyPet</a></li>
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

mobileNav?.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', (e) => {
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
const userMenuContainer = document.querySelector('.user-menu-container');

userMenuToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    userMenuContainer.classList.toggle('active');
    userDropdown.classList.toggle('active');
});

document.addEventListener('click', (e) => {
    if (userMenuContainer && !userMenuContainer.contains(e.target)) {
        userMenuContainer.classList.remove('active');
        userDropdown?.classList.remove('active');
    }
});

// Auth Modal Handling
const authModalOverlay = document.getElementById('auth-modal-overlay');
const loginView = document.getElementById('login-view');
const signupView = document.getElementById('signup-view');
const modalCloseBtn = document.getElementById('modal-close-btn');
const loginModalBtn = document.getElementById('login-modal-btn');
const signupModalBtn = document.getElementById('signup-modal-btn');
const mobileLoginBtn = document.getElementById('mobile-login-btn');
const mobileSignupBtn = document.getElementById('mobile-signup-btn');

function showLoginModal() {
    authModalOverlay.style.display = 'flex';
    loginView.style.display = 'block';
    signupView.style.display = 'none';
    body.style.overflow = 'hidden';
}

function showSignupModal() {
    authModalOverlay.style.display = 'flex';
    loginView.style.display = 'none';
    signupView.style.display = 'block';
    body.style.overflow = 'hidden';
}

function closeModal() {
    authModalOverlay.style.display = 'none';
    body.style.overflow = '';
}

loginModalBtn?.addEventListener('click', showLoginModal);
signupModalBtn?.addEventListener('click', showSignupModal);

mobileLoginBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    showLoginModal();
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
    mobileNav?.classList.remove('active');
    const icon = mobileNavToggle?.querySelector('i');
    if (icon) {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

modalCloseBtn?.addEventListener('click', closeModal);

authModalOverlay?.addEventListener('click', (e) => {
    if (e.target === authModalOverlay) {
        closeModal();
    }
});

document.getElementById('show-signup-view')?.addEventListener('click', () => {
    loginView.style.display = 'none';
    signupView.style.display = 'block';
});

document.getElementById('show-login-view')?.addEventListener('click', () => {
    signupView.style.display = 'none';
    loginView.style.display = 'block';
});

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
<style>
/* Enhanced Pre-header styles */
.pre-header {
    background-color: #ffffff;
    padding: 0;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.pre-header .container {
    max-width: 1600px;
    padding: 0 1.25rem;
}

.pre-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0;
    gap: 2rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 0 0 auto;
}

.header-logo {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.header-logo img {
    height: 70px;
    width: auto;
}

.nav-links-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-links-left a {
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

.nav-links-left a:hover {
    background-color: #f3f4f6;
    color: #111827;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: auto;
}

.nav-link-right {
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

.nav-link-right:hover {
    background-color: #f3f4f6;
    color: #111827;
}

.icon-badge-link {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    transition: background-color 0.2s ease;
    text-decoration: none;
    color: #4b5563;
}

.icon-badge-link:hover {
    background-color: #f3f4f6;
}

.unread-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    background: #dc2626;
    color: white;
    border-radius: 10px;
    padding: 2px 5px;
    font-size: 0.65rem;
    font-weight: 700;
}

.header-right .btn {
    padding: 0.5rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
    white-space: nowrap;
    margin-left: 0.25rem;
}

.header-right .btn-outline {
    background: transparent;
    border: 1px solid #d1d5db;
    color: #4b5563;
}

.header-right .btn-outline:hover {
    background-color: #f3f4f6;
}

.header-right .btn-primary {
    background-color: #F7941E;
    color: white;
    font-weight: 700;
}

.user-menu-container {
    position: relative;
}

.user-menu-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: transparent;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 600;
    color: #4b5563;
}

.user-menu-toggle:hover {
    background-color: #f3f4f6;
}

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
    z-index: 1001;
}

.user-dropdown.active {
    display: block;
}

.dropdown-header {
    padding: 0.85rem 1.25rem;
    font-weight: 700;
    font-size: 0.8rem;
    color: #6b7280;
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
}

.user-dropdown a:hover {
    background-color: #f9fafb;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

.logout-link {
    color: #dc2626 !important;
}

.mobile-nav-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #374151;
    cursor: pointer;
}

.mobile-nav {
    display: none;
    background: white;
    position: fixed;
    top: 72px;
    left: 0;
    width: 100%;
    height: calc(50vh - 72px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 1.5rem 0;
    z-index: 999;
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

.mobile-nav ul li a {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: #374151;
    font-weight: 600;
}

@media (max-width: 992px) {
    .header-left .nav-links-left,
    .header-right {
        display: none;
    }
    
    .mobile-nav-toggle {
        display: block;
    }
}
</style>

<style>
/* ===================================
   REVAMPED PROFILE - MODERN DESIGN
   (All header conflicts removed)
   =================================== */
   
:root {
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-success: linear-gradient(135deg, #00a862, #00c875);
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.revamped-profile {
    background: #f8f9fa;
    min-height: 100vh;
    margin-top: 0;
    padding-top: 0;
}

/* ===================================
   HERO SECTION
   =================================== */
.profile-hero {
    position: relative;
    width: 100%;
    height: 400px;
    overflow: hidden;
    margin-bottom: -120px;
}

.hero-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-cover-placeholder {
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-cover-placeholder i {
    font-size: 80px;
    color: rgba(255, 255, 255, 0.3);
}

.hero-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
}

/* ===================================
   PROFILE HEADER CARD
   =================================== */
.profile-header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    position: relative;
}

.profile-header-card {
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: var(--shadow-lg);
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 32px;
    align-items: center;
}

.profile-avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.profile-avatar-large {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    overflow: hidden;
    border: 6px solid white;
    box-shadow: var(--shadow-md);
    background: #f0f0f0;
}

.profile-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e0e0e0, #f5f5f5);
}

.avatar-placeholder i {
    font-size: 64px;
    color: #bbb;
}

.verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    background: #d4edda;
    color: #155724;
}

.verified-badge.unverified {
    background: #fff3cd;
    color: #856404;
}

.profile-info-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
    text-align: left;
}

.profile-name {
    font-size: 36px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    text-align: left;
}

.profile-tagline {
    font-size: 16px;
    color: #666;
    font-style: italic;
    margin: 0;
    line-height: 1.5;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 8px 0;
}

.rating-display .stars {
    display: flex;
    gap: 4px;
    color: #fbbf24;
    font-size: 18px;
}

.rating-display .rating-text {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
}
.sitter-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 50px;
    background: var(--gradient-success);
    color: white;
    font-size: 14px;
    font-weight: 600;
    width: fit-content;
}

.profile-meta {
    display: flex;
    align-items: center;
    gap: 24px;
    color: #666;
    font-size: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.meta-item i {
    color: #00a862;
}

.profile-cta-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-message {
    padding: 16px 32px;
    background: var(--gradient-success);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 168, 98, 0.3);
    text-decoration: none;
}

.btn-message:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 168, 98, 0.4);
}



/* ===================================
   CONTENT GRID
   =================================== */
.profile-content {
    max-width: 1200px;
    margin: 32px auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
}

.main-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sidebar-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* ===================================
   MODERN CARDS
   =================================== */
.modern-card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: var(--shadow-md);
}

.card-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.card-title {
    font-size: 24px;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-title i {
    color: #00a862;
}

/* ===================================
   BOOKING CARD (STICKY SIDEBAR)
   =================================== */
.booking-card {
    position: sticky;
    top: 100px;
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: var(--shadow-lg);
    border: 2px solid #00a862;
}

.booking-header {
    text-align: center;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 2px solid #f0f0f0;
}

.booking-service {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.booking-price {
    font-size: 32px;
    font-weight: 700;
    color: #00a862;
}

.booking-price small {
    font-size: 16px;
    font-weight: 400;
    color: #666;
}

.form-group-modern {
    margin-bottom: 20px;
}

.form-group-modern label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.form-control-modern {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.2s ease;
    font-family: inherit;
}

.form-control-modern:focus {
    outline: none;
    border-color: #00a862;
    box-shadow: 0 0 0 4px rgba(0, 168, 98, 0.1);
}

.date-range-group {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 12px;
    align-items: end;
}

.date-arrow {
    font-size: 20px;
    color: #00a862;
    margin-bottom: 14px;
}

.btn-book {
    width: 100%;
    padding: 18px;
    background: var(--gradient-success);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 168, 98, 0.3);
}

.btn-book:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 168, 98, 0.4);
}

/* ===================================
   ABOUT SECTION
   =================================== */
.about-text {
    font-size: 16px;
    line-height: 1.8;
    color: #333;
}

.section-subtitle {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 24px 0 16px 0;
}

/* ===================================
   INFO LIST
   =================================== */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.info-item:hover {
    background: #e9ecef;
}

.info-label {
    font-weight: 600;
    color: #666;
}

.info-value {
    color: #1a1a1a;
    text-align: right;
}

/* ===================================
   SERVICES GRID
   =================================== */
.services-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
}

.service-card-modern {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: default;
}

.service-card-modern:hover {
    border-color: #00a862;
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.service-icon-modern {
    width: 64px;
    height: 64px;
    background: var(--gradient-success);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.service-icon-modern i {
    font-size: 28px;
    color: white;
}

.service-name {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.service-detail {
    font-size: 13px;
    color: #666;
}

.service-restriction {
    font-size: 12px;
    color: #ff9800;
    font-style: italic;
    margin-top: 8px;
}

/* ===================================
   CAPABILITIES
   =================================== */
.capabilities-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}

.capability-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-radius: 16px;
    transition: all 0.2s ease;
}

.capability-card.active {
    background: #d4edda;
    border: 2px solid #00a862;
}

.capability-card.inactive {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    opacity: 0.6;
}

.capability-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.capability-card.active .capability-icon {
    background: #00a862;
    color: white;
}

.capability-card.inactive .capability-icon {
    background: #e9ecef;
    color: #999;
}

.capability-text {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
}

/* ===================================
   PHOTO ALBUM
   =================================== */
.photo-count {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.photo-album-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.album-thumbnail {
    position: relative;
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.album-thumbnail:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.album-thumbnail:hover .thumbnail-overlay {
    opacity: 1;
}

.album-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.thumbnail-overlay i {
    color: white;
    font-size: 32px;
}

/* ===================================
   HOME PHOTOS
   =================================== */
.home-photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.home-photo-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    aspect-ratio: 4/3;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.home-photo-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.home-photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-label-modern {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    color: white;
    padding: 24px 16px 12px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
}

/* ===================================
   LIGHTBOX
   =================================== */
.lightbox-modern {
    display: none;
    position: fixed;
    z-index: 99999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.lightbox-modern img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.lightbox-close-modern {
    position: absolute;
    top: 30px;
    right: 40px;
    font-size: 48px;
    color: white;
    cursor: pointer;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transition: all 0.3s ease;
    z-index: 100000;
}

.lightbox-close-modern:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: rotate(90deg);
}

/* ===================================
   RESPONSIVE DESIGN
   =================================== */
@media (max-width: 1024px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
    
    .booking-card {
        position: relative;
        top: 0;
    }
    
    .sidebar-content {
        order: -1;
    }
}

@media (max-width: 768px) {
    .profile-hero {
        height: 300px;
        margin-bottom: -100px;
    }
    
    .profile-header-card {
        grid-template-columns: 1fr;
        padding: 32px 24px;
        text-align: center;
        gap: 24px;
    }
    
    .profile-avatar-large {
        width: 140px;
        height: 140px;
    }
    
    .profile-info-section {
        align-items: center;
    }
    
    .profile-name {
        font-size: 28px;
    }
    
    .profile-meta {
        flex-direction: column;
        gap: 12px;
    }
    
    .profile-cta-section {
        width: 100%;
    }
    
    .modern-card {
        padding: 24px 20px;
    }
    
    .card-title {
        font-size: 20px;
    }
    
    .date-range-group {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .date-arrow {
        display: none;
    }
    
    .services-grid-modern,
    .capabilities-grid-modern {
        grid-template-columns: 1fr;
    }
    
    .photo-album-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .home-photos-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .profile-hero {
        height: 250px;
        margin-bottom: -80px;
    }
    
    .profile-header-card {
        padding: 24px 16px;
    }
    
    .profile-avatar-large {
        width: 120px;
        height: 120px;
    }
    
    .profile-name {
        font-size: 24px;
    }
    
    .profile-content {
        padding: 0 16px;
    }
    
    .modern-card {
        padding: 20px 16px;
        border-radius: 16px;
    }
    
    .booking-card {
        padding: 24px 20px;
    }
    
    .photo-album-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
}

/* ===================================
   ANIMATIONS
   =================================== */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modern-card {
    animation: slideUp 0.5s ease forwards;
}

.modern-card:nth-child(1) { animation-delay: 0.1s; }
.modern-card:nth-child(2) { animation-delay: 0.2s; }
.modern-card:nth-child(3) { animation-delay: 0.3s; }
.modern-card:nth-child(4) { animation-delay: 0.4s; }
</style>

<main class="revamped-profile">
    <!-- Hero Section -->
    <section class="profile-hero">
        <?php 
        $coverPath = $sitter['cover_photo_path'] ?? null;
        if ($coverPath && !empty($coverPath)):
        ?>
            <img src="<?php echo htmlspecialchars($coverPath); ?>?v=<?php echo $cache_bust; ?>" alt="Cover Photo" class="hero-cover">
        <?php else: ?>
            <div class="hero-cover-placeholder">
                <i class="fa-solid fa-image"></i>
            </div>
        <?php endif; ?>
        <div class="hero-overlay"></div>
    </section>

    <!-- Profile Header Card -->
    <div class="profile-header-container">
        <div class="profile-header-card">
            <div class="profile-avatar-section">
                <div class="profile-avatar-large">
                    <?php 
                    $photoPath = $sitter['profile_photo_path'] ?? null;
                    if ($photoPath && !empty($photoPath)):
                    ?>
                        <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="<?php echo $display_name; ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="verified-badge <?php echo $sitter['profile_verified'] ? '' : 'unverified'; ?>">
                    <i class="fa-solid <?php echo $sitter['profile_verified'] ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <span><?php echo $sitter['profile_verified'] ? 'Verified' : 'Unverified'; ?></span>
                </div>
            </div>

           <div class="profile-info-section">
                <h1 class="profile-name"><?php echo $display_name; ?></h1>
                
                <?php if (!empty($first_service['headline'])): ?>
                <p class="profile-tagline"><?php echo htmlspecialchars($first_service['headline']); ?></p>
                <?php endif; ?>
                
                <div class="rating-display">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <span class="rating-text">4.8 (24 reviews)</span>
                </div>
                
                <div class="sitter-badge">
                    <i class="fa-solid fa-paw"></i>
                    <span>Professional Pet Sitter</span>
                </div>
                <div class="profile-meta">
                    <div class="meta-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span><?php echo $location; ?></span>
                    </div>
                    <?php if (!empty($profile_data['years_experience'])): ?>
                    <div class="meta-item">
                        <i class="fa-solid fa-award"></i>
                        <span><?php echo $profile_data['years_experience']; ?>+ years experience</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-cta-section">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="messages.php?conversation_with=<?php echo $sitter['id']; ?>" class="btn-message">
                        <i class="fa-solid fa-envelope"></i> Message <?php echo $display_name; ?>
                    </a>
                <?php else: ?>
                    <button class="btn-message" onclick="document.getElementById('login-modal-btn').click()">
                        <i class="fa-solid fa-envelope"></i> Login to Message
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="profile-content">
        <!-- Main Content Column -->
        <div class="main-content">
            
          <!-- About Section -->
            <div class="modern-card">
                <div class="card-header-modern">
                    <h2 class="card-title">
                        <i class="fa-solid fa-user-circle"></i>
                        About <?php echo $display_name; ?>
                    </h2>
                </div>
                
                <?php if (!empty($first_service['sitter_about_me'])): ?>
                <p class="about-text"><?php echo nl2br(htmlspecialchars($first_service['sitter_about_me'])); ?></p>
                <?php else: ?>
                <p class="about-text" style="color: #999; font-style: italic;">No description provided yet.</p>
                <?php endif; ?>
            </div>

            <!-- Photo Album -->
            <?php if (!empty($album_photos)): ?>
            <div class="modern-card">
                <div class="card-header-modern">
                    <h2 class="card-title">
                        <i class="fa-solid fa-images"></i>
                        Photo Gallery
                    </h2>
                    <span class="photo-count"><?php echo count($album_photos); ?> photos</span>
                </div>
                
                <div class="photo-album-grid">
                    <?php foreach ($album_photos as $photo): ?>
                        <div class="album-thumbnail" onclick="openLightboxModern('<?php echo htmlspecialchars($photo['photo_path']); ?>')">
                            <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>?v=<?php echo $cache_bust; ?>" alt="Album photo">
                            <div class="thumbnail-overlay">
                                <i class="fa-solid fa-expand"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Services Offered -->
            <?php if (!empty($services_offered)): ?>
            <div class="modern-card">
                <div class="card-header-modern">
                    <h2 class="card-title">
                        <i class="fa-solid fa-briefcase"></i>
                        Services Offered
                    </h2>
                </div>
                
                <div class="services-grid-modern">
                    <?php foreach ($services_offered as $service): ?>
                        <div class="service-card-modern">
                            <div class="service-icon-modern">
                                <i class="fa-solid <?php 
    echo match($service['service_name']) {
        'boarding' => 'fa-house',
        'daycare' => 'fa-sun',
        'walking' => 'fa-person-walking',
        'dropin' => 'fa-clock',
        'smallpet' => 'fa-dove',
        'homesitting' => 'fa-house-user',
        default => 'fa-paw'
    };
?>"></i>
                            </div>
                            <div class="service-name"><?php 
    echo match($service['service_name']) {
        'boarding' => 'Pet Boarding',
        'daycare' => 'Dog Day Care',
        'walking' => 'Dog Walking',
        'dropin' => 'Cat Drop-In Visits',
        'smallpet' => 'Small Pet Care',
        'homesitting' => 'House Sitting',
        default => ucfirst($service['service_name'])
    };
?></div>
                            <div class="service-detail">Max <?php echo $service['max_pets']; ?> pet<?php echo $service['max_pets'] > 1 ? 's' : ''; ?></div>
                            <?php if (!empty($service['breed_size_restrictions'])): ?>
                            <div class="service-restriction"><?php echo htmlspecialchars($service['breed_size_restrictions']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

           <!-- About My Care (Merged Experience + Capabilities) -->
            <?php if (!empty($profile_data)): ?>
            <div class="modern-card">
                <div class="card-header-modern">
                    <h2 class="card-title">
                        <i class="fa-solid fa-heart"></i>
                        About My Care
                    </h2>
                </div>
                
                <h3 class="section-subtitle">Experience & Background</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Years of Experience</span>
                        <span class="info-value"><?php echo $profile_data['years_experience']; ?> years</span>
                    </div>
                    <?php if (!empty($profile_data['qualifications'])): ?>
                    <div class="info-item">
                        <span class="info-label">Qualifications</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($profile_data['qualifications'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile_data['animal_background'])): ?>
                    <div class="info-item">
                        <span class="info-label">Background</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($profile_data['animal_background'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <h3 class="section-subtitle" style="margin-top: 32px;">What I Can Offer</h3>
                <div class="capabilities-grid-modern">
                    <div class="capability-card <?php echo $profile_data['can_administer_medication'] ? 'active' : 'inactive'; ?>">
                        <div class="capability-icon">
                            <i class="fa-solid fa-pills"></i>
                        </div>
                        <span class="capability-text">Can Administer Medication</span>
                    </div>
                    
                    <div class="capability-card <?php echo $profile_data['emergency_transport_available'] ? 'active' : 'inactive'; ?>">
                        <div class="capability-icon">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <span class="capability-text">Emergency Transport Available</span>
                    </div>
                    
                    <div class="capability-card <?php echo $profile_data['training_video_watched'] ? 'active' : 'inactive'; ?>">
                        <div class="capability-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <span class="capability-text">Training Completed</span>
                    </div>
                    
                    <?php if ($profile_data['offers_home_sitting']): ?>
                    <div class="capability-card <?php echo ($profile_data['dbs_check_status'] === 'completed') ? 'active' : 'inactive'; ?>">
                        <div class="capability-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <span class="capability-text">DBS Check: <?php echo ucfirst($profile_data['dbs_check_status']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Home & Environment -->
            <div class="modern-card">
                <div class="card-header-modern">
                    <h2 class="card-title">
                        <i class="fa-solid fa-home"></i>
                        Home & Environment
                    </h2>
                </div>
                
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Home Type</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile_data['home_type']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Outdoor Space</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile_data['outdoor_space']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Has Children</span>
                        <span class="info-value"><?php echo $profile_data['has_children'] ? 'Yes' : 'No'; ?></span>
                    </div>
                    <?php if ($profile_data['has_children'] && !empty($profile_data['children_ages'])): ?>
                    <div class="info-item">
                        <span class="info-label">Children's Ages</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile_data['children_ages']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">Owns Pets</span>
                        <span class="info-value"><?php echo $profile_data['owns_pets'] ? 'Yes' : 'No'; ?></span>
                    </div>
                    <?php if ($profile_data['owns_pets'] && !empty($profile_data['owned_pet_details'])): ?>
                    <div class="info-item">
                        <span class="info-label">Sitter's Pet Details</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile_data['owned_pet_details']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Home Photos -->
                <?php if (!empty($profile_data['profile_photo_living_room']) || !empty($profile_data['profile_photo_sleeping_area']) || !empty($profile_data['profile_photo_outdoor'])): ?>
                <h3 class="section-subtitle">Home Photos</h3>
                <div class="home-photos-grid">
                    <?php if (!empty($profile_data['profile_photo_living_room'])): ?>
                    <div class="home-photo-card">
                        <img src="<?php echo htmlspecialchars($profile_data['profile_photo_living_room']); ?>" alt="Living Room">
                        <div class="photo-label-modern">Living Room</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile_data['profile_photo_sleeping_area'])): ?>
                    <div class="home-photo-card">
                        <img src="<?php echo htmlspecialchars($profile_data['profile_photo_sleeping_area']); ?>" alt="Sleeping Area">
                        <div class="photo-label-modern">Sleeping Area</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile_data['profile_photo_outdoor'])): ?>
                    <div class="home-photo-card">
                        <img src="<?php echo htmlspecialchars($profile_data['profile_photo_outdoor']); ?>" alt="Outdoor Space">
                        <div class="photo-label-modern">Outdoor Space</div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

           

        </div>

        <!-- Sidebar Column -->
        <div class="sidebar-content">
            
            <!-- Booking Card -->
            <div class="booking-card">
                <div class="booking-header">
                    <div class="booking-service"><?php echo ucfirst(htmlspecialchars($first_service['service_type'])); ?></div>
                    <div class="booking-price">
                        £<?php echo number_format($first_service['price_per_night'], 2); ?>
                        <small>/ night</small>
                    </div>
                </div>
                
                <form action="process_booking_request.php" method="POST">
                    <input type="hidden" name="sitter_id" value="<?php echo $sitter_id; ?>">
                    <input type="hidden" name="service_type" value="<?php echo htmlspecialchars($first_service['service_type']); ?>">
                    <input type="hidden" name="price_per_night" value="<?php echo htmlspecialchars($first_service['price_per_night']); ?>">
                    
                    <div class="date-range-group">
                        <div class="form-group-modern">
                            <label for="start_date">Drop off</label>
                            <input type="date" id="start_date" name="start_date" class="form-control-modern" required>
                        </div>
                        <div class="date-arrow">→</div>
                        <div class="form-group-modern">
                            <label for="end_date">Pick up</label>
                            <input type="date" id="end_date" name="end_date" class="form-control-modern" required>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label for="num_pets">Number of Pets</label>
                        <select id="num_pets" name="num_pets" class="form-control-modern">
                            <option value="1">1 Pet</option>
                            <option value="2">2 Pets</option>
                            <option value="3">3 Pets</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-book">Request Booking</button>
                </form>
            </div>

        </div>
    </div>
</main>

<!-- Lightbox -->
<div id="lightbox-modern" class="lightbox-modern" onclick="closeLightboxModern()">
    <span class="lightbox-close-modern" onclick="closeLightboxModern()">&times;</span>
    <img id="lightbox-img-modern" src="" alt="Album photo">
</div>

<script>
// Lightbox functions
function openLightboxModern(imageSrc) {
    const lightbox = document.getElementById('lightbox-modern');
    const lightboxImg = document.getElementById('lightbox-img-modern');
    if (lightbox && lightboxImg) {
        lightboxImg.src = imageSrc;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeLightboxModern() {
    const lightbox = document.getElementById('lightbox-modern');
    if (lightbox) {
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close lightbox with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightboxModern();
    }
});

// Prevent lightbox image click from closing
document.getElementById('lightbox-img-modern')?.addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>

<?php include 'footer.php'; ?>