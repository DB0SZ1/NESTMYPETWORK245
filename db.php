<?php
// --- Database Connection --
// These are the standard details for a local XAMPP server.
$host = '127.0.0.1'; // or 'localhost'
$dbname = 'nestpet';      // The database you created in Step 1
$user = 'root';        // Default XAMPP username
$pass = '';           // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // This line creates the actual database connection
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, stop everything and show an error.
    // This helps with debugging.
    error_log($e->getMessage()); // Log the actual error for you to see
    exit('Failed to connect to the database. Please check your credentials in db.php and ensure the database exists.');
}

