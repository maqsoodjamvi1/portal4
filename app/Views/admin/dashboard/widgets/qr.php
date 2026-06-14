<div class="card shadow-sm border-0">
    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-qrcode me-2"></i> QR Attendance
        </h5>
        <small id="qrCurrentTime"></small>
    </div>

    <div class="card-body">

        <!-- STATUS BOX -->
        <div id="qrStatusBox" class="alert alert-secondary text-center">
            <i class="fas fa-info-circle"></i> Ready to scan
        </div>

        <!-- SCAN BUTTON -->
        <div class="text-center">
            <button class="btn btn-primary btn-lg px-4" data-bs-toggle="modal" data-bs-target="#qrModal">
                <i class="fas fa-camera me-2"></i> Scan QR Code
            </button>
        </div>

        <!-- LAST ACTION -->
        <div class="mt-3 text-center text-muted" id="lastAction"></div>

    </div>
</div>

<!-- QR MODAL -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i> Scan QR Code
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-reader-results" class="mt-3"></div>
            </div>

        </div>
    </div>
</div>

<!-- QR SCRIPT -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let qrScanner;
let scanning = false;

// ================================
// Live Time
// ================================
setInterval(() => {
    const now = new Date();
    document.getElementById('qrCurrentTime').innerText = now.toLocaleTimeString();
}, 1000);

// ================================
// START SCANNER
// ================================
$('#qrModal').on('shown.bs.modal', function () {

    if (scanning) return;

    qrScanner = new Html5Qrcode("qr-reader");

    qrScanner.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: 250
        },
        (decodedText) => {
            processQR(decodedText);
            stopScanner();
        },
        (error) => {}
    );

    scanning = true;
});

// ================================
// STOP SCANNER
// ================================
function stopScanner() {
    if (qrScanner && scanning) {
        qrScanner.stop().then(() => {
            scanning = false;
        });
    }
}

// ================================
// PROCESS QR (AJAX)
// ================================
function processQR(qrCode) {

    $('#qrStatusBox').removeClass().addClass('alert alert-info')
        .html('<i class="fas fa-spinner fa-spin"></i> Processing...');

    $.ajax({
        url: "<?= base_url('admin/dashboard/processTeacherAttendance') ?>",
        method: "POST",
        data: {
            qr_code: qrCode,
            <?= csrf_token() ?>: "<?= csrf_hash() ?>"
        },
        success: function(res) {

            if (res.success) {

                let statusClass = res.type === 'checkin' ? 'success' : 'primary';

                $('#qrStatusBox')
                    .removeClass()
                    .addClass('alert alert-' + statusClass)
                    .html(res.message);

                $('#lastAction').html(
                    `<strong>${res.type.toUpperCase()}</strong> at ${res.time}`
                );

            } else {

                $('#qrStatusBox')
                    .removeClass()
                    .addClass('alert alert-danger')
                    .html(res.message);

            }
        },
        error: function() {
            $('#qrStatusBox')
                .removeClass()
                .addClass('alert alert-danger')
                .html('Server error. Try again.');
        }
    });

    $('#qrModal').modal('hide');
}
</script>
