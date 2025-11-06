<?php
// manage_payments.php (NOW FOR SITTER PAYOUTS)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'vendor/autoload.php';
require 'db.php';

// --- Security Check & Setup ---
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$stripeSecretKey = trim('sk_test_51RrMbj3lvfiHaIRcBGGNoiMv9n7pqV6We1iXaQUOWuPic65AHD1rQd56Docq13ik7Adt1JBWGjcjDsbGYjz9pTpb002o9k2sjG');
$stripe = new \Stripe\StripeClient($stripeSecretKey);
$YOUR_DOMAIN = 'http://localhost/nestpet'; // Using localhost as requested

// --- Fetch User's Sitter Status and Stripe Connect ID ---
try {
    $stmt = $pdo->prepare("SELECT email, stripe_connect_id, is_sitter FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // This page is now ONLY for sitters (both home and normal)
    if (!$user || $user['is_sitter'] != 1) {
        $_SESSION['error_message'] = "This feature is only available for registered sitters.";
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("DB Error fetching user for payout setup: " . $e->getMessage());
    $_SESSION['error_message'] = "A database error occurred.";
    header('Location: dashboard.php');
    exit();
}

$stripe_connect_id = $user['stripe_connect_id'];

try {
    // --- Step 1: Create a Stripe Connect account if it doesn't exist ---
    if (empty($stripe_connect_id)) {
        $account = $stripe->accounts->create([
            'type' => 'express',
            'country' => 'GB', // Great Britain
            'email' => $user['email'],
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);
        $stripe_connect_id = $account->id;

        // Save the new Connect ID to your database
        $update_stmt = $pdo->prepare("UPDATE users SET stripe_connect_id = ? WHERE id = ?");
        $update_stmt->execute([$stripe_connect_id, $user_id]);
    }

    // --- Step 2: Create an Account Link for onboarding/management ---
    $account_link = $stripe->accountLinks->create([
        'account' => $stripe_connect_id,
        'refresh_url' => $YOUR_DOMAIN . '/manage_payments.php', // Re-trigger this page if link expires
        'return_url' => $YOUR_DOMAIN . '/payout_return.php',  // Page to return to after completion
        'type' => 'account_onboarding',
    ]);

    // --- Step 3: Redirect to Stripe's secure onboarding flow ---
    header("HTTP/1.1 303 See Other");
    header("Location: " . $account_link->url);
    exit();

} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe Connect Error for user $user_id: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not connect to Stripe to set up payouts. Please try again later.";
    header('Location: dashboard.php');
    exit();
} catch (Exception $e) {
    error_log("General Error in payout setup for user $user_id: " . $e->getMessage());
    $_SESSION['error_message'] = "An unexpected error occurred during payout setup.";
    header('Location: dashboard.php');
    exit();
}
?>
