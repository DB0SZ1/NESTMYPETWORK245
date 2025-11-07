<footer class="main-footer">
    <!-- Animated Pets Background Layer -->
    <div class="pets-background" id="pets-background"></div>
    
    <div class="container">
        <div class="footer-grid">
            <div class="footer-column footer-about-column">
                <div class="footer-logo">
                     <a href="index.php" class="logo">
                       <img src="newlogo.png" alt="NestMyPet Logo" />
                    </a>
                </div>
                <p class="footer-about">At NestMyPet, we match your furry friend with a loving, local host — someone who cares from the heart.</p>
            </div>
            
            <div class="footer-column footer-help-column">
                <h4><i class="fas fa-headset"></i> Need help? We're here for you.</h4>
                
                <p class="footer-help-tagline">Support that's friendly, fast & human.</p>
                <ul>
                    <li class="footer-phone">
                        <i class="fas fa-phone"></i>
                        <a href="tel:+1234567890">+1 (234) 567-890</a>
                    </li>
                    <li><i class="fa fa-building"></i><a href="about.php">ABOUT</a></li>
                    <li><i class="fas fa-envelope"></i><a href="contact.php">CONTACT US</a>
                    <li><i class="fas fa-question-circle"></i><a href="info.php?page=faqs">FAQs</a></li>
                    <li><i class="fas fa-life-ring"></i><a href="info.php?page=help">HELP CENTER</a></li>
                     
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Legal</h4>
                <ul>
                    <li><a href="info.php?page=terms">Terms</a></li>
                    <li><a href="info.php?page=privacy">Privacy</a></li>
                    <li><a href="info.php?page=cookies">Cookies</a></li>
                </ul>
            </div>
            
             <div class="footer-column">
                <h4>Follow Us</h4>
                <ul class="social-icons">
                    <li><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 NestMyPet. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
/* Enhanced Responsive Footer Styles with Animated Pets */
.main-footer {
    position: relative;
    background-color: var(--dark-color);
    color: #a0a0a0;
    padding: 4rem 0 2rem;
    margin-top: auto;
    overflow: hidden; /* Contain animated pets */
}

/* Animated Pets Background Layer */
.pets-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}

.pet-icon {
    position: absolute;
    font-size: 2.5rem;
    color: rgba(242, 148, 30, 0.15); /* Semi-transparent orange */
    transition: color 0.3s ease;
}

.pet-icon.bounce {
    animation: bounce-effect 0.3s ease-out;
}

