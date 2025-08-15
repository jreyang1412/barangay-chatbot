<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Help Desk System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-orange: #FF6B35;
            --dark-orange: #E85A2C;
            --light-orange: #FFE5DB;
            --white: #FFFFFF;
            --gray: #6B7280;
            --light-gray: #F3F4F6;
            --success: #10B981;
            --error: #EF4444;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, var(--light-orange) 0%, var(--white) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            line-height: 1.6;
        }
        
        .container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(255, 107, 53, 0.08);
            padding: 32px;
            max-width: 1000px;
            width: 100%;
            animation: fadeIn 0.6s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
            color: var(--white);
        }
        
        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }
        
        .subtitle {
            color: var(--gray);
            font-size: 16px;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .auth-section {
            background: var(--light-gray);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
            text-align: center;
        }
        
        .auth-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--primary-orange);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .auth-description {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        
        .input-group {
            position: relative;
            max-width: 320px;
            margin: 0 auto;
        }
        
        .password-input {
            width: 100%;
            padding: 12px 48px 12px 16px;
            border: 2px solid transparent;
            border-radius: 12px;
            font-size: 16px;
            background: var(--white);
            transition: all 0.3s ease;
            outline: none;
        }
        
        .password-input:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            padding: 4px;
            font-size: 18px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
        }
        
        .toggle-password i {
            pointer-events: none;
        }
        
        .toggle-password:hover {
            background: var(--light-gray);
            color: var(--primary-orange);
        }
        
        .toggle-password:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .btn {
            background: linear-gradient(135deg, var(--primary-orange), var(--dark-orange));
            color: var(--white);
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 16px;
            min-width: 160px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.25);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .message {
            margin-top: 16px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .error-message {
            color: var(--error);
            background: #FEE2E2;
        }
        
        .success-message {
            color: var(--success);
            background: #D1FAE5;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .card {
            background: var(--white);
            border: 2px solid var(--light-gray);
            border-radius: 16px;
            padding: 24px;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-orange), var(--dark-orange));
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(255, 107, 53, 0.12);
            border-color: var(--primary-orange);
        }
        
        .card-icon {
            width: 48px;
            height: 48px;
            background: var(--light-orange);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }
        
        .card-description {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .features {
            list-style: none;
        }
        
        .features li {
            color: var(--gray);
            font-size: 13px;
            padding: 6px 0;
            padding-left: 24px;
            position: relative;
        }
        
        .features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary-orange);
            font-weight: bold;
        }
        
        .status {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.6;
                transform: scale(1.2);
            }
        }
        
        .footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid var(--light-gray);
        }
        
        .footer-text {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 16px;
        }
        
        .tags {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .tag {
            background: var(--light-orange);
            color: var(--primary-orange);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .hidden {
            display: none !important;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 24px;
        }
        
        .back-button:hover {
            background: var(--light-gray);
            color: var(--primary-orange);
            transform: translateX(-4px);
        }
        
        .back-button span:first-child {
            font-size: 18px;
            transition: transform 0.3s ease;
        }
        
        .back-button:hover span:first-child {
            transform: translateX(-4px);
        }
        
        /* Mobile Responsive */
        @media (max-width: 640px) {
            body {
                padding: 12px;
            }
            
            .container {
                padding: 24px 16px;
                border-radius: 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .subtitle {
                font-size: 14px;
            }
            
            .auth-section {
                padding: 24px 16px;
            }
            
            .cards-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .card {
                padding: 20px;
            }
            
            .btn {
                width: 100%;
                padding: 14px;
            }
            
            .password-input {
                font-size: 16px;
                padding: 14px 16px;
            }
            
            .back-button {
                padding: 6px 12px;
                font-size: 13px;
            }
        }
        
        /* Tablet */
        @media (min-width: 641px) and (max-width: 1024px) {
            .cards-grid {
                grid-template-columns: 1fr;
                max-width: 500px;
                margin: 0 auto 40px;
            }
        }
        
        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --white: #1F2937;
                --light-gray: #374151;
                --gray: #9CA3AF;
            }
            
            body {
                background: linear-gradient(135deg, #1F2937 0%, #111827 100%);
            }
            
            .container {
                background: #111827;
                box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
            }
            
            h1 {
                color: #F3F4F6;
            }
            
            .auth-section {
                background: #1F2937;
            }
            
            .password-input {
                background: #374151;
                color: #F3F4F6;
            }
            
            .toggle-password {
                color: #9CA3AF;
            }
            
            .toggle-password:hover {
                background: #374151;
                color: var(--primary-orange);
            }
            
            .card {
                background: #1F2937;
                border-color: #374151;
            }
            
            .card-title {
                color: #F3F4F6;
            }
            
            .card-icon {
                background: rgba(255, 107, 53, 0.1);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">
            <span>←</span>
            <span>Back to Home</span>
        </a>
        
        <div class="header">
            <div class="logo">⚙️</div>
            <h1>Admin Portal</h1>
            <p class="subtitle">Administrative access for barangay officials</p>
        </div>
        
        <div class="auth-section" id="passwordProtection">
            <h3 class="auth-title">
                <span>🔒</span>
                <span>Restricted Access</span>
            </h3>
            <p class="auth-description">
                This section is restricted to authorized officials only.<br>
                Please enter your access code to continue.
            </p>
            <div class="input-group">
                <input type="password" 
                       class="password-input" 
                       id="adminPassword" 
                       placeholder="Enter access code"
                       maxlength="20"
                       autocomplete="off">
                <button type="button" class="toggle-password" id="togglePassword" onclick="togglePasswordVisibility()">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
            <button class="btn" onclick="checkPassword()">Unlock Admin Portal</button>
            <div id="passwordMessage"></div>
        </div>
        
        <div class="cards-grid hidden" id="adminButtons">
            <a href="admin_login.php" class="card">
                <div class="status"></div>
                <div class="card-icon">🔐</div>
                <h3 class="card-title">Admin Login</h3>
                <p class="card-description">Access administrative dashboard</p>
            </a>
            
            <a href="admin_register.php" class="card">
                <div class="status"></div>
                <div class="card-icon">👨‍💼</div>
                <h3 class="card-title">Admin Register</h3>
                <p class="card-description">Create administrative account</p>
            </a>
        </div>
        
        <div class="footer">
            <p class="footer-text"><strong>Official • Secure • Monitored</strong></p>
            <div class="tags">
                <span class="tag">Admin Panel</span>
                <span class="tag">Role Management</span>
                <span class="tag">Audit Logs</span>
                <span class="tag">Security</span>
            </div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('adminPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        }
        
        function checkPassword() {
            const password = document.getElementById('adminPassword').value;
            const messageDiv = document.getElementById('passwordMessage');
            const passwordProtection = document.getElementById('passwordProtection');
            const adminButtons = document.getElementById('adminButtons');
            
            if (password === 'barangayofficials') {
                messageDiv.innerHTML = '<div class="message success-message">✅ Access granted</div>';
                
                setTimeout(() => {
                    passwordProtection.style.transition = 'all 0.4s ease';
                    passwordProtection.style.opacity = '0';
                    passwordProtection.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        passwordProtection.classList.add('hidden');
                        adminButtons.classList.remove('hidden');
                        adminButtons.style.opacity = '0';
                        
                        setTimeout(() => {
                            adminButtons.style.transition = 'all 0.4s ease';
                            adminButtons.style.opacity = '1';
                        }, 50);
                    }, 400);
                }, 1000);
            } else {
                messageDiv.innerHTML = '<div class="message error-message">❌ Invalid access code</div>';
                document.getElementById('adminPassword').value = '';
                
                // Reset password visibility to hidden
                const passwordInput = document.getElementById('adminPassword');
                passwordInput.type = 'password';
                document.getElementById('eyeIcon').className = 'bi bi-eye';
                
                passwordInput.style.animation = 'shake 0.4s';
                setTimeout(() => {
                    passwordInput.style.animation = '';
                }, 400);
            }
        }
        
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
                25% { transform: translateX(-4px); }
                75% { transform: translateX(4px); }
            }
        `;
        document.head.appendChild(style);
        
        // Touch feedback for mobile
        document.querySelectorAll('.card, .btn').forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            element.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
        
        // Auto-focus password input on load
        window.addEventListener('load', function() {
            document.getElementById('adminPassword').focus();
        });
        
        // Prevent zoom on double tap (mobile)
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(e) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>
</html>