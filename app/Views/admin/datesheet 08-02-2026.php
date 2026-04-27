<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
// GET values
$cls_sec_id   = $_GET['cls_sec_id']   ?? '';
$hide_marks   = $_GET['hide_marks']   ?? '';
$footer_line1 = $_GET['footer_line1'] ?? '';
$footer_line2 = $_GET['footer_line2'] ?? '';
$show_line1   = $_GET['show_line1']   ?? '';
$show_line2   = $_GET['show_line2']   ?? '';

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
          <form action="<?= base_url('admin/datesheet') ?>" method="get" class="form-row align-items-end">
            <?php helper('url'); ?>
            <ul class="nav nav-tabs w-100 px-2 mb-3">
              <li class="nav-item"><a class="nav-link <?= url_is('admin/datesheet') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet') ?>"><i class="fas fa-id-card-alt mr-1"></i> Admit Card</a></li>
              <li class="nav-item"><a class="nav-link <?= url_is('admin/datesheet2') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet2') ?>"><i class="far fa-id-card mr-1"></i> Admit Card 2</a></li>
              <li class="nav-item"><a class="nav-link <?= url_is('admin/datesheet/without-syllabus') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet/without-syllabus') ?>"><i class="fas fa-table mr-1"></i> Admit Card Without Syllabus</a></li>
              <li class="nav-item"><a class="nav-link <?= url_is('admin/datesheet/add-syllabus') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet/add-syllabus') ?>"><i class="fas fa-list-ul mr-1"></i> Add Syllabus</a></li>
              <li class="nav-item"><a class="nav-link <?= url_is('admin/datesheet/add') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet/add') ?>"><i class="far fa-calendar-plus mr-1"></i> Add Datesheet</a></li>
            </ul>

            <div class="form-group col-md-4">
              <label class="mb-1"><strong>Class</strong></label>
              <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                <option value="">All Classes</option>
                <?php if (!empty($sectionsclassinfo)): foreach ($sectionsclassinfo as $row):
                  $id  = is_array($row) ? ($row['cls_sec_id'] ?? $row['section_id'] ?? '') : ($row->cls_sec_id ?? $row->section_id ?? '');
                  $lbl = is_array($row) ? ($row['sectionclassname'] ?? (($row['class_short_name'] ?? $row['class_name'] ?? '').' - '.($row['section_name'] ?? ''))) : ($row->sectionclassname ?? (($row->class_short_name ?? $row->class_name ?? '').' - '.($row->section_name ?? '')));
                ?>
                  <option value="<?= esc($id) ?>" <?= ($cls_sec_id == (string)$id ? 'selected' : '') ?>><?= esc($lbl) ?></option>
                <?php endforeach; endif; ?>
              </select>
            </div>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block"><strong>Hide Marks</strong></label>
              <input type="checkbox" name="hide_marks" value="1" <?= ($hide_marks=='1'?'checked':'') ?> />
            </div>

            <div class="form-group col-md-3">
              <label class="mb-1">Footer Line 1</label>
              <input type="text" class="form-control" name="footer_line1" value="<?= esc($footer_line1) ?>" placeholder="e.g. Best of luck!">
            </div>

            <div class="form-group col-md-3">
              <label class="mb-1">Footer Line 2</label>
              <input type="text" class="form-control" name="footer_line2" value="<?= esc($footer_line2) ?>" placeholder="e.g. Bring your admit card.">
            </div>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block">Show Line 1</label>
              <input type="checkbox" name="show_line1" value="1" <?= ($show_line1=='1'?'checked':'') ?> />
            </div>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block">Show Line 2</label>
              <input type="checkbox" name="show_line2" value="1" <?= ($show_line2=='1'?'checked':'') ?> />
            </div>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block"><strong>Line Height</strong></label>
              <input type="number" step="0.1" min="1" max="3" 
                     class="form-control" id="line_height_input" 
                     name="line_height" value="<?= esc($_GET['line_height'] ?? '2.0') ?>">
            </div>
            <div class="form-group col-sm-2">
              <button class="btn btn-primary btn-block" name="submit" value="view" type="submit">
                <i class="fas fa-eye mr-1"></i> View
              </button>
            </div>

            <div class="form-group col-sm-2 ml-auto">
              <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-block">
                <i class="fas fa-print mr-1"></i> Print
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
          
          <!-- Fixed Logo Section -->
          <div class="admit-header">
            <?php
              // FIXED LOGO PATH HANDLING
              $defaultLogo = base_url('uploads/logo_school.png');
              $logoUrl = $defaultLogo;
              
              // Check multiple possible logo locations
              if (!empty($schoolinfo->logo) && file_exists(ROOTPATH . 'public/uploads/' . $schoolinfo->logo)) {
                  $logoUrl = base_url('uploads/' . $schoolinfo->logo);
              } elseif (!empty($value['campus_logo']) && file_exists(ROOTPATH . 'public/uploads/' . $value['campus_logo'])) {
                  $logoUrl = base_url('uploads/' . $value['campus_logo']);
              } elseif (!empty($systemLogo) && file_exists(ROOTPATH . 'public/uploads/' . $systemLogo)) {
                  $logoUrl = base_url('uploads/' . $systemLogo);
              }
            ?>
            <div class="school-logo">
              <img src="<?= esc($logoUrl) ?>" alt="School Logo"
                   onerror="this.onerror=null;this.src='<?= esc($defaultLogo) ?>';"
                   style="width: 100%; height: 100%; object-fit: contain; display: block;">
            </div>
            
            <div class="school-meta">
              <h1 class="english-text"><?= esc($schoolName) ?></h1>
              <div class="sub english-text">
                <?= esc($campusName) ?>
                <?php if ($campusLoc): ?><span class="dot">•</span> <?= esc($campusLoc) ?><?php endif; ?>
                <?php if ($campusPhone): ?><span class="dot">•</span> <i class="fas fa-phone-alt"></i> <?= esc($campusPhone) ?><?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Exam headline + dotted line -->
          <div class="headline">
            <span class="ribbon"><i class="far fa-id-card mr-2"></i>Admit Card of <?= esc($examName) ?></span>
          </div>
          <hr class="header-sep">

          <div class="admit-body">
            <!-- Student facts -->
            <div class="student-row">
              <div class="avatar">
                <?php if (!empty($profile)): ?>
                  <img src="<?= base_url('uploads/'.$profile) ?>" alt="Student Photo" 
                       onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2245%22 fill=%22%23f0f0f0%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2236%22 fill=%22%23999%22%3E%3Ctspan%3E?%3C/tspan%3E%3C/text%3E%3C/svg%3E'">
                <?php else: ?>
                  <i class="fa fa-user" style="font-size: 40px; color: #666;"></i>
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

            <!-- Attendance Summary -->
            <div class="att-wrap">
              <div class="att-title english-text"><i class="fas fa-clipboard-check mr-1"></i> Attendance Summary </div>
              <?php if ((float)$dues > 0): ?>
                <div class="due-badge" title="Remaining dues">
                  <span><?= number_format((float)$dues) ?></span>
                </div>
              <?php endif; ?>
              <div class="chips">
                <?php if ($workingDays !== null): ?>
                  <div class="chip badge-wd" title="Working days"><i class="fas fa-business-time"></i> Working Days: <strong><?= (int)$workingDays ?></strong></div>
                <?php endif; ?>
                <?php if ($cntA !== null): ?><div class="chip badge-A"><i class="fas fa-user-slash"></i> A: <strong><?= (int)$cntA ?></strong></div><?php endif; ?>
                <?php if ($cntL !== null): ?><div class="chip badge-L"><i class="fas fa-door-open"></i> L: <strong><?= (int)$cntL ?></strong></div><?php endif; ?>
                <?php if ($cntLC !== null): ?><div class="chip badge-LC"><i class="fas fa-clock"></i> LC: <strong><?= (int)$cntLC ?></strong></div><?php endif; ?>
                <?php if ($cntEL !== null): ?><div class="chip badge-EL"><i class="fas fa-walking"></i> EL: <strong><?= (int)$cntEL ?></strong></div><?php endif; ?>
              </div>
            </div>

            <!-- Datesheet -->
            <div class="datesheet-wrap">
              <table class="datesheet-table compact relax">
                <colgroup>
                  <col class="col-date" style="width:10.5ch">
                  <col class="col-subject" style="max-width:40%">
                  <col class="col-syll">
                </colgroup>
                <thead>
                  <tr>
                    <th class="english-text">Date & Day</th>
                    <th class="english-text">Subject</th>
                    <th class="english-text">Exam Syllabus</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $map = ['Sun'=>'Sunday','Mon'=>'Monday','Tue'=>'Tuesday','Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday','Sat'=>'Saturday'];
                    if (!empty($dsRows)):
                      foreach ($dsRows as $row):
                        $cols       = array_values((array)$row);
                        $dateDayRaw = (string)($cols[0] ?? '');
                        $subjectRaw = (string)($cols[1] ?? '');
                        $syllabus   = (string)($cols[2] ?? '');

                        $datePart = $dateDayRaw; $dayPart = '';
                        if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/', $dateDayRaw, $m)) { $datePart = trim($m[1]); $dayPart = trim($m[2]); }
                        $fullDay = $map[$dayPart] ?? $dayPart;

                        $marksText = '';
                        if (preg_match('/\((\d+)\)\s*$/', $subjectRaw, $mm)) {
                          $marksVal  = (int)$mm[1];
                          if (($hide_marks ?? '0') !== '1' && $marksVal > 0) $marksText = 'Total: '.$marksVal;
                          $subject = trim(preg_replace('/\s*\(\d+\)\s*$/', '', $subjectRaw));
                        } else { $subject = $subjectRaw; }
                  ?>
                    <tr>
                      <td class="date-day-cell english-text" style="white-space:normal;line-height:1.1">
                        <span class="dd-date" style="display:block;font-weight:600"><?= esc($datePart) ?></span>
                        <?php if ($fullDay): ?><span class="dd-day" style="display:block;opacity:.85"><?= esc($fullDay) ?></span><?php endif; ?>
                      </td>
                      <td class="subject-cell english-text" title="<?= esc($subject) ?>" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= esc($subject) ?>
                        <?php if ($marksText): ?><div class="dd-marks"><i class="far fa-dot-circle"></i> <?= esc($marksText) ?></div><?php endif; ?>
                      </td>
                      <?php
                        // Decide Urdu vs English for syllabus
                        $syllClean = (string)$syllabus;
                        $isUrdu = (bool)preg_match('/\p{Arabic}/u', $syllClean);
                        $syllClass = $isUrdu ? 'syll syll-ur' : 'syll syll-en';
                        $syllHtml  = nl2br(esc(strip_tags(html_entity_decode($syllClean))), false);
                      ?>
                      <td class="<?= $syllClass ?>"><?= $syllHtml ?></td>
                    </tr>
                  <?php
                      endforeach;
                    else:
                  ?>
                    <tr><td colspan="3" class="text-muted english-text">No entries found.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <?php if ($show_line1=='1' && !empty($footer_line1)): ?>
              <div class="footer-lines english-text"><?= esc($footer_line1) ?></div>
            <?php endif; ?>
            <?php if ($show_line2=='1' && !empty($footer_line2)): ?>
              <div class="footer-lines english-text"><?= esc($footer_line2) ?></div>
            <?php endif; ?>

           
            
            <!-- Print watermark (optional) -->
            <div class="print-watermark">
              <div class="watermark-text"><?= esc($schoolName) ?> - <?= esc($examName) ?></div>
            </div>

          </div> <!-- /.admit-body -->
        </div>   <!-- /.admit-card -->
      <?php endforeach; ?>

    <?php else: ?>
      <div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i> No records to display. Choose a class and click "View".</div>
    <?php endif; ?>

  </div>
