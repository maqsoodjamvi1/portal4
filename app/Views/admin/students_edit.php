<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<?php
  $status = ''; 
  if(!empty($_GET['status'])){
    $status = $_GET['status']; 
  }

  $isEdit = isset($info);

  if ($isEdit) {
      $header      = 'Edit Student';
      $id          = (int) $info->student_id;
      $parent_id   = $parentsinfo->parent_id ?? 0;
      $reg_no      = $info->reg_no ?? '';
      $first_name  = $info->first_name ?? '';
      $last_name   = $info->last_name ?? '';
      $father_cnic = $parentsinfo->father_cnic ?? '';
      $f_name      = $parentsinfo->f_name ?? '';

      $m_name             = $parentsinfo->m_name ?? '';
      $mother_contact     = $parentsinfo->mother_contact ?? '';
      $whatsapp_contact   = $parentsinfo->whatsapp ?? '';
      $address_line1      = $parentsinfo->address_line1 ?? '';
      $previous_school    = $info->previous_school ?? '';
      $ps_city            = $info->ps_city ?? '';
      $city               = $parentsinfo->city ?? '';
      $hear_source        = $parentsinfo->hear_source ?? '';
      $date_of_admission  = $info->date_of_admission ?? '';
      $date_of_birth      = $info->date_of_birth ?? '';
      $caste              = $parentsinfo->caste ?? '';
      $gr_no              = $info->gr_no ?? '';
      $gr_date            = $info->gr_date ?? '';
      $discounted_amount  = $info->discounted_amount ?? 0;
      $transport_discount = $info->transport_discount ?? 0;
      $emergency_contact_person = $parentsinfo->emergency_contact_person ?? '';
      $emergency_contact  = $parentsinfo->emergency_contact ?? '';
      $emergency_address  = $parentsinfo->a_address ?? '';
      $health_conditions  = $info->health_conditions ?? '';
      $major_injuries     = $info->major_injuries ?? '';
      $fee_plan           = $info->fee_plan ?? '';
      $profile_photo      = $info->profile_photo ?? '';

      if (!empty($studentclassinfo)) {
          $section_id = (int) $studentclassinfo->cls_sec_id;
      } else {
          $section_id = (int) ($info->class_id ?? 0);
      }

      $session_id  = (int) ($info->session_id ?? 0);
      $status      = (int) ($info->status ?? 0);
      $campus_id   = $sessionData['campusid'] ?? 0;
      $student_cnic= $info->std_cnic ?? '';
  } else {
      $header = 'Add Student';
      $id = 0;
      $parent_id = 0;
      $reg_no = $reg_no ?? '';
      $first_name = '';

      $father_cnic = '';
      $f_name = '';

      $father_office_contact = '';
      $m_name = '';
      $classesfee = 0;
      $transportfee = 0;
      $m_last_name = '';
      $mother_contact = '';
      $whatsapp_contact = '';
      $address_line1 = '';
      $city = '';
      $previous_school = '';
      $ps_city = '';
      $hear_source = '';
      $date_of_admission = '';
      $date_of_birth = '';
      $caste = '';
      $gr_no = '';
      $gr_date = '';
      $class_id = 0;
      $section_id = 0;
      $session_id = 0;
      $discounted_amount = 0;
      $transport_discount = 0;
      $emergency_contact_person = '';
      $emergency_contact = '';
      $emergency_address = '';
      $health_conditions = '';
      $major_injuries = '';
      $profile_photo = '';
      $fee_plan = '';
      $status = 0;
      $student_cnic = '';
      $campus_id = $sessionData['campusid'] ?? 0;
  }

  // Helpers to show dates as dd/mm/yyyy in the form (on EDIT)
  $fmt = function($d) {
      if (!$d || $d === '0000-00-00') return '';
      $ts = strtotime($d);
      return $ts ? date('d/m/Y', $ts) : '';
  };
  $gr_date_dmy           = $fmt($gr_date ?? '');
  $date_of_admission_dmy = $fmt($date_of_admission ?? '');
  $date_of_birth_dmy     = $fmt($date_of_birth ?? '');
