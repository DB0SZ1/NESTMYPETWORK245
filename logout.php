<?php
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Start a new session to pass a logout message
session_start();
$_SESSION['success_message'] = "You have been logged out successfully.";

// Redirect to the homepage
header("Location: auth.php");
exit();
