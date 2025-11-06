// ==========================================
// MULTI-STEP FORM - WITH CONDITIONAL STEPS
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('multiStepForm');
    if (!form) return;
    
    const steps = document.querySelectorAll('.form-step');
    const progressBar = document.getElementById('progressBar');
    const nextButtons = document.querySelectorAll('.next-btn');
    const prevButtons = document.querySelectorAll('.prev-btn');
    
    let currentStep = 0;
    const homeDetailsStepIndex = 2; // Step 3 (0-indexed)

    // Initialize form on load
    showStep(currentStep);
    initializeAllControls();
    initAutoSave();

    // ==========================================
    // CONDITIONAL STEP LOGIC
    // ==========================================
    
    function shouldShowHomeDetailsStep() {
        // Services that require home details
        const homeRequiredServices = ['boarding', 'daycare', 'smallpet'];
        const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
        
        for (let checkbox of serviceCheckboxes) {
            if (checkbox.checked && homeRequiredServices.includes(checkbox.value)) {
                return true;
            }
        }
        return false;
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
                let nextStep = currentStep + 1;
                
                // Skip home details step if not needed
                if (nextStep === homeDetailsStepIndex && !shouldShowHomeDetailsStep()) {
                    nextStep = homeDetailsStepIndex + 1;
                }
                
                currentStep = nextStep;
                showStep(currentStep);
            }
        });
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let prevStep = currentStep - 1;
            
            // Skip home details step when going back if not needed
            if (prevStep === homeDetailsStepIndex && !shouldShowHomeDetailsStep()) {
                prevStep = homeDetailsStepIndex - 1;
            }
            
            currentStep = prevStep;
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

        // Calculate progress considering conditional steps
        let totalSteps = steps.length;
        if (!shouldShowHomeDetailsStep()) {
            totalSteps = totalSteps - 1;
        }
        
        let displayStep = step;
        if (step > homeDetailsStepIndex && !shouldShowHomeDetailsStep()) {
            displayStep = step - 1;
        }
        
        const progress = ((displayStep + 1) / totalSteps) * 100;
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

        // Service selection validation for step 1
        if (step === 0) {
            const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
            const checkedServices = Array.from(serviceCheckboxes).filter(cb => cb.checked);
            if (checkedServices.length === 0) {
                isValid = false;
                showError(serviceCheckboxes[0], 'Please select at least one service');
            }
        }

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
        
        const parent = input.closest('.password-input-group') || input.closest('.service-card-checkbox') || input.parentElement;
        parent.appendChild(errorMsg);
    }

    // ==========================================
    // INITIALIZE ALL FORM CONTROLS
    // ==========================================
    
    function initializeAllControls() {
        initPasswordMatching();
        initOwnerFormToggles();
        initSitterFormToggles();
        initInputFormatting();
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
// GLOBAL PASSWORD TOGGLE FUNCTION
// ==========================================

function togglePasswordVisibilityAlt(button, inputId) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (!input || !icon) {
        console.error('Could not find input or icon for:', inputId);
        return;
    }
    
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