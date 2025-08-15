<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Portal - Help Desk Chat System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Animated gradient background */
        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #fff5f0 0%, #ffe8db 25%, #fff5f0 50%, #ffeee6 75%, #fff5f0 100%);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
            z-index: 0;
        }
        
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Glowing orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 20s infinite ease-in-out;
            pointer-events: none;
        }
        
        .orb1 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #ff6b35 0%, transparent 70%);
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }
        
        .orb2 {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, #ff9558 0%, transparent 70%);
            bottom: -125px;
            right: -125px;
            animation-delay: 5s;
        }
        
        .orb3 {
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, #ffa574 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        
        /* Grid pattern overlay */
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 107, 53, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 107, 53, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 1;
            pointer-events: none;
        }
        
        /* Main container */
        .container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 15px 20px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(255, 107, 53, 0.08);
        }
        
        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-button {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: rgba(255, 107, 53, 0.08);
            border: 1px solid rgba(255, 107, 53, 0.2);
            border-radius: 8px;
            color: #ff6b35;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: rgba(255, 107, 53, 0.15);
            border-color: #ff6b35;
            transform: translateX(-3px);
        }
        
        .back-icon {
            font-size: 1.1rem;
            line-height: 1;
        }
        
        .back-text {
            display: inline;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #ff6b35, #ff9558);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.25);
        }
        
        .logo-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3436;
            letter-spacing: -0.5px;
        }
        
        .nav-status {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            border-radius: 20px;
            color: #4caf50;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-dot {
            width: 6px;
            height: 6px;
            background: #4caf50;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }
        
        /* Hero Section */
        .hero {
            text-align: center;
            max-width: 900px;
            width: 100%;
            margin-bottom: 60px;
            animation: fadeInUp 0.8s ease;
            padding: 0 20px;
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
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: rgba(255, 107, 53, 0.08);
            border: 1px solid rgba(255, 107, 53, 0.2);
            border-radius: 30px;
            color: #ff6b35;
            font-size: 0.9rem;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease 0.1s both;
        }
        
        .hero-badge-icon {
            width: 16px;
            height: 16px;
            background: linear-gradient(135deg, #ff6b35, #ff9558);
            border-radius: 50%;
        }
        
        h1 {
            font-size: clamp(2rem, 7vw, 4rem);
            font-weight: 700;
            color: #2d3436;
            line-height: 1.2;
            margin-bottom: 25px;
            letter-spacing: -1px;
            animation: fadeInUp 0.8s ease 0.2s both;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #ff6b35, #ff9558, #ffa574);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: clamp(1rem, 3vw, 1.25rem);
            color: #636e72;
            line-height: 1.6;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease 0.3s both;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Auth buttons - minimalist design */
        .auth-section {
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        
        .btn {
            padding: 14px 32px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff6b35, #ff9558);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.25);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.35);
        }
        
        .btn-secondary {
            background: white;
            color: #ff6b35;
            border: 2px solid rgba(255, 107, 53, 0.3);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 107, 53, 0.05);
            border-color: #ff6b35;
            transform: translateY(-2px);
        }
        
        .btn-icon {
            font-size: 1.1rem;
        }
        
        /* Features grid */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            max-width: 900px;
            width: 100%;
            margin-top: 60px;
            padding: 0 20px;
            animation: fadeInUp 0.8s ease 0.5s both;
        }
        
        .feature-card {
            background: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 107, 53, 0.1);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .feature-card:hover {
            border-color: rgba(255, 107, 53, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.15);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(255, 149, 88, 0.1));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .feature-title {
            color: #2d3436;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .feature-desc {
            color: #636e72;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        /* Footer */
        .footer {
            position: relative;
            width: 100%;
            padding: 30px 20px;
            margin-top: 80px;
            text-align: center;
            color: #636e72;
            font-size: 0.85rem;
            z-index: 2;
            background: linear-gradient(0deg, rgba(255, 255, 255, 0.95) 0%, transparent 100%);
        }
        
        .tech-pills {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        
        .tech-pill {
            padding: 4px 12px;
            background: rgba(255, 107, 53, 0.08);
            border: 1px solid rgba(255, 107, 53, 0.2);
            border-radius: 20px;
            font-size: 0.75rem;
            color: #ff6b35;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            nav {
                padding: 12px 15px;
            }
            
            .nav-content {
                justify-content: space-between;
                gap: 10px;
            }
            
            .logo-section {
                gap: 8px;
            }
            
            .logo-icon {
                width: 32px;
                height: 32px;
                font-size: 16px;
            }
            
            .logo-text {
                font-size: 1rem;
            }
            
            .nav-status {
                font-size: 0.75rem;
                padding: 5px 10px;
            }
            
            .hero {
                margin-top: 80px;
                margin-bottom: 40px;
                padding: 0 15px;
            }
            
            .hero-badge {
                font-size: 0.8rem;
                padding: 6px 14px;
                margin-bottom: 20px;
            }
            
            h1 {
                font-size: 1.8rem;
                line-height: 1.3;
                margin-bottom: 20px;
            }
            
            .hero-subtitle {
                font-size: 0.95rem;
                padding: 0;
                margin-bottom: 30px;
            }
            
            .auth-section {
                width: 100%;
                flex-direction: column;
                max-width: 280px;
                margin: 0 auto;
                gap: 12px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                padding: 14px 24px;
                font-size: 0.9rem;
            }
            
            .features {
                grid-template-columns: 1fr;
                margin-top: 40px;
                gap: 20px;
                padding: 0 15px;
            }
            
            .feature-card {
                padding: 25px 20px;
            }
            
            .footer {
                margin-top: 60px;
                padding: 20px 15px;
            }
            
            .orb1 {
                width: 200px;
                height: 200px;
                top: -100px;
                left: -100px;
            }
            
            .orb2 {
                width: 180px;
                height: 180px;
                bottom: -90px;
                right: -90px;
            }
            
            .orb3 {
                width: 200px;
                height: 200px;
            }
        }
        
        @media (max-width: 480px) {
            nav {
                padding: 10px 12px;
            }
            
            .logo-icon {
                width: 30px;
                height: 30px;
                font-size: 15px;
                border-radius: 8px;
            }
            
            .logo-text {
                font-size: 0.95rem;
            }
            
            .nav-status {
                font-size: 0.7rem;
                padding: 4px 8px;
                gap: 5px;
            }
            
            .status-dot {
                width: 5px;
                height: 5px;
            }
            
            .hero {
                margin-top: 70px;
            }
            
            h1 {
                font-size: 1.6rem;
                margin-bottom: 15px;
            }
            
            h1 br {
                display: none;
            }
            
            .hero-badge {
                font-size: 0.75rem;
                padding: 5px 12px;
            }
            
            .hero-badge-icon {
                width: 12px;
                height: 12px;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
                line-height: 1.5;
            }
            
            .btn {
                font-size: 0.85rem;
                padding: 12px 20px;
            }
            
            .feature-card {
                padding: 20px 15px;
            }
            
            .feature-icon {
                width: 40px;
                height: 40px;
                font-size: 20px;
                margin-bottom: 15px;
            }
            
            .feature-title {
                font-size: 0.95rem;
            }
            
            .feature-desc {
                font-size: 0.8rem;
            }
            
            .tech-pills {
                gap: 6px;
            }
            
            .tech-pill {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
        }
        
        /* Tablet specific */
        @media (min-width: 769px) and (max-width: 1024px) {
            .features {
                grid-template-columns: repeat(2, 1fr);
            }
            
            h1 {
                font-size: 3rem;
            }
        }
        
        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>
    <div class="orb orb3"></div>
    <div class="grid-overlay"></div>
    
    <nav>
        <div class="nav-content">
            <div class="nav-left">
                <a href="index.php" class="back-button">
                    <span class="back-icon">←</span>
                    <span class="back-text">Back</span>
                </a>
                <div class="logo-section">
                    <div class="logo-icon">🏠</div>
                    <div class="logo-text">ResidentHub</div>
                </div>
            </div>
            <div class="nav-status">
                <div class="status-dot"></div>
                <span>System Online</span>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="hero">
            <div class="hero-badge">
                <div class="hero-badge-icon"></div>
                <span>24/7 Support Available</span>
            </div>
            
            <h1>
                Welcome to <br>
                <span class="gradient-text">Resident Portal</span>
            </h1>
            
            <p class="hero-subtitle">
                Connect with our support team instantly. Get help, submit requests, 
                and manage your account all in one secure platform.
            </p>
            
            <div class="auth-section">
                <a href="user_login.php" class="btn btn-primary">
                    <span class="btn-icon">→</span>
                    <span>Sign In</span>
                </a>
                <a href="user_register.php" class="btn btn-secondary">
                    <span>Create Account</span>
                </a>
            </div>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <div class="feature-title">Real-time Chat</div>
                <div class="feature-desc">Connect instantly with our support team</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔐</div>
                <div class="feature-title">Secure Platform</div>
                <div class="feature-desc">Your data is protected with enterprise security</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Fast Response</div>
                <div class="feature-desc">Get help quickly with priority support</div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Secure • Fast • Reliable</strong></p>
            <div class="tech-pills">
                <span class="tech-pill">SSL Encrypted</span>
                <span class="tech-pill">ISO Certified</span>
                <span class="tech-pill">GDPR Compliant</span>
            </div>
        </div>
    </div>
    
    <script>
        // Smooth entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add subtle parallax effect on mouse move (desktop only)
            if (window.innerWidth > 768) {
                document.addEventListener('mousemove', (e) => {
                    const x = e.clientX / window.innerWidth;
                    const y = e.clientY / window.innerHeight;
                    
                    document.querySelectorAll('.orb').forEach((orb, index) => {
                        const speed = (index + 1) * 15;
                        orb.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
                    });
                });
            }
            
            // Button ripple effect
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        left: ${x}px;
                        top: ${y}px;
                        width: ${size}px;
                        height: ${size}px;
                        background: rgba(255, 255, 255, 0.5);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: rippleEffect 0.6s ease-out;
                        pointer-events: none;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes rippleEffect {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Smooth scroll reveal for features
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.feature-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `all 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
            
            // Handle orientation change
            window.addEventListener('orientationchange', function() {
                setTimeout(() => {
                    window.scrollTo(0, 0);
                }, 100);
            });
        });
        
        // System status check
        function checkSystemStatus() {
            const statusDot = document.querySelector('.status-dot');
            const statusText = document.querySelector('.nav-status span');
            
            // Simulate status check
            statusDot.style.background = '#4caf50';
            statusText.textContent = 'System Online';
        }
        
        checkSystemStatus();
        setInterval(checkSystemStatus, 30000);
        
        // Prevent zoom on double tap (iOS)
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>

</body>
</html>