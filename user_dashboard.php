<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

require_once 'config.php';

// Get user status and barangay from database
try {
    $stmt = $pdo->prepare("
        SELECT status, is_active, barangay, first_name, last_name, profile_picture
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        session_destroy();
        header("Location: user_login.php");
        exit();
    }
    
    $user_status   = $user_data['status'];
    $is_active     = $user_data['is_active'];
    $user_barangay = $user_data['barangay'];
    $first_name    = $user_data['first_name'];
    $last_name     = $user_data['last_name'];
    $profile_picture = $user_data['profile_picture'];

    // Check if user account is active
    if (!$is_active) {
        session_destroy();
        header("Location: user_login.php?error=account_deactivated");
        exit();
    }
    
} catch (Exception $e) {
    session_destroy();
    header("Location: user_login.php");
    exit();
}

// Check if user has existing verification request
$verification_status = null;
try {
    $stmt = $pdo->prepare("SELECT status, created_at FROM verification_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $verification_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($verification_data) {
        $verification_status = $verification_data;
    }
} catch (Exception $e) {
    // Continue without verification status
}

// Barangay services data
$barangay_services = [
    'barangay_clearance' => [
        'name' => 'Barangay Clearance',
        'description' => 'Certifies that a person had no bad record in the barangay and is a resident',
        'required_for' => 'Job application, business permit, police clearance',
        'icon' => '📋'
    ],
    'certificate_of_residency' => [
        'name' => 'Certificate of Residency',
        'description' => 'Confirms a person is residing in the barangay',
        'required_for' => 'Scholarship application, court documentation',
        'icon' => '🏠'
    ],
    'certificate_of_indigency' => [
        'name' => 'Certificate of Indigency',
        'description' => 'Certifies that a person belongs to a low-income or indigent family',
        'required_for' => 'Medical assistance, PAO, scholarship, PhilHealth, social welfare benefits',
        'icon' => '💰'
    ],
    'business_clearance' => [
        'name' => 'Barangay Business Clearance',
        'description' => 'Certifies that a business is authorized to operate in the barangay',
        'required_for' => 'Business permit application with city/municipal hall',
        'icon' => '🏢'
    ],
    'solo_parent_certificate' => [
        'name' => 'Solo Parent Certificate',
        'description' => 'Confirms the status of an individual as solo parent',
        'required_for' => 'Accessing benefits under the Solo Parents\' Welfare Act',
        'icon' => '👨‍👧‍👦'
    ],
    'barangay_id' => [
        'name' => 'Barangay ID',
        'description' => 'Confirmation of identity and residency of the individual',
        'required_for' => 'Secondary ID and identification of residency',
        'icon' => '🆔'
    ],
    'event_permit' => [
        'name' => 'Permit for Event',
        'description' => 'Certifies that the local gathering is safe for public',
        'required_for' => 'Local gathering, fiesta, and public activities',
        'icon' => '🎉'
    ],
    'calamity_certificate' => [
        'name' => 'Certificate of Calamity Victim',
        'description' => 'Certifies that holder is a victim of disaster (e.g., Fire, flood)',
        'required_for' => 'Relief assistance, insurance claim, or financial aid',
        'icon' => '🆘'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Barangay Help Desk</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ff914d 0%, #ff5e00 100%);
    min-height: 100vh;
    color: #2c3e50;
}

/* NAVBAR */
.navbar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    padding: 15px 0;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}

.nav-links {
    display: flex;
    gap: 20px;
    align-items: center;
}

.nav-link {
    color: #2c3e50;
    text-decoration: none;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: rgba(255, 145, 77, 0.1);
    color: #ff914d;
}

.nav-link.active {
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    color: white;
}

.logo {
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* USER INFO */
.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.user-avatar-initial {
    font-size: 16px;
    font-weight: 600;
}

.user-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(255, 145, 77, 0.4);
}

