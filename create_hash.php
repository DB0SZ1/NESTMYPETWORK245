<?php
// --- SET YOUR NEW ADMIN PASSWORD HERE ---
$my_password = 'password123';
// ----------------------------------------

// Generate a secure hash
$hashed_password = password_hash($my_password, PASSWORD_DEFAULT);

// Display the hash
echo "Your new password is: " . $my_password . "<br>";
echo "Copy this hash into your SQL query:<br><br>";
echo '<textarea style="width: 100%; height: 60px; font-size: 1.2rem;, padding: 10px;">';
echo $hashed_password;
echo '</textarea>';

?>