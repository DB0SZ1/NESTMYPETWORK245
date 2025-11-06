<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = [];
$pets = [];
$profile_data = [];
$services_offered = [];
$bookings = [];
$album_photos = [];
$sitter_service = [];

// Cache busting timestamp
$cache_bust = time();

try {
    // Fetch complete user data
    $stmt_user = $pdo->prepare("
        SELECT id, fullname, email, phone_number, city, country, street, postcode,
               profile_photo_path, cover_photo_path, created_at, role, is_sitter, 
               profile_verified, sitter_status, about_me
        FROM users WHERE id = ?
    ");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header('Location: index.php');
        exit();
    }
    
    // Clean paths
    $user['profile_photo_path'] = trim($user['profile_photo_path'] ?? '');
    $user['cover_photo_path'] = trim($user['cover_photo_path'] ?? '');
    
    // Fetch photo album (max 15 photos)
    $stmt_album = $pdo->prepare("
        SELECT id, photo_path, caption, uploaded_at 
        FROM photo_albums 
        WHERE user_id = ? 
        ORDER BY uploaded_at DESC 
        LIMIT 15
    ");
    $stmt_album->execute([$user_id]);
    $album_photos = $stmt_album->fetchAll(PDO::FETCH_ASSOC);

    // Determine user type
    $is_owner = ($user['role'] === 'owner' || !$user['is_sitter']);
    $is_sitter = ($user['is_sitter'] == 1 || $user['role'] === 'host');

    // Fetch owner-specific data
    if ($is_owner) {
        $stmt_owner = $pdo->prepare("
            SELECT emergency_contact_name, emergency_contact_phone,
                   vet_name, vet_address, vet_phone,
                   auth_emergency_treatment, has_pet_insurance, insurance_details,
                   preferred_communication, daily_updates_wanted, update_frequency,
                   meet_sitter_beforehand, additional_notes, profile_completed,
                   comfortable_with_other_pets
            FROM owner_profiles WHERE user_id = ?
        ");
        $stmt_owner->execute([$user_id]);
        $profile_data = $stmt_owner->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt_pets = $pdo->prepare("
            SELECT id, name, pet_type, breed, size, age, 
                   temperament_notes, medical_conditions, medications,
                   is_neutered, comfortable_with_other_pets, pet_photo_path,
                   is_microchipped
            FROM pets WHERE user_id = ? ORDER BY name ASC
        ");
        $stmt_pets->execute([$user_id]);
        $pets = $stmt_pets->fetchAll(PDO::FETCH_ASSOC);

        $stmt_bookings = $pdo->prepare("
            SELECT b.*, u.fullname as sitter_name, u.city as sitter_city,
                   u.profile_photo_path as sitter_photo
            FROM bookings b
            JOIN users u ON b.sitter_id = u.id
            WHERE b.user_id = ?
            ORDER BY b.start_date DESC
            LIMIT 5
        ");
        $stmt_bookings->execute([$user_id]);
        $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch sitter-specific data
    if ($is_sitter) {
        $stmt_host = $pdo->prepare("
            SELECT sitter_type, sitter_role, date_of_birth, home_type, outdoor_space,
                   smokes_indoors, has_children, children_ages, lives_alone, other_adults,
                   owns_pets, owned_pet_details, years_experience, animal_background,
                   qualifications, availability_notes, max_pets_at_once, breed_size_restrictions,
                   can_administer_medication, emergency_transport_available,
                   training_video_watched, offers_home_sitting, dbs_check_status,
                   id_verification_path, dbs_certificate_path,
                   payment_method, bank_details, paypal_email, profile_completed,
                   profile_photo_living_room, profile_photo_sleeping_area, profile_photo_outdoor
            FROM host_profiles WHERE user_id = ?
        ");
        $stmt_host->execute([$user_id]);
        $profile_data = $stmt_host->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt_services = $pdo->prepare("
            SELECT service_name, max_pets, breed_size_restrictions,
                   can_administer_meds, has_emergency_transport
            FROM host_services WHERE host_user_id = ?
        ");
        $stmt_services->execute([$user_id]);
        $services_offered = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

        $stmt_sitter_service = $pdo->prepare("
            SELECT service_type, price_per_night, headline, sitter_about_me,
                   total_earnings, withdrawn_amount, pending_amount
            FROM sitter_services WHERE user_id = ? LIMIT 1
        ");
        $stmt_sitter_service->execute([$user_id]);
        $sitter_service = $stmt_sitter_service->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt_bookings = $pdo->prepare("
            SELECT b.*, u.fullname as owner_name, u.city as owner_city,
                   u.profile_photo_path as owner_photo
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            WHERE b.sitter_id = ?
            ORDER BY b.start_date DESC
            LIMIT 5
        ");
        $stmt_bookings->execute([$user_id]);
        $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log("Profile Error: " . $e->getMessage());
    $_SESSION['error_message'] = "A database error occurred.";
    header('Location: dashboard.php');
    exit();
}

// Format user display name
$name_parts = explode(' ', trim($user['fullname']));
$firstname = $name_parts[0] ?? '';
$lastname = implode(' ', array_slice($name_parts, 1));

$display_name = htmlspecialchars($firstname);
if (!empty($lastname)) {
    $display_name .= ' ' . htmlspecialchars(strtoupper(substr($lastname, 0, 1))) . '.';
}

$initials = strtoupper(substr($firstname, 0, 1));
if (!empty($lastname)) {
    $initials .= strtoupper(substr($lastname, 0, 1));
}

// Format location
$location = "Location not provided";
if (!empty($user['city']) && !empty($user['country'])) {
    $location = htmlspecialchars($user['city']) . ', ' . htmlspecialchars($user['country']);
} elseif (!empty($user['city'])) {
    $location = htmlspecialchars($user['city']);
} elseif (!empty($user['country'])) {
    $location = htmlspecialchars($user['country']);
}

// Format joined date
$joined_date = "Date not available";
if (!empty($user['created_at'])) {
    $joined_date = date('F Y', strtotime($user['created_at']));
}

$pageTitle = "My Profile";
include 'header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="profile.css">
<script src="new-profile.js"></script>
<main class="profile-page">
    <div class="container">
        <!-- Cover Photo Section -->
        <div class="profile-cover-section">
            <?php if (!empty($user['cover_photo_path']) && file_exists($user['cover_photo_path'])): ?>
                <img src="<?php echo htmlspecialchars($user['cover_photo_path']); ?>?v=<?php echo $cache_bust; ?>" alt="Cover Photo" class="cover-photo">
            <?php else: ?>
                <div class="cover-photo-placeholder">
                    <i class="fa-solid fa-image"></i>
                </div>
            <?php endif; ?>
            <button class="btn-upload-cover" onclick="document.getElementById('cover-photo-input').click()">
                <i class="fa-solid fa-camera"></i> Change Cover
            </button>
            <form id="cover-photo-form" action="process_upload_photos.php" method="POST" enctype="multipart/form-data" style="display: none;">
                <input type="file" id="cover-photo-input" name="cover_photo" accept="image/jpeg,image/png,image/jpg" onchange="document.getElementById('cover-photo-form').submit()">
            </form>
        </div>

        <div class="profile-page-layout">
            <!-- Sidebar: Profile Photo and User Info -->
            <aside class="profile-page-sidebar">
                <div class="profile-photo-container">
                    <div class="profile-photo-large">
                        <?php if (!empty($user['profile_photo_path']) && file_exists($user['profile_photo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_photo_path']); ?>?v=<?php echo $cache_bust; ?>" alt="<?php echo $display_name; ?>">
                        <?php else: ?>
                            <div class="profile-avatar-placeholder-large">
                                <div class="avatar-initials"><?php echo $initials; ?></div>
                            </div>
                        <?php endif; ?>
                        <button class="btn-change-profile-photo" onclick="document.getElementById('profile-photo-input').click()" title="Change profile photo">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                    </div>
                    <form id="profile-photo-form" action="process_upload_photos.php" method="POST" enctype="multipart/form-data" style="display: none;">
                        <input type="file" id="profile-photo-input" name="profile_photo" accept="image/jpeg,image/png,image/jpg" onchange="document.getElementById('profile-photo-form').submit()">
                    </form>
                </div>
                
                <h1 class="profile-page-name"><?php echo $display_name; ?></h1>
                
                <div class="user-type-badge <?php echo $is_sitter ? 'sitter' : 'owner'; ?>">
                    <i class="fa-solid <?php echo $is_sitter ? 'fa-paw' : 'fa-heart'; ?>"></i>
                    <span><?php echo $is_sitter ? 'Pet Sitter' : 'Pet Owner'; ?></span>
                </div>
                
                <p class="profile-page-location">
                    <i class="fa-solid fa-location-dot"></i> <?php echo $location; ?>
                </p>
                
                <div class="verification-badge-compact <?php echo $user['profile_verified'] ? 'verified' : 'unverified'; ?>">
                    <i class="fa-solid <?php echo $user['profile_verified'] ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <span><?php echo $user['profile_verified'] ? 'Verified' : 'Unverified'; ?></span>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php" class="profile-link">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                    </a>
                    <?php if ($is_sitter): ?>
                    <button class="profile-link" onclick="openRoleSwitchModal()">
                        <i class="fa-solid fa-arrows-rotate"></i> Switch Role
                    </button>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Main Content -->
            <section class="profile-page-main">
                <!-- Photo Album Section -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-images"></i> Photo Album (<?php echo count($album_photos); ?>/15)</h2>
                        <?php if (count($album_photos) < 15): ?>
                        <button class="btn-add-small" onclick="document.getElementById('album-photo-input').click()">
                            <i class="fa-solid fa-plus"></i> Add Photo
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <form id="album-photo-form" action="process_upload_photos.php" method="POST" enctype="multipart/form-data" style="display: none;">
                        <input type="file" id="album-photo-input" name="album_photo" accept="image/jpeg,image/png,image/jpg" onchange="document.getElementById('album-photo-form').submit()">
                    </form>
                    
                    <?php if (empty($album_photos)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-images"></i>
                        <p>No photos in your album yet</p>
                        <small>Share your favorite moments (max 15 photos)</small>
                    </div>
                    <?php else: ?>
                    <div class="photo-album-grid">
                        <?php foreach ($album_photos as $photo): ?>
                            <div class="album-photo-item">
                                <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>?v=<?php echo $cache_bust; ?>" 
                                     alt="Album photo" 
                                     onclick="openLightbox('<?php echo htmlspecialchars($photo['photo_path']); ?>')">
                                <button class="btn-delete-album-photo" onclick="event.stopPropagation(); deleteAlbumPhoto(<?php echo $photo['id']; ?>)" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- About Section -->
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-user"></i> About</h2>
                    <ul class="profile-about-list">
                        <li>
                            <i class="fa-regular fa-calendar"></i> 
                            <span>Joined NestMyPet <?php echo $joined_date; ?></span>
                        </li>
                        <?php if (!empty($user['email'])): ?>
                        <li>
                            <i class="fa-regular fa-envelope"></i>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($user['phone_number'])): ?>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <span><?php echo htmlspecialchars($user['phone_number']); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Availability Calendar Section -->
                <?php if ($is_sitter): ?>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-calendar-days"></i> My Availability</h2>
                        <button class="btn-add-small" onclick="toggleAvailabilityMode()">
                            <i class="fa-solid fa-plus"></i> <span id="availability-btn-text">Add Dates</span>
                        </button>
                    </div>
                    <p class="section-subtitle">Manage your available dates for pet sitting services</p>
                    
                    <!-- Availability Controls -->
                    <div class="availability-controls" style="display: none;">
                        <div class="date-range-inputs">
                            <div class="date-input-group">
                                <label><i class="fa-solid fa-calendar-plus"></i> Start Date</label>
                                <input type="date" id="availability-start" class="date-picker" data-mode="single" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="date-input-group">
                                <label><i class="fa-solid fa-calendar-check"></i> End Date</label>
                                <input type="date" id="availability-end" class="date-picker" data-mode="single" min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="availability-actions">
                            <button class="btn-save-availability" onclick="saveAvailability()">
                                <i class="fa-solid fa-check"></i> Save Dates
                            </button>
                            <button class="btn-cancel-availability" onclick="cancelAvailability()">
                                <i class="fa-solid fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                    
                    <!-- Availability Calendar Display -->
                    <div class="availability-calendar-container">
                        <div class="calendar-view-controls">
                            <button class="btn-calendar-nav" onclick="changeAvailabilityMonth(-1)">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <h3 id="current-month-year"></h3>
                            <button class="btn-calendar-nav" onclick="changeAvailabilityMonth(1)">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                        <div id="availability-calendar-grid"></div>
                    </div>
                    
                    <!-- Saved Availability List -->
                    <div class="saved-availability-list">
                        <h4><i class="fa-solid fa-list"></i> Your Available Dates</h4>
                        <div id="availability-list-container">
                            <div class="empty-state" style="padding: 40px 20px;">
                                <i class="fa-solid fa-calendar-xmark"></i>
                                <p>No availability dates set</p>
                                <small>Add your available dates to start receiving bookings</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- OWNER-SPECIFIC SECTIONS -->
                <?php if ($is_owner): ?>
                
                <?php if (!empty($profile_data['emergency_contact_name'])): ?>
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-phone-volume"></i> Emergency Contact</h2>
                    <ul class="profile-detail-list">
                        <li>
                            <strong>Name:</strong>
                            <span><?php echo htmlspecialchars($profile_data['emergency_contact_name']); ?></span>
                        </li>
                        <li>
                            <strong>Phone:</strong>
                            <span><?php echo htmlspecialchars($profile_data['emergency_contact_phone']); ?></span>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-comments"></i> Communication Preferences</h2>
                    <ul class="profile-detail-list">
                        <li>
                            <strong>Preferred Method:</strong>
                            <span><?php echo htmlspecialchars($profile_data['preferred_communication'] ?? 'Email'); ?></span>
                        </li>
                        <li>
                            <strong>Daily Updates:</strong>
                            <span><?php echo !empty($profile_data['daily_updates_wanted']) ? 'Yes' : 'No'; ?></span>
                        </li>
                        <li>
                            <strong>Meet Sitter Beforehand:</strong>
                            <span><?php echo !empty($profile_data['meet_sitter_beforehand']) ? 'Yes' : 'No'; ?></span>
                        </li>
                    </ul>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fa-solid fa-paw"></i> Your Pets (<?php echo count($pets); ?>)</h2>
                        <button class="btn-add-small" onclick="window.location.href='add_pet.php'">
                            <i class="fa-solid fa-plus"></i> Add Pet
                        </button>
                    </div>
                    
                    <?php if (empty($pets)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-paw"></i>
                            <p>No pets added yet</p>
                            <small>Add your furry friends to get started</small>
                        </div>
                    <?php else: ?>
                        <div class="pet-grid">
                            <?php foreach ($pets as $pet): ?>
                                <div class="pet-card-detailed">
                                    <?php if (!empty($pet['pet_photo_path']) && file_exists($pet['pet_photo_path'])): ?>
                                    <div class="pet-photo-thumb">
                                        <img src="<?php echo htmlspecialchars($pet['pet_photo_path']); ?>?v=<?php echo $cache_bust; ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
                                    </div>
                                    <?php else: ?>
                                    <div class="pet-photo-placeholder">
                                        <i class="fa-solid fa-paw"></i>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="pet-details">
                                        <h4><?php echo htmlspecialchars($pet['name']); ?></h4>
                                        <p class="pet-meta">
                                            <?php echo htmlspecialchars($pet['pet_type'] ?? 'Pet'); ?> 
                                            <?php if (!empty($pet['breed'])): ?>• <?php echo htmlspecialchars($pet['breed']); ?><?php endif; ?>
                                            <?php if (!empty($pet['age'])): ?>• <?php echo htmlspecialchars($pet['age']); ?> years<?php endif; ?>
                                        </p>
                                        <?php if (!empty($pet['size'])): ?>
                                        <span class="pet-badge"><?php echo htmlspecialchars($pet['size']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($pet['is_neutered'])): ?>
                                        <span class="pet-badge">Neutered/Spayed</span>
                                        <?php endif; ?>
                                        <?php if (!empty($pet['is_microchipped'])): ?>
                                        <span class="pet-badge">Microchipped</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="pet-actions">
                                        <button class="btn-icon" onclick="location.href='edit_pet.php?id=<?php echo $pet['id']; ?>'" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn-icon delete" onclick="if(confirm('Delete <?php echo htmlspecialchars($pet['name']); ?>?')) location.href='process_delete_pet.php?id=<?php echo $pet['id']; ?>'" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($bookings)): ?>
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-calendar-check"></i> Recent Bookings</h2>
                    <div class="bookings-list">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-item">
                                <div class="booking-info">
                                    <h4><?php echo htmlspecialchars($booking['sitter_name']); ?></h4>
                                    <p><?php echo ucfirst(htmlspecialchars($booking['service_type'])); ?> • <?php echo date('M j, Y', strtotime($booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?></p>
                                    <p class="booking-price">£<?php echo number_format($booking['total_price'], 2); ?></p>
                                </div>
                                <div class="booking-status <?php echo $booking['booking_status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="my_bookings.php" class="btn-view-all">View All Bookings</a>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>

                <!-- SITTER-SPECIFIC SECTIONS -->
                <?php if ($is_sitter): ?>
                
                <?php if (!empty($sitter_service)): ?>
                <div class="dashboard-card highlight">
                    <div class="sitter-profile-header">
                        <div>
                            <h2><i class="fa-solid fa-star"></i> <?php echo htmlspecialchars($sitter_service['headline'] ?? 'Pet Sitter'); ?></h2>
                            <p class="price-display">£<?php echo number_format($sitter_service['price_per_night'] ?? 0, 2); ?> per night</p>
                        </div>
                    </div>
                    <?php if (!empty($sitter_service['sitter_about_me'])): ?>
                    <div class="about-section">
                        <h4>About Me</h4>
                        <p><?php echo nl2br(htmlspecialchars($sitter_service['sitter_about_me'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                

                <?php if (!empty($profile_data)): ?>
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-graduation-cap"></i> Experience & Qualifications</h2>
                    <ul class="profile-detail-list">
                        <?php if (!empty($profile_data['sitter_type'])): ?>
                        <li>
                            <strong>Sitter Type:</strong>
                            <span><?php echo htmlspecialchars($profile_data['sitter_type']); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($profile_data['sitter_role'])): ?>
                        <li>
                            <strong>Sitter Role:</strong>
                            <span><?php echo ucfirst(str_replace('_', ' ', $profile_data['sitter_role'])); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($profile_data['years_experience'])): ?>
                        <li>
                            <strong>Years of Experience:</strong>
                            <span><?php echo $profile_data['years_experience']; ?> years</span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($profile_data['qualifications'])): ?>
                        <li>
                            <strong>Qualifications:</strong>
                            <span><?php echo nl2br(htmlspecialchars($profile_data['qualifications'])); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($profile_data['animal_background'])): ?>
                        <li>
                            <strong>Background:</strong>
                            <span><?php echo nl2br(htmlspecialchars($profile_data['animal_background'])); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile_data)): ?>
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-home"></i> Home & Environment</h2>
                    <ul class="profile-detail-list">
                        <?php if (!empty($profile_data['home_type'])): ?>
                        <li>
                            <strong>Home Type:</strong>
                            <span><?php echo htmlspecialchars($profile_data['home_type']); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($profile_data['outdoor_space'])): ?>
                        <li>
                            <strong>Outdoor Space:</strong>
                            <span><?php echo htmlspecialchars($profile_data['outdoor_space']); ?></span>
                        </li>
                        <?php endif; ?>
                        <li>
                            <strong>Has Children:</strong>
                            <span><?php echo !empty($profile_data['has_children']) ? 'Yes' : 'No'; ?></span>
                        </li>
                        <?php if (!empty($profile_data['has_children']) && !empty($profile_data['children_ages'])): ?>
                        <li>
                            <strong>Children's Ages:</strong>
                            <span><?php echo htmlspecialchars($profile_data['children_ages']); ?></span>
                        </li>
                        <?php endif; ?>
                        <li>
                            <strong>Lives Alone:</strong>
                            <span><?php echo !empty($profile_data['lives_alone']) ? 'Yes' : 'No'; ?></span>
                        </li>
                        <?php if (empty($profile_data['lives_alone']) && !empty($profile_data['other_adults'])): ?>
                        <li>
                            <strong>Other Adults:</strong>
                            <span><?php echo htmlspecialchars($profile_data['other_adults']); ?></span>
                        </li>
                        <?php endif; ?>
                        <li>
                            <strong>Owns Pets:</strong>
                            <span><?php echo !empty($profile_data['owns_pets']) ? 'Yes' : 'No'; ?></span>
                        </li>
                        <?php if (!empty($profile_data['owns_pets']) && !empty($profile_data['owned_pet_details'])): ?>
                        <li>
                            <strong>Pet Details:</strong>
                            <span><?php echo htmlspecialchars($profile_data['owned_pet_details']); ?></span>
                        </li>
                        <?php endif; ?>
                        <li>
                            <strong>Smokes Indoors:</strong>
                            <span><?php echo !empty($profile_data['smokes_indoors']) ? 'Yes' : 'No'; ?></span>
                        </li>
                    </ul>

                    <?php 
                    $has_home_photos = (!empty($profile_data['profile_photo_living_room']) && file_exists($profile_data['profile_photo_living_room'])) ||
                                      (!empty($profile_data['profile_photo_sleeping_area']) && file_exists($profile_data['profile_photo_sleeping_area'])) ||
                                      (!empty($profile_data['profile_photo_outdoor']) && file_exists($profile_data['profile_photo_outdoor']));
                    ?>
                    <?php if ($has_home_photos): ?>
                    <div class="home-photos">
                        <h4>Home Photos</h4>
                        <div class="photo-grid">
                            <?php if (!empty($profile_data['profile_photo_living_room']) && file_exists($profile_data['profile_photo_living_room'])): ?>
                            <div class="home-photo" onclick="openLightbox('<?php echo htmlspecialchars($profile_data['profile_photo_living_room']); ?>')">
                                <img src="<?php echo htmlspecialchars($profile_data['profile_photo_living_room']); ?>?v=<?php echo $cache_bust; ?>" alt="Living Room">
                                <span class="photo-label">Living Room</span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($profile_data['profile_photo_sleeping_area']) && file_exists($profile_data['profile_photo_sleeping_area'])): ?>
                            <div class="home-photo" onclick="openLightbox('<?php echo htmlspecialchars($profile_data['profile_photo_sleeping_area']); ?>')">
                                <img src="<?php echo htmlspecialchars($profile_data['profile_photo_sleeping_area']); ?>?v=<?php echo $cache_bust; ?>" alt="Sleeping Area">
                                <span class="photo-label">Sleeping Area</span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($profile_data['profile_photo_outdoor']) && file_exists($profile_data['profile_photo_outdoor'])): ?>
                            <div class="home-photo" onclick="openLightbox('<?php echo htmlspecialchars($profile_data['profile_photo_outdoor']); ?>')">
                                <img src="<?php echo htmlspecialchars($profile_data['profile_photo_outdoor']); ?>?v=<?php echo $cache_bust; ?>" alt="Outdoor Space">
                                <span class="photo-label">Outdoor Space</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($services_offered)): ?>
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-briefcase"></i> Services Offered</h2>
                    <p class="section-subtitle">I provide the following pet care services</p>
                    <div class="services-grid">
                        <?php foreach ($services_offered as $service): ?>
                            <div class="service-card">
                                <div class="service-icon">
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
                                <h4><?php echo ucfirst($service['service_name']); ?></h4>
                                <p>Max <?php echo $service['max_pets']; ?> pet<?php echo $service['max_pets'] > 1 ? 's' : ''; ?></p>
                                <?php if (!empty($service['breed_size_restrictions'])): ?>
                                <p class="restriction"><?php echo htmlspecialchars($service['breed_size_restrictions']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile_data)): ?>
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-list-check"></i> Capabilities</h2>
                    <div class="capabilities-grid">
                        <div class="capability-item <?php echo !empty($profile_data['can_administer_medication']) ? 'active' : 'inactive'; ?>">
                            <i class="fa-solid fa-pills"></i>
                            <span>Can Administer Medication</span>
                        </div>
                        <div class="capability-item <?php echo !empty($profile_data['emergency_transport_available']) ? 'active' : 'inactive'; ?>">
                            <i class="fa-solid fa-car"></i>
                            <span>Emergency Transport Available</span>
                        </div>
                        <div class="capability-item <?php echo !empty($profile_data['training_video_watched']) ? 'active' : 'inactive'; ?>">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>Training Completed</span>
                        </div>
                        <?php if (!empty($profile_data['offers_home_sitting'])): ?>
                        <div class="capability-item <?php echo ($profile_data['dbs_check_status'] === 'completed') ? 'active' : 'pending'; ?>">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>DBS Check: <?php echo ucfirst($profile_data['dbs_check_status'] ?? 'Not Started'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($bookings)): ?>
                <div class="dashboard-card">
                    <h2><i class="fa-solid fa-calendar-check"></i> Recent Bookings</h2>
                    <div class="bookings-list">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-item">
                                <div class="booking-info">
                                    <h4><?php echo htmlspecialchars($booking['owner_name']); ?></h4>
                                    <p><?php echo ucfirst(htmlspecialchars($booking['service_type'])); ?> • <?php echo date('M j, Y', strtotime($booking['start_date'])); ?> - <?php echo date('M j, Y', strtotime($booking['end_date'])); ?></p>
                                    <p class="booking-price">£<?php echo number_format($booking['total_price'], 2); ?></p>
                                </div>
                                <div class="booking-status <?php echo $booking['booking_status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="my_bookings.php" class="btn-view-all">View All Bookings</a>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>

            </section>
        </div>
    </div>
</main>

<!-- Lightbox for album photos -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img id="lightbox-img" src="" alt="Photo">
</div>

<script>

</script>

<?php include 'footer.php'; ?>

