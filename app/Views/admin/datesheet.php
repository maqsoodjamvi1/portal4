<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
// Filters: prefer values computed in Datesheet::index (same as server-side datesheet rows)
$af = isset($admitFilters) && is_array($admitFilters) ? $admitFilters : [];
$mode = $_GET['mode'] ?? '';
$cls_sec_id   = $_GET['cls_sec_id']   ?? '';
$student_id   = (int) ($selectedStudentId ?? ($_GET['student_id'] ?? 0));
$student_label = (string) ($selectedStudentLabel ?? '');
$hmRaw = $af['hide_marks'] ?? ($_GET['hide_marks'] ?? '');
if (is_array($hmRaw)) {
    $hmRaw = end($hmRaw);
}
$hmRaw = (string) $hmRaw;
$hide_marks = ($hmRaw === '1' || strtolower($hmRaw) === 'on') ? '1' : '';
$fsRaw = $af['full_subject'] ?? ($_GET['full_subject'] ?? '');
if (is_array($fsRaw)) {
    $fsRaw = end($fsRaw);
}
$fsRaw = (string) $fsRaw;
$show_full_subject = ($fsRaw === '1' || strtolower($fsRaw) === 'on') ? '1' : '';
$footer_line1 = $_GET['footer_line1'] ?? '';
$footer_line2 = $_GET['footer_line2'] ?? '';
$show_line1   = $_GET['show_line1']   ?? '';
$show_line2   = $_GET['show_line2']   ?? '';
$font_size    = (string) ($af['font_size'] ?? ($_GET['font_size'] ?? 'medium'));
$font_size    = in_array($font_size, ['small', 'medium', 'large'], true) ? $font_size : 'medium';

$line_height = isset($af['line_height']) ? (float) $af['line_height'] : (isset($_GET['line_height']) ? floatval($_GET['line_height']) : 2.0);
if ($line_height < 1.0) {
    $line_height = 1.0;
}
if ($line_height > 3.0) {
    $line_height = 3.0;
}
$line_height_css = number_format($line_height, 2, '.', '');
?>

<?= view('components/page_header', [
    'title' => 'Exam Datesheet',
    'icon' => 'fas fa-calendar-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Exam Datesheet', 'active' => true],
    ],
]) ?>

