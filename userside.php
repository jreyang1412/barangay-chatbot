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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 10%;
            right: 30%;
            animation-delay: 1s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: clamp(20px, 5vw, 60px);
            text-align: center;
            max-width: 1200px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .logo {
            font-size: clamp(2.5rem, 8vw, 4rem);
            margin-bottom: clamp(15px, 3vw, 20px);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .main-title {
            font-size: clamp(1.8rem, 6vw, 2.5rem);
            color: #2c3e50;
            margin-bottom: clamp(10px, 2vw, 15px);
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        
        .subtitle {
            font-size: clamp(1rem, 3vw, 1.2rem);
            color: #7f8c8d;
            margin-bottom: clamp(30px, 5vw, 40px);
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .portal-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: clamp(20px, 4vw, 30px);
            justify-content: center;
            margin-bottom: clamp(30px, 5vw, 40px);
        }
        
        .portal-card {
            background: white;
            border-radius: 15px;
            padding: clamp(25px, 5vw, 40px) clamp(20px, 4vw, 30px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 300px;
            border-top: 4px solid #3498db;
        }
        
        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }
        
        .portal-card:hover::before {
            left: 100%;
        }
        
        .portal-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .portal-card:active {
            transform: translateY(-5px) scale(1.01);
        }
        
        .portal-icon {
            font-size: clamp(2rem, 6vw, 3rem);
            margin-bottom: clamp(15px, 3vw, 20px);
            display: block;
            color: #3498db;
        }
        
        .portal-title {
            font-size: clamp(1.3rem, 4vw, 1.5rem);
            font-weight: 600;
            margin-bottom: clamp(8px, 2vw, 10px);
            color: #2c3e50;
        }
        
        .portal-description {
            color: #7f8c8d;
            font-size: clamp(0.9rem, 2.5vw, 0.95rem);
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .features-list {
            margin-top: 15px;
            text-align: left;
            flex-grow: 1;
        }
        
        .features-list li {
            color: #95a5a6;
            font-size: clamp(0.8rem, 2vw, 0.85rem);
            margin: 8px 0;
            list-style: none;
            position: relative;
            padding-left: 20px;
            line-height: 1.4;
        }
        
        .features-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
        }
        
        .footer-info {
            margin-top: clamp(30px, 5vw, 40px);
            padding-top: clamp(15px, 3vw, 20px);
            border-top: 1px solid #ecf0f1;
            color: #95a5a6;
            font-size: clamp(0.85rem, 2vw, 0.9rem);
        }
        
        .tech-stack {
            display: flex;
            justify-content: center;
            gap: clamp(8px, 2vw, 15px);
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .tech-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: clamp(4px, 1vw, 5px) clamp(8px, 2vw, 12px);
            border-radius: 15px;
            font-size: clamp(0.7rem, 1.8vw, 0.75rem);
            font-weight: 500;
            white-space: nowrap;
        }
        
        .status-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            width: clamp(10px, 2vw, 12px);
            height: clamp(10px, 2vw, 12px);
            background: #27ae60;
            border-radius: 50%;
            animation: statusBlink 2s ease-in-out infinite;
        }
        
        @keyframes statusBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        /* Mobile Responsive */
        @media only screen and (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .main-container {
                border-radius: 15px;
                padding: 20px;
            }
            
            .portal-buttons {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .portal-card {
                min-height: 250px;
            }
        }
        
        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .shape,
            .logo,
            .status-indicator {
                animation: none;
            }
            
            .portal-card {
                transition: none;
            }
            
            .main-container::before {
                animation: none;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="main-container">
        <div class="logo">👤</div>
        <h1 class="main-title">User Portal</h1>
        <p class="subtitle">Need help? Access our support system through secure authentication</p>
        
        <div class="portal-buttons">
            <a href="user_login.php" class="portal-card">
                <div class="status-indicator"></div>
                <div>
                    <div class="portal-icon">🔑</div>
                    <h3 class="portal-title">User Login</h3>
                    <p class="portal-description">Sign in to your existing account</p>
                </div>
                <ul class="features-list">
                    <li>Access your support history</li>
                    <li>Continue existing conversations</li>
                    <li>Track request status</li>
                    <li>Secure authentication</li>
                    <li>Quick access</li>
                </ul>
            </a>
            
            <a href="user_register.php" class="portal-card">
                <div class="status-indicator"></div>
                <div>
                    <div class="portal-icon">📝</div>
                    <h3 class="portal-title">User Register</h3>
                    <p class="portal-description">Create a new account to get started</p>
                </div>
                <ul class="features-list">
                    <li>Quick registration process</li>
                    <li>Secure account creation</li>
                    <li>Personal support profile</li>
                    <li>Emergency contact setup</li>
                    <li>Location preferences</li>
                </ul>
            </a>
        </div>
        
        <div class="footer-info">
            <p><strong>Secure • Fast • Reliable</strong></p>
            <p>Get help from our support team</p>
            <div class="tech-stack">
                <span class="tech-badge">24/7 Support</span>
                <span class="tech-badge">Secure Login</span>
                <span class="tech-badge">Real-time Chat</span>
            </div>
        </div>
    </div>
    
    <script>
        // Card animations and effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load
            const cards = document.querySelectorAll('.portal-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200 + 500);
            });
            
            // Add click ripple effect
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    const ripple = document.createElement('div');
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
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                        z-index: 10;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
        
        // Check system status
        function checkSystemStatus() {
            const indicators = document.querySelectorAll('.status-indicator');
            indicators.forEach(indicator => {
                indicator.style.background = '#27ae60';
                indicator.title = 'System Online';
            });
        }
        
        checkSystemStatus();
        setInterval(checkSystemStatus, 30000);
        
        // Handle orientation and resize
        window.addEventListener('orientationchange', function() {
            setTimeout(() => {
                checkSystemStatus();
            }, 500);
        });
        
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(checkSystemStatus, 250);
        });
        
        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    <?php include('user.php'); ?>
</body>
</html>