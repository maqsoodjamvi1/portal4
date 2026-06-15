<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&display=swap" rel="stylesheet">

<?= view('components/page_header', [
    'title' => 'Daily Recitation',
    'icon' => 'fas fa-quran',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Hifz Program', 'url' => base_url('admin/hifz/students')],
        ['label' => 'Daily Recitation', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card card-primary card-outline hifz-rec-card-main">
    <div class="card-header">
      <h3 class="card-title">Sabaq · Sabqi · Manzil · Mutalia</h3>
    </div>
    <div class="card-body">
      <div class="row align-items-end mb-3 hifz-rec-filters">
        <div class="col-md-4 col-12 mb-2 mb-md-0">
          <label class="small text-muted mb-1">Date</label>
          <input type="date" id="recitation-date" class="form-control" value="<?= esc($today ?? date('Y-m-d')) ?>">
        </div>
        <div class="col-md-6 col-12 mb-2 mb-md-0">
          <label class="small text-muted mb-1">Hifz Section</label>
          <select id="recitation-section" class="form-control">
            <option value="">— Select section —</option>
            <?php foreach ($sections ?? [] as $sec): ?>
              <option value="<?= (int) $sec['hifz_sec_id'] ?>"><?= esc($sec['section_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 text-md-end">
          <span class="badge text-bg-secondary" id="recitation-progress">—</span>
        </div>
      </div>

      <?php if (empty($sections)): ?>
        <div class="alert alert-warning mb-0">
          No Hifz sections available. Create sections and assign teachers first.
        </div>
      <?php else: ?>
        <p class="text-muted small mb-2" id="recitation-type-hint"></p>
        <div id="recitation-type-tabs-wrap" class="d-none mb-3"></div>
        <div id="recitation-students">
          <p class="text-center text-muted py-5">Select a Hifz section to load students.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<style>
.hifz-rec-page-header h1 { color: #0d5c46; }
.hifz-rec-card-main { border-top: 3px solid #0d5c46; background: linear-gradient(180deg, #faf8f3 0%, #fff 120px); }
.hifz-rec-tabs .nav-link { font-weight: 600; border-radius: .4rem; margin-right: .35rem; margin-bottom: .35rem; padding: .5rem 1rem; min-height: 44px; }
.hifz-rec-tabs .nav-link.active.mutalia { background: #0d5c46; color: #fff; }
.hifz-rec-tabs .nav-link.active.sabaq { background: #1a7a4c; color: #fff; }
.hifz-rec-tabs .nav-link.active.sabqi { background: #17a2b8; color: #fff; }
.hifz-rec-tabs .nav-link.active.manzil { background: #c9a227; color: #212529; }
.hifz-rec-tabs .nav-link:not(.active) { background: #f0f2f0; color: #495057; }
.hifz-student-acc-item { border: 1px solid #d4e8da; border-radius: .5rem; overflow: hidden; box-shadow: 0 2px 8px rgba(13,92,70,.08); margin-bottom: .75rem; }
.hifz-student-acc-item.saved { border-color: #28a745; box-shadow: 0 2px 12px rgba(40,167,69,.15); }
.hifz-student-acc-header { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; padding: .75rem 1rem; cursor: pointer; background: #fff; min-height: 56px; }
.hifz-student-acc-header:hover { background: #f8faf9; }
.hifz-student-acc-header .hifz-acc-photo { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #c9a227; flex-shrink: 0; }
.hifz-student-acc-header .hifz-acc-main { flex: 1; min-width: 140px; }
.hifz-student-acc-header .hifz-acc-name { font-weight: 700; color: #0d5c46; margin: 0; font-size: 1rem; }
.hifz-student-acc-header .hifz-acc-meta { font-size: .8rem; color: #6c757d; }
.hifz-student-acc-header .hifz-acc-chevron { color: #0d5c46; font-size: 1.1rem; transition: transform .2s; }
.hifz-student-acc-item.is-expanded .hifz-acc-chevron { transform: rotate(180deg); }
.hifz-student-acc-body { display: none; border-top: 1px solid #e8f0ea; }
.hifz-student-acc-item.is-expanded .hifz-student-acc-body { display: block; }
.hifz-student-acc-body .hifz-rec-tabs-wrap { padding: .65rem 1rem 0; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.hifz-student-card { border: 1px solid #d4e8da; border-radius: .5rem; overflow: hidden; box-shadow: 0 2px 8px rgba(13,92,70,.08); }
.hifz-student-card.saved { border-color: #28a745; box-shadow: 0 2px 12px rgba(40,167,69,.15); }
.hifz-student-hero { display: flex; flex-wrap: wrap; gap: 1rem; padding: 1rem 1.1rem; background: linear-gradient(135deg, #0d5c46 0%, #1a7a4c 55%, #2d8f5f 100%); color: #fff; align-items: center; }
.hifz-student-photo { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid #c9a227; flex-shrink: 0; background: #fff; }
.hifz-student-hero-main { flex: 1; min-width: 180px; }
.hifz-student-hero-name { font-size: 1.15rem; font-weight: 700; margin: 0 0 .15rem; }
.hifz-student-hero-reg { opacity: .9; font-size: .85rem; }
.hifz-para-badge { display: inline-flex; align-items: center; gap: .5rem; background: rgba(255,255,255,.15); border: 1px solid rgba(201,162,39,.6); border-radius: .35rem; padding: .35rem .65rem; margin-top: .4rem; }
.hifz-para-badge-num { font-weight: 700; font-size: 1.1rem; line-height: 1; }
.hifz-para-badge-ar { font-family: 'Amiri Quran', serif; font-size: 1rem; line-height: 1.3; }
.hifz-para-inline { display: inline-flex; align-items: center; gap: .35rem; }
.hifz-para-inline-num { font-weight: 700; }
.hifz-para-inline-ar { font-family: 'Amiri Quran', serif; direction: rtl; }
.hifz-manzil-today { margin-bottom: .75rem; padding: .65rem .75rem; background: #fffef5; border: 1px solid #f0e0a8; border-radius: .35rem; }
.hifz-manzil-today-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .35rem; }
.hifz-manzil-today-chip { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .65rem; border: 2px solid #e0a800; border-radius: .35rem; background: #fff; font-weight: 600; }
.hifz-manzil-pool-wrap { margin-top: .5rem; }
.hifz-manzil-week-stack { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }
.hifz-manzil-week-row .hifz-manzil-cal-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.hifz-manzil-cal { width: 100%; min-width: 0; border-collapse: collapse; table-layout: fixed; }
.hifz-manzil-cal th, .hifz-manzil-cal td { font-size: .75rem; padding: .35rem .25rem; vertical-align: top; border: 1px solid #e8dcc0; }
.hifz-manzil-cal th.is-today, .hifz-manzil-cal td.is-today { background: #fff8dc; }
.hifz-manzil-cal td.has-entry { background: #f8fff8; }
.hifz-student-hero-side { min-width: 200px; flex: 1; max-width: 320px; }
.hifz-progress-wrap { margin-top: .35rem; }
.hifz-progress-bar { height: 10px; background: rgba(0,0,0,.2); border-radius: 5px; overflow: hidden; }
.hifz-progress-fill { height: 100%; background: linear-gradient(90deg, #c9a227, #f0d878); border-radius: 5px; transition: width .3s; }
.hifz-progress-text { font-size: .8rem; margin-top: .25rem; display: block; }
.hifz-status-pills { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: flex-end; }
.hifz-status-pill { font-size: .72rem; padding: .2rem .5rem; border-radius: 1rem; background: rgba(255,255,255,.2); }
.hifz-status-pill.is-on { background: #c9a227; color: #1a1a1a; font-weight: 600; }
.hifz-rec-block { padding: 1rem 1.1rem; background: #fff; }
.hifz-rec-block.mutalia { border-top: 3px solid #6f42c1; background: #fcfbff; }
.hifz-rec-block.sabaq { border-top: 3px solid #28a745; background: #f8fffa; }
.hifz-rec-block.sabqi { border-top: 3px solid #17a2b8; }
.hifz-rec-block.manzil { border-top: 3px solid #e0a800; }
.hifz-lesson-card { background: linear-gradient(135deg, #e8f8ee 0%, #f4fff8 100%); border: 1px solid #b8e6c8; border-radius: .45rem; padding: 1rem; margin-bottom: 1rem; }
.hifz-lesson-card-title { font-size: 1.05rem; font-weight: 700; color: #0d5c46; margin: 0 0 .25rem; }
.hifz-lesson-card-meta { color: #5a6c63; font-size: .88rem; }
.hifz-quality-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin: .75rem 0; }
.hifz-quality-chip { min-height: 44px; padding: .45rem .85rem; border: 2px solid #dee2e6; border-radius: .4rem; background: #fff; font-weight: 600; font-size: .85rem; cursor: pointer; transition: all .15s; }
.hifz-quality-chip:hover { border-color: #0d5c46; }
.hifz-quality-chip.is-selected { border-color: #0d5c46; background: #0d5c46; color: #fff; }
.hifz-quality-chip.is-saving { opacity: .55; pointer-events: none; }
.hifz-quality-chip[data-quality="weak"].is-selected { background: #dc3545; border-color: #dc3545; }
.hifz-remarks-toggle { min-height: 44px; margin-top: .5rem; }
.hifz-remarks-panel textarea { resize: vertical; }
.hifz-toggle-link { display: inline-block; min-height: 44px; line-height: 44px; padding: 0 .5rem; color: #0d5c46; font-weight: 600; cursor: pointer; border: none; background: none; text-align: left; }
.hifz-toggle-link:hover { text-decoration: underline; color: #094a38; }
.hifz-quran-progress-panel.d-none { display: none !important; }
.hifz-history-panel.d-none { display: none !important; }
.hifz-sabqi-chip-readonly { cursor: default; }
.hifz-action-btns { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .65rem; margin: .75rem 0; }
.hifz-action-btn { min-height: 52px; text-align: left; padding: .65rem .85rem; border: 2px solid #dee2e6; border-radius: .4rem; background: #fff; cursor: pointer; transition: all .15s; }
.hifz-action-btn:hover { border-color: #0d5c46; }
.hifz-action-btn.is-active { border-color: #0d5c46; background: #f0faf5; box-shadow: 0 0 0 2px rgba(13,92,70,.2); }
.hifz-action-btn strong { display: block; color: #0d5c46; }
.hifz-action-btn small { color: #6c757d; }
.hifz-history-title { font-weight: 700; color: #0d5c46; margin: 1rem 0 .5rem; font-size: .95rem; }
.hifz-history-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.hifz-history-table { width: 100%; font-size: .82rem; margin: 0; }
.hifz-history-table th { background: #f0faf5; color: #0d5c46; white-space: nowrap; }
.hifz-btn-save-mutalia { background: #0d5c46; border-color: #0d5c46; color: #fff; min-height: 44px; padding: .5rem 1.25rem; }
.hifz-btn-save-mutalia:hover { background: #094a38; color: #fff; }
.hifz-btn-save-sabaq { min-height: 44px; padding: .5rem 1.5rem; font-weight: 600; }
.hifz-sabqi-chip { display: inline-flex; align-items: center; margin: .2rem .35rem .2rem 0; padding: .25rem .5rem; border: 1px solid #bee5eb; border-radius: .25rem; background: #fff; font-size: .85rem; }
.hifz-sabqi-chip.is-current { border-color: #6f42c1; background: #faf7ff; }
.hifz-manzil-pool { margin: .5rem 0; padding: .55rem .65rem; background: #fff; border: 1px solid #f0e0a8; border-radius: .35rem; }
.hifz-manzil-para-strip { display: flex; flex-wrap: wrap; gap: .4rem; }
.hifz-manzil-para-selectable { cursor: pointer; min-width: 100px; padding: .4rem .5rem; border: 1px solid #e8dcc0; border-radius: .3rem; background: #fffef8; font-size: .82rem; }
.hifz-manzil-para-selectable.is-selected { border-color: #ffc107; background: #fff8dc; }
.hifz-quran-progress { margin: .75rem 0; padding: .65rem .75rem; background: #f5f0ff; border: 1px solid #e0d8f0; border-radius: .35rem; }
.hifz-mutalia-summary-stats { font-size: .88rem; color: #5a6c63; }
.hifz-mutalia-timeline-stack { display: flex; flex-direction: column; gap: 1rem; max-height: 420px; overflow-y: auto; margin-top: .75rem; }
.hifz-mutalia-week-cal { width: 100%; min-width: 520px; border-collapse: collapse; table-layout: fixed; }
.hifz-mutalia-week-cal th, .hifz-mutalia-week-cal td { font-size: .75rem; padding: .4rem .3rem; vertical-align: top; border: 1px solid #e0d8f0; text-align: center; }
.hifz-mutalia-week-cal th { background: #f5f0ff; color: #0d5c46; }
.hifz-mutalia-week-cal td.has-entry { background: #faf8ff; cursor: pointer; }
.hifz-mutalia-week-cal td.has-entry:hover { background: #efe8ff; }
.hifz-mutalia-week-cal td.is-today { outline: 2px solid #6f42c1; }
.hifz-mutalia-week-cal td.is-missed { background: #fff5f5; color: #c82333; font-style: italic; }
.hifz-mutalia-week-cal .cell-lines { font-weight: 700; font-size: .85rem; }
.hifz-mutalia-week-cal .cell-lesson { font-size: .7rem; color: #6c757d; }
.hifz-mutalia-week-cal .cell-sabaq { font-size: .72rem; margin-top: .15rem; }
.hifz-mutalia-form-wrap { background: #fff; border: 1px solid #e0d8f0; border-radius: .4rem; padding: 1rem; margin-bottom: 1rem; }
.hifz-mutalia-remarks-wrap.d-none { display: none !important; }
.hifz-mutalia-sabaq-section { margin: 1rem 0; padding: 1rem; border: 1px solid #b8e6c8; border-radius: .45rem; background: #f8fffa; }
.hifz-mutalia-sabaq-section.is-pending { border-color: #ffc107; background: #fffef5; }
.hifz-mutalia-step-title { font-size: .95rem; font-weight: 700; color: #0d5c46; margin: 0 0 .75rem; }
@media (max-width: 767px) {
  .hifz-student-hero { flex-direction: column; align-items: flex-start; }
  .hifz-student-hero-side { max-width: 100%; width: 100%; }
  .hifz-status-pills { justify-content: flex-start; }
  .hifz-rec-block .row > [class*="col-"] { flex: 0 0 100%; max-width: 100%; margin-bottom: .5rem; }
  .hifz-remarks-toggle { width: 100%; }
  .hifz-toggle-link { width: 100%; }
  .hifz-rec-block { padding: .75rem; }
  .hifz-student-acc-header { padding: .65rem .75rem; }
}
</style>

<script src="<?= base_url('assets/js/hifz_recitation.js') ?>"></script>
<script>
$(function () {
  var qualities = <?= json_encode($qualities ?? []) ?>;
  var paras = <?= json_encode(array_values(array_map(static function ($p) {
      return [
          'no'       => (int) ($p['juz_no'] ?? 0),
          'name_ar'  => $p['name_ar'] ?? '',
          'title'    => $p['title'] ?? ('Para ' . ($p['juz_no'] ?? '')),
          'name_en'  => $p['name_en'] ?? '',
          'title_ar' => $p['title_ar'] ?? '',
      ];
  }, $paras ?? hifzJuzCatalog()))) ?>;
  var paraByNo = {};
  paras.forEach(function (p) { if (p.no) paraByNo[p.no] = p; });
  var defaultAvatar = '<?= base_url('assets/img/avatar-student.png') ?>';
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  var loadUrl = '<?= base_url('admin/hifz/recitation/load') ?>';
  var saveUrl = '<?= base_url('admin/hifz/recitation/save') ?>';

  var sectionPeers = [];
  var loadedStudents = [];
  var activeRecType = 'mutalia';
  var expandedStudentId = 0;
  var currentSecId = 0;
  var currentDate = '';

  var recTypes = [
    { key: 'mutalia', label: 'Mutalia & Sabaq', hint: 'Add today\'s lines, then grade Sabaq. Para advances at 320 lines.' },
    { key: 'sabqi', label: 'Sabqi', hint: 'Revision of active paras (set by the system). Set listener and mistakes, then tap quality to save.' },
    { key: 'manzil', label: 'Manzil', hint: 'Today\'s Manzil paras rotate automatically. Optional: change from pool. History available on demand.' }
  ];

  var loadDebounceTimer = null;
  currentDate = $('#recitation-date').val() || '';

  var esc = window.HifzRecitation && window.HifzRecitation.esc
    ? window.HifzRecitation.esc
    : function (s) { return $('<div/>').text(s == null ? '' : String(s)).html(); };

  function qualityOptions(selected) {
    if (window.HifzRecitation && window.HifzRecitation.qualityOptions) {
      return window.HifzRecitation.qualityOptions(qualities, selected);
    }
    var html = '<option value="">—</option>';
    $.each(qualities, function (val, label) {
      html += '<option value="' + esc(val) + '"' + (selected === val ? ' selected' : '') + '>' + esc(label) + '</option>';
    });
    return html;
  }

  function paraOptions(selected, student) {
    var html = '<option value="">—</option>';
    var reverse = student && student.is_para_reverse;
    var list = paras.slice();
    if (reverse) {
      list.sort(function (a, b) { return b.no - a.no; });
    }
    list.forEach(function (p) {
      var label = 'Para ' + p.no;
      html += '<option value="' + p.no + '"' + (selected === p.no ? ' selected' : '') + '>' + label + '</option>';
    });
    return html;
  }

  function paraNameAr(no, fallback) {
    var p = paraByNo[parseInt(no, 10)];
    return (p && p.name_ar) ? p.name_ar : (fallback || '');
  }

  function formatParaBadge(no, nameAr) {
    nameAr = nameAr || paraNameAr(no, '');
    var html = '<span class="hifz-para-badge"><span class="hifz-para-badge-num">' + esc(String(no)) + '</span>';
    if (nameAr) html += '<span class="hifz-para-badge-ar" dir="rtl">' + esc(nameAr) + '</span>';
    return html + '</span>';
  }

  function formatParaInline(no, nameAr) {
    nameAr = nameAr || paraNameAr(no, '');
    var html = '<span class="hifz-para-inline"><span class="hifz-para-inline-num">' + esc(String(no)) + '</span>';
    if (nameAr) html += '<span class="hifz-para-inline-ar">' + esc(nameAr) + '</span>';
    return html + '</span>';
  }

  function setHint() {
    var t = recTypes.find(function (x) { return x.key === activeRecType; });
    $('#recitation-type-hint').text(t ? t.hint : '');
  }

  function renderTypeTabs() {
    var html = '<ul class="nav nav-pills hifz-rec-tabs flex-nowrap">';
    recTypes.forEach(function (t) {
      var active = t.key === activeRecType ? ' active ' + t.key : '';
      html += '<li class="nav-item"><a class="nav-link' + active + '" href="#" data-type="' + t.key + '">' + esc(t.label) + '</a></li>';
    });
    html += '</ul>';
    return html;
  }

  function studentAccItem(sid) {
    return $('.hifz-student-acc-item[data-student-id="' + sid + '"]');
  }

  function badgeSaved(flag) {
    return flag ? ' <span class="badge text-bg-success">Saved</span>' : '';
  }

  function renderProgressBar(s) {
    var pct = Math.min(100, Math.max(0, parseInt(s.progress_percent, 10) || 0));
    return '<div class="hifz-progress-wrap">' +
      '<div class="hifz-progress-bar"><div class="hifz-progress-fill" style="width:' + pct + '%"></div></div>' +
      '<span class="hifz-progress-text">' + esc(s.progress_label || '') + ' (' + pct + '%)</span></div>';
  }

  function renderStatusPills(s) {
    var items = [
      { on: s.record_mutalia, label: 'Mutalia' },
      { on: s.record_sabaq, label: 'Sabaq' },
      { on: s.record_sabqi, label: 'Sabqi' },
      { on: s.record_manzil, label: 'Manzil' }
    ];
    var html = '<div class="hifz-status-pills">';
    items.forEach(function (it) {
      html += '<span class="hifz-status-pill' + (it.on ? ' is-on' : '') + '">' + esc(it.label) + (it.on ? ' ✓' : '') + '</span>';
    });
    return html + '</div>';
  }

  function renderStudentHero(s) {
    var photo = s.photo_url || defaultAvatar;
    var nameAr = s.current_para_name_ar || paraNameAr(s.current_para, '');
    return '<div class="hifz-student-hero">' +
      '<img class="hifz-student-photo" src="' + esc(photo) + '" alt="">' +
      '<div class="hifz-student-hero-main">' +
      '<h4 class="hifz-student-hero-name">' + esc(s.student_name) + '</h4>' +
      '<div class="hifz-student-hero-reg">' + esc(s.reg_no || '') + '</div>' +
      formatParaBadge(s.current_para, nameAr) +
      '</div>' +
      '<div class="hifz-student-hero-side">' + renderProgressBar(s) + renderStatusPills(s) + '</div></div>';
  }

  function renderQualityChips(selected, hiddenClass, saveType) {
    hiddenClass = hiddenClass || 'js-sabaq-quality';
    var html = '<div class="hifz-quality-chips">';
    $.each(qualities, function (val, label) {
      var cls = 'hifz-quality-chip' + (selected === val ? ' is-selected' : '');
      var saveAttr = saveType ? ' data-save-type="' + esc(saveType) + '"' : '';
      html += '<button type="button" class="' + cls + '" data-quality="' + esc(val) + '"' + saveAttr + '>' + esc(label) + '</button>';
    });
    return html + '</div><input type="hidden" class="' + hiddenClass + '" value="' + esc(selected || '') + '">';
  }

  function renderMistakeInputs(prefix, hard, soft) {
    return '<div class="col-md-2 col-6"><label class="small">Hard mistakes</label>' +
      '<input type="number" class="form-control form-control-sm js-' + prefix + '-hard" min="0" value="' + (hard || 0) + '"></div>' +
      '<div class="col-md-2 col-6"><label class="small">Soft mistakes</label>' +
      '<input type="number" class="form-control form-control-sm js-' + prefix + '-soft" min="0" value="' + (soft || 0) + '"></div>';
  }

  function renderListenerRow(s, prefix, b) {
    var peers = '<option value="">—</option>';
    (s.section_peers || sectionPeers).forEach(function (p) {
      peers += '<option value="' + p.student_id + '"' + (b.listener_student_id == p.student_id ? ' selected' : '') + '>' + esc(p.student_name) + '</option>';
    });
    var fellowHide = (b.listener_type === 'fellow') ? '' : ' style="display:none"';
    return '<div class="col-md-3 col-6"><label class="small">Listener</label>' +
      '<select class="form-control form-control-sm js-' + prefix + '-listener-type">' +
      '<option value="teacher"' + (b.listener_type === 'teacher' ? ' selected' : '') + '>Teacher</option>' +
      '<option value="fellow"' + (b.listener_type === 'fellow' ? ' selected' : '') + '>Class fellow</option></select></div>' +
      '<div class="col-md-4 col-12 js-' + prefix + '-fellow-wrap"' + fellowHide + '><label class="small">Fellow student</label>' +
      '<select class="form-control form-control-sm js-' + prefix + '-fellow">' + peers + '</select></div>';
  }

  function renderRemarksToggle(prefix, value) {
    var has = (value || '').length > 0;
    var openCls = has ? '' : ' d-none';
    var btnLabel = has ? 'Hide remarks' : 'Add remarks (optional)';
    return '<button type="button" class="hifz-remarks-toggle btn btn-outline-secondary btn-sm js-' + prefix + '-remarks-btn">' + esc(btnLabel) + '</button>' +
      '<div class="hifz-remarks-panel js-' + prefix + '-remarks-wrap' + openCls + ' mt-2">' +
      '<textarea class="form-control js-' + prefix + '-remarks" rows="2" placeholder="Optional remarks">' + esc(value || '') + '</textarea></div>';
  }

  function readRemarksFromScope($scope, prefix) {
    var $panel = $scope.find('.js-' + prefix + '-remarks-wrap');
    if ($panel.hasClass('d-none')) return '';
    return $scope.find('.js-' + prefix + '-remarks').val() || '';
  }

  function renderHistoryCalendarGrid(cal) {
    if (!cal) return '<p class="text-muted small mb-0">No history in this period.</p>';
    var weeks = [
      { title: cal.this_week_label || 'This week', days: cal.this_week || [] },
      { title: cal.last_week_label || 'Previous week', days: cal.last_week || [] }
    ];
    var cols = cal.weekday_columns || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var html = '<div class="hifz-manzil-week-stack">';
    weeks.forEach(function (wk) {
      if (!wk.days || !wk.days.length) return;
      html += '<div class="hifz-manzil-week-row"><div class="small fw-bold text-muted mb-1">' + esc(wk.title) + '</div>' +
        '<div class="hifz-manzil-cal-wrap"><table class="hifz-manzil-cal"><thead><tr>';
      cols.forEach(function (lbl, i) {
        var d = wk.days[i] || {};
        var cls = d.date === currentDate ? ' is-today' : '';
        html += '<th class="' + cls + '"><span class="d-block text-uppercase">' + esc(lbl) + '</span>';
        if (d.date) html += '<span class="d-block small text-muted">' + esc(d.date) + '</span>';
        html += '</th>';
      });
      html += '</tr></thead><tbody><tr>';
      wk.days.forEach(function (d) {
        var cls = (d.date === currentDate ? ' is-today' : '') + (d.has_entry ? ' has-entry' : '');
        var body = d.has_entry
          ? '<div dir="rtl" class="hifz-para-inline-ar">' + esc((d.paras || []).join(' · ')) + '</div>' +
            '<div class="text-muted small">' + esc(d.quality || '') +
            (d.listener ? ' · ' + esc(d.listener) : '') +
            (d.mistakes ? ' · ' + esc(d.mistakes) : '') + '</div>'
          : '<span class="text-muted fst-italic">—</span>';
        html += '<td class="' + cls + '">' + body + '</td>';
      });
      html += '</tr></tbody></table></div></div>';
    });
    return html + '</div>';
  }

  function renderTwoWeekHistoryPanel(title, cal, panelKey) {
    panelKey = panelKey || 'history';
    var grid = renderHistoryCalendarGrid(cal);
    return '<div class="hifz-history-toggle-wrap mt-3">' +
      '<button type="button" class="hifz-toggle-link js-history-toggle" data-panel="' + esc(panelKey) + '">' +
      '<i class="fas fa-calendar-alt me-1"></i> Show history (2 weeks)</button>' +
      '<div class="hifz-history-panel d-none js-history-panel-' + esc(panelKey) + '">' +
      '<div class="hifz-history-title"><i class="fas fa-calendar-alt"></i> ' + esc(title) + '</div>' +
      grid + '</div></div>';
  }

  function renderMutaliaQuranBar(s) {
    var pct = s.quran_progress_percent || 0;
    return '<div class="hifz-quran-progress-wrap mb-2">' +
      '<button type="button" class="hifz-toggle-link js-quran-progress-toggle"><i class="fas fa-book-quran me-1"></i> Show Quran progress</button>' +
      '<div class="hifz-quran-progress-panel d-none mt-2">' +
      '<div class="hifz-quran-progress">' +
      '<div class="small fw-bold mb-1"><i class="fas fa-book-quran me-1"></i> Full Quran memorization</div>' +
      '<div class="hifz-progress-bar"><div class="hifz-progress-fill" style="width:' + pct + '%"></div></div>' +
      '<span class="hifz-progress-text">' + esc(s.quran_progress_label || '') + '</span></div></div></div>';
  }

  function renderGradingBlock(s, prefix, data, saveType, historyTitle, calendar) {
    data = data || {};
    return '<div class="row mt-2 mb-2">' + renderListenerRow(s, prefix, data) + renderMistakeInputs(prefix, data.hard_mistakes, data.soft_mistakes) + '</div>' +
      '<label class="fw-bold d-block mb-1">Recitation quality</label>' +
      renderQualityChips(data.quality || '', 'js-' + prefix + '-quality', saveType) +
      renderRemarksToggle(prefix, data.remarks || '') +
      (historyTitle ? renderTwoWeekHistoryPanel(historyTitle, calendar, prefix + '-history') : '');
  }

  function renderMutaliaSabaqSection(s) {
    var m = s.mutalia || {};
    var b = m.sabaq || s.sabaq || {};
    if (s.record_sabaq && b.quality) {
      var qLabel = qualities[b.quality] || b.quality;
      return '<div class="alert alert-success py-2 px-3 small mb-3"><i class="fas fa-check-circle me-1"></i> Sabaq graded: <strong>' + esc(qLabel) + '</strong></div>' +
        renderTwoWeekHistoryPanel('Sabaq history (Mon–Sat)', b.calendar, 'sabaq-history');
    }
    if (!m.show_sabaq_form && !b.ready) {
      if (!s.record_mutalia) {
        return '<p class="text-muted small mb-3"><i class="fas fa-headphones me-1"></i> ' + esc(b.note || 'Add the first Mutalia lesson to begin.') + '</p>';
      }
      return '';
    }
    if (!m.show_sabaq_form) return '';
    var pendingCls = s.record_mutalia && !s.record_sabaq ? ' is-pending' : '';
    var hint = '';
    if (b.lines_count) {
      hint = '<p class="text-muted small mb-2">Previous lesson · <strong>' + esc(String(b.lines_count)) + '</strong> lines</p>';
    }
    return '<div class="hifz-mutalia-sabaq-section' + pendingCls + '">' +
      '<h6 class="hifz-mutalia-step-title"><i class="fas fa-headphones me-1"></i> Grade Sabaq</h6>' +
      hint +
      renderGradingBlock(s, 'sabaq', b, 'sabaq', 'Sabaq history (Mon–Sat)', b.calendar) +
      '</div>';
  }

  function renderMutaliaBlock(s) {
    var m = s.mutalia || {};
    var linesDone = m.lines_done_in_para != null ? m.lines_done_in_para : (s.lines_done_in_para || 0);
    var totalLines = m.para_total_lines || s.para_total_lines || 320;
    var linesRemaining = m.lines_remaining != null ? m.lines_remaining : Math.max(0, totalLines - linesDone);
    var maxLines = linesRemaining > 0 ? linesRemaining : 0;
    var defaultLines = maxLines > 0 ? Math.max(1, Math.min(maxLines, parseInt(m.default_lines, 10) || parseInt(m.last_mutalia_lines, 10) || 3)) : 0;
    var nameAr = m.para_name_ar || s.current_para_name_ar || paraNameAr(s.current_para, '');
    var lessons = m.lessons_in_para || 0;
    var statsLine = 'Lessons: <strong>' + lessons + '</strong> · Lines: <strong>' + linesDone + '/' + totalLines + '</strong>';
    var maxHint = maxLines > 0
      ? (maxLines + ' max remaining' + (maxLines <= 50 ? ' · entering ' + maxLines + ' completes this para' : ''))
      : 'Para complete — no lines left';

    var statusMsg = '';
    if (!m.show_mutalia_form && !m.show_sabaq_form) {
      var msg = '';
      if (s.record_mutalia && s.record_sabaq) {
        msg = 'Today\'s Mutalia and Sabaq are complete.';
      } else if (s.record_mutalia) {
        msg = 'Mutalia added for today. Grade Sabaq on the previous lesson below.';
      } else if (m.entry_message) {
        msg = m.entry_message;
      } else {
        msg = 'No actions available for this date.';
      }
      statusMsg = '<div class="alert alert-info py-2 px-3 small mb-3"><i class="fas fa-info-circle me-1"></i>' + esc(msg) + '</div>';
    } else if (!m.show_mutalia_form && m.show_sabaq_form && !s.record_mutalia) {
      statusMsg = '<div class="alert alert-warning py-2 px-3 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> Grade Sabaq on the previous lesson before adding new Mutalia.</div>';
    }

    var hasRemarks = (m.remarks || '').length > 0;
    var formHtml = '';
    if (m.show_mutalia_form && maxLines > 0) {
      formHtml = '<div class="hifz-mutalia-form-wrap">' +
        '<h6 class="hifz-mutalia-step-title"><i class="fas fa-book-open me-1"></i> Add Mutalia</h6>' +
        '<div class="row align-items-end">' +
        '<div class="col-md-4 col-sm-6 mb-2 mb-md-0"><label class="fw-bold">Lines</label>' +
        '<input type="number" class="form-control js-mutalia-lines" min="1" max="' + maxLines + '" value="' + defaultLines + '" data-max-lines="' + maxLines + '">' +
        '<small class="text-muted js-mutalia-lines-hint">' + esc(maxHint) + '</small></div>' +
        '<div class="col-md-8 col-sm-6">' + renderRemarksToggle('mutalia', '') + '</div></div>' +
        '<button type="button" class="btn hifz-btn-save-mutalia mt-3 js-save-student" data-type="mutalia">' +
        '<i class="fas fa-plus me-1"></i> Add Mutalia</button></div>';
    } else if (m.show_mutalia_form && maxLines <= 0) {
      formHtml = '<div class="alert alert-warning py-2 px-3 small mb-3">No lines remaining in this para.</div>';
    } else if (s.record_mutalia) {
      formHtml = '<div class="alert alert-light border py-2 px-3 small mb-3"><i class="fas fa-check text-success me-1"></i> Mutalia added for today (' + esc(String(m.lines_count || '')) + ' lines).</div>';
    }

    var sabaqFirst = m.show_sabaq_form && !m.show_mutalia_form && !s.record_mutalia;
    var sabaqHtml = renderMutaliaSabaqSection(s);
    var workflowHtml = sabaqFirst ? (sabaqHtml + statusMsg + formHtml) : (statusMsg + formHtml + sabaqHtml);

    return '<div class="hifz-rec-block mutalia" data-block="mutalia">' +
      '<h5 class="mb-2" style="color:#0d5c46"><i class="fas fa-book-open me-1"></i> Mutalia &amp; Sabaq — ' + formatParaInline(s.current_para, nameAr) + '</h5>' +
      '<div class="hifz-mutalia-summary-stats mb-2">' + statsLine + '</div>' +
      renderMutaliaQuranBar(s) + workflowHtml + '</div>';
  }

  function renderSabqiBlock(s) {
    var b = s.sabqi || {};
    var chips = '';
    (b.active_paras || []).forEach(function (p, i) {
      var label = (b.active_para_labels && b.active_para_labels[i]) ? b.active_para_labels[i] : ('Para ' + p);
      var isCur = p === s.current_para;
      chips += '<span class="hifz-sabqi-chip hifz-sabqi-chip-readonly' + (isCur ? ' is-current' : '') + '">' + esc(label) + '</span>';
    });
    var listenerData = {
      listener_type: b.listener_type || 'teacher',
      listener_student_id: b.listener_student_id || 0,
      quality: b.quality || '',
      hard_mistakes: b.hard_mistakes,
      soft_mistakes: b.soft_mistakes,
      remarks: b.remarks || ''
    };
    return '<div class="hifz-rec-block sabqi" data-block="sabqi">' +
      '<p class="small text-muted mb-1">Sabqi paras (automatic)</p><div class="mb-2">' + (chips || '<span class="text-muted">None</span>') + '</div>' +
      renderGradingBlock(s, 'sabqi', listenerData, 'sabqi', 'Sabqi history (Mon–Sat)', b.calendar) +
      '</div>';
  }

  function renderManzilBlock(s) {
    var b = s.manzil || {};
    var maxPick = parseInt(s.manzil_paras_per_day, 10) || 1;
    var todayList = b.juz_list || b.today_juz_list || b.suggested_juz_list || [];
    var selected = todayList.slice();
    var suggested = b.suggested_juz_list || [];

    var todayChips = '';
    todayList.forEach(function (j) {
      var nameAr = paraNameAr(j, '');
      (b.pool_paras || []).forEach(function (card) {
        if (card.juz_no === j && card.name_ar) nameAr = card.name_ar;
      });
      todayChips += '<span class="hifz-manzil-today-chip js-manzil-today-chip" data-para="' + j + '">' +
        formatParaInline(j, nameAr) + '</span>';
    });
    if (!todayChips) todayChips = '<span class="text-muted small">No paras in rotation (pool empty).</span>';

    var poolHtml = '<div class="hifz-manzil-pool-wrap d-none"><div class="hifz-manzil-pool"><div class="small fw-bold text-warning mb-1">Manzil pool</div><div class="hifz-manzil-para-strip">';
    (b.pool_paras || []).forEach(function (card) {
      var j = card.juz_no;
      var sel = selected.indexOf(j) >= 0 ? ' is-selected' : '';
      var sug = suggested.indexOf(j) >= 0 ? ' is-suggested' : '';
      var nameAr = card.name_ar || paraNameAr(j, '');
      poolHtml += '<button type="button" class="hifz-manzil-para-selectable js-manzil-pick' + sel + sug + '" data-para="' + j + '">' +
        '<span class="hifz-para-inline-num">' + j + '</span> <span class="hifz-para-inline-ar" dir="rtl">' + esc(nameAr) + '</span></button>';
    });
    if (!(b.pool_paras || []).length) poolHtml += '<span class="text-muted small">Pool empty.</span>';
    poolHtml += '</div><p class="small text-muted mb-0 mt-1">Select up to ' + maxPick + ' para(s) for today.</p></div></div>';

    var manzilData = {
      listener_type: b.listener_type || 'teacher',
      listener_student_id: b.listener_student_id || 0,
      quality: b.quality || '',
      hard_mistakes: b.hard_mistakes,
      soft_mistakes: b.soft_mistakes,
      remarks: b.remarks || ''
    };

    return '<div class="hifz-rec-block manzil" data-block="manzil">' +
      '<div class="hifz-manzil-today">' +
      '<div class="small fw-bold" style="color:#856404">Today\'s Manzil <span class="text-muted fw-normal">(auto rotation)</span></div>' +
      '<div class="hifz-manzil-today-chips">' + todayChips + '</div></div>' +
      '<label class="small mb-2 d-block"><input type="checkbox" class="js-manzil-show-pool me-1"> Change selection from Manzil pool</label>' +
      poolHtml +
      renderGradingBlock(s, 'manzil', manzilData, 'manzil', 'Manzil history (Mon–Sat)', b.calendar) +
      '</div>';
  }

  function renderAccHeaderSummary(s) {
    var photo = s.photo_url || defaultAvatar;
    var nameAr = s.current_para_name_ar || paraNameAr(s.current_para, '');
    var linesDone = s.lines_done_in_para || 0;
    var totalLines = s.para_total_lines || 320;
    return '<img class="hifz-acc-photo" src="' + esc(photo) + '" alt="">' +
      '<div class="hifz-acc-main">' +
      '<p class="hifz-acc-name mb-0">' + esc(s.student_name) + '</p>' +
      '<div class="hifz-acc-meta">' + esc(s.reg_no || '') + ' · ' + formatParaInline(s.current_para, nameAr) +
      ' · ' + linesDone + '/' + totalLines + ' lines</div></div>' +
      renderStatusPills(s) +
      '<i class="fas fa-chevron-down hifz-acc-chevron ms-auto"></i>';
  }

  function renderStudentAccordionItem(s) {
    var saved = s.record_sabaq || s.record_sabqi || s.record_manzil || s.record_mutalia;
    var expanded = expandedStudentId === s.student_id;
    var itemCls = 'hifz-student-acc-item' + (saved ? ' saved' : '') + (expanded ? ' is-expanded' : '');
    var blocks = { mutalia: renderMutaliaBlock(s), sabqi: renderSabqiBlock(s), manzil: renderManzilBlock(s) };
    var activeBlock = blocks[activeRecType] || '';

    return '<div class="' + itemCls + '" data-student-id="' + s.student_id + '">' +
      '<div class="hifz-student-acc-header" role="button" tabindex="0">' + renderAccHeaderSummary(s) + '</div>' +
      '<div class="hifz-student-acc-body">' +
      '<div class="hifz-rec-tabs-wrap">' + renderTypeTabs() + '</div>' +
      '<p class="text-muted small px-3 mb-0 hifz-acc-type-hint"></p>' +
      '<div class="card-body p-0">' + activeBlock + '</div></div></div>';
  }

  function renderStudents(list) {
    if (!list.length) {
      $('#recitation-students').html('<p class="text-center text-muted py-4">No students in this section.</p>');
      return;
    }
    var html = '';
    list.forEach(function (s) { html += renderStudentAccordionItem(s); });
    $('#recitation-students').html(html);
    $('#recitation-type-tabs-wrap').addClass('d-none').empty();
    setHint();
    list.forEach(function (s) {
      if (expandedStudentId === s.student_id) {
        studentAccItem(s.student_id).find('.hifz-acc-type-hint').text($('#recitation-type-hint').text());
      }
    });
  }

  function refreshCards() {
    renderStudents(loadedStudents);
  }

  function countStudentsWithEntries() {
    var n = 0;
    loadedStudents.forEach(function (s) {
      if (s.record_sabaq || s.record_sabqi || s.record_manzil || s.record_mutalia) n++;
    });
    return n;
  }

  function updateProgressBadge() {
    $('#recitation-progress').text(countStudentsWithEntries() + ' / ' + loadedStudents.length + ' with entries');
  }

  function updateStudentInList(student) {
    if (!student || !student.student_id) return;
    var idx = -1;
    loadedStudents.forEach(function (s, i) {
      if (s.student_id === student.student_id) idx = i;
    });
    if (idx >= 0) loadedStudents[idx] = student;
    else loadedStudents.push(student);
    if (student.section_peers && student.section_peers.length) {
      sectionPeers = student.section_peers;
    }
  }

  function postData(extra) {
    var d = { hifz_sec_id: currentSecId, recitation_date: currentDate };
    d[csrfName] = csrfHash;
    return $.extend(d, extra || {});
  }

  function loadRecitation(showToast) {
    currentSecId = parseInt($('#recitation-section').val(), 10) || 0;
    currentDate = $('#recitation-date').val() || '';
    if (!currentSecId) {
      $('#recitation-type-tabs-wrap').addClass('d-none').empty();
      $('#recitation-progress').text('—');
      $('#recitation-students').html('<p class="text-center text-muted py-5">Select a Hifz section.</p>');
      return;
    }
    $('#recitation-students').html('<p class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading…</p>');
    $.post(loadUrl, postData({ hifz_sec_id: currentSecId, recitation_date: currentDate }), function (res) {
      if (!res.success) {
        toastr.error(res.msg || 'Load failed');
        $('#recitation-students').html('<p class="text-danger text-center">' + esc(res.msg) + '</p>');
        return;
      }
      loadedStudents = res.students || [];
      sectionPeers = res.section_peers || [];
      if (!expandedStudentId && loadedStudents.length) {
        expandedStudentId = loadedStudents[0].student_id;
      } else if (expandedStudentId && !loadedStudents.some(function (x) { return x.student_id === expandedStudentId; })) {
        expandedStudentId = loadedStudents.length ? loadedStudents[0].student_id : 0;
      }
      refreshCards();
      $('#recitation-progress').text((res.saved_count || 0) + ' / ' + (res.total_count || 0) + ' with entries');
      if (showToast && res.section_name) toastr.success('Loaded: ' + res.section_name);
    }, 'json').fail(function (xhr) {
      var msg = 'Could not load students.';
      if (xhr.responseJSON && xhr.responseJSON.msg) {
        msg = xhr.responseJSON.msg;
      } else if (xhr.status) {
        msg += ' (HTTP ' + xhr.status + ')';
      }
      toastr.error(msg);
      $('#recitation-students').html('<p class="text-danger text-center">' + esc(msg) + '</p>');
    });
  }

  function scheduleLoadRecitation(showToast) {
    clearTimeout(loadDebounceTimer);
    loadDebounceTimer = setTimeout(function () { loadRecitation(showToast); }, 200);
  }

  $('#recitation-date, #recitation-section').on('change', function () {
    if ($(this).is('#recitation-section')) expandedStudentId = 0;
    scheduleLoadRecitation(false);
  });

  $(document).on('click', '.hifz-student-acc-header', function (e) {
    if ($(e.target).closest('a, button, input, select, textarea, .hifz-status-pill').length) return;
    var sid = parseInt($(this).closest('.hifz-student-acc-item').data('student-id'), 10);
    expandedStudentId = expandedStudentId === sid ? 0 : sid;
    refreshCards();
  });

  $(document).on('click', '.js-mutalia-remarks-btn, .js-sabaq-remarks-btn, .js-sabqi-remarks-btn, .js-manzil-remarks-btn', function () {
    var $btn = $(this);
    var prefix = 'mutalia';
    if ($btn.hasClass('js-sabaq-remarks-btn')) prefix = 'sabaq';
    else if ($btn.hasClass('js-sabqi-remarks-btn')) prefix = 'sabqi';
    else if ($btn.hasClass('js-manzil-remarks-btn')) prefix = 'manzil';
    var $wrap = $btn.closest('.hifz-rec-block, .hifz-mutalia-sabaq-section').find('.js-' + prefix + '-remarks-wrap');
    $wrap.toggleClass('d-none');
    $btn.text($wrap.hasClass('d-none') ? 'Add remarks (optional)' : 'Hide remarks');
  });

  $(document).on('click', '.js-quran-progress-toggle', function () {
    var $wrap = $(this).closest('.hifz-quran-progress-wrap');
    var $panel = $wrap.find('.hifz-quran-progress-panel');
    $panel.toggleClass('d-none');
    $(this).html($panel.hasClass('d-none')
      ? '<i class="fas fa-book-quran me-1"></i> Show Quran progress'
      : '<i class="fas fa-book-quran me-1"></i> Hide Quran progress');
  });

  $(document).on('click', '.js-history-toggle', function () {
    var key = $(this).data('panel');
    var $panel = $(this).closest('.hifz-history-toggle-wrap').find('.js-history-panel-' + key);
    $panel.toggleClass('d-none');
    $(this).html($panel.hasClass('d-none')
      ? '<i class="fas fa-calendar-alt me-1"></i> Show history (2 weeks)'
      : '<i class="fas fa-calendar-alt me-1"></i> Hide history');
  });

  function clampMutaliaLinesInput($input) {
    var maxL = parseInt($input.data('max-lines'), 10);
    if (!maxL || maxL < 1) return;
    var v = parseInt($input.val(), 10);
    if (isNaN(v) || v < 1) $input.val(1);
    else if (v > maxL) $input.val(maxL);
  }

  $(document).on('input blur', '.js-mutalia-lines', function () {
    clampMutaliaLinesInput($(this));
  });

  $(document).on('change', '.js-sabaq-listener-type, .js-sabqi-listener-type, .js-manzil-listener-type', function () {
    var $block = $(this).closest('.hifz-rec-block, .hifz-mutalia-sabaq-section');
    var isFellow = $(this).val() === 'fellow';
    if ($(this).hasClass('js-sabaq-listener-type')) {
      $block.find('.js-sabaq-fellow-wrap').toggle(isFellow);
    } else if ($(this).hasClass('js-sabqi-listener-type')) {
      $block.find('.js-sabqi-fellow-wrap').toggle(isFellow);
    } else {
      $block.find('.js-manzil-fellow-wrap').toggle(isFellow);
    }
  });

  $(document).on('click', '.hifz-rec-tabs .nav-link', function (e) {
    e.preventDefault();
    activeRecType = $(this).data('type');
    setHint();
    refreshCards();
  });

  function readQualityFromCard($scope) {
    return $scope.find('input[type="hidden"][class*="quality"]').val()
      || $scope.find('.hifz-quality-chip.is-selected').data('quality') || '';
  }

  $(document).on('change', '.js-manzil-show-pool', function () {
    $(this).closest('.hifz-rec-block').find('.hifz-manzil-pool-wrap').toggleClass('d-none', !$(this).is(':checked'));
  });

  $(document).on('click', '.js-manzil-pick', function () {
    var $card = $(this).closest('.hifz-student-acc-item');
    var sid = parseInt($card.data('student-id'), 10);
    var s = loadedStudents.find(function (x) { return x.student_id === sid; });
    var maxPick = s ? (parseInt(s.manzil_paras_per_day, 10) || 1) : 1;
    var $picked = $card.find('.js-manzil-pick.is-selected');
    if ($(this).hasClass('is-selected')) {
      $(this).removeClass('is-selected');
    } else {
      if ($picked.length >= maxPick) $picked.first().removeClass('is-selected');
      $(this).addClass('is-selected');
    }
  });

  function scopeForSaveType($item, type) {
    if (type === 'sabaq') {
      var $s = $item.find('.hifz-mutalia-sabaq-section');
      return $s.length ? $s : $item;
    }
    if (type === 'mutalia') {
      var $m = $item.find('.hifz-rec-block[data-block="mutalia"]');
      return $m.length ? $m : $item;
    }
    var $b = $item.find('.hifz-rec-block[data-block="' + type + '"]');
    return $b.length ? $b : $item;
  }

  function buildSavePayload($item, type) {
    var sid = parseInt($item.data('student-id'), 10);
    var payload = postData({ student_id: sid });
    var $scope = scopeForSaveType($item, type);

    if (type === 'mutalia') {
      var $lines = $scope.find('.js-mutalia-lines');
      var maxL = parseInt($lines.data('max-lines'), 10) || parseInt($lines.attr('max'), 10) || 0;
      var linesVal = parseInt($lines.val(), 10) || 0;
      if (maxL > 0 && linesVal > maxL) {
        toastr.warning('Maximum ' + maxL + ' lines remaining in this para.');
        return null;
      }
      payload.record_mutalia = 1;
      payload.mutalia_lines = $lines.val();
      payload.mutalia_remarks = readRemarksFromScope($scope, 'mutalia');
    } else if (type === 'sabaq') {
      payload.record_sabaq = 1;
      payload.sabaq_quality = readQualityFromCard($scope);
      payload.sabaq_remarks = readRemarksFromScope($scope, 'sabaq');
      payload.sabaq_hard_mistakes = $scope.find('.js-sabaq-hard').val();
      payload.sabaq_soft_mistakes = $scope.find('.js-sabaq-soft').val();
      payload.sabaq_listener_type = $scope.find('.js-sabaq-listener-type').val();
      payload.sabaq_listener_student_id = $scope.find('.js-sabaq-fellow').val();
      if (!payload.sabaq_quality) { toastr.warning('Select Sabaq quality.'); return null; }
      if (payload.sabaq_listener_type === 'fellow' && !payload.sabaq_listener_student_id) {
        toastr.warning('Select the class fellow who listened.'); return null;
      }
    } else if (type === 'sabqi') {
      payload.record_sabqi = 1;
      payload.sabqi_quality = readQualityFromCard($scope);
      payload.sabqi_remarks = readRemarksFromScope($scope, 'sabqi');
      payload.sabqi_hard_mistakes = $scope.find('.js-sabqi-hard').val();
      payload.sabqi_soft_mistakes = $scope.find('.js-sabqi-soft').val();
      payload.sabqi_listener_type = $scope.find('.js-sabqi-listener-type').val();
      payload.sabqi_listener_student_id = $scope.find('.js-sabqi-fellow').val();
      if (!payload.sabqi_quality) { toastr.warning('Select Sabqi quality.'); return null; }
      if (payload.sabqi_listener_type === 'fellow' && !payload.sabqi_listener_student_id) {
        toastr.warning('Select the class fellow who listened.'); return null;
      }
    } else if (type === 'manzil') {
      payload.record_manzil = 1;
      var juz = [];
      $scope.find('.js-manzil-pick.is-selected').each(function () { juz.push($(this).data('para')); });
      if (!juz.length) {
        $scope.find('.js-manzil-today-chip').each(function () { juz.push($(this).data('para')); });
      }
      payload.manzil_juz_list = juz.join(',');
      payload.manzil_quality = readQualityFromCard($scope);
      payload.manzil_remarks = readRemarksFromScope($scope, 'manzil');
      payload.manzil_listener_type = $scope.find('.js-manzil-listener-type').val();
      payload.manzil_listener_student_id = $scope.find('.js-manzil-fellow').val();
      payload.manzil_hard_mistakes = $scope.find('.js-manzil-hard').val();
      payload.manzil_soft_mistakes = $scope.find('.js-manzil-soft').val();
      if (!payload.manzil_quality) { toastr.warning('Select Manzil quality.'); return null; }
      if (payload.manzil_listener_type === 'fellow' && !payload.manzil_listener_student_id) {
        toastr.warning('Select the class fellow who listened.'); return null;
      }
    }
    return payload;
  }

  function submitStudentDay($item, type, $trigger) {
    var payload = buildSavePayload($item, type);
    if (!payload) return;
    var $chips = $item.find('.hifz-quality-chips');
    if ($trigger && $trigger.hasClass('hifz-quality-chip')) {
      $chips.find('.hifz-quality-chip').addClass('is-saving');
    }
    $.post(saveUrl, payload, function (res) {
      if (res.success) {
        toastr.success(res.msg);
        if (res.student) {
          updateStudentInList(res.student);
          refreshCards();
          updateProgressBadge();
        } else {
          loadRecitation(false);
        }
      } else {
        toastr.error(res.msg || 'Save failed');
      }
    }, 'json').always(function () {
      $chips.find('.hifz-quality-chip').removeClass('is-saving');
    });
  }

  $(document).on('click', '.hifz-quality-chip[data-save-type]', function () {
    var saveType = $(this).data('save-type');
    if (!saveType) return;
    var $scope = $(this).closest('.hifz-rec-block, .hifz-mutalia-sabaq-section');
    var $item = $(this).closest('.hifz-student-acc-item');
    $scope.find('.hifz-quality-chip').removeClass('is-selected');
    $(this).addClass('is-selected');
    $scope.find('input[type="hidden"][class*="quality"]').val($(this).data('quality') || '');
    submitStudentDay($item, saveType, $(this));
  });

  $(document).on('click', '.js-save-student', function () {
    var type = $(this).data('type');
    var $item = $(this).closest('.hifz-student-acc-item');
    var $btn = $(this);
    $btn.prop('disabled', true);
    var payload = buildSavePayload($item, type);
    if (!payload) {
      $btn.prop('disabled', false);
      return;
    }
    $.post(saveUrl, payload, function (res) {
      if (res.success) {
        toastr.success(res.msg);
        if (res.student) {
          updateStudentInList(res.student);
          refreshCards();
          updateProgressBadge();
        } else {
          loadRecitation(false);
        }
      } else {
        toastr.error(res.msg || 'Save failed');
      }
    }, 'json').always(function () { $btn.prop('disabled', false); });
  });
});
</script>

<?= $this->endSection() ?>
