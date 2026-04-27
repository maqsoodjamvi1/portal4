<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<section class="content">
    <!-- Filters Section -->
    
<div class="row">

        <div class="col-lg-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header">
                    <h3 class="card-title">Top Level Planning (<?= $session_name ?>)</h3>
                </div>
                <div class="card-body">
                    <!-- Results Section -->
    <div class="row">
        <div class="col-lg-12 no-print">
         <!-- Terms -->
                        <div class="form-group">
                            <label>Terms:</label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-link" onclick="toggleCheckboxes('term', true)">Select All</button>
                                <button type="button" class="btn btn-sm btn-link" onclick="toggleCheckboxes('term', false)">Deselect All</button>
                            </div>
                            <div class="row">
                                <?php foreach($filter_data['all_terms'] as $term): ?>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input term-checkbox" type="checkbox" 
                                                   name="terms[]" value="<?= $term['term_session_id'] ?>" 
                                                   <?= in_array($term['term_session_id'], $selected_filters['terms']) ? 'checked' : '' ?>>
                                            <label class="form-check-label"><?= $term['term_name'] ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Classes -->
                        <div class="form-group mt-4">
                            <label>Classes:</label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-link" onclick="toggleCheckboxes('class', true)">Select All</button>
                                <button type="button" class="btn btn-sm btn-link" onclick="toggleCheckboxes('class', false)">Deselect All</button>
                            </div>
                            <div class="row">
                                <?php foreach($filter_data['all_classes'] as $class): ?>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input class-checkbox" type="checkbox" 
                                                   name="classes[]" value="<?= $class['class_id'] ?>" 
                                                   <?= in_array($class['class_id'], $selected_filters['classes']) ? 'checked' : '' ?>>
                                            <label class="form-check-label"><?= $class['class_name'] ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Subjects -->
                        <div class="form-group mt-4">
                            <label>Subjects:</label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-link" onclick="toggleCheckboxes('subject', true)">Select All</button>
                                <button type="button" class="btn btn-sm btn-link" onclick="toggleCheckboxes('subject', false)">Deselect All</button>
                            </div>
                            <div class="row">
                                <?php foreach($filter_data['all_subjects'] as $subject): ?>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input subject-checkbox" type="checkbox" 
                                                   name="subjects[]" value="<?= $subject['sid'] ?>" 
                                                   <?= in_array($subject['sid'], $selected_filters['subjects']) ? 'checked' : '' ?>>
                                            <label class="form-check-label"><?= $subject['subject_name'] ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                        <button type="button" id="filterForm" class="btn btn-primary">Apply Filters</button>
                        <button type="button" class="btn btn-default" onclick="resetFilters()">Reset</button>
                         </div>
                    </div>

                    </div>
                <div id="tplhtmlresult">       
                    <?php if(!empty($grouped_data)): ?>
                        <div class="table-responsive">
                            <?php foreach($grouped_data as $class): ?>
                                <div class="mb-4" style="page-break-inside: avoid;">
                                    <h4 class="text-center font-weight-bold mb-3"><?= $class['class_name'] ?></h4>
                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:20%;">Subject</th>
                                                <?php foreach($class['terms'] as $term_id => $term_name): ?>
                                                    <th><?= $term_name ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($class['subjects'] as $subject): ?>
                                                <tr>
                                                    <td><?= $subject['subject_name'] ?></td>
                                                    <?php foreach($class['terms'] as $term_id => $term_name): ?>
                                                        <td style="direction: <?= in_array($subject['subject_name'], ['Urdu','Islamiat','Nazra']) ? 'rtl' : 'ltr' ?>">
                                                            <?= isset($subject['objectives'][$term_id]) ? nl2br(htmlspecialchars($subject['objectives'][$term_id])) : '' ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <hr class="my-4">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No data found with selected filters</div>
                    <?php endif; ?>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function resetFilters() {
    document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(cb => cb.checked = true);
    document.getElementById('filterForm').submit();
}

function toggleCheckboxes(type, check) {
    document.querySelectorAll(`.${type}-checkbox`).forEach(cb => cb.checked = check);
}
</script>

<script>
$(document).ready(function() {
    // Handle form submission via AJAX
    $('#filterForm').on('click', function(e) {
        //e.preventDefault();
        
        // Show loading indicator
        $('#tplhtmlresult').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

        // Collect selected filters
        let terms = [];
        $('input[name="terms[]"]:checked').each(function() {
            terms.push($(this).val());
        });

        let classes = [];
        $('input[name="classes[]"]:checked').each(function() {
            classes.push($(this).val());
        });

        let subjects = [];
        $('input[name="subjects[]"]:checked').each(function() {
            subjects.push($(this).val());
        });

        // AJAX request
        $.ajax({
            url: 'admin.php?c=top_level_planning_gradewise&m=index',
            //url: 'admin.php?c=top_level_planning_gradewise&m=data', 
            method: 'POST',
            data: {
                terms: terms,
                classes: classes,
                subjects: subjects,
                is_ajax: 1
            },
            success: function(response) {
                $('#tplhtmlresult').html(response);
            },
            error: function(xhr) {
                $('#tplhtmlresult').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Update reset function to use AJAX
    // window.resetFilters = function() {
    //     $('input[type="checkbox"]').prop('checked', true);
    //    // $('#filterForm').submit();
    // }
});

// Keep existing checkbox toggle functions
// function toggleCheckboxes(type, check) {
//     $(`.${type}-checkbox`).prop('checked', check);
// }
</script>

<?= $this->endSection() ?>