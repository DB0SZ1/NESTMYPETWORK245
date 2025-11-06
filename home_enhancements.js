/**
 * HOME ENHANCEMENTS
 * Infinite carousel animation, smooth scroll, intersection observers
 */

(function() {
    'use strict';

    // ===================================
    // INFINITE CAROUSEL DUPLICATION
    // ===================================
    function initCarousel() {
        const track = document.getElementById('features-track');
        if (!track) return;

        // Clone all items and append them for seamless infinite scroll
        const items = Array.from(track.children);
        items.forEach(item => {
            const clone = item.cloneNode(true);
            track.appendChild(clone);
        });
    }

    // ===================================
    // INTERSECTION OBSERVER FOR ANIMATIONS
    // ===================================
    function initScrollAnimations() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe sections for fade-in animations
        const animatableElements = document.querySelectorAll('.work-step, .testimonial-card, .blog-card, .guarantee-item');
        animatableElements.forEach(el => observer.observe(el));
    }

    // ===================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ===================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const headerOffset = 100;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // ===================================
    // HERO SEARCH FORM VALIDATION
    // ===================================
    function initSearchForm() {
        const searchForm = document.querySelector('.hero-search-form');
        if (!searchForm) return;

        searchForm.addEventListener('submit', function(e) {
            const serviceType = this.querySelector('[name="service_type"]');
            const location = this.querySelector('[name="location"]');

            let isValid = true;

            // Validate service type
            if (!serviceType.value) {
                showFormError(serviceType, 'Please select a service');
                isValid = false;
            }

            // Validate location
            if (!location.value.trim()) {
                showFormError(location, 'Please enter your location');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Clear errors on input
        const formInputs = searchForm.querySelectorAll('.form-control-hero');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearFormError(this);
            });
        });
    }

    function showFormError(input, message) {
        input.style.borderColor = '#dc2626';
        input.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
        
        // Remove existing error message
        const existingError = input.parentElement.querySelector('.form-error');
        if (existingError) existingError.remove();

        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        errorDiv.style.cssText = 'color: #dc2626; font-size: 0.85rem; margin-top: 0.5rem; position: absolute; bottom: -25px; left: 20px;';
        input.parentElement.appendChild(errorDiv);
    }

    function clearFormError(input) {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        const errorDiv = input.parentElement.querySelector('.form-error');
        if (errorDiv) errorDiv.remove();
    }

    // ===================================
    // TESTIMONIAL ROTATION (Optional)
    // ===================================
    function initTestimonialHighlight() {
        const testimonials = document.querySelectorAll('.testimonial-card');
        if (testimonials.length === 0) return;

        let currentIndex = 0;

        setInterval(() => {
            // Remove highlight from all
            testimonials.forEach(card => {
                card.style.transform = '';
                card.style.boxShadow = '';
            });

            // Add highlight to current
            const current = testimonials[currentIndex];
            current.style.transform = 'scale(1.02)';
            current.style.boxShadow = '0 12px 35px rgba(0, 0, 0, 0.15)';
            current.style.transition = 'all 0.5s ease';

            // Move to next
            currentIndex = (currentIndex + 1) % testimonials.length;
        }, 4000);
    }

    // ===================================
    // PARALLAX EFFECT ON HERO
    // ===================================
    function initParallax() {
        const heroBg = document.querySelector('.hero-image-bg');
        if (!heroBg) return;

        let ticking = false;

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const scrolled = window.pageYOffset;
                    const parallaxSpeed = 0.5;
                    heroBg.style.transform = `translateY(${scrolled * parallaxSpeed}px)`;
                    ticking = false;
                });
                ticking = true;
            }
        });
    }

    // ===================================
    // LAZY LOAD IMAGES
    // ===================================
    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // ===================================
    // ADD CSS ANIMATION CLASSES
    // ===================================
    function addAnimationStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .animate-in {
                animation: fadeInUp 0.6s ease-out forwards;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .form-error {
                animation: shake 0.3s ease;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }

            /* Smooth transitions */
            .work-step, .testimonial-card, .blog-card, .guarantee-item {
                opacity: 0;
                transform: translateY(30px);
                transition: opacity 0.6s ease, transform 0.6s ease;
            }

            .work-step.animate-in,
            .testimonial-card.animate-in,
            .blog-card.animate-in,
            .guarantee-item.animate-in {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(style);
    }

    // ===================================
    // INITIALIZE ALL
    // ===================================
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        // Run initialization functions
        addAnimationStyles();
        initCarousel();
        initScrollAnimations();
        initSmoothScroll();
        initSearchForm();
        initParallax();
        initLazyLoad();
        // initTestimonialHighlight(); // Optional - uncomment for rotating highlight

        console.log('🐾 NestMyPet home enhancements loaded');
    }

    // Start initialization
    init();

})();