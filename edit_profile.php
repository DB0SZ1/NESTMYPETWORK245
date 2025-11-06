<?php
// Start session at the very top to ensure stability
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session state
error_log("Session user_id in edit_profile.php: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));

require 'db.php';

// Validate PDO object
if (!isset($pdo) || !$pdo instanceof PDO) {
    error_log("PDO object is not valid in edit_profile.php");
    $_SESSION['error_message'] = "Database connection error. Please try again later.";
    header('Location: dashboard.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    error_log("Missing user_id in session, redirecting to index.php");
    $_SESSION['error_message'] = "Please log in to edit your profile.";
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = [];
$profile_data = [];
$is_owner = false;
$is_sitter = false;

try {
    // Fetch user data - EXPLICITLY fetch all fields
    $stmt = $pdo->prepare("SELECT id, fullname, email, phone_number, street, city, postcode, country, 
                           about_me, profile_photo_path, cover_photo_path, role, is_sitter, profile_verified 
                           FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!is_array($user) || empty($user)) {
        error_log("User not found for ID: {$user_id}");
        $_SESSION['error_message'] = "Could not find user data. Please log in again.";
        header('Location: dashboard.php');
        exit();
    }
    
    // Log fetched data for debugging
    error_log("Fetched user data for ID {$user_id}: " . json_encode($user));
    
    // Determine user type
    $is_owner = ($user['role'] === 'owner' || !$user['is_sitter']);
    $is_sitter = ($user['is_sitter'] == 1 || $user['role'] === 'host');
    
    // Fetch type-specific profile data
    if ($is_owner) {
        $stmt_owner = $pdo->prepare("
            SELECT emergency_contact_name, emergency_contact_phone,
                   vet_name, vet_address, vet_phone,
                   preferred_communication, daily_updates_wanted,
                   meet_sitter_beforehand, additional_notes
            FROM owner_profiles WHERE user_id = ?
        ");
        $stmt_owner->execute([$user_id]);
        $profile_data = $stmt_owner->fetch(PDO::FETCH_ASSOC) ?: [];
    } elseif ($is_sitter) {
        $stmt_host = $pdo->prepare("
            SELECT home_type, outdoor_space, has_children, children_ages,
                   owns_pets, owned_pet_details, years_experience,
                   animal_background, qualifications, max_pets_at_once,
                   breed_size_restrictions, can_administer_medication,
                   emergency_transport_available, availability_notes
            FROM host_profiles WHERE user_id = ?
        ");
        $stmt_host->execute([$user_id]);
        $profile_data = $stmt_host->fetch(PDO::FETCH_ASSOC) ?: [];
        
        // Fetch sitter service data
        $stmt_service = $pdo->prepare("
            SELECT headline, sitter_about_me, price_per_night
            FROM sitter_services WHERE user_id = ? LIMIT 1
        ");
        $stmt_service->execute([$user_id]);
        $service_data = $stmt_service->fetch(PDO::FETCH_ASSOC);
        if ($service_data) {
            $profile_data = array_merge($profile_data, $service_data);
        }
    }
    
} catch (PDOException $e) {
    error_log("Database Error in edit_profile.php: " . $e->getMessage());
    $_SESSION['error_message'] = "A database error occurred. Please try again later.";
    header('Location: dashboard.php');
    exit();
}

$pageTitle = "Edit Profile";
include 'header.php';
?>

<link rel="stylesheet" href="dashboard_new.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<main class="dashboard-page">
    <div class="container">
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-times-circle"></i>
                <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <nav class="settings-nav">
            <a href="#basic-info" class="active">Basic Info</a>
            <?php if ($is_owner): ?>
            <a href="#owner-details">Pet Owner Details</a>
            <?php endif; ?>
            <?php if ($is_sitter): ?>
            <a href="#sitter-details">Sitter Details</a>
            <a href="#sitter-services">Services & Pricing</a>
            <?php endif; ?>
            <a href="#security">Security</a>
        </nav>

        <div class="settings-content">
            <!-- Basic Info Tab -->
            <div id="basic-info" class="settings-tab active">
                <div class="dashboard-card">
                    <h2>Basic Information</h2>
                    <p class="info-note"><i class="fas fa-info-circle"></i> Fields marked with <strong>*</strong> cannot be changed after registration</p>
                    
                    <form action="process_edit_profile.php" method="POST" enctype="multipart/form-data" id="edit-profile-form">
                        
                        <!-- READ-ONLY: Full Name -->
                        <div class="form-group read-only-field">
                            <label for="fullname_display">Full Name *</label>
                            <input type="text" id="fullname_display" 
                                   value="<?php echo isset($user['fullname']) ? htmlspecialchars($user['fullname']) : ''; ?>" 
                                   readonly disabled>
                            <small class="field-note"><i class="fas fa-lock"></i> This field cannot be changed after registration</small>
                        </div>
                        
                        <!-- READ-ONLY: Email -->
                        <div class="form-group read-only-field">
                            <label for="email_display">Email Address *</label>
                            <input type="email" id="email_display" 
                                   value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" 
                                   readonly disabled>
                            <small class="field-note"><i class="fas fa-lock"></i> This field cannot be changed after registration</small>
                        </div>
                        
                        <!-- READ-ONLY: Phone Number -->
                        <div class="form-group">
                            <label for="phone_display">Phone Number</label>
                            <input type="text" id="phone_display" 
                                   value="<?php echo isset($user['phone_number']) ? htmlspecialchars($user['phone_number']) : ''; ?>" 
                            >
                           
                        </div>

                        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--border-color);">

                        <!-- EDITABLE: About Me -->
                        <div class="form-group">
                            <label for="about_me">About Me</label>
                            <textarea id="about_me" name="about_me" rows="5" placeholder="Tell others about yourself..."><?php echo isset($user['about_me']) ? htmlspecialchars($user['about_me']) : ''; ?></textarea>
                            <small><?php echo $is_sitter ? 'Tell pet owners why you would be a great sitter' : 'Tell sitters about yourself and your pets'; ?></small>
                        </div>

                        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--border-color);">

                        <h3 style="margin-bottom: 1rem;">Address (Editable)</h3>
                        
                        <div class="form-group">
                            <label for="street">Street Address</label>
                            <input type="text" id="street" name="street" placeholder="Street Name and Number" 
                                   value="<?php echo isset($user['street']) ? htmlspecialchars($user['street']) : ''; ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group form-group-half">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" placeholder="London" 
                                       value="<?php echo isset($user['city']) ? htmlspecialchars($user['city']) : ''; ?>">
                            </div>
                            <div class="form-group form-group-half">
                                <label for="postcode">Postcode</label>
                                <input type="text" id="postcode" name="postcode" placeholder="SW1A 1AA" 
                                       value="<?php echo isset($user['postcode']) ? htmlspecialchars($user['postcode']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" placeholder="United Kingdom" 
                                   value="<?php echo isset($user['country']) ? htmlspecialchars($user['country']) : 'United Kingdom'; ?>">
                        </div>

                        <button type="submit" class="btn btn-primary btn-full-green" style="display: block; margin-top: 1rem;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($is_owner): ?>
            <!-- Owner Details Tab -->
            <div id="owner-details" class="settings-tab">
                <div class="dashboard-card">
                    <h2>Pet Owner Details</h2>
                    <form action="process_edit_owner_profile.php" method="POST">
                        <h3 style="margin-top: 0;">Emergency Contact</h3>
                        <div class="form-group">
                            <label for="emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" 
                                   value="<?php echo isset($profile_data['emergency_contact_name']) ? htmlspecialchars($profile_data['emergency_contact_name']) : ''; ?>"
                                   placeholder="Full name of emergency contact">
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_phone">Emergency Contact Phone</label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" 
                                   value="<?php echo isset($profile_data['emergency_contact_phone']) ? htmlspecialchars($profile_data['emergency_contact_phone']) : ''; ?>"
                                   placeholder="+44 7700 900000">
                        </div>

                        <hr style="margin: 2rem 0;">

                        <h3>Veterinary Information</h3>
                        <div class="form-group">
                            <label for="vet_name">Vet Name / Clinic</label>
                            <input type="text" id="vet_name" name="vet_name" 
                                   value="<?php echo isset($profile_data['vet_name']) ? htmlspecialchars($profile_data['vet_name']) : ''; ?>"
                                   placeholder="Dr. Smith or ABC Veterinary Clinic">
                        </div>
                        <div class="form-group">
                            <label for="vet_address">Vet Address</label>
                            <textarea id="vet_address" name="vet_address" rows="2" placeholder="Full clinic address"><?php echo isset($profile_data['vet_address']) ? htmlspecialchars($profile_data['vet_address']) : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="vet_phone">Vet Phone Number</label>
                            <input type="tel" id="vet_phone" name="vet_phone" 
                                   value="<?php echo isset($profile_data['vet_phone']) ? htmlspecialchars($profile_data['vet_phone']) : ''; ?>"
                                   placeholder="+44 20 7946 0958">
                        </div>

                        <hr style="margin: 2rem 0;">

                        <h3>Communication Preferences</h3>
                        <div class="form-group">
                            <label for="preferred_communication">Preferred Method</label>
                            <select id="preferred_communication" name="preferred_communication" class="form-control">
                                <option value="Email" <?php echo (isset($profile_data['preferred_communication']) && $profile_data['preferred_communication'] === 'Email') ? 'selected' : ''; ?>>Email</option>
                                <option value="Text" <?php echo (isset($profile_data['preferred_communication']) && $profile_data['preferred_communication'] === 'Text') ? 'selected' : ''; ?>>Text Message</option>
                                <option value="Phone" <?php echo (isset($profile_data['preferred_communication']) && $profile_data['preferred_communication'] === 'Phone') ? 'selected' : ''; ?>>Phone Call</option>
                                <option value="App" <?php echo (isset($profile_data['preferred_communication']) && $profile_data['preferred_communication'] === 'App') ? 'selected' : ''; ?>>App Notifications</option>
                            </select>
                        </div>

                        <div class="form-group-checkbox">
                            <input type="checkbox" id="daily_updates_wanted" name="daily_updates_wanted" value="1" 
                                   <?php echo (isset($profile_data['daily_updates_wanted']) && !empty($profile_data['daily_updates_wanted'])) ? 'checked' : ''; ?>>
                            <label for="daily_updates_wanted">I want daily updates about my pet</label>
                        </div>

                        <div class="form-group-checkbox">
                            <input type="checkbox" id="meet_sitter_beforehand" name="meet_sitter_beforehand" value="1" 
                                   <?php echo (isset($profile_data['meet_sitter_beforehand']) && !empty($profile_data['meet_sitter_beforehand'])) ? 'checked' : ''; ?>>
                            <label for="meet_sitter_beforehand">I would like to meet the sitter beforehand</label>
                        </div>

                        <div class="form-group">
                            <label for="additional_notes">Additional Notes</label>
                            <textarea id="additional_notes" name="additional_notes" rows="4" placeholder="Any other information sitters should know..."><?php echo isset($profile_data['additional_notes']) ? htmlspecialchars($profile_data['additional_notes']) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full-green">
                            <i class="fas fa-save"></i> Save Owner Details
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($is_sitter): ?>
            <!-- Sitter Details Tab -->
            <div id="sitter-details" class="settings-tab">
                <div class="dashboard-card">
                    <h2>Sitter Profile Details</h2>
                    <form action="process_edit_sitter_profile.php" method="POST">
                        <h3 style="margin-top: 0;">Home Environment</h3>
                        <div class="form-group">
                            <label for="home_type">Type of Home</label>
                            <select id="home_type" name="home_type" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="House with Garden" <?php echo (isset($profile_data['home_type']) && $profile_data['home_type'] === 'House with Garden') ? 'selected' : ''; ?>>House with Garden</option>
                                <option value="House without Garden" <?php echo (isset($profile_data['home_type']) && $profile_data['home_type'] === 'House without Garden') ? 'selected' : ''; ?>>House without Garden</option>
                                <option value="Flat or Apartment" <?php echo (isset($profile_data['home_type']) && $profile_data['home_type'] === 'Flat or Apartment') ? 'selected' : ''; ?>>Flat or Apartment</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="outdoor_space">Outdoor Space</label>
                            <select id="outdoor_space" name="outdoor_space" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Fully Fenced Garden" <?php echo (isset($profile_data['outdoor_space']) && $profile_data['outdoor_space'] === 'Fully Fenced Garden') ? 'selected' : ''; ?>>Fully Fenced Garden</option>
                                <option value="Balcony" <?php echo (isset($profile_data['outdoor_space']) && $profile_data['outdoor_space'] === 'Balcony') ? 'selected' : ''; ?>>Balcony</option>
                                <option value="No Outdoor Space" <?php echo (isset($profile_data['outdoor_space']) && $profile_data['outdoor_space'] === 'No Outdoor Space') ? 'selected' : ''; ?>>No Outdoor Space</option>
                            </select>
                        </div>

                        <div class="form-group-checkbox">
                            <input type="checkbox" id="has_children" name="has_children" value="1" 
                                   <?php echo (isset($profile_data['has_children']) && !empty($profile_data['has_children'])) ? 'checked' : ''; ?>>
                            <label for="has_children">I have children living with me</label>
                        </div>

                        <div class="form-group" id="children_ages_group" style="display: <?php echo (isset($profile_data['has_children']) && !empty($profile_data['has_children'])) ? 'block' : 'none'; ?>;">
                            <label for="children_ages">Children's Ages</label>
                            <input type="text" id="children_ages" name="children_ages" 
                                   value="<?php echo isset($profile_data['children_ages']) ? htmlspecialchars($profile_data['children_ages']) : ''; ?>"
                                   placeholder="e.g., 5, 8, 12">
                        </div>

                        <div class="form-group-checkbox">
                            <input type="checkbox" id="owns_pets" name="owns_pets" value="1" 
                                   <?php echo (isset($profile_data['owns_pets']) && !empty($profile_data['owns_pets'])) ? 'checked' : ''; ?>>
                            <label for="owns_pets">I own pets</label>
                        </div>

                        <div class="form-group" id="owned_pet_details_group" style="display: <?php echo (isset($profile_data['owns_pets']) && !empty($profile_data['owns_pets'])) ? 'block' : 'none'; ?>;">
                            <label for="owned_pet_details">Pet Details</label>
                            <textarea id="owned_pet_details" name="owned_pet_details" rows="3" placeholder="Type, number, temperament (e.g., 2 friendly dogs, 1 indoor cat)"><?php echo isset($profile_data['owned_pet_details']) ? htmlspecialchars($profile_data['owned_pet_details']) : ''; ?></textarea>
                        </div>

                        <hr style="margin: 2rem 0;">

                        <h3>Experience & Qualifications</h3>
                        <div class="form-group">
                            <label for="years_experience">Years of Experience with Pets</label>
                            <input type="number" id="years_experience" name="years_experience" min="0" 
                                   value="<?php echo isset($profile_data['years_experience']) ? htmlspecialchars($profile_data['years_experience']) : '0'; ?>"
                                   placeholder="0">
                        </div>

                        <div class="form-group">
                            <label for="animal_background">Your Background with Animals</label>
                            <textarea id="animal_background" name="animal_background" rows="4" placeholder="Share your experience, passion, and relevant information..."><?php echo isset($profile_data['animal_background']) ? htmlspecialchars($profile_data['animal_background']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="qualifications">Relevant Training or Qualifications</label>
                            <textarea id="qualifications" name="qualifications" rows="2" placeholder="Certifications, courses, or professional training"><?php echo isset($profile_data['qualifications']) ? htmlspecialchars($profile_data['qualifications']) : ''; ?></textarea>
                        </div>

                        <hr style="margin: 2rem 0;">

                        <h3>Capabilities</h3>
                        <div class="form-group">
                            <label for="max_pets_at_once">Maximum Pets at Once</label>
                            <input type="number" id="max_pets_at_once" name="max_pets_at_once" min="1" max="10" 
                                   value="<?php echo isset($profile_data['max_pets_at_once']) ? htmlspecialchars($profile_data['max_pets_at_once']) : '2'; ?>">
                        </div>

                        <div class="form-group">
                            <label for="breed_size_restrictions">Breed or Size Restrictions (if any)</label>
                            <input type="text" id="breed_size_restrictions" name="breed_size_restrictions" 
                                   value="<?php echo isset($profile_data['breed_size_restrictions']) ? htmlspecialchars($profile_data['breed_size_restrictions']) : ''; ?>"
                                   placeholder="e.g., No large dogs, Only small breeds">
                        </div>

                        <div class="form-group-checkbox">
                            <input type="checkbox" id="can_administer_medication" name="can_administer_medication" value="1" 
                                   <?php echo (isset($profile_data['can_administer_medication']) && !empty($profile_data['can_administer_medication'])) ? 'checked' : ''; ?>>
                            <label for="can_administer_medication">I can administer medication</label>
                        </div>

                        <div class="form-group-checkbox">
                            <input type="checkbox" id="emergency_transport_available" name="emergency_transport_available" value="1" 
                                   <?php echo (isset($profile_data['emergency_transport_available']) && !empty($profile_data['emergency_transport_available'])) ? 'checked' : ''; ?>>
                            <label for="emergency_transport_available">Emergency transport to vet available</label>
                        </div>

                        <!-- Availability Notes removed - now managed via calendar system -->
<div class="info-box" style="margin: 1rem 0;">
    <p><i class="fas fa-calendar-alt"></i> <strong>Availability Management:</strong> Your availability is now managed through the calendar system in your dashboard.</p>
</div>

                        <button type="submit" class="btn btn-primary btn-full-green">
                            <i class="fas fa-save"></i> Save Sitter Details
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sitter Services Tab -->
            <div id="sitter-services" class="settings-tab">
                <div class="dashboard-card">
                    <h2>Services & Pricing</h2>
                    <form action="process_edit_sitter_service.php" method="POST">
                        <div class="form-group">
                            <label for="headline">Service Headline (max 100 characters)</label>
                            <input type="text" id="headline" name="headline" maxlength="100" 
                                   value="<?php echo isset($profile_data['headline']) ? htmlspecialchars($profile_data['headline']) : ''; ?>"
                                   placeholder="e.g., Experienced dog lover with fenced garden">
                            <small>This appears on your profile listing</small>
                        </div>

                        <div class="form-group">
                            <label for="sitter_about_me">About My Services</label>
                            <textarea id="sitter_about_me" name="sitter_about_me" rows="5" placeholder="Describe the care you provide, your approach, and what makes you special..."><?php echo isset($profile_data['sitter_about_me']) ? htmlspecialchars($profile_data['sitter_about_me']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="price_per_night">Base Price per Night (£)</label>
                            <input type="number" id="price_per_night" name="price_per_night" min="10" max="500" step="5" 
                                   value="<?php echo isset($profile_data['price_per_night']) ? htmlspecialchars($profile_data['price_per_night']) : '50'; ?>">
                            <small>This is your base rate. Adjust as needed for your services.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full-green">
                            <i class="fas fa-save"></i> Save Service Details
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Security Tab -->
            <div id="security" class="settings-tab">
                <div class="dashboard-card">
                    <h2>Change Password</h2>
                    <form action="process_change_password.php" method="POST" id="change-password-form">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required placeholder="Enter your current password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="At least 8 characters">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-full-green" style="display: block;">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert i {
    font-size: 20px;
}

.alert-error {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.info-note {
    background: #e7f3ff;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: #0066cc;
    font-size: 14px;
}

.read-only-field {
    background: #f9f9f9;
    padding: 16px;
    border-radius: 8px;
    border: 2px solid #e8e8e8;
}

.read-only-field input {
    background: #f5f5f5 !important;
    cursor: not-allowed;
    color: #666;
    border: 1px solid #ddd;
}

.field-note {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #999;
    font-size: 13px;
    margin-top: 6px;
}

.field-note i {
    color: #ccc;
}

.form-group-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 16px 0;
}

.form-group-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.form-group-checkbox label {
    margin: 0;
    cursor: pointer;
    font-weight: 400;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const tabs = document.querySelectorAll('.settings-nav a');
    const tabContents = document.querySelectorAll('.settings-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            const targetId = tab.getAttribute('href');
            document.querySelector(targetId).classList.add('active');
        });
    });

    // Password validation
    const passwordForm = document.getElementById('change-password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (newPassword !== confirmPassword) {
                event.preventDefault();
                alert('New password and confirm password do not match.');
            }
        });
    }

    // Children toggle for sitters
    const hasChildrenCheckbox = document.getElementById('has_children');
    const childrenAgesGroup = document.getElementById('children_ages_group');
    if (hasChildrenCheckbox && childrenAgesGroup) {
        hasChildrenCheckbox.addEventListener('change', function() {
            childrenAgesGroup.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Owned pets toggle for sitters
    const ownsPetsCheckbox = document.getElementById('owns_pets');
    const ownedPetDetailsGroup = document.getElementById('owned_pet_details_group');
    if (ownsPetsCheckbox && ownedPetDetailsGroup) {
        ownsPetsCheckbox.addEventListener('change', function() {
            ownedPetDetailsGroup.style.display = this.checked ? 'block' : 'none';
        });
    }           
});
</script>
<?php
include 'footer.php';
?>
