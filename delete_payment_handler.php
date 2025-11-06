<?php
/**
 * IMAGE PATH DIAGNOSTIC SCRIPT
 * Save this as: debug_images.php
 * Access it in browser: http://localhost/nestpet/debug_images.php
 */

session_start();
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please log in first. <a href="index.php">Go to homepage</a>');
}

$user_id = $_SESSION['user_id'];

echo "<h1>Image Path Diagnostics</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .section { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #4CAF50; color: white; }
    img { max-width: 200px; border: 2px solid #ddd; }
</style>";

try {
    // Fetch user data
    $stmt = $pdo->prepare("
        SELECT id, fullname, profile_photo_path, cover_photo_path 
        FROM users WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die('User not found!');
    }
    
    echo "<div class='section'>";
    echo "<h2>1. User Information</h2>";
    echo "<strong>User ID:</strong> " . $user['id'] . "<br>";
    echo "<strong>Name:</strong> " . htmlspecialchars($user['fullname']) . "<br>";
    echo "</div>";
    
    // Check profile photo
    echo "<div class='section'>";
    echo "<h2>2. Profile Photo Analysis</h2>";
    
    $profilePath = $user['profile_photo_path'];
    echo "<strong>Database Value:</strong> <code>" . ($profilePath ? htmlspecialchars($profilePath) : 'NULL') . "</code><br><br>";
    
    if ($profilePath) {
        echo "<table>";
        echo "<tr><th>Test</th><th>Result</th></tr>";
        
        // Test 1: Check if path is empty
        echo "<tr><td>Is Empty?</td><td>" . (empty($profilePath) ? '<span class="error">YES (PROBLEM)</span>' : '<span class="success">NO (GOOD)</span>') . "</td></tr>";
        
        // Test 2: Path structure
        echo "<tr><td>Starts with 'uploads/'?</td><td>" . (strpos($profilePath, 'uploads/') === 0 ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . "</td></tr>";
        echo "<tr><td>Starts with '/'?</td><td>" . (strpos($profilePath, '/') === 0 ? '<span class="info">YES</span>' : '<span class="success">NO (GOOD)</span>') . "</td></tr>";
        echo "<tr><td>Contains 'nestpet/'?</td><td>" . (strpos($profilePath, 'nestpet/') !== false ? '<span class="info">YES</span>' : '<span class="success">NO (GOOD)</span>') . "</td></tr>";
        
        // Test 3: File existence checks
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        
        // Method 1: Direct path
        $testPath1 = $docRoot . '/' . ltrim($profilePath, '/');
        $exists1 = file_exists($testPath1);
        echo "<tr><td>File exists (Method 1)?</td><td>" . ($exists1 ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . "</td></tr>";
        echo "<tr><td>Path tested (Method 1):</td><td><code>" . htmlspecialchars($testPath1) . "</code></td></tr>";
        
        // Method 2: With BASE_PATH
        $testPath2 = $docRoot . BASE_PATH . ltrim($profilePath, '/');
        $exists2 = file_exists($testPath2);
        echo "<tr><td>File exists (Method 2)?</td><td>" . ($exists2 ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . "</td></tr>";
        echo "<tr><td>Path tested (Method 2):</td><td><code>" . htmlspecialchars($testPath2) . "</code></td></tr>";
        
        // Method 3: Remove BASE_PATH from path if it exists
        $cleanPath = str_replace('nestpet/', '', $profilePath);
        $testPath3 = $docRoot . BASE_PATH . ltrim($cleanPath, '/');
        $exists3 = file_exists($testPath3);
        echo "<tr><td>File exists (Method 3)?</td><td>" . ($exists3 ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . "</td></tr>";
        echo "<tr><td>Path tested (Method 3):</td><td><code>" . htmlspecialchars($testPath3) . "</code></td></tr>";
        
        echo "</table>";
        
        // Test 4: URL generation
        echo "<h3>URL Generation Tests:</h3>";
        echo "<table>";
        echo "<tr><th>Method</th><th>Generated URL</th><th>Preview</th></tr>";
        
        $url1 = htmlspecialchars($profilePath);
        echo "<tr><td>Direct (htmlspecialchars)</td><td><code>" . $url1 . "</code></td>";
        echo "<td><img src='" . $url1 . "' onerror=\"this.parentElement.innerHTML='<span class=error>FAILED</span>'\" onload=\"this.parentElement.innerHTML='<span class=success>SUCCESS</span> <br><img src=" . $url1 . " style=max-width:100px>'\"></td></tr>";
        
        $url2 = BASE_PATH . htmlspecialchars($profilePath);
        echo "<tr><td>BASE_PATH + path</td><td><code>" . $url2 . "</code></td>";
        echo "<td><img src='" . $url2 . "' onerror=\"this.parentElement.innerHTML='<span class=error>FAILED</span>'\" onload=\"this.parentElement.innerHTML='<span class=success>SUCCESS</span> <br><img src=" . $url2 . " style=max-width:100px>'\"></td></tr>";
        
        $url3 = BASE_PATH . str_replace('nestpet/', '', $profilePath);
        echo "<tr><td>BASE_PATH + cleaned path</td><td><code>" . $url3 . "</code></td>";
        echo "<td><img src='" . $url3 . "' onerror=\"this.parentElement.innerHTML='<span class=error>FAILED</span>'\" onload=\"this.parentElement.innerHTML='<span class=success>SUCCESS</span> <br><img src=" . $url3 . " style=max-width:100px>'\"></td></tr>";
        
        $url4 = '/' . ltrim($profilePath, '/');
        echo "<tr><td>Root + path</td><td><code>" . $url4 . "</code></td>";
        echo "<td><img src='" . $url4 . "' onerror=\"this.parentElement.innerHTML='<span class=error>FAILED</span>'\" onload=\"this.parentElement.innerHTML='<span class=success>SUCCESS</span> <br><img src=" . $url4 . " style=max-width:100px>'\"></td></tr>";
        
        echo "</table>";
        
    } else {
        echo "<span class='error'>No profile photo path in database</span>";
    }
    echo "</div>";
    
    // Check cover photo
    echo "<div class='section'>";
    echo "<h2>3. Cover Photo Analysis</h2>";
    
    $coverPath = $user['cover_photo_path'];
    echo "<strong>Database Value:</strong> <code>" . ($coverPath ? htmlspecialchars($coverPath) : 'NULL') . "</code><br><br>";
    
    if ($coverPath) {
        echo "<table>";
        echo "<tr><th>Method</th><th>Generated URL</th><th>Preview</th></tr>";
        
        $url1 = htmlspecialchars($coverPath);
        echo "<tr><td>Direct</td><td><code>" . $url1 . "</code></td>";
        echo "<td><img src='" . $url1 . "' onerror=\"this.parentElement.innerHTML='<span class=error>FAILED</span>'\" onload=\"this.parentElement.innerHTML='<span class=success>SUCCESS</span> <br><img src=" . $url1 . " style=max-width:150px>'\"></td></tr>";
        
        $url2 = BASE_PATH . htmlspecialchars($coverPath);
        echo "<tr><td>BASE_PATH + path</td><td><code>" . $url2 . "</code></td>";
        echo "<td><img src='" . $url2 . "' onerror=\"this.parentElement.innerHTML='<span class=error>FAILED</span>'\" onload=\"this.parentElement.innerHTML='<span class=success>SUCCESS</span> <br><img src=" . $url2 . " style=max-width:150px>'\"></td></tr>";
        
        echo "</table>";
    } else {
        echo "<span class='info'>No cover photo uploaded yet</span>";
    }
    echo "</div>";
    
    // Environment info
    echo "<div class='section'>";
    echo "<h2>4. Environment Information</h2>";
    echo "<table>";
    echo "<tr><th>Variable</th><th>Value</th></tr>";
    echo "<tr><td>BASE_PATH</td><td><code>" . BASE_PATH . "</code></td></tr>";
    echo "<tr><td>DOCUMENT_ROOT</td><td><code>" . $_SERVER['DOCUMENT_ROOT'] . "</code></td></tr>";
    echo "<tr><td>SERVER_NAME</td><td><code>" . $_SERVER['SERVER_NAME'] . "</code></td></tr>";
    echo "<tr><td>Is Localhost?</td><td>" . (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) ? 'YES' : 'NO') . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // Check upload directory
    echo "<div class='section'>";
    echo "<h2>5. Upload Directory Check</h2>";
    
    $uploadDirs = [
        'uploads/',
        'uploads/profiles/',
        'uploads/covers/',
        'uploads/albums/',
        'nestpet/uploads/',
        'nestpet/uploads/profiles/',
    ];
    
    echo "<table>";
    echo "<tr><th>Directory Path</th><th>Exists?</th><th>Writable?</th></tr>";
    
    foreach ($uploadDirs as $dir) {
        $fullPath = $docRoot . '/' . ltrim($dir, '/');
        $exists = is_dir($fullPath);
        $writable = $exists && is_writable($fullPath);
        
        echo "<tr>";
        echo "<td><code>" . htmlspecialchars($fullPath) . "</code></td>";
        echo "<td>" . ($exists ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . "</td>";
        echo "<td>" . ($writable ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='section'>";
    echo "<span class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</span>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Look for the row with <span class='success'>SUCCESS</span> in the 'URL Generation Tests' section</li>";
echo "<li>Use that exact method in your dashboard.php and profile.php</li>";
echo "<li>If all tests FAIL, check if the file actually exists in the 'File exists' tests</li>";
echo "<li>If file doesn't exist anywhere, you need to re-upload the image</li>";
echo "</ol>";
echo "</div>";

echo "<br><a href='dashboard.php'>← Back to Dashboard</a>";
?>