@keyframes bounce-effect {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* Ensure footer content is above animated pets */
.main-footer .container {
    position: relative;
    z-index: 1;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr repeat(3, 1fr);
    gap: 2.5rem;
    margin-bottom: 3rem;
}

.footer-column h4 {
    color: var(--white-color);
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-column h4 i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.footer-column ul {
    list-style: none;
    padding: 0;
}

.footer-column li {
    margin-bottom: 0.8rem;
}

.footer-column a {
    color: #a0a0a0;
    transition: color 0.3s ease;
}

.footer-column a:hover {
    color: var(--white-color);
    text-decoration: underline;
}

/* Footer help column with enhanced spacing */
.footer-help-column {
    padding: 0 1rem;
}

.footer-help-column ul li {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-help-column ul li i {
    color: var(--primary-color);
    font-size: 1rem;
    min-width: 18px;
}

.footer-help-tagline {
    color: #a0a0a0;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    line-height: 1.6;
    font-style: italic;
}

.footer-phone {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-phone i {
    color: var(--primary-color);
    font-size: 1rem;
}

.footer-phone a {
    font-weight: 600;
    color: var(--white-color);
}

.footer-logo {
    margin-bottom: 1rem;
}

.footer-about {
    max-width: 300px;
    line-height: 1.8;
}

.social-icons {
    display: flex;
    gap: 1rem;
    padding: 0;
    flex-wrap: wrap;
}

.social-icons a {
    color: #a0a0a0;
    font-size: 1.3rem;
    transition: color 0.3s ease, transform 0.3s ease;
}

.social-icons a:hover {
    color: var(--primary-color);
    transform: translateY(-3px);
}

/* Enhanced contrast footer divider */
.footer-bottom {
    border-top: 2px solid #666;
    padding-top: 2rem;
    text-align: center;
    font-size: 0.9rem;
}

/* Tablet View (992px and below) */
@media (max-width: 992px) {
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
    
    .footer-help-column {
        grid-column: 1 / -1;
        text-align: center;
        max-width: 100%;
        padding: 0;
    }
    
    .footer-about-column {
        grid-column: 1 / -1;
        text-align: center;
        max-width: 100%;
    }
    
    .footer-about {
        max-width: 100%;
        margin: 0 auto;
    }
    
    .footer-logo {
        display: flex;
        justify-content: center;
    }
    
    .social-icons {
        justify-content: center;
    }
    
    .footer-help-column ul li {
        justify-content: center;
    }
    
    .pet-icon {
        font-size: 2rem; /* Smaller pets on tablet */
    }
}

/* Mobile View (768px and below) */
@media (max-width: 768px) {
    .main-footer {
        padding: 3rem 0 1.5rem;
    }
    
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
    }
    
    .footer-help-column {
        grid-column: 1;
    }
    
    .footer-about-column {
        grid-column: 1;
    }
    
    .footer-column {
        margin-bottom: 1rem;
    }
    
    .footer-column h4 {
        font-size: 1.2rem;
        margin-bottom: 1rem;
        justify-content: center;
    }
    
    .footer-phone {
        justify-content: center;
    }
    
    .footer-help-column ul li {
        justify-content: center;
    }
    
    .social-icons {
        justify-content: center;
    }
    
    .footer-bottom {
        padding-top: 1.5rem;
        font-size: 0.85rem;
    }
    
    .pet-icon {
        font-size: 1.5rem; /* Even smaller on mobile */
    }
}

/* Small Mobile View (480px and below) */
@media (max-width: 480px) {
    .main-footer {
        padding: 2rem 0 1rem;
    }
    
    .footer-grid {
        gap: 1.5rem;
    }
    
    .footer-logo img {
        height: 60px;
    }
    
    .footer-about {
        font-size: 0.9rem;
    }
    
    .footer-column h4 {
        font-size: 1.1rem;
        justify-content: center;
    }
    
    .footer-column li {
        margin-bottom: 0.6rem;
    }
    
    .footer-column a,
    .footer-phone a {
        font-size: 0.95rem;
    }
    
    .social-icons a {
        font-size: 1.2rem;
    }
    
    .pet-icon {
        font-size: 1.2rem; /* Smallest on small mobile */
    }
}
</style>

<script>
// Animated Pets Background with Collision Detection
(function() {
    const petsContainer = document.getElementById('pets-background');
    if (!petsContainer) return;
    
    // Font Awesome pet icons
    const petIcons = [
        'fas fa-dog',
        'fas fa-cat',
        'fas fa-paw',
        'fas fa-bone',
        'fas fa-fish',
        'fas fa-dove',
        'fas fa-crow',
        'fas fa-spider',
        'fas fa-horse',
        'fas fa-frog'
    ];
    
    const pets = [];
    const numPets = 20; // Number of floating pets
    
    // Pet class with position, velocity, and collision detection
    class Pet {
        constructor() {
            this.element = document.createElement('i');
            this.element.className = `pet-icon ${petIcons[Math.floor(Math.random() * petIcons.length)]}`;
            
            // Random starting position
            this.x = Math.random() * 100;
            this.y = Math.random() * 100;
            
            // Random velocity (slow movement)
            this.vx = (Math.random() - 0.5) * 0.05;
            this.vy = (Math.random() - 0.5) * 0.05;
            
            // Size for collision detection
            this.size = 40;
            
            this.element.style.left = this.x + '%';
            this.element.style.top = this.y + '%';
            
            petsContainer.appendChild(this.element);
        }
        
        update() {
            // Update position
            this.x += this.vx;
            this.y += this.vy;
            
            // Bounce off edges
            if (this.x <= 0 || this.x >= 100) {
                this.vx *= -1;
                this.x = Math.max(0, Math.min(100, this.x));
            }
            if (this.y <= 0 || this.y >= 100) {
                this.vy *= -1;
                this.y = Math.max(0, Math.min(100, this.y));
            }
            
            // Update DOM position
            this.element.style.left = this.x + '%';
            this.element.style.top = this.y + '%';
        }
        
        checkCollision(other) {
            const footerWidth = petsContainer.offsetWidth;
            const footerHeight = petsContainer.offsetHeight;
            
            const dx = (this.x - other.x) * footerWidth / 100;
            const dy = (this.y - other.y) * footerHeight / 100;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            return distance < this.size;
        }
        
        bounce(other) {
            // Simple elastic collision
            const tempVx = this.vx;
            const tempVy = this.vy;
            
            this.vx = other.vx;
            this.vy = other.vy;
            other.vx = tempVx;
            other.vy = tempVy;
            
            // Add bounce animation
            this.element.classList.add('bounce');
            other.element.classList.add('bounce');
            
            setTimeout(() => {
                this.element.classList.remove('bounce');
                other.element.classList.remove('bounce');
            }, 300);
        }
    }
    
    // Create pets
    for (let i = 0; i < numPets; i++) {
        pets.push(new Pet());
    }
    
    // Animation loop
    function animate() {
        // Update all pets
        pets.forEach(pet => pet.update());
        
        // Check collisions
        for (let i = 0; i < pets.length; i++) {
            for (let j = i + 1; j < pets.length; j++) {
                if (pets[i].checkCollision(pets[j])) {
                    pets[i].bounce(pets[j]);
                }
            }
        }
        
        requestAnimationFrame(animate);
    }
    
    animate();
})();
</script>