<script>
function initDiaryAccordion(root = document, opts = {}) {
    const autoOpenFirst = opts.autoOpenFirst !== false;

    root.querySelectorAll('.diary-card-header').forEach(header => {
        if (header.dataset.bound === '1') return;
        header.dataset.bound = '1';

        header.addEventListener('click', function(e) {
            if (e.target.closest('.quiz-btn')) return;

            const cardId = this.dataset.card;
            const body = root.querySelector(`.diary-card-body[data-body="${cardId}"]`);
            if (!body) return;

            const isActive = body.classList.contains('active');

            root.querySelectorAll('.diary-card-body').forEach(b => b.classList.remove('active'));
            root.querySelectorAll('.diary-card-header').forEach(h => h.classList.remove('active'));

            if (!isActive) {
                body.classList.add('active');
                this.classList.add('active');
            }
        });
    });

    if (autoOpenFirst) {
        const firstCard = root.querySelector('.diary-card-header');
        if (firstCard) firstCard.click();
    }
}

initDiaryAccordion(document, { autoOpenFirst: true });

// ============================================
// AUDIO RECORDING - Updated Selectors
// ============================================
let mediaRecorder = null;
let audioChunks = [];
let timerInterval = null;
let startTime = 0;

function startAudioTimer(diaryId) {
    const timerDiv = document.getElementById(`audio-timer-${diaryId}`);
    let seconds = 0;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        seconds++;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        if (timerDiv) timerDiv.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }, 1000);
}

function stopAudioTimer(diaryId) {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    const timerDiv = document.getElementById(`audio-timer-${diaryId}`);
    if (timerDiv) timerDiv.textContent = '';
}

// Audio Record Button
document.querySelectorAll('.audio-record-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const stopBtn = document.querySelector(`.audio-stop-btn[data-diary-id="${diaryId}"]`);
        const uploadStatus = document.getElementById(`upload-status-${diaryId}`);
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];
            
            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };
            
            mediaRecorder.onstop = async () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const formData = new FormData();
                formData.append('audio_recording', audioBlob, 'recording.webm');
                formData.append('student_id', studentId);
                formData.append('diary_id', diaryId);
                formData.append('duration', Math.floor(Date.now() / 1000) - startTime);
                
                if (uploadStatus) uploadStatus.innerHTML = '<span class="text-info">Uploading...</span>';
                
                const response = await fetch('<?= base_url("student/upload-audio") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.json();
                if (result.success) {
                    if (uploadStatus) uploadStatus.innerHTML = '<span class="text-success">✓ Uploaded! Refreshing...</span>';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    if (uploadStatus) uploadStatus.innerHTML = '<span class="text-danger">✗ Failed: ' + result.message + '</span>';
                }
                
                stream.getTracks().forEach(track => track.stop());
            };
            
            mediaRecorder.start();
            startTime = Math.floor(Date.now() / 1000);
            
            // Update UI
            this.style.display = 'none';
            if (stopBtn) stopBtn.style.display = 'inline-flex';
            startAudioTimer(diaryId);
            
        } catch (err) {
            alert('Unable to access microphone. Please check permissions.');
        }
    });
});

// Audio Stop Button
document.querySelectorAll('.audio-stop-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const diaryId = this.dataset.diaryId;
        const recordBtn = document.querySelector(`.audio-record-btn[data-diary-id="${diaryId}"]`);
        
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            this.style.display = 'none';
            if (recordBtn) recordBtn.style.display = 'inline-flex';
            stopAudioTimer(diaryId);
        }
    });
});

// ============================================
// VIDEO RECORDING
// ============================================
let videoMediaRecorder = null;
let videoChunks = [];
let isVideoRecording = false;
let videoStartTime = 0;
let currentVideoStream = null;

