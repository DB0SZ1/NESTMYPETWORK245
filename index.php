<?php
$pageTitle = "NestMyPet | Your Pet's Second Family";
require 'db.php';
include 'header.php';
?>

<style>
/* =========================================
   HOMEPAGE ENHANCEMENTS - INLINE STYLES
   Full-screen hero, animated carousel, enhanced testimonials
   ========================================= */

/* ===================================
   FULL-SCREEN HERO
   =================================== */
.hero-fullscreen {
    position: relative;
    height: 100vh;
    min-height: 600px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: linear-gradient(135deg, #f7941e 0%, #2ecc71 100%);
}

.hero-image-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=1600');
    background-size: cover;
    background-position: center;
    z-index: 1;
    animation: slowZoom 20s ease-in-out infinite alternate;
}

@keyframes slowZoom {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(1.1);
    }
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(247, 148, 30, 0.85) 0%, rgba(46, 204, 113, 0.75) 100%);
    z-index: 2;
}

.hero-container {
    position: relative;
    z-index: 3;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-content-center {
    text-align: center;
    max-width: 800px;
    padding: 2rem;
}

.hero-content-center h1 {
    font-size: 3.5rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.hero-lead {
    font-size: 1.3rem;
    color: #ffffff;
    line-height: 1.7;
    margin-bottom: 2.5rem;
    text-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
}

/* ===================================
   HERO SEARCH FORM
   =================================== */
.hero-search-form {
    display: flex;
    background: var(--white-color);
    border-radius: var(--border-radius);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    margin-top: 2.5rem;
    width: 100%;
    max-width: 700px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.form-group-hero {
    display: flex;
    align-items: center;
    flex: 1;
    position: relative;
}

.form-group-hero i {
    position: absolute;
    left: 20px;
    color: var(--text-color);
    font-size: 1.1rem;
    z-index: 10;
}

.form-control-hero {
    border: none;
    background: none;
    font-family: var(--font-family);
    font-size: 1rem;
    padding: 1.2rem 1rem 1.2rem 50px;
    width: 100%;
    color: var(--dark-color);
}

.form-control-hero:focus {
    outline: none;
}

.form-group-hero select.form-control-hero {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    font-weight: 600;
    background: #f8f9fa;
    border-right: 1px solid var(--border-color);
}

.btn-hero-search {
    background-color: var(--primary-color);
    color: var(--white-color);
    border: none;
    padding: 0 2rem;
    font-size: 1.5rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    flex-shrink: 0;
}

.btn-hero-search:hover {
    background-color: var(--primary-color-dark);
}

/* ===================================
   ANIMATED FEATURES CAROUSEL
   =================================== */
.features-carousel {
    background-color: #ffffff;
    padding: 2rem 0;
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
    overflow: hidden;
    position: relative;
}

.carousel-track-wrapper {
    width: 100%;
    overflow: hidden;
}

.carousel-track {
    display: flex;
    gap: 3rem;
    animation: scroll 30s linear infinite;
    width: max-content;
}

@keyframes scroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.feature-item-carousel {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--dark-color);
    font-weight: 600;
    font-size: 0.95rem;
    white-space: nowrap;
    flex-shrink: 0;
}

.feature-item-carousel i {
    font-size: 1.8rem;
    color: var(--primary-color);
}

/* ===================================
   SECTION STYLING & ALTERNATION
   =================================== */
.how-it-works {
    padding: 6rem 0;
    background-color: #f8f9fa;
}

.how-it-works h2 {
    text-align: center;
    font-size: 2.8rem;
    color: var(--dark-color);
    margin-bottom: 1rem;
}

.section-subtitle {
    text-align: center;
    font-size: 1.2rem;
    color: var(--text-color);
    margin-bottom: 3rem;
    font-weight: 400;
}

.works-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2.5rem;
    text-align: center;
}

.work-step {
    background-color: #ffffff;
    padding: 2.5rem 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.work-step:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.work-step-icon {
    margin-bottom: 1.5rem;
}

.work-step-icon i {
    font-size: 3.5rem;
    color: var(--primary-color);
}

.work-step h3 {
    font-size: 1.6rem;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
}

.work-step p {
    line-height: 1.7;
    color: var(--text-color);
}

/* Process illustration line animation */
.work-step::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.6s ease;
}

.work-step:hover::before {
    transform: scaleX(1);
}

/* ===================================
   GUARANTEE SECTION
   =================================== */
.guarantee {
    background-color: #ffffff;
    padding: 6rem 0;
    border-top: 4px solid var(--primary-color);
}

.guarantee-box {
    background-color: #fff3e6;
    padding: 3.5rem;
    border-radius: var(--border-radius);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    text-align: center;
    max-width: 1000px;
    margin: 0 auto;
    border: 2px solid var(--primary-color);
}

.guarantee-box h2 {
    font-size: 2.8rem;
    color: var(--dark-color);
    margin-bottom: 1.5rem;
}

.guarantee-box > p {
    max-width: 750px;
    margin: 0 auto 3rem auto;
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--text-color);
}

