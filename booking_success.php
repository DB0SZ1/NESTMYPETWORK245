<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'vendor/autoload.php';
require 'db.php';

// --- Stripe API Keys (Replace with your actual keys) ---
$stripeSecretKey = 'sk_test_51RrMbj3lvfiHaIRcBGGNoiMv9n7pqV6We1iXaQUOWuPic65AHD1rQd56Docq13ik7Adt1JBWGjcjDsbGYjz9pTpb002o9k2sjG';
\Stripe\Stripe::setApiKey($stripeSecretKey);
// ---------------------------------------------------------

$pageTitle = "Booking Successful";
include 'header.php';

$booking_id = null;
$error_message = null;

// --- Get the Stripe session ID from the URL ---
$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    $error_message = "Invalid session ID.";
} else {
    try {
        // --- Retrieve the Stripe Checkout Session ---
        $session = \Stripe\Checkout\Session::retrieve($session_id);

        // --- Retrieve the Payment Intent ---
        $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);

        // --- Get the booking ID from metadata ---
        $booking_id = $session->metadata->booking_id;

        // --- Verify Payment Status ---
        if ($paymentIntent->status == 'succeeded' && $booking_id) {
            
            // --- Check if we already processed this booking ---
            $stmt_check = $pdo->prepare("SELECT booking_status FROM bookings WHERE id = ?");
            $stmt_check->execute([$booking_id]);
            $current_status = $stmt_check->fetchColumn();

            if ($current_status === 'pending_payment') {
                // --- Update Booking Status ---
                $stmt_update_booking = $pdo->prepare("UPDATE bookings SET booking_status = 'confirmed', stripe_checkout_session_id = ? WHERE id = ?");
                $stmt_update_booking->execute([$session_id, $booking_id]);

                // --- Record the Transaction ---
                $stmt_insert_txn = $pdo->prepare("INSERT INTO transactions (booking_id, user_id, stripe_payment_intent_id, amount_paid, currency, payment_status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_insert_txn->execute([
                    $booking_id,
                    $_SESSION['user_id'], // Assuming user is still logged in
                    $paymentIntent->id,
                    $paymentIntent->amount / 100, // Convert from pence/cents
                    $paymentIntent->currency,
                    $paymentIntent->status
                ]);
                
                // Set success message for display
                $_SESSION['success_message'] = "Your booking is confirmed! Details have been saved.";

            } elseif ($current_status === 'confirmed') {
                // Already confirmed, just show success message
                $_SESSION['success_message'] = "Your booking was already confirmed.";
            } else {
                 $error_message = "Booking status could not be updated (" . $current_status . ").";
            }

        } else {
            // Payment failed or booking ID missing
            $error_message = "Payment verification failed. Status: " . ($paymentIntent->status ?? 'unknown');
        }

    } catch (\Stripe\Exception\ApiErrorException $e) {
        error_log("Stripe Error on Success Page: " . $e->getMessage());
        $error_message = "Could not retrieve payment details. Please contact support.";
    } catch (PDOException $e) {
        error_log("Database Error on Success Page: " . $e->getMessage());
        $error_message = "Could not update booking details. Please contact support.";
    } catch (Exception $e) {
         error_log("General Error on Success Page: " . $e->getMessage());
         $error_message = "An unexpected error occurred. Please contact support.";
    }
}
?>

<main class="dashboard-page">
    <div class="container" style="max-width: 600px; text-align: center; padding: 4rem 1rem;">
        
        <?php if ($error_message): ?>
            <div class="dashboard-card" style="border-top: 5px solid var(--red-color);">
                <i class="fas fa-times-circle" style="font-size: 3rem; color: var(--red-color); margin-bottom: 1rem;"></i>
                <h2>Payment Failed</h2>
                <p style="font-size: 1.1rem;"><?php echo htmlspecialchars($error_message); ?></p>
                <a href="search.php" class="btn btn-outline" style="margin-top: 1rem;">Find Another Sitter</a>
            </div>
        <?php else: ?>
            <div class="dashboard-card" style="border-top: 5px solid var(--green-color);">
                 <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--green-color); margin-bottom: 1rem;"></i>
                <h2>Booking Confirmed!</h2>
                <p style="font-size: 1.1rem;">Your payment was successful and your booking (ID: <?php echo htmlspecialchars($booking_id); ?>) is confirmed.</p>
                <p>You can view your booking details in your dashboard.</p>
                 <a href="dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">Go to Dashboard</a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'footer.php'; ?>