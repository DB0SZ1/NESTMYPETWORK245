<?php
/**
 * NestMyPet Notification Dispatcher - AUTO NOTIFICATION SYSTEM
 * Automatically detects ALL database events and sends notifications
 * 
 * SETUP INSTRUCTIONS:
 * 1. Make sure this file is in the same folder as db.php
 * 2. db.php should have: require_once __DIR__ . '/notification_dispatcher.php';
 * 3. Visit: yourdomain.com/notification_dispatcher.php?install=1 (ONE TIME ONLY)
 * 4. Setup cron job: * * * * * php /path/to/cron_process_notifications.php
 * 
 * THAT'S IT! No other changes needed anywhere!
 */

// Only load notifications.php if not already loaded
if (!function_exists('sendNotification')) {
    require_once __DIR__ . '/notifications.php';
}

class NotificationDispatcher {
    private $pdo;
    private $enabled = true;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Install notification system (run once via URL)
     */
    public function install() {
        echo "<h2>🚀 Installing NestMyPet Notification System...</h2>";
        
        try {
            // Step 1: Create queue table
            $this->createQueueTable();
            echo "✅ Notification queue table created<br>";
            
            // Step 2: Create notifications table (if not exists)
            $this->createNotificationsTable();
            echo "✅ Notifications table ready<br>";
            
            // Step 3: Install all triggers
            $this->installAllTriggers();
            echo "✅ Database triggers installed<br>";
            
            echo "<br><h3 style='color: green;'>✅ Installation Complete!</h3>";
            echo "<p><strong>What happens now:</strong></p>";
            echo "<ul>";
            echo "<li>✉️ New user signups → Automatic welcome emails</li>";
            echo "<li>💬 New messages → Instant email notifications</li>";
            echo "<li>📅 Bookings confirmed/cancelled → Both parties notified</li>";
            echo "<li>💳 Payments received → Email receipts sent</li>";
            echo "<li>✔️ Profile verifications → Status emails sent</li>";
            echo "</ul>";
            echo "<p><strong>Next step:</strong> Setup cron job to run <code>cron_process_notifications.php</code> every minute</p>";
            echo "<p>Cron command: <code>* * * * * php " . __DIR__ . "/cron_process_notifications.php</code></p>";
            
        } catch (Exception $e) {
            echo "❌ <strong>Error:</strong> " . $e->getMessage();
            error_log("Notification Install Error: " . $e->getMessage());
        }
    }
    
