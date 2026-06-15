<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info) ){
		$header = 'Edit Students Results';
		$id = $info->student_id;
		$class_id = $info->class_id;
		$subject_id = $info->sub_id;
		$obtained_marks = $info->obtained_marks;
		$total_marks = $info->Total_marks;
		$campus_id = $sessionData['campusid'];
		$session_id = $sessionData['sessionid'];

	}else{
		$header = 'Add Students Results';
		$id = '';
		$class_id = '';
		$subject_id = '';
		$obtained_marks = 0;
		$total_marks = 0;
		$campus_id = $sessionData['campusid'];
		$session_id = $sessionData['sessionid'];
	}
?>
<?= view('components/page_header', [
    'title' => 'Students Subject Results',
    'icon' => 'fas fa-book-open',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Subject Results', 'active' => true],
    ],
]) ?>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
       	<div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs">
		<!-- <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students_results_compilation/add') ?>"> Compile Results </a></li> -->
	    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students-results-card') ?>">View Results Cards</a></li>	
          <?php if($id == ''){ ?>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/students-results/add') ?>"><?php echo $header;?></a></li>
          <?php }else{ ?>
          <li class="nav-item"><a class="nav-link active" href="<?php echo base_url('admin/students-results/edit?id=') . $id;?>"><?php echo $header;?></a></li>
          <?php } ?>
        </ul>
        <div class="card-body">
        <div class="tab-content">
		<?php
			echo form_open( base_url('admin/students-subject-results/save'), 'role="form" id="user-edit-form"');
			echo form_hidden('id', (string)$id);
		?>
		<input type="hidden" value="<?= (int)$session_id; ?>" name="session_id" id="session_id" class="form-control">
		 <div class="row no-print">
		 
		<input type="hidden" name="campus_id" id="campus_id" value="<?php echo $campus_id; ?>" />
		
          <div class="col-12 col-md-6 col-lg-4 mb-3 mb-lg-0">
            <div class="form-group mb-0">
	              <label for="class">Classes</label>
	             <select class="form-control select2" name="cls_sec_id" id="cls_sec_id">
  <option value="0">Select Section</option>
  <?php if (isset($sectionsclassinfo)) {
        foreach ($sectionsclassinfo as $secionvalue) { ?>
    <!-- BEFORE: value="<?= $secionvalue['section_id']; ?>" -->
    <option value="<?= $secionvalue['cls_sec_id']; ?>">
      <?= $secionvalue['sectionclassname']; ?>
    </option>
  <?php } } ?>
</select>
	            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-4 mb-3 mb-lg-0">
          	<div class="form-group mb-0">
          		<label>Subjects</label>
          		<select class="form-control" name="sub_id" id="sub_id">
          			
          		</select>
          	</div>
          </div>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group mb-0">
              <label for="sort_order">List order</label>
              <select class="form-control" id="sort_order" name="sort_order">
                <option value="name" selected>Student name (A-Z)</option>
                <option value="reg_no">Registration number</option>
              </select>
            </div>
          </div>
		<!--   <div class="col-lg-1">
		   <div class="form-group">
		    <button type="button" onclick="getstudents();" class="btn btn-primary" style="margin-top: 18px;line-height: 10px;height: 25px;">View</button>
          </div>
		  </div> -->
		  <!--  <div class="col-lg-1 float-end">
		   <div class="form-group">
		    <button type="button" onclick="printout();" class="btn btn-primary" style="margin-top: 18px;line-height: 10px;height: 25px;">Print</button>
          </div>
		  </div> -->
		  </div>
		  <div class="row mx-0">          
		  <div class="col-12 px-0 px-sm-2">
         <div id="students_list_container" class="students-subject-results-list"></div>
		 </div>
		 
		  </div>
		  </div>
		<?php echo form_close();?> 
 </div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- /.content -->