?>
<?= view('components/page_header', [
    'title' => $header,
    'icon' => 'fas fa-user-graduate',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'url' => base_url('admin/students')],
        ['label' => $isEdit ? 'Edit student' : 'Add student', 'active' => true],
    ],
]) ?>
<script>
function checkfathercnic() {
  var father_cnic = $('#father_cnic').val();
  $.ajax({
    url: "<?= base_url('admin/ajax/check_father_cinic') ?>",
    type: "POST",
    data:{father_cnic: father_cnic},
    success:function(res){
      if(res){
        var sjson = res;
        console.log(sjson.parent_id);
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
    .noprint{ display:none; }
  }
  .nav-pills-custom .nav-link.active{ color: #fff !important; }
</style>


<!-- Main content -->
<section class="content">
  <div class="container-fluid px-2">

    <?php
      // Render the full admission view (it already includes its own card + form)
    $full_name = trim(($first_name ?? '') . ' ' . ($last_name ?? ''));
      $cls_sec_id = $info->cls_sec_id ?? 0;
      echo view('admin/studentstabs/edit_basic_info', get_defined_vars());
    ?>

  </div>
</section>

<?php if (!empty($isEdit) && !empty($id)): ?>
<!-- Inject edit identifiers into #student-admission-form and prefill dates if empty -->
<script>

  $(document).ready(function() {
    setTimeout(function() {
        var fullName = '<?= addslashes($full_name) ?>';
        if (fullName && $('#full_name').length) {
            $('#full_name').val(fullName);
            console.log('Set full_name to:', fullName);
        }
        
        // Also handle if the field has different ID
        if (fullName && $('#student-full_name').length) {
            $('#student-full_name').val(fullName);
        }
    }, 300);
});
(function () {
  var f = document.getElementById('student-admission-form');
  if (!f) return;

  // Ensure hidden field exists and set value
  function ensureHidden(name, val) {
    if (val === undefined || val === null) return;
    var el = f.querySelector('input[name="'+name+'"]');
    if (!el) {
      el = document.createElement('input');
      el.type = 'hidden';
      el.name = name;
      f.appendChild(el);
    }
    el.value = String(val);
  }

  // Provide IDs/context for UPDATE
  ensureHidden('student_id', '<?= (int)$id ?>');
  <?php if (isset($parent_id)): ?>
  ensureHidden('parent_id',  '<?= (int)$parent_id ?>');
  <?php endif; ?>
  ensureHidden('session_id', '<?= (int)$session_id ?>');
  ensureHidden('campus_id',  '<?= (int)$campus_id ?>');

  // Prefill dates on edit if inputs are empty
  function setIfEmpty(id, val) {
    if (!val) return;
    var el = document.getElementById(id);
    if (el && (!el.value || el.value.trim() === '')) el.value = val;
  }
  setIfEmpty('gr_date',           '<?= esc($gr_date_dmy        ?? "") ?>');
  setIfEmpty('date_of_admission', '<?= esc($date_of_admission_dmy ?? "") ?>');
  setIfEmpty('date_of_birth',     '<?= esc($date_of_birth_dmy  ?? "") ?>');

  // Keep reg_no readonly on edit
  var reg = document.getElementById('reg_no');
  if (reg) reg.setAttribute('readonly', 'readonly');
})();
</script>
<?php endif; ?>


<style>
  .nav-tabs .nav-link { font-weight: 600; color: #495057; }
  .nav-tabs .nav-link.active { color: #fff; background-color: #007bff; border-color: #dee2e6 #dee2e6 #fff; }
  .tab-content { background-color: #fff; padding: 20px; border-radius: 0 0 6px 6px; border: 1px solid #dee2e6; border-top: none; }
  .disabled { pointer-events: none; opacity: 0.5; }
  @media print { .noprint { display: none !important; } }
</style>

<?= $this->endSection() ?>
