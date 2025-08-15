<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

require_once 'config.php';

// Get user status from database
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

// Get user's requests
$user_requests = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM barangay_requests 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $user_requests = [];
}

// Pagination
$requests_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$total_requests = count($user_requests);
$total_pages = ceil($total_requests / $requests_per_page);
$offset = ($current_page - 1) * $requests_per_page;
$paginated_requests = array_slice($user_requests, $offset, $requests_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - Barangay Help Desk</title>
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

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.page-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.page-title {
    font-size: 2rem;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.requests-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.5rem;
    margin-bottom: 20px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.requests-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    border-left: 4px solid;
}

.stat-card.total { border-color: #ff914d; }
.stat-card.pending { border-color: #ffc107; }
.stat-card.processing { border-color: #17a2b8; }
.stat-card.ready { border-color: #28a745; }
.stat-card.completed { border-color: #007bff; }
.stat-card.rejected { border-color: #dc3545; }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.9rem;
    color: #7f8c8d;
    text-transform: uppercase;
    font-weight: 600;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.requests-table th,
.requests-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
}

.requests-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.requests-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #d1ecf1; color: #0c5460; }
.status-ready { background: #d4edda; color: #155724; }
.status-completed { background: #ffe5cc; color: #994d00; }
.status-rejected { background: #f8d7da; color: #721c24; }

.no-requests {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.no-requests-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
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
    margin-top: 15px;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 30px;
}

.pagination a,
.pagination span {
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    border: 2px solid #ecf0f1;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background: #ff914d;
    color: white;
    border-color: #ff914d;
}

.pagination .current {
    background: linear-gradient(135deg, #ff914d, #ff5e00);
    color: white;
    border-color: transparent;
}

.pagination .disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.filter-section {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-select {
    padding: 8px 12px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    background: white;
    color: #2c3e50;
    font-weight: 600;
}

.filter-select:focus {
    outline: none;
    border-color: #ff914d;
    box-shadow: 0 0 0 3px rgba(255, 145, 77, 0.1);
}

.access-restriction {
    background: linear-gradient(135deg, #ff6b6b, #ffb84d);
    color: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    font-weight: 600;
}

.verification-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 12px 24px;
    border: 2px solid white;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    margin-top: 20px;
    transition: all 0.3s ease;
}

.verification-btn:hover {
    background: white;
    color: #ff914d;
}

@media (max-width: 768px) {
    .nav-container {
        flex-direction: column;
        gap: 15px;
    }
    
    .nav-links {
        order: -1;
    }
    
    .requests-table {
        font-size: 12px;
    }
    
    .requests-table th,
    .requests-table td {
        padding: 8px 4px;
    }
    
    .requests-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-section {
        justify-content: center;
    }
    
    .pagination {
        flex-wrap: wrap;
    }
}

    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🛡️ Barangay <?php echo htmlspecialchars($user_barangay); ?></div>
            <div class="nav-links">
                <a href="user_dashboard.php" class="nav-link">Dashboard</a>
                <?php if ($user_status === 'verified'): ?>
                    <a href="user_forms.php" class="nav-link">Request Forms</a>
                <?php endif; ?>
                <a href="user_requests.php" class="nav-link active">My Requests</a>
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
        <div class="page-header">
            <h1 class="page-title">📋 My Service Requests</h1>
            <p>Track the status of your barangay service requests and documents.</p>
        </div>

        <?php if ($user_status === 'basic'): ?>
            <div class="access-restriction">
                🔒 <strong>Account Verification Required</strong><br>
                You need to verify your account to submit and view service requests.
                <a href="verification.php" class="verification-btn">Get Verified Now</a>
            </div>
        <?php else: ?>
            <div class="requests-section">
                <h2 class="section-title">📊 Request Summary</h2>
                
                <?php
                // Calculate statistics
                $stats = [
                    'total' => count($user_requests),
                    'pending' => 0,
                    'processing' => 0,
                    'ready' => 0,
                    'completed' => 0,
                    'rejected' => 0
                ];
                
                foreach ($user_requests as $request) {
                    if (isset($stats[$request['status']])) {
                        $stats[$request['status']]++;
                    }
                }
                ?>
                
                <div class="requests-stats">
                    <div class="stat-card total">
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total Requests</div>
                    </div>
                    <div class="stat-card pending">
                        <div class="stat-number"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card processing">
                        <div class="stat-number"><?php echo $stats['processing']; ?></div>
                        <div class="stat-label">Processing</div>
                    </div>
                    <div class="stat-card ready">
                        <div class="stat-number"><?php echo $stats['ready']; ?></div>
                        <div class="stat-label">Ready</div>
                    </div>
                    <div class="stat-card completed">
                        <div class="stat-number"><?php echo $stats['completed']; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card rejected">
                        <div class="stat-number"><?php echo $stats['rejected']; ?></div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>
            </div>

            <div class="requests-section">
                <h2 class="section-title">📋 Request History</h2>
                
                <?php if (empty($user_requests)): ?>
                    <div class="no-requests">
                        <div class="no-requests-icon">📝</div>
                        <h3>No Requests Yet</h3>
                        <p>You haven't submitted any service requests yet.</p>
                        <a href="user_forms.php" class="action-btn">Submit Your First Request</a>
                    </div>
                <?php else: ?>
                    <div class="filter-section">
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="ready">Ready</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        
                        <select class="filter-select" id="serviceFilter">
                            <option value="">All Services</option>
                            <option value="barangay_clearance">Barangay Clearance</option>
                            <option value="certificate_of_residency">Certificate of Residency</option>
                            <option value="certificate_of_indigency">Certificate of Indigency</option>
                            <option value="business_clearance">Business Clearance</option>
                            <option value="solo_parent_certificate">Solo Parent Certificate</option>
                            <option value="barangay_id">Barangay ID</option>
                            <option value="event_permit">Event Permit</option>
                            <option value="calamity_certificate">Calamity Certificate</option>
                        </select>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="requests-table" id="requestsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Service</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Date Requested</th>
                                    <th>Purpose</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paginated_requests as $request): ?>
                                    <tr data-status="<?php echo $request['status']; ?>" data-service="<?php echo $request['service_type']; ?>">
                                        <td>#<?php echo str_pad($request['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $request['service_type'])); ?></td>
                                        <td><?php echo htmlspecialchars($request['given_name'] . ' ' . $request['surname']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $request['status']; ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($request['purpose']); ?></td>
                                        <td>
                                            <a href="request_details.php?id=<?php echo $request['id']; ?>" 
                                               style="color: #ff914d; text-decoration: none; font-weight: 600;">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>">← Previous</a>
                            <?php else: ?>
                                <span class="disabled">← Previous</span>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $current_page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>">Next →</a>
                            <?php else: ?>
                                <span class="disabled">Next →</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const serviceFilter = document.getElementById('serviceFilter');
            
            if (statusFilter) statusFilter.addEventListener('change', filterRequests);
            if (serviceFilter) serviceFilter.addEventListener('change', filterRequests);
        });
        
        function filterRequests() {
            const statusFilter = document.getElementById('statusFilter').value;
            const serviceFilter = document.getElementById('serviceFilter').value;
            const rows = document.querySelectorAll('#requestsTable tbody tr');
            
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const service = row.getAttribute('data-service');
                
                const statusMatch = !statusFilter || status === statusFilter;
                const serviceMatch = !serviceFilter || service === serviceFilter;
                
                if (statusMatch && serviceMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>