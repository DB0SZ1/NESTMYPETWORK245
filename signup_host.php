<?php
$pageTitle = "Become a Host";
include 'header.php';
?>

<main class="signup-page">
    <div class="container signup-form-container">
        <div class="form-header">
            <h2>Become a NestMyPet Host</h2>
            <p>We're excited to have you join our community! Please fill out your details to get started.</p>
        </div>

        <form action="process_signup_host.php" method="POST">
            <!-- Step 1: About You -->
            <fieldset>
                <legend>Step 1: About You</legend>
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                 <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                </div>
                 <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control">
                </div>
                 <div class="form-group">
                    <label for="address">Home Address</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>
            </fieldset>

            <!-- Step 2: Your Home -->
            <fieldset>
                <legend>Step 2: Your Home</legend>
                <div class="form-group">
                    <label for="home_type">Type of Home</label>
                    <select id="home_type" name="home_type" class="form-control">
                        <option value="House with Garden">House with Garden</option>
                        <option value="House without Garden">House without Garden</option>
                        <option value="Flat or Apartment">Flat or Apartment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="outdoor_space">Outdoor Space</label>
                     <select id="outdoor_space" name="outdoor_space" class="form-control">
                        <option value="Fully Fenced Garden">Fully Fenced Garden</option>
                        <option value="Balcony">Balcony</option>
                        <option value="No Outdoor Space">No Outdoor Space</option>
                    </select>
                </div>
            </fieldset>

            <!-- Step 3: Experience -->
             <fieldset>
                <legend>Step 3: Your Experience</legend>
                 <div class="form-group">
                    <label for="experience">Years of experience with pets</label>
                    <input type="number" id="experience" name="experience" min="0">
                </div>
                 <div class="form-group">
                    <label for="background">Tell us about your background with animals</label>
                    <textarea id="background" name="background" rows="4"></textarea>
                </div>
            </fieldset>

            <!-- Step 4: Consent -->
            <fieldset>
                <legend>Step 4: Agreement</legend>
                 <div class="form-group-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the NestMyPet Terms of Service and Privacy Policy.</label>
                </div>
                <div class="form-group-checkbox">
                    <input type="checkbox" id="protocol" name="protocol" required>
                    <label for="protocol">I have read and agree to the Pet Death & Emergency Protocol.</label>
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary btn-full-green">Create My Host Account</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
