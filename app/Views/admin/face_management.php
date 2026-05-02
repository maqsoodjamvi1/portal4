<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.camera-preview {
    position: relative;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.camera-preview video {
    width: 100%;
    height: auto;
    max-height: 300px;
    object-fit: cover;
}

.camera-placeholder {
    color: #fff;
    text-align: center;
    padding: 40px;
}

.camera-placeholder i {
    font-size: 48px;
    margin-bottom: 15px;
}

.camera-controls {
    margin-top: 15px;
}

#cameraStatus {
    font-size: 12px;
    margin-top: 10px;
}
</style>

<section class="content">
<div class="card">

<div class="card-header">
  <h3 class="card-title">Face Management</h3>
  <div class="card-tools">
    <button class="btn btn-success btn-sm" id="openCamera">
      <i class="fas fa-camera"></i> Enroll Face
    </button>
  </div>
</div>

<div class="card-body">
<table id="tbl" class="table table-bordered table-hover">
<thead>
<tr>
  <th>#</th>
  <th>Student ID</th>
  <th>Student Name</th>
  <th>Face</th>
  <th>Action</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
</div>

</div>
</section>

<!-- CAMERA MODAL -->
<div class="modal fade" id="cameraModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Enroll Face</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body text-center">

        <div class="form-group">
          <label>Select Student</label>
          <select id="student_id" class="form-control select2" style="width: 100%;">
            <option value="">-- Select Student --</option>
          </select>
        </div>

        <div class="camera-preview" id="cameraPreview">
          <div class="camera-placeholder" id="cameraPlaceholder">
            <i class="fas fa-camera"></i>
            <p>Camera will start here</p>
          </div>
          <video id="video" style="display: none;" autoplay playsinline></video>
        </div>

        <div id="cameraStatus" class="text-muted"></div>

        <div class="camera-controls">
          <button class="btn btn-primary" id="captureFace" disabled>
            <i class="fas fa-camera"></i> Capture & Save
          </button>
          <button class="btn btn-secondary" id="retryCamera" style="display: none;">
            <i class="fas fa-sync-alt"></i> Retry Camera
          </button>
        </div>

      </div>

    </div>
  </div>
