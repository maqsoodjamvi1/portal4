<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Add Test Results</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/test_results') ?>">Test Results</a></li>
          <li class="breadcrumb-item active">Add</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <h3 class="card-title">Test Details</h3>
    </div>

    <div class="card-body">

<?= form_open('admin/test_results/save', ['id' => 'testResultForm']) ?>
<?= csrf_field() ?>

<input type="hidden" name="session_id" value="<?= session('member_sessionid') ?>">
<input type="hidden" name="campus_id" value="<?= session('member_campusid') ?>">
<input type="hidden" id="sec_sub_id" name="sec_sub_id">
<input type="hidden" id="test_id" name="test_id">

<!-- NEW: evaluation mode + remarks toggle -->
<div class="form-row align-items-end">
  <div class="form-group col-md-4">
    <label for="cls_sec_id">Class Section</label>
    <select id="cls_sec_id" name="cls_sec_id" class="form-control">
      <option value="">Select</option>
      <?php foreach ($sectionsclassinfo as $row): ?>
        <option value="<?= $row['cls_sec_id'] ?>"><?= $row['class_name'] ?> - <?= $row['section_name'] ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group col-md-4">
    <label for="subject_id">Subject</label>
    <select id="subject_id" name="subject_id" class="form-control" disabled>
      <option value="">Select</option>
    </select>
  </div>

  <div class="form-group col-md-4">
    <label for="test_date">Test Date</label>
    <input type="date" class="form-control" id="test_date" name="test_date" required>
  </div>
</div>

<div class="form-row align-items-end">
  <div class="form-group col-md-3">
    <label for="test_marks">Total Marks</label>
    <input type="number" class="form-control" id="test_marks" name="test_marks" min="1" required>
  </div>
  <div class="form-group col-md-5">
    <label for="test_syllabus">Syllabus (optional)</label>
    <input type="text" class="form-control" id="test_syllabus" name="test_syllabus" placeholder="e.g., Unit 3: Fractions, Ex 3.1–3.3">
  </div>

  <!-- Show remarks toggle (hidden by default) -->
  <div class="form-group col-md-2">
    <label>&nbsp;</label>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" id="toggle_remarks" />
      <label class="form-check-label" for="toggle_remarks"> Show Remarks</label>
    </div>
  </div>

  <!-- Evaluation mode -->
  <div class="form-group col-md-2">
    <label>Evaluation</label>
    <select id="evaluation_mode" name="evaluation_mode" class="form-control">
      <option value="marks" selected>Marks</option>
      <option value="options">Pre-defined options</option>
    </select>
  </div>
</div>

<div id="testsList" class="mb-3"></div>
<!-- (keep your existing) -->
<div id="loader" class="text-center" style="display:none;">
  <i class="fas fa-sync-alt fa-spin fa-2x"></i> Loading...
</div>
<!-- NEW: options builder (only when evaluation_mode = options) -->
<div id="options-builder" class="border rounded p-3 mb-3" style="display:none;">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <strong>Define evaluation options (label → value)</strong>
    <button type="button" id="addOptionRow" class="btn btn-sm btn-outline-primary">Add option</button>
  </div>

  <div id="optionsRows">
    <!-- rows inserted by JS -->
  </div>

  



<div id="resultsContainer" class="mt-3"></div>

  <div class="text-muted" style="font-size:.9rem;">
    The largest value becomes this test’s <em>Total Marks</em> automatically (you can still override).
  </div>

  <!-- this will carry options to server if you want to persist later -->
  <input type="hidden" id="options_json" name="options_json" value="[]">
</div>
<!-- Students List -->
<div id="students-list-container" style="display:none;">

  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Students List</h5>

    <div class="form-inline">
      <label for="order_by" class="mr-2 mb-0">Order by:</label>
      <select id="order_by" class="form-control form-control-sm">
        <option value="name">Name (A–Z)</option>
        <option value="student_id">Student ID</option>
      </select>
    </div>
  </div>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th style="width:60px;">#</th>
        <th>Name</th>
        <th style="width:160px;" class="th-marks">Obt Marks</th>
        <th style="width:220px; display:none;" class="th-remarks">Remarks</th>
      </tr>
    </thead>
    <tbody id="students-list-body"></tbody>
  </table>
