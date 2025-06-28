<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🕵️‍♀️ Debug Remote Upload - Phân Tích Lỗi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .debug-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #fff;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 16px;
        }

        .input-group input:focus, .input-group select:focus {
            outline: none;
            box-shadow: 0 0 15px rgba(64, 224, 255, 0.6);
        }

        .btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a5a);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            margin: 10px 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .results-container {
            margin-top: 30px;
        }

        .diagnostic-section {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .section-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 20px;
            font-weight: bold;
            font-size: 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-content {
            padding: 20px;
            display: none;
        }

        .section-content.active {
            display: block;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-success { background: #4caf50; }
        .status-error { background: #f44336; }
        .status-warning { background: #ff9800; }
        .status-info { background: #2196f3; }

        .detail-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }

        .detail-item.error {
            border-left-color: #f44336;
        }

        .detail-item.warning {
            border-left-color: #ff9800;
        }

        .log-container {
            background: #000;
            color: #00ff00;
            padding: 15px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
            margin: 15px 0;
        }

        .log-entry {
            margin: 2px 0;
            padding: 2px 0;
        }

        .log-timestamp {
            color: #888;
            margin-right: 10px;
        }

        .log-success { color: #4caf50; }
        .log-error { color: #f44336; }
        .log-warning { color: #ff9800; }
        .log-info { color: #2196f3; }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4caf50, #8bc34a);
            border-radius: 10px;
            transition: width 0.3s ease;
            width: 0%;
        }

        .recommendations {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .recommendation-category {
            margin-bottom: 15px;
        }

        .recommendation-category h4 {
            color: #ffc107;
            margin-bottom: 8px;
        }

        .recommendation-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 5px;
            border-left: 3px solid #ffc107;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .loading {
            animation: pulse 1.5s infinite;
        }

        .file-upload {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload:hover {
            border-color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.05);
        }

        .file-upload.dragover {
            border-color: #4caf50;
            background: rgba(76, 175, 80, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🕵️‍♀️ Debug Remote Upload</h1>
            <p>Phân tích toàn diện lỗi upload remote hosting</p>
        </div>

        <div class="debug-panel">
            <h2>🎯 Cấu hình kiểm tra</h2>
            
            <div class="input-group">
                <label>🌐 Target URL (Remote Hosting):</label>
                <input type="text" id="targetUrl" placeholder="https://example.com" 
                       value="<?php echo isset($_GET['target']) ? htmlspecialchars($_GET['target']) : 'http://demo31.phuongnamvina.vn'; ?>">
            </div>

            <div class="input-group">
                <label>📁 Shell File (Optional):</label>
                <div class="file-upload" onclick="document.getElementById('shellFile').click()">
                    <input type="file" id="shellFile" style="display: none;" accept=".php,.txt">
                    <p>🔄 Click hoặc kéo thả file shell vào đây</p>
                    <p><small>Để trống nếu chỉ muốn phân tích mà không test upload</small></p>
                </div>
            </div>

            <div class="input-group">
                <label>📝 Shell Name:</label>
                <input type="text" id="shellName" placeholder="hack.php" value="test.php">
            </div>

            <button class="btn" onclick="startDiagnostics()" id="startBtn">
                🚀 Bắt đầu phân tích
            </button>

            <button class="btn" onclick="exportResults()" id="exportBtn" disabled>
                📊 Xuất báo cáo
            </button>
        </div>

        <div class="progress-bar" id="progressBar" style="display: none;">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="results-container" id="resultsContainer" style="display: none;">
            <!-- Results will be populated here -->
        </div>
    </div>

    <script>
        let diagnosticResults = null;

        // File upload handling
        document.getElementById('shellFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.querySelector('.file-upload p').textContent = 
                    `✅ Selected: ${file.name} (${(file.size/1024).toFixed(2)} KB)`;
            }
        });

        // Drag and drop
        const fileUpload = document.querySelector('.file-upload');
        fileUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUpload.classList.add('dragover');
        });

        fileUpload.addEventListener('dragleave', () => {
            fileUpload.classList.remove('dragover');
        });

        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('shellFile').files = files;
                document.querySelector('.file-upload p').textContent = 
                    `✅ Selected: ${files[0].name} (${(files[0].size/1024).toFixed(2)} KB)`;
            }
        });

        async function startDiagnostics() {
            const targetUrl = document.getElementById('targetUrl').value.trim();
            const shellFile = document.getElementById('shellFile').files[0];
            const shellName = document.getElementById('shellName').value.trim();

            if (!targetUrl) {
                alert('⚠️ Vui lòng nhập Target URL!');
                return;
            }

            // Disable button and show progress
            document.getElementById('startBtn').disabled = true;
            document.getElementById('startBtn').textContent = '🔄 Đang phân tích...';
            document.getElementById('progressBar').style.display = 'block';
            
            // Animate progress
            animateProgress();

            try {
                const formData = new FormData();
                formData.append('target_url', targetUrl);
                formData.append('action', 'full_diagnostics');
                formData.append('shell_name', shellName);
                
                if (shellFile) {
                    formData.append('shell_file', shellFile);
                }

                const response = await fetch('debug_remote_upload.php', {
                    method: 'POST',
                    body: formData
                });

                const results = await response.json();
                
                if (results.success === false) {
                    throw new Error(results.message || 'Unknown error');
                }

                diagnosticResults = results;
                displayResults(results);
                
                document.getElementById('exportBtn').disabled = false;
                
            } catch (error) {
                console.error('Error:', error);
                alert(`❌ Lỗi: ${error.message}`);
            } finally {
                // Reset button
                document.getElementById('startBtn').disabled = false;
                document.getElementById('startBtn').textContent = '🚀 Bắt đầu phân tích';
                document.getElementById('progressBar').style.display = 'none';
            }
        }

        function animateProgress() {
            const progressFill = document.getElementById('progressFill');
            let width = 0;
            const interval = setInterval(() => {
                width += Math.random() * 15;
                if (width > 90) {
                    clearInterval(interval);
                    width = 90;
                }
                progressFill.style.width = width + '%';
            }, 500);

            // Complete progress when done
            setTimeout(() => {
                clearInterval(interval);
                progressFill.style.width = '100%';
            }, 10000);
        }

        function displayResults(results) {
            const container = document.getElementById('resultsContainer');
            container.style.display = 'block';
            
            let html = `
                <h2>📊 Kết quả phân tích: ${results.target_url}</h2>
                <p><strong>⏰ Thời gian:</strong> ${results.timestamp}</p>
            `;

            // Connectivity Section
            if (results.diagnostics.connectivity) {
                const conn = results.diagnostics.connectivity;
                html += createSection('🌐 Kết nối mạng', 
                    conn.ping_test ? 'success' : 'error',
                    `
                    <div class="detail-item ${conn.ping_test ? '' : 'error'}">
                        <strong>🔗 Kết nối HTTP:</strong> ${conn.ping_test ? '✅ Thành công' : '❌ Thất bại'}
                        ${conn.http_response ? ` (HTTP ${conn.http_response})` : ''}
                    </div>
                    <div class="detail-item">
                        <strong>⚡ Thời gian phản hồi:</strong> ${conn.response_time}ms
                    </div>
                    <div class="detail-item ${conn.can_resolve_dns ? '' : 'error'}">
                        <strong>🌐 DNS Resolution:</strong> ${conn.can_resolve_dns ? '✅' : '❌'} 
                        ${conn.resolved_ip ? `(${conn.resolved_ip})` : ''}
                    </div>
                    ${conn.error_details.length > 0 ? 
                        `<div class="detail-item error"><strong>❌ Lỗi:</strong> ${conn.error_details.join(', ')}</div>` 
                        : ''}
                    `
                );
            }

            // SSL Section
            if (results.diagnostics.ssl) {
                const ssl = results.diagnostics.ssl;
                html += createSection('🔒 SSL/TLS', 
                    ssl.is_https ? (ssl.ssl_valid ? 'success' : 'warning') : 'info',
                    `
                    <div class="detail-item">
                        <strong>🔐 HTTPS:</strong> ${ssl.is_https ? '✅ Có' : '❌ Không'}
                    </div>
                    ${ssl.is_https ? `
                        <div class="detail-item ${ssl.ssl_valid ? '' : 'error'}">
                            <strong>🛡️ SSL Certificate:</strong> ${ssl.ssl_valid ? '✅ Hợp lệ' : '❌ Không hợp lệ'}
                        </div>
                        ${ssl.certificate_details ? `
                            <div class="detail-item">
                                <strong>📜 Subject:</strong> ${ssl.certificate_details.subject}<br>
                                <strong>🏢 Issuer:</strong> ${ssl.certificate_details.issuer}<br>
                                <strong>📅 Valid:</strong> ${ssl.certificate_details.valid_from} → ${ssl.certificate_details.valid_to}
                            </div>
                        ` : ''}
                    ` : ''}
                    `
                );
            }

            // Server Analysis
            if (results.diagnostics.server) {
                const server = results.diagnostics.server;
                html += createSection('🖥️ Thông tin Server', 'info',
                    `
                    <div class="detail-item">
                        <strong>🖥️ Server Software:</strong> ${server.server_software}
                    </div>
                    <div class="detail-item">
                        <strong>⚡ Powered By:</strong> ${server.powered_by}
                    </div>
                    <div class="detail-item">
                        <strong>🎯 CMS Detected:</strong> ${server.cms_detected}
                    </div>
                    ${server.security_headers.length > 0 ? `
                        <div class="detail-item warning">
                            <strong>🛡️ Security Headers:</strong><br>
                            ${server.security_headers.map(h => `• ${h}`).join('<br>')}
                        </div>
                    ` : ''}
                    `
                );
            }

            // Endpoints Discovery
            if (results.diagnostics.endpoints) {
                const endpoints = results.diagnostics.endpoints;
                html += createSection('🔍 Endpoint Discovery', 
                    endpoints.accessible.length > 0 ? 'success' : 'warning',
                    `
                    <div class="detail-item">
                        <strong>🧪 Tested Endpoints:</strong> ${endpoints.tested.length}
                    </div>
                    <div class="detail-item ${endpoints.accessible.length > 0 ? '' : 'warning'}">
                        <strong>✅ Accessible:</strong> ${endpoints.accessible.length}
                        ${endpoints.accessible.length > 0 ? `<br>${endpoints.accessible.map(e => `• ${e}`).join('<br>')}` : ''}
                    </div>
                    <div class="detail-item ${endpoints.upload_forms.length > 0 ? 'warning' : ''}">
                        <strong>📤 Upload Forms Found:</strong> ${endpoints.upload_forms.length}
                        ${endpoints.upload_forms.length > 0 ? `<br>${endpoints.upload_forms.map(e => `• ${e}`).join('<br>')}` : ''}
                    </div>
                    `
                );
            }

            // Upload Tests (if performed)
            if (results.diagnostics.upload_tests) {
                const upload = results.diagnostics.upload_tests;
                const successfulUploads = upload.detailed_results.filter(r => r.result.success);
                
                html += createSection('🚀 Upload Testing', 
                    successfulUploads.length > 0 ? 'success' : 'error',
                    `
                    <div class="detail-item">
                        <strong>🔧 Methods Attempted:</strong> ${upload.methods_attempted.join(', ')}
                    </div>
                    <div class="detail-item ${successfulUploads.length > 0 ? '' : 'error'}">
                        <strong>✅ Successful Uploads:</strong> ${successfulUploads.length}
                    </div>
                    <div class="detail-item">
                        <strong>❌ Failed Attempts:</strong> ${upload.detailed_results.length - successfulUploads.length}
                    </div>
                    ${upload.curl_errors.length > 0 ? `
                        <div class="detail-item error">
                            <strong>🚫 CURL Errors:</strong><br>
                            ${[...new Set(upload.curl_errors)].map(e => `• ${e}`).join('<br>')}
                        </div>
                    ` : ''}
                    <details>
                        <summary><strong>🔍 Chi tiết từng attempt</strong></summary>
                        ${upload.detailed_results.map(r => `
                            <div class="detail-item ${r.result.success ? '' : 'error'}">
                                <strong>${r.method}</strong> → ${r.url}<br>
                                Status: ${r.result.success ? '✅ Success' : '❌ Failed'} 
                                (HTTP ${r.result.http_code || 'N/A'})
                                ${r.result.error ? `<br>Error: ${r.result.error}` : ''}
                            </div>
                        `).join('')}
                    </details>
                    `
                );
            }

            // Security Analysis
            if (results.diagnostics.security) {
                const security = results.diagnostics.security;
                html += createSection('🛡️ Security Analysis', 
                    security.waf_detected ? 'warning' : 'info',
                    `
                    <div class="detail-item ${security.waf_detected ? 'warning' : ''}">
                        <strong>🛡️ WAF Detected:</strong> ${security.waf_detected ? '⚠️ Có' : '✅ Không'}
                    </div>
                    ${security.blocked_requests.length > 0 ? `
                        <div class="detail-item warning">
                            <strong>🚫 Blocked Requests:</strong> ${security.blocked_requests.join(', ')}
                        </div>
                    ` : ''}
                    <div class="detail-item">
                        <strong>⏱️ Rate Limiting:</strong> ${security.rate_limiting ? '⚠️ Có' : '✅ Không'}
                    </div>
                    `
                );
            }

            // Debug Log
            if (results.debug_log && results.debug_log.length > 0) {
                html += createSection('📝 Debug Log', 'info',
                    `<div class="log-container">
                        ${results.debug_log.map(log => 
                            `<div class="log-entry log-${log.type}">
                                <span class="log-timestamp">[${log.timestamp}]</span>
                                ${log.message}
                            </div>`
                        ).join('')}
                    </div>`
                );
            }

            // Recommendations
            if (results.recommendations) {
                const rec = results.recommendations;
                html += `
                    <div class="recommendations">
                        <h3>💡 Khuyến nghị</h3>
                        
                        ${rec.immediate_actions.length > 0 ? `
                            <div class="recommendation-category">
                                <h4>⚡ Hành động ngay lập tức:</h4>
                                ${rec.immediate_actions.map(action => 
                                    `<div class="recommendation-item">${action}</div>`
                                ).join('')}
                            </div>
                        ` : ''}
                        
                        ${rec.alternative_methods.length > 0 ? `
                            <div class="recommendation-category">
                                <h4>🔄 Phương pháp thay thế:</h4>
                                ${rec.alternative_methods.map(method => 
                                    `<div class="recommendation-item">${method}</div>`
                                ).join('')}
                            </div>
                        ` : ''}
                        
                        ${rec.manual_steps.length > 0 ? `
                            <div class="recommendation-category">
                                <h4>👥 Bước thủ công:</h4>
                                ${rec.manual_steps.map(step => 
                                    `<div class="recommendation-item">${step}</div>`
                                ).join('')}
                            </div>
                        ` : ''}
                    </div>
                `;
            }

            container.innerHTML = html;

            // Add click handlers for collapsible sections
            document.querySelectorAll('.section-header').forEach(header => {
                header.addEventListener('click', function() {
                    const content = this.nextElementSibling;
                    content.classList.toggle('active');
                    const icon = this.querySelector('.toggle-icon');
                    if (icon) {
                        icon.textContent = content.classList.contains('active') ? '▼' : '▶';
                    }
                });
            });
        }

        function createSection(title, status, content) {
            const statusClass = `status-${status}`;
            const icon = '▼'; // Default expanded
            
            return `
                <div class="diagnostic-section">
                    <div class="section-header">
                        <span>
                            <span class="status-indicator ${statusClass}"></span>
                            ${title}
                        </span>
                        <span class="toggle-icon">${icon}</span>
                    </div>
                    <div class="section-content active">
                        ${content}
                    </div>
                </div>
            `;
        }

        function exportResults() {
            if (!diagnosticResults) {
                alert('❌ Không có dữ liệu để xuất!');
                return;
            }

            const reportContent = generateReport(diagnosticResults);
            const blob = new Blob([reportContent], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `debug_report_${new Date().getTime()}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function generateReport(results) {
            let report = `
🕵️‍♀️ REMOTE UPLOAD DEBUG REPORT
================================
Target: ${results.target_url}
Timestamp: ${results.timestamp}

`;

            // Connectivity
            if (results.diagnostics.connectivity) {
                const conn = results.diagnostics.connectivity;
                report += `
🌐 CONNECTIVITY ANALYSIS
-----------------------
✅ HTTP Connection: ${conn.ping_test ? 'SUCCESS' : 'FAILED'}
📊 Response Time: ${conn.response_time}ms
🌐 DNS Resolution: ${conn.can_resolve_dns ? 'SUCCESS' : 'FAILED'}
${conn.resolved_ip ? `IP Address: ${conn.resolved_ip}` : ''}
${conn.error_details.length > 0 ? `Errors: ${conn.error_details.join(', ')}` : ''}

`;
            }

            // Add more sections...
            
            // Debug Log
            if (results.debug_log) {
                report += `
📝 DEBUG LOG
-----------
${results.debug_log.map(log => `[${log.timestamp}] ${log.type.toUpperCase()}: ${log.message}`).join('\n')}

`;
            }

            // Recommendations
            if (results.recommendations) {
                report += `
💡 RECOMMENDATIONS
-----------------
`;
                const rec = results.recommendations;
                
                if (rec.immediate_actions.length > 0) {
                    report += `
⚡ Immediate Actions:
${rec.immediate_actions.map(action => `- ${action}`).join('\n')}
`;
                }
                
                if (rec.alternative_methods.length > 0) {
                    report += `
🔄 Alternative Methods:
${rec.alternative_methods.map(method => `- ${method}`).join('\n')}
`;
                }
                
                if (rec.manual_steps.length > 0) {
                    report += `
👥 Manual Steps:
${rec.manual_steps.map(step => `- ${step}`).join('\n')}
`;
                }
            }

            return report;
        }
    </script>
</body>
</html> 