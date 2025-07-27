<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_city = $_SESSION['admin_city'];
$admin_barangay = $_SESSION['admin_barangay'];

// Handle ticket status updates
if (isset($_POST['update_status'])) {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['status'];
    $admin_response = trim($_POST['admin_response']);
    
    try {
        $stmt = $pdo->prepare("UPDATE tickets SET status = ?, admin_response = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_status, $admin_response, $ticket_id]);
        $success_message = "Ticket updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Failed to update ticket. Please try again.";
    }
}

// Get dashboard statistics
try {
    // Total tickets
    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM tickets");
    $totalStmt->execute();
    $total_tickets = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tickets by status
    $statusStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $statusStmt->execute();
    $status_counts = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $counts = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
    foreach ($status_counts as $count) {
        $counts[$count['status']] = $count['count'];
    }
    
    // Recent tickets
    $recentStmt = $pdo->prepare("SELECT t.*, u.first_name, u.middle_name, u.last_name, u.email FROM tickets t 
                                 JOIN users u ON t.user_id = u.id 
                                 ORDER BY t.created_at DESC LIMIT 10");
    $recentStmt->execute();
    $recent_tickets = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Today's tickets
    $todayStmt = $pdo->prepare("SELECT COUNT(*) as today_count FROM tickets WHERE DATE(created_at) = CURDATE()");
    $todayStmt->execute();
    $today_tickets = $todayStmt->fetch(PDO::FETCH_ASSOC)['today_count'];
    
    // Priority distribution
    $priorityStmt = $pdo->prepare("SELECT priority, COUNT(*) as count FROM tickets GROUP BY priority");
    $priorityStmt->execute();
    $priority_counts = $priorityStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $priorities = ['low' => 0, 'medium' => 0, 'high' => 0, 'urgent' => 0];
    foreach ($priority_counts as $priority) {
        $priorities[$priority['priority']] = $priority['count'];
    }
    
} catch (PDOException $e) {
    $total_tickets = 0;
    $counts = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
    $recent_tickets = [];
    $today_tickets = 0;
    $priorities = ['low' => 0, 'medium' => 0, 'high' => 0, 'urgent' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Help Desk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .logout-btn {
            background: #34495e;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #2c3e50;
            transform: translateY(-2px);
        }
        
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
        }
        
        .welcome-title {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--accent-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card.total { --accent-color: #3498db; }
        .stat-card.open { --accent-color: #e67e22; }
        .stat-card.progress { --accent-color: #f39c12; }
        .stat-card.resolved { --accent-color: #27ae60; }
        .stat-card.today { --accent-color: #9b59b6; }
        .stat-card.urgent { --accent-color: #e74c3c; }
        
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .ticket-table th,
        .ticket-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .ticket-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ticket-table tr:hover {
            background: #f8f9fa;
        }
        
        .ticket-id {
            font-weight: 600;
            color: #e74c3c;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .ticket-subject {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-open { background: #fff3cd; color: #856404; }
        .status-in-progress { background: #d1ecf1; color: #0c5460; }
        .status-resolved { background: #d4edda; color: #155724; }
        .status-closed { background: #f8d7da; color: #721c24; }
        
        .priority-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-low { background: #e8f5e8; color: #2e7d32; }
        .priority-medium { background: #fff3e0; color: #f57c00; }
        .priority-high { background: #ffebee; color: #c62828; }
        .priority-urgent { background: #fce4ec; color: #ad1457; }
        
        .action-btn {
            background: #3498db;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        
        .priority-chart {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .priority-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .priority-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .priority-count {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .priority-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #7f8c8d;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-action-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .sos-btn {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: block;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            animation: pulse 2s infinite;
        }
        
        .sos-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 107, 53, 0.4);
            animation: none;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3); }
            50% { box-shadow: 0 4px 25px rgba(255, 107, 53, 0.5); }
            100% { box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3); }
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .close-btn:hover {
            color: #e74c3c;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        select:focus, textarea:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .admin-info {
                flex-direction: column;
                text-align: center;
            }
            
            .ticket-table {
                font-size: 12px;
            }
            
            .ticket-table th,
            .ticket-table td {
                padding: 8px 4px;
            }
            
            .ticket-subject {
                max-width: 120px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">⚙️ Admin Control Panel</div>
            <div class="admin-info">
                <div class="admin-avatar"><?php echo strtoupper(substr($admin_username, 0, 1)); ?></div>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($admin_username); ?></div>
                    <div class="admin-badge">Administrator</div>
                    <div style="font-size: 12px; color: #7f8c8d;">
                        <?php echo htmlspecialchars($admin_city); ?> - Barangay <?php echo htmlspecialchars($admin_barangay); ?>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1 class="welcome-title">Admin Dashboard</h1>
            <p>Monitor and manage help desk tickets for <?php echo htmlspecialchars($admin_city); ?> - Barangay <?php echo htmlspecialchars($admin_barangay); ?></p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_tickets; ?></div>
                <div class="stat-label">Total Tickets</div>
            </div>
            <div class="stat-card open">
                <div class="stat-number"><?php echo $counts['open']; ?></div>
                <div class="stat-label">Open Tickets</div>
            </div>
            <div class="stat-card progress">
                <div class="stat-number"><?php echo $counts['in_progress']; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card resolved">
                <div class="stat-number"><?php echo $counts['resolved']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card today">
                <div class="stat-number"><?php echo $today_tickets; ?></div>
                <div class="stat-label">Today's Tickets</div>
            </div>
            <div class="stat-card urgent">
                <div class="stat-number"><?php echo $priorities['urgent']; ?></div>
                <div class="stat-label">Urgent Priority</div>
            </div>
        </div>

        <div class="main-content">
            <div class="section">
                <h2 class="section-title">🎫 Recent Tickets</h2>
                
                <div style="overflow-x: auto;">
                    <table class="ticket-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Subject</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_tickets)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">
                                        No tickets found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_tickets as $ticket): ?>
                                    <tr>
                                        <td class="ticket-id">#<?php echo $ticket['id']; ?></td>
                                        <td class="user-name">
                                            <?php 
                                            $fullName = trim($ticket['first_name'] . ' ' . 
                                                       ($ticket['middle_name'] ? $ticket['middle_name'] . ' ' : '') . 
                                                       $ticket['last_name']);
                                            echo htmlspecialchars($fullName); 
                                            ?>
                                        </td>
                                        <td class="ticket-subject" title="<?php echo htmlspecialchars($ticket['subject']); ?>">
                                            <?php echo htmlspecialchars($ticket['subject']); ?>
                                        </td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $ticket['priority']; ?>">
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                        <td>
                                            <button class="action-btn" onclick="openTicketModal(<?php echo htmlspecialchars(json_encode($ticket)); ?>)">
                                                Manage
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($recent_tickets) >= 10): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="all_tickets.php" style="color: #e74c3c; text-decoration: none; font-weight: 600;">
                            View All Tickets →
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display: flex; flex-direction: column; gap: 30px;">
                <div class="section">
                    <h2 class="section-title">📊 Priority Distribution</h2>
                    
                    <div class="priority-chart">
                        <div class="priority-item">
                            <div class="priority-count" style="color: #2e7d32;"><?php echo $priorities['low']; ?></div>
                            <div class="priority-label">Low Priority</div>
                        </div>
                        <div class="priority-item">
                            <div class="priority-count" style="color: #f57c00;"><?php echo $priorities['medium']; ?></div>
                            <div class="priority-label">Medium Priority</div>
                        </div>
                        <div class="priority-item">
                            <div class="priority-count" style="color: #c62828;"><?php echo $priorities['high']; ?></div>
                            <div class="priority-label">High Priority</div>
                        </div>
                        <div class="priority-item">
                            <div class="priority-count" style="color: #ad1457;"><?php echo $priorities['urgent']; ?></div>
                            <div class="priority-label">Urgent Priority</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h2 class="section-title">⚡ Quick Actions</h2>
                    
                    <!-- SOS Button - Prominent placement at top -->
                    <div style="margin-bottom: 20px;">
                        <a href="admin.php" class="sos-btn">
                            🚨 SOS
                        </a>
                    </div>
                    
                    <div class="quick-actions">
                        <a href="all_tickets.php" class="quick-action-btn">
                            📋 All Tickets
                        </a>
                        <a href="reports.php" class="quick-action-btn">
                            📈 Reports
                        </a>
                        <a href="users.php" class="quick-action-btn">
                            👥 Manage Users
                        </a>
                        <a href="settings.php" class="quick-action-btn">
                            ⚙️ Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Management Modal -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Manage Ticket</h3>
                <button class="close-btn" onclick="closeTicketModal()">&times;</button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="ticketId" name="ticket_id">
                
                <div class="form-group">
                    <label>Ticket Details</label>
                    <div id="ticketDetails" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;"></div>
                </div>
                
                <div class="form-group">
                    <label for="status">Update Status</label>
                    <select id="status" name="status" required>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="admin_response">Admin Response</label>
                    <textarea id="admin_response" name="admin_response" placeholder="Add your response or update notes..."></textarea>
                </div>
                
                <button type="submit" name="update_status" class="btn">Update Ticket</button>
            </form>
        </div>
    </div>

    <script>
        function openTicketModal(ticket) {
            document.getElementById('ticketId').value = ticket.id;
            document.getElementById('modalTitle').textContent = 'Manage Ticket #' + ticket.id;
            document.getElementById('status').value = ticket.status;
            document.getElementById('admin_response').value = ticket.admin_response || '';
            
            const details = `
                <strong>Subject:</strong> ${ticket.subject}<br>
                <strong>User:</strong> ${ticket.first_name} ${ticket.last_name} (${ticket.email})<br>
                <strong>Category:</strong> ${ticket.category.replace('_', ' ')}<br>
                <strong>Priority:</strong> ${ticket.priority}<br>
                <strong>Created:</strong> ${new Date(ticket.created_at).toLocaleString()}<br>
                <strong>Description:</strong><br>
                <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 4px;">
                    ${ticket.description}
                </div>
            `;
            
            document.getElementById('ticketDetails').innerHTML = details;
            document.getElementById('ticketModal').style.display = 'block';
        }
        
        function closeTicketModal() {
            document.getElementById('ticketModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('ticketModal');
            if (event.target === modal) {
                closeTicketModal();
            }
        }
        
        // Auto-refresh every 5 minutes
        setInterval(function() {
            if (!document.getElementById('ticketModal').style.display || 
                document.getElementById('ticketModal').style.display === 'none') {
                location.reload();
            }
        }, 300000);
        
        // Add confirmation for status changes
        document.querySelector('form').addEventListener('submit', function(e) {
            const status = document.getElementById('status').value;
            const ticketId = document.getElementById('ticketId').value;
            
            if (!confirm(`Are you sure you want to update ticket #${ticketId} to "${status.replace('_', ' ')}"?`)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>