<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'vendor/autoload.php';
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$verification_url = null;
$error_message = null;
$success_message = null;
$verification_status = null; // Track status type for UI display
$show_status_message = false;

// Initialize Stripe
$stripeSecretKey = trim('sk_test_51RrMbj3lvfiHaIRcBGGNoiMv9n7pqV6We1iXaQUOWuPic65AHD1rQd56Docq13ik7Adt1JBWGjcjDsbGYjz9pTpb002o9k2sjG');
\Stripe\Stripe::setApiKey($stripeSecretKey);

try {
    // Get user data
    $stmt = $pdo->prepare("SELECT fullname, email, stripe_customer_id, profile_verified, verification_notes FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception("User not found");
    }
    
    // Check if already verified
    if ($user['profile_verified']) {
        $_SESSION['success_message'] = "Your profile is already verified!";
        header('Location: dashboard.php');
        exit();
    }
    
    // Handle "Skip for now" action
    if (isset($_GET['skip']) && $_GET['skip'] === 'true') {
        $_SESSION['info_message'] = "You can complete verification later from your dashboard.";
        header('Location: dashboard.php');
        exit();
    }
    
    if ($user['verification_notes']) {
        try {
            $verificationSession = \Stripe\Identity\VerificationSession::retrieve($user['verification_notes']);
            $verification_status = $verificationSession->status;
            $show_status_message = true;
            
            // Handle each possible status
            if ($verificationSession->status === 'verified') {
                $stmt_verify = $pdo->prepare("UPDATE users SET profile_verified = 1, verification_status = 'verified' WHERE id = ?");
                $stmt_verify->execute([$user_id]);
                $_SESSION['success_message'] = "Identity verified successfully! Your profile is now active.";
                header('Location: dashboard.php');
                exit();
            } elseif ($verificationSession->status === 'requires_input') {
                $error_message = "⚠ Verification requires additional input. Please complete the process or try again.";
                $verification_status = 'failed';
            } elseif ($verificationSession->status === 'unverified') {
                $error_message = "✗ Verification was not completed. Please start the verification process again.";
                $verification_status = 'failed';
            } elseif ($verificationSession->status === 'canceled') {
                $error_message = "✗ Verification was canceled. You can start a new verification session below.";
                $verification_status = 'canceled';
                // Clear the old session ID so user can start fresh
                $stmt_clear = $pdo->prepare("UPDATE users SET verification_notes = NULL WHERE id = ?");
                $stmt_clear->execute([$user_id]);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // If verification session retrieval fails, allow user to start fresh
            error_log("Verification Status Check Error: " . $e->getMessage());
        }
    }
    
    // Create Stripe Identity Verification Session
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_verification'])) {
        try {
            $verificationSession = \Stripe\Identity\VerificationSession::create([
                'type' => 'document',
                'metadata' => [
                    'user_id' => $user_id,
                ],
                'options' => [
                    'document' => [
                        'allowed_types' => ['driving_license', 'passport', 'id_card'],
                        'require_matching_selfie' => true,
                    ],
                ],
            ]);
            
            // Store session ID in database
            $stmt_update = $pdo->prepare("UPDATE users SET verification_status = 'pending', verification_notes = ? WHERE id = ?");
            $stmt_update->execute([$verificationSession->id, $user_id]);
            
            $verification_url = $verificationSession->url;
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $error_message = "Verification error: " . $e->getMessage();
            error_log("Stripe Identity Error: " . $e->getMessage());
        }
    }
    
    // Check for verification callback from Stripe
    if (isset($_GET['session_id'])) {
        $session_id = $_GET['session_id'];
        
        try {
            $verificationSession = \Stripe\Identity\VerificationSession::retrieve($session_id);
            
            if ($verificationSession->status === 'verified') {
                $stmt_verify = $pdo->prepare("UPDATE users SET profile_verified = 1, verification_status = 'verified' WHERE id = ?");
                $stmt_verify->execute([$user_id]);
                
                $success_message = "✓ Identity Verified Successfully! Your profile is now active and you can start accepting bookings.";
                $verification_status = 'success';
            } elseif ($verificationSession->status === 'requires_input') {
                $error_message = "⚠ Verification requires additional input. Please try again.";
                $verification_status = 'failed';
            } elseif ($verificationSession->status === 'canceled') {
                $error_message = "✗ Verification was canceled. Please start again.";
                $verification_status = 'canceled';
            } elseif ($verificationSession->status === 'unverified') {
                $error_message = "✗ Verification could not be completed. Please try again.";
                $verification_status = 'failed';
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $error_message = "Error checking verification: " . $e->getMessage();
            $verification_status = 'error';
        }
    }
    
} catch (Exception $e) {
    error_log("Verification Page Error: " . $e->getMessage());
    $error_message = "An error occurred: " . $e->getMessage();
}

$pageTitle = "Identity Verification";
include 'header.php';
?>

<link rel="stylesheet" href="dashboard_new.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<main class="dashboard-page">
    <div class="container">
        <div class="verification-container">
            <div class="dashboard-card verification-card">
                
                <!-- Dynamic icon based on verification result -->
                <div class="verification-icon">
                    <?php if ($verification_status === 'success'): ?>
                        <i class="fa-solid fa-circle-check" style="color: white;"></i>
                    <?php elseif ($verification_status === 'failed' || $verification_status === 'canceled' || $verification_status === 'error'): ?>
                        <i class="fa-solid fa-circle-xmark" style="color: white;"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-shield-halved"></i>
                    <?php endif; ?>
                </div>
                
                <!-- Dynamic heading based on verification result -->
                <?php if ($verification_status === 'success'): ?>
                    <h1>Verification Successful!</h1>
                <?php elseif ($verification_status === 'failed' || $verification_status === 'canceled' || $verification_status === 'error'): ?>
                    <h1>Verification Failed</h1>
                <?php else: ?>
                    <h1>Identity Verification Required</h1>
                <?php endif; ?>
                
                <!-- Success message popup -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <strong>Success!</strong>
                            <p><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    </div>
                    <a href="dashboard.php" class="btn btn-primary btn-full-green btn-large">
                        <i class="fa-solid fa-arrow-right"></i> Go to Dashboard
                    </a>
                
                <!-- Error/failure message popup -->
                <?php elseif ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>
                            <strong>Verification Status:</strong>
                            <p><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    </div>
                    <!-- Add retry and back buttons -->
                    <div style="margin-top: 20px;">
                        <button onclick="location.href='upload_verification.php'" class="btn btn-primary btn-full-green btn-large">
                            <i class="fa-solid fa-redo"></i> Try Again
                        </button>
                        <button onclick="location.href='dashboard.php'" class="btn-link-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                        </button>
                    </div>
                
                <!-- Normal verification flow when no status message -->
                <?php elseif ($verification_url): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        Verification session created! Click the button below to continue.
                    </div>
                    <a href="<?php echo htmlspecialchars($verification_url); ?>" class="btn btn-primary btn-full-green btn-large">
                        <i class="fa-solid fa-arrow-right"></i> Continue to Verification
                    </a>
                    <button onclick="skipVerification()" class="btn-link-secondary">I'll do this later</button>
                
                <?php else: ?>
                    <div class="verification-info">
                        <p>To ensure the safety of our community, we need to verify your identity.</p>
                        
                        <div class="verification-steps">
                            <div class="step">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h3>Prepare Your ID</h3>
                                    <p>Have a valid government-issued ID ready (Passport, Driver's License, or National ID Card)</p>
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h3>Take a Selfie</h3>
                                    <p>You'll be asked to take a selfie to match with your ID photo</p>
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h3>Get Verified</h3>
                                    <p>Verification usually completes instantly. You'll be notified once approved</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="verification-notice">
                            <i class="fa-solid fa-lock"></i>
                            <p><strong>Your privacy matters:</strong> All verification data is securely encrypted and handled by Stripe Identity. We never store your ID images.</p>
                        </div>
                        
                        <form method="POST">
                            <button type="submit" name="start_verification" class="btn btn-primary btn-full-green btn-large">
                                <i class="fa-solid fa-id-card"></i> Start Verification
                            </button>
                        </form>
                        
                        <button onclick="skipVerification()" class="btn-link-secondary">I'll do this later</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.verification-container {
    max-width: 700px;
    margin: 60px auto;
    padding: 0 20px;
    position: relative;
}

.verification-card {
    text-align: center;
    padding: 50px 40px;
    position: relative;
}

.verification-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #00a862, #00c875);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
}

