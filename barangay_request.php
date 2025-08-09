<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_barangay']) || !isset($_SESSION['admin_city'])) {
    header('Location: login.php');
    exit;
}

// Database configuration
$host = 'sql308.infinityfree.com';
$dbname = 'if0_38484017_barangay_chatbot';
$username = 'if0_38484017';
$password = '8QPEk7NCVncLbL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get admin's barangay and city information
$admin_barangay_number = $_SESSION['admin_barangay'];
$admin_city = $_SESSION['admin_city'];

// Handle status update
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['request_id'], $_POST['new_status'])) {
    // Verify that the request belongs to the admin's barangay AND city before updating
    $verify_stmt = $pdo->prepare("
        SELECT br.id 
        FROM barangay_requests br 
        JOIN users u ON br.user_id = u.id 
        WHERE br.id = ? AND 
        LOWER(TRIM(u.city)) = LOWER(TRIM(?)) AND
        (
            TRIM(LEADING '0' FROM u.barangay) = TRIM(LEADING '0' FROM ?) OR
            LOWER(TRIM(u.barangay)) = LOWER(TRIM(?))
        )
    ");
    $verify_stmt->execute([$_POST['request_id'], $admin_city, $admin_barangay_number, $admin_barangay_number]);
    
    if ($verify_stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE barangay_requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$_POST['new_status'], $_POST['request_id']]);
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$service_filter = $_GET['service'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build WHERE clause with barangay AND city filter
$where_conditions = [
    "LOWER(TRIM(u.city)) = LOWER(TRIM(?))",
    "(TRIM(LEADING '0' FROM u.barangay) = TRIM(LEADING '0' FROM ?) OR LOWER(TRIM(u.barangay)) = LOWER(TRIM(?)))"
];
$params = [$admin_city, $admin_barangay_number, $admin_barangay_number];

if ($status_filter) {
    $where_conditions[] = "br.status = ?";
    $params[] = $status_filter;
}

if ($service_filter) {
    $where_conditions[] = "br.service_type = ?";
    $params[] = $service_filter;
}

if ($search) {
    $where_conditions[] = "(br.surname LIKE ? OR br.given_name LIKE ? OR br.middle_name LIKE ? OR br.contact_number LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM barangay_requests br JOIN users u ON br.user_id = u.id $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Get records - Use direct integer values for LIMIT and OFFSET (not bound parameters)
$query = "SELECT br.*, u.barangay as user_barangay, u.city as user_city,
          CASE 
            WHEN br.middle_name IS NOT NULL AND br.middle_name != '' 
            THEN CONCAT(br.surname, ', ', br.given_name, ' ', br.middle_name)
            ELSE CONCAT(br.surname, ', ', br.given_name)
          END as full_name
          FROM barangay_requests br 
          JOIN users u ON br.user_id = u.id
          $where_clause 
          ORDER BY br.created_at DESC 
          LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics for admin's barangay AND city only
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN br.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN br.status = 'processing' THEN 1 ELSE 0 END) as processing,
    SUM(CASE WHEN br.status = 'ready' THEN 1 ELSE 0 END) as ready,
    SUM(CASE WHEN br.status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN br.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM barangay_requests br
    JOIN users u ON br.user_id = u.id
    WHERE LOWER(TRIM(u.city)) = LOWER(TRIM(?)) AND
    (TRIM(LEADING '0' FROM u.barangay) = TRIM(LEADING '0' FROM ?) OR LOWER(TRIM(u.barangay)) = LOWER(TRIM(?)))";

$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute([$admin_city, $admin_barangay_number, $admin_barangay_number]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Ensure all stats values are numeric (handle null values)
$stats = [
    'total' => (int)($stats['total'] ?? 0),
    'pending' => (int)($stats['pending'] ?? 0),
    'processing' => (int)($stats['processing'] ?? 0),
    'ready' => (int)($stats['ready'] ?? 0),
    'completed' => (int)($stats['completed'] ?? 0),
    'cancelled' => (int)($stats['cancelled'] ?? 0)
];

// Service type mapping - Updated to match database values
$service_types = [
    'barangay_clearance' => 'Barangay Clearance',
    'certificate_of_residency' => 'Certificate of Residency', 
    'certificate_of_indigency' => 'Certificate of Indigency',
    'business_clearance' => 'Business Clearance',
    'solo_parent_certificate' => 'Solo Parent Certificate',
    'barangay_id' => 'Barangay ID',
    'event_permit' => 'Event Permit',
    'calamity_certificate' => 'Calamity Certificate',
    'other' => 'Other'
];

// Status colors
$status_colors = [
    'pending' => '#fbbf24',
    'processing' => '#3b82f6',
    'ready' => '#10b981',
    'completed' => '#6b7280',
    'cancelled' => '#ef4444'
];

// Get admin info for display
$admin_stmt = $pdo->prepare("SELECT city, barangay_number FROM admins WHERE id = ?");
$admin_stmt->execute([$_SESSION['admin_id']]);
$admin_info = $admin_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> Requests</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            position: relative;
        }

        .header h1 {
            color: #1e293b;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .admin-info {
            position: absolute;
            top: 20px;
            right: 30px;
            background: #f1f5f9;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.875rem;
            color: #64748b;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            margin-top: 5px;
            display: inline-block;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .filters {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .btn {
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            background: #2563eb;
        }

        .btn-clear {
            background: #6b7280;
            margin-left: 10px;
        }

        .btn-clear:hover {
            background: #4b5563;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
        }

        .service-type {
            background: #e5e7eb;
            color: #374151;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .contact-info {
            font-size: 0.875rem;
            color: #64748b;
        }

        .date-info {
            font-size: 0.875rem;
            color: #64748b;
            white-space: nowrap;
        }

        .status-update {
            display: flex;
            gap: 5px;
        }

        .status-select {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 0.875rem;
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
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-decoration: none;
            color: #374151;
        }

        .pagination a:hover {
            background: #f3f4f6;
        }

        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .text-truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            .admin-info {
                position: static;
                margin-top: 15px;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 0.875rem;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><?= htmlspecialchars($admin_info['city'] ?? $admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> Requests</h1>
            <p>Manage and track barangay service requests for your area</p>
            <div class="admin-info">
                <div><strong><?= htmlspecialchars($admin_info['city'] ?? $admin_city) ?></strong></div>
                <div>Barangay <?= htmlspecialchars($admin_barangay_number) ?></div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color: #64748b;"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #fbbf24;"><?= number_format($stats['pending']) ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #3b82f6;"><?= number_format($stats['processing']) ?></div>
                <div class="stat-label">Processing</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #10b981;"><?= number_format($stats['ready']) ?></div>
                <div class="stat-label">Ready</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #6b7280;"><?= number_format($stats['completed']) ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" id="filterForm">
                <div class="filters-grid">
                    <div class="form-group">
                        <label>Search (Name/Phone)</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Enter name or phone number" id="searchInput">
                    </div>
                    <div class="form-group">
                        <label>Status Filter</label>
                        <select name="status" id="statusFilter" onchange="autoFilter()">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="ready" <?= $status_filter === 'ready' ? 'selected' : '' ?>>Ready</option>
                            <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Service Type</label>
                        <select name="service" id="serviceFilter" onchange="autoFilter()">
                            <option value="">All Services</option>
                            <?php foreach ($service_types as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $service_filter === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; justify-content: flex-end; align-items: flex-end;">
                        <a href="?" class="btn btn-clear">Clear All Filters</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Service Type</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: #64748b;">
                                    No requests found for <?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?> matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><strong>#<?= $request['id'] ?></strong></td>
                                    <td>
                                        <div><?= htmlspecialchars($request['full_name']) ?></div>
                                        <div class="contact-info">Born: <?= date('M j, Y', strtotime($request['birthdate'])) ?></div>
                                    </td>
                                    <td>
                                        <span class="service-type">
                                            <?= $service_types[$request['service_type']] ?? $request['service_type'] ?>
                                        </span>
                                    </td>
                                    <td class="contact-info"><?= htmlspecialchars($request['contact_number']) ?></td>
                                    <td class="text-truncate" title="<?= htmlspecialchars($request['address']) ?>">
                                        <?= htmlspecialchars($request['address']) ?>
                                    </td>
                                    <td class="text-truncate" title="<?= htmlspecialchars($request['purpose']) ?>">
                                        <?= htmlspecialchars($request['purpose']) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?= $status_colors[$request['status']] ?? '#64748b' ?>">
                                            <?= ucfirst($request['status']) ?>
                                        </span>
                                    </td>
                                    <td class="date-info">
                                        <?= date('M j, Y', strtotime($request['created_at'])) ?><br>
                                        <small><?= date('g:i A', strtotime($request['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <form method="POST" class="status-update">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                            <select name="new_status" class="status-select">
                                                <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="processing" <?= $request['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="ready" <?= $request['status'] === 'ready' ? 'selected' : '' ?>>Ready</option>
                                                <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="cancelled" <?= $request['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">← Previous</a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                ?>
                
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Footer Info -->
        <div style="text-align: center; margin-top: 40px; color: #64748b; font-size: 0.875rem;">
            Showing <?= count($requests) ?> of <?= number_format($total_records) ?> total requests for <?= htmlspecialchars($admin_city) ?> - Barangay <?= htmlspecialchars($admin_barangay_number) ?>
            <?php if ($total_pages > 1): ?>
                (Page <?= $page ?> of <?= $total_pages ?>)
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-filter function for select dropdowns
        function autoFilter() {
            document.getElementById('filterForm').submit();
        }

        // Auto-filter for search input with debounce
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                document.getElementById('filterForm').submit();
            }, 500); // 500ms delay after user stops typing
        });
    </script>
</body>
</html>