</section>

<script>
(function () {
  function sizeOneTable(card) {
    const table = card.querySelector('.datesheet-table');
    if (!table) return;

    const rect = card.getBoundingClientRect?.() || { width: table.offsetWidth };
    const containerW = rect.width || table.offsetWidth || window.innerWidth;

    const ths = table.querySelectorAll('thead th');
    if (ths.length < 3) return;

    const dateCells = table.querySelectorAll('tbody td.date-day-cell');
    const subjCells = table.querySelectorAll('tbody td.subject-cell');

    let maxDate = 0, maxSubj = 0;
    dateCells.forEach(td => {
      const t = td.innerText.replace(/\s+/g, ' ').trim();
      maxDate = Math.max(maxDate, measureTextPx(t, table));
    });
    subjCells.forEach(td => {
      const t = td.querySelector('.dd-marks')
        ? td.childNodes[0].textContent.trim()
        : td.innerText.replace(/\s+/g, ' ').trim();
      maxSubj = Math.max(maxSubj, measureTextPx(t, table));
    });

    const DATE_MIN = 70, DATE_MAX = 110;
    const SUBJ_MIN = 150, SUBJ_MAX = 200;
    const THIRD_MIN = 240;
    const PAD = 24;

    let dateW = clamp(Math.ceil(maxDate + PAD), DATE_MIN, DATE_MAX);
    let subjW = clamp(Math.ceil(maxSubj + PAD), SUBJ_MIN, SUBJ_MAX);

    let thirdW = containerW - dateW - subjW - 6;
    if (thirdW < THIRD_MIN) {
      const deficit = THIRD_MIN - thirdW;
      const spareDate = dateW - DATE_MIN;
      const spareSubj = subjW - SUBJ_MIN;
      const takeDate = Math.min(spareDate, Math.ceil(deficit/2));
      const takeSubj = Math.min(spareSubj, deficit - takeDate);
      dateW -= takeDate;
      subjW -= takeSubj;
      thirdW = Math.max(THIRD_MIN, containerW - dateW - subjW - 6);
    }

    ths[0].style.width = dateW + 'px';
    ths[1].style.width = subjW + 'px';
    ths[2].style.width = thirdW + 'px';

    table.querySelectorAll('tbody tr').forEach(tr => {
      const tds = tr.children;
      if (tds.length >= 3) {
        tds[0].style.width = dateW + 'px';
        tds[1].style.width = subjW + 'px';
        tds[2].style.width = thirdW + 'px';
      }
    });
  }

  function measureTextPx(text, sampleEl) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const cs = window.getComputedStyle(sampleEl || document.body);
    const font = `${cs.fontStyle} ${cs.fontVariant} ${cs.fontWeight} ${cs.fontSize} / ${cs.lineHeight} ${cs.fontFamily}`;
    ctx.font = font;
    return ctx.measureText(text || '').width;
  }

  function clamp(n, lo, hi){ return Math.max(lo, Math.min(hi, n)); }

  function sizeAllTables() {
    document.querySelectorAll('.admit-card').forEach(sizeOneTable);
  }

  window.addEventListener('load', sizeAllTables);
  window.addEventListener('resize', (() => {
    let t=null;
    return function(){
      if (t) return;
      t = setTimeout(() => { t=null; sizeAllTables(); }, 120);
    };
  })());
})();

