<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'vendor/autoload.php'; // Required for Stripe PHP library
require 'db.php';

// --- Stripe API Keys ---
$stripeSecretKey = 'sk_test_51RrMbj3lvfiHaIRcBGGNoiMv9n7pqV6We1iXaQUOWuPic65AHD1rQd56Docq13ik7Adt1JBWGjcjDsbGYjz9pTpb002o9k2sjG'; // Your Secret Key
$stripePublicKey = 'pk_test_51RrMbj3lvfiHaIRcBUwz76PzM3B9ELVipttrdi0hfhhZ67Gg9i0NQqOVZ0R5AdSwjcrRJGPbdXxk4gFlVNEexc5p00EEPuclee'; // Your Publishable Key
// ------------------------

\Stripe\Stripe::setApiKey($stripeSecretKey);

// --- Check if user is logged in ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to make a booking.";
    header('Location: index.php'); // Redirect to login/home
    exit();
}
$user_id = $_SESSION['user_id'];

// --- Validate Request Method ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: search.php'); // Redirect if not a POST request
    exit();
}

// --- Get Form Data ---
$sitter_id = $_POST['sitter_id'] ?? null;
$service_type = $_POST['service_type'] ?? null;
$price_per_night = $_POST['price_per_night'] ?? null;
$start_date_str = $_POST['start_date'] ?? null;
$end_date_str = $_POST['end_date'] ?? null;
$num_pets = $_POST['num_pets'] ?? 1; // Default to 1 pet if not provided

// --- Basic Validation ---
if (!$sitter_id || !$service_type || !$price_per_night || !$start_date_str || !$end_date_str) {
    $_SESSION['error_message'] = "Missing booking details. Please fill out the form completely.";
    header('Location: sitter_profile.php?id=' . $sitter_id);
    exit();
}

// --- Date Validation & Calculation ---
try {
    $start_date = new DateTime($start_date_str);
    $end_date = new DateTime($end_date_str);
    $today = new DateTime('today');

    if ($start_date < $today || $end_date <= $start_date) {
        $_SESSION['error_message'] = "Invalid dates selected. Please choose a future start date and an end date after the start date.";
        header('Location: sitter_profile.php?id=' . $sitter_id);
        exit();
    }

    $interval = $start_date->diff($end_date);
    $total_nights = $interval->days;

    if ($total_nights <= 0) {
         $_SESSION['error_message'] = "Booking must be for at least one night.";
         header('Location: sitter_profile.php?id=' . $sitter_id);
         exit();
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Invalid date format.";
    header('Location: sitter_profile.php?id=' . $sitter_id);
    exit();
}

// --- Price Calculation ---
$sitter_total = $price_per_night * $total_nights;
$service_fee = $sitter_total * 0.15; // Example: 15% service fee
$total_price = $sitter_total + $service_fee;

// --- Save Initial Booking to Database ---
$booking_id = null;
try {
    $sql = "INSERT INTO bookings (user_id, sitter_id, service_type, start_date, end_date, total_nights, price_per_night, service_fee, total_price, booking_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_payment')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user_id,
        $sitter_id,
        $service_type,
        $start_date->format('Y-m-d'),
        $end_date->format('Y-m-d'),
        $total_nights,
        $price_per_night,
        $service_fee,
        $total_price
    ]);
    $booking_id = $pdo->lastInsertId();

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to save booking request. Please try again.";
    header('Location: sitter_profile.php?id=' . $sitter_id);
    exit();
}

if (!$booking_id) {
     $_SESSION['error_message'] = "Failed to create booking record.";
     header('Location: sitter_profile.php?id=' . $sitter_id);
     exit();
}

// --- Create Stripe Checkout Session ---
$YOUR_DOMAIN = 'http://localhost/nestpet'; // Replace with your actual live domain later

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'gbp',
                'product_data' => [
                    'name' => ucfirst($service_type) . ' Booking (' . $total_nights . ' nights)',
                    'description' => 'Booking with Sitter ID: ' . $sitter_id . ' from ' . $start_date->format('d M Y') . ' to ' . $end_date->format('d M Y'),
                ],
                'unit_amount' => round($total_price * 100), // Amount in pence
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $YOUR_DOMAIN . '/booking_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $YOUR_DOMAIN . '/booking_cancel.php?booking_id=' . $booking_id,
        'metadata' => [
            'booking_id' => $booking_id
        ]
    ]);

    // --- Save Stripe Session ID to Booking ---
    $stmt_update = $pdo->prepare("UPDATE bookings SET stripe_checkout_session_id = ? WHERE id = ?");
    $stmt_update->execute([$checkout_session->id, $booking_id]);

    // --- Redirect User to Stripe Checkout ---
    header("HTTP/1.1 303 See Other");
    header("Location: " . $checkout_session->url);
    exit();

} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not connect to payment gateway. Please try again later.";
    $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$booking_id]);
    header('Location: sitter_profile.php?id=' . $sitter_id);
    exit();
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
     $_SESSION['error_message'] = "An unexpected error occurred. Please try again.";
     $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$booking_id]);
     header('Location: sitter_profile.php?id=' . $sitter_id);
     exit();
}
?>