<?php //$this->extend('layouts/admin_template') ?>
<?php //$this->section('content') ?>

<input type="hidden" class="student_id" name="student_id" value="<?= esc($id) ?>">
<?php /*$attachments = []; */
$db = \Config\Database::connect();  ?>
<?php foreach ($attachementTypesInfo as $key => $value):
    $attachment_id = 0;
    $attachementsinfo = $db->table('attachements')
                           ->where('student_id', $id)
                           ->where('a_type_id', $value->a_type_id)
                           ->get()
                           ->getRow();

    ///$attachments[$type->a_type_id] = $row;

    // return view('admin/studentstabs/attachements', [
    // 'id' => $id,
    // 'attachementTypesInfo' => $attachementTypesInfo,
    // 'attachments' => $attachments,
    // ]);
?>
<li class="list-group-item active"><h5><?= esc($value->a_type_name) ?></h5></li>

<input type="hidden" name="a_type_id" class="a_type_id<?= $value->a_type_id ?>" value="<?= $value->a_type_id ?>">
<input type="hidden" name="attachement_id" class="attachement_id<?= $value->a_type_id ?>" value="<?= $attachment_id ?>">

<li class="list-group-item">
    <div class="input-group mb-3">
        <div class="custom-file">
            <input type="file" class="custom-file-input" name="thumbnail<?= $value->a_type_id ?>" id="thumbnail<?= $value->a_type_id ?>">
            <label class="custom-file-label" for="thumbnail<?= $value->a_type_id ?>">Choose file</label>
        </div>
    </div>
    <div class="img-thumbnail text-center">
        <img src="<?= isset($attachementsinfo) ? base_url('studentattachements/' . $attachementsinfo->attachement_path) : '' ?>" 
             id="imgthumbnail<?= $value->a_type_id ?>" 
             class="img-fluid" alt="">
    </div>
</li>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('thumbnail<?= $value->a_type_id ?>');
    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        const formData = new FormData();

        formData.append('file', file);
        formData.append('a_type_id', document.querySelector('.a_type_id<?= $value->a_type_id ?>').value);
        formData.append('student_id', document.querySelector('.student_id').value);
        formData.append('attachement_id', document.querySelector('.attachement_id<?= $value->a_type_id ?>').value);

        fetch("<?= site_url('students/save_attachment') ?>", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(json => {
            if (json.success) {
                toastr.success(json.msg);
            } else {
                toastr.error('Update Error');
            }
        });

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("imgthumbnail<?= $value->a_type_id ?>").src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
});
</script>

<?php endforeach; ?>

<style>
body {
    font-family: Arial;
    font-size: 14px;
}
.bgColor {
    max-width: 440px;
    height: 150px;
    background-color: #fff4be;
    border-radius: 4px;
}
.bgColor label {
    font-weight: bold;
    color: #A0A0A0;
}
#targetLayer {
    float: left;
    width: 150px;
    height: 150px;
    text-align: center;
    line-height: 150px;
    font-weight: bold;
    color: #C0C0C0;
    background-color: #F0E8E0;
    border-bottom-left-radius: 4px;
    border-top-left-radius: 4px;
}
#uploadFormLayer {
    float: left;
    padding: 20px;
}
.btnSubmit {
    background-color: #696969;
    padding: 5px 30px;
    border: #696969 1px solid;
    border-radius: 4px;
    color: #FFFFFF;
    margin-top: 10px;
}
.inputFile {
    padding: 5px;
    background-color: #FFFFFF;
    border: #F0E8E0 1px solid;
    border-radius: 4px;
}
.image-preview {
    width: 150px;
    height: 150px;
    border-bottom-left-radius: 4px;
    border-top-left-radius: 4px;
}
</style>

<?php //$this->endSection() ?>