<section class="content">
  <div id="admin-admit-datesheet" class="container-fluid admit-scope admit-scope-fs-<?= esc($font_size) ?>" style="--admit-syll-lh: <?= esc($line_height_css, 'attr') ?>;">

    <!-- Top actions / Filters -->
    <div class="page-actions no-print">
      <div class="card card-outline card-primary">
        <div class="card-body py-2">
          <form id="datesheet-filter-form" action="<?= base_url('admin/datesheet') ?>" method="get" class="row align-items-end">
            <?php helper('url'); ?>
            <?php if ($mode === 'sample'): ?>
              <input type="hidden" name="mode" value="sample">
            <?php endif; ?>
            <ul class="nav nav-tabs w-100 px-2 mb-3">
              <li class="nav-item"><a class="nav-link <?= $mode === '' ? 'active' : '' ?>" href="<?= base_url('admin/datesheet') ?>"><i class="fas fa-id-card-alt me-1"></i> Admit Card</a></li>
        
            <li class="nav-item">
              <a class="nav-link <?= $mode === 'without_syllabus' ? 'active' : '' ?>"
                 href="<?= base_url('admin/datesheet?mode=without_syllabus') ?>">
                <i class="fas fa-table me-1"></i> Admit Card Without Syllabus
              </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $mode === 'sample' ? 'active' : '' ?>" href="<?= base_url('admin/datesheet?mode=sample') ?>">
                  <i class="fas fa-copy me-1"></i> Sample Admit Cards
                </a>
              </li>
              <li class="nav-item"><a class="nav-link <?= url_is('admin/datesheet/add-syllabus') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet/add-syllabus') ?>"><i class="fas fa-list-ul me-1"></i> Add Syllabus</a></li>
              <li class="nav-item"><a class="nav-link <?= url_is('admin/datesheet/add') ? 'active' : '' ?>" href="<?= base_url('admin/datesheet/add') ?>"><i class="far fa-calendar-plus me-1"></i> Add Datesheet</a></li>
            </ul>

            <?php if ($mode === 'sample'): ?>
              <div class="col-12 mb-2">
                <div class="alert alert-info mb-0 py-2">
                  <i class="fas fa-info-circle me-1"></i>
                  Showing the first student admit card from each class section for proof reading.
                  <strong class="d-block mt-1">Change any option below to refresh — no View button in sample mode. Marks column stays visible for proofreading.</strong>
                </div>
              </div>
            <?php else: ?>
            <div class="form-group col-md-3">
              <label class="mb-1"><strong>Class</strong></label>
              <select class="form-control" name="cls_sec_id" id="cls_sec_id">
                <option value="">All Classes</option>
                <?php if (!empty($sectionsclassinfo)): foreach ($sectionsclassinfo as $row):
                  $id = is_array($row) ? ($row['cls_sec_id'] ?? $row['section_id'] ?? '') : ($row->cls_sec_id ?? $row->section_id ?? '');
                  $lbl = is_array($row) ? ($row['sectionclassname'] ?? (($row['class_short_name'] ?? $row['class_name'] ?? '') . ' - ' . ($row['section_name'] ?? ''))) : ($row->sectionclassname ?? (($row->class_short_name ?? $row->class_name ?? '') . ' - ' . ($row->section_name ?? '')));
                ?>
                  <option value="<?= esc($id) ?>" <?= ($cls_sec_id == (string)$id ? 'selected' : '') ?>><?= esc($lbl) ?></option>
                <?php endforeach; endif; ?>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label class="mb-1"><strong>Search student</strong> <small class="text-muted">(individual admit card)</small></label>
              <div class="input-group">
                <input type="text" class="form-control" id="admit_student_search"
                       placeholder="Type name or reg no (min 2 chars)…"
                       value="<?= esc($student_label) ?>"
                       autocomplete="off">
                <input type="hidden" name="student_id" id="admit_student_id" value="<?= $student_id > 0 ? (int) $student_id : '' ?>">
                <?php if ($student_id > 0): ?>
                <button type="button" class="btn btn-outline-secondary" id="admit_student_clear" title="Clear student filter">
                    <i class="fas fa-times"></i>
                  </button>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block"><strong>Hide Marks</strong></label>
              <input type="checkbox" name="hide_marks" value="1" <?= ($hide_marks === '1' ? 'checked' : '') ?> />
            </div>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block"><strong>Full subject name</strong></label>
              <input type="checkbox" name="full_subject" value="1" <?= ($show_full_subject === '1' ? 'checked' : '') ?> title="Show complete subject name instead of short code" />
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
              <input type="checkbox" name="show_line1" value="1" <?= ($show_line1=='1' ? 'checked' : '') ?> />
            </div>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block">Show Line 2</label>
              <input type="checkbox" name="show_line2" value="1" <?= ($show_line2=='1' ? 'checked' : '') ?> />
            </div>

            <div class="form-group col-sm-2">
              <label class="mb-1 d-block"><strong>Line Height</strong></label>
              <input type="number" step="0.1" min="1" max="3" 
                     class="form-control" id="line_height_input" 
                     name="line_height" value="<?= esc($line_height_css) ?>">
            </div>
            <div class="form-group col-sm-2">
              <label class="mb-1 d-block"><strong>Font Size</strong></label>
              <select class="form-control" name="font_size" id="font_size_select">
                <option value="small" <?= $font_size === 'small' ? 'selected' : '' ?>>Small</option>
                <option value="medium" <?= $font_size === 'medium' ? 'selected' : '' ?>>Medium</option>
                <option value="large" <?= $font_size === 'large' ? 'selected' : '' ?>>Large</option>
              </select>
            </div>
            <div class="form-group col-sm-2 <?= $mode === 'sample' ? 'd-none' : '' ?>">
              <button class="btn btn-primary w-100" type="submit">
                <i class="fas fa-eye me-1"></i> View
              </button>
            </div>

            <div class="form-group col-sm-2 ms-auto">
              <button type="button" onclick="window.print()" class="btn btn-outline-secondary w-100">
                <i class="fas fa-print me-1"></i> Print
              </button>
            </div>
          </form>
          <div id="admit-print-fit-hint" class="no-print alert alert-light border py-2 px-3 mb-0 mt-2" style="display:none;" role="status" aria-live="polite">
            <i class="fas fa-file-alt me-2 text-secondary" aria-hidden="true"></i>
            <span id="admit-print-fit-text"></span>
            <span class="d-block small text-muted mt-1 mb-0">This compares each card’s height on screen to one A4 sheet’s printable height (297&nbsp;mm − 2×10&nbsp;mm margins), matching your print CSS. Browser print headers or zoom can still shift results slightly.</span>
          </div>
          <script>
          (function () {
            function initDatesheetFilterForm() {
              var form = document.getElementById('datesheet-filter-form');
              if (!form) return;

              // Native form.submit is shadowed if any control uses name="submit" — use prototype.
              var nativeFormSubmit = HTMLFormElement.prototype.submit;
              function submitForm() {
                try {
                  if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                  } else {
                    nativeFormSubmit.call(form);
                  }
                } catch (e) {
                  nativeFormSubmit.call(form);
                }
              }

              var submitTimer = null;
              function submitFormDebounced() {
                if (submitTimer) clearTimeout(submitTimer);
                submitTimer = setTimeout(submitForm, 80);
              }

              var lhInput = form.querySelector('#line_height_input');
              var lhTimer = null;

              function bind(el, events, fn) {
                if (!el) return;
                events.split(/\s+/).forEach(function (ev) {
                  el.addEventListener(ev, fn);
                });
              }

              function previewFontSizeFromSelect() {
                var root = document.getElementById('admin-admit-datesheet');
                var sel = form.querySelector('#font_size_select');
                if (!root || !sel) return;
                ['admit-scope-fs-small', 'admit-scope-fs-medium', 'admit-scope-fs-large'].forEach(function (c) {
                  root.classList.remove(c);
                });
                root.classList.add('admit-scope-fs-' + sel.value);
                document.querySelectorAll('.admit-card').forEach(function (card) {
                  card.classList.remove('admit-font-small', 'admit-font-medium', 'admit-font-large');
                  card.classList.add('admit-font-' + sel.value);
                });
                if (typeof window.updateAdmitPrintFitHint === 'function') {
                  window.updateAdmitPrintFitHint();
                }
                if (typeof window.fitAdmitAvatarImages === 'function') {
                  window.fitAdmitAvatarImages();
                }
              }

              var fontSel = form.querySelector('#font_size_select');
              if (fontSel) {
                fontSel.addEventListener('change', function () {
                  previewFontSizeFromSelect();
                  submitFormDebounced();
                });
              }
              bind(form.querySelector('input[name="hide_marks"]'), 'change input', submitFormDebounced);
              bind(form.querySelector('input[name="full_subject"]'), 'change input', submitFormDebounced);
              bind(form.querySelector('input[name="show_line1"]'), 'change input', submitFormDebounced);
              bind(form.querySelector('input[name="show_line2"]'), 'change input', submitFormDebounced);
              bind(form.querySelector('#cls_sec_id'), 'change', function () {
                var sid = form.querySelector('#admit_student_id');
                if (sid) sid.value = '';
                var search = form.querySelector('#admit_student_search');
                if (search) search.value = '';
                submitFormDebounced();
              });

              var clearBtn = document.getElementById('admit_student_clear');
              if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                  var sid = form.querySelector('#admit_student_id');
                  var search = form.querySelector('#admit_student_search');
                  if (sid) sid.value = '';
                  if (search) search.value = '';
                  submitForm();
                });
              }

              if (typeof jQuery !== 'undefined' && jQuery.fn.autocomplete) {
                var $search = jQuery('#admit_student_search');
                if ($search.length) {
                  $search.autocomplete({
                    source: function (request, response) {
                      if (request.term.length < 2) {
                        response([]);
                        return;
                      }
                      jQuery.ajax({
                        url: '<?= site_url('admin/datesheet/search-students') ?>',
                        dataType: 'json',
                        data: { term: request.term },
                        success: function (data) {
                          response(jQuery.map(data || [], function (item) {
                            var name = ((item.first_name || '') + ' ' + (item.last_name || '')).trim();
                            var cls = ((item.class_name || '') + (item.section_name ? ' - ' + item.section_name : '')).trim();
                            var reg = item.reg_no || '';
                            return {
                              label: name + (reg ? ' (' + reg + ')' : '') + (cls ? ' — ' + cls : ''),
                              value: name,
                              student_id: item.student_id,
                              cls_sec_id: item.cls_sec_id
                            };
                          }));
                        }
                      });
                    },
                    minLength: 2,
                    select: function (event, ui) {
                      event.preventDefault();
                      jQuery('#admit_student_id').val(ui.item.student_id);
                      $search.val(ui.item.value);
                      if (ui.item.cls_sec_id) {
                        jQuery('#cls_sec_id').val(ui.item.cls_sec_id);
                      }
                      submitForm();
                    },
                    focus: function (event) {
                      event.preventDefault();
                    }
                  });
                }
              }

              if (lhInput) {
                lhInput.addEventListener('change', submitFormDebounced);
                lhInput.addEventListener('input', function () {
                  var root = document.getElementById('admin-admit-datesheet');
                  if (root) {
                    var v = parseFloat(lhInput.value);
                    if (isNaN(v) || v < 1) {
                      v = 1;
                    }
                    if (v > 3) {
                      v = 3;
                    }
                    root.style.setProperty('--admit-syll-lh', v.toFixed(2));
                  }
                  if (typeof window.updateAdmitPrintFitHint === 'function') {
                    window.updateAdmitPrintFitHint();
                  }
                  if (typeof window.fitAdmitAvatarImages === 'function') {
                    window.fitAdmitAvatarImages();
                  }
                  if (lhTimer) clearTimeout(lhTimer);
                  lhTimer = setTimeout(submitFormDebounced, 500);
                });
              }

              var f1 = form.querySelector('input[name="footer_line1"]');
              var f2 = form.querySelector('input[name="footer_line2"]');
              bind(f1, 'change blur', submitFormDebounced);
              bind(f2, 'change blur', submitFormDebounced);
            }

            if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', initDatesheetFilterForm);
            } else {
              initDatesheetFilterForm();
            }
          })();
          </script>
          <script>
          (function () {
            var ROOT_ID = 'admin-admit-datesheet';
            var BANNER_ID = 'admit-print-fit-hint';
            var TEXT_ID = 'admit-print-fit-text';
            /** Match @media print @page { size: A4; margin: 1cm } — printable height */
            var PRINTABLE_HEIGHT_MM = 277;

            function debounce(fn, ms) {
              var t;
              return function () {
                clearTimeout(t);
                t = setTimeout(fn, ms);
              };
            }

            function mmToPx(mm) {
              var el = document.createElement('div');
              el.style.cssText = 'position:absolute;left:-9999px;top:0;height:' + mm + 'mm;width:1mm;visibility:hidden;pointer-events:none;';
              document.body.appendChild(el);
              var px = el.getBoundingClientRect().height;
              document.body.removeChild(el);
              if (!px || px < 40) {
                return (mm * 96) / 25.4;
              }
              return px;
            }

            function updateAdmitPrintFitHint() {
              var root = document.getElementById(ROOT_ID);
              var banner = document.getElementById(BANNER_ID);
              var textEl = document.getElementById(TEXT_ID);
              if (!root || !banner || !textEl) {
                return;
              }

              var cards = root.querySelectorAll('.admit-card');
              if (!cards.length) {
                banner.style.display = 'none';
                return;
              }

              var pageH = mmToPx(PRINTABLE_HEIGHT_MM);
              var maxPages = 1;
              var worstIdx = 1;
              for (var i = 0; i < cards.length; i++) {
                var c = cards[i];
                var h = Math.max(c.offsetHeight, c.scrollHeight);
                var p = h <= pageH + 12 ? 1 : Math.ceil((h - 12) / pageH);
                if (p > maxPages) {
                  maxPages = p;
                  worstIdx = i + 1;
                }
              }

              banner.style.display = '';
              if (maxPages <= 1) {
                banner.className = 'no-print alert alert-success border py-2 px-3 mb-0 mt-2';
                textEl.innerHTML = '<strong>A4 print check:</strong> Each loaded admit card is within about <strong>one A4 page</strong> in height (same printable area as print: 297&nbsp;mm height minus 1&nbsp;cm top and bottom margins).';
              } else {
                banner.className = 'no-print alert alert-warning border py-2 px-3 mb-0 mt-2';
                textEl.innerHTML = '<strong>A4 print check:</strong> At least one card may need about <strong>' + maxPages + ' sheet(s)</strong> (tallest looks like card #' + worstIdx + '). Try smaller font or line height, shorter syllabus text, or fewer footer lines before opening Print.';
              }
            }

            window.updateAdmitPrintFitHint = updateAdmitPrintFitHint;

            var debounced = debounce(updateAdmitPrintFitHint, 120);
            var roAttached = false;

            function boot() {
              updateAdmitPrintFitHint();
              if (roAttached) {
                return;
              }
              var root = document.getElementById(ROOT_ID);
              if (root && typeof ResizeObserver !== 'undefined') {
                roAttached = true;
                var ro = new ResizeObserver(function () {
                  debounced();
                });
                ro.observe(root);
              }
            }

            function scheduleBoot() {
              function go() {
                setTimeout(boot, 0);
              }
              if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', go);
              } else {
                go();
              }
            }

            scheduleBoot();

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

            function scheduleFitAvatars() {
              setTimeout(fitAdmitAvatarImages, 0);
            }
            if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', scheduleFitAvatars);
            } else {
              scheduleFitAvatars();
            }

            window.addEventListener('resize', debounced);
            window.addEventListener('beforeprint', function () {
              fitAdmitAvatarImages();
              updateAdmitPrintFitHint();
            });

            window.addEventListener('load', function () {
              setTimeout(updateAdmitPrintFitHint, 200);
              setTimeout(updateAdmitPrintFitHint, 1200);
              setTimeout(fitAdmitAvatarImages, 80);
              setTimeout(fitAdmitAvatarImages, 600);
            });
          })();
          </script>
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
      $cntA  = $value['att_A']  ?? null;
      $cntL  = $value['att_L']  ?? null;
      $cntLC = $value['att_LC'] ?? null;
      $cntEL = $value['att_EL'] ?? null;
      
      // BMI — must match Datesheet::data(): bmi_value, height_cm, weight_kg, student_age_years
      $bmi = $value['bmi_value'] ?? $value['bmi'] ?? null;
      $bmiCategory = $value['bmi_category'] ?? '';
      $height = $value['height_cm'] ?? $value['height'] ?? $value['std_height'] ?? null;
      $weight = $value['weight_kg'] ?? $value['weight'] ?? null;
      $dob = $value['date_of_birth'] ?? null;
      $age = null;
      if (isset($value['student_age_years']) && $value['student_age_years'] !== '' && $value['student_age_years'] !== null) {
        $age = (int) $value['student_age_years'];
      }
      if ($age === null && !empty($dob) && $dob != '0000-00-00') {
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;
      }
    ?>

    <!-- Each admit card -->
    <div class="admit-card admit-font-<?= esc($font_size) ?><?= ($index > 0 ? ' page-break-before' : '') ?>">

      <!-- Header (LOGO + SCHOOL META) -->
      <div class="admit-header">
        <?php
          $defaultLogo = base_url('uploads/logo_school.png');
          if (!empty($finalLogo)) {
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
        <!-- BMI Strip -->
      <!-- BMI Strip - Enhanced with Age, Weight, Height, BMI -->
<div class="admit-bmi-strip">
    <div class="admit-bmi-title">
        <i class="fas fa-heartbeat"></i> Health & BMI Report
    </div>
    <div class="admit-bmi-items">
        <?php if ($age !== null): ?>
            <span class="admit-bmi-item">
                <i class="fas fa-birthday-cake"></i> <b>Age:</b> <?= (int) $age ?> yrs
            </span>
        <?php endif; ?>
        <?php if ($height !== null && $height !== '' && is_numeric($height)): ?>
            <span class="admit-bmi-item">
                <i class="fas fa-arrows-alt-v"></i> <b>Height:</b> <?= number_format((float) $height, 2) ?> cm
            </span>
        <?php endif; ?>
        <?php if ($weight !== null && $weight !== '' && is_numeric($weight)): ?>
            <span class="admit-bmi-item">
                <i class="fas fa-weight-hanging"></i> <b>Weight:</b> <?= number_format((float) $weight, 2) ?> kg
            </span>
        <?php endif; ?>
        <?php if ($bmi !== null && $bmi !== '' && is_numeric($bmi)): ?>
            <span class="admit-bmi-item">
                <i class="fas fa-calculator"></i> <b>BMI:</b> <?= number_format((float) $bmi, 2) ?>
            </span>
        <?php endif; ?>
        <?php if ($bmiCategory): ?>
            <span class="admit-bmi-item bmi-status <?= strtolower($bmiCategory) ?>">
                <i class="fas fa-chart-line"></i> <b>Status:</b> <?= ucfirst($bmiCategory) ?>
            </span>
        <?php endif; ?>
    </div>
</div>
        
        <!-- Attendance Strip -->
        <div class="admit-attendance-strip <?= ($dues > 0 ? 'admit-attendance-strip--dues' : '') ?>">
          <div class="admit-attendance-title">
            <i class="fas fa-chart-line"></i> Attendance Summary
          </div>
          <div class="admit-attendance-items">
            <span class="admit-attendance-item"><b>Total Working Day</b> <?= $workingDays ?? '—' ?></span>
            <span class="admit-attendance-item"><b>Absent Count</b> <?= $cntA ?? '—' ?></span>
            <span class="admit-attendance-item"><b>Late Count</b> <?= $cntL ?? '—' ?></span>
            <span class="admit-attendance-item"><b>Early Left Count</b> <?= $cntEL ?? '—' ?></span>
            <span class="admit-attendance-item"><b>Leave Count</b> <?= $cntLC ?? '—' ?></span>
          </div>
          <?php if ($dues > 0): ?>
            <div class="due-badge">
              <i class="fas fa-exclamation-triangle"></i> <?= number_format($dues, 0) ?>
            </div>
          <?php endif; ?>
        </div>

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

        <!-- Datesheet (grouped by exam date) -->
        <div class="datesheet-wrap">
          <?php
            $dayMap = ['Sun'=>'Sunday','Mon'=>'Monday','Tue'=>'Tuesday','Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday','Sat'=>'Saturday'];

            $normalizedDs = [];
            foreach (($dsRows ?? []) as $row) {
              if (is_array($row) && isset($row['exam_date'])) {
                $normalizedDs[] = $row;
                continue;
              }
              $cols       = array_values((array)$row);
              $dateDayRaw = (string)($cols[0] ?? '');
              $subjectRaw = (string)($cols[1] ?? '');
              $syllabusL  = (string)($cols[2] ?? '');

              $marksVal = 0;
              if (preg_match('/\((\d+)\)\s*$/', $subjectRaw, $mm)) {
                $marksVal = (int)$mm[1];
              }
              $subjOnly = trim(preg_replace('/\s*\(\d+\)\s*$/', '', $subjectRaw));

              $examSort = '';
              if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/', $dateDayRaw, $dm)) {
                $dp = trim($dm[1]);
                $ts = strtotime($dp);
                if ($ts) {
                  $examSort = date('Y-m-d', $ts);
                }
              }
              if ($examSort === '') {
                $examSort = '0000-00-00-' . substr(md5($dateDayRaw), 0, 8);
              }

              $normalizedDs[] = [
                'date_day'      => $dateDayRaw,
                'exam_date'     => $examSort,
                'subject_line'  => $subjectRaw,
                'subject_full'  => $subjOnly !== '' ? $subjOnly : $subjectRaw,
                'marks'         => $marksVal,
                'syllabus'      => $syllabusL,
              ];
            }

            $byExamDate = [];
            foreach ($normalizedDs as $nr) {
              $k = (string)($nr['exam_date'] ?? '');
              if (!isset($byExamDate[$k])) {
                $byExamDate[$k] = [];
              }
              $byExamDate[$k][] = $nr;
            }
            ksort($byExamDate);
          ?>
          <?php
            // Sample mode: always show Marks column for proofreading. Hide marks still affects
            // server-side subject text (Datesheet::data) when the checkbox is checked.
            $dsHideMarks = ($mode === 'sample') ? false : ($hide_marks === '1');
          ?>
          <table class="datesheet-table datesheet-table-tabular compact relax datesheet-grouped <?= $dsHideMarks ? 'datesheet-table--two-col' : 'datesheet-table--three-col' ?>">
            <colgroup>
              <?php if ($dsHideMarks): ?>
              <col class="col-subject" style="width:18%">
              <col class="col-syll" style="width:82%">
              <?php else: ?>
              <col class="col-subject" style="width:13%">
              <col class="col-marks" style="width:7%">
              <col class="col-syll" style="width:80%">
              <?php endif; ?>
            </colgroup>
            <thead>
              <tr>
                <th class="english-text ds-th-subject">Subject</th>
                <?php if (!$dsHideMarks): ?>
                <th class="english-text ds-th-marks text-center">Marks</th>
                <?php endif; ?>
                <th class="english-text ds-th-syll text-center">Exam Syllabus</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($byExamDate)): ?>
                <?php foreach ($byExamDate as $groupRows): ?>
                  <?php
                    $head       = $groupRows[0];
                    $dateDayRaw = (string)($head['date_day'] ?? '');
                    $datePart   = $dateDayRaw;
                    $dayPart    = '';
                    if (preg_match('/^(.*?)\s*\(([^)]+)\)\s*$/', $dateDayRaw, $m)) {
                      $datePart = trim($m[1]);
                      $dayPart  = trim($m[2]);
                    }
                    $fullDay = $dayMap[$dayPart] ?? $dayPart;

                    $dayTotal = 0;
                    foreach ($groupRows as $gr) {
                      $dayTotal += (int)($gr['marks'] ?? 0);
                    }
                    $dsColspan = $dsHideMarks ? 2 : 3;
                  ?>
                  <tr class="ds-date-band">
                    <td colspan="<?= (int) $dsColspan ?>" class="ds-date-heading english-text">
                      <span class="ds-date-line">
                        <strong class="ds-date-part"><?= esc($datePart) ?></strong>
                        <?php if ($fullDay !== ''): ?>
                          <span class="ds-day-part"><?= esc($fullDay) ?></span>
                        <?php endif; ?>
                      </span>
                    </td>
                  </tr>
                  <?php foreach ($groupRows as $row): ?>
                    <?php
                      $subjectShort = trim((string)($row['subject_short'] ?? ''));
                      $subjectLine  = trim((string)($row['subject_line'] ?? ''));
                      $subjectFullRaw = trim((string)($row['subject_full'] ?? ''));
                      $subjectNameOnly = $subjectFullRaw;
                      if ($subjectNameOnly === '') {
                        $subjectNameOnly = trim(preg_replace('/\s*\(\d+\)\s*$/', '', $subjectLine));
                      }
                      if ($show_full_subject === '1') {
                        $subjectDisplay = $subjectNameOnly !== '' ? $subjectNameOnly : $subjectLine;
                      } else {
                        $subjectDisplay = $subjectShort;
                        if ($subjectDisplay === '' && $subjectNameOnly !== '') {
                          $subjectDisplay = $subjectNameOnly;
                        }
                        if ($subjectDisplay === '') {
                          $subjectDisplay = preg_replace('/\s*\(\d+\)\s*$/', '', $subjectLine);
                        }
                        if ($subjectDisplay === '') {
                          $subjectDisplay = $subjectLine;
                        }
                      }
                      $marksVal = (int)($row['marks'] ?? 0);
                      if ($marksVal <= 0 && preg_match('/\((\d+)\)\s*$/', $subjectLine, $mmarks)) {
                        $marksVal = (int) $mmarks[1];
                      }
                      $syllabus     = (string)($row['syllabus'] ?? '');
                      $syllClean    = $syllabus;
                      $isUrdu       = (bool)preg_match('/\p{Arabic}/u', $syllClean);
                      $syllClass    = $isUrdu ? 'syll syll-ur' : 'syll syll-en';
                      $syllHtml     = nl2br(esc(strip_tags(html_entity_decode($syllClean))), false);
                      $titleFull    = $subjectLine !== '' ? $subjectLine : ($subjectNameOnly !== '' ? $subjectNameOnly : $subjectDisplay);
                    ?>
                    <tr class="ds-subject-row">
                      <td class="subject-name-cell english-text" title="<?= esc($titleFull) ?>"><?= esc($subjectDisplay) ?></td>
                      <?php if (!$dsHideMarks): ?>
                      <td class="marks-cell english-text"><?= $marksVal > 0 ? (int) $marksVal : '—' ?></td>
                      <?php endif; ?>
                      <td class="<?= esc($syllClass) ?>"><?= $syllHtml ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="<?= (int) ($dsHideMarks ? 2 : 3) ?>" class="text-muted english-text">No entries found.</td></tr>
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
        
      </div> <!-- /.admit-body -->
    </div>   <!-- /.admit-card -->
  <?php endforeach; ?>

