<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Services</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        :root {
            --primary-gradient: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            --secondary-gradient: linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%);
            --accent-gradient: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
            --light-gradient: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.3);
            --primary-orange: #ff6b35;
            --secondary-orange: #f7931e;
            --text-dark: #2d3748;
            --text-light: #6c757d;
            --shadow-light: 0 10px 25px rgba(255, 107, 53, 0.1);
            --shadow-medium: 0 20px 40px rgba(255, 107, 53, 0.15);
            --shadow-heavy: 0 30px 60px rgba(255, 107, 53, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #ff6b35 50%, #ffffff 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 107, 53, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(247, 147, 30, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(255, 152, 0, 0.2) 0%, transparent 50%);
            animation: backgroundShift 20s ease-in-out infinite;
            z-index: -2;
        }

        @keyframes backgroundShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Floating Particles */
        .particle {
            position: absolute;
            background: rgba(255, 107, 53, 0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: floatParticle 15s infinite linear;
        }

        @keyframes floatParticle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Header Styles */
        .header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 107, 53, 0.3);
            color: var(--primary-orange);
            padding: 3rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255, 107, 53, 0.1), transparent);
            animation: headerRotate 8s linear infinite;
        }

        @keyframes headerRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .header h1 {
            font-size: 4rem;
            font-weight: 900;
            text-shadow: 0 0 30px rgba(255, 107, 53, 0.5);
            letter-spacing: 3px;
            position: relative;
            z-index: 1;
            background: linear-gradient(45deg, #ff6b35, #f7931e, #ff6b35);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textShine 3s ease-in-out infinite;
        }

        @keyframes textShine {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Carousel Container */
        .carousel-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
            padding: 4rem 2rem;
            position: relative;
        }

        .carousel-wrapper {
            position: relative;
            width: 500px;
            height: 400px;
            perspective: 1500px;
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 1s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .carousel-item {
            position: absolute;
            width: 300px;
            height: 320px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            border: 2px solid rgba(255, 107, 53, 0.3);
            box-shadow: var(--shadow-heavy);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all 0.6s ease;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -160px;
        }

        .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 107, 53, 0.2), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .carousel-item:hover::before {
            opacity: 1;
        }

        .carousel-item img {
            width: calc(100% - 20px);
            height: calc(100% - 20px);
            object-fit: cover;
            border-radius: 20px;
            transition: transform 0.3s ease;
            filter: brightness(1.1) contrast(1.1);
        }

        .carousel-item:hover img {
            transform: scale(1.05);
        }

        /* Position items in 3D circle */
        .carousel-item:nth-child(1) {
            transform: rotateY(0deg) translateZ(280px);
        }

        .carousel-item:nth-child(2) {
            transform: rotateY(90deg) translateZ(280px);
        }

        .carousel-item:nth-child(3) {
            transform: rotateY(180deg) translateZ(280px);
        }

        .carousel-item:nth-child(4) {
            transform: rotateY(270deg) translateZ(280px);
        }

        /* Navigation Buttons */
        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 107, 53, 0.3);
            border-radius: 50%;
            color: var(--primary-orange);
            font-size: 28px;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: var(--shadow-medium);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-button:hover {
            transform: translateY(-50%) scale(1.1);
            background: var(--primary-orange);
            color: white;
            border-color: var(--primary-orange);
            box-shadow: var(--shadow-heavy);
        }

        .nav-button::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255, 107, 53, 0.3), transparent, rgba(255, 107, 53, 0.3));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .nav-button:hover::before {
            opacity: 1;
        }

        .nav-button.prev {
            left: -110px;
        }

        .nav-button.next {
            right: -110px;
        }

        /* Indicators */
        .indicators {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            padding: 15px 25px;
            border-radius: 50px;
            border: 2px solid rgba(255, 107, 53, 0.3);
        }

        .indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: rgba(255, 107, 53, 0.3);
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
        }

        .indicator::before {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.3), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .indicator.active {
            background: var(--primary-orange);
            transform: scale(1.3);
            box-shadow: 0 0 20px rgba(255, 107, 53, 0.5);
        }

        .indicator:hover::before {
            opacity: 1;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            padding: 4rem 2rem;
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 2rem;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            animation: sectionGlow 6s ease-in-out infinite;
        }

        @keyframes sectionGlow {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(180deg); }
        }

        .welcome-section h2 {
            font-size: 3rem;
            color: white;
            margin-bottom: 1.5rem;
            font-weight: 800;
            position: relative;
            z-index: 1;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
        }

        .welcome-section p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.8;
            margin-bottom: 3rem;
            position: relative;
            z-index: 1;
            font-weight: 400;
        }

        .cta-button {
            background: var(--primary-gradient);
            color: white;
            padding: 1.2rem 3rem;
            border: none;
            border-radius: 60px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.4s ease;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .cta-button:hover::before {
            width: 300px;
            height: 300px;
        }

        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
            background: var(--secondary-gradient);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(30px);
            padding: 4rem 3rem;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-heavy);
            transform: scale(0.7) rotateX(10deg);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1) rotateX(0deg);
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: modalShimmer 4s linear infinite;
        }

        @keyframes modalShimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal-title {
            font-size: 2.2rem;
            color: white;
            margin-bottom: 2rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .modal-buttons {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .modal-button {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            color: var(--primary-orange);
            padding: 1.5rem 2rem;
            border: 2px solid rgba(255, 107, 53, 0.3);
            border-radius: 20px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .modal-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .modal-button:hover::before {
            left: 100%;
        }

        .modal-button:hover {
            transform: translateY(-3px);
            background: var(--primary-orange);
            color: white;
            border-color: var(--primary-orange);
            box-shadow: var(--shadow-medium);
        }

        .modal-button.admin {
            background: rgba(247, 147, 30, 0.2);
            border-color: rgba(247, 147, 30, 0.5);
            color: var(--secondary-orange);
        }

        .modal-button.admin:hover {
            background: var(--secondary-orange);
            color: white;
            border-color: var(--secondary-orange);
            box-shadow: 0 10px 30px rgba(247, 147, 30, 0.3);
        }
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .modal-button:hover::before {
            left: 100%;
        }

        .modal-button:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
        }

        .modal-button.admin {
            background: rgba(255, 165, 0, 0.2);
            border-color: rgba(255, 165, 0, 0.3);
        }

        .modal-button.admin:hover {
            background: rgba(255, 165, 0, 0.3);
            border-color: rgba(255, 165, 0, 0.5);
            box-shadow: 0 10px 30px rgba(255, 165, 0, 0.2);
        }

        .close-button {
            position: absolute;
            top: 20px;
            right: 25px;
            background: none;
            border: none;
            font-size: 28px;
            color: var(--primary-orange);
            cursor: pointer;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            z-index: 2;
        }

        .close-button:hover {
            color: white;
            background: var(--primary-orange);
            transform: rotate(90deg);
        }

        /* Floating elements - Enhanced */
        .floating-element {
            position: absolute;
            background: rgba(255, 107, 53, 0.2);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 107, 53, 0.3);
        }

        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 40%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 30%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg) scale(1); 
                opacity: 0.6;
            }
            50% { 
                transform: translateY(-30px) rotate(180deg) scale(1.1); 
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.8rem;
                letter-spacing: 2px;
            }
            
            .carousel-wrapper {
                width: 350px;
                height: 300px;
            }
            
            .carousel-item {
                width: 240px;
                height: 260px;
                margin-left: -120px;
                margin-top: -130px;
            }
            
            .carousel-item:nth-child(1),
            .carousel-item:nth-child(2),
            .carousel-item:nth-child(3),
            .carousel-item:nth-child(4) {
                transform: rotateY(0deg) translateZ(200px);
            }

            .carousel-item:nth-child(2) {
                transform: rotateY(90deg) translateZ(200px);
            }

            .carousel-item:nth-child(3) {
                transform: rotateY(180deg) translateZ(200px);
            }

            .carousel-item:nth-child(4) {
                transform: rotateY(270deg) translateZ(200px);
            }
            
            .nav-button {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }

            .nav-button.prev {
                left: -80px;
            }

            .nav-button.next {
                right: -80px;
            }
            
            .welcome-section {
                margin: 1rem;
                padding: 3rem 1.5rem;
            }

            .welcome-section h2 {
                font-size: 2.2rem;
            }

            .modal-content {
                padding: 3rem 2rem;
                margin: 1rem;
            }

            .modal-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 2.2rem;
            }
            
            .carousel-wrapper {
                width: 300px;
                height: 250px;
            }
            
            .carousel-item {
                width: 200px;
                height: 220px;
                margin-left: -100px;
                margin-top: -110px;
            }
            
            .nav-button.prev {
                left: -60px;
            }

            .nav-button.next {
                right: -60px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>

    <!-- Header -->
    <header class="header">
        <h1>BARANGAY SERVICES</h1>
    </header>

    <!-- Carousel Section -->
    <div class="carousel-container">
        <div class="carousel-wrapper">
            <div class="carousel" id="carousel">
                <div class="carousel-item">
                    <img src="landing/pic1.jpg" alt="Service 1" />
                </div>
                <div class="carousel-item">
                    <img src="landing/pic1.jpg" alt="Service 2" />
                </div>
                <div class="carousel-item">
                    <img src="landing/pic1.jpg" alt="Service 3" />
                </div>
                <div class="carousel-item">
                    <img src="landing/pic1.jpg" alt="Service 4" />
                </div>
            </div>
            
            <!-- Navigation Buttons -->
            <button class="nav-button prev" id="prevBtn">&#8249;</button>
            <button class="nav-button next" id="nextBtn">&#8250;</button>
            
            <!-- Indicators -->
            <div class="indicators" id="indicators">
                <div class="indicator active" data-slide="0"></div>
                <div class="indicator" data-slide="1"></div>
                <div class="indicator" data-slide="2"></div>
                <div class="indicator" data-slide="3"></div>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <h2>Welcome to Our Community</h2>
        <p>
            Experience efficient and reliable barangay services designed to serve our community better. 
            We are committed to providing quality assistance and support for all residents.
        </p>
        <button class="cta-button" id="getStartedBtn">
            Get Started
        </button>
    </section>

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <button class="close-button" id="closeBtn">&times;</button>
            <h3 class="modal-title">Choose Your Access Type</h3>
            <div class="modal-buttons">
                <button class="modal-button" id="userBtn">Are you a User?</button>
                <button class="modal-button admin" id="adminBtn">Are you an Admin?</button>
            </div>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            for (let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = Math.random() * 5 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                document.body.appendChild(particle);
            }
        }

        class Carousel3D {
            constructor() {
                this.carousel = document.getElementById('carousel');
                this.prevBtn = document.getElementById('prevBtn');
                this.nextBtn = document.getElementById('nextBtn');
                this.indicators = document.querySelectorAll('.indicator');
                this.currentSlide = 0;
                this.totalSlides = 4;
                
                this.init();
            }
            
            init() {
                // Navigation button events
                this.prevBtn.addEventListener('click', () => this.prevSlide());
                this.nextBtn.addEventListener('click', () => this.nextSlide());
                
                // Indicator events
                this.indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', () => this.goToSlide(index));
                });
                
                // Auto-rotation (optional)
                // this.startAutoRotation();
            }
            
            updateCarousel() {
                const rotateY = this.currentSlide * -90;
                this.carousel.style.transform = `rotateY(${rotateY}deg)`;
                this.updateIndicators();
            }
            
            updateIndicators() {
                this.indicators.forEach((indicator, index) => {
                    indicator.classList.toggle('active', index === this.currentSlide);
                });
            }
            
            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                this.updateCarousel();
            }
            
            prevSlide() {
                this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                this.updateCarousel();
            }
            
            goToSlide(slideIndex) {
                this.currentSlide = slideIndex;
                this.updateCarousel();
            }
            
            startAutoRotation() {
                setInterval(() => {
                    this.nextSlide();
                }, 5000);
            }
        }

        // Modal functionality
        class Modal {
            constructor() {
                this.modalOverlay = document.getElementById('modalOverlay');
                this.getStartedBtn = document.getElementById('getStartedBtn');
                this.closeBtn = document.getElementById('closeBtn');
                this.userBtn = document.getElementById('userBtn');
                this.adminBtn = document.getElementById('adminBtn');
                
                this.init();
            }
            
            init() {
                this.getStartedBtn.addEventListener('click', () => this.openModal());
                this.closeBtn.addEventListener('click', () => this.closeModal());
                this.modalOverlay.addEventListener('click', (e) => {
                    if (e.target === this.modalOverlay) {
                        this.closeModal();
                    }
                });
                
                this.userBtn.addEventListener('click', () => {
                    // Redirect to user side
                    window.location.href = 'userside.php';
                });
                
                this.adminBtn.addEventListener('click', () => {
                    // Redirect to admin side
                    window.location.href = 'adminside.php';
                });
                
                // Close modal with ESC key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.modalOverlay.classList.contains('show')) {
                        this.closeModal();
                    }
                });
            }
            
            openModal() {
                this.modalOverlay.style.display = 'flex';
                setTimeout(() => {
                    this.modalOverlay.classList.add('show');
                }, 10);
            }
            
            closeModal() {
                this.modalOverlay.classList.remove('show');
                setTimeout(() => {
                    this.modalOverlay.style.display = 'none';
                }, 300);
            }
        }

        // Initialize components when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            new Carousel3D();
            new Modal();
            
            // Add some additional interactive effects
            const carouselItems = document.querySelectorAll('.carousel-item');
            
            carouselItems.forEach((item, index) => {
                item.addEventListener('click', function() {
                    console.log(`Clicked on Service ${index + 1}`);
                    // You can add more functionality here
                });
            });
        });
    </script>
</body>
</html>