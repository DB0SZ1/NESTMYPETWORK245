<?php
// This file should be included in header.php or at the top of dashboard.php
// It checks if the user has an incomplete profile and shows the appropriate modal

if (!isset($_SESSION['user_id'])) {
    return; // Don't show modal if not logged in
}

$user_id = $_SESSION['user_id'];
$show_modal = false;
$modal_content = [];

try {
    // Get user type and verification status
    $stmt = $pdo->prepare("SELECT role, is_sitter, profile_verified FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return;
    }
    
    // Check if this is a new signup (session flag) or unverified profile
    if ((isset($_SESSION['profile_incomplete']) && $_SESSION['profile_incomplete']) || !$user['profile_verified']) {
        $show_modal = true;
        
        // Check what's missing based on user type
        if ($user['role'] === 'owner') {
            // Check owner profile completion
            $stmt_owner = $pdo->prepare("SELECT profile_completed FROM owner_profiles WHERE user_id = ?");
            $stmt_owner->execute([$user_id]);
            $owner_profile = $stmt_owner->fetch();
            
            $modal_content = [
                'type' => 'owner',
                'icon' => 'fa-heart',
                'title' => 'Complete Your Pet Owner Profile',
                'message' => 'Welcome to NestMyPet! To get the best experience and connect with trusted sitters, please complete your profile.',
                'missing_items' => [],
                'cta_text' => 'Complete Profile Now',
                'cta_link' => 'edit_profile.php'
            ];
            
            // Check what's missing
            $stmt_check = $pdo->prepare("
                SELECT 
                    emergency_contact_name,
                    emergency_contact_phone,
                    vet_name,
                    preferred_communication
                FROM owner_profiles 
                WHERE user_id = ?
            ");
            $stmt_check->execute([$user_id]);
            $profile_data = $stmt_check->fetch();
            
            if (empty($profile_data['emergency_contact_name']) || empty($profile_data['emergency_contact_phone'])) {
                $modal_content['missing_items'][] = 'Emergency contact information';
            }
            if (empty($profile_data['vet_name'])) {
                $modal_content['missing_items'][] = 'Veterinary information (recommended)';
            }
            
            // Check if user has added pet photos
            $stmt_pets = $pdo->prepare("SELECT COUNT(*) as count FROM pets WHERE user_id = ? AND pet_photo_path IS NOT NULL");
            $stmt_pets->execute([$user_id]);
            $pet_photos = $stmt_pets->fetch();
            if ($pet_photos['count'] == 0) {
                $modal_content['missing_items'][] = 'Pet photos (helps sitters know your pet)';
            }
            
        } elseif ($user['is_sitter'] == 1 || $user['role'] === 'host') {
            // Check sitter profile completion
            $stmt_host = $pdo->prepare("
                SELECT 
                    profile_completed,
                    training_video_watched,
                    id_verification_path,
                    offers_home_sitting,
                    dbs_check_status,
                    profile_photo_living_room,
                    profile_photo_sleeping_area
                FROM host_profiles 
                WHERE user_id = ?
            ");
            $stmt_host->execute([$user_id]);
            $host_profile = $stmt_host->fetch();
            
            $modal_content = [
                'type' => 'sitter',
                'icon' => 'fa-paw',
                'title' => 'Complete Your Sitter Profile',
                'message' => 'You\'re almost ready to start accepting bookings! Please complete these important steps to verify your profile.',
                'missing_items' => [],
                'cta_text' => 'Complete Profile Now',
                'cta_link' => 'edit_profile.php'
            ];
            
            // Check critical missing items
            if (empty($host_profile['training_video_watched']) || !$host_profile['training_video_watched']) {
                $modal_content['missing_items'][] = '<strong>Watch Training Video</strong> (Required)';
            }
            if (empty($host_profile['id_verification_path'])) {
                $modal_content['missing_items'][] = '<strong>Upload Government-issued ID</strong> (Required)';
            }
            if ($host_profile['offers_home_sitting'] && $host_profile['dbs_check_status'] === 'not_required') {
                $modal_content['missing_items'][] = '<strong>DBS Check</strong> (Required for home sitting)';
            }
            if (empty($host_profile['profile_photo_living_room']) || empty($host_profile['profile_photo_sleeping_area'])) {
                $modal_content['missing_items'][] = 'Home photos (helps build trust with pet owners)';
            }
            
            // Check user profile photo
            $stmt_user = $pdo->prepare("SELECT profile_photo_path FROM users WHERE id = ?");
            $stmt_user->execute([$user_id]);
            $user_data = $stmt_user->fetch();
            if (empty($user_data['profile_photo_path'])) {
                $modal_content['missing_items'][] = 'Profile photo (increases booking chances by 70%)';
            }
        }
        
        // If nothing is missing, mark as complete and don't show modal
        if (empty($modal_content['missing_items'])) {
            $show_modal = false;
            // Mark profile as verified
            $stmt_update = $pdo->prepare("UPDATE users SET profile_verified = 1 WHERE id = ?");
            $stmt_update->execute([$user_id]);
            
            if ($user['role'] === 'owner') {
                $stmt_owner_update = $pdo->prepare("UPDATE owner_profiles SET profile_completed = 1 WHERE user_id = ?");
                $stmt_owner_update->execute([$user_id]);
            } else {
                $stmt_host_update = $pdo->prepare("UPDATE host_profiles SET profile_completed = 1 WHERE user_id = ?");
                $stmt_host_update->execute([$user_id]);
            }
        }
    }
    
    // Clear the session flag after first check
    if (isset($_SESSION['profile_incomplete'])) {
        unset($_SESSION['profile_incomplete']);
    }
    
} catch (PDOException $e) {
    error_log("Profile completion check error: " . $e->getMessage());
}
?>

<?php if ($show_modal && !empty($modal_content['missing_items'])): ?>
<div class="profile-completion-modal-overlay" id="profileCompletionModal">
    <div class="profile-completion-modal">
        <button class="modal-close-btn" onclick="closeProfileModal()">&times;</button>
        
        <div class="modal-icon">
            <i class="fa-solid <?php echo $modal_content['icon']; ?>"></i>
        </div>
        
        <h2><?php echo $modal_content['title']; ?></h2>
        <p class="modal-message"><?php echo $modal_content['message']; ?></p>
        
        <div class="missing-items-list">
            <h4>What's Missing:</h4>
            <ul>
                <?php foreach ($modal_content['missing_items'] as $item): ?>
                    <li><i class="fa-solid fa-circle-exclamation"></i> <?php echo $item; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <?php if ($modal_content['type'] === 'sitter'): ?>
        <div class="verification-notice">
            <i class="fa-solid fa-shield-halved"></i>
            <p><strong>Important:</strong> You cannot accept bookings until your profile is fully verified. This protects both you and pet owners.</p>
        </div>
        <?php endif; ?>
        
        <div class="modal-actions">
            <a href="<?php echo $modal_content['cta_link']; ?>" class="btn-complete-profile">
                <?php echo $modal_content['cta_text']; ?>
            </a>
            <button class="btn-later" onclick="closeProfileModal()">I'll do this later</button>
        </div>
    </div>
</div>

<style>
.profile-completion-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.profile-completion-modal {
    background: white;
    border-radius: 16px;
    padding: 40px;
    max-width: 550px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
    text-align: center;
}

@keyframes slideUp {
    from { 
        opacity: 0;
        transform: translateY(30px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-completion-modal .modal-close-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 36px;
    height: 36px;
    border: none;
    background: #f5f5f5;
    border-radius: 50%;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.profile-completion-modal .modal-close-btn:hover {
    background: #e8e8e8;
    color: #333;
}

.modal-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #00a862, #00c875);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.modal-icon i {
    font-size: 36px;
    color: white;
}

.profile-completion-modal h2 {
    font-size: 28px;
    color: #1a1a1a;
    margin-bottom: 12px;
    font-weight: 600;
}

.modal-message {
    color: #666;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 24px;
}

.missing-items-list {
    background: #f9f9f9;
    border-radius: 12px;
    padding: 24px;
    margin: 24px 0;
    text-align: left;
}

.missing-items-list h4 {
    color: #333;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
}

.missing-items-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.missing-items-list li {
    padding: 12px 0;
    border-bottom: 1px solid #e8e8e8;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #333;
    font-size: 15px;
}

.missing-items-list li:last-child {
    border-bottom: none;
}

.missing-items-list li i {
    color: #ff9800;
    font-size: 18px;
    flex-shrink: 0;
}

.verification-notice {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin: 20px 0;
    text-align: left;
}

.verification-notice i {
    color: #ffc107;
    font-size: 24px;
    flex-shrink: 0;
    margin-top: 2px;
}

.verification-notice p {
    margin: 0;
    color: #856404;
    font-size: 14px;
    line-height: 1.5;
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 28px;
}

.btn-complete-profile {
    background: linear-gradient(135deg, #00a862, #00c875);
    color: white;
    padding: 16px 32px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(0, 168, 98, 0.3);
}

.btn-complete-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 168, 98, 0.4);
}

.btn-later {
    background: transparent;
    color: #666;
    padding: 12px 24px;
    border: none;
    border-radius: 30px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-later:hover {
    background: #f5f5f5;
    color: #333;
}

@media (max-width: 600px) {
    .profile-completion-modal {
        padding: 30px 20px;
        width: 95%;
    }
    
    .profile-completion-modal h2 {
        font-size: 24px;
    }
    
    .modal-message {
        font-size: 15px;
    }
    
    .missing-items-list {
        padding: 20px 16px;
    }
}
</style>

<script>
function closeProfileModal() {
    const modal = document.getElementById('profileCompletionModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Prevent closing on outside click for first-time users
document.getElementById('profileCompletionModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        // Optional: allow closing by clicking outside
        // closeProfileModal();
    }
});
</script>
<?php endif; ?>