<?php else: ?>
  <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i>
    <?php if ($mode === 'sample'): ?>
      No sample admit cards found. Please check class sections, enrolled students, and active datesheet.
    <?php elseif ($student_id > 0): ?>
      No admit card found for this student. Check that they are enrolled in the current session and a datesheet exists for their class.
    <?php else: ?>
      No records to display. Choose a class or search a student by name, then click "View".
    <?php endif; ?>
  </div>
<?php endif; ?>

  </div>
</section>

<style type="text/css">

.ui-autocomplete {
  z-index: 2000 !important;
  max-height: 280px;
  overflow-y: auto;
}
#admit_student_search + .ui-autocomplete .ui-menu-item-wrapper {
  padding: 6px 10px;
  font-size: 13px;
}

/* Admit datesheet filters — high specificity (AdminLTE table rules) */
#admin-admit-datesheet.admit-scope-fs-small .datesheet-table-tabular tbody td,
#admin-admit-datesheet.admit-scope-fs-small .datesheet-table-tabular thead th {
  font-size: 13px !important;
}
#admin-admit-datesheet.admit-scope-fs-medium .datesheet-table-tabular tbody td,
#admin-admit-datesheet.admit-scope-fs-medium .datesheet-table-tabular thead th {
  font-size: 14px !important;
}
#admin-admit-datesheet.admit-scope-fs-large .datesheet-table-tabular tbody td,
#admin-admit-datesheet.admit-scope-fs-large .datesheet-table-tabular thead th {
  font-size: 16px !important;
}
#admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll,
#admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll-en,
#admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll-ur {
  line-height: var(--admit-syll-lh, 2) !important;
}

  /* BMI Status Colors */
