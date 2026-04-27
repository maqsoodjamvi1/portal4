<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
    $header = 'Add Section Incharge';
    $id = isset($info) ? (string) $info->ts_id : '0';
?>
<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Section Incharge</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Section Incharge</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0"></div>
                <div class="card-body">
                    <div class="tab-content">
                        <?= form_open(base_url('admin/teacher_section/save'), ['role' => 'form', 'id' => 'user-edit-form']) ?>
                        <?= form_hidden('id', (string) $id) ?>

                        <div class="teachersection" id="teachersection"></div>

                        <div class="form-group mt-3">
                            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
                            <button type="reset" class="btn btn-default">Reset</button>
                            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
                        </div>

                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript -->
<script type="text/javascript">
    $(function () {
        $.ajax({
            url: '<?= base_url('admin/teacher_section/data') ?>',
            type: "POST",
            data: {},
            success: function (res) {
                $("#teachersection").html(res);
            }
        });

        $('#user-edit-form').validate({});

        $('#user-edit-form').ajaxForm({
            beforeSubmit: function () {
                $('#submitBtn').html("Saving").prop('disabled', true);
                return $('#user-edit-form').valid();
            },
            success: function (responseText) {
                $('#submitBtn').html("Save").prop('disabled', false);
                var json = $.parseJSON(responseText);
                if (json.success) {
                    toastr.success(json.msg);
                    window.location.href = '<?= base_url('admin/teacher_section/add') ?>';
                } else {
                    toastr.error(json.msg);
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>