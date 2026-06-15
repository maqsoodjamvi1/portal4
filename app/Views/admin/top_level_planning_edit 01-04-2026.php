<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $isEdit = isset($info) && !empty($info);
  $header = $isEdit ? 'Edit Scheme of Studies' : 'Add Scheme of Studies';
  $id     = $isEdit ? (string)($info->id ?? '') : '';
?>

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="mb-0">Scheme of Studies</h1>
        <small class="text-muted d-block"><?= esc($header) ?></small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Scheme of Studies</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header p-2">
        <ul class="nav nav-pills" role="tablist">
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning') ?>">Scheme of Studies (List)</a></li>
          <?php if ($id === ''): ?>
            <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/top_level_planning/add') ?>"><?= esc($header) ?></a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link active" href="<?= site_url('admin/top_level_planning/edit?id=' . urlencode($id)) ?>"><?= esc($header) ?></a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_sections/add') ?>">Add Scheme (Class-wise)</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_subject/add') ?>">Add Scheme (Subject-wise)</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_gradewise') ?>">Grade-wise Views</a></li>
        </ul>
      </div>

      <div class="card-body">
        <?php
          echo form_open(base_url('admin/top_level_planning/save'), 'role="form" id="user-edit-form"');
          echo form_hidden('id', (string)$id);
          if (function_exists('csrf_field')) { echo csrf_field(); }
        ?>

        <div class="row">
          <!-- Session -->
          <div class="col-lg-4 col-md-6">
            <div class="form-group">
              <label for="session_id" class="mb-1">Session</label>
              <?php $pre = $preselect['session_id'] ?? ''; ?>
<select name="session_id" id="session_id" class="form-control">
  <?php foreach ($academic_session as $s): ?>
    <option value="<?= esc($s->session_id) ?>"
      <?= ((string)$s->session_id === (string)$pre) ? 'selected' : '' ?>>
      <?= esc($s->session_name) ?>
    </option>
  <?php endforeach; ?>
</select>
              <small class="form-text text-muted">Choose the academic session.</small>
            </div>
          </div>

          <!-- Section -->
          <div class="col-lg-4 col-md-6">
            <div class="form-group">
              <label for="section_id" class="mb-1">Section</label>
              <select class="form-control select2" name="section_id" id="section_id" data-placeholder="Select section">
                <option value=""></option>
                <?php foreach (($sectionsclassinfo ?? []) as $sec):
                  $row = is_array($sec) ? $sec : (array)$sec;
                  $clsSecId = $row['cls_sec_id']
                    ?? $row['cls_secid']
                    ?? $row['cls_secID']
                    ?? $row['section_id']
                    ?? $row['id']
                    ?? null;
                  $label = $row['sectionclassname']
                    ?? $row['section_class_name']
                    ?? $row['class_section_name']
                    ?? $row['section_name']
                    ?? $row['name']
                    ?? null;
                  if (!$clsSecId) { continue; }
                  if (!$label) { $label = 'Section ' . $clsSecId; }
                ?>
                  <option value="<?= esc($clsSecId) ?>"><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Pick a section to load its subjects.</small>
            </div>
          </div>

          <!-- Subject -->
          <div class="col-lg-3 col-md-6">
            <div class="form-group">
              <label for="subject_id" class="mb-1">Subject</label>
              <select name="subject_id" id="subject_id" class="form-control select2" data-placeholder="Select subject" disabled>
                <option value=""></option>
              </select>
              <small class="form-text text-muted">Select subject to load editors.</small>
            </div>
          </div>

          <!-- Sync -->
          <div class="col-lg-1 col-md-6">
            <div class="form-group">
              <label class="mb-1 d-block">Sync</label>
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="synch" name="synch" value="1">
                <label class="form-check-label" for="synch">All campuses</label>
              </div>
            </div>
          </div>
        </div>

        <!-- Selection summary (optional) -->
        <div class="mb-3">
          <span class="badge text-bg-light border me-1" id="badgeSession">Session: —</span>
          <span class="badge text-bg-light border me-1" id="badgeSection">Section: —</span>
          <span class="badge text-bg-light border" id="badgeSubject">Subject: —</span>
        </div>

        <!-- Loader + dynamic list -->
        <div class="position-relative">
          <div id="loader-1"
               class="d-none position-absolute w-100 h-100"
               style="top:0;left:0;background:rgba(255,255,255,.7);z-index:10;display:flex;align-items:center;justify-content:center;">
            <div class="text-center">
              <i class="fas fa-sync-alt fa-spin fa-2x mb-2"></i>
              <div class="small text-muted">Loading editors…</div>
            </div>
          </div>
          <div id="subjects_list" class="table-responsive"></div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button type="submit" id="submitBtn" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
          <button type="reset" class="btn btn-outline-secondary">Reset</button>
          <a href="javascript:history.back();" class="btn btn-outline-dark">Cancel</a>
        </div>

        <?= form_close(); ?>
      </div>
    </div>

  </div>
