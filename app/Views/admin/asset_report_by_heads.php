<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if (isset($info)) {
    $header = 'Edit Asset Report';
    $id = $info->term_session_id ?? '';
    $term_id = $info->term_id ?? '';
    $session_id = $info->session_id ?? '';
    $start_date = $info->start_date ?? '';
    $end_date = $info->end_date ?? '';
} else {
    $header = 'Add Asset Report';
    $id = '';
    $term_id = '';
    $session_id = '';
    $start_date = '';
    $end_date = '';
}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-8">
                <h1>
                    <?= esc($header) ?>
                </h1>
            </div>
            <div class="col-sm-4">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Asset Report</li>
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
                        <div class="col-md-12 bg">
                            <div id="loader-1" class="overlay" style="display: none;">
                                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                            </div>
                        </div> 
                        <div id="termssessionarea" class="termssessionarea"></div>	
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<script type="text/javascript">
$(function() {
    // If you ever want to reload on session change, uncomment and use below.
    // $("#session_id").change(function() {
    var session_id = '<?= esc($session_id) ?>';
    if (session_id !== '') {
        $("#loader-1").css("display", "block");
        $.ajax({
            url: "<?= base_url('admin/assets_report/data'); ?>", 
            type: "POST",
            data: { session_id: session_id },
            success: function(res) {
                $("#termssessionarea").html(res);
                $("#loader-1").css("display", "none");
            }
        });
    }
    // });

    $('.datepicker').datetimepicker({
        format: 'DD/MM/YYYY'
    });
});
</script>

<?= $this->endSection() ?>
