<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.bmi-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}
.bmi-underweight { background: #3498db; color: white; }
.bmi-normal { background: #2ecc71; color: white; }
.bmi-overweight { background: #f39c12; color: white; }
.bmi-obese { background: #e74c3c; color: white; }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-chart-line mr-2"></i>BMI Records</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/health/bmi-dashboard') ?>">BMI</a></li>
                    <li class="breadcrumb-item active">Records</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Student BMI Records</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#bulkUpdateModal">
                    <i class="fas fa-upload mr-1"></i> Bulk Update
                </button>
                <a href="<?= base_url('admin/students_bulk_info_date_of_birth') ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-edit mr-1"></i> Bulk Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="classFilter" class="form-control">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class->cls_sec_id ?>">
                                <?= esc($class->class_name . ' - ' . $class->section_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="categoryFilter" class="form-control">
                        <option value="">All Categories</option>
                        <option value="underweight">Underweight</option>
                        <option value="normal">Normal</option>
                        <option value="overweight">Overweight</option>
                        <option value="obese">Obese</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name or reg no...">
                </div>
                <div class="col-md-2">
                    <button id="filterBtn" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <button id="resetBtn" class="btn btn-secondary btn-block">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="bmiRecordsTable">
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Gender</th>
                            <th>Height (cm)</th>
                            <th>Weight (kg)</th>
                            <th>BMI</th>
                            <th>Category</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </thead>
                        <tbody id="recordsTableBody">
                            <tr>
                                <td colspan="10" class="text-center text-muted">Select filters to load data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Edit BMI Modal -->
    <div class="modal fade" id="editBmiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit BMI Record</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="editBmiForm">
                    <?= csrf_field() ?>
                    <input type="hidden" id="edit_student_id" name="student_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Student Name</label>
                            <input type="text" id="edit_student_name" class="form-control" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Height (cm)</label>
                                    <input type="number" step="0.1" class="form-control" id="edit_height" name="height" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" id="edit_weight" name="weight" required>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info" id="bmiPreview">
                            BMI: -- (-- category)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

  <script>
$(document).ready(function() {
    let currentData = [];
    
    function calculateBMI(height, weight) {
        if (!height || !weight || height <= 0 || weight <= 0) return null;
        const heightInMeters = height / 100;
        return Math.round((weight / (heightInMeters * heightInMeters)) * 100) / 100;
    }
    
    function getBMICategory(bmi) {
        if (!bmi) return { text: 'Unknown', class: '' };
        if (bmi < 18.5) return { text: 'Underweight', class: 'bmi-underweight' };
        if (bmi < 25) return { text: 'Normal', class: 'bmi-normal' };
        if (bmi < 30) return { text: 'Overweight', class: 'bmi-overweight' };
        return { text: 'Obese', class: 'bmi-obese' };
    }
    
    function loadRecords() {
        const clsSecId = $('#classFilter').val();
        const category = $('#categoryFilter').val();
        const search = $('#searchInput').val();
        
        $('#recordsTableBody').html('<tr><td colspan="10" class="text-center"><div class="spinner-border spinner-border-sm"></div> Loading...</td></tr>');
        
        // FIX: Use the correct base URL
        
        const url = '<?= base_url("admin/health/bmi-records/getRecordsData") ?>';
        
        console.log('Loading records from:', url); // Debug log
        console.log('Filters:', { cls_sec_id: clsSecId, category: category, search: search });
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                cls_sec_id: clsSecId,
                category: category,
                search: search,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(data) {
                console.log('Data received:', data); // Debug log
                currentData = data;
                if (data.length === 0) {
                    $('#recordsTableBody').html('<tr><td colspan="10" class="text-center text-muted">No records found</td></tr>');
                    return;
                }
                
                let html = '';
                data.forEach(function(row) {
                    const category = getBMICategory(row.bmi);
                    html += '<tr>';
                    html += '<td>' + (row.reg_no || '-') + '</td>';
                    html += '<td><strong>' + (row.first_name || '') + ' ' + (row.last_name || '') + '</strong></td>';
                    html += '<td>' + (row.class_name || '') + ' ' + (row.section_name || '') + '</td>';
                    html += '<td>' + (row.gender || '-') + '</td>';
                    html += '<td>' + (row.height || '-') + '</td>';
                    html += '<td>' + (row.weight || '-') + '</td>';
                    html += '<td><strong>' + (row.bmi || '-') + '</strong></td>';
                    html += '<td><span class="bmi-badge ' + category.class + '">' + category.text + '</span></td>';
                    html += '<td>' + (row.bmi_updated_date ? new Date(row.bmi_updated_date).toLocaleDateString() : '-') + '</td>';
                    html += '<td><button class="btn btn-sm btn-primary edit-record" data-id="' + row.student_id + '" data-name="' + (row.first_name || '') + ' ' + (row.last_name || '') + '" data-height="' + (row.height || '') + '" data-weight="' + (row.weight || '') + '"><i class="fas fa-edit"></i> Edit</button></td>';
                    html += '</tr>';
                });
                $('#recordsTableBody').html(html);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                $('#recordsTableBody').html('<tr><td colspan="10" class="text-center text-danger">Error loading records: ' + error + '</td></tr>');
            }
        });
    }
    
    $('#filterBtn').click(loadRecords);
    $('#resetBtn').click(function() {
        $('#classFilter').val('');
        $('#categoryFilter').val('');
        $('#searchInput').val('');
        loadRecords();
    });
    
    // Edit record
    $(document).on('click', '.edit-record', function() {
        const studentId = $(this).data('id');
        const studentName = $(this).data('name');
        const height = $(this).data('height');
        const weight = $(this).data('weight');
        
        $('#edit_student_id').val(studentId);
        $('#edit_student_name').val(studentName);
        $('#edit_height').val(height);
        $('#edit_weight').val(weight);
        
        updateBmiPreview();
        $('#editBmiModal').modal('show');
    });
    
    $('#edit_height, #edit_weight').on('input', updateBmiPreview);
    
    function updateBmiPreview() {
        const height = parseFloat($('#edit_height').val());
        const weight = parseFloat($('#edit_weight').val());
        const bmi = calculateBMI(height, weight);
        const category = getBMICategory(bmi);
        $('#bmiPreview').html('BMI: ' + (bmi || '--') + ' (<span class="' + category.class + '" style="padding:2px 8px;border-radius:12px;">' + category.text + '</span>)');
    }
    
    $('#editBmiForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url("admin/health/bmi-records/save") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.msg);
                    $('#editBmiModal').modal('hide');
                    loadRecords();
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function(xhr) {
                console.error('Save error:', xhr.responseText);
                toastr.error('Error saving record');
            }
        });
    });
    
    // Load on page load
    loadRecords();
});
</script>
    
    <?= $this->endSection() ?>