</section>

<!-- Select2 (use same working path as your other screens) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
(function($){
  'use strict';

  const CSRF_FIELD = '<?= esc(csrf_token()) ?>';
  const CSRF_HASH  = '<?= esc(csrf_hash()) ?>';

  // FIXED: Use the correct URL that matches your controller method
  const SELECT_SUBJECTS_BY_SECTION = '<?= site_url('admin/top_level_planning/getSubjectsBySection') ?>';
  const LOAD_EDITORS_URL           = '<?= site_url('admin/top_level_planning/selectSubjectsforTopLevelPlanning') ?>';

  function showLoader(show){ 
    $('#loader-1').toggleClass('d-none', !show); 
  }
  
  function setBadge($el, label, val){ 
    $el.text(label + (val ? (' ' + val) : ' —')); 
  }

  function enableSubject(enable){
    $('#subject_id').prop('disabled', !enable);
    if ($.fn.select2) {
      $('#subject_id').trigger('change.select2');
    }
  }

  $(function(){
    // Init Select2
    if ($.fn.select2){
      $('.select2').select2({
        width: '100%',
        allowClear: true,
        placeholder: function(){ return $(this).data('placeholder') || 'Select'; }
      });
    }

    // Session change
    $('#session_id').on('change', function(){
      const text = $(this).find('option:selected').text().trim();
      setBadge($('#badgeSession'), 'Session:', text || '—');
      $('#subjects_list').empty();
      $('#subject_id').empty().append('<option value=""></option>').trigger('change');
      enableSubject(false);
    });

    // Section change -> load subjects
    $('#section_id').on('change', function(){
      const sectionText = $(this).find('option:selected').text().trim();
      setBadge($('#badgeSection'), 'Section:', sectionText || '—');

      const section_id = $(this).val() || '';
      $('#subjects_list').empty();
      $('#subject_id').empty().append('<option value=""></option>').trigger('change');
      enableSubject(false);

      if (!section_id) {
        console.log('No section selected');
        return;
      }

      console.log('Loading subjects for section_id:', section_id);
      showLoader(true);
      
      $.ajax({
        url: SELECT_SUBJECTS_BY_SECTION,
        type: 'POST',
        data: {
          section_id: section_id,
          [CSRF_FIELD]: CSRF_HASH
        },
        dataType: 'html',
        success: function(res) {
          console.log('Subjects response length:', res ? res.length : 0);
          console.log('Subjects response:', res);
          
          if (res && res.trim() !== '') {
            $('#subject_id').html(res);
            enableSubject(true);
            
            const $options = $('#subject_id option');
            console.log('Number of subject options:', $options.length);
            
            if ($options.length <= 1) {
              toastr.warning('No subjects found for this section. Please assign subjects first.');
            } else {
              toastr.success('Subjects loaded successfully.');
            }
          } else {
            $('#subject_id').html('<option value=""></option><option value="" disabled>No subjects available</option>');
            toastr.error('No subjects found for this section.');
          }
        },
        error: function(xhr, status, error) {
          console.error('Error loading subjects:', {
            status: status,
            error: error,
            statusCode: xhr.status,
            responseText: xhr.responseText
          });
          
          let errorMsg = 'Failed to load subjects. ';
          if (xhr.status === 404) {
            errorMsg += 'Please check if the getSubjectsBySection method exists in your Top_level_planning controller.';
          } else if (xhr.status === 500) {
            errorMsg += 'Server error. Check PHP error logs.';
          } else {
            errorMsg += 'Please check if subjects are assigned to this section.';
          }
          
          toastr.error(errorMsg);
          $('#subject_id').html('<option value=""></option><option value="" disabled>Error loading subjects</option>');
        },
        complete: function() {
          showLoader(false);
        }
      });
    });

    // Subject change -> load editors
    $('#subject_id').on('change', function(){
      const subjectText = $(this).find('option:selected').text().trim();
      setBadge($('#badgeSubject'), 'Subject:', subjectText || '—');

      const payload = {
        section_id: $('#section_id').val() || '',
        subject_id: $('#subject_id').val() || '',
        session_id: $('#session_id').val() || '',
        [CSRF_FIELD]: CSRF_HASH
      };

      $('#subjects_list').empty();

      if (!payload.section_id){
        toastr.warning('Please select a Section first.');
        return;
      }
      if (!payload.subject_id){
        return;
      }
      if (!payload.session_id){
        toastr.warning('Please select a Session first.');
        return;
      }

      console.log('Loading editors for:', payload);
      showLoader(true);
      
      $.ajax({
        url: LOAD_EDITORS_URL,
        type: 'POST',
        data: payload,
        dataType: 'html',
        success: function(res){
          console.log('Editors response length:', res ? res.length : 0);
          
          if (res && res.indexOf('alert-danger') !== -1) {
            $('#subjects_list').html(res);
            toastr.error('Failed to load editors.');
          } else if (res && res.trim() !== '') {
            $('#subjects_list').html(res);
            
            if ($.fn.summernote){
              $('.editor').each(function(){
                if (!$(this).next('.note-editor').length){
                  $(this).summernote({
                    height: 150,
                    toolbar: [
                      ['style', ['style']],
                      ['font', ['bold', 'underline', 'clear']],
                      ['fontname', ['fontname']],
                      ['color', ['color']],
                      ['para', ['ul', 'ol', 'paragraph']],
                      ['table', ['table']],
                      ['insert', ['link']],
                      ['view', ['fullscreen', 'codeview']]
                    ]
                  });
                }
              });
            }
            
            toastr.success('Editors loaded successfully.');
          } else {
            $('#subjects_list').html('<div class="alert alert-warning">No terms found for this session.</div>');
            toastr.warning('No terms found for the selected session.');
          }
        },
        error: function(xhr, status, error){
          console.error('Error loading editors:', {
            status: status,
            error: error,
            statusCode: xhr.status,
            responseText: xhr.responseText
          });
          toastr.error('Failed to load editors. Please check the server logs.');
          $('#subjects_list').html('<div class="alert alert-danger">Failed to load editors. Please try again.</div>');
        },
        complete: function(){ 
          showLoader(false); 
        }
      });
    });

    // Form save
    $('#user-edit-form').on('submit', function(e){
      e.preventDefault();
      const $btn = $('#submitBtn');

      if ($.fn.summernote){
        $('.editor').each(function(){
          if ($(this).next('.note-editor').length){
            $(this).val($(this).summernote('code'));
          }
        });
      }

      $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving');

      $.ajax({
        url: this.action,
        type: 'POST',
        data: $(this).serialize()
      }).done(function(resp){
        let json = typeof resp === 'object' ? resp : JSON.parse(resp);
        if (json.success){
          toastr.success(json.msg || 'Saved successfully.');
        } else {
          toastr.error(json.msg || 'Unable to save.');
        }
      }).fail(function(){
        toastr.error('Request failed.');
      }).always(function(){
        $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
      });
    });

  });
})(jQuery);
</script>

<?= $this->endSection() ?>