.guarantee-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2.5rem;
    margin-bottom: 3rem;
    text-align: center;
}

.guarantee-icon {
    margin-bottom: 1rem;
}

.guarantee-icon i {
    font-size: 3.5rem;
    color: var(--primary-color);
}

.guarantee-item h3 {
    font-size: 1.3rem;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
}

.guarantee-item p {
    font-size: 0.95rem;
    line-height: 1.6;
}

.guarantee-link {
    font-weight: 600;
    color: var(--dark-color);
    text-decoration: underline;
    font-size: 1rem;
}

.guarantee-link:hover {
    color: var(--primary-color);
}

/* ===================================
   TESTIMONIALS CAROUSEL
   =================================== */
.testimonials {
    padding: 6rem 0;
    background-color: #f8f9fa;
    border-top: 4px solid var(--secondary-color);
    position: relative;
    overflow: hidden;
}

.testimonials h2 {
    text-align: center;
    font-size: 2.8rem;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.testimonials .section-subtitle {
    text-align: center;
    font-size: 1.2rem;
    color: var(--text-color);
    margin-bottom: 3rem;
    font-weight: 400;
}

.testimonial-carousel-wrapper {
    position: relative;
    max-width: 100%;
    margin: 0 auto;
    padding: 0;
    overflow: hidden;
}

.testimonial-carousel-container {
    overflow: hidden;
    width: 100%;
    position: relative;
}

.testimonial-carousel-track {
    display: flex;
    gap: 2rem;
    animation: testimonialScroll 50s linear infinite;
    width: max-content;
    padding: 1rem 0;
}

@keyframes testimonialScroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.testimonial-carousel-track:hover {
    animation-play-state: paused;
}

/* Testimonial Card Styling */
.testimonial-card-carousel {
    background-color: var(--white-color);
    border-radius: var(--border-radius);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 2rem;
    width: 400px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left: 4px solid var(--secondary-color);
}

.testimonial-card-carousel:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

/* Testimonial Header */
.testimonial-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.testimonial-header img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--secondary-color);
    flex-shrink: 0;
}

.testimonial-info {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.testimonial-info strong {
    font-size: 1.1rem;
    color: var(--dark-color);
    font-weight: 600;
}

.testimonial-rating {
    display: flex;
    gap: 0.2rem;
}

.testimonial-rating i {
    color: #fbbf24;
    font-size: 0.9rem;
}

.testimonial-date {
    font-size: 0.85rem;
    color: #9ca3af;
}

/* Testimonial Content */
.testimonial-content p {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--text-color);
    margin: 0;
    font-style: italic;
}

/* Testimonial Footer */
.testimonial-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.pet-badge,
.service-badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-weight: 600;
}

.pet-badge {
    background-color: #d1fae5;
    color: #065f46;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.pet-badge i {
    font-size: 0.9rem;
}

.service-badge {
    background-color: #fef3c7;
    color: #92400e;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .testimonial-card-carousel {
        width: 350px;
        padding: 1.5rem;
    }

    .testimonial-header img {
        width: 60px;
        height: 60px;
    }

    .testimonial-info strong {
        font-size: 1rem;
    }

    .testimonial-content p {
        font-size: 0.95rem;
    }
}

/* ===================================
   BLOG SECTION WITH MOVING CAROUSEL
   =================================== */
.blog-section {
    padding: 6rem 0;
    background-color: #ffffff;
    border-top: 4px solid var(--primary-color);
    position: relative;
    overflow: hidden;
}