</div>

<button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
<a href="<?= base_url('admin/test_results') ?>" class="btn btn-secondary">
  <i class="fas fa-times"></i> Cancel
</a>

<?= form_close() ?>

    </div>
  </div>
</section>

<script>
$(function () {
  const $classSection   = $('#cls_sec_id');
  const $subject        = $('#subject_id');
  const $studentsWrap   = $('#students-list-container');
  const $studentsBody   = $('#students-list-body');
  const $submitBtn      = $('#submitBtn');
  const $testDate       = $('#test_date');
  const $testMarks      = $('#test_marks');
  const $testSyllabus   = $('#test_syllabus');
  const $secSubId       = $('#sec_sub_id');
  const $testId         = $('#test_id');

  const $evalMode       = $('#evaluation_mode');
  const $optionsBuilder = $('#options-builder');
  const $optionsRows    = $('#optionsRows');
  const $optionsJson    = $('#options_json');

  const $toggleRemarks  = $('#toggle_remarks');
  const $orderBy        = $('#order_by'); 

  // today default
  $('#test_date').val(new Date().toISOString().split('T')[0]);

  // remarks column toggle
  $toggleRemarks.on('change', function(){
    const show = $(this).is(':checked');
    $('.th-remarks').toggle(show);
    $('.td-remarks').toggle(show);
  });

  // switch evaluation mode
  $evalMode.on('change', function () {
    const mode = $(this).val();
    if (mode === 'options') {
      $optionsBuilder.show();
      ensureAtLeastOneOptionRow();
      rebuildStudentInputs(); // flip to selects
      syncTotalMarksFromOptions();
    } else {
      $optionsBuilder.hide();
      rebuildStudentInputs(); // flip to numeric inputs
    }
  });

  // add / remove option rows
  $('#addOptionRow').on('click', function(){
    addOptionRow('', '');
    syncOptionsJson();
    syncTotalMarksFromOptions();
    rebuildStudentInputs();
  });

  $optionsRows.on('input', '.opt-label, .opt-value', function(){
    syncOptionsJson();
    syncTotalMarksFromOptions();
    rebuildStudentInputs(); // to refresh dropdown lists
  });

  $optionsRows.on('click', '.remove-option', function(){
    $(this).closest('.opt-row').remove();
    if ($optionsRows.children().length === 0) addOptionRow('Prepared', 10);
    syncOptionsJson();
    syncTotalMarksFromOptions();
    rebuildStudentInputs();
  });

  function addOptionRow(label, value) {
    const row = `
      <div class="row opt-row g-2 mb-2">
        <div class="col-md-6">
          <input type="text" class="form-control opt-label" placeholder="Label (e.g., Prepared)" value="${label}">
        </div>
        <div class="col-md-4">
          <input type="number" class="form-control opt-value" placeholder="Value (e.g., 10)" value="${value}" min="0">
        </div>
        <div class="col-md-2 d-grid">
          <button type="button" class="btn btn-outline-danger remove-option">Remove</button>
        </div>
      </div>`;
    $optionsRows.append(row);
  }

  function ensureAtLeastOneOptionRow() {
    if ($optionsRows.children().length === 0) {
      addOptionRow('Prepared', 10);
      addOptionRow('Not Prepared', 0);
    }
    syncOptionsJson();
  }

  function getOptions() {
    const list = [];
    $optionsRows.find('.opt-row').each(function(){
      const label = $(this).find('.opt-label').val().trim();
      const value = parseFloat($(this).find('.opt-value').val());
      if (label !== '' && !isNaN(value)) list.push({ label, value });
    });
    // sort by value (asc) for a nicer dropdown
    list.sort((a,b) => a.value - b.value);
    return list;
  }

  function syncOptionsJson() {
    $optionsJson.val(JSON.stringify(getOptions()));
  }

  function syncTotalMarksFromOptions() {
    if ($evalMode.val() !== 'options') return;
    const opts = getOptions();
    if (!opts.length) return;
    const max = Math.max.apply(null, opts.map(o => o.value));
    if (!isFinite(max)) return;
    // only auto-fill if empty or smaller than max
    const current = parseFloat($testMarks.val() || '0');
    if (!current || current < max) $testMarks.val(max);
  }

  // subject loading
  $classSection.on('change', function () {
    resetSubjects();
    resetStudents();
    const clsSecId = $(this).val();
    if (clsSecId) loadSubjects(clsSecId);
  });

  // whenever subject or date changes => load students
  $subject.add($testDate).on('change', function () {
    // do not wipe teacher inputs here; only refresh student list
    const clsSecId = $classSection.val();
    const subjectId = $subject.val();
    const testDate  = $testDate.val();
    if (clsSecId && subjectId && testDate) {
      loadStudents(clsSecId, subjectId, testDate);
    } else {
      resetStudents();
    }
  });

  $orderBy.on('change', function () {
  const clsSecId = $classSection.val();
  const subjectId = $subject.val();
  const testDate  = $testDate.val();

  if (clsSecId && subjectId && testDate) {
    loadStudents(clsSecId, subjectId, testDate, $(this).val());
  }
});

  function loadSubjects(clsSecId) {
    $.ajax({
      url: "<?= base_url('admin/test_results/get_subjects') ?>",
      type: "POST",
      data: { cls_sec_id: clsSecId, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
      dataType: 'json',
      success: function (response) {
        if (response.success && response.subjects.length > 0) {
          let options = '<option value="">Select Subject</option>';
          response.subjects.forEach(s => options += `<option value="${s.sid}">${s.subject_name}</option>`);
          $subject.html(options).prop('disabled', false);
        } else {
          $subject.html('<option value="">No subjects found</option>').prop('disabled', true);
        }
      },
      error: function () {
        alert('Failed to load subjects.');
        $subject.html('<option value="">Error loading</option>').prop('disabled', true);
      }
    });
  }

  function loadStudents(clsSecId, subjectId, testDate, orderBy) {
  orderBy = orderBy || $orderBy.val() || 'name';

  $.ajax({
    url: "<?= base_url('admin/test_results/get_students') ?>",
    type: "POST",
    data: {
      cls_sec_id: clsSecId,
      subject_id: subjectId,
      test_date:  testDate,
      session_id: $('input[name="session_id"]').val(),
      campus_id:  $('input[name="campus_id"]').val(),
      order_by:   orderBy, // <-- NEW
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    },
    dataType: 'json',
    beforeSend: function () {
      $studentsBody.html('<tr><td colspan="4" class="text-center">Loading students...</td></tr>');
      $studentsWrap.show();
      $submitBtn.prop('disabled', true);
    },
    success: function (res) {
      if (res.success && Array.isArray(res.students)) {
        $secSubId.val(res.sec_sub_id || '');
        $testId.val(res.test_id || '');
        if (res.test_marks)   $testMarks.val(res.test_marks);
        if (res.syllabus)     $testSyllabus.val(res.syllabus);

        buildStudentRows(res.students);
        $submitBtn.prop('disabled', false);
      } else {
        $studentsBody.html('<tr><td colspan="4" class="text-center text-warning">No students found.</td></tr>');
        $submitBtn.prop('disabled', true);
      }
    },
    error: function () {
      $studentsBody.html('<tr><td colspan="4" class="text-center text-danger">Failed to load students.</td></tr>');
      $submitBtn.prop('disabled', true);
    }
  });
}

  function buildStudentRows(students) {
  const mode = $evalMode.val();
  const showRemarks = $toggleRemarks.is(':checked');
  let rows = '';

  students.forEach((st, idx) => {
    // use provided full name if present (from rebuild), else combine first/last
    const fullName = (st.student_name && st.student_name.trim().length)
      ? st.student_name.trim()
      : (`${st.first_name ?? ''} ${st.last_name ?? ''}`).trim();
    const existing = (st.marks !== null && st.marks !== undefined && st.marks !== '') ? st.marks : '';

    rows += `<tr>
      <td>${idx + 1}</td>

      <td class="td-name">
        <span class="std-name">${fullName}</span>
        <input type="hidden" name="student_id[]" value="${st.student_id}">
        <!-- keep a hidden copy so rebuilds never lose it -->
        <input type="hidden" name="student_name[]" value="${fullName}">
      </td>`;

    if (mode === 'marks') {
      rows += `<td class="td-marks">
                <input type="number" class="form-control obt-marks" name="obtained_marks[]" value="${existing}" min="0">
              </td>`;
    } else {
      const opts = getOptions();
      const optionsHtml = opts.map(o => {
        const sel = (existing !== '' && Number(existing) === Number(o.value)) ? 'selected' : '';
        return `<option value="${o.value}" ${sel}>${o.label} (${o.value})</option>`;
      }).join('');

      rows += `<td class="td-marks">
                <select class="form-control obt-select">${optionsHtml}</select>
                <input type="hidden" class="obt-marks" name="obtained_marks[]" value="${existing}">
              </td>`;
    }

    rows += `<td class="td-remarks" style="display:${showRemarks ? '' : 'none'};">
               <input type="text" class="form-control" name="remarks[]" value="${st.remarks || ''}" placeholder="Remarks">
             </td>
    </tr>`;
  });

  $studentsBody.html(rows);

  if ($evalMode.val() === 'options') {
    // avoid stacking handlers across rebuilds
    $studentsBody.off('change', '.obt-select').on('change', '.obt-select', function(){
      $(this).closest('td').find('.obt-marks').val($(this).val());
    });
  }
}

function rebuildStudentInputs() {
  const current = [];
  $studentsBody.find('tr').each(function(){
    const sid  = $(this).find('input[name="student_id[]"]').val();
    const name = $(this).find('input[name="student_name[]"]').val() 
              || $(this).find('.std-name').text().trim();
    const mark = $(this).find('.obt-marks').val();
    const rem  = $(this).find('input[name="remarks[]"]').val();
    if (sid) current.push({ sid, name, mark, rem });
  });

  if (!current.length) return;

  const students = current.map(c => ({
    student_id: c.sid,
    student_name: c.name,   // <- keep name
    marks: c.mark,
    remarks: c.rem
  }));

  buildStudentRows(students);
}

  function resetStudents() {
    $studentsBody.html('');
    $studentsWrap.hide();
    $submitBtn.prop('disabled', true);
  }

  function resetSubjects() {
    $subject.html('<option value="">Select Subject</option>').prop('disabled', true);
  }

  // --- submit via AJAX (same as you had, just intact) ---
  $('#testResultForm').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn  = $('#submitBtn');

    // ensure options_json is up-to-date
    if ($evalMode.val() === 'options') syncOptionsJson();

    $btn.prop('disabled', true).text('Saving...');

    $.ajax({
      url: $form.attr('action'),
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json',
      success: function (res, status, xhr) {
        const newHash = xhr.getResponseHeader('X-CSRF-HASH') || (res && res.csrfHash);
        if (newHash) $('input[name="<?= csrf_token() ?>"]').val(newHash);

        if (res && res.success) {
          alert(res.msg || 'Saved successfully.');
        } else {
          alert((res && res.msg) ? res.msg : 'Save failed.');
        }
      },
      error: function (xhr) {
        alert('Error ' + xhr.status + ': could not save.');
      },
      complete: function () {
        $btn.prop('disabled', false).text('Save');
      }
    });
  });
});



