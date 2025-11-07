<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    error_log("Missing user_id in session, redirecting to index.php");
    $_SESSION['error_message'] = "Please log in to access the dashboard.";
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cache_bust = time();

// Initialize all variables
$user = null;
$pets = [];
$bookings = [];
$profile_completion = 0;
$is_owner = false;
$is_sitter = false;
$host_data = null;
$owner_data = null;
$sitter_stats = null;
$sitter_services = [];
$sitter_service_data = null;

try {
    // ============================================
    // FETCH USER DATA - ULTRA ROBUST VERSION
    // ============================================
    
    // First, let's do a simple test query to ensure connection works
    $test_query = "SELECT COUNT(*) FROM users WHERE id = ?";
    $test_stmt = $pdo->prepare($test_query);
    $test_stmt->execute([$user_id]);
    $user_exists = $test_stmt->fetchColumn();
    
    if ($user_exists == 0) {
        error_log("CRITICAL: User ID {$user_id} does not exist in database");
        $_SESSION['error_message'] = "User not found. Please log in again.";
        session_destroy();
        header('Location: index.php');
        exit();
    }
    
    // Now fetch ALL user data using positional parameter
    $sql_user = "SELECT * FROM users WHERE id = ?";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        error_log("CRITICAL: User fetch failed for ID: $user_id despite user existing");
        error_log("PDO Error Info: " . print_r($stmt_user->errorInfo(), true));
        $_SESSION['error_message'] = "Database error. Please try again.";
        header('Location: index.php');
        exit();
    }
    
    // Log the RAW data before any processing
    error_log("=== RAW USER DATA ===");
    error_log("User ID: " . ($user['id'] ?? 'MISSING'));
    error_log("Fullname: " . ($user['fullname'] ?? 'MISSING'));
    error_log("Profile Photo Path RAW: " . var_export($user['profile_photo_path'] ?? null, true));
    error_log("Profile Photo Path Type: " . gettype($user['profile_photo_path'] ?? null));
    error_log("Profile Photo Path Length: " . strlen($user['profile_photo_path'] ?? ''));
    error_log("====================");
    
    // Clean photo paths with extensive logging
    $original_photo_path = $user['profile_photo_path'] ?? '';
    $user['profile_photo_path'] = trim($original_photo_path);
    
    if ($original_photo_path !== $user['profile_photo_path']) {
        error_log("Photo path had whitespace! Original length: " . strlen($original_photo_path) . ", Trimmed: " . strlen($user['profile_photo_path']));
    }
    
    $user['cover_photo_path'] = !empty($user['cover_photo_path']) ? trim($user['cover_photo_path']) : '';
    
    // Extensive photo path validation
    $photo_exists = false;
    error_log("=== PHOTO VALIDATION ===");
    error_log("Photo path empty check: " . (empty($user['profile_photo_path']) ? 'EMPTY' : 'HAS VALUE'));
    error_log("Photo path value: '{$user['profile_photo_path']}'");
    
    if (!empty($user['profile_photo_path'])) {
        $photo_exists = file_exists($user['profile_photo_path']);
        $full_path = __DIR__ . '/' . $user['profile_photo_path'];
        $full_path_exists = file_exists($full_path);
        
        error_log("Relative path: {$user['profile_photo_path']}");
        error_log("Relative path exists: " . ($photo_exists ? 'YES' : 'NO'));
        error_log("Full path: {$full_path}");
        error_log("Full path exists: " . ($full_path_exists ? 'YES' : 'NO'));
        error_log("Current directory: " . __DIR__);
        
        if (!$photo_exists && !$full_path_exists) {
            error_log("ERROR: Photo file not found at either path!");
        }
    } else {
        error_log("Photo path is EMPTY for user {$user_id}");
    }
    error_log("========================");
    
    // Set verification defaults
    $user['verification_status'] = $user['verification_status'] ?? 'pending';
    $user['profile_verified'] = (int)($user['profile_verified'] ?? 0);

    // ============================================
    // FORMAT USER NAME
    // ============================================
    $fullname = trim($user['fullname'] ?? '');
    $name_parts = explode(' ', $fullname);
    $firstname = $name_parts[0] ?? 'User';
    $lastname = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 1)) : '';
    
    $display_name = htmlspecialchars($firstname);
    if (!empty($lastname)) {
        $display_name .= ' ' . htmlspecialchars(strtoupper(substr($lastname, 0, 1))) . '.';
    }
    $firstname_only = htmlspecialchars($firstname);
    
    // Generate initials
    $initials = strtoupper(substr($firstname, 0, 1));
    if (!empty($lastname)) {
        $initials .= strtoupper(substr($lastname, 0, 1));
    }

    // ============================================
    // DETERMINE USER TYPE
    // ============================================
    $is_sitter = ((int)$user['is_sitter'] === 1) || ($user['role'] === 'host');
    $is_owner = !$is_sitter || ($user['role'] === 'owner');

    // ============================================
    // FETCH OWNER DATA
    // ============================================
    if ($is_owner) {
        // Get owner profile
        $sql_owner = "SELECT 
            emergency_contact_name, emergency_contact_phone,
            vet_name, vet_phone, vet_address, 
            preferred_communication, has_pet_insurance, 
            insurance_details, daily_updates_wanted
        FROM owner_profiles 
        WHERE user_id = :user_id";
        
        $stmt_owner = $pdo->prepare($sql_owner);
        $stmt_owner->execute(['user_id' => $user_id]);
        $owner_data = $stmt_owner->fetch(PDO::FETCH_ASSOC);
        
        // Get pets
        $sql_pets = "SELECT 
            id, name, pet_type, breed, size, age, pet_photo_path,
            temperament_notes, medical_notes, is_neutered, 
            is_microchipped, comfortable_with_other_pets
        FROM pets 
        WHERE user_id = :user_id 
        ORDER BY name ASC";
        
        $stmt_pets = $pdo->prepare($sql_pets);
        $stmt_pets->execute(['user_id' => $user_id]);
        $pets = $stmt_pets->fetchAll(PDO::FETCH_ASSOC);
        
        // Get owner bookings
        $sql_bookings = "SELECT 
            b.*, 
            u.fullname as sitter_name, 
            u.city as sitter_city,
            u.profile_photo_path as sitter_photo,
            ss.service_type as service_name
        FROM bookings b
        JOIN users u ON b.sitter_id = u.id
        LEFT JOIN sitter_services ss ON b.sitter_id = ss.user_id 
            AND b.service_type = ss.service_type
        WHERE b.user_id = :user_id
        ORDER BY b.start_date DESC
        LIMIT 10";
        
        $stmt_bookings = $pdo->prepare($sql_bookings);
        $stmt_bookings->execute(['user_id' => $user_id]);
        $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // FETCH SITTER DATA
    // ============================================
    if ($is_sitter) {
        // Get host profile
        $sql_host = "SELECT 
            sitter_role, date_of_birth, home_address, 
            has_children, children_ages, lives_alone, 
            other_adults, home_type, outdoor_space, 
            smokes_indoors, owns_pets, owned_pet_details,
            years_experience, animal_background, qualifications,
            availability_notes, profile_photo_living_room, 
            profile_photo_sleeping_area, profile_photo_outdoor,
            max_pets_at_once, breed_size_restrictions,
            can_administer_medication, emergency_transport_available,
            training_video_watched, dbs_check_status, 
            id_verification_path, dbs_certificate_path,
            payment_method, bank_details, paypal_email,
            profile_completed, offers_home_sitting
        FROM host_profiles 
        WHERE user_id = :user_id";
        
        $stmt_host = $pdo->prepare($sql_host);
        $stmt_host->execute(['user_id' => $user_id]);
        $host_data = $stmt_host->fetch(PDO::FETCH_ASSOC);
        
        // Get sitter services
        $sql_services = "SELECT 
            service_name, max_pets, breed_size_restrictions,
            can_administer_meds, has_emergency_transport
        FROM host_services 
        WHERE host_user_id = :user_id";
        
        $stmt_services = $pdo->prepare($sql_services);
        $stmt_services->execute(['user_id' => $user_id]);
        $sitter_services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);
        
        // Get sitter service details
        $sql_sitter_service = "SELECT 
            service_type, price_per_night, headline, 
            sitter_about_me, total_earnings, 
            withdrawn_amount, pending_amount
        FROM sitter_services 
        WHERE user_id = :user_id 
        LIMIT 1";
        
        $stmt_sitter_service = $pdo->prepare($sql_sitter_service);
        $stmt_sitter_service->execute(['user_id' => $user_id]);
        $sitter_service_data = $stmt_sitter_service->fetch(PDO::FETCH_ASSOC);
        
        // Get sitter bookings
        $sql_bookings = "SELECT 
            b.*, 
            u.fullname as owner_name, 
            u.city as owner_city,
            u.profile_photo_path as owner_photo, 
            u.phone_number as owner_phone
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.sitter_id = :user_id
        ORDER BY b.start_date DESC
        LIMIT 10";
        
        $stmt_bookings = $pdo->prepare($sql_bookings);
        $stmt_bookings->execute(['user_id' => $user_id]);
        $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
        
        // Get sitter statistics
        $sql_stats = "SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN booking_status = 'completed' THEN total_price ELSE 0 END) as total_earned
        FROM bookings 
        WHERE sitter_id = :user_id";
        
        $stmt_stats = $pdo->prepare($sql_stats);
        $stmt_stats->execute(['user_id' => $user_id]);
        $sitter_stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    }

    // ============================================
    // CALCULATE PROFILE COMPLETION
    // ============================================
    $completion_items = 0;
    $completed_items = 0;

    if ($is_owner) {
        $completion_items = 8;
        
        if (!empty($user['profile_photo_path']) && file_exists($user['profile_photo_path'])) {
            $completed_items++;
        }
        if (!empty($user['email'])) $completed_items++;
        if (!empty($user['phone_number'])) $completed_items++;
        
        if ($owner_data) {
            if (!empty($owner_data['emergency_contact_name'])) $completed_items++;
            if (!empty($owner_data['emergency_contact_phone'])) $completed_items++;
            if (!empty($owner_data['vet_name'])) $completed_items++;
            if (!empty($owner_data['preferred_communication'])) $completed_items++;
        }
        
        if (count($pets) > 0) $completed_items++;
        
    } else {
        $completion_items = 10;
        
        if (!empty($user['profile_photo_path']) && file_exists($user['profile_photo_path'])) {
            $completed_items++;
        }
        if (!empty($user['email'])) $completed_items++;
        if (!empty($user['phone_number'])) $completed_items++;
        
        if ($host_data) {
            if (!empty($host_data['years_experience'])) $completed_items++;
            if (!empty($host_data['animal_background'])) $completed_items++;
            if (!empty($host_data['home_type'])) $completed_items++;
            if (!empty($host_data['outdoor_space'])) $completed_items++;
            if (!empty($host_data['id_verification_path'])) $completed_items++;
            
            if (count($sitter_services) > 0) $completed_items++;
            
            if ($host_data['offers_home_sitting'] ?? false) {
                if (($host_data['dbs_check_status'] ?? '') === 'completed') {
                    $completed_items++;
                }
            } else {
                $completed_items++;
            }
        }
    }
    
    $base_completion = ($completion_items > 0) ? round(($completed_items / $completion_items) * 100) : 0;
    
    if ($user['verification_status'] === 'verified' && $user['profile_verified'] == 1) {
        $profile_completion = 100;
    } elseif ($user['verification_status'] === 'pending' && $base_completion >= 80) {
        $profile_completion = 90;
    } else {
        $profile_completion = $base_completion;
    }
    
} catch (PDOException $e) {
    error_log("Dashboard Database Error: " . $e->getMessage());
    error_log("Dashboard Error Trace: " . $e->getTraceAsString());
    $_SESSION['error_message'] = "An error occurred while loading your dashboard. Please try again later.";
}

