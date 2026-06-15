<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
// ---- Section stats (for the right-side legend) ----

$db        = \Config\Database::connect();
$campusId  = (int)($campus_id ?? session('member_campusid'));
$sessionId = (int)($session_id ?? session('member_sessionid'));

// ---- Class-section list (unchanged) ----
$secRows = $db->table('class_section cs')
    ->select('cs.cls_sec_id, cs.class_id, cs.section_id, c.class_name, s.section_name')
    ->join('classes c',  'c.class_id = cs.class_id',   'left')
    ->join('sections s', 's.section_id = cs.section_id','left')
    ->where('cs.campus_id', $campusId)
    ->where('cs.status', 1)
    ->orderBy('c.class_id', 'ASC')
    
    ->get()->getResultArray();

// ---- Counts per cls_sec_id from student_class (ACTIVE only) ----
// Use DISTINCT in case of accidental duplicates per student/session.
$cntRows = $db->table('student_class sc')
    ->select('sc.cls_sec_id, COUNT(DISTINCT sc.student_id) AS cnt', false)
    ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
    ->where('cs.campus_id', $campusId)
    ->where('cs.status', 1)
    ->where('sc.session_id', $sessionId)
    ->where('sc.status', 1)
    ->groupBy('sc.cls_sec_id')
    ->get()->getResultArray();

$cntMap = [];
foreach ($cntRows as $r) {
    $cntMap[(int)$r['cls_sec_id']] = (int)$r['cnt'];
}

// ---- Totals for the badges (from student_class) ----
// Active total (status=1)
$totalActive = (int) ($db->table('student_class sc')
    ->select('COUNT(DISTINCT sc.student_id) AS c', false)
    ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
    ->where('cs.campus_id', $campusId)
    ->where('cs.status', 1)
    ->where('sc.session_id', $sessionId)
    ->where('sc.status', 1)
    ->get()->getRow('c') ?? 0);

// All in session (regardless of sc.status) – if you want only active, copy the filter above
$totalAll = (int) ($db->table('student_class sc')
    ->select('COUNT(DISTINCT sc.student_id) AS c', false)
    ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'inner')
    ->where('cs.campus_id', $campusId)
    ->where('cs.status', 1)
    ->where('sc.session_id', $sessionId)
    ->get()->getRow('c') ?? 0);


  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
	if(isset($info)){
		$header = 'Edit Student Bulk';
		$id =  $info->student_id;
		$parent_id = $parentsinfo->parent_id;
		$reg_no =  $info->reg_no;
		$first_name =  $info->first_name;
		$last_name =  $info->last_name;
		$date_of_birth =  $info->date_of_birth;
		$gender =  $info->gender;
		$religion =  $parentsinfo->religion;
		$father_cnic =  $parentsinfo->father_cnicnew;
		$f_name =  $parentsinfo->f_name;
		$father_contact =  $parentsinfo->father_contact;
		$father_email =  $parentsinfo->father_email;
		$father_occupation =  $parentsinfo->father_occupation;
		$father_office_address =  $parentsinfo->father_office_address;
		$m_name  =  $parentsinfo->m_name;
		$mother_contact =  $parentsinfo->mother_contact;
    $whatsapp_contact =  $parentsinfo->whatsapp;
    $address_line1 =  $parentsinfo->address_line1;
		$previous_school =  $info->previous_school;
		$ps_city =  $info->ps_city;
    $city =  $parentsinfo->city;
		$hear_source =  $parentsinfo->hear_source;
		$date_of_admission =  $info->date_of_admission;
		$discounted_amount =  $info->discounted_amount;
		$emergency_contact_person =  $parentsinfo->emergency_contact_person;
		$emergency_contact =  $parentsinfo->emergency_contact;
    $emergency_address = $parentsinfo->a_address;
		$health_conditions =  $info->health_conditions;
		$major_injuries =  $info->major_injuries;
		$profile_photo =  $info->profile_photo;
		if($studentclassinfo){
			$section_id =  $studentclassinfo->cls_sec_id;
		}else{
			$section_id =  $info->class_id;
		}
        $session_id =  $info->session_id;
      	$status = intval($info->status);
		    $campus_id = $sessionData['campusid'];
				
		}else{
		$header = 'Add Student Bulk';
		$id = '';
		$parent_id = '';
		$reg_no = $reg_no;
		$first_name = '';
		$last_name = '';
		$date_of_birth = '';
		$gender = '';
		$religion = '';
		$father_cnic = '';
		$f_name = '';
		$father_contact = '';
		$father_email = '';
		$father_occupation = '';
		$father_office_address = '';
		$father_office_contact = '';
		$m_name = '';
		$classesfee = '';
		$m_last_name = '';
		$mother_contact = '';
    $whatsapp_contact = '';
  	$address_line1 = '';
		$city = '';
		$previous_school = '';
		$ps_city = '';
		$hear_source = '';
		$date_of_admission = '';
		$class_id = 0;
		$section_id = 0;
		$session_id = 0;
		$discounted_amount = '';
		$emergency_contact_person = '';
		$emergency_contact = '';
		$emergency_address = '';
		$health_conditions = '';
		$major_injuries = '';
		$profile_photo =  '';
		$status = 0;
		$campus_id = $sessionData['campusid'];
	}
