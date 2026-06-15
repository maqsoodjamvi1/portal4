<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.nutrition-card {
    transition: all 0.3s ease;
    border-start: 4px solid;
    margin-bottom: 20px;
}
.nutrition-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.nutrition-underweight { border-start-color: #3498db; }
.nutrition-normal { border-start-color: #2ecc71; }
.nutrition-overweight { border-start-color: #f39c12; }
.nutrition-obese { border-start-color: #e74c3c; }
.food-good { color: #27ae60; }
.food-bad { color: #e74c3c; }
</style>

<?= view('components/page_header', [
    'title' => 'Nutrition Suggestions',
    'icon' => 'fas fa-apple-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'BMI Dashboard', 'url' => base_url('admin/health/bmi-dashboard')],
        ['label' => 'Nutrition', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Add New Suggestion</h3>
                </div>
                <form id="addSuggestionForm">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="form-group">
                            <label>BMI Category <span class="text-danger">*</span></label>
                            <select name="bmi_category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="underweight">Underweight</option>
                                <option value="normal">Normal</option>
                                <option value="overweight">Overweight</option>
                                <option value="obese">Obese</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Age Group</label>
                            <select name="age_group" class="form-control">
                                <option value="">All Ages</option>
                                <option value="4-6">4-6 years</option>
                                <option value="7-9">7-9 years</option>
                                <option value="10-12">10-12 years</option>
                                <option value="13-15">13-15 years</option>
                                <option value="16-18">16-18 years</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="both">Both</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Diet Tips</label>
                            <textarea name="diet_tips" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Foods to Eat</label>
                            <textarea name="foods_to_eat" class="form-control" rows="2" placeholder="List foods separated by commas"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Foods to Avoid</label>
                            <textarea name="foods_to_avoid" class="form-control" rows="2" placeholder="List foods separated by commas"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Exercise Suggestions</label>
                            <textarea name="exercise_suggestions" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Medical Advice</label>
                            <textarea name="medical_advice" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">Add Suggestion</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nutrition Suggestions List</h3>
                    <div class="card-tools">
                        <select id="filterCategory" class="form-control form-control-sm" style="width: 150px;">
                            <option value="">All Categories</option>
                            <option value="underweight">Underweight</option>
                            <option value="normal">Normal</option>
                            <option value="overweight">Overweight</option>
                            <option value="obese">Obese</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div id="suggestionsList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p>Loading suggestions...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Edit Modal -->
<div class="modal fade" id="editSuggestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Nutrition Suggestion</h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <form id="editSuggestionForm">
                <?= csrf_field() ?>
                <input type="hidden" name="suggestion_id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>BMI Category</label>
                                <select name="bmi_category" id="edit_category" class="form-control" required>
                                    <option value="underweight">Underweight</option>
                                    <option value="normal">Normal</option>
                                    <option value="overweight">Overweight</option>
                                    <option value="obese">Obese</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Age Group</label>
                                <select name="age_group" id="edit_age_group" class="form-control">
                                    <option value="">All Ages</option>
                                    <option value="4-6">4-6 years</option>
                                    <option value="7-9">7-9 years</option>
                                    <option value="10-12">10-12 years</option>
                                    <option value="13-15">13-15 years</option>
                                    <option value="16-18">16-18 years</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" id="edit_gender" class="form-control">
                                    <option value="both">Both</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foods to Eat</label>
                                <textarea name="foods_to_eat" id="edit_foods_eat" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Foods to Avoid</label>
                                <textarea name="foods_to_avoid" id="edit_foods_avoid" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Exercise Suggestions</label>
                        <textarea name="exercise_suggestions" id="edit_exercise" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Medical Advice</label>
                        <textarea name="medical_advice" id="edit_medical" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Suggestion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadSuggestions();
    
    function loadSuggestions() {
        const category = $('#filterCategory').val();
        
        $.ajax({
            url: '<?= base_url("admin/health/nutrition-suggestions/data") ?>',
            type: 'POST',
            data: {
                category: category,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(data) {
                if (data.length === 0) {
                    $('#suggestionsList').html('<div class="text-center text-muted py-4">No suggestions found</div>');
                    return;
                }
                
                let html = '';
                data.forEach(function(item) {
                    let categoryClass = '';
                    if (item.bmi_category === 'underweight') categoryClass = 'nutrition-underweight';
                    else if (item.bmi_category === 'normal') categoryClass = 'nutrition-normal';
                    else if (item.bmi_category === 'overweight') categoryClass = 'nutrition-overweight';
                    else categoryClass = 'nutrition-obese';
                    
                    html += `
                        <div class="card nutrition-card ${categoryClass}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title">${item.title}</h5>
                                        <span class="badge text-bg-${item.bmi_category === 'underweight' ? 'info' : (item.bmi_category === 'normal' ? 'success' : (item.bmi_category === 'overweight' ? 'warning' : 'danger'))} ms-2">
                                            ${item.bmi_category.toUpperCase()}
                                        </span>
                                        ${item.age_group ? `<span class="badge text-bg-secondary ms-1">Age: ${item.age_group}</span>` : ''}
                                        ${item.gender !== 'both' ? `<span class="badge text-bg-secondary ms-1">${item.gender}</span>` : ''}
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-warning edit-suggestion" data-id="${item.suggestion_id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-suggestion" data-id="${item.suggestion_id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                                ${item.description ? `<p class="mt-2">${item.description}</p>` : ''}
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-check-circle text-success"></i> Foods to Eat:</strong>
                                        <div class="small food-good">${item.foods_to_eat || '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-times-circle text-danger"></i> Foods to Avoid:</strong>
                                        <div class="small food-bad">${item.foods_to_avoid || '-'}</div>
                                    </div>
                                </div>
                                ${item.exercise_suggestions ? `<div class="mt-2"><strong><i class="fas fa-dumbbell"></i> Exercise:</strong> <div class="small">${item.exercise_suggestions}</div></div>` : ''}
                                ${item.medical_advice ? `<div class="mt-2"><strong><i class="fas fa-stethoscope"></i> Medical Advice:</strong> <div class="small">${item.medical_advice}</div></div>` : ''}
                            </div>
                        </div>
                    `;
                });
                $('#suggestionsList').html(html);
            },
            error: function() {
                $('#suggestionsList').html('<div class="text-center text-danger py-4">Error loading suggestions</div>');
            }
        });
    }
    
    $('#filterCategory').change(loadSuggestions);
    
    $('#addSuggestionForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url("admin/health/nutrition-suggestions/add") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success('Suggestion added successfully');
                    $('#addSuggestionForm')[0].reset();
                    loadSuggestions();
                } else {
                    toastr.error(res.msg || 'Error adding suggestion');
                }
            },
            error: function() {
                toastr.error('Error adding suggestion');
            }
        });
    });
    
    $(document).on('click', '.edit-suggestion', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url("admin/health/nutrition-suggestions/data") ?>',
            type: 'POST',
            data: {
                suggestion_id: id,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(data) {
                const item = data[0];
                $('#edit_id').val(item.suggestion_id);
                $('#edit_category').val(item.bmi_category);
                $('#edit_age_group').val(item.age_group || '');
                $('#edit_gender').val(item.gender || 'both');
                $('#edit_sort_order').val(item.sort_order || 0);
                $('#edit_title').val(item.title);
                $('#edit_description').val(item.description || '');
                $('#edit_foods_eat').val(item.foods_to_eat || '');
                $('#edit_foods_avoid').val(item.foods_to_avoid || '');
                $('#edit_exercise').val(item.exercise_suggestions || '');
                $('#edit_medical').val(item.medical_advice || '');
                $('#editSuggestionModal').modal('show');
            }
        });
    });
    
    $('#editSuggestionForm').submit(function(e) {
        e.preventDefault();
        const id = $('#edit_id').val();
        
        $.ajax({
            url: '<?= base_url("admin/health/nutrition-suggestions/update") ?>/' + id,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success('Suggestion updated successfully');
                    $('#editSuggestionModal').modal('hide');
                    loadSuggestions();
                } else {
                    toastr.error(res.msg || 'Error updating suggestion');
                }
            },
            error: function() {
                toastr.error('Error updating suggestion');
            }
        });
    });
    
    $(document).on('click', '.delete-suggestion', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this suggestion?')) {
            $.ajax({
                url: '<?= base_url("admin/health/nutrition-suggestions/delete") ?>/' + id,
                type: 'POST',
                data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        toastr.success('Suggestion deleted');
                        loadSuggestions();
                    } else {
                        toastr.error('Error deleting suggestion');
                    }
                }
            });
        }
    });
});
</script>

<?= $this->endSection() ?>