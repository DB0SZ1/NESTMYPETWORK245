<?php
$pageTitle = 'Sign Up - NestMyPet';
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
    max-width: 580px;
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

.form-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.form-header h2 {
    font-size: 2.2rem;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
}

.form-header p {
    color: var(--text-color);
    font-size: 1.05rem;
}

/* Role Selection Styles */
.role-selection {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin: 2.5rem 0;
}

.role-box {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    padding: 2rem;
    border: 3px solid #e5e7eb;
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-color);
    transition: all 0.3s ease;
    background: white;
    cursor: pointer;
}

.role-box:hover {
    border-color: var(--secondary-color);
    background-color: #f0fdf4;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(46, 204, 113, 0.2);
}

.role-box:active {
    transform: translateY(-2px);
}

.role-box i {
    font-size: 3rem;
    color: var(--secondary-color);
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f0fdf4;
    border-radius: 12px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.role-box:hover i {
    background-color: var(--secondary-color);
    color: white;
    transform: scale(1.1);
}

.role-box-content {
    flex: 1;
}

.role-box-content h3 {
    margin: 0 0 0.5rem 0;
    color: var(--dark-color);
    font-size: 1.4rem;
    font-weight: 700;
}

.role-box-content p {
    margin: 0;
    font-size: 1rem;
    line-height: 1.5;
    color: #6b7280;
}

.role-box .arrow-icon {
    font-size: 1.5rem;
    color: #d1d5db;
    transition: all 0.3s ease;
}

.role-box:hover .arrow-icon {
    color: var(--secondary-color);
    transform: translateX(5px);
}

.form-footer {
    text-align: center;
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.form-footer p {
    color: var(--text-color);
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.form-footer a {
    color: var(--secondary-color);
    font-weight: 600;
    text-decoration: none;
    font-size: 1rem;
}

.form-footer a:hover {
    text-decoration: underline;
}

/* Additional Info Section */
.info-section {
    margin-top: 2rem;
    padding: 1.5rem;
    background-color: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.info-section h4 {
    font-size: 0.95rem;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.info-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-section li {
    padding: 0.5rem 0;
    color: #6b7280;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-section li i {
    color: var(--secondary-color);
    font-size: 0.85rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.alert-info {
    background-color: #dbeafe;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}

@media (max-width: 768px) {
    .auth-page {
        padding: 2rem 0;
    }
    
    .auth-card {
        padding: 2rem 1.5rem;
    }
    
    .form-header h2 {
        font-size: 1.75rem;
    }
    
    .role-box {
        padding: 1.5rem;
        gap: 1.25rem;
    }
    
    .role-box i {
        font-size: 2.5rem;
        width: 50px;
        height: 50px;
    }
    
    .role-box-content h3 {
        font-size: 1.2rem;
    }
    
    .role-box-content p {
        font-size: 0.9rem;
    }
}
</style>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            
            <div class="form-header">
                <h2>Join NestMyPet</h2>
                <p>First, tell us who you are.</p>
            </div>
            
            <?php if (isset($_SESSION['info_message'])): ?>
                <div class="alert alert-info">
                    <?php 
                    echo htmlspecialchars($_SESSION['info_message']);
                    unset($_SESSION['info_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="role-selection">
                <a href="signup_owner.php" class="role-box">
                    <i class="fas fa-paw"></i>
                    <div class="role-box-content">
                        <h3>I'm a Pet Owner</h3>
                        <p>I want to find trusted care for my pets.</p>
                    </div>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </a>
                
                <a href="signup_sitter.php" class="role-box">
                    <i class="fas fa-home"></i>
                    <div class="role-box-content">
                        <h3>I want to be a Sitter</h3>
                        <p>I want to provide care for pets and earn money.</p>
                    </div>
                    <i class="fas fa-arrow-right arrow-icon"></i>
                </a>
            </div>

         

            <div class="form-footer">
                <p>Already have an account?</p>
                <a href="auth.php">Sign in here</a>
            </div>

        </div>
    </div>
</div>

<script>
// Add hover effect sound feedback (optional)
const roleBoxes = document.querySelectorAll('.role-box');
roleBoxes.forEach(box => {
    box.addEventListener('mouseenter', function() {
        this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    });
});

// Track analytics (optional - for tracking which role is more popular)
roleBoxes.forEach(box => {
    box.addEventListener('click', function(e) {
        const role = this.querySelector('h3').textContent;
        console.log('User selected role:', role);
        // You can send this to your analytics service
    });
});
</script>

<?php require_once 'footer.php'; ?>