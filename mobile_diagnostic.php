<?php
// mobile_diagnostic.php - Diagnostic tool for mobile browser issues
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

$action = $_GET['action'] ?? $_POST['action'] ?? 'test';

switch($action) {
    case 'test':
        performDiagnosticTest();
        break;
    case 'database':
        testDatabaseConnection();
        break;
    case 'session':
        testSessionHandling();
        break;
    case 'post':
        testPostData();
        break;
    default:
        echo json_encode(['error' => 'Invalid diagnostic action']);
}

function performDiagnosticTest() {
    $diagnostics = [
        'timestamp' => date('Y-m-d H:i:s'),
        'mobile_info' => getMobileInfo(),
        'server_info' => [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
            'http_accept' => $_SERVER['HTTP_ACCEPT'] ?? 'not set'
        ],
        'session_info' => [
            'session_id' => session_id(),
            'session_status' => session_status(),
            'session_save_path' => session_save_path(),
            'session_cookie_params' => session_get_cookie_params()
        ],
        'request_data' => [
            'get_params' => $_GET,
            'post_params' => $_POST,
            'raw_input' => file_get_contents('php://input'),
            'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 0
        ],
        'headers' => getallheaders(),
        'tests' => []
    ];
    
    // Test 1: Basic connectivity
    $diagnostics['tests']['connectivity'] = [
        'status' => 'pass',
        'message' => 'Mobile device can reach the server'
    ];
    
    // Test 2: Database connectivity
    try {
        global $pdo;
        $stmt = $pdo->query("SELECT 1");
        $diagnostics['tests']['database'] = [
            'status' => 'pass',
            'message' => 'Database connection successful'
        ];
    } catch (Exception $e) {
        $diagnostics['tests']['database'] = [
            'status' => 'fail',
            'message' => 'Database connection failed: ' . $e->getMessage()
        ];
    }
    
    // Test 3: POST data handling
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postDataReceived = !empty($_POST) || !empty(file_get_contents('php://input'));
        $diagnostics['tests']['post_data'] = [
            'status' => $postDataReceived ? 'pass' : 'fail',
            'message' => $postDataReceived ? 'POST data received successfully' : 'No POST data received'
        ];
    }
    
    // Test 4: JSON parsing
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $json = json_decode($rawInput, true);
        $diagnostics['tests']['json_parsing'] = [
            'status' => json_last_error() === JSON_ERROR_NONE ? 'pass' : 'fail',
            'message' => json_last_error() === JSON_ERROR_NONE ? 'JSON parsing successful' : 'JSON parsing failed: ' . json_last_error_msg()
        ];
    }
    
    echo json_encode($diagnostics, JSON_PRETTY_PRINT);
}

function testDatabaseConnection() {
    global $pdo;
    
    try {
        // Test basic connection
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        // Test table existence
        $tables = ['conversations', 'messages', 'users', 'admins'];
        $tableStatus = [];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                $tableStatus[$table] = $stmt->rowCount() > 0 ? 'exists' : 'missing';
            } catch (Exception $e) {
                $tableStatus[$table] = 'error: ' . $e->getMessage();
            }
        }
        
        // Test insert capability
        try {
            $testUserId = 'diagnostic_test_' . time();
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, sender_type, message) VALUES (?, 'user', 'Diagnostic test')");
            $stmt->execute([$testUserId]);
            $insertTest = 'success';
            
            // Clean up test data
            $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id = ?");
            $stmt->execute([$testUserId]);
        } catch (Exception $e) {
            $insertTest = 'failed: ' . $e->getMessage();
        }
        
        echo json_encode([
            'database_connection' => 'success',
            'basic_query' => $result,
            'table_status' => $tableStatus,
            'insert_test' => $insertTest,
            'mobile_info' => getMobileInfo()
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'database_connection' => 'failed',
            'error' => $e->getMessage(),
            'mobile_info' => getMobileInfo()
        ]);
    }
}

function testSessionHandling() {
    $sessionTest = [
        'initial_session_id' => session_id(),
        'session_status' => session_status(),
        'session_data_before' => $_SESSION ?? []
    ];
    
    // Test session write
    $_SESSION['diagnostic_test'] = 'mobile_test_' . time();
    $_SESSION['mobile_info'] = getMobileInfo();
    
    $sessionTest['session_data_after'] = $_SESSION;
    $sessionTest['test_value_set'] = isset($_SESSION['diagnostic_test']);
    
    // Test session persistence (simulate by checking if session_id exists)
    $sessionTest['session_persistence'] = !empty(session_id());
    
    echo json_encode($sessionTest);
}

function testPostData() {
    $postTest = [
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 0,
        'post_data' => $_POST,
        'raw_input' => file_get_contents('php://input'),
        'mobile_info' => getMobileInfo()
    ];
    
    // Test different POST data formats
    $rawInput = file_get_contents('php://input');
    
    // Test URL-encoded data
    if (!empty($rawInput)) {
        parse_str($rawInput, $urlEncoded);
        $postTest['url_encoded_parsed'] = $urlEncoded;
    }
    
    // Test JSON data
    if (!empty($rawInput)) {
        $jsonData = json_decode($rawInput, true);
        $postTest['json_parsed'] = $jsonData;
        $postTest['json_error'] = json_last_error_msg();
    }
    
    // Test specific fields that your app uses
    $testFields = ['action', 'user_id', 'message', 'help_type'];
    $postTest['field_extraction'] = [];
    
    foreach ($testFields as $field) {
        $postTest['field_extraction'][$field] = [
            'from_post' => $_POST[$field] ?? 'not found',
            'from_raw' => $urlEncoded[$field] ?? 'not found',
            'from_json' => $jsonData[$field] ?? 'not found'
        ];
    }
    
    echo json_encode($postTest, JSON_PRETTY_PRINT);
}

// If accessed directly, show a simple test interface
if (!isset($_GET['action']) && !isset($_POST['action'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Mobile Diagnostic Tool</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            button { padding: 10px 20px; margin: 10px; background: #007cba; color: white; border: none; border-radius: 5px; }
            .result { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; white-space: pre-wrap; }
        </style>
    </head>
    <body>
        <h1>Mobile Browser Diagnostic Tool</h1>
        <p>This tool helps diagnose issues with mobile browsers accessing your chat system.</p>
        
        <button onclick="runTest('test')">Run Full Diagnostic</button>
        <button onclick="runTest('database')">Test Database</button>
        <button onclick="runTest('session')">Test Sessions</button>
        <button onclick="runTest('post')">Test POST Data</button>
        
        <div id="results"></div>
        
        <script>
            function runTest(testType) {
                const results = document.getElementById('results');
                results.innerHTML = '<div class="result">Running ' + testType + ' test...</div>';
                
                fetch('mobile_diagnostic.php?action=' + testType, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'test_data=mobile_diagnostic&timestamp=' + Date.now()
                })
                .then(response => response.json())
                .then(data => {
                    results.innerHTML = '<div class="result">' + JSON.stringify(data, null, 2) + '</div>';
                })
                .catch(error => {
                    results.innerHTML = '<div class="result">Error: ' + error.message + '</div>';
                });
            }
            
            // Auto-run basic test on mobile
            if (/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                runTest('test');
            }
        </script>
    </body>
    </html>
    <?php
}
?>