?>
<script>
function checkfathercnic() {
 	var father_cnic = $('#father_cnic').val();
 	      $.ajax({
            url: 'admin.php?c=ajax&m=check_father_cinic',
            type: "POST",
            data:{father_cnic: father_cnic, },
            success:function(res){
			if(res){

			var sjson = $.parseJSON(res);
			//console.log(sjson.parent_id);
			
			   $("#parent_id").val(sjson.parent_id);
			   $("#religion").val(sjson.religion);
			   $("#f_name").val(sjson.f_name);
			   $("#father_contact").val(sjson.father_contact);
			   $("#father_email").val(sjson.father_email);
			   $("#father_occupation").val(sjson.father_occupation);
			   $("#father_office_address").val(sjson.father_office_address);	
			   $("#address_line1").val(sjson.address_line1);
			   $("#city").val(sjson.city);
			   $("#m_name").val(sjson.m_name);
			   $("#mother_contact").val(sjson.mother_contact);
         $("#whatsapp_contact").val(sjson.whatsapp);
			   $("#hear_source").val(sjson.hear_source);
			   $("#emergency_contact_person").val(sjson.emergency_contact_person);
			   $("#emergency_contact").val(sjson.emergency_contact);
			   $("#a_address").val(sjson.a_address);
			}
			}
  });

 }
</script>
<style type="text/css">	
	@media print{
   .noprint{
       display:none;
   }
}
.nav-pills-custom .nav-link.active{
	color: #fff !important;
}
</style>
<?= view('components/page_header', [
    'title' => 'Entries through Excel',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Entries through Excel', 'active' => true],
    ],
]) ?>

