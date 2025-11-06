<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// This page's main job is to show a success message.
// The actual verification of the sitter's Stripe account happens in the background.

$pageTitle = "Payout Setup Complete";
include 'header.php'; // Include your standard header

// Set a success message that will be displayed on the dashboard when they are redirected.
$_SESSION['success_message'] = "Your payout account has been connected successfully! Stripe is now reviewing your details.";

?>
<main class="dashboard-page">
    <div class="container" style="max-width: 600px; text-align: center; padding: 4rem 1rem;">
        
        <div class="dashboard-card" style="border-top: 5px solid var(--green-color);">
             <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--green-color); margin-bottom: 1rem;"></i>
            <h2>Payout Account Connected!</h2>
            <p style="font-size: 1.1rem;">Thank you for setting up your payout details. You will be notified by Stripe once your account is fully verified and ready to receive payments.</p>
            <p>You will now be redirected back to your dashboard.</p>
            <a href="dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">Go to Dashboard Now</a>
        </div>

    </div>
</main>

<script>
    // Automatically redirect the user back to their dashboard after a few seconds
    // to provide a smooth user experience.
    setTimeout(function() {
        window.location.href = 'dashboard.php';
    }, 5000); // Redirect after 5 seconds
</script>

<?php include 'footer.php'; // Include your standard footer ?>

