<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-file-alt mr-2"></i>BMI Reports</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/health/bmi-dashboard') ?>">BMI</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Generate BMI Report</h3>
        </div>
        <div class="card-body">
            <form id="reportForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Class</label>
                            <select name="cls_sec_id" class="form-control">
                                <option value="">All Classes</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class->cls_sec_id ?>">
                                        <?= esc($class->class_name . ' - ' . $class->section_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>BMI Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                <option value="underweight">Underweight</option>
                                <option value="normal">Normal</option>
                                <option value="overweight">Overweight</option>
                                <option value="obese">Obese</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Report Type</label>
                            <select name="report_type" class="form-control">
                                <option value="summary">Summary Report</option>
                                <option value="detailed">Detailed Report</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-chart-bar mr-1"></i> Generate
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="btn-group btn-block">
                                <button type="button" class="btn btn-success" id="exportExcel">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </button>
                                <button type="button" class="btn btn-danger" id="exportPdf">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div id="reportResult" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Report Results</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body" id="reportContent">
            </div>
        </div>
    </div>
</section>

<script>
$('#reportForm').submit(function(e) {
    e.preventDefault();
    
    $('#reportResult').hide();
    $('#reportContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Generating report...</p></div>');
    
    $.ajax({
        url: '<?= base_url("admin/health/bmi-reports/generate") ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'html',
        success: function(html) {
            $('#reportContent').html(html);
            $('#reportResult').show();
        },
        error: function() {
            $('#reportContent').html('<div class="alert alert-danger">Error generating report</div>');
            $('#reportResult').show();
        }
    });
});

$('#exportExcel').click(function() {
    const formData = $('#reportForm').serialize();
    window.location.href = '<?= base_url("admin/health/bmi-reports/export-excel") ?>?' + formData;
});

$('#exportPdf').click(function() {
    const formData = $('#reportForm').serialize();
    window.open('<?= base_url("admin/health/bmi-reports/export-pdf") ?>?' + formData, '_blank');
});
</script>

<?= $this->endSection() ?>