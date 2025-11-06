<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'vendor/autoload.php';
require 'db.php';

// --- Stripe API Key ---
$stripeSecretKey = trim('sk_test_51RrMbj3lvfiHaIRcBGGNoiMv9n7pqV6We1iXaQUOWuPic65AHD1rQd56Docq13ik7Adt1JBWGjcjDsbGYjz9pTpb002o9k2sjG');
\Stripe\Stripe::setApiKey($stripeSecretKey);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // --- Step 1: User Info ---
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $phone_number = trim($_POST['phone_number']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $date_of_birth = $_POST['date_of_birth'] ?? null;

        // --- Step 2: Location Info ---
        $street = trim($_POST['street']);
        $city = trim($_POST['city']);
        $postcode = trim($_POST['postcode']);
        $country = trim($_POST['country']);

        // --- Step 3: Home Info (CONDITIONAL - may be empty) ---
        $home_type = trim($_POST['home_type'] ?? '');
        $outdoor_space = trim($_POST['outdoor_space'] ?? '');
        $smokes_indoors = isset($_POST['smokes_indoors']) ? 1 : 0;
        $has_children = isset($_POST['has_children']) ? 1 : 0;
        $children_ages = trim($_POST['children_ages'] ?? '');
        $lives_alone = isset($_POST['lives_alone']) ? 1 : 0;
        $other_adults = trim($_POST['other_adults'] ?? '');
        $owns_pets = isset($_POST['owns_pets']) ? 1 : 0;
        $owned_pet_details = trim($_POST['owned_pet_details'] ?? '');

        // --- Step 4: Experience & Service Info ---
        $experience = intval($_POST['experience']);
        $background = trim($_POST['background']);
        $qualifications = trim($_POST['qualifications'] ?? '');
        $headline = trim($_POST['headline']);
        $services = $_POST['services'] ?? [];
        $max_pets = intval($_POST['max_pets']);
        $breed_size_restrictions = trim($_POST['breed_size_restrictions'] ?? '');
        $can_administer_meds = isset($_POST['can_administer_meds']) ? 1 : 0;
        $emergency_transport = isset($_POST['emergency_transport']) ? 1 : 0;
        $price_per_night = floatval($_POST['price_per_night']);
        $availability_notes = ''; // Now handled via calendar in profile

        // --- Step 5: Verification & Consent ---
        // Auto-detect if offering home sitting based on service selection
        $offers_home_sitting = in_array('homesitting', $services) ? 1 : 0;
        $payment_method = trim($_POST['payment_method']);
        
        // Bank details - separate fields (PayPal removed)
        $bank_account_name = trim($_POST['bank_account_name'] ?? '');
        $bank_sort_code = trim($_POST['bank_sort_code'] ?? '');
        $bank_account_number = trim($_POST['bank_account_number'] ?? '');

        // Basic Validation
        if (empty($fullname) || empty($email) || empty($password) || 
            empty($city) || empty($country) || empty($headline)) {
            throw new Exception("Please fill out all required fields.");
        }

        // Password validation
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }

        // Validate services
        if (empty($services)) {
            throw new Exception("Please select at least one service you offer.");
        }

        // Validate price
        if ($price_per_night < 10 || $price_per_night > 500) {
            throw new Exception("Price must be between £10 and £500.");
        }

        // Validate UK postcode format
        $postcode_pattern = '/^[A-Z]{1,2}\d{1,2}[A-Z]?\s?\d[A-Z]{2}$/i';
        if (!preg_match($postcode_pattern, $postcode)) {
            throw new Exception("Please enter a valid UK postcode.");
        }

        // Validate bank details (PayPal option removed)
        if ($payment_method === 'bank_transfer') {
            if (empty($bank_account_name) || empty($bank_sort_code) || empty($bank_account_number)) {
                throw new Exception("Please provide complete bank account details.");
            }
            
            // Validate sort code format (12-34-56)
            if (!preg_match('/^\d{2}-\d{2}-\d{2}$/', $bank_sort_code)) {
                throw new Exception("Invalid sort code format. Use format: 12-34-56");
            }
            
            // Validate account number (8 digits)
            if (!preg_match('/^\d{8}$/', $bank_account_number)) {
                throw new Exception("Account number must be 8 digits.");
            }
            
            // Combine bank details into a formatted string
            $bank_details = "Account Name: {$bank_account_name}\nSort Code: {$bank_sort_code}\nAccount Number: {$bank_account_number}";
        } else {
            throw new Exception("Please select a valid payment method.");
        }

        // Validate home details if home-based services are selected
        $home_based_services = ['boarding', 'daycare', 'smallpet'];
        $has_home_services = !empty(array_intersect($home_based_services, $services));
        
        if ($has_home_services) {
            if (empty($home_type) || empty($outdoor_space)) {
                throw new Exception("Please provide your home details for the services you selected.");
            }
        }

        $pdo->beginTransaction();

        // 1. Check if email already exists
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            throw new Exception("An account with this email already exists.");
        }

        // 2. Create Stripe Customer
        $customer = \Stripe\Customer::create([
            'name' => $fullname, 
            'email' => $email,
            'phone' => $phone_number
        ]);
        $stripe_customer_id = $customer->id;
        
        // 3. Auto-approve sitters but require verification
        $sitter_status = 'approved';
        
        // 3b. Determine sitter type based on services
        $has_home_services = !empty(array_intersect(['boarding', 'daycare', 'smallpet'], $services));
        $has_housesitting = in_array('homesitting', $services);
        
        if ($has_housesitting && $has_home_services) {
            $sitter_type = 'Boarder and House Sitter';
        } elseif ($has_housesitting) {
            $sitter_type = 'House Sitter';
        } else {
            $sitter_type = 'Boarder';
        }

        // 4. Create the user with ALL location fields
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_user = $pdo->prepare("
            INSERT INTO users (
                fullname, email, password, phone_number, 
                street, city, postcode, country,
                role, is_sitter, sitter_status, stripe_customer_id,
                profile_verified, verification_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_user->execute([
            $fullname,              // 1
            $email,                 // 2
            $password_hash,         // 3
            $phone_number,          // 4
            $street,                // 5
            $city,                  // 6
            $postcode,              // 7
            $country,               // 8
            'host',                 // 9 - role
            1,                      // 10 - is_sitter
            $sitter_status,         // 11 - sitter_status
            $stripe_customer_id,    // 12 - stripe_customer_id
            0,                      // 13 - profile_verified
            'pending'               // 14 - verification_status
        ]);
        $user_id = $pdo->lastInsertId();

        // 5. Create the host profile
        $stmt_host = $pdo->prepare("
            INSERT INTO host_profiles (
                user_id, sitter_type, sitter_role, date_of_birth,
                home_type, outdoor_space, smokes_indoors,
                has_children, children_ages, lives_alone, other_adults,
                owns_pets, owned_pet_details,
                years_experience, animal_background, qualifications,
                availability_notes, max_pets_at_once, breed_size_restrictions,
                can_administer_medication, emergency_transport_available,
                offers_home_sitting, payment_method, bank_details, paypal_email,
                profile_completed, dbs_check_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $dbs_status = $offers_home_sitting ? 'required' : 'not_required';
        $sitter_role = $offers_home_sitting ? 'house_sitter' : 'boarder';
        
        $stmt_host->execute([
            $user_id,                       // 1
            $sitter_type,                   // 2
            $sitter_role,                   // 3
            $date_of_birth,                 // 4
            $home_type,                     // 5
            $outdoor_space,                 // 6
            $smokes_indoors,                // 7
            $has_children,                  // 8
            $children_ages,                 // 9
            $lives_alone,                   // 10
            $other_adults,                  // 11
            $owns_pets,                     // 12
            $owned_pet_details,             // 13
            $experience,                    // 14
            $background,                    // 15
            $qualifications,                // 16
            $availability_notes,            // 17
            $max_pets,                      // 18
            $breed_size_restrictions,       // 19
            $can_administer_meds,           // 20
            $emergency_transport,           // 21
            $offers_home_sitting,           // 22
            $payment_method,                // 23
            $bank_details,                  // 24
            '',                             // 25 - paypal_email (removed)
            0,                              // 26 - profile_completed
            $dbs_status                     // 27
        ]);

        // 6. Create sitter_services entry (using first service as primary)
        $primary_service = $services[0];
        $stmt_service = $pdo->prepare("
            INSERT INTO sitter_services (
                user_id, service_type, price_per_night, 
                headline, sitter_about_me
            ) VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_service->execute([
            $user_id, 
            $primary_service, 
            $price_per_night, 
            $headline, 
            $background
        ]);

        // 7. Create host_services entries for all selected services
        foreach ($services as $service) {
            $stmt_host_service = $pdo->prepare("
                INSERT INTO host_services (
                    host_user_id, service_name, max_pets,
                    breed_size_restrictions, can_administer_meds,
                    has_emergency_transport
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt_host_service->execute([
                $user_id, 
                $service, 
                $max_pets,
                $breed_size_restrictions, 
                $can_administer_meds,
                $emergency_transport
            ]);
        }

        $pdo->commit();

        // --- Log the new sitter in ---
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = 'sitter';
        list($firstname) = explode(' ', $fullname, 2);
        $_SESSION['user_firstname'] = $firstname;
        $_SESSION['user_lastname'] = trim(str_replace($firstname, '', $fullname));
        
        // Store signup data in session for profile editing
        $_SESSION['signup_data'] = [
            'fullname' => $fullname,
            'email' => $email,
            'phone_number' => $phone_number,
            'street' => $street,
            'city' => $city,
            'postcode' => $postcode,
            'country' => $country,
            'bank_details' => $bank_details,
            'payment_method' => $payment_method,
            'services' => $services,
            'sitter_type' => $sitter_type
        ];
        
        // --- Redirect to verification upload ---
        $_SESSION['success_message'] = "Welcome! Please complete identity verification to activate your account.";
        $_SESSION['profile_incomplete'] = true;
        $_SESSION['needs_verification'] = true;
        $_SESSION['sitter_needs_dbs'] = $offers_home_sitting;
        header("Location: upload_verification.php");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Sitter Signup Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: signup_sitter.php");
        exit();
    }
} else {
    header("Location: auth.php");
    exit();
}
?>