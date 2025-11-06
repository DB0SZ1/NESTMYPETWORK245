<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$user_id = $_SESSION['user_id'];

// Fetch the CURRENT user's data (user 20, not user 17)
$stmt = $pdo->prepare("SELECT profile_photo_path, cover_photo_path FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

echo "<h2>Current User: " . $user_id . "</h2>";
echo "<h3>Profile Photo Path from DB:</h3>";
echo "<code>" . htmlspecialchars($user['profile_photo_path'] ?? 'NULL') . "</code><br><br>";

if (!empty($user['profile_photo_path'])) {
    echo "<h3>Testing Profile Photo URLs:</h3>";
    
    // Test 1: /nestpet/ prefix
    $url1 = "/nestpet/" . $user['profile_photo_path'];
    echo "<p><strong>Test 1:</strong> <code>" . htmlspecialchars($url1) . "</code></p>";
    echo "<img src='" . htmlspecialchars($url1) . "' style='max-width:200px;border:3px solid red;' onerror=\"this.style.display='none'; this.nextElementSibling.style.display='block';\">";
    echo "<p style='display:none;color:red;'>❌ Failed to load</p><br>";
    
    // Test 2: Relative path
    $url2 = $user['profile_photo_path'];
    echo "<p><strong>Test 2:</strong> <code>" . htmlspecialchars($url2) . "</code></p>";
    echo "<img src='" . htmlspecialchars($url2) . "' style='max-width:200px;border:3px solid blue;' onerror=\"this.style.display='none'; this.nextElementSibling.style.display='block';\">";
    echo "<p style='display:none;color:red;'>❌ Failed to load</p><br>";
    
    // Test 3: Full path from root
    $url3 = "/" . $user['profile_photo_path'];
    echo "<p><strong>Test 3:</strong> <code>" . htmlspecialchars($url3) . "</code></p>";
    echo "<img src='" . htmlspecialchars($url3) . "' style='max-width:200px;border:3px solid green;' onerror=\"this.style.display='none'; this.nextElementSibling.style.display='block';\">";
    echo "<p style='display:none;color:red;'>❌ Failed to load</p><br>";
}

echo "<h3>Cover Photo Path from DB:</h3>";
echo "<code>" . htmlspecialchars($user['cover_photo_path'] ?? 'NULL') . "</code><br><br>";

if (!empty($user['cover_photo_path'])) {
    echo "<h3>Testing Cover Photo URLs:</h3>";
    
    // Test 1: /nestpet/ prefix
    $url1 = "/nestpet/" . $user['cover_photo_path'];
    echo "<p><strong>Test 1:</strong> <code>" . htmlspecialchars($url1) . "</code></p>";
    echo "<img src='" . htmlspecialchars($url1) . "' style='max-width:300px;border:3px solid red;' onerror=\"this.style.display='none'; this.nextElementSibling.style.display='block';\">";
    echo "<p style='display:none;color:red;'>❌ Failed to load</p><br>";
    
    // Test 2: Relative path
    $url2 = $user['cover_photo_path'];
    echo "<p><strong>Test 2:</strong> <code>" . htmlspecialchars($url2) . "</code></p>";
    echo "<img src='" . htmlspecialchars($url2) . "' style='max-width:300px;border:3px solid blue;' onerror=\"this.style.display='none'; this.nextElementSibling.style.display='block';\">";
    echo "<p style='display:none;color:red;'>❌ Failed to load</p><br>";
}

// Check actual file existence
echo "<h3>File System Check:</h3>";
$base_path = __DIR__ . '/' . $user['profile_photo_path'];
echo "<p><strong>Looking for file at:</strong> <code>" . htmlspecialchars($base_path) . "</code></p>";
echo "<p><strong>File exists:</strong> " . (file_exists($base_path) ? '✅ YES' : '❌ NO') . "</p>";

if (!empty($user['cover_photo_path'])) {
    $cover_base_path = __DIR__ . '/' . $user['cover_photo_path'];
    echo "<p><strong>Looking for cover at:</strong> <code>" . htmlspecialchars($cover_base_path) . "</code></p>";
    echo "<p><strong>File exists:</strong> " . (file_exists($cover_base_path) ? '✅ YES' : '❌ NO') . "</p>";
}
?>