<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>


<?php
  // ---------- Month helpers (server-side safe defaults) ----------
  $refMonthStr = isset($ref_month) && preg_match('/^\d{4}-\d{2}$/', $ref_month ?? '') ? ($ref_month . '-01') : date('Y-m-01');
  $refMonth    = new DateTime($refMonthStr);

  $prevMonth = (clone $refMonth)->modify('-1 month');
  $currMonth = (clone $refMonth);
  $nextMonth = (clone $refMonth)->modify('+1 month');

  $prevKey = $prevMonth->format('Y-m');
  $currKey = $currMonth->format('Y-m');
  $nextKey = $nextMonth->format('Y-m');

  $prevLbl = $prevMonth->format('F Y'); // e.g., July 2025
  $currLbl = $currMonth->format('F Y'); // e.g., August 2025
  $nextLbl = $nextMonth->format('F Y'); // e.g., September 2025
?>

<?= view('components/bulk_students_header', [
  'title' => 'Student Other Info',
  'subtitle' => 'Other Student Info'
]) ?>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">

      <!-- Nav tabs -->
       <div class="card-header pb-0">
        <?= view('components/bulk_students_tabs', ['active' => 'other']) ?>
      </div>

      <!-- Class picker (months are controlled via Columns selector) -->
      <div class="p-3">
        <div class="row">
          <div class="col-lg-6 form-group">
            <label for="cls_sec_id"><strong>Class</strong></label>
            <select class="form-control" name="cls_sec_id" id="cls_sec_id">
              <option value="">All Classes</option>
              <?php if (!empty($sectionsclassinfo)) : ?>
                <?php foreach ($sectionsclassinfo as $sectionvalue) : ?>
                  <option value="<?= esc($sectionvalue['cls_sec_id']) ?>">
                    <?= esc($sectionvalue['sectionclassname']) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
           <!-- NEW: Student search -->
    <div class="col-lg-6 form-group">
      <label for="student_search"><strong>Student</strong></label>
      <select class="form-control" id="student_search" style="width:100%"></select>
      <small class="text-muted">Type at least 2 letters, pick a student to load all siblings.</small>
    </div>
        </div>
      </div>

<!-- Columns selector -->
<div class="p-3 pt-2 pb-0 border-bottom bg-light">
  <div class="row align-items-stretch">

    <!-- ========== Student Fields ========== -->
    <div class="col-lg-4 mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
          <strong>Student Fields</strong>
          <div class="btn-group btn-group-sm" role="group" aria-label="Student toggles">
            <button type="button" class="btn btn-outline-primary group-toggle" data-group="student" data-action="all">Select all</button>
            <button type="button" class="btn btn-outline-secondary group-toggle" data-group="student" data-action="none">None</button>
          </div>
        </div>
        <div class="card-body py-2">
          <div class="d-flex flex-wrap">

            <!-- Core -->
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_dob" value="date_of_birth" data-group="student" checked>
              <label class="form-check-label" for="col_dob">Date of Birth</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_gender" value="gender" data-group="student">
              <label class="form-check-label" for="col_gender">Gender</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_flag" value="flag" data-group="student">
              <label class="form-check-label" for="col_flag">Student Type</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_photo" value="profile_photo" data-group="student">
              <label class="form-check-label" for="col_photo">Photo</label>
            </div>

            <!-- Student table (new) -->
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_first_name" value="first_name" data-group="student">
              <label class="form-check-label" for="col_first_name">First Name</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_last_name" value="last_name" data-group="student">
              <label class="form-check-label" for="col_last_name">Last Name</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_date_of_admission" value="date_of_admission" data-group="student">
              <label class="form-check-label" for="col_date_of_admission">Date of Admission</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_std_cnic" value="std_cnic" data-group="student">
              <label class="form-check-label" for="col_std_cnic">Student CNIC</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_std_type" value="std_type" data-group="student">
              <label class="form-check-label" for="col_std_type">Student Type (Alt)</label>
            </div>

            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_address" value="address" data-group="student">
              <label class="form-check-label" for="col_address">Address</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_prev_school" value="previous_school" data-group="student">
              <label class="form-check-label" for="col_prev_school">Previous School</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_ps_city" value="ps_city" data-group="student">
              <label class="form-check-label" for="col_ps_city">PS City</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_health" value="health_condition" data-group="student">
              <label class="form-check-label" for="col_health">Health Condition</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_injuries" value="major_injuries" data-group="student">
              <label class="form-check-label" for="col_injuries">Major Injuries</label>
            </div>
            
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_grno" value="gr_no" data-group="student">
              <label class="form-check-label" for="col_grno">GR No</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_grdate" value="gr_date" data-group="student">
              <label class="form-check-label" for="col_grdate">GR Date</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_religion" value="religion" data-group="student">
              <label class="form-check-label" for="col_religion">Religion</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_city" value="city" data-group="student">
              <label class="form-check-label" for="col_city">City</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_hear_source" value="hear_source" data-group="student">
              <label class="form-check-label" for="col_hear_source">Hear Source</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_emergency_person" value="emergency_contact_person" data-group="student">
              <label class="form-check-label" for="col_emergency_person">Emergency Person</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_relationship" value="relationship" data-group="student">
              <label class="form-check-label" for="col_relationship">Relationship</label>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- ========== Parent Fields ========== -->
    <div class="col-lg-4 mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
          <strong>Parent Fields</strong>
          <div class="btn-group btn-group-sm" role="group" aria-label="Parent toggles">
            <button type="button" class="btn btn-outline-primary group-toggle" data-group="parent" data-action="all">Select all</button>
            <button type="button" class="btn btn-outline-secondary group-toggle" data-group="parent" data-action="none">None</button>
          </div>
        </div>
        <div class="card-body py-2">
          <div class="d-flex flex-wrap">

            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_f_name" value="f_name" data-group="parent">
              <label class="form-check-label" for="col_f_name">Father Name</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_father_email" value="father_email" data-group="parent">
              <label class="form-check-label" for="col_father_email">Father Email</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_father_occupation" value="father_occupation" data-group="parent">
              <label class="form-check-label" for="col_father_occupation">Father Occupation</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_caste" value="caste" data-group="parent">
              <label class="form-check-label" for="col_caste">Caste</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_father_office_address" value="father_office_address" data-group="parent">
              <label class="form-check-label" for="col_father_office_address">Father Office Address</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_m_name" value="m_name" data-group="parent">
              <label class="form-check-label" for="col_m_name">Mother Name</label>
            </div>

            <!-- NEW Parent (bulk) -->
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_father_contact" value="father_contact" data-group="parent">
              <label class="form-check-label" for="col_father_contact">Father Contact</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_whatsapp" value="whatsapp" data-group="parent">
              <label class="form-check-label" for="col_whatsapp">Whatsapp</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_mother_contact" value="mother_contact" data-group="parent">
              <label class="form-check-label" for="col_mother_contact">Mother Contact</label>
            </div>
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_emergency_contact" value="emergency_contact" data-group="parent">
              <label class="form-check-label" for="col_emergency_contact">Emergency Contact</label>
            </div>

            <!-- Dependency: selecting CNIC will auto-select Father Name -->
            <div class="form-check form-check me-3 mb-2">
              <input type="checkbox" class="form-check-input upd-col" id="col_father_cnic" value="father_cnic" data-group="parent">
              <label class="form-check-label" for="col_father_cnic">Father CNIC</label>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- ========== Fee Related Fields ========== -->
    <div class="col-lg-4 mb-3">
      <div class="card h-100 shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
          <strong>Fee Related</strong>
          <div class="btn-group btn-group-sm" role="group" aria-label="Fee toggles">
            <button type="button" class="btn btn-outline-primary group-toggle" data-group="fee" data-action="all">Select all</button>
            <button type="button" class="btn btn-outline-secondary group-toggle" data-group="fee" data-action="none">None</button>
          </div>
        </div>
        <?php