<script>
(function () {
  // ===== Helpers =====
  const SAVE_MARK_URL   = "<?= base_url('admin/students-subject-results/save-mark') ?>";
  const GET_STUDENTS_URL= "<?= base_url('admin/students-subject-results/get-students') ?>";
  const GET_SUBJECTS_URL= "<?= base_url('admin/students-subject-results/select-section-subject-by-section') ?>";

  // CSRF (CI4)
  const CSRF_NAME = "<?= csrf_token() ?>";
  const CSRF_HASH = "<?= csrf_hash() ?>";
  function addCsrf(d){ if (CSRF_NAME && CSRF_HASH) d[CSRF_NAME]=CSRF_HASH; return d; }

  // Small, unobtrusive UI feedback
  function okBlink($el){ $el.addClass('is-valid'); setTimeout(()=> $el.removeClass('is-valid'), 800); }
  function errBlink($el){ $el.addClass('is-invalid'); setTimeout(()=> $el.removeClass('is-invalid'), 1200); }

  // Build subject_id -> sec_sub_id map from hidden inputs you render in <th> headers:
  //   <input type="hidden" name="sec_sub_id[<subject_id>]" value="<sec_sub_id>">
  function buildSecSubMap($scope){
    const map = {};
    ($scope || $(document)).find("input[name^='sec_sub_id[']").each(function(){
      const m = this.name.match(/^sec_sub_id\[(\d+)\]$/);
      if (m) map[m[1]] = Number(this.value || 0);
    });
    return map;
  }

  // Replace your autosave binding with this:
function bindAutoSave($scope){
  const $container = $scope || $(document);

  // remove any prior .mark namespace handlers (idempotent)
  $container.off('.mark');

  // debounce per-input using element data
  $container.on('blur.mark', '.mark-input', function(){
    const $in = $(this);
    clearTimeout($in.data('timer'));
    const t = setTimeout(() => saveOne($in), 60);
    $in.data('timer', t);
  });

  // optional: mark dirty on typing, but save only on blur
  $container.on('input.mark', '.mark-input', function(){
    $(this).data('dirty', true);
  });
}

function saveOne($in){
  // only save if value actually changed since last successful save
  const val = $in.val();
  const last = $in.data('last-saved');
  if (!$in.data('dirty') && last !== undefined && String(val) === String(last)) return;

  const ids = (name => {
    const m = (name||'').match(/^obtained_marks\[(\d+)\]\[(\d+)\]$/);
    return m ? { studentId:+m[1], subjectId:+m[2] } : null;
  })($in.attr('name'));

  if (!ids) return;

  const sec_sub_id = Number($in.data('sec-sub-id') || 0)
    || Number($("input[name='sec_sub_id["+ids.subjectId+"]']").val() || 0);

  const payload = addCsrf({
    student_id: ids.studentId,
    cls_sec_id: Number($('#cls_sec_id').val() || 0),
    sec_sub_id,
    obtained_marks: val,
    session_id: $('#session_id').val(),
    campus_id:  $('#campus_id').val()
  });

  if (!payload.cls_sec_id || !payload.sec_sub_id) return;

  // prevent overlapping saves for this input
  if ($in.data('saving')) { $in.data('queued', true); return; }
  $in.data('saving', true);

  $in.addClass('is-loading');
  $.post("<?= base_url('admin/students-subject-results/save-mark') ?>", payload)
    .done(function(res){
      if (res && res.success) {
        $in.data('last-saved', val).data('dirty', false);
        okBlink($in);
        // Optional: if you return fresh CSRF in response, update it here.
        // if (res.csrf) { window.CSRF_NAME = res.csrf.name; window.CSRF_HASH = res.csrf.hash; }
      } else {
        errBlink($in);
      }
    })
    .fail(function(){ errBlink($in); })
    .always(function(){
      $in.removeClass('is-loading');
      $in.data('saving', false);
      if ($in.data('queued')) {
        $in.data('queued', false);
        saveOne($in); // run once more with latest value
      }
    });
}

  // ===== Your existing dropdown flows (kept), but no exam picker needed =====
  function loadStudentsList(){
    const session_id = $('#session_id').val();
    const campus_id  = $('#campus_id').val();
    const cls_sec_id = $('#cls_sec_id').val();
    const sub_id     = $('#sub_id').val();
    const sort_order = $('#sort_order').val() || 'name';

    if (!cls_sec_id || cls_sec_id === '0' || !sub_id || sub_id === '') {
      $('#students_list_container').empty();
      return;
    }

    $.post(GET_STUDENTS_URL, addCsrf({
      session_id,
      campus_id,
      cls_sec_id,
      sub_id,
      sort_order
    }))
      .done(function(res){
        $('#students_list_container').html(res);
        bindAutoSave($('#students_list_container'));
      });
  }

$("#cls_sec_id").on('change', function(){
  const cls_sec_id = $('#cls_sec_id').val();
  const campus_id  = $('#campus_id').val();
  const session_id = $('#session_id').val();

  $("#sub_id").html('<option>Loading…</option>');
  $('#students_list_container').empty();

  $.ajax({
    type: 'POST',
    url: "<?= base_url('admin/students-subject-results/select-section-subject-by-section') ?>",
    dataType: 'json',
    data: {
      "<?= csrf_token() ?>": "<?= csrf_hash() ?>",
      cls_sec_id,
      campus_id,
      session_id
    },
    success: function(res){
      $("#sub_id").html(res.html);
      console.log('meta', res.meta); // should now show real campus_id, session_id, and resolved eid
    },
    error: function(xhr){ console.warn('Subjects load failed', xhr.status); }
  });
});

  // When subject changes → fetch students grid (server will resolve active exam)
  $("#sub_id").on('change', function(){
    loadStudentsList();
  });

  $('#sort_order').on('change', function(){
    loadStudentsList();
  });

  // Initial bind (in case markup is already present)
  bindAutoSave($("#students_list_container"));
})();
</script>

<style>
/* Small visual hints (optional) */
.mark-input.is-loading { background-image: linear-gradient(90deg,#f7f7f7 25%,#efefef 37%,#f7f7f7 63%); background-size: 400% 100%; animation: shimmer 1s infinite linear; }
@keyframes shimmer { 0%{background-position:100% 0} 100%{background-position:0 0} }
.mark-input.is-valid   { box-shadow: 0 0 0 .18rem rgba(40,167,69,.25); }
.mark-input.is-invalid { box-shadow: 0 0 0 .18rem rgba(220,53,69,.25); }
</style>

<?= $this->endSection() ?>