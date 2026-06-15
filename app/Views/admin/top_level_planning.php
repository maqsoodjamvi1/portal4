<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Top Level Planning',
    'icon' => 'fas fa-project-diagram',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Top Level Planning', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card sms-card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye me-2"></i>
                        Top Level Planning
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="term_session_id">Select Term</label>
                                <select class="form-control" name="term_session_id" id="term_session_id">
                                    <option value="">Select Term</option>
                                    <?php foreach ($terms as $term): ?>
                                        <option value="<?= $term->term_session_id ?>">
                                            <?= esc($term->term_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" id="view_btn" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="loader" class="text-center" style="display: none;">
                        <i class="fas fa-2x fa-spinner fa-spin"></i> Loading...
                    </div>
                    
                    <div id="planning_view_container"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#view_btn').click(function() {
        var term_session_id = $('#term_session_id').val();
        
        if (!term_session_id) {
            toastr.warning('Please select a term');
            return;
        }
        
        $('#loader').show();
        $('#planning_view_container').hide();
        
        $.ajax({
            url: '<?= base_url('admin/top_level_planning/getViewData') ?>',
            type: 'POST',
            data: { term_session_id: term_session_id },
            dataType: 'json',
            success: function(res) {
                $('#planning_view_container').html(res.html).show();
                $('#loader').hide();
            },
            error: function() {
                $('#loader').hide();
                toastr.error('Error loading data');
            }
        });
    });
});
</script>

<?= $this->endSection() ?>