.user-avatar::after {
    content: "✏️";
    position: absolute;
    bottom: -2px;
    right: -2px;
    font-size: 12px;
    background: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.user-avatar:hover::after {
    opacity: 1;
}

.user-status {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-basic {
    background: #fff3cd;
    color: #856404;
}

.status-verified {
    background: #d4edda;
    color: #155724;
}

.logout-btn {
    background: #e74c3c;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

/* CONTENT CONTAINERS */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.welcome-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.welcome-title {
    font-size: 2rem;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ALERTS & NOTICES */
.access-restriction-notice {
    background: linear-gradient(135deg, #ff6b6b, #ffb84d);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 30px;
    text-align: center;
    font-weight: 600;
    z-index: 2;
}

/* SERVICES GRID */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.service-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    z-index: 1;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    border-color: #ff914d;
}

.service-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: #ff5e00;
}

.service-name {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #2c3e50;
}

.service-description {
    color: #7f8c8d;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.service-required {
    color: #27ae60;
    font-size: 0.8rem;
    font-weight: 600;
}

/* CONTACT SECTION */
.contact-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.contact-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.get-verified-btn {
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 15px;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.get-verified-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(255, 145, 77, 0.4);
}

.verification-pending {
    background: #fff3e0;
    color: #e65100;
    padding: 15px;
    border-radius: 10px;
    margin-top: 15px;
    border-left: 4px solid #ff914d;
}

.verification-rejected {
    background: #ffe6e6;
    color: #cc0000;
    padding: 15px;
    border-radius: 10px;
    margin-top: 15px;
    border-left: 4px solid #dc3545;
}

.contact-icon {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #ff5e00;
}

/* ACTION BUTTONS */
.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
}

.action-btn {
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.action-btn.secondary {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.action-btn.chat {
    background: linear-gradient(135deg, #6c5ce7, #a29bfe);
}

.section-title {
    font-size: 1.5rem;
    margin-bottom: 20px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
    }

    .nav-container {
        flex-direction: column;
        gap: 15px;
    }

    .nav-links {
        order: -1;
    }

    .contact-info {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
}

    </style>
</head>
<body>
   <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🛡️ Barangay <?php echo $user_barangay; ?></div>
            <div class="nav-links">
                <a href="user_dashboard.php" class="nav-link active">Dashboard</a>
                <?php if ($user_status === 'verified'): ?>
                    <a href="user_forms.php" class="nav-link">Request Forms</a>
                <?php endif; ?>
                <a href="user_requests.php" class="nav-link">My Requests</a>
                <a href="user.php" class="nav-link">💬 Chat Support</a>
            </div>
            <div class="user-info">
                <a href="user_edit.php" class="user-avatar" title="Edit Profile">
                    <?php if (!empty($profile_picture) && file_exists($profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <span class="user-avatar-initial"><?php echo strtoupper($first_name[0]); ?></span>
                    <?php endif; ?>
                </a>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div style="font-size: 12px; color: #7f8c8d;">
                        <?php echo $first_name . " " . $last_name; ?>
                    </div>
                </div>
                <div class="user-status status-<?php echo $user_status; ?>">
                    <?php echo ucfirst($user_status); ?>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </nav>


    <div class="container">
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome to Barangay <?php echo $user_barangay; ?> Services</h1>
            <?php if ($user_status === 'basic'): ?>
                <p>Your account has <strong>Basic</strong> access.</p>
            <?php else: ?>
                <p>Access barangay certificates and documents online. Manage your requests and stay updated on their status.</p>
                <div class="action-buttons">
                    <a href="user_forms.php" class="action-btn">📝 Request New Form</a>
                    <a href="user_requests.php" class="action-btn secondary">📋 View My Requests</a>
                    <a href="user.php" class="action-btn chat">💬 Chat Support</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($user_status === 'basic'): ?>
            <div class="access-restriction-notice">
                🔒 <strong>Account Verification Required</strong><br>
                Your account currently has basic access. Submit your verification request or visit the barangay office for account verification.
                
                <?php if (!$verification_status): ?>
                    <a href="verification.php" class="get-verified-btn">
                        ✅ Get Verified Now
                    </a>
                <?php elseif ($verification_status['status'] === 'pending'): ?>
                    <div class="verification-pending">
                        📋 <strong>Verification Pending</strong><br>
                        Your verification request is being reviewed. Submitted on <?php echo date('M j, Y', strtotime($verification_status['created_at'])); ?>
                    </div>
                <?php elseif ($verification_status['status'] === 'rejected'): ?>
                    <div class="verification-rejected">
                        ❌ <strong>Verification Rejected</strong><br>
                        Your previous request was rejected. You can submit a new request.
                        <a href="verification.php" class="get-verified-btn">
                            🔄 Submit New Request
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="user.php" class="action-btn chat">💬 Chat Support</a>
                </div>
            </div>
            
            <div class="contact-section" id="contact">
                <h2 class="section-title">📞 Contact Information</h2>
                <p>Visit or contact the barangay office to verify your account and access online services.</p>
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">🏢</div>
                        <h4>Barangay Office</h4>
                        <p>123 Barangay Hall Street<br>Your City, Metro Manila</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <h4>Phone</h4>
                        <p>(02) 123-4567</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <h4>Email</h4>
                        <p>info@barangay.gov.ph</p>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <h4>Office Hours</h4>
                        <p>Mon-Fri: 8:00 AM - 5:00 PM</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Chat Support Section for Verified Users -->
            <div class="contact-section">
                <h2 class="section-title">💬 Need Help?</h2>
                <p>Get instant support through our chat system or contact the barangay office directly.</p>
                <div class="action-buttons">
                    <a href="user.php" class="action-btn chat">💬 Start Chat Support</a>
                    <a href="#contact-info" class="action-btn secondary">📞 Office Contact</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="services-grid">
            <?php foreach ($barangay_services as $service_key => $service): ?>
                <div class="service-card">
                    <div class="service-icon"><?php echo $service['icon']; ?></div>
                    <div class="service-name"><?php echo $service['name']; ?></div>
                    <div class="service-description"><?php echo $service['description']; ?></div>
                    <div class="service-required">Required for: <?php echo $service['required_for']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Contact Information Section -->
        <div class="contact-section" id="contact-info">
            <h2 class="section-title">📞 Barangay Office Information</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">🏢</div>
                    <h4>Barangay Office</h4>
                    <p>123 Barangay Hall Street<br><?php echo htmlspecialchars($user_barangay); ?>, Metro Manila</p>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <h4>Phone</h4>
                    <p>(02) 123-4567</p>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📧</div>
                    <h4>Email</h4>
                    <p>barangay<?php echo strtolower(str_replace(' ', '', $user_barangay)); ?>@gov.ph</p>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">🕒</div>
                    <h4>Office Hours</h4>
                    <p>Mon-Fri: 8:00 AM - 5:00 PM<br>Sat: 8:00 AM - 12:00 PM</p>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <h4 style="color: #2c3e50; margin-bottom: 15px;">📱 Digital Services Available</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; text-align: left;">
                    <div>
                        <strong>💬 Chat Support</strong><br>
                        <span style="font-size: 14px; color: #666;">Instant help and inquiries</span>
                    </div>
                    <?php if ($user_status === 'verified'): ?>
                    <div>
                        <strong>📝 Online Forms</strong><br>
                        <span style="font-size: 14px; color: #666;">Submit certificate requests</span>
                    </div>
                    <div>
                        <strong>📋 Request Tracking</strong><br>
                        <span style="font-size: 14px; color: #666;">Monitor application status</span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <strong>📞 Virtual Assistance</strong><br>
                        <span style="font-size: 14px; color: #666;">Get guidance and information</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add hover effects to cards
            const cards = document.querySelectorAll('.service-card, .action-btn');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Highlight chat support for basic users
            <?php if ($user_status === 'basic'): ?>
            const chatButtons = document.querySelectorAll('.action-btn.chat');
            chatButtons.forEach(btn => {
                btn.style.animation = 'pulse 2s infinite';
            });

            // Add pulse animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse {
                    0% { box-shadow: 0 0 0 0 rgba(108, 92, 231, 0.7); }
                    70% { box-shadow: 0 0 0 10px rgba(108, 92, 231, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(108, 92, 231, 0); }
                }
            `;
            document.head.appendChild(style);
            <?php endif; ?>
        });
    </script>
</body>
</html>