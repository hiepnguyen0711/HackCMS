<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Universal Target Discovery for Localhost Penetration Testing
// Automatically discovers available localhost projects and analyzes their structure

function scanXamppHtdocs() {
    $xamppPath = dirname($_SERVER['DOCUMENT_ROOT']);
    $targets = [];
    
    // Common XAMPP structure patterns
    $searchPaths = [
        $xamppPath,
        $xamppPath . '/xampp/htdocs',
        $_SERVER['DOCUMENT_ROOT'],
        dirname($_SERVER['DOCUMENT_ROOT']) . '/public_html',
        dirname($_SERVER['DOCUMENT_ROOT']) . '/www'
    ];
    
    foreach ($searchPaths as $basePath) {
        if (is_dir($basePath)) {
            $targets = array_merge($targets, discoverProjects($basePath));
        }
    }
    
    return array_unique($targets, SORT_REGULAR);
}

function discoverProjects($basePath) {
    $projects = [];
    
    try {
        $items = scandir($basePath);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($fullPath)) {
                // Check if it's a web project
                $projectInfo = analyzeProject($fullPath, $item);
                if ($projectInfo) {
                    $projects[] = $projectInfo;
                }
                
                // Recursively check subdirectories (one level deep)
                if (preg_match('/^\d{4}$/', $item)) { // Year folder like 2025
                    $yearProjects = discoverYearProjects($fullPath, $item);
                    $projects = array_merge($projects, $yearProjects);
                }
            }
        }
    } catch (Exception $e) {
        // Continue silently if can't scan directory
    }
    
    return $projects;
}

