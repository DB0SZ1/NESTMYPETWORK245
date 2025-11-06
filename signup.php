<?php
// Start session only if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'vendor/autoload.php'; // Required for Stripe PHP library
require 'db.php'; // Use our correct PDO connection

// --- Stripe API Key ---
$stripeSecretKey = 'sk_test_51RrMbj3lvfiHaIRcBGGNoiMv9n7pqV6We1iXaQUOWuPic65AHD1rQd56Docq13ik7Adt1JBWGjcjDsbGYjz9pTpb002o9k2sjG'; // Your Secret Key
$stripe = new \Stripe\StripeClient($stripeSecretKey);
// ------------------------

// THE EXTERNAL URL FOR SITTER VERIFICATION
$verification_url = "https://www.hr-platform.co.uk/individual/application-login/?ENgQwM%2FygHlrjiVEwETMmbIwoLxIQ3hnUPCwfPb4aZdxk2Pcxt4LzG1OzM6ljsM%2F%2F68cWOAqqfm3nnOPGh1c597iGbeQDESHZmmCaXx%2ByRuR%2FQ%2F4WUwZuMH6IfHE04UR";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_sitter_signup = isset($_POST['is_sitter']) && $_POST['is_sitter'] == '1';
    
    // Additional fields from signup_sitter.php
    $sitter_type = isset($_POST['sitter_type']) ? trim($_POST['sitter_type']) : null;
    $home_type = isset($_POST['home_type']) ? trim($_POST['home_type']) : null;
    $outdoor_space = isset($_POST['outdoor_space']) ? trim($_POST['outdoor_space']) : null;
    $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;
    $background = isset($_POST['background']) ? trim($_POST['background']) : '';
    $terms = isset($_POST['terms']) ? true : false;

    // --- Validation ---
    if (empty($fullname) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: index.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: index.php");
        exit();
    }

    if ($is_sitter_signup && (empty($sitter_type) || !$terms)) {
        $_SESSION['error_message'] = "Sitter type and terms agreement are required for sitter signup.";
        header("Location: signup_sitter.php");
        exit();
    }

    // --- Start Transaction ---
    try {
        $pdo->beginTransaction(); // Start transaction for atomicity

        // 1. Check if email already exists using PDO
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $_SESSION['error_message'] = "An account with this email already exists.";
            $pdo->rollBack(); // Rollback transaction
            header("Location: index.php");
            exit();
        }

        // 2. Create Stripe Customer
        $stripe_customer_id = null;
        try {
            $customer = $stripe->customers->create([
                'name' => $fullname,
                'email' => $email,
            ]);
            $stripe_customer_id = $customer->id;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Customer Creation Error: " . $e->getMessage());
            $_SESSION['error_message'] = "Could not create payment profile. Please check your details or try again later.";
            $pdo->rollBack(); // Rollback transaction
            header("Location: index.php");
            exit();
        }

        // Ensure we got a customer ID
        if (!$stripe_customer_id) {
            $_SESSION['error_message'] = "Failed to initialize payment profile.";
            $pdo->rollBack(); // Rollback transaction
            header("Location: index.php");
            exit();
        }

        // 3. Hash the password for security
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 4. Determine Sitter Status
        $is_sitter_db = $is_sitter_signup ? 1 : 0;
        $sitter_status = $is_sitter_signup ? 'pending' : 'not_sitter';

        // 5. Insert User into Database (including Stripe ID)
        $sql = "INSERT INTO users (fullname, email, password, is_sitter, sitter_status, stripe_customer_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql);
        $stmt_insert->execute([$fullname, $email, $password_hash, $is_sitter_db, $sitter_status, $stripe_customer_id]);

        // Check if insert was successful
        if ($stmt_insert->rowCount() > 0) {
            $user_id = $pdo->lastInsertId(); // Get the new user ID

            // 6. If sitter signup, insert into sitter_services
            if ($is_sitter_signup) {
                $default_price_per_night = 25.00; // Default price, can be updated later
                $default_headline = "Experienced pet sitter ready to care for your pets!";
                $service_type = $sitter_type === 'Boarder' ? 'boarding' : 'house_sitting';

                $sql_sitter = "INSERT INTO sitter_services (user_id, service_type, price_per_night, headline, home_type, outdoor_space, experience, background) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_sitter = $pdo->prepare($sql_sitter);
                $stmt_sitter->execute([
                    $user_id,
                    $service_type,
                    $default_price_per_night,
                    $default_headline,
                    $home_type,
                    $outdoor_space,
                    $experience,
                    $background
                ]);

                if ($stmt_sitter->rowCount() === 0) {
                    $_SESSION['error_message'] = "Failed to create sitter profile.";
                    $pdo->rollBack();
                    header("Location: signup_sitter.php");
                    exit();
                }
            }

            // Commit the transaction
            $pdo->commit();

            // --- Redirect Logic ---
            if ($is_sitter_signup) {
                // Sitter registered: Log them in and redirect to verification
                $_SESSION['user_id'] = $user_id;
                list($firstname) = explode(' ', $fullname, 2);
                $_SESSION['user_firstname'] = $firstname;
                header("Location: " . $verification_url);
                exit();
            } else {
                // Regular user registered: Show success and redirect to homepage
                $_SESSION['success_message'] = "Registration successful! You can now log in.";
                header("Location: index.php");
                exit();
            }

        } else {
            // Insert failed for some reason
            $_SESSION['error_message'] = "Error during registration. Could not save user data.";
            $pdo->rollBack(); // Rollback transaction
        }

    } catch(PDOException $e) {
        $pdo->rollBack(); // Rollback transaction on DB error
        error_log("Database Error during signup: " . $e->getMessage());
        $_SESSION['error_message'] = "A database error occurred during registration. Please try again later.";
    } catch(Exception $e) {
        // Catch any other unexpected errors
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Rollback transaction
        }
        error_log("General Error during signup: " . $e->getMessage());
        $_SESSION['error_message'] = "An unexpected error occurred. Please try again.";
    }

    // Redirect back to the homepage on any failure after try-catch
    header("Location: index.php");
    exit();
} else {
    // If not a POST request, redirect
    header("Location: index.php");
    exit();
}
?>