document.addEventListener("DOMContentLoaded", function() {
  const input = document.getElementById('line_height_input');
  const styleEl = document.getElementById('lh-override');

  function applyLineHeight(h) {
    if (!h || isNaN(h)) h = 1.6;
    h = Math.max(1.0, Math.min(3.0, h));
    styleEl.textContent =
      `.datesheet-table.compact.relax .syll{ line-height:${h} !important; }
       @media print{ .datesheet-table.compact.relax .syll{ line-height:${h} !important; } }`;
  }

  applyLineHeight(parseFloat(input.value || 1.6));
  input.addEventListener('input', () => applyLineHeight(parseFloat(input.value)));
  
  // Force page breaks on print
 
});
</script>


<style type="text/css">
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
  border: 2px solid #e0e0e0 !important;
  border-radius: 8px !important;
  overflow: hidden;
  padding: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.school-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain !important;
  background: white !important;
  display: block;
}

/* ===============================
   Urdu Font Support & Typography
   =============================== */

/* ===============================
   Admit Card / Datesheet — Screen + Print (A4)
   =============================== */

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
  border:2px solid #e0e0e0 !important;
  border-radius:8px !important;
  overflow:hidden;
  padding:4px;
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

/* Corner badge */
.due-badge{
  position:absolute; 
  top:140px; 
  right:40px; 
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

/* ---- Student row ---- */
.student-row{ 
  display:grid; 
  grid-template-columns:84px 1fr; 
  column-gap:12px; 
  align-items:start; 
  margin-bottom:12px; 
}
.avatar{ 
  width:84px; 
  height:84px; 
  border:1px solid #ddd; 
  border-radius:6px; 
  overflow:hidden; 
  display:flex; 
  align-items:center; 
  justify-content:center; 
  background:#f5f7fb; 
  color:#90a4ae; 
}
.avatar img{ 
  width:100%; 
  height:100%; 
  object-fit:cover; 
}
.avatar i{ 
  font-size:40px; 
  opacity:.65; 
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

/* ---- Datesheet table ---- */
.datesheet-wrap{ 
  margin-top:6px; 
}
.datesheet-table {
  width: 100%;
  border-collapse: collapse !important;
  border: 2px solid #000 !important;
}

.datesheet-table thead th {
  background: #f2f2f2 !important;
  border-bottom: 3px solid #000 !important;
  font-weight: 800;
}


.datesheet-table td{ 
  border:1px solid var(--line-2);
  padding:10px 12px; 
  font-size:15px; 
  vertical-align:middle; 
}

/* date/day in two lines */
.date-day-cell .dd-date{ 
  display:block; 
  font-weight:800; 
  color:var(--ink); 
  line-height:1.05; 
}
.date-day-cell .dd-day{ 
  display:block; 
  color:#000; 
  line-height:1.05; 
}
.dd-marks{ 
  color:var(--ink); 
  font-size:12px; 
  margin-top:4px; 
}

/* subject trims, syllabus wraps */
.subject-cell{ 
  white-space:nowrap; 
  overflow:hidden; 
  text-overflow:ellipsis; 
}

/* Neutral base – shared spacing/wrapping */
.syll{
  white-space: normal;
  word-wrap: break-word;
  font-size: 1.1em;
  line-height: 1.8;
  letter-spacing: 0.01em;
}

/* Urdu: RTL + Nastaleeq + right aligned */
.syll-ur{
  direction: rtl;
  text-align: right;
  font-family: 'Jameel Noori Nastaleeq','Jameel Noori Nastaleeq Kasheeda','Nafees','Alvi','Urdu', serif !important;
  font-size: 1.3em;
  line-height: 2.0;
  letter-spacing: 0.02em;
}

/* English: LTR + left aligned */
.syll-en{
  direction: ltr;
  text-align: left;
  font-family: 'Times New Roman', Times, serif !important;
}

/* Compact variant */
.datesheet-table.compact thead th,
.datesheet-table.compact td{ 
  padding:2px 6px !important; 
  line-height:1.05 !important; 
  vertical-align:middle !important; 
}
.datesheet-table.compact .date-day-cell,
.datesheet-table.compact .subject-cell,
.datesheet-table.compact .syll{ 
  padding-top:0 !important; 
  padding-bottom:0 !important; 
}
.datesheet-table.compact .dd-date,
.datesheet-table.compact .dd-day,
.datesheet-table.compact .dd-marks{ 
  margin:0 !important; 
  line-height:1.05 !important; 
}

/* Softer compact: add a bit more air without losing density */
.datesheet-table.compact.relax thead th,
.datesheet-table.compact.relax td{
  padding: 6px 10px !important;
  line-height: 1.0 !important;
}

.datesheet-table.compact.relax .date-day-cell,
.datesheet-table.compact.relax .subject-cell,
.datesheet-table.compact.relax .syll{
  padding-top: 4px !important;
  padding-bottom: 4px !important;
}

.datesheet-table.compact.relax .dd-date,
.datesheet-table.compact.relax .dd-day,
.datesheet-table.compact.relax .dd-marks{
  margin: 1px 0 !important;
  line-height: 1.15 !important;
}

/* Better readability + subtle striping */
.datesheet-table.compact.relax tbody tr:nth-child(odd) td{
  background: #fbfcff;
}
.datesheet-table.compact.relax tbody tr:hover td{
  background: #f3f7ff;
}

/* keep syllabus comfy (both urdu & english) */
.datesheet-table.compact.relax .syll{
  line-height: 1.6 !important;
  font-size: 1.2em;
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

/* ---- Screen border normalization ---- */


.datesheet-table th,
.datesheet-table td {
  border: 1.8px solid #000 !important;
  color: #000 !important;
}

/* ---- Urdu specific improvements ---- */
.urdu-header {
  font-family: 'Jameel Noori Nastaleeq', 'Jameel Noori Nastaleeq Kasheeda', 'Nafees', 'Alvi', 'Urdu', serif !important;
  font-size: 1.4em;
  text-align: center;
  margin: 10px 0;
  line-height: 2.0;
  direction: rtl;
}

.urdu-footer {
  font-family: 'Jameel Noori Nastaleeq', 'Jameel Noori Nastaleeq Kasheeda', 'Nafees', 'Alvi', 'Urdu', serif !important;
  font-size: 1.2em;
  text-align: center;
  margin: 10px 0;
  direction: rtl;
  line-height: 2.0;
}

/* ===============================
   PRINT STYLES - CRITICAL FIXES
   =============================== */
@media print {
  /* Reset everything for print */
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
  
  /* Hide only what should be hidden */
  .no-print,
  .page-actions,
  .card.card-outline.card-primary,
  .nav.nav-tabs,
  .form-row,
  .alert.alert-info {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    width: 0 !important;
    overflow: hidden !important;
    position: absolute !important;
  }
  
  /* Show admit cards */
   .admit-card {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;

    page-break-after: always !important;
    break-after: page !important;

    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  /* Do NOT add break after last card */
  .admit-card:last-child {
    page-break-after: auto !important;
    break-after: auto !important;
  }
  
  /* Page breaks between admit cards */
  .admit-card.page-break-before {
    page-break-before: always !important;
    break-before: page !important;
  }
  
  /* Admit card content */
  .admit-header {
    display: grid !important;
    grid-template-columns: 96px 1fr !important;
    gap: 16px !important;
    padding: 16px 16px 10px !important;
    border-bottom: 2px solid var(--accent) !important;
    page-break-inside: avoid !important;
  }
  
  .admit-body {
    padding: 16px !important;
    display: block !important;
    visibility: visible !important;
  }
  
  /* Logo in print */
  .school-logo {
    background: white !important;
    border: 2px solid #e0e0e0 !important;
    display: flex !important;
  }
  
  .school-logo img {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background: white !important;
  }
  
  /* Table in print */
  .datesheet-table {
    border: 2px solid var(--line) !important;
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
  
  /* Typography in print */
  .syll-ur {
    direction: rtl !important;
    text-align: right !important;
    font-family: 'Jameel Noori Nastaleeq','Jameel Noori Nastaleeq Kasheeda','Nafees','Alvi','Urdu', serif !important;
  }
  
  .syll-en {
    direction: ltr !important;
    text-align: left !important;
    font-family: 'Times New Roman', Times, serif !important;
  }
  
  .urdu-text,
  .urdu-header,
  .urdu-footer,
  .urdu-syllabus {
    font-family: 'Jameel Noori Nastaleeq', 'Jameel Noori Nastaleeq Kasheeda', 'Nafees', 'Alvi', 'Urdu', serif !important;
  }
  
  /* Keep line heights */
  .school-meta h1 {
  font-size: 48px !important;
  font-weight: 900 !important;
  letter-spacing: 0.6px !important;
  margin-bottom: 6px !important;
  color: #000 !important;
}
  .school-meta .sub { line-height: 1.2 !important; }
  .facts, .facts-compact, .fact { line-height: 1.5 !important; }
  .datesheet-table.compact.relax .syll { line-height: <?= $line_height ?> !important; }
  
  /* Page setup */
 @page {
  size: A4 portrait;
  margin-top: 10mm;
  margin-left: 10mm;
  margin-right: 10mm;
  margin-bottom: 10mm;
}
  
  /* Remove any left/right margins that might hide content */
  .content,
  .container-fluid {
    width: 100% !important;
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  
  /* Watermark (optional) */
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

/* ===============================
   Solid black borders (scoped)
   =============================== */

.admit-card.solid-borders table {
  border-collapse: collapse !important;
  border: 2px solid #000 !important;
}

.admit-card.solid-borders table th,
.admit-card.solid-borders table td {
  border: 2px solid #000 !important;
}

.admit-card.solid-borders .datesheet-table,
.admit-card.solid-borders .datesheet-table th,
.admit-card.solid-borders .datesheet-table td {
  border: 2px solid #000 !important;
}

.admit-card.solid-borders .fact {
  border: 2px solid #000 !important;
  background: #fff;
}

.admit-card.solid-borders .sign-box {
  border-top: 2px solid #000 !important;
}

.admit-card.solid-borders .datesheet-table tbody tr:hover td {
  background: #f3f7ff;
  border-color: #000 !important;
}

/* Print: preserve solid black */
@media print {
  .admit-card.solid-borders table,
  .admit-card.solid-borders table th,
  .admit-card.solid-borders table td,
  .admit-card.solid-borders .fact,
  .admit-card.solid-borders .sign-box {
    border-color: #000 !important;
  }
}
</style>

<style id="lh-override">
  .datesheet-table.compact.relax .syll{ line-height: <?= $line_height ?> !important; }
  @media print{
    .datesheet-table.compact.relax .syll{ line-height: <?= $line_height ?> !important; }
  }
</style>
<?= $this->endSection() ?>