(function(){
  const $form   = $('#resultFilterForm');
  const $loader = $('#loader');
  const $cont   = $('#resultsContainer');
  const $btn    = $('#btnView');
  const $tests  = $('#testsList');

  function setLoading(on){
    if(on){
      $loader.show();
      $btn.prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin"></i> Loading');
    } else {
      $loader.hide();
      $btn.prop('disabled', false).html('<i class="fas fa-eye"></i> View Results');
    }
  }

  function fetchCards(){
    setLoading(true);
    $cont.empty();

    $.ajax({
      url: "<?= base_url('admin/test_results/data') ?>",
      type: "POST",
      data: $form.serialize(),
      success: function(res){
        $cont.html(res);
        setLoading(false);
      },
      error: function(xhr){
        $cont.html('<div class="alert alert-danger">Error loading results ('+xhr.status+').</div>');
        setLoading(false);
      }
    });
  }

  // NEW: fetch tests list (compact table under filter)
 function fetchTests(){
    const payload = {
      cls_sec_id : $('#cls_sec_id').val() || '',
      subject_id : $('#subject_id').val() || '',
      start_date : $('#start_date').val() || '',   // ok if you don't have these inputs
      end_date   : $('#end_date').val()   || '',
      "<?= csrf_token() ?>": $('input[name="<?= csrf_token() ?>"]').val()
    };

    if (!payload.cls_sec_id) {
      $tests.html('<div class="alert alert-info mb-2">Select a class section to see tests.</div>');
      return;
    }

    $tests.html('<div class="text-muted small"><i class="fas fa-sync-alt fa-spin"></i> Loading tests…</div>');

    $.ajax({
      url : "<?= site_url('admin/test_results/list-tests') ?>", // matches your route
      type: "POST",
      data: payload,
      success: function(html){
        $tests.html(html);
        // refresh CSRF if server returned it in header (optional)
        const newHash = this.getResponseHeader && this.getResponseHeader('X-CSRF-HASH');
        if (newHash) $('input[name="<?= csrf_token() ?>"]').val(newHash);
      },
      error: function(xhr){
        $tests.html('<div class="alert alert-danger">Failed to load tests ('+xhr.status+').</div>');
      }
    });
  }

  // Load whenever class/subject/dates change
  $('#cls_sec_id, #subject_id, #start_date, #end_date').on('change', fetchTests);

  // Also load once when the page has a class preselected
  if ($('#cls_sec_id').val()) fetchTests();

  // Delete handler (kept simple)
  $(document).on('click', '.js-del-test', function(){
    const id = $(this).closest('tr').data('test-id');
    if (!id) return;
    if (!confirm('Delete this test and its marks? This cannot be undone.')) return;

    $.post("<?= site_url('admin/test_results/delete-test') ?>", {
      test_id: id,
      "<?= csrf_token() ?>": $('input[name="<?= csrf_token() ?>"]').val()
    }, function(resp){
      if (resp && resp.success) {
        fetchTests();
      } else {
        alert((resp && resp.message) ? resp.message : 'Delete failed.');
      }
    }, 'json').fail(function(){
      alert('Delete failed (network).');
    });
  });

  // Submit (your existing)
  $form.on('submit', function(e){
    e.preventDefault();
    if(!$('#cls_sec_id').val()){
      alert('Please select a Class Section.');
      return;
    }
    // load both: tests list + student/cards
    fetchTests();
    fetchCards();
  });

  // Also update the tests list immediately when class/subject changes
  $('#cls_sec_id, #subject_id, #start_date, #end_date').on('change', function(){
    if ($('#cls_sec_id').val()){
      fetchTests();
    } else {
      $('#testsList').empty();
    }
  });

  // Reset filters
  $('#btnReset').on('click', function(){
    $('#cls_sec_id').val('');
    $('#start_date').val('');
    $('#end_date').val('');
    $('#subject_id').val('');
    $('#show_percentage').prop('checked', false);
    $cont.empty();
    $tests.empty();
  });

  // Print
  $('#btnPrint').on('click', function(){ window.print(); });
})();
</script>


<style type="text/css">
  /* Compact tests table under the filter */
#testsTable.table {
  font-size: .9rem;
}
#testsTable.table td, #testsTable.table th {
  padding: .35rem .5rem;
}
#testsList .card { border: 1px solid #e5e7eb; }
#testsList .card-body { padding: .5rem .6rem !important; }
</style>

<?= $this->endSection() ?>
