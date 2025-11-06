<?php


$pageTitle = "Sign Up as a Pet Owner";
include 'header.php';


?>
<link rel="stylesheet" href="signup.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<main class="signup-page">
    <div class="container signup-form-container">
        <div class="form-header">
            <h2>Welcome, Pet Owner!</h2>
            <p>Let's get your account set up in a few simple steps.</p>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="process_signup_owner.php" method="POST" id="multiStepForm">
            <!-- Step 1: Personal Information -->
            <div class="form-step active">
                <fieldset>
                    <legend>Step 1: Your Information</legend>
                    <div class="form-group">
                        <label for="fullname">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+44 123 456 7890">
                    </div>
                <!-- Password Field -->
<div class="form-group password-field">
    <label for="password">Password * (minimum 8 characters)</label>
    <div class="password-input-group">
        <input type="password" id="password" name="password" minlength="8" required>
        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')" aria-label="Show password">
            <i class="fas fa-eye-slash"></i>
        </button>
    </div>
</div>

<!-- Confirm Password Field -->
<div class="form-group password-field">
    <label for="confirm_password">Confirm Password *</label>
    <div class="password-input-group">
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password')" aria-label="Show password">
            <i class="fas fa-eye-slash"></i>
        </button>
    </div>
    <small id="password-match-message" style="color: red; display: none;">Passwords do not match</small>
</div>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 2: About Your Pet (Enhanced) -->
<div class="form-step">
    <fieldset>
        <legend>Step 2: About Your Pet</legend>
        <p>Tell us about one of your pets. You can add more later.</p>
        
        <div class="form-group">
            <label for="pet_name">Pet's Name *</label>
            <input type="text" id="pet_name" name="pet_name" required placeholder="e.g., Max">
        </div>
        
        <div class="form-group">
            <label for="pet_type">Pet Type *</label>
            <select id="pet_type" name="pet_type" class="form-control" required>
                <option value="">-- Select Pet Type --</option>
                <option value="Dog">Dog</option>
                <option value="Cat">Cat</option>
                <option value="Guinea Pig">Guinea Pig</option>
                <option value="Hamster">Hamster</option>
                <option value="Rabbit">Rabbit</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="breed">Breed</label>
            <input type="text" id="breed" name="breed" placeholder="e.g., Golden Retriever">
        </div>
        
        <div class="form-group">
            <label for="pet_size">Size</label>
            <select id="pet_size" name="pet_size" class="form-control">
                <option value="">-- Select Size --</option>
                <option value="Small">Small (0-10kg)</option>
                <option value="Medium">Medium (10-25kg)</option>
                <option value="Large">Large (25kg+)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="age">Age (years)</label>
            <input type="number" id="age" name="age" min="0" max="30" placeholder="e.g., 3">
        </div>
        
        <div class="form-group">
            <label for="temperament_notes">Temperament & Special Needs</label>
            <textarea id="temperament_notes" name="temperament_notes" rows="3" placeholder="Friendly, energetic, shy around strangers, etc."></textarea>
        </div>
        
        <div class="form-group">
            <label for="medical_conditions">Medical Conditions or Medications</label>
            <textarea id="medical_conditions" name="medical_conditions" rows="2" placeholder="Any health issues or medications?"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group-checkbox">
                <input type="checkbox" id="is_neutered" name="is_neutered" value="1">
                <label for="is_neutered">Neutered/Spayed</label>
            </div>
            
            <div class="form-group-checkbox">
                <input type="checkbox" id="comfortable_with_pets" name="comfortable_with_pets" value="1" checked>
                <label for="comfortable_with_pets">Comfortable with other pets</label>
            </div>
            
            <div class="form-group-checkbox">
                <input type="checkbox" id="is_microchipped" name="is_microchipped" value="1">
                <label for="is_microchipped">Microchipped</label>
            </div>
        </div>
    </fieldset>
    <div class="form-navigation">
        <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
        <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
    </div>
