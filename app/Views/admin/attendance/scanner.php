<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Teacher Attendance Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .scanner-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .scanner-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .video-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #scanner {
            width: 100%;
            height: auto;
            display: block;
        }
        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid #00ff00;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.3);
            pointer-events: none;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast-custom {
            min-width: 300px;
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        .toast-success { border-left: 5px solid #28a745; }
        .toast-error { border-left: 5px solid #dc3545; }
        .toast-warning { border-left: 5px solid #ffc107; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-top: 15px;
            font-size: 12px;
            font-family: monospace;
            max-height: 150px;
            overflow-y: auto;
        }
        .camera-select {
            margin: 10px 0;
            padding: 5px;
            border-radius: 5px;
        }
        .scanning {
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        .btn-camera-reset {
            margin-left: 10px;
        }
        .camera-tips {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 10px;
            margin: 15px 0;
            font-size: 13px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="scanner-container">
        <div id="toastContainer" class="toast-container"></div>
        
        <div class="scanner-card">
            <h2 class="text-center mb-4">
                <i class="fas fa-qrcode me-2"></i>
                Teacher Attendance Scanner
            </h2>
            
            <div class="text-center mb-3">
                <span id="statusBadge" class="badge bg-secondary p-3">
                    <i class="fas fa-camera me-2"></i>Initializing camera...
                </span>
            </div>
            
            <!-- Camera controls -->
            <div class="text-center mb-2">
                <select id="cameraSelect" class="camera-select form-select d-inline-block w-auto">
                    <option value="">Select Camera...</option>
                </select>
                <button class="btn btn-warning btn-sm btn-camera-reset" onclick="resetCameraAndRestart()">
                    <i class="fas fa-sync-alt me-1"></i> Reset Camera
                </button>
                <button class="btn btn-info btn-sm btn-camera-reset" onclick="checkCameraHealth()">
                    <i class="fas fa-stethoscope me-1"></i> Check Camera
                </button>
            </div>
            
            <div class="video-container position-relative">
                <video id="scanner" playsinline autoplay muted></video>
                <div class="scan-overlay"></div>
            </div>
            
            <div id="cameraHelp" class="camera-tips" style="display: none;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Camera Tips:</strong>
                <ul class="mb-0 mt-1">
                    <li>Close OBS, Zoom, Skype, or any other camera apps</li>
                    <li>Click "Reset Camera" after closing other apps</li>
                    <li>Check browser permissions: Allow camera access for this site</li>
                </ul>
            </div>
            
            <div class="mt-3 text-center">
                <input type="text" id="manualQrInput" class="form-control d-inline-block w-50" placeholder="Enter QR code manually">
                <button class="btn btn-primary" onclick="processManualQr()">Submit</button>
            </div>
            
            <div class="mt-4 text-center">
                <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">Back to Users</a>
            </div>
            
            <!-- Debug Panel -->
            <div class="debug-panel">
                <strong>Debug Console:</strong><br>
                <div id="debugLog"></div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
    <script>
        let codeReader = null;
        let currentStream = null;
        let scanningActive = true;
        let isProcessing = false;
        
        function addDebugLog(message) {
            const debugDiv = document.getElementById('debugLog');
            const timestamp = new Date().toLocaleTimeString();
            debugDiv.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            debugDiv.scrollTop = debugDiv.scrollHeight;
            console.log(message);
        }
        
        async function resetCameraAndRestart() {
            addDebugLog('🔄 Manually resetting camera...');
            updateStatus('Resetting camera...', 'warning');
            
            // Stop all tracks
            if (currentStream) {
                currentStream.getTracks().forEach(track => {
                    track.stop();
                    addDebugLog(`Stopped track: ${track.kind}`);
                });
                currentStream = null;
            }
            
            // Reset code reader
            if (codeReader) {
                try {
                    await codeReader.reset();
                    addDebugLog('Code reader reset');
                } catch(e) {
                    addDebugLog('Error resetting reader: ' + e.message);
                }
                codeReader = null;
            }
            
            // Clear video element
            const videoElement = document.getElementById('scanner');
            if (videoElement) {
                videoElement.srcObject = null;
                videoElement.pause();
            }
            
            // Wait a moment for camera to release
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Try to start again with the selected camera
            const selectedCamera = document.getElementById('cameraSelect').value;
            if (selectedCamera) {
                await startScanner(selectedCamera);
            } else {
                await startScanner();
            }
        }
        
        async function checkCameraHealth() {
            addDebugLog('🔍 Checking camera health...');
            updateStatus('Checking camera...', 'warning');
            
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                addDebugLog(`Found ${videoDevices.length} video devices`);
                
                if (videoDevices.length === 0) {
                    addDebugLog('⚠️ No video devices found!');
                    updateStatus('No camera detected', 'danger');
                    document.getElementById('cameraHelp').style.display = 'block';
                    return false;
                }
                
                for (let i = 0; i < videoDevices.length; i++) {
                    addDebugLog(`Device ${i}: ${videoDevices[i].label || 'Unnamed'}`);
                }
                
                // Try to get a test stream
                try {
                    const testStream = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: { exact: videoDevices[0].deviceId } }
                    });
                    testStream.getTracks().forEach(track => track.stop());
                    addDebugLog('✅ Camera test successful - camera is working');
                    updateStatus('Camera is working', 'success');
                    document.getElementById('cameraHelp').style.display = 'none';
                    return true;
                } catch (testErr) {
                    addDebugLog(`❌ Camera test failed: ${testErr.message}`);
                    document.getElementById('cameraHelp').style.display = 'block';
                    
                    if (testErr.name === 'NotAllowedError') {
                        addDebugLog('Camera permission denied. Please allow camera access.');
                        updateStatus('Permission denied', 'danger');
                        showToast('Camera access denied. Please allow camera in browser settings.', 'error');
                    } else if (testErr.name === 'NotFoundError') {
                        addDebugLog('Camera device not found.');
                        updateStatus('No camera found', 'danger');
                    } else if (testErr.name === 'NotReadableError') {
                        addDebugLog('Camera is in use by another application (OBS, Zoom, etc.)');
                        updateStatus('Camera in use', 'danger');
                        showToast('Camera is in use by another app. Close OBS, Zoom, etc. and click Reset Camera.', 'warning');
                    }
                    return false;
                }
            } catch (err) {
                addDebugLog(`❌ Enumeration error: ${err.message}`);
                updateStatus('Camera check failed', 'danger');
                return false;
            }
        }
        
        async function startScanner(deviceId = null) {
            addDebugLog('Starting scanner...');
            updateStatus('Starting camera...', 'warning');
            scanningActive = true;
            isProcessing = false;
            
            // Stop any existing scanner
            if (codeReader) {
                try {
                    await codeReader.reset();
                    addDebugLog('Previous scanner reset');
                } catch(e) {
                    addDebugLog('Error resetting: ' + e.message);
                }
            }
            
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            
            codeReader = new ZXing.BrowserQRCodeReader();
            
            try {
                // Get available cameras
                const videoInputDevices = await codeReader.listVideoInputDevices();
                addDebugLog(`Found ${videoInputDevices.length} camera(s)`);
                
                if (videoInputDevices.length === 0) {
                    addDebugLog('No cameras found!');
                    updateStatus('No camera found', 'danger');
                    document.getElementById('cameraHelp').style.display = 'block';
                    return;
                }
                
                // Clear and repopulate camera dropdown
                const cameraSelect = document.getElementById('cameraSelect');
                cameraSelect.innerHTML = '<option value="">Select Camera...</option>';
                
                // Log all cameras and add to dropdown
                videoInputDevices.forEach((device, index) => {
                    const label = device.label || `Camera ${index + 1}`;
                    addDebugLog(`Camera ${index}: ${label} (ID: ${device.deviceId.substring(0, 20)}...)`);
                    
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = label;
                    cameraSelect.appendChild(option);
                });
                
                // Use selected camera or first one
                let targetDeviceId = deviceId || videoInputDevices[0].deviceId;
                addDebugLog(`Using camera: ${targetDeviceId.substring(0, 20)}...`);
                
                // Get the video element
                const videoElement = document.getElementById('scanner');
                
                // Start decoding
                await codeReader.decodeFromConstraints(
                    { video: { deviceId: { exact: targetDeviceId } } },
                    videoElement,
                    (result, err) => {
                        if (result && scanningActive && !isProcessing) {
                            isProcessing = true;
                            addDebugLog(`✅ QR Code detected: ${result.text.substring(0, 50)}...`);
                            handleScan(result.text);
                            setTimeout(() => {
                                isProcessing = false;
                                addDebugLog('Resuming scanner...');
                            }, 3000);
                        }
                        if (err && !(err instanceof ZXing.NotFoundException) && scanningActive) {
                            if (!err.message.includes('NotFoundException')) {
                                addDebugLog(`Scan error: ${err.message}`);
                            }
                        }
                    }
                );
                
                addDebugLog('✅ Scanner started successfully!');
                updateStatus('Scanner active - point at QR code', 'success');
                document.getElementById('cameraHelp').style.display = 'none';
                
                // Add scanning animation
                document.querySelector('.video-container').classList.add('scanning');
                
            } catch (err) {
                addDebugLog(`❌ Failed to start scanner: ${err.message}`);
                document.getElementById('cameraHelp').style.display = 'block';
                
                if (err.message.includes('Could not start video source')) {
                    addDebugLog('Camera is likely in use by another application');
                    updateStatus('Camera in use - close other apps', 'danger');
                    showToast('Camera is in use. Please close OBS, Zoom, or other camera apps and click Reset Camera.', 'warning');
                } else if (err.message.includes('Permission denied')) {
                    updateStatus('Camera permission denied', 'danger');
                    showToast('Camera access denied. Please allow camera in browser settings.', 'error');
                } else {
                    updateStatus('Camera error: ' + err.message, 'danger');
                }
            }
        }
        
        // Camera selection change handler
        document.getElementById('cameraSelect').addEventListener('change', function() {
            if (this.value) {
                addDebugLog(`Switching to camera: ${this.value.substring(0, 20)}...`);
                startScanner(this.value);
            }
        });
        
        function handleScan(qrCode) {
            addDebugLog(`Processing QR code...`);
            updateStatus('Processing...', 'warning');
            
            fetch('<?= base_url("admin/attendance/process") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'qr_code=' + encodeURIComponent(qrCode)
            })
            .then(response => response.json())
            .then(data => {
                addDebugLog(`Server response: ${data.success ? '✅ Success' : '❌ Failed'} - ${data.message}`);
                if (data.success) {
                    showToast(data.message, 'success');
                    updateStatus('✅ ' + data.message, 'success');
                    playBeep();
                    setTimeout(() => {
                        if (scanningActive) {
                            updateStatus('Scanner active - point at QR code', 'success');
                        }
                    }, 3000);
                } else {
                    showToast(data.message, 'error');
                    updateStatus('❌ ' + data.message, 'danger');
                    setTimeout(() => {
                        if (scanningActive) {
                            updateStatus('Scanner active - point at QR code', 'success');
                        }
                    }, 3000);
                }
            })
            .catch(error => {
                addDebugLog(`❌ Fetch error: ${error.message}`);
                showToast('Network error. Please try again.', 'error');
                updateStatus('Network error', 'danger');
            });
        }
        
        function playBeep() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.frequency.value = 800;
                gainNode.gain.value = 0.3;
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch(e) {
                // Beep not supported
            }
        }
        
        function processManualQr() {
            const qrCode = document.getElementById('manualQrInput').value.trim();
            if (qrCode) {
                addDebugLog(`📝 Manual QR entry: ${qrCode.substring(0, 50)}...`);
                handleScan(qrCode);
                document.getElementById('manualQrInput').value = '';
            } else {
                showToast('Please enter a QR code', 'warning');
            }
        }
        
        function updateStatus(text, type) {
            const badge = document.getElementById('statusBadge');
            const iconMap = {
                'success': 'check',
                'danger': 'times',
                'warning': 'exclamation-triangle',
                'secondary': 'camera'
            };
            const icon = iconMap[type] || 'camera';
            badge.className = `badge bg-${type} p-3`;
            badge.innerHTML = `<i class="fas fa-${icon} me-2"></i>${text}`;
        }
        
        function showToast(message, type) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-custom toast-${type}`;
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')} me-3 fa-2x text-${type}"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            container.appendChild(toast);
            setTimeout(() => { if (toast.parentElement) toast.remove(); }, 5000);
        }
        
        // Start scanner on page load
        document.addEventListener('DOMContentLoaded', function() {
            addDebugLog('Page loaded, initializing scanner...');
            startScanner();
        });
        
        // Handle page unload
        window.addEventListener('beforeunload', function() {
            if (codeReader) {
                codeReader.reset();
            }
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>