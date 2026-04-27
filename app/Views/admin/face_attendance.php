<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content">
<div class="card">
<div class="card-header"><h3>Face Attendance</h3></div>

<div class="card-body text-center">
<video id="video" width="300" autoplay></video><br><br>
<button id="capture" class="btn btn-success">Mark Attendance</button>
<p id="result"></p>
</div>
</div>
</section>

<script>
const video = document.getElementById('video');
navigator.mediaDevices.getUserMedia({ video: true }).then(s => video.srcObject = s);

$('#capture').click(function () {
    let canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(function(blob) {
        let fd = new FormData();
        fd.append('image', blob);

        $.ajax({
            url: "<?= base_url('admin/face-attendance/mark') ?>",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            dataType: "json",
            success: res => $('#result').text(res.msg)
        });
    });
});
</script>

<?= $this->endSection() ?>