</div>

            <!-- Step 3: Services Required -->
            <div class="form-step">
                <fieldset>
                    <legend>Step 3: Services Required</legend>
                    <p>What type of pet care services do you need?</p>
                    
                    <div class="form-group">
                        <label>Services Needed (check all that apply)</label>
                        <div class="checkbox-group">
                            <div class="form-group-checkbox">
                                <input type="checkbox" id="service_boarding" name="services[]" value="boarding">
                                <label for="service_boarding">Boarding (overnight in sitter's home)</label>
                            </div>
                            <div class="form-group-checkbox">
                                <input type="checkbox" id="service_daycare" name="services[]" value="daycare">
                                <label for="service_daycare">Day Care</label>
                            </div>
                            <div class="form-group-checkbox">
                                <input type="checkbox" id="service_walking" name="services[]" value="walking">
                                <label for="service_walking">Dog Walking</label>
                            </div>
                            <div class="form-group-checkbox">
                                <input type="checkbox" id="service_dropin" name="services[]" value="dropin">
                                <label for="service_dropin">Drop-in Visit</label>
                            </div>
                            <div class="form-group-checkbox">
                                <input type="checkbox" id="service_homesitting" name="services[]" value="homesitting">
                                <label for="service_homesitting">Sitting in My Home</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="preferred_sitter_gender">Preferred Sitter Gender</label>
                        <select id="preferred_sitter_gender" name="preferred_sitter_gender" class="form-control">
                            <option value="Any">Any</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="one_on_one_required" name="one_on_one_required" value="1">
                        <label for="one_on_one_required">One-on-one attention required</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="household_requirements">Household Requirements</label>
                        <p style="font-size: 14px; color: #666; margin-bottom: 10px;">Do you have any specific requirements for the sitter's household? (e.g., no smoking, fenced garden, no other pets)</p>
                        <textarea id="household_requirements" name="household_requirements" rows="3" placeholder="e.g., Must have a fenced garden, no smoking indoors, experience with large dogs"></textarea>
                    </div>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 4: Emergency & Vet Information (Enhanced) -->
            <div class="form-step">
                <fieldset>
                    <legend>Step 4: Emergency & Vet Information</legend>
                    
                    <h4 style="margin-top: 0; color: #333;">Emergency Contact</h4>
                    <div class="form-group">
                        <label for="emergency_name">Emergency Contact Name</label>
                        <input type="text" id="emergency_name" name="emergency_name" placeholder="Contact person's name">
                    </div>
                    <div class="form-group">
                        <label for="emergency_phone">Emergency Contact Phone</label>
                        <input type="tel" id="emergency_phone" name="emergency_phone" placeholder="+44 123 456 7890">
                    </div>
                    
                    <h4 style="margin-top: 2rem; color: #333;">Veterinary Information</h4>
                    <div class="form-group">
                        <label for="vet_name">Vet Name</label>
                        <input type="text" id="vet_name" name="vet_name" placeholder="Your vet's name or clinic">
                    </div>
                    <div class="form-group">
                        <label for="vet_address">Vet Address</label>
                        <textarea id="vet_address" name="vet_address" rows="2" placeholder="Full vet clinic address"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="vet_postcode">Vet Postcode</label>
                        <input type="text" id="vet_postcode" name="vet_postcode" placeholder="e.g., SW1A 1AA" pattern="^[A-Z]{1,2}\d{1,2}[A-Z]?\s?\d[A-Z]{2}$">
                        <small>UK postcode format required</small>
                    </div>
                    <div class="form-group">
                        <label for="vet_phone">Vet Phone Number</label>
                        <input type="tel" id="vet_phone" name="vet_phone" placeholder="+44 123 456 7890">
                    </div>
                    
                    <div class="form-group-checkbox" style="margin-top: 1.5rem;">
                        <input type="checkbox" id="auth_emergency_treatment" name="auth_emergency_treatment" value="1">
                        <label for="auth_emergency_treatment">Authorize emergency treatment if unable to reach me</label>
                    </div>
                    
                    <h4 style="margin-top: 2rem; color: #333;">Pet Insurance</h4>
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="has_pet_insurance" name="has_pet_insurance" value="1">
                        <label for="has_pet_insurance">I have pet insurance</label>
                    </div>
                    
                    <div class="form-group" id="insurance_details_group" style="display: none;">
                        <label for="insurance_details">Insurance Provider & Policy Number</label>
                        <input type="text" id="insurance_details" name="insurance_details" placeholder="Provider name and policy number">
                    </div>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 5: Communication & Preferences -->
            <div class="form-step">
                <fieldset>
                    <legend>Step 5: Communication & Preferences</legend>
                    
                    <div class="form-group">
                        <label for="preferred_communication">Preferred Communication Method</label>
                        <select id="preferred_communication" name="preferred_communication" class="form-control">
                            <option value="Email">Email</option>
                            <option value="Text">Text Message</option>
                            <option value="Phone">Phone Call</option>
                            <option value="App">App Notifications</option>
                        </select>
                    </div>
                    
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="daily_updates_wanted" name="daily_updates_wanted" value="1" checked>
                        <label for="daily_updates_wanted">I want daily updates about my pet</label>
                    </div>
                    
                    <div class="form-group" id="update_frequency_group">
                        <label for="update_frequency">How often would you like updates?</label>
                        <input type="text" id="update_frequency" name="update_frequency" placeholder="e.g., Every 2 days, Weekly">
                    </div>
                    
                    <div class="form-group-checkbox" style="margin-top: 1.5rem;">
                        <input type="checkbox" id="meet_sitter_beforehand" name="meet_sitter_beforehand" value="1" checked>
                        <label for="meet_sitter_beforehand">I would like to meet the sitter beforehand</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional_notes">Additional Notes</label>
                        <textarea id="additional_notes" name="additional_notes" rows="4" placeholder="Any other information the sitter should know..."></textarea>
                    </div>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="button" class="btn btn-primary next-btn">Next &rarr;</button>
                </div>
            </div>

            <!-- Step 6: Consent & Agreement (Enhanced) -->
            <div class="form-step">
                <fieldset>
                    <legend>Step 6: Consent & Agreement</legend>
                    
                    <div class="consent-section">
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
<label for="terms">I agree to the <a href="info.php?page=terms" target="_blank" rel="noopener noreferrer">NestMyPet Terms of Service</a></label>                        </div>
                        
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="safety_policy" name="safety_policy" required>
                          <label for="safety_policy">I have read the <a href="info.php?page=safety" target="_blank" rel="noopener noreferrer">Safety and Emergency Policy</a></label>
                        </div>
                        
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="info_accurate" name="info_accurate" required>
                            <label for="info_accurate">I confirm all pet information is accurate</label>
                        </div>
                        
                        <div class="form-group-checkbox">
                            <input type="checkbox" id="payment_terms" name="payment_terms" required>
                            <label for="payment_terms">I agree to payment terms</label>
                        </div>
                    </div>
                    
                    <p style="margin-top: 2rem; color: #666; font-size: 14px;">
                        <strong>Note:</strong> You'll be able to complete additional profile details after registration.
                    </p>
                </fieldset>
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary prev-btn">&larr; Back</button>
                    <button type="submit" class="btn btn-primary btn-full-green">Create My Account</button>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Terms of Service Modal -->
<div id="termsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Terms of Service</h2>
            <span class="close" onclick="closeModal('termsModal')">&times;</span>
        </div>
        <div class="modal-body">
            <h3>1. Acceptance of Terms</h3>
            <p>By accessing and using NestMyPet, you accept and agree to be bound by these Terms of Service.</p>
            
            <h3>2. User Accounts</h3>
            <p>You must provide accurate, complete information when creating your account. You are responsible for maintaining the confidentiality of your account credentials.</p>
            
            <h3>3. Pet Owner Responsibilities</h3>
            <ul>
                <li>Provide accurate information about your pet's health, temperament, and needs</li>
                <li>Ensure all vaccinations are up to date</li>
                <li>Communicate any behavioral issues or medical conditions</li>
                <li>Provide emergency contact information</li>
            </ul>
            
            <h3>4. Booking and Payment</h3>
            <p>All bookings are subject to sitter availability. Payment is processed securely through Stripe. Cancellation policies apply as stated at the time of booking.</p>
            
            <h3>5. Liability</h3>
            <p>NestMyPet acts as a platform connecting pet owners with sitters. While we verify our sitters, owners are responsible for selecting appropriate care for their pets.</p>
            
            <h3>6. Dispute Resolution</h3>
            <p>Any disputes should be reported to NestMyPet support within 24 hours of the incident.</p>
            
            <h3>7. Privacy</h3>
            <p>Your personal data is handled in accordance with our Privacy Policy and GDPR regulations.</p>
            
            <h3>8. Termination</h3>
            <p>We reserve the right to suspend or terminate accounts that violate these terms.</p>
            
            <p style="margin-top: 20px;"><strong>Last Updated:</strong> January 2025</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('termsModal')" class="btn btn-primary">I Understand</button>
        </div>
    </div>
</div>

<!-- Safety Policy Modal -->
<div id="safetyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Safety & Emergency Policy</h2>
            <span class="close" onclick="closeModal('safetyModal')">&times;</span>
        </div>
        <div class="modal-body">
            <h3>1. Safety Standards</h3>
            <p>All sitters on NestMyPet are required to:</p>
            <ul>
                <li>Complete identity verification</li>
                <li>Provide accurate information about their home environment</li>
                <li>Maintain a safe, secure space for pets</li>
                <li>Have DBS checks for home sitting services</li>
            </ul>
            
            <h3>2. Emergency Protocols</h3>
            <p><strong>In case of medical emergency:</strong></p>
            <ul>
                <li>Sitters will immediately contact you via phone</li>
                <li>If unable to reach you, will contact your emergency contact</li>
                <li>Will transport pet to your designated vet or nearest emergency vet</li>
                <li>Will authorize emergency treatment if you've given prior consent</li>
            </ul>
            
            <h3>3. Incident Reporting</h3>
            <p>Any incidents, injuries, or illnesses must be reported immediately to both the owner and NestMyPet support.</p>
            
            <h3>4. Insurance</h3>
            <p>We strongly recommend pet owners maintain active pet insurance. Sitters carry liability insurance, but pet medical costs are the owner's responsibility.</p>
            
            <h3>5. Lost or Escaped Pets</h3>
            <p>In the event a pet escapes or goes missing:</p>
            <ul>
                <li>Immediate search of the area will be conducted</li>
                <li>Owner and local authorities will be notified immediately</li>
                <li>Social media alerts will be posted</li>
                <li>Local vets and shelters will be contacted</li>
            </ul>
            
            <h3>6. Pet Death Protocol</h3>
            <p>In the tragic event of a pet's death during care:</p>
            <ul>
                <li>Owner will be contacted immediately</li>
                <li>Full incident report will be filed</li>
                <li>Veterinary assessment will be conducted</li>
                <li>Investigation will determine circumstances</li>
                <li>Support resources will be provided to the owner</li>
            </ul>
            
            <h3>7. Medication Administration</h3>
            <p>Only sitters who have confirmed they can administer medication will do so. Written instructions must be provided.</p>
            
            <h3>8. Behavioral Issues</h3>
            <p>If a pet displays unexpected aggressive behavior, sitters may end the booking early for safety reasons with full communication to the owner.</p>
            
            <p style="margin-top: 20px;"><strong>Emergency Hotline:</strong> Available 24/7 at +44 800 123 4567</p>
            <p><strong>Last Updated:</strong> January 2025</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeModal('safetyModal')" class="btn btn-primary">I Understand</button>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6);
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    border-radius: 20px;
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 30px 40px 20px;
    border-bottom: 2px solid #e8e8e8;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 28px;
    color: #1a1a1a;
}

