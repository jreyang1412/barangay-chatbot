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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
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
            padding: 60px;
            text-align: center;
            max-width: 600px;
            width: 90vw;
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
            font-size: 4rem;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .main-title {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .portal-buttons {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .portal-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            min-width: 200px;
            text-decoration: none;
            color: inherit;
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
        
        .user-portal {
            border-top: 4px solid #3498db;
        }
        
        .admin-portal {
            border-top: 4px solid #e74c3c;
        }
        
        .portal-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
        }
        
        .user-portal .portal-icon {
            color: #3498db;
        }
        
        .admin-portal .portal-icon {
            color: #e74c3c;
        }
        
        .portal-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .portal-description {
            color: #7f8c8d;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .features-list {
            margin-top: 15px;
            text-align: left;
        }
        
        .features-list li {
            color: #95a5a6;
            font-size: 0.85rem;
            margin: 5px 0;
            list-style: none;
            position: relative;
            padding-left: 20px;
        }
        
        .features-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
        }
        
        .footer-info {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            color: #95a5a6;
            font-size: 0.9rem;
        }
        
        .tech-stack {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .tech-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 40px 30px;
            }
            
            .portal-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .portal-card {
                width: 100%;
                max-width: 300px;
            }
            
            .main-title {
                font-size: 2rem;
            }
            
            .logo {
                font-size: 3rem;
            }
        }
        
        .status-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 12px;
            height: 12px;
            background: #27ae60;
            border-radius: 50%;
            animation: statusBlink 2s ease-in-out infinite;
        }
        
        @keyframes statusBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
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
        <div class="logo">💬</div>
        <h1 class="main-title">Help Desk Chat System</h1>
        <p class="subtitle">Professional support platform connecting users with administrators in real-time</p>
        
        <div class="portal-buttons">
            <a href="user.php" class="portal-card user-portal">
                <div class="status-indicator"></div>
                <div class="portal-icon">👤</div>
                <h3 class="portal-title">User Portal</h3>
                <p class="portal-description">Need help? Get instant support from our team</p>
                <ul class="features-list">
                    <li>Emergency assistance</li>
                    <li>Technical support</li>
                    <li>General inquiries</li>
                    <li>Location sharing</li>
                    <li>Real-time chat</li>
                </ul>
            </a>
            
            <a href="admin.php" class="portal-card admin-portal">
                <div class="status-indicator"></div>
                <div class="portal-icon">⚙️</div>
                <h3 class="portal-title">Admin Portal</h3>
                <p class="portal-description">Manage support conversations and help users</p>
                <ul class="features-list">
                    <li>Live conversation management</li>
                    <li>Emergency request alerts</li>
                    <li>Location tracking</li>
                    <li>Multi-user support</li>
                    <li>Activity monitoring</li>
                </ul>
            </a>
        </div>
        
        <div class="footer-info">
            <p><strong>Secure • Fast • Reliable</strong></p>
            <p>Built with modern web technologies for optimal performance</p>
            <div class="tech-stack">
                <span class="tech-badge">PHP</span>
                <span class="tech-badge">MySQL</span>
                <span class="tech-badge">JavaScript</span>
                <span class="tech-badge">HTML5</span>
                <span class="tech-badge">CSS3</span>
            </div>
        </div>
    </div>
    
    <script>
        // Add some interactive effects
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
            
            // Add click analytics (optional)
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Add ripple effect
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
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
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
        
        // Check system status (optional enhancement)
        function checkSystemStatus() {
            // This could ping your server to check if the chat system is online
            // For now, we'll just simulate it's always online
            const indicators = document.querySelectorAll('.status-indicator');
            indicators.forEach(indicator => {
                indicator.style.background = '#27ae60'; // Green for online
                indicator.title = 'System Online';
            });
        }
        
        checkSystemStatus();
        
        // Update status every 30 seconds
        setInterval(checkSystemStatus, 30000);
    </script>
</body>
</html>