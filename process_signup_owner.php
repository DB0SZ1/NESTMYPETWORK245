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
        // --- Step 1: Personal Information ---
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // --- Step 2: Pet Information ---
        $pet_name = trim($_POST['pet_name']);
        $pet_type = trim($_POST['pet_type']);
        $breed = trim($_POST['breed'] ?? '');
        $pet_size = trim($_POST['pet_size'] ?? '');
        $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
        $temperament_notes = trim($_POST['temperament_notes'] ?? '');
        $medical_conditions = trim($_POST['medical_conditions'] ?? '');
        $is_neutered = isset($_POST['is_neutered']) ? 1 : 0;
        $comfortable_with_pets = isset($_POST['comfortable_with_pets']) ? 1 : 0;
        $is_microchipped = isset($_POST['is_microchipped']) ? 1 : 0;

        // --- Step 3: Service Requirements ---
        $services = $_POST['services'] ?? [];
        $preferred_sitter_gender = trim($_POST['preferred_sitter_gender'] ?? 'Any');
        $one_on_one_required = isset($_POST['one_on_one_required']) ? 1 : 0;
        $household_requirements = trim($_POST['household_requirements'] ?? '');

        // --- Step 4: Emergency & Vet Information ---
        $emergency_name = trim($_POST['emergency_name'] ?? '');
        $emergency_phone = trim($_POST['emergency_phone'] ?? '');
        $vet_name = trim($_POST['vet_name'] ?? '');
        $vet_address = trim($_POST['vet_address'] ?? '');
        $vet_postcode = trim($_POST['vet_postcode'] ?? '');
        $vet_phone = trim($_POST['vet_phone'] ?? '');
        $auth_emergency_treatment = isset($_POST['auth_emergency_treatment']) ? 1 : 0;
        $has_pet_insurance = isset($_POST['has_pet_insurance']) ? 1 : 0;
        $insurance_details = trim($_POST['insurance_details'] ?? '');

        // --- Step 5: Communication Preferences ---
        $preferred_communication = trim($_POST['preferred_communication'] ?? 'Email');
        $daily_updates_wanted = isset($_POST['daily_updates_wanted']) ? 1 : 0;
        $update_frequency = trim($_POST['update_frequency'] ?? '');
        $meet_sitter_beforehand = isset($_POST['meet_sitter_beforehand']) ? 1 : 0;
        $additional_notes = trim($_POST['additional_notes'] ?? '');

        // Basic Validation
        if (empty($fullname) || empty($email) || empty($password)) {
            throw new Exception("Please fill out all required fields.");
        }

        // Password validation
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long.");
        }

        if (empty($pet_name) || empty($pet_type)) {
            throw new Exception("Please provide your pet's information.");
        }

        // Validate UK postcode format if provided
        if (!empty($vet_postcode)) {
            $postcode_pattern = '/^[A-Z]{1,2}\d{1,2}[A-Z]?\s?\d[A-Z]{2}$/i';
            if (!preg_match($postcode_pattern, $vet_postcode)) {
                throw new Exception("Please enter a valid UK postcode for vet address.");
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
            'phone' => $phone
        ]);
        $stripe_customer_id = $customer->id;

        // 3. Create the user account
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_user = $pdo->prepare("
            INSERT INTO users (
                fullname, email, password, phone_number,
                role, is_sitter, sitter_status, stripe_customer_id,
                profile_verified, verification_status
            ) VALUES (?, ?, ?, ?, 'owner', 0, 'not_sitter', ?, 0, 'pending')
        ");
        $stmt_user->execute([
            $fullname, $email, $password_hash, $phone, $stripe_customer_id
        ]);
        $user_id = $pdo->lastInsertId();

        // 4. Create owner profile with vet postcode
        $stmt_owner = $pdo->prepare("
            INSERT INTO owner_profiles (
                user_id, emergency_contact_name, emergency_contact_phone,
                vet_name, vet_address, vet_phone,
                auth_emergency_treatment, has_pet_insurance, insurance_details,
                preferred_communication, daily_updates_wanted, update_frequency,
                meet_sitter_beforehand, additional_notes, profile_completed
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
        ");
        
        // Combine vet address with postcode
        $full_vet_address = $vet_address;
        if (!empty($vet_postcode)) {
            $full_vet_address .= "\n" . $vet_postcode;
        }
        
        $stmt_owner->execute([
            $user_id, $emergency_name, $emergency_phone,
            $vet_name, $full_vet_address, $vet_phone,
            $auth_emergency_treatment, $has_pet_insurance, $insurance_details,
            $preferred_communication, $daily_updates_wanted, $update_frequency,
            $meet_sitter_beforehand, $additional_notes
        ]);

        // 5. Create the first pet with microchipped field
        $stmt_pet = $pdo->prepare("
            INSERT INTO pets (
                user_id, name, pet_type, breed, size, age,
                temperament_notes, medical_conditions,
                is_neutered, is_comfortable_with_pets, is_microchipped
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_pet->execute([
            $user_id, $pet_name, $pet_type, $breed, $pet_size, $age,
            $temperament_notes, $medical_conditions,
            $is_neutered, $comfortable_with_pets, $is_microchipped
        ]);

        // 6. Create service requirements
        if (!empty($services)) {
            foreach ($services as $service) {
                $stmt_service = $pdo->prepare("
                    INSERT INTO owner_service_requirements (
                        user_id, service_type, preferred_sitter_gender,
                        one_on_one_required, household_requirements
                    ) VALUES (?, ?, ?, ?, ?)
                ");
                $stmt_service->execute([
                    $user_id, $service, $preferred_sitter_gender,
                    $one_on_one_required, $household_requirements
                ]);
            }
        }

        $pdo->commit();

        // --- Log the new owner in ---
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_type'] = 'owner';
        list($firstname) = explode(' ', $fullname, 2);
        $_SESSION['user_firstname'] = $firstname;
        $_SESSION['user_lastname'] = trim(str_replace($firstname, '', $fullname));
        
        // Store signup data in session for profile editing
        $_SESSION['signup_data'] = [
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
            'emergency_contact_name' => $emergency_name,
            'emergency_contact_phone' => $emergency_phone,
            'vet_name' => $vet_name,
            'vet_address' => $full_vet_address,
            'vet_phone' => $vet_phone
        ];
        
        // --- Redirect to verification upload ---
        $_SESSION['success_message'] = "Welcome to NestMyPet! Please complete identity verification.";
        $_SESSION['profile_incomplete'] = true;
        $_SESSION['needs_verification'] = true;
        header("Location: upload_verification.php");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Owner Signup Error: " . $e->getMessage());
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: signup_owner.php");
        exit();
    }
} else {
    header("Location: auth.php");
    exit();
}