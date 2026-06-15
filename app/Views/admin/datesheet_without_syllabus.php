<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>


<?php
// GET values
$mode = $_GET['mode'] ?? ''; // '' | 'without_syllabus'
$cls_sec_id   = $_GET['cls_sec_id']   ?? '';
$hide_marks   = $_GET['hide_marks']   ?? '';

$line_height = isset($_GET['line_height']) ? floatval($_GET['line_height']) : 1.6;
if ($line_height < 1.0) $line_height = 1.0;
if ($line_height > 3.0) $line_height = 3.0;
?>

<section class="content">
  <div class="container-fluid">

    <!-- Top actions / Filters -->
    <div class="page-actions no-print">
      <div class="card card-outline card-primary">
        <div class="card-body py-2">
          <form action="<?= base_url('admin/datesheet') ?>" method="get" class="row align-items-end">
            <?php if (!empty($_GET['mode'])): ?>
              <input type="hidden" name="mode" value="<?= esc($_GET['mode']) ?>">
            <?php endif; ?>
            
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs w-100 px-2 mb-3">
              <li class="nav-item">
                <a class="nav-link <?= $mode === '' ? 'active' : '' ?>" href="<?= base_url('admin/datesheet') ?>">
                  <i class="fas fa-id-card-alt me-1"></i> Admit Card
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $mode === 'without_syllabus' ? 'active' : '' ?>" href="<?= base_url('admin/datesheet?mode=without_syllabus') ?>">
                  <i class="fas fa-table me-1"></i> Admit Card Without Syllabus
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= url_is('admin/datesheet/add-syllabus') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet/add-syllabus') ?>">
                  <i class="fas fa-list-ul me-1"></i> Add Syllabus
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= url_is('admin/datesheet/add') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet/add') ?>">
                  <i class="far fa-calendar-plus me-1"></i> Add Datesheet
                </a>
              </li>
            </ul>

            <!-- Instructions Field (Top Priority) -->
            <div class="form-group col-md-12 mb-2">
              <div class="card card-secondary mb-0">
                <div class="card-header py-2">
                  <h3 class="card-title mb-0">
                    <i class="fas fa-info-circle me-1"></i> Exam Instructions
                    <?php if (!empty($examInstructions)): ?>
                      <span class="badge text-bg-success ms-2">Saved</span>
                    <?php endif; ?>
                  </h3>
                </div>
                <div class="card-body py-2">
                  <div class="row">
                    <div class="col-md-8">
                      <textarea class="form-control form-control-sm" name="instructions" rows="3" 
                                placeholder="Enter exam instructions here..."><?= esc($examInstructions ?? '') ?></textarea>
                      <small class="text-muted">Use separate lines for multiple instructions.</small>
                    </div>
                    <div class="col-md-2">
                      <div class="form-check mb-1">
                        <input type="checkbox" class="form-check-input" id="show_instructions" 
                               name="show_instructions" value="1" <?= ($showInstructions ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_instructions">Show Instructions</label>
                      </div>
                      <select class="form-control form-control-sm" name="instructions_position">
                        <option value="before" <?= ($instructionsPosition ?? 'after') === 'before' ? 'selected' : '' ?>>Before Datesheet</option>
                        <option value="after" <?= ($instructionsPosition ?? 'after') === 'after' ? 'selected' : '' ?>>After Datesheet</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                     <button type="button" class="btn btn-success btn-sm w-100" 
        onclick="saveInstructions()" id="save_instructions_btn">
    <i class="fas fa-save me-1"></i> Save
</button>
<div id="save_status" class="mt-1"></div>
                      <div id="save_status" class="mt-1"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Filter Controls (Single Row) -->
            <div class="form-group col-md-3">
              <label class="mb-0"><small><strong>Class</strong></small></label>
              <select class="form-control form-control-sm" name="cls_sec_id" id="cls_sec_id">
                <option value="">All Classes</option>
                <?php if (!empty($sectionsclassinfo)): foreach ($sectionsclassinfo as $row):
                  $id  = is_array($row) ? ($row['cls_sec_id'] ?? $row['section_id'] ?? '') : ($row->cls_sec_id ?? $row->section_id ?? '');
                  $lbl = is_array($row) ? ($row['sectionclassname'] ?? (($row['class_short_name'] ?? $row->class_name ?? '').' - '.($row['section_name'] ?? ''))) : ($row->sectionclassname ?? (($row->class_short_name ?? $row->class_name ?? '').' - '.($row->section_name ?? '')));
                ?>
                  <option value="<?= esc($id) ?>" <?= ($cls_sec_id == (string)$id ? 'selected' : '') ?>><?= esc($lbl) ?></option>
                <?php endforeach; endif; ?>
              </select>
            </div>

            <div class="form-group col-md-2">
              <label class="mb-0 d-block"><small><strong>Hide Marks</strong></small></label>
              <div class="form-check form-switch form-switch-md mt-1">
                <input type="checkbox" class="form-check-input" id="hide_marks_switch" 
                       name="hide_marks" value="1" <?= ($hide_marks=='1'?'checked':'') ?>>
                <label class="form-check-label" for="hide_marks_switch"></label>
              </div>
            </div>

            <div class="form-group col-md-2">
              <label class="mb-0"><small><strong>Line Height</strong></small></label>
              <input type="number" step="0.1" min="1" max="3" 
                     class="form-control form-control-sm" id="line_height_input" 
                     name="line_height" value="<?= esc($line_height) ?>">
            </div>
            
            <div class="form-group col-md-2">
              <label class="mb-0 d-block">&nbsp;</label>
              <button class="btn btn-primary btn-sm w-100" name="submit" value="view" type="submit">
                <i class="fas fa-eye me-1"></i> View
              </button>
            </div>

            <div class="form-group col-md-2">
              <label class="mb-0 d-block">&nbsp;</label>
              <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm w-100">
                <i class="fas fa-print me-1"></i> Print
              </button>
            </div>

            <div class="form-group col-md-1">
              <label class="mb-0 d-block">&nbsp;</label>
              <button type="button" onclick="window.location.href='<?= base_url('admin/datesheet/debugExamStatus') ?>'" 
                      class="btn btn-warning btn-sm w-100" title="Debug">
                <i class="fas fa-bug"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


    <!-- Admit Cards -->
    <?php if (!empty($data)): ?>
      <?php foreach ($data as $index => $value): ?>
        <?php
          $examName    = $value['terms'] ?? 'Exam';
          $schoolName  = $schoolinfo->system_name ?? ($value['campus_name'] ?? 'School');
          $campusName  = $value['campus_name'] ?? '';
          $campusLoc   = $value['campus_location'] ?? '';
          $campusPhone = $value['campus_phone'] ?? ($value['mobile_no'] ?? '');
          $profile     = $value['profile_photo'] ?? '';
          $dsRows      = $value['datesheetbysubject'] ?? [];
          $dues        = $value['remaining_dues'] ?? 0;

          $workingDays = $value['working_days'] ?? null;
          $cntA = $value['att_A'] ?? null; $cntL = $value['att_L'] ?? null;
          $cntLC = $value['att_LC'] ?? null; $cntEL = $value['att_EL'] ?? null;
        ?>
        
        <!-- Each admit card as separate page for printing -->
        <div class="admit-card<?= ($index > 0 ? ' page-break-before' : '') ?>">
          
          <div class="admit-header">
            <?php
              // Use system-logo/ directory for logos (not uploads/)
              $defaultLogo = base_url('uploads/logo_school.png');
              
              if (!empty($finalLogo)) {
                  // System logos are in system-logo/ directory
                  $logoUrl = base_url('system-logo/' . $finalLogo);
              } else {
                  $logoUrl = $defaultLogo;
              }
            ?>

            <div class="school-logo">
              <img src="<?= esc($logoUrl) ?>"
                   alt="School Logo"
                   onerror="this.onerror=null; this.src='<?= esc($defaultLogo) ?>';"
                   style="width:100%;height:100%;object-fit:contain;display:block;">
            </div>

            <div class="school-meta">
              <h1 class="english-text"><?= esc($schoolName) ?></h1>
              <div class="sub english-text">
                <?= esc($campusName) ?>
                <?php if ($campusLoc): ?>
                  <span class="dot">•</span> <?= esc($campusLoc) ?>
                <?php endif; ?>
                <?php if ($campusPhone): ?>
                  <span class="dot">•</span> <i class="fas fa-phone-alt"></i> <?= esc($campusPhone) ?>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Exam headline + dotted line -->
          <div class="headline">
            <span class="ribbon"><i class="far fa-id-card me-2"></i>Admit Card of <?= esc($examName) ?></span>
          </div>
          <hr class="header-sep">

          <div class="admit-body">
            <?= view('admin/partials/admit_card_bmi_strip', ['admit' => $value]) ?>
            <?= view('admin/partials/admit_card_attendance_strip', [
              'workingDays' => $workingDays,
              'cntA'          => $cntA,
              'cntL'          => $cntL,
              'cntLC'         => $cntLC,
              'cntEL'         => $cntEL,
              'dues'          => $dues,
            ]) ?>

            <!-- Student facts -->
            <div class="student-row">
              <div class="avatar">
                <?php if (!empty($profile)): ?>
                  <span class="avatar-photo-shell">
                    <img class="avatar-img" src="<?= base_url('uploads/'.$profile) ?>" alt=""
                         data-admit-avatar="1"
                         onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2245%22 fill=%22%23f0f0f0%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2236%22 fill=%22%23999%22%3E%3Ctspan%3E?%3C/tspan%3E%3C/text%3E%3C/svg%3E'">
                  </span>
                <?php else: ?>
                  <span class="avatar-placeholder" aria-hidden="true"><i class="fa fa-user"></i></span>
                <?php endif; ?>
              </div>

              <div class="facts facts-compact">
                <div class="fact"><b>Student</b> <?= esc($value['name'] ?? '-') ?></div>
                <div class="fact"><b>Reg #</b> <?= esc($value['reg_no'] ?? '-') ?></div>
                <div class="fact"><b>Father</b> <?= esc($value['f_name'] ?? '-') ?></div>

                <div class="fact"><b>Class</b> <?= esc($value['class'] ?? '-') ?></div>
                <div class="fact"><b>Contact 1</b> <?= esc($value['father_contact'] ?? '-') ?></div>
                <div class="fact"><b>Contact 2</b> <?= esc($value['mother_contact'] ?? '-') ?></div>
              </div>
            </div>
<!-- Instructions BEFORE datesheet (if enabled) -->
<!-- Instructions BEFORE datesheet (if enabled) -->
<?php if ($showInstructions && $instructionsPosition === 'before' && !empty($examInstructions)): ?>
    <div class="instructions-section">
        <div class="instructions-title english-text">
            <i class="fas fa-info-circle me-1"></i> Exam Instructions
        </div>
        <div class="instructions-content english-text" style="line-height: 1.2; margin: 0; padding: 0 0 0 15px;">
            <?php
            // Clean and display instructions with bullet points
            $lines = explode("\n", trim($examInstructions));
            $nonEmptyLines = [];
            
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    $nonEmptyLines[] = esc($trimmed);
                }
            }
            
            if (!empty($nonEmptyLines)) {
                echo '<ul style="margin: 0; padding: 0 0 0 20px; list-style-position: outside;">';
                foreach ($nonEmptyLines as $line) {
                    echo '<li style="margin: 1px 0; padding: 0; line-height: 1.2;">' . $line . '</li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
<?php endif; ?>

            <!-- Datesheet Table -->
            <div class="datesheet-wrap">
              <table class="datesheet-table compact relax">
                <?php if ($hide_marks == '1'): ?>
                  <!-- 3 COLUMNS when marks are hidden -->
                  <colgroup>
                    <col style="width: 33.33%">
                    <col style="width: 33.33%">
                    <col style="width: 33.33%">
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="english-text">Date</th>
                      <th class="english-text">Day</th>
                      <th class="english-text">Exam</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $map = [
                      'Sun'=>'Sunday','Mon'=>'Monday','Tue'=>'Tuesday',
                      'Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday','Sat'=>'Saturday'
                    ];

                    if (!empty($dsRows)):
                      foreach ($dsRows as $row):
                        $cols = array_values((array)$row);
                        $dateDayRaw = (string)($cols[0] ?? '');
                        $subjectRaw = (string)($cols[1] ?? '');

                        $datePart = $dateDayRaw;
                        $dayPart  = '';

                        if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/', $dateDayRaw, $m)) {
                          $datePart = trim($m[1]);
                          $dayPart  = trim($m[2]);
                        }

                        $fullDay = $map[$dayPart] ?? $dayPart;
                        
                        // Remove marks from subject when hidden
                        $subject = preg_replace('/\s*\(\d+\)\s*$/', '', $subjectRaw);
                    ?>
                      <tr>
                        <td class="english-text text-center"><?= esc($datePart) ?></td>
                        <td class="english-text text-center"><?= esc($fullDay) ?></td>
                        <td class="english-text text-center"><?= esc($subject) ?></td>
                      </tr>
                    <?php
                      endforeach;
                    else:
                    ?>
                      <tr>
                        <td colspan="3" class="text-muted text-center">No entries found.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                <?php else: ?>
                  <!-- 4 COLUMNS when marks are shown -->
                  <colgroup>
                    <col style="width: 25%">
                    <col style="width: 25%">
                    <col style="width: 25%">
                    <col style="width: 25%">
                  </colgroup>
                  <thead>
                    <tr>
                      <th class="english-text">Date</th>
                      <th class="english-text">Day</th>
                      <th class="english-text">Exam</th>
                      <th class="english-text">Total Marks</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $map = [
                      'Sun'=>'Sunday','Mon'=>'Monday','Tue'=>'Tuesday',
                      'Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday','Sat'=>'Saturday'
                    ];

                    if (!empty($dsRows)):
                      foreach ($dsRows as $row):
                        $cols = array_values((array)$row);
                        $dateDayRaw = (string)($cols[0] ?? '');
                        $subjectRaw = (string)($cols[1] ?? '');

                        $datePart = $dateDayRaw;
                        $dayPart  = '';

                        if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/', $dateDayRaw, $m)) {
                          $datePart = trim($m[1]);
                          $dayPart  = trim($m[2]);
                        }

                        $fullDay = $map[$dayPart] ?? $dayPart;

                        // Extract marks
                        $marks = '';
                        $subject = $subjectRaw;

                        if (preg_match('/\((\d+)\)\s*$/', $subjectRaw, $mm)) {
                          $marksVal = (int)$mm[1];
                          $subject  = trim(preg_replace('/\s*\(\d+\)\s*$/', '', $subjectRaw));
                          $marks = $marksVal;
                        }
                    ?>
                      <tr>
                        <td class="english-text text-center"><?= esc($datePart) ?></td>
                        <td class="english-text text-center"><?= esc($fullDay) ?></td>
                        <td class="english-text text-center"><?= esc($subject) ?></td>
                        <td class="english-text text-center">
                          <?= $marks !== '' ? esc($marks) : '-' ?>
                        </td>
                      </tr>
                    <?php
                      endforeach;
                    else:
                    ?>
                      <tr>
                        <td colspan="4" class="text-muted text-center">No entries found.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                <?php endif; ?>
              </table>
            </div>

        <!-- Instructions AFTER datesheet (if enabled) -->