$base   = new DateTime('first day of this month');
$prev   = (clone $base)->modify('-1 month');
$curr   = (clone $base);
$next   = (clone $base)->modify('+1 month');

$prevLbl = $prev->format('M Y');   $prevYm = $prev->format('Y-m');
$currLbl = $curr->format('M Y');   $currYm = $curr->format('Y-m');
$nextLbl = $next->format('M Y');   $nextYm = $next->format('Y-m');
?>
       <div class="card-body py-2">
  <div class="d-flex flex-wrap">

    <!-- Month columns -->
    <div class="form-check form-check me-3 mb-2">
      <input type="checkbox"
             class="form-check-input upd-col upd-month"
             id="col_month_prev"
             value="month_prev"
             data-ym="<?= esc($prevYm) ?>"
             data-bs-target=".col-month_prev">
      <label class="form-check-label" for="col_month_prev"><?= esc($prevLbl) ?></label>
    </div>

    <div class="form-check form-check me-3 mb-2">
      <input type="checkbox"
             class="form-check-input upd-col upd-month"
             id="col_month_curr"
             value="month_curr"
             data-ym="<?= esc($currYm) ?>"
             data-bs-target=".col-month_curr">
      <label class="form-check-label" for="col_month_curr"><?= esc($currLbl) ?></label>
    </div>

    <div class="form-check form-check me-3 mb-2">
      <input type="checkbox"
             class="form-check-input upd-col upd-month"
             id="col_month_next"
             value="month_next"
             data-ym="<?= esc($nextYm) ?>"
             data-bs-target=".col-month_next">
      <label class="form-check-label" for="col_month_next"><?= esc($nextLbl) ?></label>
    </div>

    <!-- Fee attrs (unchanged) -->
    <div class="form-check form-check me-3 mb-2">
      <input type="checkbox" class="form-check-input upd-col" id="col_discounted_amount"
             value="discounted_amount" data-group="fee" data-bs-target=".col-discounted_amount">
      <label class="form-check-label" for="col_discounted_amount">Student Fee</label>
    </div>

    <div class="form-check form-check me-3 mb-2">
      <input type="checkbox" class="form-check-input upd-col" id="col_fee_plan"
             value="fee_plan" data-group="fee" data-bs-target=".col-fee_plan">
      <label class="form-check-label" for="col_fee_plan">Fee Plan</label>
    </div>

    <!-- Where hidden month inputs will be injected (keep INSIDE the form) -->
    <div id="month-shims"></div>
  </div>
</div>
      </div>
    </div>

  </div>

  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-1">
    <small class="text-muted d-block mb-0">
      Only checked columns are shown, validated, and updated. Month columns also control which months are loaded.
    </small>
    <div class="d-flex align-items-center gap-2">
      <button type="button" id="mobileCardModeBtn" class="btn btn-sm btn-outline-secondary d-none">
        Expanded View
      </button>
      <button type="button" id="applyColsBtn" class="btn btn-sm btn-primary">Apply Columns</button>
    </div>
  </div>
</div>

      <!-- Table -->
      <div class="card-body">
        <div id="studentsList"
             data-prev-key="<?= esc($prevKey) ?>"
             data-curr-key="<?= esc($currKey) ?>"
             data-next-key="<?= esc($nextKey) ?>"
             data-prev-lbl="<?= esc($prevLbl) ?>"
             data-curr-lbl="<?= esc($currLbl) ?>"
             data-next-lbl="<?= esc($nextLbl) ?>">
          <div class="table-sticky-wrap table-responsive">
            <table class="table table-sm table-striped mb-0" id="studentsTable">
              <thead>
                <tr>
               <th class="sticky-col th-sno" style="width:70px;">S.No</th>
<th class="sticky-col-2 th-name">Student Name</th>

                  <!-- Month columns -->
                  <th data-col="month_prev" style="min-width:180px;"> <?= esc($prevLbl) ?></th>
                  <th data-col="month_curr" style="min-width:180px;"> <?= esc($currLbl) ?></th>
                  <th data-col="month_next" style="min-width:180px;"> <?= esc($nextLbl) ?></th>

                  <!-- Core -->
                  <th data-col="date_of_birth" style="min-width:140px;">Date of Birth</th>
                  <th data-col="gender" style="min-width:120px;">Gender</th>
                  <th data-col="flag" style="min-width:140px;">Student Type</th>
                  <th data-col="profile_photo" style="min-width:160px;">Photo</th>

                  <!-- Students table (new) -->
                  <th data-col="address" style="min-width:200px;">Address</th>
                  <th data-col="previous_school" style="min-width:180px;">Previous School</th>
                  <th data-col="ps_city" style="min-width:140px;">PS City</th>
                  <th data-col="health_condition" style="min-width:180px;">Health Condition</th>
                  <th data-col="major_injuries" style="min-width:160px;">Major Injuries</th>
                  <th data-col="caste" style="min-width:120px;">Caste</th>
                  <th data-col="gr_no" style="min-width:120px;">GR No</th>
                  <th data-col="gr_date" style="min-width:140px;">GR Date</th>
                  <th data-col="religion" style="min-width:140px;">Religion</th>
                  <th data-col="city" style="min-width:140px;">City</th>
                  <th data-col="hear_source" style="min-width:160px;">Hear Source</th>
                  <th data-col="emergency_contact_person" style="min-width:200px;">Emergency Person</th>
                  <th data-col="relationship" style="min-width:140px;">Relationship</th>

                  <!-- Parent fields -->
                  <th data-col="father_email" style="min-width:180px;">Father Email</th>
                  <th data-col="father_occupation" style="min-width:160px;">Father Occupation</th>
                  <th data-col="father_office_address" style="min-width:220px;">Father Office Address</th>
                  <th data-col="m_name" style="min-width:160px;">Mother Name</th>
                  <th data-col="father_contact" style="min-width:140px;">Father Contact</th>
                  <th data-col="whatsapp" style="min-width:140px;">Whatsapp</th>
                  <th data-col="mother_contact" style="min-width:140px;">Mother Contact</th>
                  <th data-col="emergency_contact" style="min-width:160px;">Emergency Contact</th>
                  <th data-col="father_cnic" style="min-width:200px;">Father CNIC</th>
                  <th data-col="f_name" style="min-width:160px;">Father Name</th>

                  <!-- Student fields -->
                  <th data-col="first_name" style="min-width:160px;">First Name</th>
                  <th data-col="last_name" style="min-width:160px;">Last Name</th>
                  <th data-col="date_of_admission" style="min-width:160px;">Date of Admission</th>
                  <th data-col="discounted_amount" style="min-width:160px;">Student Fee</th>
                  <th data-col="fee_plan" style="min-width:140px;">Fee Plan</th>
                  <th data-col="std_cnic" style="min-width:160px;">Student CNIC</th>
                  <th data-col="std_type" style="min-width:140px;">Student Type</th>

                  <th class="text-end" style="width: 110px;">Action</th>
                </tr>
              </thead>
              <tbody id="studentsTbody">
                <tr>
                  <td colspan="32" class="text-center text-muted">Select a class to view students…</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Capture/Crop Modal -->
      <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header py-2">
              <h6 class="modal-title" id="photoModalLabel">Capture / Crop Photo</h6>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" id="btnModalClose">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body py-2">
              <ul class="nav nav-tabs mb-2" id="photoTabs" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="camera-tab" data-bs-toggle="tab" href="#cameraPane" role="tab">Camera</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="upload-tab" data-bs-toggle="tab" href="#uploadPane" role="tab">Upload</a>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane fade show active" id="cameraPane" role="tabpanel">
                  <div class="border rounded p-2 mb-2 d-flex justify-content-center">
                    <video id="cameraVideo" playsinline autoplay muted style="max-width:100%;height:320px;background:#000;border-radius:6px;"></video>
                  </div>
                  <div class="text-center">
                    <button type="button" class="btn btn-sm btn-secondary" id="btnFlipCam">Flip</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnTakeSnap">Take Snapshot</button>
                  </div>
                </div>

                <div class="tab-pane fade" id="uploadPane" role="tabpanel">
                  <div class="form-group">
                    <input type="file" class="form-control-file" id="fileForCrop" accept="image/*">
                  </div>
                </div>
              </div>

              <hr class="my-2">
              <div class="border rounded p-2" style="max-height:420px;overflow:auto;">
                <img id="cropperImage" style="max-width:100%;display:none;" alt="Crop Preview">
              </div>
            </div>

            <div class="modal-footer py-2 d-flex justify-content-between">
              <div class="text-muted small">Aspect ratio set to ID card style (9:11). You can change this in JS.</div>
              <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetCrop">Reset</button>
                <button type="button" class="btn btn-sm btn-success" id="btnUseCropped">Use Photo</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loader -->
      <div id="loader-1" style="display:none;position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,0.7);">
        <div style="position:absolute;top:45%;left:50%;transform:translate(-50%,-50%);">
          <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
          <div>Loading...</div>
        </div>
      </div>

    </div>
  </div>