/* Playful pet illustrations peeking from top */
.blog-section::before {
    content: '🐾';
    position: absolute;
    top: -20px;
    left: 10%;
    font-size: 3rem;
    opacity: 0.3;
    animation: float 3s ease-in-out infinite;
}

.blog-section::after {
    content: '🐾';
    position: absolute;
    top: -15px;
    right: 15%;
    font-size: 2.5rem;
    opacity: 0.3;
    animation: float 4s ease-in-out infinite;
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.blog-section h2 {
    font-size: 2.8rem;
    color: var(--dark-color);
    margin-bottom: 1rem;
    text-align: center;
}

.blog-carousel-wrapper {
    position: relative;
    max-width: 100%;
    margin: 0 auto;
    padding: 0;
    overflow: hidden;
}

.blog-carousel-container {
    overflow: hidden;
    width: 100%;
    position: relative;
}

.blog-carousel-track {
    display: flex;
    gap: 2rem;
    animation: blogScroll 40s linear infinite;
    width: max-content;
    padding: 1rem 0;
}

@keyframes blogScroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.blog-carousel-track:hover {
    animation-play-state: paused;
}

.blog-card {
    background-color: var(--white-color);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    width: 350px;
    flex-shrink: 0;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.blog-card img {
    width: 100%;
    height: 280px;
    object-fit: cover;
}

.blog-card-content {
    padding: 2rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    min-height: 200px;
}

.blog-card-content h3 {
    font-size: 1.4rem;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.blog-card-content p {
    margin-bottom: 1.5rem;
    flex-grow: 1;
    line-height: 1.7;
    color: var(--text-color);
    font-size: 0.95rem;
}

.btn-outline-alt {
    align-self: flex-start;
    background-color: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
    padding: 10px 24px;
    border-radius: var(--border-radius);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-outline-alt:hover {
    background-color: var(--primary-color);
    color: var(--white-color);
}

/* Navigation arrows for blog carousel */
.blog-nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.blog-nav-btn {
    background-color: var(--primary-color);
    color: var(--white-color);
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(247, 148, 30, 0.3);
}

.blog-nav-btn:hover {
    background-color: var(--primary-color-dark);
    transform: scale(1.1);
}

.blog-nav-btn:active {
    transform: scale(0.95);
}

/* ===================================
   RESPONSIVE DESIGN
   =================================== */
@media (max-width: 992px) {
    .hero-content-center h1 {
        font-size: 2.8rem;
    }

    .hero-lead {
        font-size: 1.1rem;
    }

    .hero-search-form {
        flex-direction: column;
    }

    .form-group-hero select.form-control-hero {
        border-right: none;
        border-bottom: 1px solid var(--border-color);
    }

    .btn-hero-search {
        width: 100%;
        padding: 1.2rem;
    }

    .carousel-track {
        animation: scroll 20s linear infinite;
    }
}

@media (max-width: 768px) {
    .hero-fullscreen {
        min-height: 500px;
    }

    .hero-content-center {
        padding: 1.5rem;
    }

    .hero-content-center h1 {
        font-size: 2.2rem;
    }

    .hero-lead {
        font-size: 1rem;
    }

    .how-it-works,
    .guarantee,
    .testimonials,
    .blog-section {
        padding: 4rem 0;
    }

    .how-it-works h2,
    .guarantee-box h2,
    .testimonials h2,
    .blog-section h2 {
        font-size: 2.2rem;
    }

    .section-subtitle {
        font-size: 1rem;
    }

    .works-grid,
    .testimonial-grid,
    .blog-grid {
        gap: 2rem;
    }

    .guarantee-box {
        padding: 2rem;
    }

    .feature-item-carousel {
        font-size: 0.85rem;
    }

    .feature-item-carousel i {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .hero-content-center h1 {
        font-size: 1.8rem;
    }

    .hero-lead {
        font-size: 0.95rem;
        margin-bottom: 2rem;
    }

    .hero-search-form {
        margin-top: 1.5rem;
    }

    .form-control-hero {
        padding: 1rem 1rem 1rem 45px;
        font-size: 0.9rem;
    }

    .form-group-hero i {
        left: 15px;
        font-size: 1rem;
    }

    .works-grid,
    .testimonial-grid,
    .blog-grid {
        grid-template-columns: 1fr;
    }

    .work-step,
    .testimonial-card,
    .blog-card {
        padding: 1.5rem;
    }

    .guarantee-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* ===================================
   ANIMATION ENHANCEMENTS
   =================================== */
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

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Section fade-in classes */
.fade-in-section {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}

.fade-in-section.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Staggered animations for grid items */
.work-step,
.testimonial-card,
.guarantee-item {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.work-step.visible,
.testimonial-card.visible,
.guarantee-item.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Delay classes for staggered effect */
.work-step:nth-child(1) { transition-delay: 0.1s; }
.work-step:nth-child(2) { transition-delay: 0.2s; }
.work-step:nth-child(3) { transition-delay: 0.3s; }

.guarantee-item:nth-child(1) { transition-delay: 0.1s; }
.guarantee-item:nth-child(2) { transition-delay: 0.2s; }
.guarantee-item:nth-child(3) { transition-delay: 0.3s; }

.testimonial-card:nth-child(1) { transition-delay: 0.1s; }
.testimonial-card:nth-child(2) { transition-delay: 0.2s; }
.testimonial-card:nth-child(3) { transition-delay: 0.3s; }

/* Pause carousel animation on hover */
.carousel-track:hover {
    animation-play-state: paused;
}

/* ===================================
   SMOOTH SCROLL BEHAVIOR
   =================================== */
html {
    scroll-behavior: smooth;
}

/* ===================================
   ACCESSIBILITY ENHANCEMENTS
   =================================== */
.btn-hero-search:focus,
.form-control-hero:focus,
.btn-outline-alt:focus {
    outline: 3px solid var(--secondary-color);
    outline-offset: 2px;
}
</style>

<main>
    <!-- FULL-SCREEN HERO with Background Image (Pet + Owner for emotional connection) -->
    <section class="hero-fullscreen">
        <div class="hero-image-bg"></div>
        <div class="hero-overlay"></div>
        <div class="container hero-container">
            <div class="hero-content-center">
                <h1>Your pet's second family is waiting just around the corner</h1>
                <p class="hero-lead">Finding someone who truly cares for your furry friend shouldn't feel like a chore. At NestMyPet, we connect you with loving, local hosts who treat pets like family — because every tail wag, purr, and happy bark matters.</p>
                
                <!-- Hero Search Form -->
                <form action="search.php" method="GET" class="hero-search-form">
                    <div class="form-group-hero">
                        <i class="fas fa-paw"></i>
                        <select name="service_type" class="form-control-hero" required>
                            <option value="">What does your pet need?</option>
                            <option value="boarding">Boarding</option>
                            <option value="sitting">House Sitting</option>
                            <option value="daycare">Doggy Day Care</option>
                            <option value="walking">Dog Walking</option>
                        </select>
                    </div>
                    <div class="form-group-hero">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="location" class="form-control-hero" placeholder="Where are you located?" required>
                    </div>
                    <button type="submit" class="btn-hero-search">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- ANIMATED FEATURES CAROUSEL -->
    <section class="features-carousel">
        <div class="carousel-track-wrapper">
            <div class="carousel-track" id="features-track">
                <!-- Items will be duplicated via JS for infinite scroll -->
                <div class="feature-item-carousel">
                    <i class="fas fa-shield-alt"></i>
                    <span>Verified & Background-Checked Hosts</span>
                </div>
                <div class="feature-item-carousel">
                    <i class="fas fa-star"></i>
                    <span>Reviewed by Real Pet Owners</span>
                </div>
                <div class="feature-item-carousel">
                    <i class="fas fa-home"></i>
                    <span>Home-to-Home Personal Care</span>
                </div>
                <div class="feature-item-carousel">
                    <i class="fas fa-check-circle"></i>
                    <span>Basic DBS Checks for Peace of Mind</span>
                </div>
                <div class="feature-item-carousel">
                    <i class="fas fa-heart"></i>
                    <span>Loving Hosts Who Truly Care</span>
                </div>
                <div class="feature-item-carousel">
                    <i class="fas fa-camera"></i>
                    <span>Daily Photo Updates</span>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS with Alternating Background & Filled Icons -->
    <section class="how-it-works fade-in-section">
        <div class="container">
            <h2>How NestMyPet Works</h2>
            <p class="section-subtitle">Finding the perfect sitter is as easy as 1, 2, 3</p>
            <div class="works-grid">
                <div class="work-step">
                    <div class="work-step-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>1. Connect & Chat</h3>
                    <p>Browse local hosts in your area and chat freely. Ask all your questions, share your pet's favorite treats and quirks, and find someone who feels just right.</p>
                </div>
                <div class="work-step">
                    <div class="work-step-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>2. Book with Confidence</h3>
                    <p>Once you've found the perfect match, secure your booking knowing your pet is in safe, caring hands. Our NestMyPet Guarantee has your back.</p>
                </div>
                <div class="work-step">
                    <div class="work-step-icon">
                        <i class="fas fa-smile-beam"></i>
                    </div>
                    <h3>3. Relax & Stay Connected</h3>
                    <p>Head off worry-free! Get daily photo updates and messages so you can see your pet's adventures. It's like getting postcards from a best friend.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- NESTMYPET GUARANTEE with Enhanced Divider & Filled Icons -->
    <section class="guarantee fade-in-section">
        <div class="container">
            <div class="guarantee-box">
                <h2>The NestMyPet Guarantee</h2>
                <p>Your peace of mind is everything to us. Every single booking is backed by our promise to support you if things don't go to plan — because your beloved pet deserves nothing less than the best care possible.</p>
                <div class="guarantee-grid">
                    <div class="guarantee-item">
                        <div class="guarantee-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h3>Emergency Vet Cover</h3>
                        <p>Up to £500 for treatment due to sitter negligence or accidental injury during care</p>
                    </div>
                    <div class="guarantee-item">
                        <div class="guarantee-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Booking Protection</h3>
                        <p>Platform credit if a sitter cancels last-minute or fails to provide the agreed care</p>
                    </div>
                    <div class="guarantee-item">
                        <div class="guarantee-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3>Property Protection</h3>
                        <p>Up to £250 towards any damage caused by your pet while under active care</p>
                    </div>
                </div>
                <a href="guarantee-policy.php" class="guarantee-link">Read the full NestMyPet Guarantee Policy →</a>
            </div>
        </div>
    </section>
<!-- TESTIMONIALS CAROUSEL -->
    <section class="testimonials fade-in-section">
        <div class="container">
            <h2>Don't just take our word for it</h2>
            <p class="section-subtitle">Hear from pet parents who've found their perfect match</p>
            
            <div class="testimonial-carousel-wrapper">
                <div class="testimonial-carousel-container">
                    <div class="testimonial-carousel-track" id="testimonial-carousel-track">
                        
                        <!-- Testimonial Card 1 -->
                        <div class="testimonial-card-carousel">
                            <div class="testimonial-header">
                                <img src="https://i.pravatar.cc/80?img=47" alt="Jessica Martinez" onerror="this.src='https://ui-avatars.com/api/?name=Jessica+Martinez&background=2ecc71&color=fff&size=80'">
                                <div class="testimonial-info">
                                    <strong>Jessica Martinez</strong>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="testimonial-date">3 weeks ago</span>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <p>"Absolutely fantastic! Sarah looked after our dog, Buster, like he was her own. We received daily photo updates and came back to one very happy, well-exercised pup. We'll definitely be using NestMyPet again!"</p>
                            </div>
                            <div class="testimonial-footer">
                                <span class="pet-badge"><i class="fas fa-dog"></i> Buster</span>
                                <span class="service-badge">Boarding • 7 days</span>
                            </div>
                        </div>

                        <!-- Testimonial Card 2 -->
                        <div class="testimonial-card-carousel">
                            <div class="testimonial-header">
                                <img src="https://i.pravatar.cc/80?img=33" alt="David Lewis" onerror="this.src='https://ui-avatars.com/api/?name=David+Lewis&background=f7941e&color=fff&size=80'">
                                <div class="testimonial-info">
                                    <strong>David Lewis</strong>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="testimonial-date">1 month ago</span>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <p>"Honestly the best platform I've found for pet sitters. The NestMyPet Guarantee gave me so much peace of mind. Our sitter Tom was verified, friendly, and clearly a true animal lover. Max had a wonderful stay!"</p>
                            </div>
                            <div class="testimonial-footer">
                                <span class="pet-badge"><i class="fas fa-dog"></i> Max</span>
                                <span class="service-badge">House Sitting • 5 days</span>
                            </div>
                        </div>

                        <!-- Testimonial Card 3 -->
                        <div class="testimonial-card-carousel">
                            <div class="testimonial-header">
                                <img src="https://i.pravatar.cc/80?img=45" alt="Anna Kowalski" onerror="this.src='https://ui-avatars.com/api/?name=Anna+Kowalski&background=2c3e50&color=fff&size=80'">
                                <div class="testimonial-info">
                                    <strong>Anna Kowalski</strong>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="testimonial-date">2 weeks ago</span>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <p>"I was nervous about leaving my cat Luna for the first time, but the whole process was seamless. Finding and connecting with a host near me was easy and completely free. Can't recommend NestMyPet enough!"</p>
                            </div>
                            <div class="testimonial-footer">
                                <span class="pet-badge"><i class="fas fa-cat"></i> Luna</span>
                                <span class="service-badge">Cat Sitting • 3 days</span>
                            </div>
                        </div>

                        <!-- Testimonial Card 4 -->
                        <div class="testimonial-card-carousel">
                            <div class="testimonial-header">
                                <img src="https://i.pravatar.cc/80?img=28" alt="Sophie Reynolds" onerror="this.src='https://ui-avatars.com/api/?name=Sophie+Reynolds&background=2ecc71&color=fff&size=80'">
                                <div class="testimonial-info">
                                    <strong>Sophie Reynolds</strong>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="testimonial-date">3 days ago</span>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <p>"The care my rabbit received was incredible! Emma went above and beyond, sending videos of playtime and making sure his diet was perfect. I felt completely at ease during my trip."</p>
                            </div>
                            <div class="testimonial-footer">
                                <span class="pet-badge"><i class="fas fa-paw"></i> Thumper</span>
                                <span class="service-badge">Pet Sitting • 10 days</span>
                            </div>
                        </div>

                        <!-- Testimonial Card 5 -->
                        <div class="testimonial-card-carousel">
                            <div class="testimonial-header">
                                <img src="https://i.pravatar.cc/80?img=38" alt="Michael Brown" onerror="this.src='https://ui-avatars.com/api/?name=Michael+Brown&background=f7941e&color=fff&size=80'">
                                <div class="testimonial-info">
                                    <strong>Michael Brown</strong>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="testimonial-date">1 week ago</span>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <p>"As a first-time user, I was impressed by how easy the platform is to navigate. Found a wonderful sitter within minutes, and my cats were so happy when I returned. Highly recommend!"</p>
                            </div>
                            <div class="testimonial-footer">
                                <span class="pet-badge"><i class="fas fa-cat"></i> Whiskers & Shadow</span>
                                <span class="service-badge">Cat Sitting • 4 days</span>
                            </div>
                        </div>

                        <!-- Testimonial Card 6 -->
                        <div class="testimonial-card-carousel">
                            <div class="testimonial-header">
                                <img src="https://i.pravatar.cc/80?img=22" alt="Emily Thompson" onerror="this.src='https://ui-avatars.com/api/?name=Emily+Thompson&background=2c3e50&color=fff&size=80'">
                                <div class="testimonial-info">
                                    <strong>Emily Thompson</strong>
                                    <div class="testimonial-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="testimonial-date">2 months ago</span>
                                </div>
                            </div>
                            <div class="testimonial-content">
                                <p>"Outstanding service! The daily updates with photos put my mind completely at ease. Our sitter treated our golden retriever like royalty. We've found our go-to pet sitter!"</p>
                            </div>
                            <div class="testimonial-footer">
                                <span class="pet-badge"><i class="fas fa-dog"></i> Bailey</span>
                                <span class="service-badge">Boarding • 14 days</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
                  

 
    <!-- BLOG SECTION with Multiple Articles & Moving Carousel -->
    <section class="blog-section fade-in-section" id="blog">
        <div class="container">
            <h2>Tales from the NestMyPet Community</h2>
            <p class="section-subtitle">Tips, stories, and advice from fellow pet lovers</p>
            
            <div class="blog-carousel-wrapper">
                <div class="blog-carousel-container">
                    <div class="blog-carousel-track" id="blog-carousel-track">
                        
                        <?php
                        $blog_posts = [];
                        try {
                            if (isset($pdo) && $pdo instanceof PDO) {
                                $stmt = $pdo->query("SELECT id, title, snippet, image_url FROM blog_posts ORDER BY created_at DESC LIMIT 8");
                                $blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } else {
                                error_log("Database connection (\$pdo) not available in index.php");
                            }
                        } catch (PDOException $e) {
                            error_log("Error fetching blog posts for index: " . $e->getMessage());
                        }
                        ?>

                        <?php if (empty($blog_posts)): ?>
                            <!-- Fallback sample posts if no database posts -->
                            <article class="blog-card">
                                <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=600" alt="Pet care tips">
                                <div class="blog-card-content">
                                    <h3>10 Signs Your Pet Really Loves Their Sitter</h3>
                                    <p>Learn how to tell when your furry friend has found their perfect match. From excited tail wags to peaceful naps, these signs show true comfort.</p>
                                    <a href="#" class="btn btn-outline-alt">Read more</a>
                                </div>
                            </article>
                            <article class="blog-card">
                                <img src="https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=600" alt="Dog walking">
                                <div class="blog-card-content">
                                    <h3>First Time Using a Pet Sitter? Here's What to Expect</h3>
                                    <p>Nervous about leaving your pet for the first time? We've got you covered with everything you need to know for a smooth experience.</p>
                                    <a href="#" class="btn btn-outline-alt">Read more</a>
                                </div>
                            </article>
                            <article class="blog-card">
                                <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=600" alt="Pet boarding">
                                <div class="blog-card-content">
                                    <h3>How to Prepare Your Pet for Boarding</h3>
                                    <p>Make your pet's stay away from home stress-free with these essential preparation tips from experienced pet sitters.</p>
                                    <a href="#" class="btn btn-outline-alt">Read more</a>
                                </div>
                            </article>
                            <article class="blog-card">
                                <img src="https://images.unsplash.com/photo-1415369629372-26f2fe60c467?w=600" alt="Cat sitting">
                                <div class="blog-card-content">
                                    <h3>Cat Sitting 101: What Makes Felines Happy</h3>
                                    <p>Cats have unique needs! Discover what makes them purr with contentment when you're away from home.</p>
                                    <a href="#" class="btn btn-outline-alt">Read more</a>
                                </div>
                            </article>
                            <article class="blog-card">
                                <img src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600" alt="Pet safety">
                                <div class="blog-card-content">
                                    <h3>Safety First: Questions to Ask Your Pet Sitter</h3>
                                    <p>Build confidence and trust by asking the right questions. Here's our checklist for finding the perfect sitter.</p>
                                    <a href="#" class="btn btn-outline-alt">Read more</a>
                                </div>
                            </article>
                            <article class="blog-card">
                                <img src="https://images.unsplash.com/photo-1623387641168-d9803ddd3f35?w=600" alt="Happy pets">
                                <div class="blog-card-content">
                                    <h3>Real Stories: How NestMyPet Changed Our Lives</h3>
                                    <p>Heartwarming tales from pet owners who found their perfect match through our community platform.</p>
                                    <a href="#" class="btn btn-outline-alt">Read more</a>
                                </div>
                            </article>
                        <?php else: ?>
                            <?php foreach ($blog_posts as $post): ?>
                                <article class="blog-card">
                                    <img src="<?php echo htmlspecialchars($post['image_url'] ?: 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=600'); ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                         onerror="this.src='https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=600';">
                                    <div class="blog-card-content">
                                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($post['snippet'] ?: 'Read this insightful article from our pet care community...'); ?></p>
                                        <a href="article.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-alt">Read more</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include 'footer.php'; ?>

<script>
/**
 * HOME ENHANCEMENTS
 * Infinite carousel animation, smooth scroll, intersection observers, fade-in animations
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
    // BLOG CAROUSEL DUPLICATION & ANIMATION
    // ===================================
    function initBlogCarousel() {
        const track = document.getElementById('blog-carousel-track');
        if (!track) return;

        // Clone all blog cards for infinite scroll
        const cards = Array.from(track.children);
        
        // Only duplicate if we have cards
        if (cards.length > 0) {
            cards.forEach(card => {
                const clone = card.cloneNode(true);
                track.appendChild(clone);
            });
        }
    }
    // ===================================
    // TESTIMONIAL CAROUSEL DUPLICATION
    // ===================================
    function initTestimonialCarousel() {
        const track = document.getElementById('testimonial-carousel-track');
        if (!track) return;

        // Clone all testimonial cards for infinite scroll
        const cards = Array.from(track.children);
        
        // Only duplicate if we have cards
        if (cards.length > 0) {
            cards.forEach(card => {
                const clone = card.cloneNode(true);
                track.appendChild(clone);
            });
        }
    }

    // ===================================
    // FADE-IN ANIMATIONS ON SCROLL
    // ===================================
    function initFadeInAnimations() {
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -100px 0px',
            threshold: 0.15
        };

        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe all sections with fade-in class
        const fadeInSections = document.querySelectorAll('.fade-in-section');
        fadeInSections.forEach(section => fadeObserver.observe(section));

        // Observe individual items for staggered animation
        const animatableItems = document.querySelectorAll('.work-step, .testimonial-card, .guarantee-item');
        
        const itemObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Don't unobserve so animation can repeat if scrolled back
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -80px 0px',
            threshold: 0.2
        });

        animatableItems.forEach(item => itemObserver.observe(item));
    }

    // ===================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ===================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#' || href === '#!') return;

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
            
            input.addEventListener('change', function() {
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
        errorDiv.style.cssText = 'color: #dc2626; font-size: 0.85rem; margin-top: 0.5rem; position: absolute; bottom: -25px; left: 20px; font-weight: 600;';
        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(errorDiv);
    }

    function clearFormError(input) {
        input.style.borderColor = '';
        input.style.boxShadow = '';
        const errorDiv = input.parentElement.querySelector('.form-error');
        if (errorDiv) errorDiv.remove();
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
                    
                    // Only apply parallax in hero section
                    if (scrolled < window.innerHeight) {
                        heroBg.style.transform = `translateY(${scrolled * parallaxSpeed}px) scale(1.1)`;
                    }
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
    // CAROUSEL PAUSE/PLAY ON HOVER
    // ===================================
    function initCarouselControls() {
        // Features carousel
        const featuresTrack = document.getElementById('features-track');
        if (featuresTrack) {
            featuresTrack.addEventListener('mouseenter', () => {
                featuresTrack.style.animationPlayState = 'paused';
            });
            featuresTrack.addEventListener('mouseleave', () => {
                featuresTrack.style.animationPlayState = 'running';
            });
        }

        // Blog carousel
        const blogTrack = document.getElementById('blog-carousel-track');
        if (blogTrack) {
            blogTrack.addEventListener('mouseenter', () => {
                blogTrack.style.animationPlayState = 'paused';
            });
            blogTrack.addEventListener('mouseleave', () => {
                blogTrack.style.animationPlayState = 'running';
            });
        }
    }

    // ===================================
    // ADD DYNAMIC CSS FOR ANIMATIONS
    // ===================================
    function addAnimationStyles() {
        const style = document.createElement('style');
        style.textContent = `
            /* Shake animation for form errors */
            .form-error {
                animation: shake 0.3s ease;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }

            /* Pulse animation for CTA buttons */
            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(247, 148, 30, 0.7); }
                70% { box-shadow: 0 0 0 10px rgba(247, 148, 30, 0); }
                100% { box-shadow: 0 0 0 0 rgba(247, 148, 30, 0); }
            }

            .btn-hero-search {
                animation: pulse 2s infinite;
            }

            /* Smooth transitions for all interactive elements */
            a, button, .blog-card, .work-step, .testimonial-card {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        `;
        document.head.appendChild(style);
    }

    // ===================================
    // SECTION REVEAL ON SCROLL
    // ===================================
    function initSectionReveals() {
        const sections = document.querySelectorAll('section');
        
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            revealObserver.observe(section);
        });
    }

    // ===================================
    // PERFORMANCE: REQUEST IDLE CALLBACK
    // ===================================
    function scheduleIdleTask(callback) {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(callback);
        } else {
            setTimeout(callback, 1);
        }
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

        // Run critical initialization functions immediately
        addAnimationStyles();
        initCarousel();
        initBlogCarousel();
        initTestimonialCarousel();
        initFadeInAnimations();
        initSmoothScroll();
        initSearchForm();

        // Schedule non-critical tasks
        scheduleIdleTask(() => {
            initParallax();
            initLazyLoad();
            initCarouselControls();
            initSectionReveals();
        });

        console.log('🐾 NestMyPet home enhancements loaded');
    }

    // Start initialization
    init();

})();
</script>