</div>
<script>
$(function () {

    let video = document.getElementById('video');
    let stream = null;
    let cameraActive = false;
    let openingCamera = false;

    // Load students
    function loadStudents() {
        $.get("<?= base_url('admin/face-management/get-students') ?>", function(response) {
            if (response.success && response.data) {
                let $select = $('#student_id');
                $select.empty().append('<option value="">-- Select Student --</option>');
                $.each(response.data, function(i, student) {
                    $select.append(`<option value="${student.student_id}">${student.reg_no} - ${student.first_name} ${student.last_name}</option>`);
                });
            }
        }, 'json');
    }
    loadStudents();

    // DataTable
    let table = $('#tbl').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "<?= base_url('admin/face-management/data') ?>",
            type: "GET",
            dataSrc: "data"
        },
        columns: [
            {data: 'sno'},
            {data: 'student_id'},
            {data: 'student_name'},
            {
                data: 'image',
                render: d => d 
                    ? `<img src="${d}" width="50" height="50" style="object-fit:cover;border-radius:50%;">`
                    : '<span class="text-muted">No Face</span>'
            },
            {
                data: 'face_id',
                render: d => d 
                    ? `<button class="btn btn-danger btn-sm del" data-id="${d}"><i class="fas fa-trash"></i></button>`
                    : '<span class="text-muted">Not enrolled</span>'
            }
        ]
    });

    // 🔥 STOP CAMERA (STRONG RESET)
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        if (video) {
            video.pause();
            video.srcObject = null;
            video.load(); // critical reset
        }

        cameraActive = false;

        $('#captureFace').prop('disabled', true);
        $('#retryCamera').hide();
        $('#cameraStatus').html('');
    }

    const CONSTRAINT_CHAIN = [
        { video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } },
        { video: { width: { ideal: 640 }, height: { ideal: 480 } } },
        { video: { width: { max: 1280 } } },
        { video: true }
    ];

    function humanReadableCameraError(err) {
        if (!err) return 'Could not open camera.';
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            return 'Camera permission denied. Allow camera for this site.';
        }
        if (err.name === 'NotFoundError') {
            return 'No camera found.';
        }
        if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
            return 'Camera could not start. Check Windows Settings → Privacy → Camera, close other browser tabs using the camera, or replug a USB webcam. Use Retry below.';
        }
        if (err.name === 'OverconstrainedError') {
            return 'This camera does not support the requested mode. Click Retry Camera.';
        }
        return err.message || 'Could not open camera.';
    }

    async function getUserMediaWithFallbacks() {
        let lastErr = null;
        for (let i = 0; i < CONSTRAINT_CHAIN.length; i++) {
            try {
                return await navigator.mediaDevices.getUserMedia(CONSTRAINT_CHAIN[i]);
            } catch (e) {
                if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
                    throw e;
                }
                lastErr = e;
            }
        }
        throw lastErr || new Error('All camera modes failed.');
    }

    // START CAMERA: release first, then try relaxed constraints (USB cams often fail on facingMode)
    async function startCamera() {
        try {
            $('#cameraStatus').html('<i class="fas fa-spinner fa-spin"></i> Starting camera...');
            $('#retryCamera').hide();

            stopCamera();

            await new Promise(r => setTimeout(r, 400));

            stream = await getUserMediaWithFallbacks();

            video.srcObject = stream;
            await video.play();

            cameraActive = true;

            $('#captureFace').prop('disabled', false);
            $('#cameraStatus').html('<span class="text-success">Camera ready</span>');

        } catch (err) {
            console.error(err);
            $('#cameraStatus').html('<span class="text-danger">' + humanReadableCameraError(err) + '</span>');
            $('#retryCamera').show();
        }
    }

    // 🔥 OPEN CAMERA (NO DOUBLE CALL)
    $('#openCamera').click(function(){

        if (openingCamera) return;
        openingCamera = true;

        $('#cameraModal').modal('show');

        setTimeout(async function(){
            await startCamera();
            openingCamera = false;
        }, 500);

    });

    // Retry
    $('#retryCamera').click(function(){
        startCamera();
    });

    // 🔥 CAPTURE FACE
    $('#captureFace').click(function(){

        let studentId = $('#student_id').val();

        if(!studentId){
            toastr.error('Please select student');
            return;
        }

        if(!cameraActive){
            toastr.error('Camera not ready');
            return;
        }

        let canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        canvas.getContext('2d').drawImage(video, 0, 0);

        canvas.toBlob(function(blob){

            let fd = new FormData();
            fd.append('image', blob);
            fd.append('student_id', studentId);
            fd.append('<?= csrf_token() ?>','<?= csrf_hash() ?>');

            $.ajax({
                url: "<?= base_url('admin/face-management/enroll') ?>",
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res){

                    if(res.success){

                        toastr.success(res.msg);

                        stopCamera(); // 🔥 important

                        $('#cameraModal').modal('hide');
                        table.ajax.reload();
                        loadStudents();

                    } else {
                        toastr.error(res.msg);
                    }
                },
                error: function(){
                    toastr.error('Upload failed');
                }
            });

        }, 'image/jpeg');
    });

    // DELETE
    $(document).on('click','.del',function(){

        if(!confirm('Delete this face?')) return;

        $.post("<?= base_url('admin/face-management/delete') ?>",{
            face_id: $(this).data('id'),
            '<?= csrf_token() ?>':'<?= csrf_hash() ?>'
        }, function(res){

            if(res.success){
                toastr.success(res.msg);
                table.ajax.reload();
                loadStudents();
            }

        }, 'json');
    });

    // 🔥 ALWAYS STOP CAMERA ON CLOSE
    $('#cameraModal').on('hidden.bs.modal', function () {
        stopCamera();
    });

});
</script>

<?= $this->endSection() ?>