$pageTitle = "My Dashboard";
include 'header.php';
include 'profile_completion_modal.php';
?>

<link rel="stylesheet" href="dashboard_new.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="dashboard-nn.css">

<main class="dashboard-page">
    <div class="container">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h1>Welcome back, <?php echo $firstname_only; ?>! 
                    <span class="user-type-badge-inline <?php echo $is_sitter ? 'sitter' : 'owner'; ?>">
                        <i class="fa-solid <?php echo $is_sitter ? 'fa-paw' : 'fa-heart'; ?>"></i>
                        <?php echo $is_sitter ? 'Pet Sitter' : 'Pet Owner'; ?>
                    </span>
                </h1>
                <p><?php echo $is_sitter ? 'Manage your bookings and services' : 'Find the perfect care for your pets'; ?></p>
            </div>
            <?php if ($profile_completion < 100): ?>
            <div class="profile-completion-widget">
                <div class="completion-circle">
                    <svg width="80" height="80">
                        <circle cx="40" cy="40" r="35" fill="none" stroke="#e8e8e8" stroke-width="6"></circle>
                        <circle cx="40" cy="40" r="35" fill="none" stroke="#00a862" stroke-width="6" 
                                stroke-dasharray="<?php echo $profile_completion * 2.199; ?> 219.9" 
                                transform="rotate(-90 40 40)"></circle>
                    </svg>
                    <span class="completion-percent"><?php echo $profile_completion; ?>%</span>
                </div>
                <div class="completion-text">
                    <strong>Profile Completion</strong>
                    <a href="edit_profile.php">Complete Now</a>
                </div>
            </div>
            <?php else: ?>
            <div class="verified-badge-large">
                <i class="fa-solid fa-circle-check"></i>
                <span>Verified Profile</span>
            </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-layout">
            <!-- Left Sidebar -->
            <aside class="dashboard-sidebar">
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="profile-avatar">
                        <?php 
                        $has_photo = !empty($user['profile_photo_path']) && file_exists($user['profile_photo_path']);
                        
                        if ($has_photo): 
                        ?>
                            <img src="<?php echo htmlspecialchars($user['profile_photo_path']); ?>?v=<?php echo $cache_bust; ?>" 
                                 alt="<?php echo $display_name; ?>"
                                 onerror="console.error('Image failed to load'); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="profile-avatar-placeholder" style="display: none;">
                                <div class="avatar-initials"><?php echo $initials; ?></div>
                            </div>
                        <?php else: ?>
                            <div class="profile-avatar-placeholder">
                                <div class="avatar-initials"><?php echo $initials; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="profile-name"><?php echo $display_name; ?></h3>
                    
                    <?php if (!$user['profile_verified']): ?>
                    <div class="verification-badge-compact unverified">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Profile Incomplete</span>
                    </div>
                    <?php else: ?>
                    <div class="verification-badge-compact verified">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Verified</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="profile-actions">
                        <a href="edit_profile.php" class="profile-link">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                        </a>
                        <a href="profile.php" class="profile-link">
                            <i class="fa-solid fa-eye"></i> View Public Profile
                        </a>
                    </div>
                </div>

                <!-- Wallet Section -->
                <div class="wallet-section">
                    <div class="wallet-header">
                        <span class="wallet-label">WALLET</span>
                        <span class="wallet-balance">£0.00</span>
                    </div>
                    <a href="#" class="wallet-promo">Apply Promo Code</a>
                    <div class="wallet-actions">
                        <a href="manage_payments.php" class="btn-outline">Add or Modify Payment Method</a>
                        <a href="#" class="btn-outline">View Payments & Promo Codes</a>
                    </div>
                </div>
                
                <?php if ($is_sitter && isset($sitter_stats)): ?>
                <!-- Sitter Stats -->
                <div class="stats-card">
                    <h4>Your Stats</h4>
                    <div class="stat-item">
                        <span class="stat-label">Total Bookings</span>
                        <span class="stat-value"><?php echo $sitter_stats['total_bookings'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Confirmed</span>
                        <span class="stat-value"><?php echo $sitter_stats['confirmed'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completed</span>
                        <span class="stat-value"><?php echo $sitter_stats['completed'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Earned</span>
                        <span class="stat-value highlight">£<?php echo number_format($sitter_stats['total_earned'] ?? 0, 2); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </aside>

            <!-- Main Content -->
            <section class="dashboard-main">
                <!-- Invite Friend Section -->
                <div class="invite-friend">
                    <div class="invite-content">
                        <div class="invite-label">INVITE A FRIEND</div>
                        <p class="invite-text">Invite a friend to <?php echo $is_sitter ? 'become a sitter' : 'book a pet sitter'; ?> on NestMyPet and you'll both get £20 in NestMyPet credit.</p>
                        <a href="#" class="btn-invite">Share now</a>
                    </div>
                    <div class="invite-icon">
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                </div>

                <?php if ($is_owner): ?>
                <!-- OWNER DASHBOARD - Pets Section -->
                <div class="pets-section">
                    <div class="pets-header">
                        <h2 class="pets-title">Your Pets</h2>
                    </div>
                    <p class="pets-description">Add your pets or edit their info</p>
                    
                    <?php if (empty($pets)): ?>
                        <button class="add-pet-box" id="add-pet-btn">
                            <div class="add-pet-icon">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <span class="add-pet-text">Add pet</span>
                        </button>
                    <?php else: ?>
                        <div class="pet-list">
                            <?php foreach ($pets as $pet): ?>
                                <div class="pet-card">
                                    <?php if (!empty($pet['pet_photo_path']) && file_exists($pet['pet_photo_path'])): ?>
                                        <div class="pet-photo-thumb">
                                            <img src="<?php echo htmlspecialchars($pet['pet_photo_path']); ?>?v=<?php echo $cache_bust; ?>" 
                                                 alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                        </div>
                                    <?php else: ?>
                                        <div class="pet-photo-thumb placeholder">
                                            <i class="fa-solid fa-paw"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="pet-info">
                                        <h4><?php echo htmlspecialchars($pet['name'] ?? 'Unknown'); ?></h4>
                                        <p><?php echo htmlspecialchars($pet['breed'] ?? 'Mixed'); ?> • <?php echo htmlspecialchars($pet['age'] ?? '?'); ?> years old</p>
                                    </div>
                                    <div class="pet-actions">
                                        <a href="edit_pet.php?id=<?php echo $pet['id'] ?? ''; ?>" class="btn-icon" title="Edit Pet">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <a href="process_delete_pet.php?id=<?php echo $pet['id'] ?? ''; ?>" 
                                           class="btn-icon delete" 
                                           title="Delete Pet" 
                                           onclick="return confirm('Are you sure you want to delete this pet?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <button class="add-pet-box" id="add-pet-btn-list" style="margin-top: 16px;">
                                <div class="add-pet-icon">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                                <span class="add-pet-text">Add another pet</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Owner Bookings Section -->
                <div class="bookings-section">
                    <div class="section-header">
                        <h2>Your Bookings</h2>
                        <a href="my_bookings.php" class="btn-link">View All</a>
                    </div>
                    
                    <?php if (empty($bookings)): ?>
                        <div class="empty-bookings">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>No bookings yet</p>
                            <a href="search.php" class="btn-primary">Find a Sitter</a>
                        </div>
                    <?php else: ?>
                        <div class="bookings-list">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card">
                                    <div class="booking-sitter-info">
                                        <?php if (!empty($booking['sitter_photo']) && file_exists($booking['sitter_photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($booking['sitter_photo']); ?>?v=<?php echo $cache_bust; ?>" 
                                                 alt="Sitter" class="sitter-avatar">
                                        <?php else: ?>
                                            <div class="sitter-avatar-placeholder">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h4><?php echo htmlspecialchars($booking['sitter_name']); ?></h4>
                                            <p class="booking-location">
                                                <i class="fa-solid fa-location-dot"></i>
                                                <?php echo htmlspecialchars($booking['sitter_city'] ?? 'Location N/A'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="booking-details">
                                        <div class="booking-date">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span><?php echo date('M j', strtotime($booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?></span>
                                        </div>
                                        <div class="booking-service">
                                            <i class="fa-solid fa-paw"></i>
                                            <span><?php echo ucfirst($booking['service_type']); ?> • <?php echo $booking['total_nights']; ?> night<?php echo $booking['total_nights'] > 1 ? 's' : ''; ?></span>
                                        </div>
                                        <div class="booking-price">£<?php echo number_format($booking['total_price'], 2); ?></div>
                                    </div>
                                    <div class="booking-status-badge <?php echo $booking['booking_status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($is_sitter): ?>
                <!-- SITTER DASHBOARD - Bookings Received -->
                <div class="bookings-section">
                    <div class="section-header">
                        <h2>Bookings Received</h2>
                        <a href="my_bookings.php" class="btn-link">View All</a>
                    </div>
                    
                    <?php if (empty($bookings)): ?>
                        <div class="empty-bookings">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <p>No bookings yet</p>
                            <p class="helper-text">Complete your profile to start receiving bookings</p>
                        </div>
                    <?php else: ?>
                        <div class="bookings-list">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card">
                                    <div class="booking-sitter-info">
                                        <?php if (!empty($booking['owner_photo']) && file_exists($booking['owner_photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($booking['owner_photo']); ?>?v=<?php echo $cache_bust; ?>" 
                                                 alt="Owner" class="sitter-avatar">
                                        <?php else: ?>
                                            <div class="sitter-avatar-placeholder">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h4><?php echo htmlspecialchars($booking['owner_name']); ?></h4>
                                            <p class="booking-location">
                                                <i class="fa-solid fa-location-dot"></i>
                                                <?php echo htmlspecialchars($booking['owner_city'] ?? 'Location N/A'); ?>
                                            </p>
                                            <?php if (!empty($booking['owner_phone'])): ?>
                                            <p class="booking-phone">
                                                <i class="fa-solid fa-phone"></i> 
                                                <?php echo htmlspecialchars($booking['owner_phone']); ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="booking-details">
                                        <div class="booking-date">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span><?php echo date('M j', strtotime($booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?></span>
                                        </div>
                                        <div class="booking-service">
                                            <i class="fa-solid fa-paw"></i>
                                            <span><?php echo ucfirst($booking['service_type']); ?> • <?php echo $booking['total_nights']; ?> night<?php echo $booking['total_nights'] > 1 ? 's' : ''; ?></span>
                                        </div>
                                        <div class="booking-price">£<?php echo number_format($booking['total_price'], 2); ?></div>
                                    </div>
                                    <div class="booking-status-badge <?php echo $booking['booking_status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                                    </div>
                                    <?php if ($booking['booking_status'] === 'pending_payment'): ?>
                                    <div class="booking-actions">
                                        <button class="btn-action accept" onclick="handleBookingAction(<?php echo $booking['id']; ?>, 'accept')">Accept</button>
                                        <button class="btn-action decline" onclick="handleBookingAction(<?php echo $booking['id']; ?>, 'decline')">Decline</button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Account Management Card -->
                <div class="account-management-card">
                    <h4>Account Management</h4>
                    
                    <?php if ($is_sitter && $host_data): ?>
                    <!-- Current Role Display -->
                    <div class="current-role-display">
                        <label>Current Role:</label>
                        <div class="role-badge <?php echo ($host_data['sitter_role'] ?? 'boarder') === 'house_sitter' ? 'house-sitter' : 'boarder'; ?>">
                            <i class="fa-solid <?php echo ($host_data['sitter_role'] ?? 'boarder') === 'house_sitter' ? 'fa-house' : 'fa-bed'; ?>"></i>
                            <span><?php echo ($host_data['sitter_role'] ?? 'boarder') === 'house_sitter' ? 'House Sitter' : 'Boarder'; ?></span>
                        </div>
                    </div>
                    
                    <!-- Switch Role Button -->
                    <button class="btn-switch-role-compact" onclick="openRoleSwitchModal()">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Switch Role</span>
                    </button>
                    
                    <div class="account-divider"></div>
                    <?php endif; ?>
                    

            </section>
        </div>
    </div>
</main>

<!-- Add Pet Modal -->
<div class="modal-overlay" id="add-pet-modal-overlay" style="display: none;">
    <div class="modal-content">
        <button class="modal-close-btn" id="pet-modal-close-btn">&times;</button>
        <div class="form-header">
            <h2>Add a New Pet</h2>
            <p>Enter your pet's details below.</p>
        </div>
        <form action="process_add_pet.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pet_name">Pet's Name *</label>
                <input type="text" id="pet_name" name="pet_name" required>
            </div>
            <div class="form-group">
                <label for="pet_type">Pet Type *</label>
                <select id="pet_type" name="pet_type" required>
                    <option value="">Select type</option>
                    <option value="Dog">Dog</option>
                    <option value="Cat">Cat</option>
                    <option value="Bird">Bird</option>
                    <option value="Rabbit">Rabbit</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="pet_breed">Breed</label>
                <input type="text" id="pet_breed" name="pet_breed">
            </div>
            <div class="form-group">
                <label for="pet_age">Age (years)</label>
                <input type="number" id="pet_age" name="pet_age" min="0" max="30">
            </div>
            <div class="form-group">
                <label for="pet_size">Size</label>
                <select id="pet_size" name="pet_size">
                    <option value="">Select size</option>
                    <option value="Small">Small (0-10kg)</option>
                    <option value="Medium">Medium (10-25kg)</option>
                    <option value="Large">Large (25kg+)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-full-green">Save Pet</button>
        </form>
    </div>
</div>

<script src="dashboard-nn.js"></script>

<!-- Debug Console Output -->
<script>
console.log('=== DASHBOARD DEBUG INFO ===');
console.log('User ID: <?php echo $user_id; ?>');
console.log('Profile Photo Path: "<?php echo addslashes($user['profile_photo_path'] ?? ''); ?>"');
console.log('Photo Path Length: <?php echo strlen($user['profile_photo_path'] ?? ''); ?>');
console.log('Photo Path Empty: <?php echo empty($user['profile_photo_path']) ? 'true' : 'false'; ?>');
console.log('Photo Exists (PHP): <?php echo (!empty($user['profile_photo_path']) && file_exists($user['profile_photo_path'])) ? 'YES' : 'NO'; ?>');
console.log('Display Name: <?php echo addslashes($display_name); ?>');
console.log('Full Name: <?php echo addslashes($user['fullname'] ?? ''); ?>');
console.log('Is Owner: <?php echo $is_owner ? 'true' : 'false'; ?>');
console.log('Is Sitter: <?php echo $is_sitter ? 'true' : 'false'; ?>');
console.log('Profile Completion: <?php echo $profile_completion; ?>%');
console.log('User Role: <?php echo $user['role'] ?? 'not set'; ?>');
console.log('is_sitter flag: <?php echo $user['is_sitter'] ?? 'not set'; ?>');
console.log('============================');

// Try to load the image and report
<?php if (!empty($user['profile_photo_path'])): ?>
var testImg = new Image();
testImg.onload = function() {
    console.log('✓ Image loaded successfully from: <?php echo addslashes($user['profile_photo_path']); ?>');
};
testImg.onerror = function() {
    console.error('✗ Image failed to load from: <?php echo addslashes($user['profile_photo_path']); ?>');
    console.error('Check if file exists at this path');
};
testImg.src = '<?php echo addslashes($user['profile_photo_path']); ?>?v=<?php echo $cache_bust; ?>';
<?php else: ?>
console.warn('No photo path set for user');
<?php endif; ?>
</script>

<?php include 'footer.php'; ?>