.verification-icon i {
    font-size: 50px;
    color: white;
}

.verification-card h1 {
    font-size: 32px;
    color: #1a1a1a;
    margin-bottom: 20px;
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin: 20px 0;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 15px;
}

.alert i {
    font-size: 20px;
    flex-shrink: 0;
    margin-top: 2px;
}

.alert strong {
    display: block;
    margin-bottom: 8px;
}

.alert p {
    margin: 0;
    line-height: 1.5;
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

.verification-info {
    text-align: left;
    margin-top: 30px;
}

.verification-info > p {
    font-size: 16px;
    color: #666;
    margin-bottom: 30px;
    text-align: center;
}

.verification-steps {
    margin: 40px 0;
}

.step {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    align-items: flex-start;
}

.step-number {
    width: 50px;
    height: 50px;
    background: #00a862;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content h3 {
    font-size: 18px;
    color: #1a1a1a;
    margin: 0 0 8px 0;
}

.step-content p {
    font-size: 14px;
    color: #666;
    margin: 0;
    line-height: 1.6;
}

.verification-notice {
    background: #f5f5f5;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    margin: 30px 0;
}

.verification-notice i {
    font-size: 24px;
    color: #00a862;
    flex-shrink: 0;
}

.verification-notice p {
    margin: 0;
    font-size: 14px;
    color: #666;
    line-height: 1.6;
}

.btn-large {
    padding: 18px 40px;
    font-size: 18px;
    margin-top: 20px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.btn-link-secondary {
    display: inline-block;
    margin-top: 20px;
    color: #666;
    text-decoration: none;
    font-size: 14px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 10px;
}

.btn-link-secondary:hover {
    color: #333;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .verification-card {
        padding: 40px 25px;
    }
    
    .verification-card h1 {
        font-size: 26px;
    }
    
    .step {
        flex-direction: column;
        text-align: center;
    }
    
    .step-number {
        margin: 0 auto;
    }
}
</style>

<script>
function skipVerification() {
    if (confirm('Are you sure you want to skip verification? You can complete this later from your dashboard.')) {
        window.location.href = 'upload_verification.php?skip=true';
    }
}
</script>

<?php include 'footer.php'; ?>