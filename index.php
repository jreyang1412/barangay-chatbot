<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Desk Chat System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 200vh;
            overflow-x: hidden;
            scroll-behavior: smooth;
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
        
        .section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        .user-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .admin-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
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
            margin: 10px;
        }
        
        .admin-section .main-container {
            background: rgba(44, 62, 80, 0.95);
            color: #ecf0f1;
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
        
        .admin-section .main-container::before {
            background: linear-gradient(90deg, #e74c3c, #c0392b, #f39c12, #d35400);
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
        
        .admin-section .main-title {
            color: #ecf0f1;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
        
        .admin-section .subtitle {
            color: #bdc3c7;
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
        }
        
        .admin-section .portal-card {
            background: rgba(52, 73, 94, 0.8);
            color: #ecf0f1;
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
        
        .user-portal {
            border-top: 4px solid #3498db;
        }
        
        .admin-portal {
            border-top: 4px solid #e74c3c;
        }
        
        .portal-icon {
            font-size: clamp(2rem, 6vw, 3rem);
            margin-bottom: clamp(15px, 3vw, 20px);
            display: block;
        }
        
        .user-portal .portal-icon {
            color: #3498db;
        }
        
        .admin-portal .portal-icon {
            color: #e74c3c;
        }
        
        .portal-title {
            font-size: clamp(1.3rem, 4vw, 1.5rem);
            font-weight: 600;
            margin-bottom: clamp(8px, 2vw, 10px);
            color: #2c3e50;
        }
        
        .admin-section .portal-title {
            color: #ecf0f1;
        }
        
        .portal-description {
            color: #7f8c8d;
            font-size: clamp(0.9rem, 2.5vw, 0.95rem);
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .admin-section .portal-description {
            color: #bdc3c7;
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
        
        .admin-section .features-list li {
            color: #95a5a6;
        }
        
        .features-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
        }
        
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-10px); }
            60% { transform: translateX(-50%) translateY(-5px); }
        }
        
        .password-protection {
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid #e74c3c;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        
        .password-input {
            width: 100%;
            max-width: 300px;
            padding: 15px;
            border: 2px solid #e74c3c;
            border-radius: 10px;
            font-size: 16px;
            margin: 15px 0;
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
            text-align: center;
        }
        
        .password-input:focus {
            outline: none;
            border-color: #c0392b;
            box-shadow: 0 0 10px rgba(231, 76, 60, 0.3);
        }
        
        .unlock-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .unlock-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        
        .hidden {
            display: none;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
            font-weight: 500;
        }
        
        .success-message {
            color: #27ae60;
            font-size: 14px;
            margin-top: 10px;
            font-weight: 500;
        }
        
        .footer-info {
            margin-top: clamp(30px, 5vw, 40px);
            padding-top: clamp(15px, 3vw, 20px);
            border-top: 1px solid #ecf0f1;
            color: #95a5a6;
            font-size: clamp(0.85rem, 2vw, 0.9rem);
        }
        
        .admin-section .footer-info {
            border-top-color: #34495e;
            color: #bdc3c7;
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
        
        .admin-section .tech-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
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
            .section {
                padding: 10px;
            }
            
            .main-container {
                border-radius: 15px;
                margin: 5px;
                padding: 20px;
            }
            
            .portal-buttons {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .portal-card {
                min-height: 250px;
            }
            
            .password-input {
                max-width: 250px;
            }
        }
        
        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .shape,
            .logo,
            .status-indicator,
            .scroll-indicator {
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
    
    <!-- User Section -->
    <div class="section user-section">
        <div class="main-container">
            <div class="logo">👤</div>
            <h1 class="main-title">User Portal</h1>
            <p class="subtitle">Need help? Access our support system through secure authentication</p>
            
            <div class="portal-buttons">
                <a href="user_login.php" class="portal-card user-portal">
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
                
                <a href="user_register.php" class="portal-card user-portal">
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
        
        <div class="scroll-indicator">
            <div>⬇️</div>
            <div>Scroll down for Admin Access</div>
        </div>
    </div>
    
    <!-- Admin Section -->
    <div class="section admin-section">
        <div class="main-container">
            <div class="logo">⚙️</div>
            <h1 class="main-title">Admin Portal</h1>
            <p class="subtitle">Administrative access for barangay officials and support staff</p>
            
            <div class="password-protection" id="passwordProtection">
                <h3 style="color: #e74c3c; margin-bottom: 15px;">🔒 Restricted Access</h3>
                <p style="color: #c0392b; margin-bottom: 20px;">
                    This section is restricted to authorized barangay officials only.<br>
                    Please enter the official access code to continue.
                </p>
                <input type="password" 
                       class="password-input" 
                       id="adminPassword" 
                       placeholder="Enter access code"
                       maxlength="20">
                <br>
                <button class="unlock-btn" onclick="checkPassword()">Unlock Admin Panel</button>
                <div id="passwordMessage"></div>
            </div>
            
            <div class="portal-buttons hidden" id="adminButtons">
                <a href="admin_login.php" class="portal-card admin-portal">
                    <div class="status-indicator"></div>
                    <div>
                        <div class="portal-icon">🔐</div>
                        <h3 class="portal-title">Admin Login</h3>
                        <p class="portal-description">Sign in to administrative dashboard</p>
                    </div>
                    <ul class="features-list">
                        <li>Conversation management</li>
                        <li>User support dashboard</li>
                        <li>Emergency alerts</li>
                        <li>System monitoring</li>
                        <li>Report generation</li>
                    </ul>
                </a>
                
                <a href="admin_register.php" class="portal-card admin-portal">
                    <div class="status-indicator"></div>
                    <div>
                        <div class="portal-icon">👨‍💼</div>
                        <h3 class="portal-title">Admin Register</h3>
                        <p class="portal-description">Create new administrative account</p>
                    </div>
                    <ul class="features-list">
                        <li>Official account creation</li>
                        <li>Role-based permissions</li>
                        <li>Secure verification</li>
                        <li>Department assignment</li>
                        <li>Access level configuration</li>
                    </ul>
                </a>
            </div>
            
            <div class="footer-info">
                <p><strong>Official • Secure • Monitored</strong></p>
                <p>Administrative tools for barangay operations</p>
                <div class="tech-stack">
                    <span class="tech-badge">Admin Panel</span>
                    <span class="tech-badge">Role Management</span>
                    <span class="tech-badge">Audit Logs</span>
                    <span class="tech-badge">Security</span>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Password check function
        function checkPassword() {
            const password = document.getElementById('adminPassword').value;
            const messageDiv = document.getElementById('passwordMessage');
            const passwordProtection = document.getElementById('passwordProtection');
            const adminButtons = document.getElementById('adminButtons');
            
            // In a real application, this should be validated server-side
            if (password === 'barangayofficials') {
                messageDiv.innerHTML = '<div class="success-message">✅ Access granted! Welcome, official.</div>';
                
                setTimeout(() => {
                    passwordProtection.style.transition = 'all 0.5s ease';
                    passwordProtection.style.opacity = '0';
                    passwordProtection.style.transform = 'translateY(-20px)';
                    
                    setTimeout(() => {
                        passwordProtection.classList.add('hidden');
                        adminButtons.classList.remove('hidden');
                        adminButtons.style.opacity = '0';
                        adminButtons.style.transform = 'translateY(20px)';
                        
                        setTimeout(() => {
                            adminButtons.style.transition = 'all 0.5s ease';
                            adminButtons.style.opacity = '1';
                            adminButtons.style.transform = 'translateY(0)';
                        }, 50);
                    }, 500);
                }, 1000);
            } else {
                messageDiv.innerHTML = '<div class="error-message">❌ Invalid access code. Contact your administrator.</div>';
                document.getElementById('adminPassword').value = '';
                
                // Add shake animation to password input
                const passwordInput = document.getElementById('adminPassword');
                passwordInput.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    passwordInput.style.animation = '';
                }, 500);
            }
        }
        
        // Allow Enter key to submit password
        document.getElementById('adminPassword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                checkPassword();
            }
        });
        
        // Add shake animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
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
        
        // Smooth scrolling for better UX
        function smoothScrollTo(element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        
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
    </script>
<?php include('user.php'); ?>
</body>
</html>