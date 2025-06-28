<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Simple Penetration Tester</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #000;
            color: #e0e0e0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid #333;
        }
        .btn {
            background: linear-gradient(45deg, #00ff41, #00d4ff);
            color: #000;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin: 10px;
        }
        .btn:hover {
            transform: scale(1.05);
        }
        .results {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            min-height: 300px;
        }
        .log-line {
            margin: 5px 0;
            padding: 8px;
            border-radius: 5px;
            border-left: 3px solid #00ff41;
            background: rgba(0, 255, 65, 0.1);
        }
        .input-group {
            margin: 15px 0;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #444;
            border-radius: 5px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center; color: #00ff41;">🔍 Simple Penetration Tester</h1>
        <p style="text-align: center; color: #888;">Test Debug Version</p>
        
        <div class="input-group">
            <label>🎯 Target URL:</label>
            <input type="text" id="target_url" value="http://localhost/LipointeTimHack">
        </div>
        
        <div class="input-group">
            <label>🔧 Test Mode:</label>
            <select id="test_mode">
                <option value="full">Full Penetration Test</option>
                <option value="recon">Reconnaissance Only</option>
                <option value="auth">Authentication Test</option>
            </select>
        </div>
        
        <button class="btn" onclick="startTest()">🚀 Bắt đầu Test</button>
        <button class="btn" onclick="clearResults()">🗑️ Clear Results</button>
        <button class="btn" onclick="testOutput()">🧪 Test Output</button>
        
        <div class="results" id="results">
            <h3>📋 Kết quả Test</h3>
            <div id="log_output">
                <div class="log-line">✅ Tool đã sẵn sàng! Click "Test Output" để kiểm tra...</div>
            </div>
        </div>
    </div>

    <script>
        function addLog(message, type = 'info') {
            const logOutput = document.getElementById('log_output');
            const logLine = document.createElement('div');
            logLine.className = 'log-line';
            logLine.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
            logOutput.appendChild(logLine);
            logOutput.scrollTop = logOutput.scrollHeight;
        }

        function testOutput() {
            addLog('🧪 Test output hoạt động!');
            addLog('✅ JavaScript function đang chạy OK');
            addLog('🎯 DOM element được tìm thấy');
        }

        function clearResults() {
            document.getElementById('log_output').innerHTML = '';
            addLog('🗑️ Results đã được clear');
        }

        async function startTest() {
            const targetUrl = document.getElementById('target_url').value;
            const testMode = document.getElementById('test_mode').value;
            
            addLog('🚀 Bắt đầu Penetration Test...');
            addLog(`🎯 Target: ${targetUrl}`);
            addLog(`🔧 Mode: ${testMode}`);
            addLog('');
            
            // Simulate testing process
            const tests = [
                '🔍 Tìm admin panel...',
                '🖐️ Fingerprinting technology...',
                '🔑 Test default credentials...',
                '💉 Test SQL injection...',
                '📤 Test file upload...'
            ];
            
            for (let i = 0; i < tests.length; i++) {
                await new Promise(resolve => setTimeout(resolve, 1000));
                addLog(tests[i]);
                
                // Random results
                if (Math.random() > 0.5) {
                    await new Promise(resolve => setTimeout(resolve, 500));
                    addLog(`✅ ${tests[i].replace('Test ', '')} - Thành công!`);
                } else {
                    await new Promise(resolve => setTimeout(resolve, 500));
                    addLog(`❌ ${tests[i].replace('Test ', '')} - Thất bại`);
                }
            }
            
            addLog('');
            addLog('🎯 Penetration test hoàn thành!');
            addLog('📊 Tổng kết: Tìm thấy một số lỗ hổng bảo mật');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            addLog('🚀 Simple Penetration Tester initialized!');
            console.log('Page loaded successfully');
        });
    </script>
</body>
</html> 