</section>
<style>
  /* ===== Base & utilities ===== */
  #studentsTable th, #studentsTable td { vertical-align: middle; }
  #cropperImage { max-height: 380px; }
  .is-tampered { box-shadow: inset 0 0 0 2px rgba(255,0,0,.35); }
  .cnic-hint { font-size: 11px; }

  /* wrap the table in <div class="table-sticky-wrap table-responsive"> */
  .table-sticky-wrap {
    max-height: 70vh;
    overflow: auto !important;
    -webkit-overflow-scrolling: touch;
  }

  /* ===== Table layout & overflow control ===== */
  #studentsTable {
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    --sno-w: 80px;          /* S.No width */
    --action-w: 110px;      /* action cell (if present) */
    min-width: 640px;       /* ensure horizontal scroll on mobile when many cols */
  }
  #studentsTable th, #studentsTable td {
    background: #fff;
    background-clip: padding-box;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Give data columns a floor so they don't compress to a sliver */
  #studentsTable th[data-col],
  #studentsTable td[data-col] { min-width: 120px; }

  /* ===== Sticky header ===== */
  #studentsTable thead th {
    position: sticky; top: 0; z-index: 10;
    box-shadow: 0 1px 0 rgba(0,0,0,0.05);
  }

  /* ===== Sticky S.No (always) ===== */
  #studentsTable th.sticky-col,
  #studentsTable td.sticky-col {
    position: sticky; left: 0; z-index: 6; background: #fff;
  }

  /* ===== Sticky Name (desktop/tablet only) ===== */
  @media (min-width: 577px) {
    #studentsTable th.sticky-col-2,
    #studentsTable td.sticky-col-2 {
      position: sticky; left: var(--sno-w); z-index: 5; background: #fff;
    }
  }
  /* On very small screens disable sticky for the name so other cols stay visible */
  @media (max-width: 576px) {
    #studentsTable th.sticky-col-2,
    #studentsTable td.sticky-col-2 {
      position: static; left: auto; z-index: auto;
    }
  }

  /* Keep sticky headers above sticky cells */
  #studentsTable thead th.sticky-col,
  #studentsTable thead th.sticky-col-2 { z-index: 12; }

  /* ===== Fixed/known widths ===== */
  #studentsTable .th-sno,
  #studentsTable .sno-cell {
    width: var(--sno-w);
    min-width: var(--sno-w);
    max-width: var(--sno-w);
    padding-left: .5rem; padding-right: .5rem;
  }
  #studentsTable .action-cell { width: var(--action-w); }

  /* Name column: clamp width so it can't eat the screen */
  #studentsTable .th-name,
  #studentsTable .student-name-cell {
    border-end: 1px solid #e9ecef;
    /* Desktop: ~280px; can shrink on smaller screens but never below 140px */
    width: clamp(140px, 40vw, 280px);
    max-width: clamp(140px, 40vw, 280px);
  }

  /* Slightly smaller cap on tablets */
  @media (max-width: 992px) {
    #studentsTable .th-name,
    #studentsTable .student-name-cell {
      width: clamp(140px, 36vw, 240px);
      max-width: clamp(140px, 36vw, 240px);
    }
  }
  /* Phones: keep name tight, let you scroll to see the rest */
  @media (max-width: 768px) {
    #studentsTable { font-size: 13px; }
    #studentsTable .th-name,
    #studentsTable .student-name-cell {
      width: clamp(140px, 52vw, 220px);
      max-width: clamp(140px, 52vw, 220px);
    }
  }
  @media (max-width: 576px) {
    #studentsTable { font-size: 12px; }
    #studentsTable .th-name,
    #studentsTable .student-name-cell {
      width: clamp(140px, 55vw, 200px);
      max-width: clamp(140px, 55vw, 200px);
    }
  }

  /* ===== Mobile-first polish for filters/controls ===== */
  @media (max-width: 768px) {
    .content .card-header .nav { flex-wrap: nowrap; overflow-x: auto; white-space: nowrap; }
    .content .card-header .nav .nav-item { float: none; display: inline-block; }
    .content .row > [class*="col-"],
    .content .row > [class*="col-"] { margin-bottom: .75rem; }
    .content .btn-group { display: flex; width: 100%; }
    .content .btn-group .btn { flex: 1 1 auto; }
    #studentsTable td .form-control,
    #studentsTable td .form-select,
    #studentsTable td select { min-width: 120px; }
    #studentsTable .action-cell .btn,
    #studentsTable td .saveStudentBtn { width: 100%; }
  }

  /* ===== Mobile card view (table -> stacked cards) ===== */
  @media (max-width: 768px) {
    .table-sticky-wrap { max-height: none; overflow: visible !important; }
    #studentsTable {
      min-width: 100%;
      width: 100%;
      table-layout: auto;
      border-collapse: separate;
      border-spacing: 0;
    }
    #studentsTable thead { display: none; }
    #studentsTable tbody,
    #studentsTable tr,
    #studentsTable td {
      display: block;
      width: 100%;
    }
    #studentsTable tbody tr {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      margin-bottom: .85rem;
      background: #fff;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
      overflow: hidden;
    }
    #studentsTable tbody td {
      border: 0;
      border-bottom: 1px solid #f1f3f5;
      padding: .6rem .75rem;
      max-width: 100% !important;
      white-space: normal;
      overflow: visible;
      text-overflow: initial;
      position: static !important;
      left: auto !important;
      z-index: auto !important;
    }
    #studentsTable tbody td:last-child { border-bottom: 0; }
    #studentsTable tbody td::before {
      content: attr(data-label);
      display: block;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      color: #6c757d;
      margin-bottom: .3rem;
      letter-spacing: .02em;
    }
    #studentsTable td .form-control,
    #studentsTable td select,
    #studentsTable td .btn-group,
    #studentsTable td .btn {
      width: 100%;
      max-width: 100%;
    }
    #studentsTable .sno-cell,
    #studentsTable .student-name-cell {
      background: #f8f9fa;
    }
    #studentsTable .sno-cell { font-weight: 600; }

    /* Compact mode: keep only identity + common editable fields visible */
    body.mobile-card-compact #studentsTable tbody td[data-col] { display: none !important; }
    body.mobile-card-compact #studentsTable tbody td[data-col="first_name"],
    body.mobile-card-compact #studentsTable tbody td[data-col="last_name"],
    body.mobile-card-compact #studentsTable tbody td[data-col="date_of_birth"],
    body.mobile-card-compact #studentsTable tbody td[data-col="gender"],
    body.mobile-card-compact #studentsTable tbody td[data-col="f_name"],
    body.mobile-card-compact #studentsTable tbody td[data-col="father_contact"],
    body.mobile-card-compact #studentsTable tbody td[data-col="mother_contact"],
    body.mobile-card-compact #studentsTable tbody td[data-col="father_cnic"],
    body.mobile-card-compact #studentsTable tbody td[data-col="discounted_amount"],
    body.mobile-card-compact #studentsTable tbody td[data-col="month_prev"],
    body.mobile-card-compact #studentsTable tbody td[data-col="month_curr"],
    body.mobile-card-compact #studentsTable tbody td[data-col="month_next"] {
      display: block !important;
    }

    body.mobile-card-compact #studentsTable tbody td.action-cell {
      display: block !important;
    }
  }
