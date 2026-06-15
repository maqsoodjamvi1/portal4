<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Make Students Current',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Make Current', 'active' => true],
    ],
]) ?>


<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
 <div class="card-header pb-0">
        <ul class="nav nav-tabs card-header-tabs">
          <li class="nav-item"><a class="nav-link " href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>          
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Class Change</a></li>                    
          <li class="nav-item"><a class="nav-link  " href="<?= base_url('admin/students_bulk_info') ?>">Other Student Info</a></li>
          <li class="nav-item"><a class="nav-link  " href="<?= base_url('admin/students_bulk_parent_info') ?>">Parent Info</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students_bulk_make_current') ?>">Make Current</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulkcsv/addbulk') ?>">Entries through Excel</a></li>
        </ul>
      </div>
      <div class="card-header">
        <div class="row">
          <div class="col-lg-6 mb-2">
            <label for="student_search"><strong>Student</strong></label>
            <select class="form-control" id="student_search" style="width:100%"></select>
            <small class="text-muted">Type 2+ letters. Only inactive students are shown.</small>
          </div>
          <div class="col-lg-6 mb-2 d-flex align-items-end">
            <button type="button" id="reloadBtn" class="btn btn-primary ms-auto">Reload</button>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="table-sticky-wrap table-responsive">
          <table class="table table-sm table-striped mb-0" id="studentsTable">
            <thead>
              <tr>
                <th class="sticky-col" style="width:70px;">S.No</th>
                <th class="sticky-col-2">Student Name</th>
                <th>Reg No</th>
                <th>Status</th>
                <th class="text-end" style="width:140px;">Action</th>
              </tr>
            </thead>
            <tbody id="studentsTbody">
              <tr>
                <td colspan="5" class="text-center text-muted">Search to load an inactive student or click Reload to list all.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div id="loader-1" style="display:none;position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,0.7);">
        <div style="position:absolute;top:45%;left:50%;transform:translate(-50%,-50%);">
          <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
          <div>Loading...</div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Modal: Make Current -->