document.querySelectorAll('.video-record-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const statusDiv = document.getElementById(`video-status-${diaryId}`);
        
        if (isVideoRecording && videoMediaRecorder && videoMediaRecorder.state === 'recording') {
            videoMediaRecorder.stop();
            isVideoRecording = false;
            this.innerHTML = '<i class="fa fa-video-camera"></i> Record';
            this.classList.remove('btn-danger');
            if (statusDiv) statusDiv.innerHTML = '<span class="text-muted">Processing video...</span>';
            return;
        }
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            currentVideoStream = stream;
            showVideoModal(stream, diaryId, studentId);
        } catch (err) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">Unable to access camera: ' + err.message + '</span>';
        }
    });
});
function showVideoModal(stream, diaryId, studentId) {
    // Remove existing modal if any
    if (document.getElementById('videoModal')) {
        document.getElementById('videoModal').remove();
    }
    
    const modalHtml = `
        <div id="videoModal" class="video-modal">
            <div class="video-modal-content">
                <div class="video-modal-header">
                    <h4 class="video-modal-title">
                        <i class="fa fa-video-camera"></i> Record Video
                    </h4>
                    <button class="video-modal-close" id="closeVideoModalBtn">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="video-modal-body">
                    <div class="video-preview-container">
                        <video id="videoPreview" class="video-preview" autoplay playsinline muted></video>
                        <div class="video-recording-indicator" id="videoRecordingIndicator" style="display: none;">
                            <span class="recording-dot"></span>
                            <span class="recording-text">RECORDING</span>
                            <span class="recording-timer" id="videoTimer">00:00</span>
                        </div>
                    </div>
                </div>
                <div class="video-modal-footer">
                    <button class="video-btn video-btn-record" id="startVideoRecordBtn">
                        <i class="fa fa-circle"></i> Record
                    </button>
                    <button class="video-btn video-btn-stop" id="stopVideoRecordBtn" style="display: none;">
                        <i class="fa fa-stop"></i> Stop
                    </button>
                    <button class="video-btn video-btn-close" id="cancelVideoBtn">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const videoElement = document.getElementById('videoPreview');
    videoElement.srcObject = stream;
    
    let mediaRecorder = null;
    let recordedChunks = [];
    let isRecording = false;
    let recordStartTime = 0;
    let timerInterval = null;
    
    const startBtn = document.getElementById('startVideoRecordBtn');
    const stopBtn = document.getElementById('stopVideoRecordBtn');
    const closeBtn = document.getElementById('closeVideoModalBtn');
    const cancelBtn = document.getElementById('cancelVideoBtn');
    const recordingIndicator = document.getElementById('videoRecordingIndicator');
    const timerDisplay = document.getElementById('videoTimer');
    
    // Format time as MM:SS
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Start recording
    startBtn.onclick = () => {
        recordedChunks = [];
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
        
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };
        
        mediaRecorder.onstop = async () => {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            const duration = Math.floor((Date.now() - recordStartTime) / 1000);
            
            // Stop timer
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            
            // Show uploading state
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            
            await uploadVideo(blob, diaryId, studentId, duration);
            
            // Close modal after upload
            setTimeout(() => {
                if (currentVideoStream) {
                    currentVideoStream.getTracks().forEach(track => track.stop());
                }
                document.getElementById('videoModal').remove();
            }, 1500);
        };
        
        mediaRecorder.start(1000);
        isRecording = true;
        recordStartTime = Date.now();
        
        // Update UI
        startBtn.style.display = 'none';
        stopBtn.style.display = 'flex';
        recordingIndicator.style.display = 'flex';
        
        // Start timer
        timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - recordStartTime) / 1000);
            timerDisplay.textContent = formatTime(elapsed);
        }, 1000);
    };
    
    // Stop recording
    stopBtn.onclick = () => {
        if (mediaRecorder && isRecording) {
            mediaRecorder.stop();
            isRecording = false;
            stopBtn.disabled = true;
            stopBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            recordingIndicator.style.display = 'none';
        }
    };
    
    // Close modal function
    function closeModal() {
        if (mediaRecorder && isRecording) {
            mediaRecorder.stop();
        }
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        if (currentVideoStream) {
            currentVideoStream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('videoModal').remove();
    }
    
    closeBtn.onclick = closeModal;
    cancelBtn.onclick = closeModal;
    
    // Close on backdrop click
    document.querySelector('.video-modal').onclick = (e) => {
        if (e.target === document.querySelector('.video-modal')) {
            closeModal();
        }
    };
}
async function uploadVideo(blob, diaryId, studentId, duration) {
    const statusDiv = document.getElementById(`video-status-${diaryId}`);
    if (statusDiv) statusDiv.innerHTML = '<span class="text-info">Uploading video...</span>';
    
    const formData = new FormData();
    formData.append('video_recording', blob, 'recording.webm');
    formData.append('student_id', studentId);
    formData.append('diary_id', diaryId);
    formData.append('duration', duration);
    
    try {
        const response = await fetch('<?= base_url("student/upload-video") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        if (result.success) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-success">✓ Video uploaded! Refreshing...</span>';
            setTimeout(() => location.reload(), 1500);
        } else {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Upload failed: ' + (result.message || 'Unknown error') + '</span>';
        }
    } catch (err) {
        if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Network error: ' + err.message + '</span>';
    }
}

// Video file upload
document.querySelectorAll('.video-file-input').forEach(input => {
    input.addEventListener('change', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const statusDiv = document.getElementById(`video-status-${diaryId}`);
        
        if (this.files.length === 0) return;
        const file = this.files[0];
        
        if (!file.type.startsWith('video/')) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">Please select a video file.</span>';
            return;
        }
        if (file.size > 100 * 1024 * 1024) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">File too large. Max 100MB.</span>';
            return;
        }
        
        await uploadVideo(file, diaryId, studentId, 0);
        this.value = '';
    });
});

// ============================================
// PICTURE CAPTURE
// ============================================
document.querySelectorAll('.picture-capture-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            showPictureModal(stream, diaryId, studentId);
        } catch (err) {
            alert('Unable to access camera. Please check permissions.');
        }
    });
});
function showPictureModal(stream, diaryId, studentId) {
    // Remove existing modal if any
    if (document.getElementById('pictureModal')) {
        document.getElementById('pictureModal').remove();
    }
    
    const modalHtml = `
        <div id="pictureModal" class="picture-modal">
            <div class="picture-modal-content">
                <div class="picture-modal-header">
                    <h4 class="picture-modal-title">
                        <i class="fa fa-camera"></i> Capture Picture
                    </h4>
                    <button class="picture-modal-close" id="closePictureModalBtn">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="picture-modal-body">
                    <div class="camera-preview-container">
                        <video id="cameraPreview" class="camera-preview" autoplay playsinline></video>
                        <canvas id="photoCanvas" style="display: none;"></canvas>
                        <div class="camera-guide">
                            <div class="guide-frame"></div>
                        </div>
                    </div>
                    <div id="capturedPreview" class="captured-preview" style="display: none;">
                        <img id="capturedImage" class="captured-image">
                        <div class="captured-actions">
                            <button class="capture-btn recapture-btn" id="recaptureBtn">
                                <i class="fa fa-refresh"></i> Retake
                            </button>
                            <button class="capture-btn submit-btn" id="submitPictureBtn">
                                <i class="fa fa-check"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
                <div class="picture-modal-footer">
                    <button class="picture-btn capture-btn-main" id="capturePhotoBtn">
                        <i class="fa fa-camera"></i>
                    </button>
                    <button class="picture-btn cancel-btn" id="cancelPictureBtn">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const videoElement = document.getElementById('cameraPreview');
    const canvas = document.getElementById('photoCanvas');
    const capturedPreview = document.getElementById('capturedPreview');
    const cameraContainer = document.querySelector('.camera-preview-container');
    const captureBtn = document.getElementById('capturePhotoBtn');
    const recaptureBtn = document.getElementById('recaptureBtn');
    const submitBtn = document.getElementById('submitPictureBtn');
    const closeBtn = document.getElementById('closePictureModalBtn');
    const cancelBtn = document.getElementById('cancelPictureBtn');
    const capturedImage = document.getElementById('capturedImage');
    
    videoElement.srcObject = stream;
    
    let capturedImageData = null;
    
    // Function to close modal
    function closeModal() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('pictureModal').remove();
    }
    
    // Capture photo
    captureBtn.onclick = () => {
        const context = canvas.getContext('2d');
        
        // Set canvas dimensions to match video
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        
        // Draw video frame to canvas
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
        
        // Get image data
        capturedImageData = canvas.toDataURL('image/jpeg', 0.9);
        capturedImage.src = capturedImageData;
        
        // Hide camera preview, show captured preview
        cameraContainer.style.display = 'none';
        capturedPreview.style.display = 'block';
        captureBtn.style.display = 'none';
        
        // Add animation
        capturedPreview.classList.add('fade-in');
    };
    
    // Retake photo
    recaptureBtn.onclick = () => {
        // Show camera preview again
        cameraContainer.style.display = 'block';
        capturedPreview.style.display = 'none';
        captureBtn.style.display = 'flex';
        capturedImageData = null;
    };
    
    // Submit photo
    submitBtn.onclick = async () => {
        if (!capturedImageData) return;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
        
        const blob = await (await fetch(capturedImageData)).blob();
        const formData = new FormData();
        formData.append('picture', blob, 'capture.jpg');
        formData.append('student_id', studentId);
        formData.append('diary_id', diaryId);
        
        const statusDiv = document.getElementById(`picture-status-${diaryId}`);
        if (statusDiv) statusDiv.innerHTML = '<span class="text-info">Uploading picture...</span>';
        
        try {
            const response = await fetch('<?= base_url("student/upload-picture") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            if (result.success) {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-success">✓ Picture uploaded! Refreshing...</span>';
                setTimeout(() => location.reload(), 1500);
            } else {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Upload failed: ' + (result.message || 'Unknown error') + '</span>';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-check"></i> Submit';
            }
        } catch (err) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Network error: ' + err.message + '</span>';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa fa-check"></i> Submit';
        }
    };
    
    // Close modal
    closeBtn.onclick = closeModal;
    cancelBtn.onclick = closeModal;
    
    // Close on backdrop click
    document.querySelector('.picture-modal').onclick = (e) => {
        if (e.target === document.querySelector('.picture-modal')) {
            closeModal();
        }
    };
}

// Picture file upload
document.querySelectorAll('.picture-file-input').forEach(input => {
    input.addEventListener('change', async function() {
        const diaryId = this.dataset.diaryId;
        const studentId = this.dataset.studentId;
        const statusDiv = document.getElementById(`picture-status-${diaryId}`);
        
        if (this.files.length === 0) return;
        const file = this.files[0];
        
        if (!file.type.startsWith('image/')) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">Please select an image file.</span>';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">File too large. Max 5MB.</span>';
            return;
        }
        
        const formData = new FormData();
        formData.append('picture', file);
        formData.append('student_id', studentId);
        formData.append('diary_id', diaryId);
        
        if (statusDiv) statusDiv.innerHTML = '<span class="text-info">Uploading picture...</span>';
        
        try {
            const response = await fetch('<?= base_url("student/upload-picture") ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            if (result.success) {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-success">✓ Picture uploaded! Refreshing...</span>';
                setTimeout(() => location.reload(), 1500);
            } else {
                if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Upload failed: ' + (result.message || 'Unknown error') + '</span>';
            }
        } catch (err) {
            if (statusDiv) statusDiv.innerHTML = '<span class="text-danger">✗ Network error: ' + err.message + '</span>';
        }
        
        this.value = '';
    });
});
</script>
