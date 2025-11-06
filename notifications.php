<?php
/**
 * NestMyPet Notification System
 * Handles email notifications and in-app notifications
 * 
 * Usage: 
 * require_once 'notifications.php';
 * sendNotification($user_id, 'booking_confirmed', ['booking_id' => 123]);
 */

require_once 'db.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Main notification function - call this from anywhere
 * 
 * @param int $user_id - ID of user to notify
 * @param string $type - Type of notification (see NOTIFICATION_TYPES below)
 * @param array $data - Additional data for the notification
 * @param bool $email_only - If true, only send email (no in-app notification)
 * @return bool - Success status
 */
function sendNotification($user_id, $type, $data = [], $email_only = false) {
    global $pdo;
    
    try {
        // Get user details
        $stmt = $pdo->prepare("SELECT fullname, email, role, is_sitter FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            error_log("Notification Error: User ID $user_id not found");
            return false;
        }
        
        // Get notification configuration
        $config = getNotificationConfig($type, $data, $user);
        
        if (!$config) {
            error_log("Notification Error: Unknown notification type '$type'");
            return false;
        }
        
        // Store in-app notification (unless email_only)
        if (!$email_only) {
            storeInAppNotification($user_id, $type, $config['title'], $config['message'], $data);
        }
        
        // Send email notification
        $email_sent = sendEmailNotification(
            $user['email'],
            $user['fullname'],
            $config['subject'],
            $config['email_body'],
            $config['cta_text'] ?? null,
            $config['cta_link'] ?? null
        );
        
        return $email_sent;
        
    } catch (Exception $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send notification to multiple users
 */
function sendBulkNotification($user_ids, $type, $data = []) {
    $results = [];
    foreach ($user_ids as $user_id) {
        $results[$user_id] = sendNotification($user_id, $type, $data);
    }
    return $results;
}

/**
 * Store in-app notification in database
 */
function storeInAppNotification($user_id, $type, $title, $message, $data = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, data, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        
        return $stmt->execute([
            $user_id,
            $type,
            $title,
            $message,
            json_encode($data)
        ]);
        
    } catch (PDOException $e) {
        error_log("Store Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email using PHPMailer
 */
function sendEmailNotification($to_email, $to_name, $subject, $body, $cta_text = null, $cta_link = null) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration (replace with your actual credentials)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Your email
        $mail->Password = 'your-app-password'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Email settings
        $mail->setFrom('noreply@nestmypet.com', 'NestMyPet');
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // Build email HTML
        $mail->Body = buildEmailTemplate($to_name, $body, $cta_text, $cta_link);
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email Send Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Get notification configuration based on type
 */
function getNotificationConfig($type, $data, $user) {
    global $pdo;
    
    $configs = [
        // BOOKING NOTIFICATIONS
        'booking_request' => [
            'title' => 'New Booking Request',
            'message' => 'You have a new booking request',
            'subject' => 'New Booking Request - NestMyPet',
            'email_body' => "You have received a new booking request for {$data['service_type']} from {$data['start_date']} to {$data['end_date']}.",
            'cta_text' => 'View Booking',
            'cta_link' => "my_bookings.php?id={$data['booking_id']}"
        ],
        
        'booking_confirmed' => [
            'title' => 'Booking Confirmed!',
            'message' => 'Your booking has been confirmed',
            'subject' => 'Booking Confirmed - NestMyPet',
            'email_body' => "Great news! Your booking for {$data['service_type']} has been confirmed. Your sitter will be expecting you on {$data['start_date']}.",
            'cta_text' => 'View Booking Details',
            'cta_link' => "my_bookings.php?id={$data['booking_id']}"
        ],
        
        'booking_cancelled' => [
            'title' => 'Booking Cancelled',
            'message' => 'A booking has been cancelled',
            'subject' => 'Booking Cancellation - NestMyPet',
            'email_body' => "Your booking for {$data['service_type']} scheduled for {$data['start_date']} has been cancelled.",
            'cta_text' => 'Find Another Sitter',
            'cta_link' => 'search.php'
        ],
        
        'booking_completed' => [
            'title' => 'Booking Completed',
            'message' => 'Your booking is complete',
            'subject' => 'Booking Completed - Leave a Review!',
            'email_body' => "Your booking with {$data['sitter_name']} has been completed. We hope your pet had a wonderful time! Please consider leaving a review.",
            'cta_text' => 'Leave a Review',
            'cta_link' => "leave_review.php?booking_id={$data['booking_id']}"
        ],
        
        // MESSAGE NOTIFICATIONS
        'new_message' => [
            'title' => 'New Message',
            'message' => "You have a new message from {$data['sender_name']}",
            'subject' => 'New Message - NestMyPet',
            'email_body' => "{$data['sender_name']} has sent you a message: \"{$data['message_preview']}\"",
            'cta_text' => 'View Message',
            'cta_link' => "messages.php?user_id={$data['sender_id']}"
        ],
        
        // PAYMENT NOTIFICATIONS
        'payment_received' => [
            'title' => 'Payment Received',
            'message' => 'Payment received successfully',
            'subject' => 'Payment Confirmation - NestMyPet',
            'email_body' => "We've received your payment of £{$data['amount']} for booking #{$data['booking_id']}. Thank you!",
            'cta_text' => 'View Transaction',
            'cta_link' => "dashboard.php"
        ],
        
        'payout_processed' => [
            'title' => 'Payout Processed',
            'message' => "Your payout of £{$data['amount']} has been processed",
            'subject' => 'Payout Processed - NestMyPet',
            'email_body' => "Great news! Your payout of £{$data['amount']} has been processed and should arrive in your account within 2-3 business days.",
            'cta_text' => 'View Earnings',
            'cta_link' => 'dashboard.php'
        ],
        
        // PROFILE NOTIFICATIONS
        'profile_incomplete' => [
            'title' => 'Complete Your Profile',
            'message' => 'Your profile is incomplete',
            'subject' => 'Complete Your Profile - NestMyPet',
            'email_body' => "Welcome to NestMyPet! Please complete your profile to start {$user['is_sitter'] ? 'accepting bookings' : 'booking sitters'}.",
            'cta_text' => 'Complete Profile',
            'cta_link' => 'edit_profile.php'
        ],
        
        'verification_approved' => [
            'title' => 'Profile Verified!',
            'message' => 'Your profile has been verified',
            'subject' => 'Profile Verified - NestMyPet',
            'email_body' => "Congratulations! Your profile has been verified. You can now start {$user['is_sitter'] ? 'accepting bookings' : 'booking trusted sitters'}.",
            'cta_text' => 'View Profile',
            'cta_link' => 'profile.php'
        ],
        
        'verification_rejected' => [
            'title' => 'Verification Update',
            'message' => 'Your verification needs attention',
            'subject' => 'Verification Update - NestMyPet',
            'email_body' => "We need additional information to verify your profile. Please review our feedback and resubmit your documents.",
            'cta_text' => 'Update Verification',
            'cta_link' => 'upload_verification.php'
        ],
        
        // REVIEW NOTIFICATIONS
        'new_review' => [
            'title' => 'New Review',
            'message' => "You received a new review from {$data['reviewer_name']}",
            'subject' => 'New Review Received - NestMyPet',
            'email_body' => "{$data['reviewer_name']} left you a {$data['rating']}-star review: \"{$data['review_text']}\"",
            'cta_text' => 'View Review',
            'cta_link' => 'profile.php'
        ],
        
        // SYSTEM NOTIFICATIONS
        'welcome_owner' => [
            'title' => 'Welcome to NestMyPet!',
            'message' => 'Welcome! Start finding trusted sitters',
            'subject' => 'Welcome to NestMyPet!',
            'email_body' => "Welcome to NestMyPet! We're excited to help you find the perfect care for your furry friends. Browse our trusted sitters and book with confidence.",
            'cta_text' => 'Find a Sitter',
            'cta_link' => 'search.php'
        ],
        
        'welcome_sitter' => [
            'title' => 'Welcome to NestMyPet!',
            'message' => 'Welcome! Complete your profile to start earning',
            'subject' => 'Welcome to NestMyPet - Start Earning!',
            'email_body' => "Welcome to the NestMyPet family! Complete your profile and verification to start accepting bookings and earning money caring for adorable pets.",
            'cta_text' => 'Complete Profile',
            'cta_link' => 'edit_profile.php'
        ],
        
        'reminder_booking' => [
            'title' => 'Booking Reminder',
            'message' => "Your booking starts in {$data['days_until']} days",
            'subject' => 'Upcoming Booking Reminder - NestMyPet',
            'email_body' => "This is a friendly reminder that your booking for {$data['service_type']} starts on {$data['start_date']}. Make sure everything is prepared!",
            'cta_text' => 'View Booking',
            'cta_link' => "my_bookings.php?id={$data['booking_id']}"
        ]
    ];
    
    return $configs[$type] ?? null;
}

/**
 * Build professional email HTML template
 */
function buildEmailTemplate($recipient_name, $body, $cta_text = null, $cta_link = null) {
    $cta_button = '';
    if ($cta_text && $cta_link) {
        $full_link = "https://yourdomain.com/" . ltrim($cta_link, '/');
        $cta_button = "
            <table cellpadding='0' cellspacing='0' style='margin: 30px auto;'>
                <tr>
                    <td style='background: linear-gradient(135deg, #00a862, #00c875); padding: 16px 40px; border-radius: 30px;'>
                        <a href='$full_link' style='color: white; text-decoration: none; font-weight: 600; font-size: 16px;'>$cta_text</a>
                    </td>
                </tr>
            </table>
        ";
    }
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background: #f5f5f5;'>
        <table cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; margin: 0 auto; background: white;'>
            <tr>
                <td style='padding: 40px 30px; text-align: center; background: linear-gradient(135deg, #00a862, #00c875);'>
                    <h1 style='margin: 0; color: white; font-size: 28px; font-weight: 700;'>🐾 NestMyPet</h1>
                </td>
            </tr>
            <tr>
                <td style='padding: 40px 30px;'>
                    <p style='margin: 0 0 20px 0; font-size: 16px; color: #333;'>Hi $recipient_name,</p>
                    <p style='margin: 0 0 20px 0; font-size: 16px; color: #666; line-height: 1.6;'>$body</p>
                    $cta_button
                    <p style='margin: 30px 0 0 0; font-size: 14px; color: #999;'>
                        Best regards,<br>
                        The NestMyPet Team
                    </p>
                </td>
            </tr>
            <tr>
                <td style='padding: 30px; text-align: center; background: #f9f9f9; border-top: 1px solid #e8e8e8;'>
                    <p style='margin: 0; font-size: 12px; color: #999;'>
                        © 2025 NestMyPet. All rights reserved.<br>
                        <a href='https://yourdomain.com' style='color: #00a862; text-decoration: none;'>Visit our website</a>
                    </p>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";
}

/**
 * Create notifications table if it doesn't exist
 */
function createNotificationsTable() {
    global $pdo;
    
    $sql = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        data JSON,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_read (user_id, is_read),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        error_log("Create Notifications Table Error: " . $e->getMessage());
        return false;
    }
}

// Auto-create table on first include
createNotificationsTable();