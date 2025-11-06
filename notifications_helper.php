<?php
/**
 * Helper functions for common notification scenarios
 * Include this file wherever you need quick notification triggers
 */

require_once 'notifications.php';
require_once 'db.php';

/**
 * Notify both parties about a new booking
 */
function notifyBookingCreated($booking_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT b.*, u1.fullname as owner_name, u2.fullname as sitter_name
        FROM bookings b
        JOIN users u1 ON b.user_id = u1.id
        JOIN users u2 ON b.sitter_id = u2.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) return false;
    
    // Notify owner
    sendNotification($booking['user_id'], 'booking_confirmed', [
        'booking_id' => $booking_id,
        'service_type' => $booking['service_type'],
        'start_date' => $booking['start_date'],
        'sitter_name' => $booking['sitter_name']
    ]);
    
    // Notify sitter
    sendNotification($booking['sitter_id'], 'booking_request', [
        'booking_id' => $booking_id,
        'service_type' => $booking['service_type'],
        'start_date' => $booking['start_date'],
        'end_date' => $booking['end_date'],
        'owner_name' => $booking['owner_name']
    ]);
    
    return true;
}

/**
 * Notify when booking is cancelled
 */
function notifyBookingCancelled($booking_id, $cancelled_by_user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT b.*, u1.id as owner_id, u2.id as sitter_id
        FROM bookings b
        JOIN users u1 ON b.user_id = u1.id
        JOIN users u2 ON b.sitter_id = u2.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) return false;
    
    // Notify the other party
    $notify_user_id = ($cancelled_by_user_id == $booking['owner_id']) 
        ? $booking['sitter_id'] 
        : $booking['owner_id'];
    
    sendNotification($notify_user_id, 'booking_cancelled', [
        'booking_id' => $booking_id,
        'service_type' => $booking['service_type'],
        'start_date' => $booking['start_date']
    ]);
    
    return true;
}

/**
 * Notify when a new message is received
 */
function notifyNewMessage($message_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT m.*, u.fullname as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch();
    
    if (!$message) return false;
    
    sendNotification($message['receiver_id'], 'new_message', [
        'sender_id' => $message['sender_id'],
        'sender_name' => $message['sender_name'],
        'message_preview' => substr($message['message'], 0, 100)
    ]);
    
    return true;
}

/**
 * Notify when profile verification status changes
 */
function notifyVerificationStatus($user_id, $status) {
    $type = ($status === 'verified') ? 'verification_approved' : 'verification_rejected';
    return sendNotification($user_id, $type);
}

/**
 * Send booking reminder (e.g., via cron job)
 */
function sendBookingReminders() {
    global $pdo;
    
    // Get bookings starting in 2 days
    $stmt = $pdo->query("
        SELECT b.*, u.id as owner_id
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.start_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY)
        AND b.booking_status = 'confirmed'
    ");
    
    $count = 0;
    while ($booking = $stmt->fetch()) {
        sendNotification($booking['owner_id'], 'reminder_booking', [
            'booking_id' => $booking['id'],
            'service_type' => $booking['service_type'],
            'start_date' => $booking['start_date'],
            'days_until' => 2
        ]);
        $count++;
    }
    
    return $count;
}

/**
 * Notify incomplete profile (can be called periodically)
 */
function notifyIncompleteProfiles() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT u.id 
        FROM users u
        LEFT JOIN owner_profiles op ON u.id = op.user_id
        LEFT JOIN host_profiles hp ON u.id = hp.user_id
        WHERE u.profile_verified = 0
        AND DATEDIFF(NOW(), u.created_at) >= 3
        AND (op.profile_completed = 0 OR hp.profile_completed = 0)
    ");
    
    $count = 0;
    while ($user = $stmt->fetch()) {
        sendNotification($user['id'], 'profile_incomplete');
        $count++;
    }
    
    return $count;
}

/**
 * Notify when payment is received
 */
function notifyPaymentReceived($booking_id, $amount) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT user_id FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $user_id = $stmt->fetchColumn();
    
    if (!$user_id) return false;
    
    return sendNotification($user_id, 'payment_received', [
        'booking_id' => $booking_id,
        'amount' => number_format($amount, 2)
    ]);
}

/**
 * Notify sitter about payout
 */
function notifyPayoutProcessed($user_id, $amount) {
    return sendNotification($user_id, 'payout_processed', [
        'amount' => number_format($amount, 2)
    ]);
}