<?php
/**
 * NestMyPet Notification Dispatcher
 * Automatically detects events and sends notifications
 * 
 * HOW TO USE:
 * 1. Include this file ONCE at the top of db.php or config.php
 * 2. It will automatically detect database changes and trigger notifications
 * 
 * NO CODE CHANGES NEEDED IN OTHER FILES!
 */

require_once 'notifications.php';

class NotificationDispatcher {
    private $pdo;
    private $enabled = true;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->registerEventListeners();
    }
    
    /**
     * Register database triggers to auto-detect events
     */
    private function registerEventListeners() {
        try {
            // Monitor bookings table for new/updated bookings
            $this->createBookingTriggers();
            
            // Monitor messages table for new messages
            $this->createMessageTriggers();
            
            // Monitor users table for verification changes
            $this->createUserTriggers();
            
            // Monitor transactions for payments
            $this->createTransactionTriggers();
            
        } catch (Exception $e) {
            error_log("Notification Dispatcher Error: " . $e->getMessage());
        }
    }
    
    /**
     * Create triggers for booking events
     */
    private function createBookingTriggers() {
        // Drop existing triggers if they exist
        $this->pdo->exec("DROP TRIGGER IF EXISTS booking_after_insert");
        $this->pdo->exec("DROP TRIGGER IF EXISTS booking_after_update");
        
        // Trigger for NEW bookings (when payment succeeds)
        $this->pdo->exec("
            CREATE TRIGGER booking_after_insert
            AFTER INSERT ON bookings
            FOR EACH ROW
            BEGIN
                IF NEW.booking_status = 'confirmed' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('booking_confirmed', JSON_OBJECT(
                        'booking_id', NEW.id,
                        'user_id', NEW.user_id,
                        'sitter_id', NEW.sitter_id,
                        'service_type', NEW.service_type,
                        'start_date', NEW.start_date,
                        'end_date', NEW.end_date
                    ), NOW());
                END IF;
            END
        ");
        
        // Trigger for UPDATED bookings (status changes)
        $this->pdo->exec("
            CREATE TRIGGER booking_after_update
            AFTER UPDATE ON bookings
            FOR EACH ROW
            BEGIN
                -- Booking confirmed
                IF OLD.booking_status != 'confirmed' AND NEW.booking_status = 'confirmed' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('booking_confirmed', JSON_OBJECT(
                        'booking_id', NEW.id,
                        'user_id', NEW.user_id,
                        'sitter_id', NEW.sitter_id,
                        'service_type', NEW.service_type,
                        'start_date', NEW.start_date
                    ), NOW());
                END IF;
                
                -- Booking cancelled
                IF OLD.booking_status != 'cancelled' AND NEW.booking_status = 'cancelled' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('booking_cancelled', JSON_OBJECT(
                        'booking_id', NEW.id,
                        'user_id', NEW.user_id,
                        'sitter_id', NEW.sitter_id,
                        'service_type', NEW.service_type,
                        'start_date', NEW.start_date
                    ), NOW());
                END IF;
                
                -- Booking completed
                IF OLD.booking_status != 'completed' AND NEW.booking_status = 'completed' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('booking_completed', JSON_OBJECT(
                        'booking_id', NEW.id,
                        'user_id', NEW.user_id,
                        'sitter_id', NEW.sitter_id
                    ), NOW());
                END IF;
            END
        ");
    }
    
    /**
     * Create triggers for message events
     */
    private function createMessageTriggers() {
        $this->pdo->exec("DROP TRIGGER IF EXISTS message_after_insert");
        
        $this->pdo->exec("
            CREATE TRIGGER message_after_insert
            AFTER INSERT ON messages
            FOR EACH ROW
            BEGIN
                INSERT INTO notification_queue (event_type, event_data, created_at)
                VALUES ('new_message', JSON_OBJECT(
                    'message_id', NEW.id,
                    'sender_id', NEW.sender_id,
                    'receiver_id', NEW.receiver_id,
                    'message', SUBSTRING(NEW.message, 1, 100)
                ), NOW());
            END
        ");
    }
    
    /**
     * Create triggers for user verification events
     */
    private function createUserTriggers() {
        $this->pdo->exec("DROP TRIGGER IF EXISTS user_after_insert");
        $this->pdo->exec("DROP TRIGGER IF EXISTS user_after_update");
        
        // Welcome notification for new users
        $this->pdo->exec("
            CREATE TRIGGER user_after_insert
            AFTER INSERT ON users
            FOR EACH ROW
            BEGIN
                IF NEW.is_sitter = 1 THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('welcome_sitter', JSON_OBJECT('user_id', NEW.id), NOW());
                ELSE
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('welcome_owner', JSON_OBJECT('user_id', NEW.id), NOW());
                END IF;
            END
        ");
        
        // Verification status changes
        $this->pdo->exec("
            CREATE TRIGGER user_after_update
            AFTER UPDATE ON users
            FOR EACH ROW
            BEGIN
                -- Verification approved
                IF OLD.profile_verified = 0 AND NEW.profile_verified = 1 THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('verification_approved', JSON_OBJECT('user_id', NEW.id), NOW());
                END IF;
                
                -- Verification rejected
                IF OLD.verification_status != 'rejected' AND NEW.verification_status = 'rejected' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('verification_rejected', JSON_OBJECT('user_id', NEW.id), NOW());
                END IF;
            END
        ");
    }
    
    /**
     * Create triggers for transaction/payment events
     */
    private function createTransactionTriggers() {
        $this->pdo->exec("DROP TRIGGER IF EXISTS transaction_after_insert");
        
        $this->pdo->exec("
            CREATE TRIGGER transaction_after_insert
            AFTER INSERT ON transactions
            FOR EACH ROW
            BEGIN
                -- Payment received
                IF NEW.transaction_type = 'payment' AND NEW.payment_status = 'completed' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('payment_received', JSON_OBJECT(
                        'user_id', NEW.user_id,
                        'booking_id', NEW.booking_id,
                        'amount', NEW.amount
                    ), NOW());
                END IF;
                
                -- Payout processed
                IF NEW.transaction_type = 'payout' AND NEW.payment_status = 'completed' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('payout_processed', JSON_OBJECT(
                        'user_id', NEW.user_id,
                        'amount', NEW.amount
                    ), NOW());
                END IF;
            END
        ");
    }
    
    /**
     * Create notification queue table
     */
    public function createQueueTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notification_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                event_data JSON NOT NULL,
                processed TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                processed_at TIMESTAMP NULL,
                INDEX idx_processed (processed, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
    
    /**
     * Process queued notifications (call this periodically or at end of request)
     */
    public function processQueue() {
        if (!$this->enabled) return;
        
        try {
            // Get unprocessed notifications
            $stmt = $this->pdo->query("
                SELECT * FROM notification_queue 
                WHERE processed = 0 
                ORDER BY created_at ASC 
                LIMIT 50
            ");
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($notifications as $notification) {
                $this->processNotification($notification);
            }
            
        } catch (Exception $e) {
            error_log("Queue Processing Error: " . $e->getMessage());
        }
    }
    
    /**
     * Process individual notification
     */
    private function processNotification($notification) {
        $event_type = $notification['event_type'];
        $event_data = json_decode($notification['event_data'], true);
        
        try {
            switch ($event_type) {
                case 'booking_confirmed':
                    $this->handleBookingConfirmed($event_data);
                    break;
                    
                case 'booking_cancelled':
                    $this->handleBookingCancelled($event_data);
                    break;
                    
                case 'booking_completed':
                    $this->handleBookingCompleted($event_data);
                    break;
                    
                case 'new_message':
                    $this->handleNewMessage($event_data);
                    break;
                    
                case 'welcome_owner':
                case 'welcome_sitter':
                    sendNotification($event_data['user_id'], $event_type);
                    break;
                    
                case 'verification_approved':
                case 'verification_rejected':
                    sendNotification($event_data['user_id'], $event_type);
                    break;
                    
                case 'payment_received':
                    $this->handlePaymentReceived($event_data);
                    break;
                    
                case 'payout_processed':
                    $this->handlePayoutProcessed($event_data);
                    break;
            }
            
            // Mark as processed
            $stmt = $this->pdo->prepare("
                UPDATE notification_queue 
                SET processed = 1, processed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$notification['id']]);
            
        } catch (Exception $e) {
            error_log("Process Notification Error ({$event_type}): " . $e->getMessage());
        }
    }
    
    /**
     * Event handlers
     */
    private function handleBookingConfirmed($data) {
        // Get additional booking details
        $stmt = $this->pdo->prepare("
            SELECT b.*, u1.fullname as owner_name, u2.fullname as sitter_name
            FROM bookings b
            JOIN users u1 ON b.user_id = u1.id
            JOIN users u2 ON b.sitter_id = u2.id
            WHERE b.id = ?
        ");
        $stmt->execute([$data['booking_id']]);
        $booking = $stmt->fetch();
        
        if (!$booking) return;
        
        // Notify owner
        sendNotification($booking['user_id'], 'booking_confirmed', [
            'booking_id' => $booking['id'],
            'service_type' => $booking['service_type'],
            'start_date' => $booking['start_date'],
            'sitter_name' => $booking['sitter_name']
        ]);
        
        // Notify sitter
        sendNotification($booking['sitter_id'], 'booking_request', [
            'booking_id' => $booking['id'],
            'service_type' => $booking['service_type'],
            'start_date' => $booking['start_date'],
            'end_date' => $booking['end_date']
        ]);
    }
    
    private function handleBookingCancelled($data) {
        // Notify both parties
        sendNotification($data['user_id'], 'booking_cancelled', [
            'booking_id' => $data['booking_id'],
            'service_type' => $data['service_type'],
            'start_date' => $data['start_date']
        ]);
        
        sendNotification($data['sitter_id'], 'booking_cancelled', [
            'booking_id' => $data['booking_id'],
            'service_type' => $data['service_type'],
            'start_date' => $data['start_date']
        ]);
    }
    
    private function handleBookingCompleted($data) {
        // Get sitter name for review prompt
        $stmt = $this->pdo->prepare("
            SELECT u.fullname as sitter_name 
            FROM bookings b 
            JOIN users u ON b.sitter_id = u.id 
            WHERE b.id = ?
        ");
        $stmt->execute([$data['booking_id']]);
        $sitter_name = $stmt->fetchColumn();
        
        sendNotification($data['user_id'], 'booking_completed', [
            'booking_id' => $data['booking_id'],
            'sitter_name' => $sitter_name
        ]);
    }
    
    private function handleNewMessage($data) {
        // Get sender name
        $stmt = $this->pdo->prepare("SELECT fullname FROM users WHERE id = ?");
        $stmt->execute([$data['sender_id']]);
        $sender_name = $stmt->fetchColumn();
        
        sendNotification($data['receiver_id'], 'new_message', [
            'sender_id' => $data['sender_id'],
            'sender_name' => $sender_name,
            'message_preview' => $data['message']
        ]);
    }
    
    private function handlePaymentReceived($data) {
        sendNotification($data['user_id'], 'payment_received', [
            'booking_id' => $data['booking_id'],
            'amount' => number_format($data['amount'], 2)
        ]);
    }
    
    private function handlePayoutProcessed($data) {
        sendNotification($data['user_id'], 'payout_processed', [
            'amount' => number_format($data['amount'], 2)
        ]);
    }
}

// Initialize the dispatcher
global $pdo;
if (isset($pdo)) {
    $notificationDispatcher = new NotificationDispatcher($pdo);
    $notificationDispatcher->createQueueTable();
    
    // Register shutdown function to process queue at end of request
    register_shutdown_function(function() use ($notificationDispatcher) {
        $notificationDispatcher->processQueue();
    });
}