<div class="modal fade" id="makeCurrentModal" tabindex="-1" role="dialog" aria-labelledby="makeCurrentLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="makeCurrentForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="makeCurrentLabel">Make Current — <span id="mcStudentName"></span></h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="student_id" id="mcStudentId">
        <div class="form-group">
          <label for="mcClsSecId">Class / Section</label>
          <select class="form-control" name="cls_sec_id" id="mcClsSecId" required>
            <option value="">Select class/section...</option>
            <?php foreach ($classSections as $cs): ?>
              <option value="<?= (int)$cs['cls_sec_id'] ?>"><?= esc($cs['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="mcFee">Fee (monthly)</label>
          <input type="number" step="0.01" min="0" class="form-control" name="fee" id="mcFee" placeholder="e.g. 2500" required>
          <small class="text-muted">Will be stored on student (e.g. in <code>discounted_amount</code>).</small>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success" type="submit">Make current</button>
      </div>
    </form>
  </div>
</div>

<style>
  #studentsTable { width:100%; table-layout: fixed; border-collapse: separate; border-spacing: 0; --sno-w: 80px; }
  #studentsTable th, #studentsTable td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; background:#fff; }
  #studentsTable thead th { position: sticky; top: 0; z-index: 10; box-shadow: 0 1px 0 rgba(0,0,0,0.05); }
  #studentsTable th.sticky-col, #studentsTable td.sticky-col { position: sticky; left: 0; z-index: 6; background:#fff; width:var(--sno-w); max-width:var(--sno-w); min-width:var(--sno-w); }
  @media (min-width: 577px) {
    #studentsTable th.sticky-col-2, #studentsTable td.sticky-col-2 { position: sticky; left: var(--sno-w); z-index: 5; background:#fff; }
  }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function(){
  'use strict';

  const URLS = {
    DATA: "<?= base_url('admin/students_bulk_make_current/data') ?>",
    SEARCH: "<?= base_url('admin/students_bulk_make_current/search-by-name') ?>",
    MAKE: "<?= base_url('admin/students_bulk_make_current/make-current') ?>"
  };
  const CSRF_NAME = "<?= csrf_token() ?>";
  let   CSRF_HASH = "<?= csrf_hash() ?>";

  // Load all inactive
  function loadAll(){
    const payload = {}; payload[CSRF_NAME] = CSRF_HASH;
    $("#loader-1").show();
    $.post(URLS.DATA, payload, function(res, status, xhr){
      updateCsrf(xhr); $("#studentsTbody").html(res || emptyRow());
    }).fail(function(){
      $("#studentsTbody").html(errorRow('Failed to load data.'));
    }).always(function(){ $("#loader-1").hide(); });
  }
  function loadOne(studentId){
    const payload = {student_id: studentId}; payload[CSRF_NAME] = CSRF_HASH;
    $("#loader-1").show();
    $.post(URLS.DATA, payload, function(res, status, xhr){
      updateCsrf(xhr); $("#studentsTbody").html(res || emptyRow());
    }).fail(function(){
      $("#studentsTbody").html(errorRow('Failed to load data.'));
    }).always(function(){ $("#loader-1").hide(); });
  }
  function updateCsrf(xhr){
    const t = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
    if (t) CSRF_HASH = t;
  }
  function emptyRow(){ return '<tr><td colspan="5" class="text-center text-info">No inactive students found.</td></tr>'; }
  function errorRow(msg){ return '<tr><td colspan="5" class="text-center text-danger">'+(msg||'Error')+'</td></tr>'; }

  // Select2 search
  $('#student_search').select2({
    placeholder: 'Search inactive student by name',
    minimumInputLength: 2,
    width: 'resolve',
    ajax: {
      url: URLS.SEARCH,
      dataType: 'json',
      delay: 250,
      cache: true,
      data: function (params) {
        return { q: params.term, limit: 20 };
      },
      processResults: function (data) {
        return { results: data && data.results ? data.results : [] };
      }
    },
    templateResult: function (item) {
      if (item.loading) return item.text;
      const $c = $('<div>').text(item.text);
      if (item.badge) $c.append($('<small class="text-muted ms-2">').text(item.badge));
      return $c;
    },
    templateSelection: function (item) { return item.text || item.id; }
  });

  $('#student_search').on('select2:select', function (e) {
    const data = e.params.data || {};
    if (data && data.id) loadOne(data.id);
  });
  $('#student_search').on('select2:clear select2:unselect', function(){
    $('#studentsTbody').html(emptyRow());
  });

  // Make current modal
  $(document).on('click', '.makeCurrentBtn', function(){
    const sid = $(this).data('student-id');
    const sname = $(this).data('student-name') || 'Student';
    $('#mcStudentId').val(sid);
    $('#mcStudentName').text(sname);
    $('#mcClsSecId').val('');
    $('#mcFee').val('');
    $('#makeCurrentModal').modal('show');
  });

  $('#makeCurrentForm').on('submit', function(ev){
    ev.preventDefault();
    const fd = new FormData(this);
    fd.append(CSRF_NAME, CSRF_HASH);

    $("#loader-1").show();
    $.ajax({
      url: URLS.MAKE,
      type: 'POST',
      data: fd,
      contentType: false,
      processData: false,
      success: function(res, _s, xhr){
        updateCsrf(xhr);
        if (res && res.success) {
          $('#makeCurrentModal').modal('hide');
          window.toastr && toastr.success(res.msg || 'Done.');
          loadAll();
        } else {
          window.toastr && toastr.error((res && res.msg) || 'Failed.');
        }
      },
      error: function(){
        window.toastr && toastr.error('Request failed.');
      },
      complete: function(){ $("#loader-1").hide(); }
    });
  });

  $('#reloadBtn').on('click', loadAll);

  // Initial
  loadAll();
})();
</script>

<?= $this->endSection() ?>
