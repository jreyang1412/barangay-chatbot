<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 20px rgba(25, 118, 210, 0.3);
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
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 4s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }

        .header h1 {
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }

        /* Carousel Container */
        .carousel-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
            padding: 2rem;
            position: relative;
        }

        .carousel-wrapper {
            position: relative;
            width: 500px;
            height: 400px;
            perspective: 1200px;
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .carousel-item {
            position: absolute;
            width: 280px;
            height: 300px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #1976d2;
            transition: all 0.4s ease;
            left: 50%;
            top: 50%;
            margin-left: -140px;
            margin-top: -150px;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
        }

        /* Position items in 3D circle */
        .carousel-item:nth-child(1) {
            transform: rotateY(0deg) translateZ(250px);
        }

        .carousel-item:nth-child(2) {
            transform: rotateY(90deg) translateZ(250px);
        }

        .carousel-item:nth-child(3) {
            transform: rotateY(180deg) translateZ(250px);
        }

        .carousel-item:nth-child(4) {
            transform: rotateY(270deg) translateZ(250px);
        }

        /* Navigation Buttons */
        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #1976d2, #42a5f5);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(25, 118, 210, 0.3);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-button:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 12px 30px rgba(25, 118, 210, 0.5);
            background: linear-gradient(45deg, #1565c0, #1976d2);
        }

        .nav-button.prev {
            left: -100px;
        }

        .nav-button.next {
            right: -100px;
        }

        /* Indicators */
        .indicators {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(25, 118, 210, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .indicator.active {
            background: #1976d2;
            transform: scale(1.2);
        }

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            padding: 3rem 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .welcome-section h2 {
            font-size: 2.5rem;
            color: #1565c0;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .welcome-section p {
            font-size: 1.2rem;
            color: #424242;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .cta-button {
            background: linear-gradient(45deg, #1976d2, #42a5f5);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.4);
            background: linear-gradient(45deg, #1565c0, #1976d2);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transform: scale(0.7);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal-content {
            transform: scale(1);
        }

        .modal-title {
            font-size: 1.8rem;
            color: #1565c0;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .modal-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .modal-button {
            background: linear-gradient(45deg, #1976d2, #42a5f5);
            color: white;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
        }

        .modal-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.4);
            background: linear-gradient(45deg, #1565c0, #1976d2);
        }

        .modal-button.admin {
            background: linear-gradient(45deg, #f57c00, #ff9800);
            box-shadow: 0 4px 15px rgba(245, 124, 0, 0.3);
        }

        .modal-button.admin:hover {
            background: linear-gradient(45deg, #e65100, #f57c00);
            box-shadow: 0 6px 20px rgba(245, 124, 0, 0.4);
        }

        .close-button {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-button:hover {
            color: #666;
        }

        /* Floating elements */
        .floating-element {
            position: absolute;
            width: 60px;
            height: 60px;
            background: rgba(25, 118, 210, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 40%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 30%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }
            
            .carousel-wrapper {
                width: 350px;
                height: 300px;
            }
            
            .carousel-item {
                width: 200px;
                height: 220px;
                margin-left: -100px;
                margin-top: -110px;
            }
            
            .carousel-item:nth-child(1) {
                transform: rotateY(0deg) translateZ(180px);
            }

            .carousel-item:nth-child(2) {
                transform: rotateY(90deg) translateZ(180px);
            }

            .carousel-item:nth-child(3) {
                transform: rotateY(180deg) translateZ(180px);
            }

            .carousel-item:nth-child(4) {
                transform: rotateY(270deg) translateZ(180px);
            }
            
            .nav-button {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .nav-button.prev {
                left: -70px;
            }

            .nav-button.next {
                right: -70px;
            }
            
            .welcome-section h2 {
                font-size: 2rem;
            }

            .modal-buttons {
                flex-direction: column;
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