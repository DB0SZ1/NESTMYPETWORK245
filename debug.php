<?php
/**
 * Profile Photo Debug Script
 * Run this file to check why profile photos aren't displaying
 */

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    die('Please log in first');
}

$user_id = $_SESSION['user_id'];

echo "<h1>Profile Photo Debug Report</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
    pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style>";

// 1. Check database
echo "<div class='section'>";
echo "<h2>1. Database Check</h2>";
$stmt = $pdo->prepare("SELECT id, fullname, profile_photo_path FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<p class='success'>✓ User found in database</p>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
} else {
    echo "<p class='error'>✗ User not found in database</p>";
}
echo "</div>";

// 2. Check photo path
echo "<div class='section'>";
echo "<h2>2. Photo Path Check</h2>";
$photo_path = trim($user['profile_photo_path'] ?? '');

if (empty($photo_path)) {
    echo "<p class='error'>✗ No photo path in database</p>";
} else {
    echo "<p class='success'>✓ Photo path exists: <code>$photo_path</code></p>";
    
    // 3. Check file existence
    echo "<h3>File Existence Checks:</h3>";
    
    // Try direct path
    echo "<p><strong>Direct path:</strong> <code>$photo_path</code> - ";
    if (file_exists($photo_path)) {
        echo "<span class='success'>EXISTS</span></p>";
    } else {
        echo "<span class='error'>NOT FOUND</span></p>";
    }
    
    // Try with __DIR__
    $full_path = __DIR__ . '/' . $photo_path;
    echo "<p><strong>Full path:</strong> <code>$full_path</code> - ";
    if (file_exists($full_path)) {
        echo "<span class='success'>EXISTS</span></p>";
    } else {
        echo "<span class='error'>NOT FOUND</span></p>";
    }
    
    // Try with DOCUMENT_ROOT
    $doc_root_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $photo_path;
    echo "<p><strong>Document root path:</strong> <code>$doc_root_path</code> - ";
    if (file_exists($doc_root_path)) {
        echo "<span class='success'>EXISTS</span></p>";
    } else {
        echo "<span class='error'>NOT FOUND</span></p>";
    }
}
echo "</div>";

// 4. Check file permissions
echo "<div class='section'>";
echo "<h2>3. File Permissions Check</h2>";
if (!empty($photo_path)) {
    $paths_to_check = [
        'Direct' => $photo_path,
        'Full' => __DIR__ . '/' . $photo_path,
        'DocRoot' => $_SERVER['DOCUMENT_ROOT'] . '/' . $photo_path
    ];
    
    foreach ($paths_to_check as $label => $path) {
        if (file_exists($path)) {
            $perms = fileperms($path);
            $perms_string = substr(sprintf('%o', $perms), -4);
            echo "<p><strong>$label path permissions:</strong> $perms_string - ";
            
            if (is_readable($path)) {
                echo "<span class='success'>READABLE</span></p>";
            } else {
                echo "<span class='error'>NOT READABLE</span></p>";
            }
            
            // Show file info
            echo "<ul>";
            echo "<li>Size: " . filesize($path) . " bytes</li>";
            echo "<li>Owner: " . fileowner($path) . "</li>";
            echo "<li>Modified: " . date('Y-m-d H:i:s', filemtime($path)) . "</li>";
            echo "</ul>";
        }
    }
}
echo "</div>";

// 5. Check directory structure
echo "<div class='section'>";
echo "<h2>4. Directory Structure</h2>";
echo "<p><strong>Current directory:</strong> <code>" . __DIR__ . "</code></p>";
echo "<p><strong>Document root:</strong> <code>" . $_SERVER['DOCUMENT_ROOT'] . "</code></p>";

// Check uploads directory
$uploads_dir = __DIR__ . '/uploads/profiles';
echo "<p><strong>Uploads directory:</strong> <code>$uploads_dir</code> - ";
if (is_dir($uploads_dir)) {
    echo "<span class='success'>EXISTS</span></p>";
    
    // List files
    echo "<h4>Files in uploads/profiles/:</h4>";
    $files = scandir($uploads_dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $file_path = $uploads_dir . '/' . $file;
            $size = filesize($file_path);
            echo "<li><code>$file</code> ($size bytes)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<span class='error'>NOT FOUND</span></p>";
}
echo "</div>";

// 6. Test image URL
echo "<div class='section'>";
echo "<h2>5. Image Display Test</h2>";
if (!empty($photo_path)) {
    echo "<p>Attempting to display image:</p>";
    echo "<img src='$photo_path?v=" . time() . "' alt='Test' style='max-width: 200px; border: 2px solid #ccc;' 
          onerror=\"this.parentElement.innerHTML += '<p class=error>✗ Image failed to load</p>';\">";
    echo "<p class='success'>If you see an image above, the path is correct!</p>";
}
echo "</div>";

// 7. Recommended solutions
echo "<div class='section'>";
echo "<h2>6. Recommended Solutions</h2>";
echo "<ol>";

if (empty($photo_path)) {
    echo "<li>Upload a profile photo through the profile page</li>";
} elseif (!file_exists($photo_path) && !file_exists(__DIR__ . '/' . $photo_path)) {
    echo "<li class='error'>The file path in database doesn't match any file on disk</li>";
    echo "<li>Try re-uploading your profile photo</li>";
    echo "<li>Check that the uploads directory exists and has correct permissions</li>";
    echo "<li><strong>Quick fix:</strong> Run this command: <code>mkdir -p uploads/profiles && chmod 755 uploads/profiles</code></li>";
} else {
    echo "<li class='success'>Everything looks good! The issue might be in the PHP conditional logic.</li>";
    echo "<li>Remove the <code>file_exists()</code> check from dashboard.php</li>";
    echo "<li>Just check if <code>\$user['profile_photo_path']</code> is not empty</li>";
}

echo "</ol>";
echo "</div>";

// 8. Code snippet
echo "<div class='section'>";
echo "<h2>7. Suggested Code Fix</h2>";
echo "<p>Use this code in dashboard.php:</p>";
echo "<pre>" . htmlspecialchars('
<!-- Profile Card -->
<div class="profile-card">
    <div class="profile-avatar">
        <?php if (!empty($user[\'profile_photo_path\'])): ?>
            <img src="<?php echo htmlspecialchars($user[\'profile_photo_path\']); ?>?v=<?php echo $cache_bust; ?>" 
                 alt="<?php echo $display_name; ?>"
                 onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">
            <div class="profile-avatar-placeholder" style="display: none;">
                <div class="avatar-initials"><?php echo $initials; ?></div>
            </div>
        <?php else: ?>
            <div class="profile-avatar-placeholder">
                <div class="avatar-initials"><?php echo $initials; ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>
') . "</pre>";
echo "</div>";

echo "<hr>";
echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";
?>