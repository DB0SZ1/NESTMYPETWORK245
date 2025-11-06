<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conversations = [];
$selected_conversation = null;
$messages = [];

try {
    // Handle sending a new message
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['receiver_id'])) {
        $receiver_id = (int)$_POST['receiver_id'];
        $message = trim($_POST['message']);
        if (!empty($message)) {
            $stmt_send = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
            if ($stmt_send->execute([$user_id, $receiver_id, $message])) {
                error_log("Message sent successfully from $user_id to $receiver_id");
                // Redirect to refresh the page with the updated conversation
                header("Location: messages.php?conversation_with=$receiver_id");
                exit();
            } else {
                error_log("Failed to send message: " . print_r($stmt_send->errorInfo(), true));
            }
        }
    }

    // Fetch all conversations (unique sender/receiver pairs)
    $sql_conversations = "
        SELECT u.id, u.fullname, u.profile_photo_path,
               m.sent_at, m.message, m.is_read,
               (SELECT COUNT(*) FROM messages m2 WHERE m2.receiver_id = ? AND m2.is_read = 0 AND m2.sender_id = u.id) as unread_count
        FROM messages m
        JOIN users u ON (u.id = m.sender_id OR u.id = m.receiver_id) AND u.id != ?
        WHERE m.sender_id = ? OR m.receiver_id = ?
        GROUP BY u.id
        ORDER BY m.sent_at DESC
    ";
    $stmt_conversations = $pdo->prepare($sql_conversations);
    $stmt_conversations->execute([$user_id, $user_id, $user_id, $user_id]);
    $conversations = $stmt_conversations->fetchAll(PDO::FETCH_ASSOC);

    // Fetch messages for a selected conversation (if any)
    if (isset($_GET['conversation_with'])) {
        $conversation_with = (int)$_GET['conversation_with'];
        $sql_messages = "
            SELECT m.*, u.fullname as sender_name, u.profile_photo_path as sender_photo
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.sent_at ASC
        ";
        $stmt_messages = $pdo->prepare($sql_messages);
        $stmt_messages->execute([$user_id, $conversation_with, $conversation_with, $user_id]);
        $messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

        // Mark messages as read
        $stmt_update_read = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0");
        $stmt_update_read->execute([$user_id, $conversation_with]);

        // Fetch selected conversation user details
        $stmt_user = $pdo->prepare("SELECT fullname, profile_photo_path FROM users WHERE id = ?");
        $stmt_user->execute([$conversation_with]);
        $selected_conversation = $stmt_user->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error in messages.php: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred while fetching messages.";
    header('Location: messages.php');
    exit();
}

$pageTitle = "Messages";
include 'header.php';
?>
<link rel="stylesheet" href="messages.css">

<main class="messaging-page">
    <div class="messaging-container">
        <div class="messaging-layout">
            <!-- Conversations List -->
            <aside class="conversations-sidebar">
                <div class="sidebar-header">
                    <h2>Chats</h2>
                    <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle conversations"><i class="fas fa-bars"></i></button>
                </div>
                <?php if (empty($conversations)): ?>
                    <p class="no-conversations">No chats yet. Start one from a sitter's profile!</p>
                <?php else: ?>
                    <ul class="conversation-list">
                        <?php foreach ($conversations as $conv): ?>
                            <li class="conversation-item <?php echo (isset($_GET['conversation_with']) && $_GET['conversation_with'] == $conv['id']) ? 'active' : ''; ?>">
                                <a href="messages.php?conversation_with=<?php echo $conv['id']; ?>">
                                    <div class="conversation-avatar">
                                        <?php if ($conv['profile_photo_path'] && file_exists($conv['profile_photo_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($conv['profile_photo_path']); ?>" alt="<?php echo htmlspecialchars($conv['fullname']); ?>">
                                        <?php else: ?>
                                            <div class="profile-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-info">
                                        <h3><?php echo htmlspecialchars($conv['fullname']); ?></h3>
                                        <p><?php echo htmlspecialchars(substr($conv['message'], 0, 30)) . (strlen($conv['message']) > 30 ? '...' : ''); ?></p>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="conversation-time"><?php echo date('H:i', strtotime($conv['sent_at'])); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>

            <!-- Messages Area -->
            <section class="messages-main">
                <?php if ($selected_conversation): ?>
                    <div class="conversation-header">
                        <button class="mobile-back-btn" id="mobile-back-btn"><i class="fas fa-arrow-left"></i></button>
                        <div class="conversation-header-info">
                            <div class="conversation-avatar">
                                <?php if ($selected_conversation['profile_photo_path'] && file_exists($selected_conversation['profile_photo_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($selected_conversation['profile_photo_path']); ?>" alt="<?php echo htmlspecialchars($selected_conversation['fullname']); ?>">
                                <?php else: ?>
                                    <div class="profile-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($selected_conversation['fullname']); ?></h3>
                        </div>
                    </div>
                    <div class="messages-container">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <p><?php echo htmlspecialchars($msg['message']); ?></p>
                                    <span class="message-time"><?php echo date('H:i, d M', strtotime($msg['sent_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form action="messages.php?conversation_with=<?php echo $conversation_with; ?>" method="POST" class="message-form">
                        <input type="hidden" name="receiver_id" value="<?php echo $conversation_with; ?>">
                        <textarea name="message" placeholder="Type a message..." required></textarea>
                        <button type="submit" class="btn btn-send"><i class="fas fa-paper-plane"></i></button>
                    </form>
                <?php else: ?>
                    <div class="no-conversation">
                        <p>Select a chat to start messaging.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<script>
    // Toggle sidebar on mobile
    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        document.querySelector('.conversations-sidebar').classList.toggle('active');
    });

    // Back button for mobile chat view
    document.getElementById('mobile-back-btn')?.addEventListener('click', () => {
        document.querySelector('.conversations-sidebar').classList.add('active');
        document.querySelector('.messages-main').classList.remove('active');
    });

    // Auto-scroll to bottom of messages
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
</script>