</style>



<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<script>
/* ================================
   BULK INFO — FULL CLIENT SCRIPT
   ================================ */

(function(){
  'use strict';

  /* ---------- Config ---------- */
  const URL_DATA        = "<?= base_url('admin/students_bulk_info/data') ?>";
  const URL_SEARCH_NAME = "<?= base_url('admin/students_bulk_info/search-by-name') ?>";
  const URL_LOOKUP_CNIC = "<?= site_url('admin/students_bulk_info/lookup_parent_by_cnic') ?>";

  const CSRF_NAME = "<?= csrf_token() ?>";
  let   CSRF_HASH = "<?= csrf_hash() ?>";

  // If non-null, we are in "parent mode" and ignore cls_sec_id
  let currentParentId = null;

  function getCampusId(){
    const $camp = $('#campus_id');
    return ($camp.length && $camp.val()) ? $camp.val() : "<?= (int) session('campus_id') ?>";
  }

  /* ---------- Group toggle (top) ---------- */
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.group-toggle');
    if (!btn) return;

    const group  = btn.getAttribute('data-group');
    const action = btn.getAttribute('data-action');
    if (!group || !action) return;

    const boxes = document.querySelectorAll('.upd-col[data-group="'+group+'"]');
    const check = action === 'all';
    boxes.forEach(cb => { cb.checked = check; cb.dispatchEvent(new Event('change')); });

    enforceFatherDependency();
  });

  /* ---------- DOM Ready ---------- */
  $(function () {

    /* ======= Capture / Crop (unchanged) ======= */
    let activeRow = null;
    let cropper = null;
    let stream = null;
    let useFacing = "user";
    const $modal = $('#photoModal');
    const $video = $('#cameraVideo');
    const $cropImg = $('#cropperImage');

    async function startCamera() {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        $('#upload-tab').tab('show');
        window.toastr && toastr.info('Camera not supported. Please upload a photo.');
        return;
      }
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: { ideal: useFacing }, width: { ideal: 1280 }, height: { ideal: 720 } },
          audio: false
        });
        $video[0].srcObject = stream;
      } catch (e) {
        console.error(e);
        $('#upload-tab').tab('show');
        window.toastr && toastr.error('Camera permission denied. Please upload a photo.');
      }
    }
    function stopCamera() { if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; } $video[0].srcObject = null; }
    function destroyCropper() { if (cropper) { cropper.destroy(); cropper = null; } }
    function initCropperFromSrc(src) {
      destroyCropper();
      $cropImg.attr('src', src).show();
      cropper = new Cropper($cropImg[0], { aspectRatio: 9/11, viewMode: 1, autoCropArea: 1, background: false, movable: true, zoomable: true, rotatable: false, scalable: false });
    }
    function openPhotoModalForRow($row, startOnCamera = true) {
      activeRow = $row; destroyCropper(); stopCamera(); $cropImg.hide().attr('src', '');
      if (startOnCamera) { $('#camera-tab').tab('show'); startCamera(); } else { $('#upload-tab').tab('show'); }
      $modal.modal('show');
    }
    function applyCroppedToActiveRow() {
      if (!activeRow || !cropper) return;
      const canvas = cropper.getCroppedCanvas({ width: 450, height: 550, imageSmoothingQuality: 'high' });
      canvas.toBlob((blob) => {
        if (!blob) return window.toastr && toastr.error('Failed to crop image.');
        const file = new File([blob], 'capture.jpg', { type: 'image/jpeg', lastModified: Date.now() });
        const dt = new DataTransfer(); dt.items.add(file);
        const $input = activeRow.find('.fileInputPhoto[name="profile_photo"]');
        if ($input.length) $input[0].files = dt.files;
        const url = URL.createObjectURL(file);
        const $prev = activeRow.find('.photoPreview');
        if ($prev.is('img')) $prev.attr('src', url); else $prev.replaceWith('<img class="photoPreview" src="'+url+'" style="height:40px;width:40px;object-fit:cover;border-radius:3px;">');
        $modal.modal('hide'); destroyCropper(); stopCamera(); window.toastr && toastr.success('Photo ready. Click Save to upload.');
      }, 'image/jpeg', 0.9);
    }
    $('#studentsTable').on('click', '.btnCaptureCrop', function () { openPhotoModalForRow($(this).closest('tr'), true); });
    $('#studentsTable').on('click', '.btnCropExisting', function () {
      const $row = $(this).closest('tr');
      const input = $row.find('.fileInputPhoto[name="profile_photo"]')[0];
      if (!input || !input.files || !input.files.length) return window.toastr && toastr.info('No selected image to crop. Use Capture/Upload first.');
      const reader = new FileReader();
      reader.onload = (e) => { openPhotoModalForRow($row, false); initCropperFromSrc(e.target.result); };
      reader.readAsDataURL(input.files[0]);
    });
    $('#btnTakeSnap').on('click', function () {
      if (!$video[0].srcObject) return window.toastr && toastr.error('Camera not started.');
      const v = $video[0], c = document.createElement('canvas'); c.width = v.videoWidth || 1280; c.height = v.videoHeight || 720;
      c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
      initCropperFromSrc(c.toDataURL('image/jpeg', 0.95));
    });
    $('#btnFlipCam').on('click', function () { useFacing = (useFacing === 'user') ? 'environment' : 'user'; stopCamera(); startCamera(); });
    $('#fileForCrop').on('change', function () { const f = this.files && this.files[0]; if (!f) return; const r = new FileReader(); r.onload = e => initCropperFromSrc(e.target.result); r.readAsDataURL(f); });
    $('#btnUseCropped').on('click', applyCroppedToActiveRow);
    $('#btnResetCrop').on('click', function () { if (cropper) cropper.reset(); });
    $modal.on('hidden.bs.modal', function () { destroyCropper(); stopCamera(); });

    /* ======= Column show/hide ======= */
    let selectedColumns = new Set(["date_of_birth"]);

    function readSelectedColumns() {
      selectedColumns.clear();
      $('.upd-col:checked').each(function () { selectedColumns.add($(this).val()); });
      if (selectedColumns.has('father_cnic')) {
        selectedColumns.add('f_name');
        if (!$('#col_f_name').prop('checked')) $('#col_f_name').prop('checked', true);
      }
    }
    function enforceFatherDependency() {
      const cnicChecked = $('#col_father_cnic').prop('checked');
      if (cnicChecked && !$('#col_f_name').prop('checked')) {
        $('#col_f_name').prop('checked', true).trigger('change');
      }
    }

    const TPL = {
      father_contact:    () => `<input name="father_contact" class="form-control form-control-sm" placeholder="03XXXXXXXXX">`,
      whatsapp:          () => `<input name="whatsapp" class="form-control form-control-sm" placeholder="WhatsApp">`,
      mother_contact:    () => `<input name="mother_contact" class="form-control form-control-sm" placeholder="03XXXXXXXXX">`,
      emergency_contact: () => `<input name="emergency_contact" class="form-control form-control-sm" placeholder="Emergency Contact">`,
     // Parent CNIC cell + helpers
father_cnic: () => `
  <div>
    <input name="father_cnic"
           class="form-control form-control-sm father-cnic cnic-mask"
           placeholder="35202-XXXXXXX-X"
           autocomplete="off"
           inputmode="numeric">
    <div class="form-check form-check mt-1">
      <input type="checkbox" class="form-check-input cnic-clear-toggle" id="clr_${Math.random().toString(36).slice(2)}">
      <label class="form-check-label small" for="clr_${Math.random().toString(36).slice(2)}">Clear & relink</label>
    </div>
    <input type="hidden" name="parent_id" class="parent-id" value="">
    <small class="text-muted cnic-hint d-block mt-1"></small>
  </div>`,

      f_name:            () => `<input name="f_name" class="form-control form-control-sm father-name" placeholder="Father Name">`,
      first_name:        () => `<input name="first_name" class="form-control form-control-sm" placeholder="First Name">`,
      last_name:         () => `<input name="last_name" class="form-control form-control-sm" placeholder="Last Name">`,
      date_of_admission: () => `<input name="date_of_admission" type="date" class="form-control form-control-sm">`,
      discounted_amount: () => `<input name="discounted_amount" class="form-control form-control-sm" placeholder="0.00">`,
      fee_plan: () => `
        <select name="fee_plan" class="form-control form-control-sm">
          <option value="0">Monthly</option>
          <option value="1">Bi-monthly</option>
          <option value="2">Quarterly</option>
          <option value="3">Annually</option>
        </select>`,
      std_cnic:          () => `<input name="std_cnic" class="form-control form-control-sm" placeholder="Student CNIC">`,
      std_type: () => `
        <select name="std_type" class="form-control form-control-sm">
          <option value="1">Daycare</option>
          <option value="2">Boarding</option>
        </select>`,
      date_of_birth:     () => `<input name="date_of_birth" type="date" class="form-control form-control-sm">`,
      gender: () => `
        <select name="gender" class="form-control form-control-sm">
          <option value="">Select</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>`,
      flag: () => `
        <select name="flag" class="form-control form-control-sm">
          <option value="1">Daycare</option>
          <option value="2">Boarding</option>
        </select>`,
      profile_photo: () => `
        <div class="d-flex align-items-center">
          <input type="file" name="profile_photo" class="fileInputPhoto d-none">
          <button type="button" class="btn btn-sm btn-outline-primary btnCaptureCrop ms-0">Capture/Upload</button>
        </div>`,
      address:                 () => `<input name="address" class="form-control form-control-sm" placeholder="Address">`,
      previous_school:         () => `<input name="previous_school" class="form-control form-control-sm" placeholder="Previous School">`,
      ps_city:                 () => `<input name="ps_city" class="form-control form-control-sm" placeholder="City">`,
      health_condition:        () => `<input name="health_condition" class="form-control form-control-sm" placeholder="Health">`,
      major_injuries:          () => `<input name="major_injuries" class="form-control form-control-sm" placeholder="Injuries">`,
      caste:                   () => `<input name="caste" class="form-control form-control-sm" placeholder="Caste">`,
      gr_no:                   () => `<input name="gr_no" class="form-control form-control-sm" placeholder="GR No">`,
      gr_date:                 () => `<input name="gr_date" type="date" class="form-control form-control-sm">`,
      religion:                () => `<input name="religion" class="form-control form-control-sm" placeholder="Religion">`,
      city:                    () => `<input name="city" class="form-control form-control-sm" placeholder="City">`,
      hear_source:             () => `<input name="hear_source" class="form-control form-control-sm" placeholder="Heard From">`,
      emergency_contact_person:() => `<input name="emergency_contact_person" class="form-control form-control-sm" placeholder="Emergency Person">`,
      relationship:            () => `<input name="relationship" class="form-control form-control-sm" placeholder="Relationship">`,
      father_email:            () => `<input name="father_email" type="email" class="form-control form-control-sm" placeholder="Father Email">`,
      father_occupation:       () => `<input name="father_occupation" class="form-control form-control-sm" placeholder="Occupation">`,
      father_office_address:   () => `<input name="father_office_address" class="form-control form-control-sm" placeholder="Office Address">`,
      m_name:                  () => `<input name="m_name" class="form-control form-control-sm" placeholder="Mother Name">`,
    };

    function renumberRows() {
      $('#studentsTable tbody tr').each(function (idx) {
        const $tr  = $(this);
        const $sno = $tr.children('td.sno-cell').first();
        if (!$sno.length) return;
        const $hid = $sno.find('[name="student_id"]').detach();
        $sno.text(String(idx + 1));
        if ($hid && $hid.length) $sno.append($hid);
      });
    }

    function getNameHTML($tr) {
      const $pref = $tr.children('td.student-name-cell').first();
      if ($pref.length) return $pref.html();
      const $marker = $tr.find('.student-name-source').first();
      if ($marker.length) return $marker.html();
      let $cand = null;
      $tr.children('td').each(function(){
        const $td = $(this);
        if ($td.is('[data-col]')) return;
        if ($td.find('.saveStudentBtn').length) return;
        if ($td.find('[name="student_id"]').length) return;
        if (!$cand) $cand = $td;
      });
      if ($cand && $cand.length) return $cand.html();
      const ds = ($tr.data('student-name') || '').toString().trim();
      if (ds) return ds;
      const fn = ($tr.find('[name="first_name"]').val() || $tr.attr('data-first-name') || '').trim();
      const ln = ($tr.find('[name="last_name"]').val()  || $tr.attr('data-last-name')  || '').trim();
      return [fn, ln].filter(Boolean).join(' ');
    }

    function applySelectionToTable() {
      const headerColsOrdered = $('#studentsTable thead [data-col]').map(function () {
        return $(this).data('col');
      }).get();
      const headerLabelMap = {};
      $('#studentsTable thead [data-col]').each(function () {
        const key = $(this).data('col');
        headerLabelMap[key] = $.trim($(this).text()) || key;
      });

      $('#studentsTable tbody tr').each(function () {
        const $tr = $(this);
        const $tds = $tr.children('td');
        if ($tds.length === 1 && $tds.attr('colspan')) return;

        let $actionTd = null;
        $tr.children('td').each(function () {
          if ($(this).find('.saveStudentBtn').length) {
            $actionTd = $(this).detach();
            $actionTd.addClass('action-cell').css('width','110px');
          }
        });

        const $studentIdInput = $tr.find('input[name="student_id"]').first().detach();
        const nameHTML = getNameHTML($tr);

        $tr.children('td').each(function(){
          const $td = $(this);
          if (!$td.is('[data-col]')) $td.remove();
        });

        const existing = {};
        $tr.children('td[data-col]').each(function(){
          const key = $(this).data('col');
          existing[key] = $(this).detach();
        });

        const $snoTd  = $('<td class="sno-cell sticky-col" style="width:70px;"></td>');
        $snoTd.attr('data-label', 'S.No');
        if ($studentIdInput && $studentIdInput.length) $snoTd.append($studentIdInput);
        const $nameTd = $('<td class="student-name-cell sticky-col-2"></td>').html(nameHTML || '');
        $nameTd.attr('data-label', 'Student Name');

        const ordered = [];
        headerColsOrdered.forEach(function (key) {
          let $cell = existing[key];
          if (!$cell || !$cell.length) {
            $cell = TPL[key] ? $(`<td data-col="${key}">${TPL[key]()}</td>`)
                             : $(`<td data-col="${key}"></td>`);
          }
          $cell.attr('data-label', headerLabelMap[key] || key);
          ordered.push($cell);
        });

        $tr.empty().append($snoTd, $nameTd);
        ordered.forEach($c => $tr.append($c));
        if ($actionTd) {
          $actionTd.attr('data-label', 'Action');
          $tr.append($actionTd);
        }

        decorateParentLinkingUI($tr);
      });

      $('#studentsTable thead [data-col]').each(function(){
        const col = $(this).data('col');
        $(this).toggleClass('d-none', !selectedColumns.has(col));
      });
      $('#studentsTable tbody [data-col]').each(function(){
        const col = $(this).data('col');
        $(this).toggleClass('d-none', !selectedColumns.has(col));
      });

      renumberRows();
      refreshMobileCardModeUI();
    }

    function isMobileViewport() {
      return window.matchMedia('(max-width: 768px)').matches;
    }

    function refreshMobileCardModeUI() {
      const $btn = $('#mobileCardModeBtn');
      if (!$btn.length) return;

      if (!isMobileViewport()) {
        $('body').removeClass('mobile-card-compact');
        $btn.addClass('d-none');
        return;
      }

      $btn.removeClass('d-none');
      const compact = $('body').hasClass('mobile-card-compact');
      $btn.text(compact ? 'Expanded View' : 'Compact View');
      $btn.toggleClass('btn-outline-secondary', compact);
      $btn.toggleClass('btn-outline-primary', !compact);
    }

    /* ======= Fee helpers ======= */
    let feeSyncing = false;
    function parseNum(v) { const n = parseFloat(String(v).replace(/,/g, '')); return isFinite(n) ? n : 0; }
    function getClassFee($row) {
      const $hidden = $row.find('[name="class_fee"]');
      if ($hidden.length) return parseNum($hidden.val());
      const attr = $row.attr('data-classfee');
      if (attr != null) return parseNum(attr);
      return 0;
    }
    function syncFromDisplayStudentFee($row, opts) {
      opts = opts || {}; if (feeSyncing) return; feeSyncing = true;
      const classFee   = getClassFee($row);
      const $display   = $row.find('[name="discounted_amount"]');
      const $hiddenSF  = $row.find('[name="student_fee"]');
      if ($display.length) {
        let sf = parseNum($display.val());
        if (classFee > 0) { if (sf > classFee) sf = classFee; if (sf < 0) sf = 0; } else { if (sf < 0) sf = 0; }
        if (opts.format === true) $display.val(sf.toFixed(2));
        if ($hiddenSF.length)      $hiddenSF.val(sf.toFixed(2));
      }
      feeSyncing = false;
    }
    function wireUpFeeHandlers($scope) {
      const $root = $scope || $('#studentsTable');
      $root.off('.fees');

      $root.find('tr').each(function () {
        const $row = $(this);
        const $hiddenSF = $row.find('[name="student_fee"]');
        const $display  = $row.find('[name="discounted_amount"]');
        if (!$display.length) return;

        let sf = null;
        if ($hiddenSF.length) {
          sf = parseNum($hiddenSF.val());
        } else if ($display.data('student-fee') != null) {
          sf = parseNum($display.data('student-fee'));
        } else if ($display.val()) {
          sf = parseNum($display.val());
        } else {
          sf = parseNum(getClassFee($row));
        }
        if (Number.isFinite(sf)) $display.val(sf.toFixed(2));
        syncFromDisplayStudentFee($row, { format: true });
      });

      $root.on('input.fees',  '[name="discounted_amount"]', function () {
        $(this).data('auto-filled', false);
        syncFromDisplayStudentFee($(this).closest('tr'), { format: false });
      });
      $root.on('change.fees blur.fees', '[name="discounted_amount"]', function () {
        $(this).data('auto-filled', false);
        syncFromDisplayStudentFee($(this).closest('tr'), { format: true });
      });
    }

    /* ======= Month shims ======= */
    function ensureMonthShimInRow($row, ym, on){
      if (!ym) return;
      const sel = `.shim-month[data-ym="${ym}"]`;
      if (on) {
        if (!$row.find(sel).length) {
          $('<input>', { type: 'hidden', name: `months[${ym}][apply]`, value: 1, class: 'shim-month', 'data-ym': ym }).appendTo($row);
        }
      } else {
        $row.find(sel).remove();
      }
    }
    function syncMonthShimsForAllRows(){
      const checkedYms = $('.upd-month:checked').map(function(){ return $(this).data('ym'); }).get();
      const onSet = new Set(checkedYms);
      $('#studentsTable tbody tr').each(function(){
        const $row = $(this);
        $row.find('.shim-month').remove();
        onSet.forEach(ym => ensureMonthShimInRow($row, ym, true));
      });
    }
    function getSelectedMonths() {
      const $cont = $('#studentsList');
      const map = {
        month_prev: { key: $cont.data('prev-key'), label: $cont.data('prev-lbl'), col: 'month_prev' },
        month_curr: { key: $cont.data('curr-key'), label: $cont.data('curr-lbl'), col: 'month_curr' },
        month_next: { key: $cont.data('next-key'), label: $cont.data('next-lbl'), col: 'month_next' },
      };
      const months = [];
      ['month_prev','month_curr','month_next'].forEach(k => { if (selectedColumns.has(k)) months.push(map[k]); });
      return months;
    }

    /* ======= Tamper hint ======= */
    $('#studentsTable').on('input', '.month-net', function () {
      const orig = parseNum($(this).data('original'));
      const now  = parseNum($(this).val());
      $(this).toggleClass('is-tampered', orig.toFixed(2) !== now.toFixed(2));
    });

    /* ======= Parent linking (CNIC) ======= */
    function normalizeCnic(raw) {
      const d = String(raw || '').replace(/\D+/g,'');
      if (d.length === 13) return d.slice(0,5)+'-'+d.slice(5,12)+'-'+d.slice(12);
      return String(raw || '').trim();
    }
    function decorateParentLinkingUI($row) {
      const $cnicCell = $row.find('td[data-col="father_cnic"]');
      if ($cnicCell.length) {
        const $cnic = $cnicCell.find('input[name="father_cnic"]');
        if ($cnic.length) $cnic.addClass('father-cnic');
        if (!$cnicCell.find('input[name="parent_id"]').length) {
          $cnicCell.append('<input type="hidden" name="parent_id" class="parent-id" value="">');
        }
        if (!$cnicCell.find('.cnic-hint').length) {
          $cnicCell.append('<small class="text-muted cnic-hint d-block mt-1"></small>');
        }
        if (!$cnicCell.find('.cnic-clear-toggle').length) {
          const sid = ($('#studentsTable [name="student_id"]').first().val() || Math.random().toString(36).slice(2));
          $cnicCell.append(
            '<div class="form-check form-check mt-1">' +
              '<input type="checkbox" class="form-check-input cnic-clear-toggle" id="clr_'+sid+'">' +
              '<label class="form-check-label small" for="clr_'+sid+'">Clear & relink</label>' +
            '</div>'
          );
        }
      }
    }
    function wireUpParentLinkingHandlers($scope) {
      const $root = $scope || $('#studentsTable');
      $root.find('tr').each(function(){ decorateParentLinkingUI($(this)); });
      $root.off('.parentlink');

      $root.on('change.parentlink', '.cnic-clear-toggle', function(){
        const $row = $(this).closest('tr');
        $row.find('input[name="father_cnic"]').val('').trigger('input');
        $row.find('input[name="f_name"]').val('');
        $row.find('input[name="parent_id"]').val('');
        $row.find('.cnic-hint').text('Cleared. Enter a CNIC to link or create new.');
      });

      $root.on('input.parentlink', 'input[name="father_cnic"]', function(){
        const v = $(this).val();
        if (!v) {
          const $row = $(this).closest('tr');
          $row.find('input[name="parent_id"]').val('');
          $row.find('.cnic-hint').text('');
        }
      });

      $root.on('blur.parentlink', 'input[name="father_cnic"]', function(){
        const $row = $(this).closest('tr');
        let cnic = normalizeCnic($(this).val());
        if (!cnic) return;

        $(this).val(cnic);
        $row.find('.cnic-hint').text('Checking CNIC…');

        $.ajax({
          url: URL_LOOKUP_CNIC,
          type: "POST",
          dataType: "json",
          data: { cnic: cnic, [CSRF_NAME]: CSRF_HASH },
          success: function(res, _s, xhr){
            const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
            if (newToken) CSRF_HASH = newToken;

            if (!res || res.success === false) {
              $row.find('.cnic-hint').text('Lookup failed.');
              return;
            }
            if (res.found) {
              $row.find('input[name="parent_id"]').val(res.parent_id || '');
              $row.find('input[name="f_name"]').val(res.f_name || '');
              $row.find('.cnic-hint').text('Linked to existing parent #'+(res.parent_id||''));
              window.toastr && toastr.success('Existing parent found and linked for this row.');
            } else {
              $row.find('input[name="parent_id"]').val('');
              if (!$row.find('input[name="f_name"]').val()) {
                $row.find('.cnic-hint').text('No parent found. Please enter Father Name to create a new parent on Save.');
              } else {
                $row.find('.cnic-hint').text('No parent found. Will create new parent on Save.');
              }
            }
          },
          error: function(){
            $row.find('.cnic-hint').text('Lookup failed.');
            window.toastr && toastr.error('CNIC lookup error.');
          }
        });
      });
    }

    /* ======= Loading — CLASS vs PARENT ======= */
    function loadStudentsByClass() {
      if (currentParentId) return loadStudentsByParent(currentParentId); // guard

      const cls_sec_id = $('#cls_sec_id').val();
      const months     = getSelectedMonths();

      if (!cls_sec_id) {
        $('#studentsTbody').html('<tr><td colspan="32" class="text-center text-muted">Select a class to view students…</td></tr>');
        return;
      }

      $("#loader-1").show();

      $.ajax({
        url: URL_DATA,
        type: "POST",
        data: {
          cls_sec_id: cls_sec_id,
          months_json: JSON.stringify(months),
          ref_month: $('#studentsList').data('curr-key'),
          campus_id: getCampusId(),
          [CSRF_NAME]: CSRF_HASH
        },
        success: function (res, _status, xhr) {
          const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
          if (newToken) CSRF_HASH = newToken;

          $("#studentsTbody").html(res);
          readSelectedColumns();
          applySelectionToTable();
          wireUpFeeHandlers($('#studentsTable'));
          wireUpParentLinkingHandlers($('#studentsTable'));
          syncMonthShimsForAllRows();
          renumberRows();
          $("#loader-1").hide();
        },
        error: function () {
          $("#loader-1").hide();
          $('#studentsTbody').html('<tr><td colspan="32" class="text-center text-danger">Failed to load students.</td></tr>');
        }
      });
    }

    function loadStudentsByParent(parentId) {
      const months = getSelectedMonths();
      $("#loader-1").show();

      $.ajax({
        url: URL_DATA,  // server: when parent_id is present, ignore cls_sec_id and scope by campus + status=1
        type: "POST",
        data: {
          parent_id: parentId,
          months_json: JSON.stringify(months),
          ref_month: $('#studentsList').data('curr-key'),
          campus_id: getCampusId(),
          [CSRF_NAME]: CSRF_HASH
        },
        success: function (res, _status, xhr) {
          const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
          if (newToken) CSRF_HASH = newToken;

          $("#studentsTbody").html(res || '<tr><td colspan="32" class="text-center text-info">No students found for this parent.</td></tr>');
          readSelectedColumns();
          applySelectionToTable();
          wireUpFeeHandlers($('#studentsTable'));
          wireUpParentLinkingHandlers($('#studentsTable'));
          syncMonthShimsForAllRows();
          renumberRows();
          $("#loader-1").hide();
          window.toastr && toastr.info('Showing students for selected parent.');
        },
        error: function (xhr) {
          $("#loader-1").hide();
          $('#studentsTbody').html('<tr><td colspan="32" class="text-center text-danger">Failed to load students by parent (HTTP '+xhr.status+').</td></tr>');
        }
      });
    }

    /* ======= Search by name (Select2) — sets parent mode ======= */
    $('#student_search').select2({
      placeholder: 'Search student by name',
      minimumInputLength: 2,
      allowClear: true,
      width: 'resolve',
      ajax: {
        url: URL_SEARCH_NAME,
        dataType: 'json',
        delay: 250,
        cache: true,
        data: function (params) {
          return {
            q: params.term,
            limit: 20,
            cls_sec_id: $('#cls_sec_id').val() || '',
            campus_id:  getCampusId()
          };
        },
        processResults: function (data) {
          return { results: data && data.results ? data.results : [] };
        }
      },
      templateResult: function (item) {
        if (item.loading) return item.text;
        return $('<div>').text(item.text);
      },
      templateSelection: function (item) {
        return item.text || item.id;
      }
    });

    // When a student is chosen → switch to parent mode and load
    $('#student_search').on('select2:select', function (e) {
      const data = e.params.data || {};
      if (!data.parent_id) {
        return window.toastr && toastr.warning('Selected student has no parent_id.');
      }
      currentParentId = data.parent_id;
      loadStudentsByParent(currentParentId);
    });

    // Clearing search → leave parent mode; if class chosen, load by class
    $('#student_search').on('select2:clear select2:unselect', function(){
      currentParentId = null;
      if ($('#cls_sec_id').val()) loadStudentsByClass();
      else $('#studentsTbody').html('<tr><td colspan="32" class="text-center text-muted">Select a class to view students…</td></tr>');
    });

    // Changing class or campus clears search & parent mode and loads by class
    $(document).on('change', '#cls_sec_id, #campus_id', function(){
      currentParentId = null;
      $('#student_search').val(null).trigger('change');
      if ($('#cls_sec_id').is(this)) loadStudentsByClass();
    });

    /* ======= Init + UI bindings ======= */
    readSelectedColumns();
    enforceFatherDependency();
    applySelectionToTable();
    wireUpFeeHandlers($('#studentsTable'));
    wireUpParentLinkingHandlers($('#studentsTable'));
    syncMonthShimsForAllRows();

    // Apply columns → reload using active mode
    $(document).on('click', '#applyColsBtn', function () {
      readSelectedColumns();
      if (selectedColumns.size === 0) return window.toastr && toastr.warning('Select at least one column to show.');
      enforceFatherDependency();
      applySelectionToTable();
      wireUpFeeHandlers($('#studentsTable'));
      wireUpParentLinkingHandlers($('#studentsTable'));
      syncMonthShimsForAllRows();
      window.toastr && toastr.info('Column selection applied.');

      if (currentParentId) loadStudentsByParent(currentParentId);
      else if ($('#cls_sec_id').val()) loadStudentsByClass();
    });

    // Column checkbox toggles: if months changed, reload with active mode
    $(document).on('change', '.upd-col', function () {
      readSelectedColumns();
      enforceFatherDependency();
      applySelectionToTable();
      wireUpFeeHandlers($('#studentsTable'));
      wireUpParentLinkingHandlers($('#studentsTable'));
      syncMonthShimsForAllRows();

      const v = $(this).val();
      if (['month_prev','month_curr','month_next'].includes(v)) {
        if (currentParentId) loadStudentsByParent(currentParentId);
        else if ($('#cls_sec_id').val()) loadStudentsByClass();
      }
    });

    // Mobile compact/expanded toggle (card view only)
    $(document).on('click', '#mobileCardModeBtn', function () {
      if (!isMobileViewport()) return;
      $('body').toggleClass('mobile-card-compact');
      refreshMobileCardModeUI();
    });

    // Default to compact mode on mobile, and keep button state synced.
    if (isMobileViewport()) {
      $('body').addClass('mobile-card-compact');
    }
    refreshMobileCardModeUI();
    $(window).on('resize', function () {
      refreshMobileCardModeUI();
    });

    /* ======= SAVE per row ======= */
    $('#studentsTable').on('click', '.saveStudentBtn', function () {
      const $row = $(this).closest('tr');
      const fd = new FormData();

      fd.append('student_id', $row.find('[name="student_id"]').val());
      selectedColumns.forEach(c => fd.append('selected_fields[]', c));
      fd.append(CSRF_NAME, CSRF_HASH);

      if (selectedColumns.has('father_cnic')) {
        fd.append('selected_fields[]', 'f_name');
      }

      const cnicVal       = ($row.find('input[name="father_cnic"]').val() || '').trim();
      const pidVal        = ($row.find('input[name="parent_id"]').val() || '').trim();
      const fname         = ($row.find('input[name="f_name"]').val() || '').trim();
      const relinkChecked = $row.find('.cnic-clear-toggle').is(':checked');

      if (relinkChecked && cnicVal && !pidVal && !fname) {
        window.toastr && toastr.warning('Enter Father Name to create a new parent for CNIC ' + cnicVal + '.');
        return;
      }

      syncFromDisplayStudentFee($row, { format: true });

      selectedColumns.forEach(function (col) {
        if (['month_prev', 'month_curr', 'month_next'].includes(col)) return;
        if (col === 'profile_photo') {
          const input = $row.find('[name="profile_photo"]')[0];
          if (input && input.files && input.files.length > 0) fd.append('profile_photo', input.files[0]);
        } else {
          const $el = $row.find('[name="'+col+'"]');
          if (!$el.length) return;
          if (col === 'discounted_amount') {
            const classFee   = getClassFee($row);
            const studentFee = parseNum($row.find('[name="student_fee"]').val());
            const discount   = Math.max(0, classFee - studentFee);
            fd.append('discounted_amount', discount.toFixed(2));
            return;
          }
          fd.append(col, $el.val());
        }
      });

      if (relinkChecked) {
        fd.append('parent_link_intent', 'relink');
        if (pidVal) fd.append('parent_id', pidVal);
      } else {
        if (cnicVal) {
          fd.append('father_cnic', cnicVal);
          fd.append('selected_fields[]', 'father_cnic');
        }
      }

      const selectedMonthCols = getSelectedMonths().map(m => m.col);
      fd.append('selected_month_cols_json', JSON.stringify(selectedMonthCols));
      $row.find(':input[name^="months["]').each(function () {
        const $inp = $(this);
        const name = $inp.attr('name');
        if ($inp.is(':checkbox')) { if ($inp.is(':checked')) fd.append(name, $inp.val() || '1'); }
        else fd.append(name, $inp.val() || '');
      });
      fd.append('ref_month', $('#studentsList').data('curr-key') || '');

      $.ajax({
        url: "<?= base_url('admin/students_bulk_info/save_student_info') ?>",
        type: "POST",
        data: fd,
        contentType: false,
        processData: false,
        beforeSend: function () { $("#loader-1").show(); },
        success: function (res, _status, xhr) {
          $("#loader-1").hide();
          const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
          if (newToken) CSRF_HASH = newToken;

          if (res && res.success) {
            window.toastr && toastr.success(res.msg || 'Updated.');
          } else {
            const errs = res && res.errors ? JSON.stringify(res.errors) : '';
            window.toastr && toastr.error(((res && res.msg) || 'Error saving student info.') + (errs ? ' ' + errs : ''));
          }
        },
        error: function () {
          $("#loader-1").hide();
          window.toastr && toastr.error("AJAX error.");
        }
      });
    });

    /* (Optional) Auto-load by class on first paint if a class is preselected */
    if ($('#cls_sec_id').val()) {
      loadStudentsByClass();
    }
  });
})();
</script>


<?= $this->endSection() ?>