    /**
     * Create notification queue table
     */
    private function createQueueTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notification_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                event_data JSON NOT NULL,
                processed TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                processed_at TIMESTAMP NULL,
                error_message TEXT NULL,
                retry_count INT DEFAULT 0,
                INDEX idx_processed (processed, created_at),
                INDEX idx_event_type (event_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
    
    /**
     * Create notifications table (for in-app notifications)
     */
    private function createNotificationsTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                data JSON,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_read (user_id, is_read),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
    
    /**
     * Install all database triggers
     */
    private function installAllTriggers() {
        // Remove old triggers first
        $this->dropOldTriggers();
        
        // Install new triggers
        $this->installUserTriggers();
        $this->installBookingTriggers();
        $this->installMessageTriggers();
        $this->installTransactionTriggers();
    }
    
    /**
     * Drop old triggers if they exist
     */
    private function dropOldTriggers() {
        $triggers = [
            'notify_new_user',
            'notify_user_verification',
            'notify_booking_insert',
            'notify_booking_update',
            'notify_new_message',
            'notify_transaction'
        ];
        
        foreach ($triggers as $trigger) {
            try {
                $this->pdo->exec("DROP TRIGGER IF EXISTS $trigger");
            } catch (Exception $e) {
                // Ignore if trigger doesn't exist
            }
        }
    }
    
    /**
     * USER TRIGGERS - Signup & Verification
     */
    private function installUserTriggers() {
        // NEW USER SIGNUP - Send welcome email
        $this->pdo->exec("
            CREATE TRIGGER notify_new_user
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
        
        // VERIFICATION STATUS CHANGES
        $this->pdo->exec("
            CREATE TRIGGER notify_user_verification
            AFTER UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF OLD.profile_verified = 0 AND NEW.profile_verified = 1 THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('verification_approved', JSON_OBJECT('user_id', NEW.id), NOW());
                END IF;
                
                IF OLD.verification_status != 'rejected' AND NEW.verification_status = 'rejected' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('verification_rejected', JSON_OBJECT('user_id', NEW.id), NOW());
                END IF;
            END
        ");
    }
    
    /**
     * BOOKING TRIGGERS - Confirm, Cancel, Complete
     */
    private function installBookingTriggers() {
        // NEW BOOKING - When first created
        $this->pdo->exec("
            CREATE TRIGGER notify_booking_insert
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
        
        // BOOKING STATUS CHANGES
        $this->pdo->exec("
            CREATE TRIGGER notify_booking_update
            AFTER UPDATE ON bookings
            FOR EACH ROW
            BEGIN
                -- Payment succeeded -> Booking confirmed
                IF OLD.booking_status = 'pending_payment' AND NEW.booking_status = 'confirmed' THEN
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
     * MESSAGE TRIGGERS - New messages
     */
    private function installMessageTriggers() {
        $this->pdo->exec("
            CREATE TRIGGER notify_new_message
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
     * TRANSACTION TRIGGERS - Payments
     */
    private function installTransactionTriggers() {
        $this->pdo->exec("
            CREATE TRIGGER notify_transaction
            AFTER INSERT ON transactions
            FOR EACH ROW
            BEGIN
                -- Payment succeeded (based on your schema)
                IF NEW.payment_status = 'succeeded' THEN
                    INSERT INTO notification_queue (event_type, event_data, created_at)
                    VALUES ('payment_received', JSON_OBJECT(
                        'user_id', NEW.user_id,
                        'booking_id', NEW.booking_id,
                        'amount', NEW.amount_paid
                    ), NOW());
                END IF;
            END
        ");
    }
    
    /**
     * Process notification queue (called by cron or shutdown function)
     */
    public function processQueue($limit = 50) {
        if (!$this->enabled) return 0;
        
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM notification_queue 
                WHERE processed = 0 
                AND retry_count < 3
                ORDER BY created_at ASC 
                LIMIT $limit
            ");
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $processed = 0;
            
            foreach ($notifications as $notification) {
                if ($this->processNotification($notification)) {
                    $processed++;
                }
            }
            
            return $processed;
            
        } catch (Exception $e) {
            error_log("Queue Processing Error: " . $e->getMessage());
            return 0;
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
            }
            
            // Mark as processed
            $stmt = $this->pdo->prepare("
                UPDATE notification_queue 
                SET processed = 1, processed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$notification['id']]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Notification Error ({$event_type}): " . $e->getMessage());
            
            // Increment retry count
            $stmt = $this->pdo->prepare("
                UPDATE notification_queue 
                SET retry_count = retry_count + 1,
                    error_message = ?
                WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $notification['id']]);
            
            return false;
        }
    }
    
    /**
     * EVENT HANDLERS - Get full data and send notifications
     */
    private function handleBookingConfirmed($data) {
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
        
        // Notify pet owner
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
}

// ============================================
// AUTO-INITIALIZATION
// ============================================

// Installation endpoint - visit yourdomain.com/notification_dispatcher.php?install=1
if (isset($_GET['install']) && $_GET['install'] == '1') {
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }
    $dispatcher = new NotificationDispatcher($pdo);
    $dispatcher->install();
    exit;
}

// Auto-initialize when included from db.php
if (isset($pdo) && !isset($GLOBALS['notificationDispatcher'])) {
    $GLOBALS['notificationDispatcher'] = new NotificationDispatcher($pdo);
    
    // Process a few notifications at end of each request (real-time processing)
    register_shutdown_function(function() {
        if (isset($GLOBALS['notificationDispatcher'])) {
            $GLOBALS['notificationDispatcher']->processQueue(10);
        }
    });
}