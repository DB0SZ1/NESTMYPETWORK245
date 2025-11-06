<?php
$pageTitle = 'Login - NestMyPet';
require_once 'header.php';
?>

<style>
.auth-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.auth-container {
    max-width: 480px;
    width: 100%;
    margin: 0 auto;
    padding: 0 20px;
}

.auth-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    padding: 3rem;
    border-top: 4px solid var(--secondary-color);
}

.auth-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.auth-header h1 {
    font-size: 2rem;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: var(--text-color);
    font-size: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--dark-color);
    font-size: 0.95rem;
}

.input-wrapper {
    position: relative;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    font-family: var(--font-family);
    transition: all 0.3s ease;
    background-color: #fafafa;
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary-color);
    background-color: white;
    box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.1);
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    font-size: 1.1rem;
    padding: 4px;
    transition: color 0.2s ease;
}

.password-toggle:hover {
    color: var(--dark-color);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.forgot-password {
    color: var(--secondary-color);
    text-decoration: none;
    font-weight: 600;
}

.forgot-password:hover {
    text-decoration: underline;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background-color: var(--secondary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: var(--font-family);
}

.btn-submit:hover {
    background-color: var(--secondary-color-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
}

.btn-submit:active {
    transform: translateY(0);
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 2rem 0;
    color: #9ca3af;
    font-size: 0.9rem;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e5e7eb;
}

.divider span {
    padding: 0 1rem;
}

.social-login {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.btn-social {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    color: var(--dark-color);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-social:hover {
    border-color: #9ca3af;
    background-color: #f9fafb;
}

.btn-social i {
    font-size: 1.2rem;
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.auth-footer p {
    color: var(--text-color);
    margin-bottom: 0.5rem;
}

.auth-footer a {
    color: var(--secondary-color);
    font-weight: 600;
    text-decoration: none;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

@media (max-width: 768px) {
    .auth-page {
        padding: 2rem 0;
    }
    
    .auth-card {
        padding: 2rem 1.5rem;
    }
    
    .auth-header h1 {
        font-size: 1.75rem;
    }
}
</style>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your NestMyPet account</p>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" id="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Enter your email"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" id="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">
                    Sign In
                </button>
            </form>

        

            <div class="auth-footer">
                <p>Don't have an account?</p>
                <a href="auth_sign.php">Create a new account</a>
            </div>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
const togglePassword = document.getElementById('toggle-password');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    const icon = this.querySelector('i');
    if (type === 'password') {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
});

// Form validation
const loginForm = document.getElementById('login-form');
loginForm.addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all fields');
        return false;
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
});

// Auto-dismiss alerts
const alerts = document.querySelectorAll('.alert');
alerts.forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            alert.remove();
        }, 500);
    }, 5000);
});
</script>

<?php require_once 'footer.php'; ?>