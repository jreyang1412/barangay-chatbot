<?php
session_start();

// Simple error handling to prevent 500 errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Initialize variables with defaults to prevent undefined variable errors
$admin = array(
    'username' => $_SESSION['admin_username'] ?? 'Admin',
    'first_name' => $_SESSION['admin_first_name'] ?? '',
    'last_name' => $_SESSION['admin_last_name'] ?? '',
    'city' => $_SESSION['admin_city'] ?? 'Unknown',
    'barangay_number' => $_SESSION['admin_barangay'] ?? 'Unknown'
);

$chatNotifications = 0;

// Only try to connect to database if config exists
if (file_exists('config.php')) {
    try {
        require_once 'config.php';
        
        // Get admin details from database if PDO connection exists
        if (isset($pdo)) {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $adminData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($adminData) {
                $admin = $adminData;
            }
            
            // Get chat notifications count with error handling
            try {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as unread_count
                    FROM messages m
                    JOIN conversations c ON m.conversation_id = c.id
                    WHERE (c.city = ? OR c.barangay = ? OR c.assigned_admin_id = ?)
                    AND m.sender_type = 'user'
                    AND m.is_read_by_admin = 0
                    AND m.is_active = 1
                    AND c.status IN ('waiting', 'active')
                ");
                
                $stmt->execute([$admin['city'], $admin['barangay_number'], $_SESSION['admin_id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $chatNotifications = $result['unread_count'] ?? 0;
            } catch (Exception $e) {
                // Silently continue if chat counting fails
                $chatNotifications = 0;
            }
        }
    } catch (Exception $e) {
        // Continue with default values if database connection fails
        error_log("Database error in admin dashboard: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Help Desk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff8a00 0%, #e52e71 100%);
            min-height: 100vh;
            color: #2c3e50;
        }
        
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
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255, 138, 0, 0.1);
            color: #ff8a00;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #ff8a00, #e52e71);
            color: white;
        }

        .chat-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ff8a00, #e52e71);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .admin-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(255, 138, 0, 0.3);
        }

        .admin-avatar-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .admin-avatar-img:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(255, 138, 0, 0.3);
        }

        .admin-avatar-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .logout-btn {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        /* Main Dashboard Styles */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .dashboard-title {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ff8a00, #e52e71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .quick-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            border: 2px solid #ecf0f1;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            border-color: #ff8a00;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 138, 0, 0.15);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #ff8a00;
        }

        .action-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .action-description {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .notification-badge {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .admin-info {
                order: -1;
                width: 100%;
                justify-content: space-between;
            }

            .nav-links {
                order: 1;
                width: 100%;
                justify-content: center;
            }
        }

        .error-banner {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🛡️ Barangay Help Desk - <?php echo htmlspecialchars($admin['city'] . ', Brgy ' . $admin['barangay_number']); ?></div>
            <div class="nav-links">
                <a href="admin_dashboard.php" class="nav-link active">Dashboard</a>
                <a href="admin.php" class="nav-link">
                    💬 Chat
                    <?php if ($chatNotifications > 0): ?>
                        <span class="chat-notification"><?php echo $chatNotifications; ?></span>
                    <?php endif; ?>
                </a>
                <a href="barangay_request.php" class="nav-link">📋 Requests</a>
                <a href="verifier.php" class="nav-link">✓ Verifier</a>
            </div>
            <div class="admin-info">
                <?php if (!empty($admin['profile_picture']) && file_exists($admin['profile_picture'])): ?>
                    <a href="admin_edit.php" class="admin-avatar-img" title="Edit Profile">
                        <img src="<?php echo htmlspecialchars($admin['profile_picture']); ?>" alt="Admin Profile Picture">
                    </a>
                <?php else: ?>
                    <a href="admin_edit.php" class="admin-avatar" title="Edit Profile">
                        <?php 
                        $initial = 'A';
                        if (!empty($admin['first_name'])) {
                            $initial = strtoupper($admin['first_name'][0]);
                        } elseif (!empty($admin['username'])) {
                            $initial = strtoupper($admin['username'][0]);
                        }
                        echo $initial;
                        ?>
                    </a>
                <?php endif; ?>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin['username']); ?></div>
                    <div style="font-size: 12px; color: #6c757d;">
                        <?php echo htmlspecialchars($admin['city'] . ', Brgy ' . $admin['barangay_number']); ?>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to log out?')">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (!file_exists('config.php')): ?>
        <div class="error-banner">
            ⚠️ <strong>Database Configuration Missing</strong><br>
            The config.php file is not found. Please ensure database configuration is properly set up.
        </div>
        <?php endif; ?>

        <div class="dashboard-header">
            <h1 class="dashboard-title">QBAGWIS</h1>
            <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                <strong>Q</strong>uick <strong>B</strong>arangay <strong>A</strong>ksyon <strong>G</strong>uidance <strong>W</strong>astong <strong>I</strong>mpormasyon at <strong>S</strong>erbisyo
            </p>
            <p>Welcome to the Barangay Help Desk Administration Center</p>
        </div>

        <div class="quick-actions">
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                ⚡ Quick Actions
            </h3>
            
            <div class="action-grid">
                <div class="action-card" onclick="window.location.href='admin.php'">
                    <div class="action-icon">💬</div>
                    <div class="action-title">Chat Center</div>
                    <div class="action-description">Manage user conversations and provide real-time support</div>
                    <?php if ($chatNotifications > 0): ?>
                        <div class="notification-badge">
                            <?php echo $chatNotifications; ?> new message<?php echo $chatNotifications > 1 ? 's' : ''; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="action-card" onclick="window.location.href='barangay_request.php'">
                    <div class="action-icon">📋</div>
                    <div class="action-title">View Requests</div>
                    <div class="action-description">Review and process barangay service requests</div>
                </div>
                
                <div class="action-card" onclick="window.location.href='verifier.php'">
                    <div class="action-icon">✅</div>
                    <div class="action-title">User Verifier</div>
                    <div class="action-description">Verify user accounts and manage access levels</div>
                </div>
                
                <div class="action-card" onclick="window.location.href='setup_chat.php'">
                    <div class="action-icon">⚙️</div>
                    <div class="action-title">System Setup</div>
                    <div class="action-description">Configure and initialize system components</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh chat notifications every 30 seconds (only if config exists)
        <?php if (file_exists('config.php') && isset($pdo)): ?>
        setInterval(function() {
            fetch('admin_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_conversations&admin_id=<?php echo $_SESSION['admin_id']; ?>&city=<?php echo urlencode($admin['city']); ?>&barangay=<?php echo urlencode($admin['barangay_number']); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const totalUnread = data.conversations.reduce((sum, conv) => sum + parseInt(conv.unread_count), 0);
                    updateNotificationBadges(totalUnread);
                }
            })
            .catch(error => {
                console.log('Error checking notifications:', error);
            });
        }, 30000);
        <?php endif; ?>

        function updateNotificationBadges(count) {
            const chatNotification = document.querySelector('.chat-notification');
            const notificationBadge = document.querySelector('.notification-badge');
            
            if (count > 0) {
                if (chatNotification) {
                    chatNotification.textContent = count;
                    chatNotification.style.display = 'flex';
                } else {
                    // Create notification badge if it doesn't exist
                    const chatLink = document.querySelector('a[href="admin.php"]');
                    if (chatLink) {
                        const badge = document.createElement('span');
                        badge.className = 'chat-notification';
                        badge.textContent = count;
                        chatLink.appendChild(badge);
                    }
                }
                
                if (notificationBadge) {
                    notificationBadge.textContent = `${count} new message${count > 1 ? 's' : ''}`;
                }
            } else {
                if (chatNotification) {
                    chatNotification.style.display = 'none';
                }
                if (notificationBadge) {
                    notificationBadge.style.display = 'none';
                }
            }
        }

        // Add hover effects and click animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.action-card');
            
            cards.forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>