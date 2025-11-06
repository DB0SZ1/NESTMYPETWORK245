<?php
require 'vendor/autoload.php';
require 'db.php';

// Set Stripe API key
$stripeSecretKey = trim('sk_test_51RrMbj3lvfiHaIRcBGGNoiMv9n7pqV6We1iXaQUOWuPic65AHD1rQd56Docq13ik7Adt1JBWGjcjDsbGYjz9pTpb002o9k2sjG');
\Stripe\Stripe::setApiKey($stripeSecretKey);

// Webhook secret from Stripe Dashboard
$webhookSecret = 'whsec_YOUR_WEBHOOK_SECRET'; // Replace with your actual webhook secret

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $webhookSecret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    error_log('Webhook Error: Invalid payload');
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    error_log('Webhook Error: Invalid signature');
    http_response_code(400);
    exit();
}

// Handle the event
switch ($event->type) {
    case 'identity.verification_session.verified':
        $verificationSession = $event->data->object;
        handleVerificationSuccess($verificationSession, $pdo);
        break;
        
    case 'identity.verification_session.requires_input':
        $verificationSession = $event->data->object;
        handleVerificationRequiresInput($verificationSession, $pdo);
        break;
        
    case 'identity.verification_session.canceled':
        $verificationSession = $event->data->object;
        handleVerificationCanceled($verificationSession, $pdo);
        break;
        
    default:
        error_log('Received unknown event type: ' . $event->type);
}

http_response_code(200);

function handleVerificationSuccess($session, $pdo) {
    $sessionId = $session->id;
    
    try {
        // Find user by verification session ID stored in verification_notes
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_notes = ?");
        $stmt->execute([$sessionId]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Mark profile as verified
            $stmt_update = $pdo->prepare("
                UPDATE users 
                SET profile_verified = 1, 
                    verification_status = 'verified',
                    verification_submitted_at = NOW()
                WHERE id = ?
            ");
            $stmt_update->execute([$user['id']]);
            
            error_log("User ID {$user['id']} successfully verified via Stripe Identity");
        } else {
            error_log("No user found with verification session ID: $sessionId");
        }
    } catch (PDOException $e) {
        error_log("Database error in verification webhook: " . $e->getMessage());
    }
}

function handleVerificationRequiresInput($session, $pdo) {
    $sessionId = $session->id;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_notes = ?");
        $stmt->execute([$sessionId]);
        $user = $stmt->fetch();
        
        if ($user) {
            $stmt_update = $pdo->prepare("
                UPDATE users 
                SET verification_status = 'pending'
                WHERE id = ?
            ");
            $stmt_update->execute([$user['id']]);
            
            error_log("User ID {$user['id']} verification requires additional input");
        }
    } catch (PDOException $e) {
        error_log("Database error in verification webhook: " . $e->getMessage());
    }
}

function handleVerificationCanceled($session, $pdo) {
    $sessionId = $session->id;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_notes = ?");
        $stmt->execute([$sessionId]);
        $user = $stmt->fetch();
        
        if ($user) {
            $stmt_update = $pdo->prepare("
                UPDATE users 
                SET verification_status = 'rejected'
                WHERE id = ?
            ");
            $stmt_update->execute([$user['id']]);
            
            error_log("User ID {$user['id']} verification was canceled");
        }
    } catch (PDOException $e) {
        error_log("Database error in verification webhook: " . $e->getMessage());
    }
}
?>