<?php if ($showInstructions && $instructionsPosition === 'after' && !empty($examInstructions)): ?>
    <div class="instructions-section">
        <div class="instructions-title english-text">
            <i class="fas fa-info-circle me-1"></i> Exam Instructions
        </div>
        <div class="instructions-content english-text" style="line-height: 1.2; margin: 0; padding: 0 0 0 15px;">
            <?php
            // Clean and display instructions with bullet points
            $lines = explode("\n", trim($examInstructions));
            $nonEmptyLines = [];
            
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '') {
                    $nonEmptyLines[] = esc($trimmed);
                }
            }
            
            if (!empty($nonEmptyLines)) {
                echo '<ul style="margin: 0; padding: 0 0 0 20px; list-style-position: outside;">';
                foreach ($nonEmptyLines as $line) {
                    echo '<li style="margin: 1px 0; padding: 0; line-height: 1.2;">' . $line . '</li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
<?php endif; ?>
          </div> <!-- /.admit-body -->
        </div>   <!-- /.admit-card -->
      <?php endforeach; ?>

    <?php else: ?>
      <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i> No records to display. Choose a class and click "View".</div>
    <?php endif; ?>

  </div>
</section>

<script>
// Save instructions via GET parameters
// Save instructions via GET parameters

function saveInstructions() {
    console.log('saveInstructions function called');
    
    // Get instruction values
    const instructions = document.querySelector('textarea[name="instructions"]').value;
    const showInstructions = document.querySelector('input[name="show_instructions"]').checked ? 1 : 0;
    const position = document.querySelector('select[name="instructions_position"]').value;
    
    // Get all form values
    const form = document.querySelector('form[action*="datesheet"]');
    const formData = new FormData(form);
    
    // Convert to URL parameters
    const params = new URLSearchParams();
    
    // Add all form data
    for (const [key, value] of formData.entries()) {
        if (key !== 'submit') { // Skip the submit button
            params.append(key, value);
        }
    }
    
    // Update instruction values
    params.set('instructions', instructions);
    params.set('show_instructions', showInstructions);
    params.set('instructions_position', position);
    params.set('save_instructions', '1');
    
    // Show saving message
    const statusDiv = document.getElementById('save_status');
    if (statusDiv) {
        statusDiv.innerHTML = `
            <div class="alert alert-info alert-dismissible fade show py-2 mb-0">
                <div class="d-flex align-items-center">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    <div>
                        <strong>Saving instructions...</strong>
                        <div class="small text-muted">Please wait while we save your changes.</div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Disable save button
    const saveBtn = document.getElementById('save_instructions_btn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    }
    
    // Redirect directly
    const url = form.action + '?' + params.toString();
    console.log('Redirecting to:', url);
    window.location.href = url;
}



// Show status message
function showStatus(message, type = 'info') {
    const statusDiv = document.getElementById('save_status');
    if (!statusDiv) return;
    
    const alertClass = {
        'success': 'alert-success',
        'danger': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const iconClass = {
        'success': 'fa-check-circle',
        'danger': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    statusDiv.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show py-2 mb-0">
            <div class="d-flex align-items-center">
                <i class="fas ${iconClass} me-2"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="close" data-bs-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    // Auto-dismiss
    setTimeout(() => {
        const alert = statusDiv.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => statusDiv.innerHTML = '', 300);
        }
    }, 5000);
}

// Debug function to test the save
function testSave() {
    console.log('=== TESTING SAVE FUNCTION ===');
    console.log('Page URL:', window.location.href);
    console.log('Form action:', document.querySelector('form[action*="datesheet"]')?.action);
    console.log('Instruction elements:');
    console.log('- Textarea:', document.querySelector('textarea[name="instructions"]'));
    console.log('- Checkbox:', document.querySelector('input[name="show_instructions"]'));
    console.log('- Select:', document.querySelector('select[name="instructions_position"]'));
    
    // Create a test form submission
    const testForm = document.createElement('form');
    testForm.method = 'GET';
    testForm.action = document.querySelector('form[action*="datesheet"]')?.action || window.location.href.split('?')[0];
    
    // Add test parameters
    const params = new URLSearchParams(window.location.search);
    params.set('save_instructions', '1');
    params.set('instructions', 'TEST INSTRUCTIONS ' + new Date().toLocaleTimeString());
    params.set('show_instructions', '1');
    params.set('instructions_position', 'before');
    
    testForm.action = testForm.action.split('?')[0] + '?' + params.toString();
    
    console.log('Test URL:', testForm.action);
    
    // Open in new tab for testing
    window.open(testForm.action, '_blank');
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function() {
    console.log('=== PAGE LOADED ===');
    
    // Show flash message if exists
    <?php if (!empty($save_success)): ?>
        console.log('Flash message found:', <?= json_encode($save_success) ?>);
        showStatus(<?= json_encode($save_success) ?>, <?= json_encode($save_success_type ?? 'info') ?>);
        
        // Update badge
        const cardHeader = document.querySelector('.card-title');
        if (cardHeader && <?= json_encode($save_success_type ?? '') ?> === 'success') {
            let badge = cardHeader.querySelector('.badge');
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge text-bg-success ms-2';
                cardHeader.appendChild(badge);
            }
            badge.textContent = 'Saved';
        }
    <?php endif; ?>
    
    // Add debug button (remove in production)
    const debugBtn = document.createElement('button');
    debugBtn.type = 'button';
    debugBtn.className = 'btn btn-sm btn-warning mt-2';
    debugBtn.innerHTML = '<i class="fas fa-bug me-1"></i> Debug Save';
    debugBtn.onclick = testSave;
    
    const statusDiv = document.getElementById('save_status');
    if (statusDiv) {
        statusDiv.parentNode.appendChild(debugBtn);
    }
    
    // Log current state
    console.log('Current exam instructions:', <?= json_encode($examInstructions ?? '') ?>);
    console.log('Show instructions:', <?= $showInstructions ? 'true' : 'false' ?>);
    console.log('Position:', <?= json_encode($instructionsPosition ?? 'after') ?>);
});


</script>
<script>
(function () {
  var ADMIT_AVATAR_MAX = 92;
  function fitAdmitAvatarImages() {
    document.querySelectorAll('img.avatar-img[data-admit-avatar="1"]').forEach(function (img) {
      function apply() {
        var nw = img.naturalWidth;
        var nh = img.naturalHeight;
        if (!nw || !nh) {
          return;
        }
        var s = Math.min(ADMIT_AVATAR_MAX / nw, ADMIT_AVATAR_MAX / nh, 1);
        img.style.width = Math.round(nw * s) + 'px';
        img.style.height = Math.round(nh * s) + 'px';
        img.style.maxWidth = 'none';
        img.style.maxHeight = 'none';
      }
      if (img.complete && img.naturalWidth) {
        apply();
      } else {
        img.addEventListener('load', apply, { once: true });
      }
    });
  }
  window.fitAdmitAvatarImages = fitAdmitAvatarImages;
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(fitAdmitAvatarImages, 0); });
  } else {
    setTimeout(fitAdmitAvatarImages, 0);
  }
  window.addEventListener('load', function () {
    setTimeout(fitAdmitAvatarImages, 100);
    setTimeout(fitAdmitAvatarImages, 500);
  });
  window.addEventListener('beforeprint', fitAdmitAvatarImages);
})();
</script>
<style type="text/css">

  /* Instructions with bullets styling */
.instructions-content ul {
    margin: 0 !important;
    padding: 0 0 0 20px !important;
    list-style-type: disc !important;
    list-style-position: outside !important;
}

.instructions-content li {
    margin: 2px 0 !important;
    padding: 0 !important;
    line-height: 1.2 !important;
    font-size: 14px !important;
}
  /* Compact form styling */
.form-control-sm {
    height: calc(1.5em + .5rem + 2px);
    padding: .25rem .5rem;
    font-size: .875rem;
}

.card-header.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.card-body.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

/* Compact switch */
.form-switch.form-switch-md .form-check-label {
    padding-left: 1.5rem;
    padding-bottom: 0;
    line-height: 1.5rem;
}

.form-switch.form-switch-md .form-check-label::before {
    height: 1.2rem;
    width: 2.5rem;
    border-radius: .6rem;
}

.form-switch.form-switch-md .form-check-label::after {
    width: 1rem;
    height: 1rem;
    border-radius: .5rem;
    left: .15rem;
}

.form-switch.form-switch-md .form-check-input:checked ~ .form-check-label::after {
    transform: translateX(1.2rem);
}

/* Alert styling */
.alert.py-1 {
    padding-top: 0.25rem !important;
    padding-bottom: 0.25rem !important;
    font-size: 0.875rem;
}

/* Form group spacing */
.form-group {
    margin-bottom: 0.5rem;
}

/* Instructions textarea */
textarea[name="instructions"] {
    resize: vertical;
    min-height: 80px;
    max-height: 150px;
}

/* ===============================
   Instructions Styling
   =============================== */
.instructions-section {
  margin: 20px 0;
  padding: 15px;
  border: 2px dashed #4CAF50;
  border-radius: 8px;
  background-color: #f8fff8;
}

.instructions-title {
  font-size: 18px;
  font-weight: bold;
  color: #2E7D32;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
}

.instructions-title i {
  color: #4CAF50;
}

.instructions-content {
  font-size: 15px;
  line-height: 1.6;
  color: #333;
  white-space: pre-line;
}

.instructions-content ul,
.instructions-content ol {
  padding-left: 20px;
  margin-bottom: 10px;
}

.instructions-content li {
  margin-bottom: 5px;
}

/* Collapsible instructions panel */
.card-secondary:not(.collapsed-card) .card-header {
  background-color: #6c757d !important;
}

.card-secondary:not(.collapsed-card) .card-title {
  color: white;
}

/* ===============================
   Datesheet Table Dynamic Columns
   =============================== */
.datesheet-wrap {
  margin-top: 12px;
  padding: 12px 14px 14px;
  border-top: 4px double #000;
  border-start: 3px solid #000;
  border-end: 1px solid #000;
  border-bottom: none;
  background: #fff;
}

.datesheet-table {
  width: 100%;
  border-collapse: collapse !important;
  border: none !important;
  table-layout: fixed;
}

.datesheet-table thead th {
  background: #f2f2f2 !important;
  border: none !important;
  border-bottom: 3px double #000 !important;
  font-weight: 800;
  text-align: center;
  padding: 10px 5px !important;
}

.datesheet-table td {
  border: none !important;
  border-bottom: 1px dotted #94a3b8 !important;
  border-end: none !important;
  padding: 10px 5px !important;
  font-size: 15px;
  vertical-align: middle;
  text-align: center;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.datesheet-table thead th:not(:last-child),
.datesheet-table tbody td:not(:last-child) {
  border-end: 1px solid #000 !important;
}

.datesheet-table tbody tr:last-child td {
  border-bottom: none !important;
}

/* 3 columns layout */
.datesheet-table.cols-3 th:nth-child(1),
.datesheet-table.cols-3 td:nth-child(1) {
  width: 33.33% !important;
}
.datesheet-table.cols-3 th:nth-child(2),
.datesheet-table.cols-3 td:nth-child(2) {
  width: 33.33% !important;
}
.datesheet-table.cols-3 th:nth-child(3),
.datesheet-table.cols-3 td:nth-child(3) {
  width: 33.33% !important;
}

/* 4 columns layout */
.datesheet-table.cols-4 th:nth-child(1),
.datesheet-table.cols-4 td:nth-child(1) {
  width: 25% !important;
}
.datesheet-table.cols-4 th:nth-child(2),
.datesheet-table.cols-4 td:nth-child(2) {
  width: 25% !important;
}
.datesheet-table.cols-4 th:nth-child(3),
.datesheet-table.cols-4 td:nth-child(3) {
  width: 25% !important;
}
.datesheet-table.cols-4 th:nth-child(4),
.datesheet-table.cols-4 td:nth-child(4) {
  width: 25% !important;
}

/* Better readability + subtle striping */
.datesheet-table.compact.relax tbody tr:nth-child(odd) td{
  background: #fbfcff;
}
.datesheet-table.compact.relax tbody tr:hover td{
  background: #f3f7ff;
}
/* ===============================
   Logo Fixes
   =============================== */
.school-logo {
  width: 96px;
  height: 96px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent !important;
  border: none !important;
  border-radius: 0 !important;
  overflow: visible;
  padding: 0;
  box-shadow: none !important;
}

.school-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain !important;
  background: white !important;
  display: block;
}

/* ---- Palette ---- */
:root{
  --ink:#0f172a; --ink-2:#111827; --muted:#475569;
  --line:#94a3b8; --line-2:#cbd5e1; --chip:#cfd8e3;
  --bg-soft:#f8fafc; --card:#ffffff; --accent:#1d4ed8;
  --danger:#b91c1c; --danger-weak:#ef4444;
}

/* ---- Layout wrappers (AdminLTE safe) ---- */
html, body, .content-wrapper, .content, .container-fluid{ 
  background:#fff !important; 
}
.content{ 
  padding:0 !important; 
}

/* ---- Typography ---- */
.urdu-text {
  font-family: 'Jameel Noori Nastaleeq', 'Jameel Noori Nastaleeq Kasheeda', 'Nafees', 'Alvi', 'Urdu', serif !important;
  font-size: 1.2em;
  line-height: 1.8;
  direction: rtl;
  text-align: right;
}

.urdu-syllabus {
  font-family: 'Jameel Noori Nastaleeq', 'Jameel Noori Nastaleeq Kasheeda', 'Nafees', 'Alvi', 'Urdu', serif !important;
  font-size: 1.3em;
  line-height: 2.0;
  direction: rtl;
  text-align: right;
  letter-spacing: 0.02em;
}

.english-text {
  font-family: 'Times New Roman', Times, serif !important;
  line-height: 1.4;
}

/* ---- Actions ---- */
.page-actions{ 
  position:sticky; 
  top:0; 
  z-index:5; 
  background:#fff; 
  padding:8px 0 0; 
  margin-bottom:8px; 
  border-bottom: 1px solid var(--line-2);
}

/* ---- Card ---- */
.admit-card{
  max-width:1100px; 
  margin:16px auto; 
  background:var(--card);
  border-radius:12px; 
  border:1px solid var(--line-2);
  box-shadow:0 6px 18px rgba(0,0,0,.06); 
  overflow:hidden;
  font-family: 'Times New Roman', Times, serif;
}

/* ---- Header ---- */
.admit-header{
  background:#fff; 
  color:#111; 
  padding:16px 16px 10px;
  display:grid; 
  grid-template-columns:96px 1fr; 
  gap:16px; 
  align-items:center;
  border-bottom: 2px solid var(--accent);
}

.school-logo{
  width:96px; 
  height:96px;
  display:flex; 
  align-items:center; 
  justify-content:center;
  background:transparent !important;
  border:none !important;
  border-radius:0 !important;
  overflow:visible;
  padding:0;
  box-shadow:none !important;
}
.school-logo img{
  width:100%; 
  height:100%;
  object-fit:contain !important;
  background:white !important;
  display:block;
}
.school-meta{ 
  display:flex; 
  flex-direction:column; 
  align-items:center; 
  justify-content:center; 
  text-align:center; 
}
.school-meta h1{ 
  font-size:42px; 
  line-height:1.1; 
  margin:0 0 4px; 
  font-weight:800; 
  letter-spacing:.3px; 
  color:var(--ink-2); 
  font-family: 'Times New Roman', Times, serif;
}
.school-meta .sub{ 
  font-size:18px; 
  line-height:1.2; 
  margin:0; 
  opacity:.95; 
  white-space:nowrap; 
  color:var(--muted); 
  font-family: 'Times New Roman', Times, serif;
}
.school-meta .sub .dot{ 
  margin:0 6px; 
  color:#94a3b8; 
}

.headline{ 
  text-align:center; 
  font-weight:800; 
  color:var(--ink); 
  margin:6px 16px 10px; 
  font-size:18px; 
  font-family: 'Times New Roman', Times, serif;
}
.headline .ribbon{ 
  display:inline-block; 
  padding:6px 14px; 
  border:2px solid var(--accent); 
  border-radius:999px; 
  font-weight:800; 
  letter-spacing:.2px; 
  background: var(--bg-soft);
}
.header-sep{ 
  border:0; 
  border-top:2px dotted #b6c2d6; 
  margin:6px 0 0; 
}

/* ---- Body ---- */
.admit-body{ 
  padding:16px; 
  position:relative; 
}

/* Dues badge (corner of attendance strip) */
.due-badge{
  position:absolute; 
  top:8px; 
  right:12px; 
  background:#fff;
  border:1px dashed var(--danger-weak); 
  color:var(--danger);
  font-size:12px; 
  padding:4px 8px; 
  border-radius:8px; 
  display:flex; 
  align-items:center; 
  gap:6px;
  font-family: 'Times New Roman', Times, serif;
}

/* ---- Student row — border hugs scaled photo ---- */
.admit-card .student-row{ 
  display:grid; 
  grid-template-columns: auto 1fr; 
  column-gap:12px; 
  align-items:start; 
  margin-bottom:12px; 
}
.admit-card .avatar{ 
  margin: 0;
  line-height: 0;
}
.admit-card .avatar-photo-shell {
  display: inline-block;
  vertical-align: top;
  border: 2px solid #1e293b;
  border-radius: 6px;
  overflow: hidden;
  line-height: 0;
  background: #f8fafc;
}
.admit-card .avatar-photo-shell .avatar-img {
  display: block;
  width: auto;
  height: auto;
  max-width: 92px;
  max-height: 92px;
  object-fit: contain;
  vertical-align: top;
}
.admit-card .avatar-placeholder {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 72px;
  height: 72px;
  border: 2px solid #1e293b;
  border-radius: 6px;
  background: #f8fafc;
  color: #64748b;
}
.admit-card .avatar-placeholder i {
  font-size: 32px;
  opacity: 0.65;
}

/* Facts */
.facts{ 
  display:grid; 
  grid-template-columns:repeat(2, minmax(240px,1fr)); 
  gap:8px 24px; 
}

.facts-compact{ 
  display:grid; 
  grid-template-columns:repeat(3, minmax(0,1fr)); 
  gap:6px 12px; 
  line-height:1.5; 
  font-size:16px; 
  padding-top:2px; 
  font-family: 'Times New Roman', Times, serif;
}
.fact{
  font-size:14px; 
  background:var(--bg-soft); 
  border:1px solid #e6ebf2;
  padding:8px 10px; 
  border-radius:8px; 
  color:var(--ink);
  font-family: 'Times New Roman', Times, serif;
}
.fact i{ 
  color:#64748b; 
  margin-right:6px; 
}
.facts-compact .fact{
  display: inline-flex;
  align-items: baseline;
  gap: 6px;
  padding: 6px 8px;
}

.facts-compact .fact b{
  min-width: 0;
  margin-right: 0;
  white-space: initial;
  font-weight: 700;
}

.facts-compact .fact b::after{
  content: ":";
  margin-left: 2px;
}

/* BMI + Attendance strips — same horizontal band */
.admit-bmi-strip,
.admit-attendance-strip {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 16px;
  margin-bottom: 12px;
  padding: 8px 12px 8px 14px;
  border: none;
  border-start: 4px double #1e293b;
  border-top: 1px dotted #cbd5e1;
  border-bottom: 1px solid #94a3b8;
  border-radius: 0;
  background: #f8fafc;
  font-size: 14px;
  color: var(--ink);
}

.admit-attendance-strip {
  position: relative;
}

.admit-attendance-strip--dues {
  padding-right: 88px;
}

.admit-bmi-title,
.admit-attendance-title {
  font-weight: 800;
  margin-right: 4px;
}

.admit-bmi-title i,
.admit-attendance-title i {
  margin-right: 6px;
  color: #334155;
}

.admit-bmi-items,
.admit-attendance-items {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 14px;
  align-items: baseline;
}

.admit-bmi-item b,
.admit-attendance-item b {
  font-weight: 700;
  margin-right: 4px;
}

/* ---- Chips / badges ---- */
.chips{ 
  margin:6px 0 10px; 
  display:flex; 
  gap:10px; 
  flex-wrap:wrap; 
  justify-content:flex-start; 
}
.chip{ 
  display:inline-flex; 
  align-items:center; 
  gap:8px; 
  padding:6px 10px; 
  border:1px solid var(--chip); 
  border-radius:10px; 
  background:#fff; 
  font-size:13px; 
  color:var(--ink); 
  font-family: 'Times New Roman', Times, serif;
}
.chip i{ 
  color:#0ea5e9; 
}
.chip.badge-wd i{ 
  color:#2563eb; 
}
.chip.badge-A{ 
  border-color:#fecaca; 
} 
.chip.badge-A i{ 
  color:#dc2626; 
}
.chip.badge-L{ 
  border-color:#fde68a; 
} 
.chip.badge-L i{ 
  color:#d97706; 
}
.chip.badge-LC{ 
  border-color:#bbf7d0; 
} 
.chip.badge-LC i{ 
  color:#16a34a; 
}
.chip.badge-EL{ 
  border-color:#e9d5ff; 
} 
.chip.badge-EL i{ 
  color:#7c3aed; 
}

/* ---- Footer & sign ---- */
.footer-lines{ 
  margin-top:12px; 
  color:var(--ink); 
  font-size:14px; 
  font-family: 'Times New Roman', Times, serif;
}
.sign-row{ 
  display:grid; 
  grid-template-columns:repeat(3,1fr); 
  gap:16px; 
  margin-top:18px; 
}
.sign-box {
  border-top: 2px solid #000 !important;
  font-weight: bold;
  color: #000 !important;
}

/* ===============================
   PRINT STYLES
   =============================== */
@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }
  
  body {
    height: auto !important;
    min-height: auto !important;
  }

  html, body {
    width: 210mm !important;
    height: 297mm !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
  }
  
  body * {
    visibility: visible !important;
  }
  
  .no-print,
  .page-actions,
  .card.card-outline.card-primary,
  .nav.nav-tabs,
  .row,
  .alert.alert-info {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    width: 0 !important;
    overflow: hidden !important;
    position: absolute !important;
  }
  
  .admit-card {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 1px solid #000 !important;
    border-start: 4px double #000 !important;
    page-break-after: always !important;
    break-after: page !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    box-shadow: none !important;
    filter: none !important;
  }

  .admit-card:last-child {
    page-break-after: auto !important;
    break-after: auto !important;
  }
  
  .admit-card.page-break-before {
    page-break-before: always !important;
    break-before: page !important;
  }
  
  .admit-header {
    display: grid !important;
    grid-template-columns: 96px 1fr !important;
    gap: 16px !important;
    padding: 16px 16px 10px !important;
    border-bottom: 3px double #000 !important;
    page-break-inside: avoid !important;
  }

  .admit-card .avatar-photo-shell {
    border: 2px solid #000 !important;
    background: #fafafa !important;
  }

  .headline .ribbon {
    border: 2px double #000 !important;
    border-radius: 0 !important;
    background: #fff !important;
    color: #000 !important;
    box-shadow: none !important;
  }

  .fact {
    border: none !important;
    border-start: 3px solid #000 !important;
    border-top: 1px dotted #000 !important;
    border-bottom: 1px dotted #000 !important;
    border-radius: 0 !important;
    background: #fff !important;
    color: #000 !important;
  }
  
  .admit-body {
    padding: 16px !important;
    display: block !important;
    visibility: visible !important;
  }
  
  .school-logo {
    background: white !important;
    border: none !important;
    box-shadow: none !important;
    display: flex !important;
  }

  .admit-bmi-strip,
  .admit-attendance-strip,
  .admit-bmi-strip .admit-bmi-title i,
  .admit-attendance-strip .admit-attendance-title i {
    background: #fff !important;
    color: #000 !important;
    border: none !important;
    border-start: 4px double #000 !important;
    border-top: 1px dotted #000 !important;
    border-bottom: 1px solid #000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .due-badge {
    background: #fff !important;
    color: #000 !important;
    border: 1px dashed #000 !important;
  }

  .datesheet-wrap {
    border-top: 4px double #000 !important;
    border-start: 3px solid #000 !important;
    border-end: 1px solid #000 !important;
    border-bottom: none !important;
    padding: 10px 12px !important;
    background: #fff !important;
  }

  .datesheet-table thead th {
    background: #fff !important;
    color: #000 !important;
    border: none !important;
    border-bottom: 3px double #000 !important;
  }

  .datesheet-table td {
    border: none !important;
    border-bottom: 1px dotted #000 !important;
    color: #000 !important;
    background: #fff !important;
  }

  .datesheet-table thead th:not(:last-child),
  .datesheet-table tbody td:not(:last-child) {
    border-end: 1px solid #000 !important;
  }

  .datesheet-table tbody tr:last-child td {
    border-bottom: none !important;
  }

  .school-logo img {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background: white !important;
  }

  .datesheet-table {
    border: none !important;
    page-break-inside: avoid !important;
    display: table !important;
  }
  
  .datesheet-table thead {
    display: table-header-group !important;
  }
  
  .datesheet-table tbody {
    display: table-row-group !important;
  }
  
  .datesheet-table tr {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }
  
  @page {
    size: A4 portrait;
    margin-top: 10mm;
    margin-left: 10mm;
    margin-right: 10mm;
    margin-bottom: 10mm;
  }
  
  .content,
  .container-fluid {
    width: 100% !important;
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  
  .print-watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    opacity: 0.05;
    z-index: -1;
    font-size: 60px;
    color: #ccc;
    white-space: nowrap;
    pointer-events: none;
  }
}

/* Screen view adjustments */
@media screen {
  .admit-card {
    margin-bottom: 30px;
  }
  
  .print-watermark {
    display: none;
  }
}

/* Watermark styling */
.print-watermark {
  display: none;
}

.watermark-text {
  font-family: 'Times New Roman', Times, serif;
  font-weight: bold;
  color: rgba(0,0,0,0.1);
  text-align: center;
}
</style>
<style id="lh-override">
  .datesheet-table.compact.relax td,
  .datesheet-table.compact.relax th {
      line-height: <?= $line_height ?> !important;
  }

  @media print{
    .datesheet-table.compact.relax td,
    .datesheet-table.compact.relax th {
        line-height: <?= $line_height ?> !important;
    }
  }
</style>
<?= $this->endSection() ?>