.close {
    color: #aaa;
    font-size: 36px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: #000;
}

.modal-body {
    padding: 30px 40px;
    overflow-y: auto;
    flex: 1;
}

.modal-body h3 {
    color: #00a862;
    font-size: 20px;
    margin: 25px 0 15px 0;
}

.modal-body h3:first-child {
    margin-top: 0;
}

.modal-body p {
    color: #666;
    line-height: 1.8;
    margin-bottom: 15px;
}

.modal-body ul {
    margin: 15px 0;
    padding-left: 25px;
}

.modal-body ul li {
    color: #666;
    line-height: 1.8;
    margin-bottom: 8px;
}

.modal-footer {
    padding: 20px 40px 30px;
    border-top: 2px solid #e8e8e8;
    text-align: right;
}

.modal-footer .btn {
    padding: 12px 30px;
    background: #00a862;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.3s;
}

.modal-footer .btn:hover {
    background: #008852;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 10% auto;
        max-height: 85vh;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding-left: 25px;
        padding-right: 25px;
    }
    
    .modal-header h2 {
        font-size: 22px;
    }
}
</style>
 <script>

// ==========================================
// MULTI-STEP FORM - SIMPLIFIED PASSWORD TOGGLES
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('multiStepForm');
    if (!form) return;
    
    const steps = document.querySelectorAll('.form-step');
    const progressBar = document.getElementById('progressBar');
    const nextButtons = document.querySelectorAll('.next-btn');
    const prevButtons = document.querySelectorAll('.prev-btn');
    
    let currentStep = 0;

    // Initialize form on load
    showStep(currentStep);
    initializeAllControls();
    initAutoSave();

    // ==========================================
    // PASSWORD TOGGLE - SUPER SIMPLIFIED
    // ==========================================
    
    function initPasswordToggles() {
        // This function can now be empty since we're using inline onclick
        // But we keep it for compatibility with initializeAllControls
    }

    // ==========================================
    // AUTO-SAVE FUNCTIONALITY
    // ==========================================
    
    function initAutoSave() {
        const STORAGE_KEY = 'nestmypet_form_draft_' + window.location.pathname;
        
        restoreDraft();
        form.addEventListener('input', debounce(saveDraft, 500));
        form.addEventListener('change', saveDraft);
        
        form.addEventListener('submit', function() {
            localStorage.removeItem(STORAGE_KEY);
        });
        
        function saveDraft() {
            const formData = {};
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    formData[input.id || input.name] = input.checked;
                } else if (input.type === 'radio') {
                    if (input.checked) {
                        formData[input.name] = input.value;
                    }
                } else if (input.name === 'services[]') {
                    if (!formData['services']) {
                        formData['services'] = [];
                    }
                    if (input.checked) {
                        formData['services'].push(input.value);
                    }
                } else if (input.type !== 'password') {
                    formData[input.id || input.name] = input.value;
                }
            });
            
            formData['__currentStep'] = currentStep;
            
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
                showAutoSaveIndicator();
            } catch (e) {
                console.error('Failed to save draft:', e);
            }
        }
        
        function restoreDraft() {
            try {
                const savedData = localStorage.getItem(STORAGE_KEY);
                if (!savedData) return;
                
                const formData = JSON.parse(savedData);
                
                if (Object.keys(formData).length > 1) {
                    const restore = confirm('We found a saved draft. Continue where you left off?');
                    
                    if (restore) {
                        Object.keys(formData).forEach(key => {
                            if (key === '__currentStep') {
                                currentStep = formData[key];
                                showStep(currentStep);
                                return;
                            }
                            
                            if (key === 'services') {
                                formData[key].forEach(value => {
                                    const checkbox = form.querySelector(`input[name="services[]"][value="${value}"]`);
                                    if (checkbox) checkbox.checked = true;
                                });
                                return;
                            }
                            
                            const input = form.querySelector(`#${key}, [name="${key}"]`);
                            if (input) {
                                if (input.type === 'checkbox') {
                                    input.checked = formData[key];
                                } else if (input.type === 'radio') {
                                    const radio = form.querySelector(`input[name="${key}"][value="${formData[key]}"]`);
                                    if (radio) radio.checked = true;
                                } else {
                                    input.value = formData[key];
                                }
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                        
                        showRestoreNotification();
                    } else {
                        localStorage.removeItem(STORAGE_KEY);
                    }
                }
            } catch (e) {
                console.error('Failed to restore draft:', e);
            }
        }
        
        function showAutoSaveIndicator() {
            const existingIndicator = document.querySelector('.auto-save-indicator');
            if (existingIndicator) existingIndicator.remove();
            
            const indicator = document.createElement('div');
            indicator.className = 'auto-save-indicator';
            indicator.innerHTML = '<i class="fas fa-check-circle"></i> Draft saved';
            indicator.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 10px 20px;
                border-radius: 8px;
                font-size: 14px;
                z-index: 9999;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInUp 0.3s ease, fadeOut 0.3s ease 2.7s;
            `;
            
            document.body.appendChild(indicator);
            setTimeout(() => indicator.remove(), 3000);
        }
        
        function showRestoreNotification() {
            const notification = document.createElement('div');
            notification.className = 'restore-notification';
            notification.innerHTML = '<i class="fas fa-info-circle"></i> Progress restored';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #007bff;
                color: white;
                padding: 15px 25px;
                border-radius: 8px;
                font-size: 14px;
                z-index: 9999;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInDown 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }
        
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    }

    // ==========================================
    // NAVIGATION HANDLERS
    // ==========================================
    
    nextButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentStep--;
            showStep(currentStep);
        });
    });

    // ==========================================
    // STEP DISPLAY & PROGRESS
    // ==========================================
    
    function showStep(step) {
        steps.forEach((s, index) => {
            s.classList.remove('active');
            if (index === step) {
                s.classList.add('active');
            }
        });

        const progress = ((step + 1) / steps.length) * 100;
        progressBar.style.width = progress + '%';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ==========================================
    // VALIDATION LOGIC
    // ==========================================
    
    function validateStep(step) {
        const currentStepElement = steps[step];
        const inputs = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        currentStepElement.querySelectorAll('.error-message').forEach(msg => msg.remove());

        inputs.forEach(input => {
            if (input.offsetParent === null) return;
            
            input.classList.remove('error');
            
            if (!input.value.trim() && input.type !== 'checkbox') {
                isValid = false;
                showError(input, 'This field is required');
            }

            if (input.type === 'checkbox' && input.hasAttribute('required') && !input.checked) {
                isValid = false;
                showError(input, 'You must agree to continue');
            }

            if (input.type === 'email' && input.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                    showError(input, 'Please enter a valid email address');
                }
            }

            if (input.id === 'password' && input.value) {
                if (input.value.length < 8) {
                    isValid = false;
                    showError(input, 'Password must be at least 8 characters');
                }
            }

            if (input.id === 'confirm_password' && input.value) {
                const password = document.getElementById('password');
                if (password && input.value !== password.value) {
                    isValid = false;
                    showError(input, 'Passwords do not match');
                }
            }

            if (input.type === 'tel' && input.value && input.hasAttribute('required')) {
                const phoneRegex = /^[\d\s\+\-\(\)]+$/;
                if (!phoneRegex.test(input.value)) {
                    isValid = false;
                    showError(input, 'Please enter a valid phone number');
                }
            }

            if ((input.id === 'postcode' || input.id === 'vet_postcode') && input.value) {
                const postcodeRegex = /^[A-Z]{1,2}\d{1,2}[A-Z]?\s?\d[A-Z]{2}$/i;
                if (!postcodeRegex.test(input.value)) {
                    isValid = false;
                    showError(input, 'Please enter a valid UK postcode');
                }
            }

            if (input.id === 'bank_sort_code' && input.value && input.hasAttribute('required')) {
                const sortCodeRegex = /^\d{2}-\d{2}-\d{2}$/;
                if (!sortCodeRegex.test(input.value)) {
                    isValid = false;
                    showError(input, 'Sort code must be in format 12-34-56');
                }
            }

            if (input.id === 'bank_account_number' && input.value && input.hasAttribute('required')) {
                const accountRegex = /^\d{8}$/;
                if (!accountRegex.test(input.value)) {
                    isValid = false;
                    showError(input, 'Account number must be exactly 8 digits');
                }
            }

            if (input.id === 'date_of_birth' && input.value) {
                const dob = new Date(input.value);
                const age = Math.floor((Date.now() - dob) / (365.25 * 24 * 60 * 60 * 1000));
                if (age < 18) {
                    isValid = false;
                    showError(input, 'You must be at least 18 years old');
                }
            }
        });

        const serviceCheckboxes = currentStepElement.querySelectorAll('input[name="services[]"]');
        if (serviceCheckboxes.length > 0) {
            const checkedServices = Array.from(serviceCheckboxes).filter(cb => cb.checked);
            if (checkedServices.length === 0) {
                isValid = false;
                showError(serviceCheckboxes[0], 'Please select at least one service');
            }
        }

        if (!isValid) {
            const firstError = currentStepElement.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return isValid;
    }

    function showError(input, message) {
        input.classList.add('error');
        
        const errorMsg = document.createElement('span');
        errorMsg.className = 'error-message';
        errorMsg.textContent = message;
        errorMsg.style.color = '#dc3545';
        errorMsg.style.fontSize = '0.85rem';
        errorMsg.style.marginTop = '0.25rem';
        errorMsg.style.display = 'block';
        
        const parent = input.closest('.password-input-group') || input.parentElement;
        parent.appendChild(errorMsg);
    }

    // ==========================================
    // INITIALIZE ALL FORM CONTROLS
    // ==========================================
    
    function initializeAllControls() {
        initPasswordToggles();
        initPasswordMatching();
        initOwnerFormToggles();
        initSitterFormToggles();
        initInputFormatting();
        initPaymentToggle();
        initRealTimeValidation();
    }

    // ========== PASSWORD MATCHING ==========
    function initPasswordMatching() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const matchMessage = document.getElementById('password-match-message');
        
        if (password && confirmPassword && matchMessage) {
            function checkPasswordMatch() {
                if (confirmPassword.value !== '') {
                    if (password.value !== confirmPassword.value) {
                        matchMessage.style.display = 'block';
                        matchMessage.style.color = '#dc3545';
                        matchMessage.textContent = '✗ Passwords do not match';
                        confirmPassword.classList.add('error');
                    } else {
                        matchMessage.style.display = 'block';
                        matchMessage.style.color = '#28a745';
                        matchMessage.textContent = '✓ Passwords match';
                        confirmPassword.classList.remove('error');
                    }
                } else {
                    matchMessage.style.display = 'none';
                    confirmPassword.classList.remove('error');
                }
            }
            
            password.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }
    }

    // ========== OWNER FORM TOGGLES ==========
    function initOwnerFormToggles() {
        const hasInsurance = document.getElementById('has_pet_insurance');
        const insuranceGroup = document.getElementById('insurance_details_group');
        if (hasInsurance && insuranceGroup) {
            hasInsurance.addEventListener('change', function() {
                insuranceGroup.style.display = this.checked ? 'block' : 'none';
                const insuranceInput = document.getElementById('insurance_details');
                if (insuranceInput) {
                    insuranceInput.required = this.checked;
                }
            });
            insuranceGroup.style.display = hasInsurance.checked ? 'block' : 'none';
        }

        const dailyUpdates = document.getElementById('daily_updates_wanted');
        const updateFreqGroup = document.getElementById('update_frequency_group');
        if (dailyUpdates && updateFreqGroup) {
            dailyUpdates.addEventListener('change', function() {
                updateFreqGroup.style.display = this.checked ? 'none' : 'block';
            });
            updateFreqGroup.style.display = dailyUpdates.checked ? 'none' : 'block';
        }
    }

    // ========== SITTER FORM TOGGLES ==========
    function initSitterFormToggles() {
        const hasChildren = document.getElementById('has_children');
        const childrenGroup = document.getElementById('children_ages_group');
        if (hasChildren && childrenGroup) {
            hasChildren.addEventListener('change', function() {
                childrenGroup.style.display = this.checked ? 'block' : 'none';
            });
            childrenGroup.style.display = hasChildren.checked ? 'block' : 'none';
        }

        const livesAlone = document.getElementById('lives_alone');
        const adultsGroup = document.getElementById('other_adults_group');
        if (livesAlone && adultsGroup) {
            livesAlone.addEventListener('change', function() {
                adultsGroup.style.display = this.checked ? 'none' : 'block';
            });
            adultsGroup.style.display = livesAlone.checked ? 'none' : 'block';
        }

        const ownsPets = document.getElementById('owns_pets');
        const petsGroup = document.getElementById('owned_pet_details_group');
        if (ownsPets && petsGroup) {
            ownsPets.addEventListener('change', function() {
                petsGroup.style.display = this.checked ? 'block' : 'none';
            });
            petsGroup.style.display = ownsPets.checked ? 'block' : 'none';
        }

        const homeSitting = document.getElementById('offers_home_sitting');
        const dbsInfo = document.getElementById('dbs_info');
        if (homeSitting && dbsInfo) {
            homeSitting.addEventListener('change', function() {
                dbsInfo.style.display = this.checked ? 'block' : 'none';
            });
            dbsInfo.style.display = homeSitting.checked ? 'block' : 'none';
        }
    }

    // ========== INPUT FORMATTING ==========
    function initInputFormatting() {
        const sortCode = document.getElementById('bank_sort_code');
        if (sortCode) {
            sortCode.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '-' + value.substring(2);
                }
                if (value.length >= 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 7);
                }
                e.target.value = value;
            });
        }

        const accountNumber = document.getElementById('bank_account_number');
        if (accountNumber) {
            accountNumber.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 8);
            });
        }

        const postcodes = document.querySelectorAll('#postcode, #vet_postcode');
        postcodes.forEach(pc => {
            pc.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        });
    }

    // ========== PAYMENT METHOD TOGGLE ==========
    function initPaymentToggle() {
        const paymentMethod = document.getElementById('payment_method');
        if (paymentMethod) {
            paymentMethod.addEventListener('change', function() {
                togglePaymentFields(this.value);
            });
            togglePaymentFields(paymentMethod.value);
        }
    }

    function togglePaymentFields(method) {
        const bankGroup = document.getElementById('bank_details_group');
        const paypalGroup = document.getElementById('paypal_email_group');
        const bankAccountName = document.getElementById('bank_account_name');
        const bankSortCode = document.getElementById('bank_sort_code');
        const bankAccountNumber = document.getElementById('bank_account_number');
        const paypalEmail = document.getElementById('paypal_email');
        
        if (!bankGroup || !paypalGroup) return;
        
        if (method === 'bank_transfer') {
            bankGroup.style.display = 'block';
            paypalGroup.style.display = 'none';
            if (bankAccountName) bankAccountName.required = true;
            if (bankSortCode) bankSortCode.required = true;
            if (bankAccountNumber) bankAccountNumber.required = true;
            if (paypalEmail) paypalEmail.required = false;
        } else {
            bankGroup.style.display = 'none';
            paypalGroup.style.display = 'block';
            if (bankAccountName) bankAccountName.required = false;
            if (bankSortCode) bankSortCode.required = false;
            if (bankAccountNumber) bankAccountNumber.required = false;
            if (paypalEmail) paypalEmail.required = true;
        }
    }

    // ========== REAL-TIME VALIDATION ==========
    function initRealTimeValidation() {
        form.addEventListener('input', function(e) {
            if (e.target.classList.contains('error')) {
                if (e.target.value.trim()) {
                    e.target.classList.remove('error');
                    const parent = e.target.closest('.password-input-group') || e.target.parentElement;
                    const errorMsg = parent.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            }
        });
    }

    // ==========================================
    // FORM SUBMISSION
    // ==========================================
    
    form.addEventListener('submit', function(e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
            return false;
        }
        
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        if (password && confirmPassword && password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match. Please check and try again.');
            return false;
        }
        
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
        }
    });

    // ==========================================
    // KEYBOARD NAVIGATION
    // ==========================================
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.target.matches('textarea')) {
            const activeStep = document.querySelector('.form-step.active');
            const nextBtn = activeStep?.querySelector('.next-btn');
            
            if (nextBtn && document.activeElement !== nextBtn) {
                e.preventDefault();
                nextBtn.click();
            }
        }
    });
});

// ==========================================
// GLOBAL PASSWORD TOGGLE FUNCTION (for inline use)
// ==========================================

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        button.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        button.setAttribute('aria-label', 'Show password');
    }
}

// ==========================================
// INJECTED STYLES
// ==========================================

(function() {
    const style = document.createElement('style');
    style.textContent = `
        .form-control.error,
        input.error,
        select.error,
        textarea.error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .error-message {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        @keyframes slideInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .auto-save-indicator,
        .restore-notification {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .toggle-password {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 8px;
            color: #666;
            transition: color 0.2s;
        }
        
        .toggle-password:hover {
            color: #333;
        }
        
        .toggle-password:focus {
            outline: 2px solid #00a862;
            outline-offset: 2px;
        }
    `;
    document.head.appendChild(style);
})();

</script>

<script src ="form.js"></script>
<?php include 'footer.php'; ?>