.bmi-status.underweight { color: #f59e0b; }
.bmi-status.normal { color: #10b981; }
.bmi-status.overweight { color: #f97316; }
.bmi-status.obese { color: #ef4444; }

.admit-bmi-item i {
    width: 16px;
    margin-right: 2px;
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

/* ---- Layout wrappers ---- */
html, body, .content-wrapper, .content, .container-fluid{ 
  background:#fff !important; 
}
.content{ 
  padding:0 !important; 
}

/* ---- Typography ---- */
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
  border-bottom: 1px solid #cbd5e1;
}

/* ---- Card ---- */
.admit-card{
  max-width:1100px; 
  margin:16px auto; 
  background:#ffffff;
  border-radius:12px; 
  border:1px solid #cbd5e1;
  box-shadow:0 6px 18px rgba(0,0,0,.06); 
  overflow:hidden;
  font-family: 'Times New Roman', Times, serif;
}

/* Admit card font-size presets */
.admit-font-small .fact,
.admit-font-small .chip,
.admit-font-small .datesheet-table td,
.admit-font-small .datesheet-table-tabular td,
.admit-font-small .datesheet-table-tabular thead th,
.admit-font-small .footer-lines {
  font-size: 13px !important;
}
.admit-font-small .syll {
  font-size: 1.05em !important;
}
.admit-font-small .syll-ur {
  font-size: 1.2em !important;
}

.admit-font-medium .fact,
.admit-font-medium .chip,
.admit-font-medium .datesheet-table td,
.admit-font-medium .datesheet-table-tabular td,
.admit-font-medium .datesheet-table-tabular thead th,
.admit-font-medium .footer-lines {
  font-size: 14px !important;
}
.admit-font-medium .syll {
  font-size: 1.2em !important;
}
.admit-font-medium .syll-ur {
  font-size: 1.3em !important;
}

.admit-font-large .fact,
.admit-font-large .chip,
.admit-font-large .datesheet-table td,
.admit-font-large .datesheet-table-tabular td,
.admit-font-large .datesheet-table-tabular thead th,
.admit-font-large .footer-lines {
  font-size: 16px !important;
}
.admit-font-large .syll {
  font-size: 1.35em !important;
}
.admit-font-large .syll-ur {
  font-size: 1.5em !important;
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
  border-bottom: 2px solid #1d4ed8;
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
  color:#111827; 
  font-family: 'Times New Roman', Times, serif;
}
.school-meta .sub{ 
  font-size:18px; 
  line-height:1.2; 
  margin:0; 
  opacity:.95; 
  white-space:nowrap; 
  color:#475569; 
  font-family: 'Times New Roman', Times, serif;
}
.school-meta .sub .dot{ 
  margin:0 6px; 
  color:#94a3b8; 
}

.headline{ 
  text-align:center; 
  font-weight:800; 
  color:#0f172a; 
  margin:6px 16px 10px; 
  font-size:18px; 
  font-family: 'Times New Roman', Times, serif;
}
.headline .ribbon{ 
  display:inline-block; 
  padding:6px 14px; 
  border:2px solid #1d4ed8; 
  border-radius:999px; 
  font-weight:800; 
  letter-spacing:.2px; 
  background: #f8fafc;
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

/* Dues badge */
.due-badge{
  position:absolute; 
  top:8px; 
  right:12px; 
  background:#fff;
  border:1px dashed #ef4444; 
  color:#b91c1c;
  font-size:12px; 
  padding:4px 8px; 
  border-radius:8px; 
  display:flex; 
  align-items:center; 
  gap:6px;
  font-family: 'Times New Roman', Times, serif;
}

/* ---- Student row — border hugs scaled photo (natural dimensions, max 92px) ---- */
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
  background:#f8fafc; 
  border:1px solid #e6ebf2;
  padding:8px 10px; 
  border-radius:8px; 
  color:#0f172a;
  font-family: 'Times New Roman', Times, serif;
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

/* BMI + Attendance strips */
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
  color: #0f172a;
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

/* ---- Datesheet: tabular (Subject | Marks | Syllabus) ---- */
.datesheet-wrap {
  margin-top: 12px;
  padding: 0;
  border: 2px solid #1e293b;
  border-bottom: none;
  border-radius: 6px 6px 0 0;
  overflow: hidden;
  background: #fff;
}

.datesheet-table-tabular {
  width: 100%;
  border-collapse: collapse !important;
  table-layout: fixed;
  background: #fff;
}

.datesheet-table-tabular thead th {
  background: #1e293b !important;
  color: #fff !important;
  border: 1px solid #0f172a !important;
  border-bottom: 2px solid #0f172a !important;
  font-weight: 800;
  padding: 10px 8px;
  font-size: 12px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.datesheet-table-tabular tbody td {
  border: 1px solid #cbd5e1 !important;
  padding: 8px 10px;
  vertical-align: top;
  background: #fff;
}

.datesheet-table-tabular tbody tr.ds-subject-row:nth-child(even) td {
  background: #f8fafc;
}

.datesheet-table-tabular .subject-name-cell {
  font-weight: 700;
  color: #0f172a;
  white-space: normal;
  overflow-wrap: break-word;
  word-break: break-word;
  line-height: 1.25;
  vertical-align: top !important;
}

.datesheet-table-tabular.datesheet-table--three-col .subject-name-cell {
  width: 13%;
}

.datesheet-table-tabular.datesheet-table--two-col .subject-name-cell {
  width: 18%;
}

.datesheet-table-tabular .marks-cell {
  font-weight: 800;
  font-variant-numeric: tabular-nums;
  text-align: center !important;
  vertical-align: middle !important;
  color: #0f172a;
  background: #eef2f7 !important;
  white-space: nowrap;
  width: 7%;
  padding-left: 6px !important;
  padding-right: 6px !important;
}

.datesheet-grouped .ds-date-band td.ds-date-heading {
  background: #334155 !important;
  color: #fff !important;
  border-top: 1px solid #1e293b !important;
  border-bottom: 1px solid #1e293b !important;
  font-weight: 800;
  padding: 10px 12px !important;
  vertical-align: middle !important;
}

.datesheet-table-tabular tbody tr.ds-date-band:first-child td.ds-date-heading {
  border-top: none !important;
}

.datesheet-grouped thead th.ds-th-subject {
  border-end: 1px solid rgba(255,255,255,0.25);
}

.datesheet-grouped thead th.ds-th-marks {
  border-end: 1px solid rgba(255,255,255,0.25);
  width: 1%;
  white-space: nowrap;
}

.datesheet-grouped thead th.ds-th-syll {
  text-align: center !important;
}

.datesheet-grouped tbody tr.ds-subject-row td.subject-name-cell {
  border-end: 1px solid #94a3b8 !important;
}

.datesheet-table--three-col.datesheet-grouped tbody tr.ds-subject-row td.marks-cell {
  border-end: 1px solid #94a3b8 !important;
}

.datesheet-grouped .ds-date-line {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 4px 12px;
}

.datesheet-grouped .ds-day-marks {
  margin-left: auto;
  font-weight: 800;
  white-space: nowrap;
  font-size: 0.95em;
}

.syll{
  white-space: normal;
  word-wrap: break-word;
  font-size: 1.1em;
  line-height: 1.8;
  letter-spacing: 0.01em;
}
.syll-ur{
  direction: rtl;
  text-align: right;
  font-family: 'Jameel Noori Nastaleeq','Jameel Noori Nastaleeq Kasheeda','Nafees','Alvi','Urdu', serif !important;
  font-size: 1.3em;
  line-height: 2.0;
  letter-spacing: 0.02em;
}
.syll-en{
  direction: ltr;
  text-align: left;
  font-family: 'Times New Roman', Times, serif !important;
}

.datesheet-table-tabular.compact thead th,
.datesheet-table-tabular.compact td{
  padding: 4px 6px !important;
  line-height: 1.15 !important;
  vertical-align: middle !important;
}
.datesheet-table-tabular.compact.relax thead th,
.datesheet-table-tabular.compact.relax td{
  padding: 6px 8px !important;
  line-height: 1.2 !important;
}

/* Footer */
.footer-lines{ 
  margin-top:12px; 
  color:#0f172a; 
  font-size:14px; 
  font-family: 'Times New Roman', Times, serif;
}

/* ===============================
   PRINT STYLES
   =============================== */
@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .no-print,
  .page-actions,
  .card.card-outline.card-primary,
  .nav.nav-tabs,
  .row,
  .alert.alert-info,
  .main-header,
  .main-sidebar,
  .main-footer {
    display: none !important;
    visibility: hidden !important;
  }
  
  .admit-card {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 2px solid #000 !important;
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
  
  @page {
    size: A4 portrait;
    margin: 1cm;
  }
  
  .school-meta h1 {
    font-size: 48px !important;
    font-weight: 900 !important;
    color: #000 !important;
  }
  
  .admit-header {
    border-bottom: 3px double #000 !important;
  }

  .admit-card .avatar-photo-shell {
    border: 2px solid #000 !important;
    background: #fafafa !important;
  }
  
  .admit-bmi-strip,
  .admit-attendance-strip {
    background: #f0f0f0 !important;
    border-start: 4px double #000 !important;
  }
  
  .datesheet-wrap {
    border: 2px solid #000 !important;
    border-bottom: none !important;
    border-radius: 6px 6px 0 0 !important;
  }
  
  .datesheet-table-tabular thead th {
    background: #1e293b !important;
    color: #fff !important;
    border: 1pt solid #000 !important;
  }

  .datesheet-table-tabular tbody td {
    border: 1pt solid #000 !important;
    color: #000 !important;
  }

  .datesheet-table-tabular .marks-cell {
    background: #e5e5e5 !important;
  }

  .datesheet-grouped .ds-date-band td.ds-date-heading {
    background: #404040 !important;
    color: #fff !important;
  }

}
</style>

<style id="lh-override">
  #admin-admit-datesheet .datesheet-table.compact.relax .syll,
  #admin-admit-datesheet .datesheet-table.compact.relax .syll-en,
  #admin-admit-datesheet .datesheet-table.compact.relax .syll-ur,
  #admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll,
  #admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll-en,
  #admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll-ur {
    line-height: var(--admit-syll-lh, 2) !important;
  }
  @media print{
    #admin-admit-datesheet .datesheet-table.compact.relax .syll,
    #admin-admit-datesheet .datesheet-table.compact.relax .syll-en,
    #admin-admit-datesheet .datesheet-table.compact.relax .syll-ur,
    #admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll,
    #admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll-en,
    #admin-admit-datesheet .datesheet-table-tabular.compact.relax .syll-ur {
      line-height: var(--admit-syll-lh, 2) !important;
    }
  }
</style>

<?= $this->endSection() ?>