<!-- Main content -->
<section class="content"> 
  <div class="row">
    <div class="col-lg-12">
     <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
         <ul class="nav nav-tabs card-header-tabs">
          <li class="nav-item"><a class="nav-link " href="<?= base_url('admin/addbulkstudents/add') ?>">Student Names</a></li>          
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/studentsbulk') ?>">Class Change</a></li>                    
          <li class="nav-item"><a class="nav-link  " href="<?= base_url('admin/students_bulk_info') ?>">Other Student Info</a></li>
          <li class="nav-item"><a class="nav-link  " href="<?= base_url('admin/students_bulk_parent_info') ?>">Parent Info</a></li>
          <li class="nav-item"><a class="nav-link " href="<?= base_url('admin/students_bulk_make_current') ?>">Make Current</a></li>
          <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/studentsbulkcsv/addbulk') ?>">Entries through Excel</a></li>
        </ul>
        
       <div class="card-body">

  <!-- Top utility bar: sample, totals, quick search -->
  <div class="d-flex flex-wrap align-items-center mb-3">
    <a class="btn btn-primary me-3 mb-2" href="/uploads/addStudentSample_latest.csv">
      Download Sample CSV for Bulk Records
    </a>

    <div class="badge text-bg-success me-2 mb-2" style="font-size:14px;">
      Active students (this session): <strong id="totalActive"><?= (int)$totalActive ?></strong>
    </div>
    <div class="badge text-bg-secondary me-3 mb-2" style="font-size:14px;">
      Total students (this session): <strong id="totalAll"><?= (int)$totalAll ?></strong>
    </div>

    <div class="ms-auto mb-2">
      <input type="text" id="csSearch" class="form-control form-control-sm" placeholder="Search class/section/code…">
    </div>
  </div>

  <!-- Main two-column layout -->
  <div class="row">
    <!-- LEFT: Upload -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <strong>Upload CSV</strong>
        </div>
        <div class="card-body">
          <?= form_open_multipart(base_url('admin/studentsbulkcsv/import'), ['role' => 'form', 'id' => 'students-edit-form-basicinfo']) ?>
            <?= form_hidden('id', $id) ?>
            <?= form_hidden('campus_id', $campus_id) ?>

            <div class="form-group">
              <label class="mb-1">Choose CSV file</label>
              <input type="file" name="file" class="form-control-file" required>
              <small class="text-muted d-block">CSV must contain a <code>section_code</code> column with the Class-Section Code (cls_sec_id) from the legend.</small>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary">
              Upload Records
            </button>
            <div id="uploadStatus" class="mt-3"></div>
          <?= form_close();?>
        </div>
      </div>

      <!-- Optional: your audio stays here -->
      <div class="mt-3">
        <audio controls>
          <source src="audio/AddStudentBulk.m4a" type="audio/ogg">
          <source src="audio/AddStudentBulk.m4a" type="audio/mpeg">
          Your browser does not support the audio element.
        </audio>
      </div>
    </div>

    <!-- RIGHT: Class-Section Code legend with live counts -->
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light d-flex align-items-center">
          <strong>Class-Section Codes (Campus <?= (int)$campus_id ?>)</strong>
          <span class="ms-auto small text-muted">Use these codes in CSV <code>section_code</code></span>
        </div>
        <div class="card-body p-0">
          <div class="p-3 border-bottom">
            <label class="mb-1">Quick pick</label>
            <select id="csQuickPick" class="form-control form-control-sm">
              <option value="">— Select a Class-Section —</option>
              <?php foreach ($secRows as $r): 
                $code   = (int)$r['cls_sec_id'];
                $cname  = trim((string)$r['class_name']);
                $sname  = trim((string)$r['section_name']);
                $label  = $cname . ' – ' . $sname;
                $count  = (int)($cntMap[$code] ?? 0);
              ?>
              <option value="<?= $code ?>">
                <?= esc($label) ?>  —  Code: <?= $code ?>  —  Active: <?= $count ?>
              </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted d-block mt-1" id="csCopyHint" style="display:none;">
              Copied <span class="fw-bold" id="copiedCode"></span> to clipboard.
            </small>
          </div>

          <div class="table-responsive" style="max-height: 420px;">
            <table class="table table-sm table-hover mb-0" id="csLegendTable">
              <thead class="table-light">
                <tr>
                  <th style="width:35%;">Class</th>
                  <th style="width:35%;">Section</th>
                  <th style="width:15%;">Code</th>
                  <th style="width:15%;" class="text-end">Active</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($secRows as $r):
                $code  = (int)$r['cls_sec_id'];
                $cname = trim((string)$r['class_name']);
                $sname = trim((string)$r['section_name']);
                $cnt   = (int)($cntMap[$code] ?? 0);
              ?>
                <tr data-search="<?= esc(strtolower($cname.' '.$sname.' '.$code)) ?>">
                  <td><?= esc($cname) ?></td>
                  <td><?= esc($sname) ?></td>
                  <td>
                    <code class="copy-code" data-code="<?= $code ?>" style="cursor:pointer;"><?= $code ?></code>
                  </td>
                  <td class="text-end">
                    <span class="badge rounded-pill <?= $cnt > 0 ? 'text-bg-success' : 'text-bg-secondary' ?>">
                      <?= $cnt ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="p-2 border-top text-end small text-muted">
            Rows: <?= count($secRows) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</section>
