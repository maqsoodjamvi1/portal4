<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.face-attendance-wrap { max-width: 520px; margin: 0 auto; }
.face-video-box {
    position: relative;
    background: #111;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 4 / 3;
    max-height: 360px;
}
.face-video-box video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.face-attendance-status {
    min-height: 1.5rem;
    margin-top: 12px;
    font-size: 0.95rem;
}
.face-attendance-meta { font-size: 0.85rem; color: #6c757d; }
</style>

<section class="content">
<div class="card face-attendance-wrap shadow-sm">
<div class="card-header">
    <h3 class="card-title mb-0">Face Attendance</h3>
    <p class="text-muted small mb-0">Position your face in the frame, then mark attendance.</p>
</div>

<div class="card-body text-center">
    <div class="face-video-box mb-2">
        <video id="faceVideo" playsinline autoplay muted></video>
    </div>

    <div class="mb-2">
        <button type="button" id="btnMarkAttendance" class="btn btn-success btn-lg px-4">
            <i class="fa fa-check-circle"></i> Mark attendance
        </button>
        <button type="button" id="btnRetryCamera" class="btn btn-outline-secondary btn-lg ml-1" style="display:none;" title="Release and reopen the camera">
            <i class="fa fa-sync-alt"></i> Retry camera
        </button>
    </div>

    <div id="faceStatus" class="face-attendance-status text-secondary" aria-live="polite"></div>
    <div id="faceMeta" class="face-attendance-meta"></div>
</div>
</div>
</section>

<script>
(function () {
    var video = document.getElementById('faceVideo');
    var btn = document.getElementById('btnMarkAttendance');
    var btnRetry = document.getElementById('btnRetryCamera');
    var statusEl = document.getElementById('faceStatus');
    var metaEl = document.getElementById('faceMeta');
    var busy = false;
    var cameraOk = false;

    var MAX_SIDE = 960;
    var JPEG_QUALITY = 0.82;

    /** Try these in order: many PCs fail on facingMode: user (USB webcams) or strict resolution. */
    var CONSTRAINT_CHAIN = [
        { video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }, audio: false },
        { video: { width: { ideal: 640 }, height: { ideal: 480 } }, audio: false },
        { video: { width: { max: 1280 } }, audio: false },
        { video: true, audio: false }
    ];

    function setStatus(html, isError) {
        statusEl.innerHTML = html;
        statusEl.className = 'face-attendance-status ' + (isError ? 'text-danger' : 'text-success');
    }

    function setMeta(text) {
        metaEl.textContent = text || '';
    }

    function stopCamera() {
        var prev = video.srcObject;
        if (prev && prev.getTracks) {
            prev.getTracks().forEach(function (t) { t.stop(); });
        }
        video.srcObject = null;
        try { video.load(); } catch (e) {}
        cameraOk = false;
    }

    function humanReadableCameraError(err) {
        if (!err) return 'Could not open camera.';
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            return 'Camera permission denied. Allow camera for this site in the browser address bar.';
        }
        if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            return 'No camera found. Plug in a webcam or enable the built-in camera.';
        }
        if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
            return 'The camera could not be started. On Windows: Settings → Privacy → Camera → allow desktop apps & browser. ' +
                'Close other tabs that use the camera, unplug/replug USB webcam, then click Retry camera.';
        }
        if (err.name === 'OverconstrainedError') {
            return 'This camera does not support the requested mode. Click Retry camera.';
        }
        return err.message || 'Could not open camera.';
    }

    function getUserMediaWithFallbacks() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return Promise.reject(new Error('Camera not supported in this browser.'));
        }
        function attempt(i, lastErr) {
            if (i >= CONSTRAINT_CHAIN.length) {
                return Promise.reject(lastErr || new Error('All camera modes failed.'));
            }
            return navigator.mediaDevices.getUserMedia(CONSTRAINT_CHAIN[i]).catch(function (e) {
                if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
                    return Promise.reject(e);
                }
                return attempt(i + 1, e);
            });
        }
        return attempt(0, null);
    }

    function startCamera() {
        stopCamera();
        btn.disabled = true;
        btnRetry.style.display = 'none';
        setStatus('<i class="fa fa-spinner fa-spin"></i> Starting camera…', false);
        statusEl.className = 'face-attendance-status text-info';

        return getUserMediaWithFallbacks().then(function (stream) {
            video.srcObject = stream;
            return video.play();
        }).then(function () {
            cameraOk = true;
            btn.disabled = false;
            setStatus('Camera ready.', false);
            statusEl.className = 'face-attendance-status text-muted';
        }).catch(function (err) {
            console.error(err);
            cameraOk = false;
            btn.disabled = true;
            btnRetry.style.display = 'inline-block';
            setStatus(humanReadableCameraError(err), true);
        });
    }

    function captureOptimizedBlob(callback) {
        if (!video.videoWidth || !video.videoHeight) {
            callback(null);
            return;
        }
        var vw = video.videoWidth;
        var vh = video.videoHeight;
        var tw = vw;
        var th = vh;
        if (vw > MAX_SIDE || vh > MAX_SIDE) {
            if (vw >= vh) {
                tw = MAX_SIDE;
                th = Math.round(vh * (MAX_SIDE / vw));
            } else {
                th = MAX_SIDE;
                tw = Math.round(vw * (MAX_SIDE / vh));
            }
        }
        var canvas = document.createElement('canvas');
        canvas.width = tw;
        canvas.height = th;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, tw, th);
        canvas.toBlob(callback, 'image/jpeg', JPEG_QUALITY);
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        setStatus('Camera not supported in this browser.', true);
        btn.disabled = true;
        return;
    }

    startCamera();

    window.addEventListener('beforeunload', stopCamera);

    btnRetry.addEventListener('click', function () {
        startCamera();
    });

    btn.addEventListener('click', function () {
        if (busy || btn.disabled || !cameraOk) return;
        busy = true;
        btn.disabled = true;
        setStatus('<i class="fa fa-spinner fa-spin"></i> Recognizing…', false);
        statusEl.className = 'face-attendance-status text-info';
        setMeta('');

        captureOptimizedBlob(function (blob) {
            if (!blob) {
                busy = false;
                btn.disabled = false;
                setStatus('Camera not ready. Wait a moment and try again.', true);
                return;
            }

            var fd = new FormData();
            fd.append('image', blob, 'capture.jpg');
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            $.ajax({
                url: "<?= base_url('admin/face-attendance/mark') ?>",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                dataType: "json",
                timeout: 60000
            }).done(function (res) {
                if (res.success) {
                    var line = res.msg || 'Done.';
                    setStatus(line, false);
                    if (res.student_name && typeof res.similarity === 'number') {
                        setMeta('Match confidence: ' + res.similarity + '%' +
                            (res.reg_no ? (' · Reg: ' + res.reg_no) : ''));
                    }
                } else {
                    setStatus(res.msg || 'Could not mark attendance.', true);
                }
            }).fail(function (xhr) {
                var m = 'Request failed.';
                if (xhr.responseJSON && xhr.responseJSON.msg) m = xhr.responseJSON.msg;
                setStatus(m, true);
            }).always(function () {
                busy = false;
                btn.disabled = false;
            });
        });
    });
})();
</script>

<?= $this->endSection() ?>
