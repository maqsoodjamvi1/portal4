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

  <h3 class="card-title">Employee Face Enrollment</h3>

  <div class="card-tools">

    <a href="<?= base_url('admin/employee-face-attendance') ?>" class="btn btn-primary btn-sm me-1">

      <i class="fas fa-camera"></i> Open Scanner

    </a>

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

  <th>Employee ID</th>

  <th>Employee Name</th>

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

        <h5 class="modal-title">Enroll Employee Face</h5>

        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

      </div>



      <div class="modal-body text-center">



        <div class="form-group">

          <label>Select Employee</label>

          <select id="emp_id" class="form-control select2" style="width: 100%;">

            <option value="">-- Select Employee --</option>

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



    function loadEmployees() {

        $.get("<?= base_url('admin/employee-face-management/get-employees') ?>", function(response) {

            if (response.success && response.data) {

                let $select = $('#emp_id');

                $select.empty().append('<option value="">-- Select Employee --</option>');

                $.each(response.data, function(i, emp) {

                    let name = (emp.first_name || '') + ' ' + (emp.last_name || '');

                    $select.append(`<option value="${emp.id}">${emp.id} - ${name.trim()}</option>`);

                });

            }

        }, 'json');

    }

    loadEmployees();



    let table = $('#tbl').DataTable({

        processing: true,

        serverSide: false,

        ajax: {

            url: "<?= base_url('admin/employee-face-management/data') ?>",

            type: "GET",

            dataSrc: function (json) {

                if (json && json.error) {

                    toastr.error(json.error);

                }

                return (json && json.data) ? json.data : [];

            },

            error: function (xhr) {

                let msg = 'Could not load face enrollment list.';

                if (xhr.responseJSON && xhr.responseJSON.error) {

                    msg = xhr.responseJSON.error;

                } else if (xhr.status === 404) {

                    msg = 'Data endpoint not found. Deploy latest routes and clear cache.';

                }

                toastr.error(msg);

            }

        },

        columns: [

            {data: 'sno'},

            {data: 'emp_id'},

            {data: 'employee_name'},

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



    function stopCamera() {

        if (stream) {

            stream.getTracks().forEach(track => track.stop());

            stream = null;

        }



        if (video) {

            video.pause();

            video.srcObject = null;

            video.load();

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

            return 'Camera could not start. Close other tabs using the camera, or use Retry.';

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



    async function startCamera() {

        try {

            $('#cameraStatus').html('<i class="fas fa-spinner fa-spin"></i> Starting camera...');

            $('#retryCamera').hide();



            stopCamera();



            await new Promise(r => setTimeout(r, 400));



            stream = await getUserMediaWithFallbacks();



            video.style.display = 'block';

            $('#cameraPlaceholder').hide();

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



    $('#openCamera').click(function(){



        if (openingCamera) return;

        openingCamera = true;



        $('#cameraModal').modal('show');



        setTimeout(async function(){

            await startCamera();

            openingCamera = false;

        }, 500);



    });



    $('#retryCamera').click(function(){

        startCamera();

    });



    $('#captureFace').click(function(){



        let empId = $('#emp_id').val();



        if(!empId){

            toastr.error('Please select an employee');

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

            fd.append('emp_id', empId);

            fd.append('<?= csrf_token() ?>','<?= csrf_hash() ?>');



            $.ajax({

                url: "<?= base_url('admin/employee-face-management/enroll') ?>",

                type: "POST",

                data: fd,

                processData: false,

                contentType: false,

                dataType: "json",

                success: function(res){



                    if(res.success){



                        toastr.success(res.msg);



                        stopCamera();



                        $('#cameraModal').modal('hide');

                        table.ajax.reload();

                        loadEmployees();



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



    $(document).on('click','.del',function(){



        if(!confirm('Delete this face?')) return;



        $.post("<?= base_url('admin/employee-face-management/delete') ?>",{

            face_id: $(this).data('id'),

            '<?= csrf_token() ?>':'<?= csrf_hash() ?>'

        }, function(res){



            if(res.success){

                toastr.success(res.msg);

                table.ajax.reload();

                loadEmployees();

            } else if (res.msg) {

                toastr.error(res.msg);

            }



        }, 'json');

    });



    $('#cameraModal').on('hidden.bs.modal', function () {

        stopCamera();

        $('#cameraPlaceholder').show();

        video.style.display = 'none';

    });



});

</script>



<?= $this->endSection() ?>
