<?php
$fromApp = false;
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';


$pageTitle = "Become a Sitter";
include 'header.php';


?>
<link rel="stylesheet" href="signup.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* ============================================
   SERVICE SELECTION GRID - ROVER STYLE
   ============================================ */

.service-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.service-card-checkbox {
    position: relative;
}

.service-card-checkbox input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.service-label {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 120px;
}

.service-label:hover {
    border-color: #00a862;
    box-shadow: 0 4px 12px rgba(0, 168, 98, 0.15);
    transform: translateY(-2px);
}

.service-card-checkbox input[type="checkbox"]:checked + .service-label {
    border-color: #00a862;
    background: linear-gradient(135deg, #f0fdf4 0%, #e8f5e9 100%);
    box-shadow: 0 4px 16px rgba(0, 168, 98, 0.2);
}

.service-card-checkbox input[type="checkbox"]:checked + .service-label::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 12px;
    right: 12px;
    width: 28px;
    height: 28px;
    background: #00a862;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.service-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #00a862, #00c875);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.service-icon i {
    font-size: 28px;
    color: white;
}

.service-card-checkbox input[type="checkbox"]:checked + .service-label .service-icon {
    transform: scale(1.1);
}

.service-content {
    flex: 1;
}

.service-content h5 {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.service-content p {
    font-size: 14px;
    color: #666;
    margin: 0;
    line-height: 1.4;
}

.service-price {
    font-weight: 600;
    color: #00a862;
    margin-top: 8px !important;
    font-size: 15px !important;
}

.badge-highest {
    display: inline-block;
    padding: 4px 8px;
    background: linear-gradient(135deg, #ff9800, #ff5722);
    color: white;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
    </style>

<main class="signup-page">
    <div class="container signup-form-container">
        <div class="form-header">
            <h2>Become a NestMyPet Sitter</h2>
            <p>Let's get your profile set up. Please follow the steps below.</p>
        </div>

        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="process_signup_sitter.php" method="POST" id="multiStepForm">
            <!-- Step 1: About You + Service Selection -->
            <div class="form-step active">
                <fieldset>
                    <legend>Step 1: Your Details</legend>
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number *</label>
                        <input type="tel" id="phone_number" name="phone_number" required placeholder="+44 123 456 7890">
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth *</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required>
                    </div>
                    
                    <!-- Password Fields -->
                    <div class="form-group password-field">
                        <label for="password">Password * (minimum 8 characters)</label>
                        <div class="password-input-group">
                            <input type="password" id="password" name="password" minlength="8" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibilityAlt(this, 'password')" aria-label="Show password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group password-field">
                        <label for="confirm_password">Confirm Password *</label>
                        <div class="password-input-group">
                            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibilityAlt(this, 'confirm_password')" aria-label="Show password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <small id="password-match-message" style="color: red; display: none;">Passwords do not match</small>
                    </div>

                    <hr style="margin: 2rem 0; border: 0; border-top: 2px solid #e8e8e8;">

                    <!-- Service Selection Section -->
                    <h4 style="margin-top: 0; color: #333; font-size: 20px;">
                        <i class="fas fa-briefcase"></i> Service Selection
                    </h4>
                    <div class="info-box">
                        <p><strong>Select at least one service you're interested in.</strong> You can always add more later.</p>
                        <p>If you select more than one service, you will only see one of them during the sign up process.</p>
                        <p>After your profile is submitted for review, you can edit your selected services or add more.</p>
                    </div>

                    <div class="service-selection-grid">
                        <div class="service-card-checkbox">
                            <input type="checkbox" id="service_boarding" name="services[]" value="boarding" class="service-checkbox">
                            <label for="service_boarding" class="service-label">
                                <div class="service-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="service-content">
                                    <h5>Boarding <span class="badge-highest">HIGHEST EARNERS</span></h5>
                                    <p>Overnight pet care in your home</p>
                                   
                                </div>
                            </label>
                        </div>

                        <div class="service-card-checkbox">
                            <input type="checkbox" id="service_homesitting" name="services[]" value="homesitting" class="service-checkbox">
                            <label for="service_homesitting" class="service-label">
                                <div class="service-icon">
                                    <i class="fas fa-house-user"></i>
                                </div>
                                <div class="service-content">
                                    <h5>House Sitting</h5>
                                    <p>Overnight pet care in the owner's home</p>
                                   
                                </div>
                            </label>
                        </div>

                        <div class="service-card-checkbox">
                            <input type="checkbox" id="service_daycare" name="services[]" value="daycare" class="service-checkbox">
                            <label for="service_daycare" class="service-label">
                                <div class="service-icon">
                                    <i class="fas fa-sun"></i>
                                </div>
                                <div class="service-content">
                                    <h5>Dog Day Care</h5>
                                    <p>Daytime care in your home</p>
                                   
                                </div>
                            </label>
                        </div>

                        <div class="service-card-checkbox">
                            <input type="checkbox" id="service_walking" name="services[]" value="walking" class="service-checkbox">
                            <label for="service_walking" class="service-label">
                                <div class="service-icon">
                                    <i class="fas fa-person-walking-with-cane"></i>
                                </div>
                                <div class="service-content">
                                    <h5>Dog Walking</h5>
                                    <p>Exercise and outdoor time</p>
                                    
                                </div>
                            </label>
                        </div>

                        <div class="service-card-checkbox">
                            <input type="checkbox" id="service_dropin" name="services[]" value="dropin" class="service-checkbox">
                            <label for="service_dropin" class="service-label">
                                <div class="service-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="service-content">
                                    <h5>Cat Drop-In Visits</h5>
                                    <p>Quick check-ins and care</p>
                                    
                                </div>
                            </label>
                        </div>

                        <div class="service-card-checkbox">
                            <input type="checkbox" id="service_smallpet" name="services[]" value="smallpet" class="service-checkbox">
                            <label for="service_smallpet" class="service-label">
                                <div class="service-icon">
                                    <i class="fas fa-dove"></i>
                                </div>
                                <div class="service-content">
                                    <h5>Small Pet Care</h5>
                                    <p>Rabbits, birds, hamsters, etc.</p>
                                   
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="info-box" style="margin-top: 1rem;">
                        <p><i class="fas fa-info-circle"></i> <strong>Which services should I choose?</strong></p>
                        <p>Choose services based on your home environment, experience, and availability. You can offer multiple services to attract more bookings!</p>
                    </div>

                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 2: Location Details -->
            <div class="form-step">
                <fieldset>
                    <legend>Step 2: Your Location</legend>
                    <p>This helps pet owners find you in their area.</p>
                    <div class="form-group">
                        <label for="street">Street Address *</label>
                        <input type="text" id="street" name="street" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="postcode">Postcode *</label>
                        <input type="text" id="postcode" name="postcode" class="form-control" required pattern="^[A-Z]{1,2}\d{1,2}[A-Z]?\s?\d[A-Z]{2}$" placeholder="e.g., SW1A 1AA">
                        <small>UK postcode format required</small>
                    </div>
                    <div class="form-group">
                        <label for="country">Country *</label>
                        <select id="country" name="country" class="form-control" required>
                            <option value="">-- Select Country --</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="Canada">Canada</option>
                            <option value="Australia">Australia</option>
                            <option value="Ireland">Ireland</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 3: Your Home (CONDITIONAL - only shown if applicable services selected) -->
            <div class="form-step" id="home-details-step">
                <fieldset>
                    <legend>Step 3: Your Home</legend>
                    <p>Tell us about the environment where you'll be caring for pets.</p>
                    
                    <div class="form-group">
                        <label for="home_type">Type of Home *</label>
                        <select id="home_type" name="home_type" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="House with Garden">House with Garden</option>
                            <option value="House without Garden">House without Garden</option>
                            <option value="Flat or Apartment">Flat or Apartment</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="outdoor_space">Outdoor Space *</label>
                        <select id="outdoor_space" name="outdoor_space" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="Fully Fenced Garden">Fully Fenced Garden</option>
                            <option value="Balcony">Balcony</option>
                            <option value="No Outdoor Space">No Outdoor Space</option>
                        </select>
                    </div>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="smokes_indoors" name="smokes_indoors" value="1">
                        <label for="smokes_indoors">Someone smokes indoors</label>
                    </div>
                    
                    <h4 style="margin-top: 2rem; color: #333;">Household Members</h4>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="has_children" name="has_children" value="1">
                        <label for="has_children">I have children living with me</label>
                    </div>
                    
                    <div class="form-group" id="children_ages_group" style="display: none;">
                        <label for="children_ages">Children's Ages</label>
                        <input type="text" id="children_ages" name="children_ages" placeholder="e.g., 5, 8, 12" class="form-control">
                    </div>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="lives_alone" name="lives_alone" value="1">
                        <label for="lives_alone">I live alone</label>
                    </div>
                    
                    <div class="form-group" id="other_adults_group">
                        <label for="other_adults">Other Adults in Home</label>
                        <textarea id="other_adults" name="other_adults" rows="2" placeholder="Names and relationships of other adults"></textarea>
                    </div>
                    
                    <h4 style="margin-top: 2rem; color: #333;">Your Pets</h4>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="owns_pets" name="owns_pets" value="1">
                        <label for="owns_pets">I own pets</label>
                    </div>
                    
                    <div class="form-group" id="owned_pet_details_group" style="display: none;">
                        <label for="owned_pet_details">Pet Details</label>
                        <textarea id="owned_pet_details" name="owned_pet_details" rows="3" placeholder="Type, number, temperament (e.g., 2 friendly dogs, 1 indoor cat)"></textarea>
                    </div>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 4: Experience & Services -->
            <div class="form-step">
                <fieldset>
                    <legend>Step 4: Experience & Services</legend>
                    
                    <div class="form-group">
                        <label for="experience">Years of experience with pets *</label>
                        <input type="number" id="experience" name="experience" min="0" required class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="background">Tell us about your background with animals *</label>
                        <textarea id="background" name="background" rows="4" required placeholder="Share your experience, passion, and any relevant information"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="qualifications">Relevant Training or Qualifications</label>
                        <textarea id="qualifications" name="qualifications" rows="2" placeholder="Certifications, courses, or professional training"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="headline">Service Headline * (max 100 characters)</label>
                        <input type="text" id="headline" name="headline" class="form-control" maxlength="100" required placeholder="e.g., Experienced dog lover with fenced garden">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_pets">Max number of pets at a time *</label>
                        <input type="number" id="max_pets" name="max_pets" min="1" max="10" value="2" required class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="breed_size_restrictions">Breed or Size Restrictions (if any)</label>
                        <input type="text" id="breed_size_restrictions" name="breed_size_restrictions" placeholder="e.g., No large dogs, Only small breeds" class="form-control">
                    </div>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="can_administer_meds" name="can_administer_meds" value="1">
                        <label for="can_administer_meds">I can administer medication</label>
                    </div>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="emergency_transport" name="emergency_transport" value="1">
                        <label for="emergency_transport">Emergency transport to vet available</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="price_per_night">Base Price per Night (£) *</label>
                        <input type="number" id="price_per_night" name="price_per_night" min="10" max="500" step="5" value="50" required class="form-control">
                        <small>This is your base rate. You can adjust this later.</small>
                    </div>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 5: Verification & Consent -->
            <div class="form-step">
                <fieldset>
                    <legend>Step 5: Verification & Consent</legend>
                    
                    <h4 style="margin-top: 0; color: #333;">Training Requirement</h4>
                    <p style="color: #666; margin-bottom: 1rem;">
                        <strong>Note:</strong> After registration, you'll need to watch our training video and complete profile verification before accepting bookings.
                    </p>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="training_video_confirm" name="training_video_confirm" value="1" required>
                        <label for="training_video_confirm">I understand I must complete training before accepting bookings</label>
                    </div>
                    
                    <h4 style="margin-top: 2rem; color: #333;">Home Sitting Verification</h4>
                    
                    <div id="dbs_info_static" style="display: none; background: #f5f5f5; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                        <p style="margin: 0; font-size: 14px; color: #666;">
                            <strong><i class="fas fa-shield-halved"></i> DBS Check Required:</strong> Since you selected House Sitting, you'll need to provide a Basic DBS certificate or consent to a check after registration.
                        </p>
                    </div>
                    
                    <h4 style="margin-top: 2rem; color: #333;">Payment Information</h4>
                    
                    <div class="form-group">
                        <label>Preferred Payment Method *</label>
                        <div class="payment-method-card">
                            <input type="radio" id="payment_bank" name="payment_method" value="bank_transfer" checked>
                            <label for="payment_bank" class="payment-label">
                                <i class="fas fa-building-columns"></i>
                                <span>Bank Transfer</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="bank_details_group">
                        <label>Bank Account Details *</label>
                        <div style="display: grid; gap: 1rem; margin-top: 0.5rem;">
                            <div>
                                <label for="bank_account_name" style="font-weight: normal; font-size: 14px;">Account Name</label>
                                <input type="text" id="bank_account_name" name="bank_account_name" class="form-control" placeholder="John Smith" required>
                            </div>
                            <div>
                                <label for="bank_sort_code" style="font-weight: normal; font-size: 14px;">Sort Code</label>
                                <input type="text" id="bank_sort_code" name="bank_sort_code" class="form-control" placeholder="12-34-56" pattern="^\d{2}-?\d{2}-?\d{2}$" maxlength="8" required>
                                <small>Format: 12-34-56</small>
                            </div>
                            <div>
                                <label for="bank_account_number" style="font-weight: normal; font-size: 14px;">Account Number</label>
                                <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" placeholder="12345678" pattern="^\d{8}$" maxlength="8" required>
                                <small>8 digits</small>
                            </div>
                        </div>
                    </div>
                    
                    <h4 style="margin-top: 2rem; color: #333;">Terms & Agreements</h4>
                    
                    <div class="consent-section">
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="info.php?page=terms">NestMyPet Terms of Service</a></label>
                        </div>
                        
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="safety_policy" name="safety_policy" required>
                          <label for="safety_policy">I have read the <a href="info.php?page=safety" target="_blank" rel="noopener noreferrer">Safety and Emergency Policy</a></label>
                        </div>
                        
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="guarantee_policy" name="guarantee_policy" required>
                            <label for="guarantee_policy">I understand the Host Guarantee Policy</label>
                        </div>
                        
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="care_commitment" name="care_commitment" required>
                            <label for="care_commitment">I commit to providing emotionally sensitive, safe, and responsible care to all pets</label>
                        </div>
                    </div>
                    
                    <p style="margin-top: 2rem; color: #666; font-size: 14px;">
                        <strong>Next Steps:</strong> After registration, you'll be prompted to upload ID verification and complete your profile.
                    </p>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="submit" class="btn btn-primary btn-full-green">Create My Sitter Account</button>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="form.js"></script>
<script>
// Additional logic for conditional step display
document.addEventListener('DOMContentLoaded', function() {
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const homeDetailsStep = document.getElementById('home-details-step');
    const dbsInfoStatic = document.getElementById('dbs_info_static');
    
    // Services that require home details
    const homeRequiredServices = ['boarding', 'daycare', 'smallpet'];
    
    // Check if house sitting is selected for DBS info
    function checkHomeSitting() {
        const homesitting = document.getElementById('service_homesitting');
        if (homesitting && homesitting.checked) {
            dbsInfoStatic.style.display = 'block';
        } else {
            dbsInfoStatic.style.display = 'none';
        }
    }
    
    // Check on service change
    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', checkHomeSitting);
    });
    
    // Initial check
    checkHomeSitting();
    
    // Note: The actual step skipping logic should be handled in form.js
    // when navigating between steps
});
</script>

<?php include 'footer.php'; ?>