<!-- /.content -->
<script>


 $('#students-edit-form-basicinfo').validate({ // <- attach '.validate()' to your form
            // Rules for form validation
            rules: {
                file: {
                    required: true
                },
            },
            // Messages for form validation
            messages: {
                file: {
                    required: 'Document Required to Upload'
                }
            },
           submitHandler: function (form) {
    var myData = new FormData($("#students-edit-form-basicinfo")[0]);

    swal({
        title: "Confirm to Upload Student in <?= esc($campusInfo->campus_name) ?>",
        text: "Update Student Information!",
        type: "warning",
        showCancelButton: true,
        closeOnConfirm: false,
        showLoaderOnConfirm: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Yes, Upload!"
    }, function () {
        $.ajax({
            url: "<?= base_url('admin/studentsbulkcsv/import') ?>", // CI4 route
            type: 'POST',
            data: myData,
            dataType: 'json',
            cache: false,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $('#loader').show();
                $("#submit").prop('disabled', true); // disable button
            },
            success: function (data) {
                $('#loader').hide();
                $("#submit").prop('disabled', false);

                if (data.type === 'success') {
                    swal("Done!", "It was successfully done!", "success");
                    notify_view(data.type, data.message);
                    $("html, body").animate({scrollTop: 0}, "slow");
                    $('#myModal').modal('hide');
                    location.href = '#/students?status=1';
                } else if (data.type === 'error') {
                    $("#status").html(data.message);
                    swal("Error!", "Please try again.", "error");
                }
            },
            error: function () {
                $('#loader').hide();
                $("#submit").prop('disabled', false);
                swal("Server Error", "AJAX failed. Please try again.", "error");
            }
        });
    });
}
  

  $('#students-edit-form-basicinfo').validate({
  rules: { file: { required: true } },
  messages: { file: { required: 'Document Required to Upload' } },
  submitHandler: function (form) {
    const myData = new FormData($("#students-edit-form-basicinfo")[0]);

    swal({
      title: "Confirm to Upload Student in <?= esc($campusInfo->campus_name ?? '') ?>",
      text:  "This will add/update students for the selected Class-Section Codes.",
      type:  "warning",
      showCancelButton: true,
      closeOnConfirm: false,
      showLoaderOnConfirm: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes, Upload!"
    }, function () {
      $.ajax({
        url: "<?= base_url('admin/studentsbulkcsv/import') ?>",
        type: 'POST',
        data: myData,
        dataType: 'json',
        cache: false,
        processData: false,
        contentType: false,
        success: function (data) {
          swal.close();
          if (data && data.type === 'success') {
            // user-friendly toast + refresh to update counts
            $('#uploadStatus').html(
              '<div class="alert alert-success mb-0">✅ ' +
                (data.message || 'Students uploaded successfully.') +
              '</div>'
            );
            // simplest reliable refresh to show updated counts
            setTimeout(function(){ location.reload(); }, 900);
          } else {
            $('#uploadStatus').html(
              '<div class="alert alert-danger mb-0">❌ ' +
                (data && data.message ? data.message : 'Upload failed. Please check your CSV and try again.') +
              '</div>'
            );
          }
        },
        error: function () {
          swal.close();
          $('#uploadStatus').html(
            '<div class="alert alert-danger mb-0">❌ Server error. Please try again.</div>'
          );
        }
      });
    });
  }
});

// Small UX helpers for the legend
(function(){
  // live filter
  $('#csSearch').on('input', function(){
    const q = $(this).val().toLowerCase();
    $('#csLegendTable tbody tr').each(function(){
      $(this).toggle($(this).data('search').indexOf(q) !== -1);
    });
  });

  // quick pick: copy the code
  $('#csQuickPick').on('change', function(){
    const code = $(this).val();
    if (!code) return;
    navigator.clipboard.writeText(code).then(function(){
      $('#copiedCode').text(code);
      $('#csCopyHint').fadeIn(120).delay(1200).fadeOut(180);
    });
  });

  // click-to-copy on code cells
  $(document).on('click', '.copy-code', function(){
    const code = $(this).data('code');
    navigator.clipboard.writeText(code).then(function(){
      $('#copiedCode').text(code);
      $('#csCopyHint').fadeIn(120).delay(1200).fadeOut(180);
    });
  });
})();




</script>

<?= $this->endSection() ?>