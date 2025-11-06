<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

$pageTitle = "Booking Cancelled";
include 'header.php';

// Optional: You could update the booking status to 'cancelled' here if desired
$booking_id = $_GET['booking_id'] ?? null;
/*
if ($booking_id && isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ? AND user_id = ? AND booking_status = 'pending_payment'");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Error cancelling booking ID $booking_id: " . $e->getMessage());
    }
}
*/

$_SESSION['error_message'] = "Your booking payment was cancelled or could not be completed.";

?>

<main class="dashboard-page">
    <div class="container" style="max-width: 600px; text-align: center; padding: 4rem 1rem;">
        
        <div class="dashboard-card" style="border-top: 5px solid var(--orange-color);">
             <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--orange-color); margin-bottom: 1rem;"></i>
            <h2>Booking Cancelled</h2>
            <p style="font-size: 1.1rem;">Your payment was cancelled. The booking (ID: <?php echo htmlspecialchars($booking_id); ?>) has not been confirmed.</p>
            <p>You can try booking again or return to your dashboard.</p>
             <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 1rem;">
                 <?php 
                 // Try to get sitter ID to link back
                 $sitter_id = null;
                 if ($booking_id) {
                     try {
                         $stmt = $pdo->prepare("SELECT sitter_id FROM bookings WHERE id = ?");
                         $stmt->execute([$booking_id]);
                         $sitter_id = $stmt->fetchColumn();
                     } catch(PDOException $e) {} // Ignore error if fetch fails
                 }
                 if ($sitter_id): 
                 ?>
                 <a href="sitter_profile.php?id=<?php echo $sitter_id; ?>" class="btn btn-primary">Try Again</a>
                 <?php endif; ?>
                 <a href="dashboard.php" class="btn btn-outline">Go to Dashboard</a>
            </div>
        </div>

    </div>
</main>

<?php include 'footer.php'; ?>