function discoverYearProjects($yearPath, $year) {
    $projects = [];
    
    try {
        $months = scandir($yearPath);
        
        foreach ($months as $month) {
            if ($month === '.' || $month === '..') continue;
            
            $monthPath = $yearPath . DIRECTORY_SEPARATOR . $month;
            
            if (is_dir($monthPath)) {
                $monthProjects = scandir($monthPath);
                
                foreach ($monthProjects as $project) {
                    if ($project === '.' || $project === '..') continue;
                    
                    $projectPath = $monthPath . DIRECTORY_SEPARATOR . $project;
                    
                    if (is_dir($projectPath)) {
                        $projectInfo = analyzeProject($projectPath, $project, "{$year}/{$month}");
                        if ($projectInfo) {
                            $projects[] = $projectInfo;
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Continue silently
    }
    
    return $projects;
}

function analyzeProject($projectPath, $projectName, $parentPath = '') {
    // Check if it's a valid web project
    $indicators = [
        'index.php', 'index.html', 'index.htm',
        'wp-config.php', // WordPress
        'config.php', 'config.inc.php', // General PHP
        'composer.json', // Composer projects
        'package.json', // Node.js projects
        'artisan', // Laravel
        '.htaccess' // Apache config
    ];
    
    $foundIndicators = [];
    $uploadDirs = [];
    $securityFiles = [];
    
    try {
        $files = scandir($projectPath);
        
        foreach ($files as $file) {
            if (in_array($file, $indicators)) {
                $foundIndicators[] = $file;
            }
            
            $filePath = $projectPath . DIRECTORY_SEPARATOR . $file;
            
            // Check for upload directories
            if (is_dir($filePath)) {
                $dirName = strtolower($file);
                if (in_array($dirName, ['uploads', 'upload', 'files', 'media', 'assets', 'images', 'sources', 'public'])) {
                    $uploadDirs[] = [
                        'name' => $file,
                        'writable' => is_writable($filePath),
                        'path' => $filePath
                    ];
                }
                
                // Check for admin directories
                if (in_array($dirName, ['admin', 'administrator', 'wp-admin', 'backend', 'manage'])) {
                    $securityFiles[] = [
                        'type' => 'admin_directory',
                        'name' => $file,
                        'path' => $filePath
                    ];
                }
            }
            
            // Check for security-relevant files
            if (preg_match('/\.(php|asp|jsp|cfm)$/i', $file)) {
                $fileContent = @file_get_contents($filePath, false, null, 0, 1000);
                if ($fileContent) {
                    if (stripos($fileContent, 'login') !== false || stripos($fileContent, 'auth') !== false) {
                        $securityFiles[] = [
                            'type' => 'auth_file',
                            'name' => $file,
                            'path' => $filePath
                        ];
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Continue with limited info
    }
    
    // Only return if it looks like a web project
    if (empty($foundIndicators)) {
        return null;
    }
    
    $projectType = detectProjectType($foundIndicators, $projectPath);
    $urlPath = $parentPath ? "{$parentPath}/{$projectName}" : $projectName;
    
    return [
        'name' => $projectName,
        'path' => $projectPath,
        'url' => "http://localhost/{$urlPath}",
        'type' => $projectType,
        'indicators' => $foundIndicators,
        'upload_dirs' => $uploadDirs,
        'security_files' => $securityFiles,
        'parent_path' => $parentPath,
        'risk_level' => calculateRiskLevel($uploadDirs, $securityFiles),
        'attack_vectors' => suggestAttackVectors($uploadDirs, $securityFiles, $projectType)
    ];
}

function detectProjectType($indicators, $projectPath) {
    if (in_array('wp-config.php', $indicators)) {
        return 'WordPress';
    }
    
    if (in_array('artisan', $indicators)) {
        return 'Laravel';
    }
    
    if (in_array('composer.json', $indicators)) {
        $composer = @file_get_contents($projectPath . '/composer.json');
        if ($composer) {
            $composerData = json_decode($composer, true);
            if (isset($composerData['require']['laravel/framework'])) {
                return 'Laravel';
            }
            if (isset($composerData['require']['symfony/symfony'])) {
                return 'Symfony';
            }
        }
        return 'PHP Composer Project';
    }
    
    if (in_array('package.json', $indicators)) {
        return 'Node.js Project';
    }
    
    if (in_array('config.php', $indicators) || in_array('config.inc.php', $indicators)) {
        return 'Custom PHP CMS';
    }
    
    if (in_array('index.php', $indicators)) {
        return 'PHP Project';
    }
    
    if (in_array('index.html', $indicators) || in_array('index.htm', $indicators)) {
        return 'Static Website';
    }
    
    return 'Unknown';
}

function calculateRiskLevel($uploadDirs, $securityFiles) {
    $risk = 0;
    
    // Writable upload directories increase risk
    foreach ($uploadDirs as $dir) {
        if ($dir['writable']) {
            $risk += 2;
        } else {
            $risk += 1;
        }
    }
    
    // Admin directories increase risk
    foreach ($securityFiles as $file) {
        if ($file['type'] === 'admin_directory') {
            $risk += 3;
        } elseif ($file['type'] === 'auth_file') {
            $risk += 1;
        }
    }
    
    if ($risk >= 8) return 'Critical';
    if ($risk >= 5) return 'High';
    if ($risk >= 3) return 'Medium';
    if ($risk >= 1) return 'Low';
    return 'Minimal';
}

function suggestAttackVectors($uploadDirs, $securityFiles, $projectType) {
    $vectors = [];
    
    // Upload-based attacks
    if (!empty($uploadDirs)) {
        $vectors[] = [
            'type' => 'File Upload',
            'method' => 'Shell Upload',
            'description' => 'Upload malicious PHP files to writable directories',
            'directories' => array_column($uploadDirs, 'name')
        ];
    }
    
    // Admin panel attacks
    $adminDirs = array_filter($securityFiles, function($f) {
        return $f['type'] === 'admin_directory';
    });
    
    if (!empty($adminDirs)) {
        $vectors[] = [
            'type' => 'Admin Panel',
            'method' => 'SQL Injection / Brute Force',
            'description' => 'Attack admin login forms',
            'directories' => array_column($adminDirs, 'name')
        ];
    }
    
    // Type-specific attacks
    switch ($projectType) {
        case 'WordPress':
            $vectors[] = [
                'type' => 'WordPress Specific',
                'method' => 'Plugin/Theme Upload',
                'description' => 'Upload malicious plugins or themes',
                'directories' => ['wp-content/plugins', 'wp-content/themes']
            ];
            break;
            
        case 'Custom PHP CMS':
            $vectors[] = [
                'type' => 'Custom CMS',
                'method' => 'Config File Access',
                'description' => 'Access configuration files for credentials',
                'files' => ['config.php', 'config.inc.php']
            ];
            break;
    }
    
    return $vectors;
}

function generatePenetrationReport($targets) {
    $report = [
        'timestamp' => date('Y-m-d H:i:s'),
        'total_targets' => count($targets),
        'risk_summary' => [],
        'recommendations' => []
    ];
    
    $riskCounts = ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0, 'Minimal' => 0];
    
    foreach ($targets as $target) {
        $riskCounts[$target['risk_level']]++;
    }
    
    $report['risk_summary'] = $riskCounts;
    
    // Generate recommendations
    if ($riskCounts['Critical'] > 0) {
        $report['recommendations'][] = '🚨 CRITICAL: Immediate security review required for high-risk targets';
    }
    
    if ($riskCounts['High'] > 0) {
        $report['recommendations'][] = '⚠️ HIGH: Review file upload permissions and admin access controls';
    }
    
    $report['recommendations'][] = '🔒 Ensure all admin panels use strong authentication';
    $report['recommendations'][] = '📁 Restrict write permissions on upload directories';
    $report['recommendations'][] = '🛡️ Implement proper input validation and sanitization';
    
    return $report;
}

// Main execution
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'discover';
        
        switch ($action) {
            case 'discover':
                $targets = scanXamppHtdocs();
                $report = generatePenetrationReport($targets);
                
                echo json_encode([
                    'success' => true,
                    'targets' => $targets,
                    'report' => $report,
                    'message' => 'Target discovery completed successfully'
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
                
            case 'info':
                echo json_encode([
                    'api_name' => 'Universal Target Discovery',
                    'version' => '1.0',
                    'description' => 'Automatically discovers localhost projects for penetration testing',
                    'features' => [
                        'auto_project_detection',
                        'upload_directory_analysis', 
                        'admin_panel_discovery',
                        'risk_assessment',
                        'attack_vector_suggestions'
                    ],
                    'supported_types' => [
                        'WordPress', 'Laravel', 'Custom PHP CMS', 
                        'Static Websites', 'Node.js Projects'
                    ]
                ], JSON_PRETTY_PRINT);
                break;
                
            default:
                throw new Exception('Invalid action specified');
        }
        
    } else {
        throw new Exception('Only GET requests are supported');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Target discovery failed'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 