<?php
$pageTitle = "NestMyPet | About";
require 'db.php';
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - NestMyPet</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        /* Hero Section */
        .about-hero {
            background: #2ecc71;
            color: #ffffff;
            padding: 8rem 0 6rem;
            position: relative;
            overflow: hidden;
        }
        
        .about-hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -15%;
            width: 800px;
            height: 800px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }
        
        .about-hero::after {
            content: '🐾';
            position: absolute;
            bottom: 10%;
            left: 10%;
            font-size: 8rem;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.1; }
            50% { transform: scale(1.2); opacity: 0.15; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }
        
        .about-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .about-hero-content .section-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }
        
        .about-hero-content h1 {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #ffffff;
            font-weight: 800;
            line-height: 1.1;
        }
        
        .about-hero-content p {
            font-size: 1.3rem;
            line-height: 1.8;
            opacity: 0.95;
            max-width: 750px;
            margin: 0 auto;
        }

        /* Animated Stats Section */
        .stats-section {
            padding: 0;
            background: #ffffff;
            margin-top: -4rem;
            position: relative;
            z-index: 3;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            max-width: 1200px;
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-radius: 16px;
            overflow: hidden;
        }
        
        .stat-item {
            text-align: center;
            padding: 3rem 2rem;
            background: #ffffff;
            position: relative;
            transition: all 0.4s ease;
        }
        
        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #F2941E;
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .stat-item:nth-child(even)::before {
            background: #2ecc71;
        }
        
        .stat-item:hover::before {
            transform: scaleX(1);
        }
        
        .stat-item:hover {
            transform: translateY(-10px);
            background: #fef9f3;
        }
        
        .stat-item:nth-child(even):hover {
            background: #f0fdf4;
        }
        
        .stat-item:not(:last-child) {
            border-right: 1px solid #e0e0e0;
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            background: #F2941E;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #ffffff;
            transition: all 0.4s ease;
        }
        
        .stat-item:nth-child(even) .stat-icon {
            background: #2ecc71;
        }
        
        .stat-item:hover .stat-icon {
            transform: rotateY(360deg) scale(1.1);
        }
        
        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            color: #F2941E;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .stat-item:nth-child(even) .stat-number {
            color: #2ecc71;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #555;
            font-weight: 600;
        }

        /* Story Section with Image Gallery */
        .story-section {
            padding: 8rem 0;
            background: #ffffff;
            position: relative;
        }
        
        .story-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
        }
        
        .story-content h2 {
            font-size: 3rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            font-weight: 700;
        }
        
        .story-content h2 .highlight {
            color: #F2941E;
            font-weight: 900;
        }
        
        .story-content p {
            font-size: 1.15rem;
            line-height: 1.9;
            color: #555;
            margin-bottom: 1.5rem;
        }
        
        .story-content p .bold-green {
            color: #2ecc71;
            font-weight: 700;
        }
        
        .story-content p .bold-orange {
            color: #F2941E;
            font-weight: 700;
        }
        
        .story-features {
            margin-top: 2rem;
            display: grid;
            gap: 1rem;
        }
        
        .story-feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .story-feature-item:hover {
            background: #fef9f3;
            transform: translateX(10px);
        }
        
        .story-feature-item:nth-child(2):hover {
            background: #f0fdf4;
        }
        
        .story-feature-item i {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F2941E;
            color: #ffffff;
            border-radius: 12px;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .story-feature-item:nth-child(2) i {
            background: #2ecc71;
        }
        
        .story-feature-item:nth-child(3) i {
            background: #F2941E;
        }
        
        .story-feature-item span {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .story-images {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            position: relative;
        }
        
        .story-image-card {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transition: all 0.5s ease;
        }
        
        .story-image-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        }
        
        .story-image-card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .story-image-card:hover img {
            transform: scale(1.1);
        }
        
        .story-image-card:nth-child(1) {
            grid-column: 1 / 2;
            margin-top: 2rem;
        }
        
        .story-image-card:nth-child(2) {
            grid-column: 2 / 3;
        }
        
        .story-image-card:nth-child(3) {
            grid-column: 1 / 3;
        }

        /* Values Section */
        .values-section {
            padding: 8rem 0;
            background: #f8f9fa;
            position: relative;
        }
        
        .values-section::before {
            content: '🐕';
            position: absolute;
            top: 5%;
            right: 5%;
            font-size: 10rem;
            opacity: 0.05;
        }
        
        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
        }
        
        .section-header .section-badge {
            display: inline-block;
            background: #2ecc71;
            color: #ffffff;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }
        
        .section-header h2 {
            font-size: 3rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .section-header p {
            font-size: 1.15rem;
            color: #555;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .value-card {
            background: #ffffff;
            padding: 3rem;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.5s ease;
            border: 2px solid transparent;
        }
        
        .value-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: #2ecc71;
        }
        
        .value-card:nth-child(odd):hover {
            border-color: #F2941E;
        }
        
        .value-card-content {
            position: relative;
            z-index: 2;
        }
        
        .value-icon {
            width: 90px;
            height: 90px;
            margin-bottom: 1.5rem;
            background: #F2941E;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #ffffff;
            transition: all 0.5s ease;
        }
        
        .value-card:nth-child(even) .value-icon {
            background: #2ecc71;
        }
        
        .value-card:hover .value-icon {
            transform: rotateY(360deg) scale(1.1);
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.4);
        }
        
        .value-card:nth-child(odd):hover .value-icon {
            box-shadow: 0 10px 30px rgba(242, 148, 30, 0.4);
        }
        
        .value-card h3 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .value-card p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #555;
        }
        
        .value-card p .bold-green {
            color: #2ecc71;
            font-weight: 700;
        }
        
        .value-card p .bold-orange {
            color: #F2941E;
            font-weight: 700;
        }

        /* Testimonials Carousel Section */
        .testimonials-section {
            padding: 8rem 0;
            background: #ffffff;
            overflow: hidden;
        }

        .testimonials-section .section-header {
            margin-bottom: 3rem;
        }

        .carousel-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            padding: 2rem 0;
        }

        .carousel-track {
            display: flex;
            gap: 2rem;
            animation: scroll 30s linear infinite;
            width: fit-content;
        }

        .carousel-track:hover {
            animation-play-state: paused;
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        .testimonial-card {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            min-width: 400px;
            max-width: 400px;
            border: 2px solid #f8f9fa;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: #2ecc71;
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #2ecc71;
        }

        .testimonial-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .testimonial-info h4 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .testimonial-info p {
            font-size: 0.9rem;
            color: #555;
            margin: 0;
        }

        .testimonial-rating {
            display: flex;
            gap: 0.25rem;
            margin-bottom: 1rem;
        }

        .testimonial-rating i {
            color: #F2941E;
            font-size: 1rem;
        }

        .testimonial-text {
            font-size: 1rem;
            line-height: 1.7;
            color: #555;
            font-style: italic;
        }

        .testimonial-pet {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f8f9fa;
            font-size: 0.9rem;
            color: #2ecc71;
            font-weight: 600;
        }

        /* Team Section */
        .team-section {
            padding: 8rem 0;
            background: #f8f9fa;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .team-member {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.5s ease;
        }

        .team-member:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .team-photo {
            width: 100%;
            height: 300px;
            overflow: hidden;
            position: relative;
        }

        .team-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .team-member:hover .team-photo img {
            transform: scale(1.1);
        }

        .team-info {
            padding: 2rem;
            text-align: center;
        }

        .team-info h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .team-info .role {
            color: #2ecc71;
            font-weight: 600;
            margin-bottom: 1rem;
            display: block;
        }

        .team-info p {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.6;
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .team-social a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            transition: all 0.3s ease;
        }

        .team-social a:hover {
            background: #2ecc71;
            color: #ffffff;
            transform: translateY(-3px);
        }

        /* CTA Section */
        .cta-section {
            padding: 8rem 0;
            background: #F2941E;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: pulse 10s ease-in-out infinite;
        }
        
        .cta-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation: pulse 12s ease-in-out infinite reverse;
        }
        
        .cta-content {
            text-align: center;
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .cta-content h2 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: #ffffff;
            font-weight: 800;
            line-height: 1.2;
        }
        
        .cta-content p {
            font-size: 1.3rem;
            margin-bottom: 3rem;
            opacity: 0.95;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-cta {
            padding: 18px 40px;
            font-size: 1.15rem;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .btn-cta-primary {
            background: #ffffff;
            color: #F2941E;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .btn-cta-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            background: #2c3e50;
            color: #ffffff;
        }
        
        .btn-cta-secondary {
            background: transparent;
            color: #ffffff;
            border: 3px solid #ffffff;
        }
        
        .btn-cta-secondary:hover {
            background: #2ecc71;
            border-color: #2ecc71;
            color: #ffffff;
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .about-hero-content h1 {
                font-size: 2.8rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .story-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .story-images {
                order: -1;
            }
            
            .values-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .testimonial-card {
                min-width: 350px;
                max-width: 350px;
            }
        }
        
        @media (max-width: 768px) {
            .about-hero {
                padding: 5rem 0 4rem;
            }
            
            .about-hero-content h1 {
                font-size: 2.2rem;
            }
            
            .about-hero-content p {
                font-size: 1.1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-item:not(:last-child) {
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .story-content h2,
            .section-header h2 {
                font-size: 2.2rem;
            }
            
            .cta-content h2 {
                font-size: 2.5rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-cta {
                width: 100%;
                max-width: 320px;
                justify-content: center;
            }

            .testimonial-card {
                min-width: 300px;
                max-width: 300px;
            }
        }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <span class="section-badge">ABOUT NESTMYPET</span>
                <h1>Where Every Pet Finds Love and Care</h1>
                <p>We're building a community of passionate pet lovers who believe every furry friend deserves the best care while you're away. No stress, no worries, just genuine peace of mind.</p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-paw"></i>
                    </div>
                    <span class="stat-number" data-target="10000">0</span>
                    <span class="stat-label">Happy Pets</span>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number" data-target="5000">0</span>
                    <span class="stat-label">Trusted Sitters</span>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span class="stat-number" data-target="50000">0</span>
                    <span class="stat-label">Bookings</span>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <span class="stat-number" data-target="98">0</span>
                    <span class="stat-label">Satisfaction</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="story-section">
        <div class="container">
            <div class="story-grid">
                <div class="story-content">
                    <h2>Our Story Begins with <span class="highlight">Max</span></h2>
                    <p>It started with a terrier named Max and a last-minute cancellation that left his owner scrambling. That stressful night revealed something important: finding <span class="bold-green">reliable, compassionate pet care</span> shouldn't feel like a gamble.</p>
                    <p>We built NestMyPet to change that story. Our platform connects pet parents with <span class="bold-orange">carefully vetted sitters</span> who genuinely love what they do. Every booking strengthens our community, every review builds trust, and every pet finds their <span class="bold-green">perfect temporary home</span>.</p>
                    
                    <div class="story-features">
                        <div class="story-feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Rigorously vetted, background-checked sitters</span>
                        </div>
                        <div class="story-feature-item">
                            <i class="fas fa-headset"></i>
                            <span>24/7 customer support when you need it</span>
                        </div>
                        <div class="story-feature-item">
                            <i class="fas fa-heart"></i>
                            <span>Genuine care from passionate animal lovers</span>
                        </div>
                    </div>
                </div>
                
                <div class="story-images">
                    <div class="story-image-card">
                        <img src="dog.png" alt="Happy dog with owner">
                    </div>
                    <div class="story-image-card">
                        <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=400&h=280&fit=crop" alt="Pet getting care">
                    </div>
                    <div class="story-image-card">
                        <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=800&h=280&fit=crop" alt="Happy pets playing">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">OUR VALUES</span>
                <h2>What Drives Us Forward</h2>
                <p>These aren't just words on a page. They're the principles we live by every single day.</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-card-content">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3>Unconditional Compassion</h3>
                        <p>Every pet deserves <span class="bold-orange">love and respect</span>. We treat each animal with the care they need to feel safe, comfortable, and genuinely happy in their temporary home.</p>
                    </div>
                </div>

                <div class="value-card">
                    <div class="value-card-content">
                        <div class="value-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Absolute Transparency</h3>
                        <p><span class="bold-green">Clear communication</span>, honest reviews, and a reliable vetting process you can trust. No hidden fees, no surprises, just straightforward service that delivers.</p>
                    </div>
                </div>

                <div class="value-card">
                    <div class="value-card-content">
                        <div class="value-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3>Effortless Experience</h3>
                        <p>Our platform is designed for <span class="bold-orange">simplicity</span>. Book quickly, communicate easily, and spend less time worrying and more time enjoying your trip with total peace of mind.</p>
                    </div>
                </div>

                <div class="value-card">
                    <div class="value-card-content">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Community First</h3>
                        <p>We're building more than a marketplace. We're fostering a <span class="bold-green">supportive community</span> of pet lovers who genuinely care about animal welfare and each other.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Carousel Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">TESTIMONIALS</span>
                <h2>What Pet Parents Say</h2>
                <p>Real stories from real people who trust us with their beloved companions.</p>
            </div>
        </div>
        
        <div class="carousel-container">
            <div class="carousel-track" id="testimonial-track">
                <!-- First set of testimonials -->
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=1" alt="Sarah Johnson">
                        </div>
                        <div class="testimonial-info">
                            <h4>Sarah Johnson</h4>
                            <p>Pet Owner, Chicago</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Finding a sitter for Bella has never been easier! The sitter sent daily photos and updates. I could actually enjoy my vacation knowing she was in great hands."</p>
                    <div class="testimonial-pet">🐕 For Bella (Golden Retriever)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=33" alt="Michael Chen">
                        </div>
                        <div class="testimonial-info">
                            <h4>Michael Chen</h4>
                            <p>Pet Owner, San Francisco</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"As a first-time user, I was nervous. But the vetting process is thorough and the sitter was absolutely wonderful with my anxious rescue dog. Highly recommend!"</p>
                    <div class="testimonial-pet">🐕 For Max (German Shepherd)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=5" alt="Emily Rodriguez">
                        </div>
                        <div class="testimonial-info">
                            <h4>Emily Rodriguez</h4>
                            <p>Pet Owner, Austin</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"My two cats are very picky about people, but they absolutely loved their sitter! The booking process was seamless and communication was excellent throughout."</p>
                    <div class="testimonial-pet">🐈 For Luna & Oliver (Cats)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=12" alt="David Thompson">
                        </div>
                        <div class="testimonial-info">
                            <h4>David Thompson</h4>
                            <p>Pet Owner, Seattle</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"NestMyPet saved me during a business emergency. Found a great sitter within hours who took amazing care of my puppy. The peace of mind was priceless!"</p>
                    <div class="testimonial-pet">🐕 For Charlie (Beagle)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=20" alt="Jessica Miller">
                        </div>
                        <div class="testimonial-info">
                            <h4>Jessica Miller</h4>
                            <p>Pet Owner, Boston</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"The transparency of reviews and background checks made me feel confident. Our sitter went above and beyond, even teaching my dog a new trick! Will definitely use again."</p>
                    <div class="testimonial-pet">🐕 For Daisy (Labrador)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=8" alt="Robert Anderson">
                        </div>
                        <div class="testimonial-info">
                            <h4>Robert Anderson</h4>
                            <p>Pet Owner, Miami</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Best pet sitting service I've used! The app makes it so easy to stay connected, and my dog actually gets excited when I mention his sitter's name. That says it all!"</p>
                    <div class="testimonial-pet">🐕 For Rocky (Pitbull)</div>
                </div>

                <!-- Duplicate set for seamless loop -->
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=1" alt="Sarah Johnson">
                        </div>
                        <div class="testimonial-info">
                            <h4>Sarah Johnson</h4>
                            <p>Pet Owner, Chicago</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Finding a sitter for Bella has never been easier! The sitter sent daily photos and updates. I could actually enjoy my vacation knowing she was in great hands."</p>
                    <div class="testimonial-pet">🐕 For Bella (Golden Retriever)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=33" alt="Michael Chen">
                        </div>
                        <div class="testimonial-info">
                            <h4>Michael Chen</h4>
                            <p>Pet Owner, San Francisco</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"As a first-time user, I was nervous. But the vetting process is thorough and the sitter was absolutely wonderful with my anxious rescue dog. Highly recommend!"</p>
                    <div class="testimonial-pet">🐕 For Max (German Shepherd)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=5" alt="Emily Rodriguez">
                        </div>
                        <div class="testimonial-info">
                            <h4>Emily Rodriguez</h4>
                            <p>Pet Owner, Austin</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"My two cats are very picky about people, but they absolutely loved their sitter! The booking process was seamless and communication was excellent throughout."</p>
                    <div class="testimonial-pet">🐈 For Luna & Oliver (Cats)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=12" alt="David Thompson">
                        </div>
                        <div class="testimonial-info">
                            <h4>David Thompson</h4>
                            <p>Pet Owner, Seattle</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"NestMyPet saved me during a business emergency. Found a great sitter within hours who took amazing care of my puppy. The peace of mind was priceless!"</p>
                    <div class="testimonial-pet">🐕 For Charlie (Beagle)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=20" alt="Jessica Miller">
                        </div>
                        <div class="testimonial-info">
                            <h4>Jessica Miller</h4>
                            <p>Pet Owner, Boston</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"The transparency of reviews and background checks made me feel confident. Our sitter went above and beyond, even teaching my dog a new trick! Will definitely use again."</p>
                    <div class="testimonial-pet">🐕 For Daisy (Labrador)</div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <img src="https://i.pravatar.cc/150?img=8" alt="Robert Anderson">
                        </div>
                        <div class="testimonial-info">
                            <h4>Robert Anderson</h4>
                            <p>Pet Owner, Miami</p>
                        </div>
                    </div>
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Best pet sitting service I've used! The app makes it so easy to stay connected, and my dog actually gets excited when I mention his sitter's name. That says it all!"</p>
                    <div class="testimonial-pet">🐕 For Rocky (Pitbull)</div>
                </div>
            </div>
        </div>

        <!-- Manual Navigation Arrows -->
        <div style="text-align: center; margin-top: 2rem;">
            <button id="prev-btn" style="background: #2ecc71; color: white; border: none; padding: 12px 24px; border-radius: 50px; margin: 0 10px; cursor: pointer; font-weight: 600; font-size: 1rem; transition: all 0.3s ease;">
                <i class="fas fa-arrow-left"></i> Previous
            </button>
            <button id="next-btn" style="background: #F2941E; color: white; border: none; padding: 12px 24px; border-radius: 50px; margin: 0 10px; cursor: pointer; font-weight: 600; font-size: 1rem; transition: all 0.3s ease;">
                Next <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">OUR TEAM</span>
                <h2>Meet The Pack</h2>
                <p>Passionate people who pour their hearts into making pet care better every day.</p>
            </div>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="team-photo">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&h=300&fit=crop" alt="Team Member">
                    </div>
                    <div class="team-info">
                        <h3>Alexandra Martinez</h3>
                        <span class="role">Founder & CEO</span>
                        <p>Former vet tech turned entrepreneur. Alex's love for animals and tech innovation sparked the creation of NestMyPet.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="team-member">
                    <div class="team-photo">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&h=300&fit=crop" alt="Team Member">
                    </div>
                    <div class="team-info">
                        <h3>James Cooper</h3>
                        <span class="role">Head of Operations</span>
                        <p>With 10+ years in customer service, James ensures every pet parent and sitter has an exceptional experience.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>

                <div class="team-member">
                    <div class="team-photo">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&h=300&fit=crop" alt="Team Member">
                    </div>
                    <div class="team-info">
                        <h3>Priya Patel</h3>
                        <span class="role">Lead Product Designer</span>
                        <p>Priya crafts beautiful, intuitive experiences that make finding pet care as easy as a few taps on your phone.</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-dribbble"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Find the Perfect Nest?</h2>
                <p>Join thousands of pet owners who trust NestMyPet for stress-free travel and happy, well-cared-for pets.</p>
                <div class="cta-buttons">
                    <a href="search.php" class="btn-cta btn-cta-primary">
                        <i class="fas fa-search"></i>
                        Find a Sitter Today
                    </a>
                    <a href="signup_sitter.php" class="btn-cta btn-cta-secondary">
                        <i class="fas fa-hand-holding-heart"></i>
                        Become a Sitter
                    </a>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Counter Animation for Stats
        const animateCounter = (element, target, suffix = '') => {
            let current = 0;
            const increment = target / 80;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString() + suffix;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString() + suffix;
                }
            }, 30);
        };

        // Stats Observer
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                    entry.target.classList.add('animated');
                    const number = entry.target.querySelector('.stat-number');
                    const target = parseInt(number.getAttribute('data-target'));
                    const suffix = target < 100 ? '%' : '+';
                    animateCounter(number, target, suffix);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-item').forEach(stat => {
            statsObserver.observe(stat);
        });

        // Smooth Scroll Animations
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.story-content, .story-images, .value-card, .section-header, .team-member').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(50px)';
            el.style.transition = 'all 1s cubic-bezier(0.4, 0, 0.2, 1)';
            observer.observe(el);
        });

        // Testimonial Carousel Manual Controls
        const track = document.getElementById('testimonial-track');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        let scrollAmount = 0;
        const cardWidth = 420; // 400px width + 20px gap

        prevBtn.addEventListener('click', () => {
            track.style.animation = 'none';
            scrollAmount -= cardWidth;
            track.style.transform = `translateX(${scrollAmount}px)`;
            
            setTimeout(() => {
                track.style.animation = 'scroll 30s linear infinite';
            }, 500);
        });

        nextBtn.addEventListener('click', () => {
            track.style.animation = 'none';
            scrollAmount += cardWidth;
            track.style.transform = `translateX(${scrollAmount}px)`;
            
            setTimeout(() => {
                track.style.animation = 'scroll 30s linear infinite';
            }, 500);
        });

        // Hover effects for buttons
        [prevBtn, nextBtn].forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body> 
</html>
<?php
include 'footer.php';
?>