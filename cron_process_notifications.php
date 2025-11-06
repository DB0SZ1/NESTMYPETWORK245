<?php
/**
 * Cron Job for Processing Notification Queue
 * 
 * Run this every 1-5 minutes via cron:
 * * * * * * php /path/to/cron_process_notifications.php
 * 
 * Or set up as a background task in your hosting panel
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notification_dispatcher.php';

// Prevent browser access
if (php_sapi_name() !== 'cli' && !isset($_GET['manual_trigger'])) {
    die("This script can only be run from command line or with ?manual_trigger parameter");
}

echo "Starting notification processing...\n";

try {
    global $pdo, $notificationDispatcher;
    
    if (!isset($notificationDispatcher)) {
        $notificationDispatcher = new NotificationDispatcher($pdo);
    }
    
    // Process queue
    $notificationDispatcher->processQueue();
    
    echo "Notification processing completed successfully.\n";
    
    // Optional: Clean up old processed notifications (older than 30 days)
    $pdo->exec("
        DELETE FROM notification_queue 
        WHERE processed = 1 
        AND processed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    
    echo "Old notifications cleaned up.\n";
    
} catch (Exception $e) {
    error_log("Cron Notification Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}