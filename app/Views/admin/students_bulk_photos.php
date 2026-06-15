<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/bulk_students_header', [
  'title'    => 'Student Photos',
  'subtitle' => 'Bulk profile photos',
]) ?>

<section class="content student-bulk-photos-page">
  <div class="container-fluid px-2 px-sm-3">
    <div class="card card-primary card-outline shadow-sm mb-3">

      <div class="card-header pb-0 px-2 px-sm-3 overflow-hidden">
        <?= view('components/bulk_students_tabs', ['active' => 'photos']) ?>
      </div>

      <div class="photos-filters border-bottom bg-light px-3 py-3">
        <p class="photos-lead text-muted small mb-3 mb-md-2">
          <span class="d-none d-md-inline">Pick a class or search for a student. Upload a file or use <strong>Capture</strong>, then <strong>Save</strong> each row.</span>
          <span class="d-md-none">Class or search → choose photo or <strong>Capture</strong> → <strong>Save</strong></span>
        </p>
        <div class="row g-0">
          <div class="col-12 col-lg-6 form-group mb-3 mb-lg-0 pe-lg-2">
            <label for="cls_sec_id" class="photos-label"><strong>Class</strong></label>
            <select class="form-control photos-touch-control" name="cls_sec_id" id="cls_sec_id" title="Filter by class section">
              <option value="">All Classes</option>
              <?php if (! empty($sectionsclassinfo)) : ?>
                <?php foreach ($sectionsclassinfo as $sectionvalue) : ?>
                  <option value="<?= esc($sectionvalue['cls_sec_id']) ?>">
                    <?= esc($sectionvalue['sectionclassname']) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-12 col-lg-6 form-group mb-0 ps-lg-2">
            <label for="student_search" class="photos-label"><strong>Student search</strong></label>
            <select class="form-control" id="student_search"></select>
            <small class="form-text text-muted mt-1 mb-0 photos-help">Type at least 2 letters, then pick a student to load siblings in this session.</small>
          </div>
        </div>
      </div>

      <div class="p-2 p-sm-3">
        <div class="photos-list-toolbar d-flex flex-wrap align-items-center gap-2 mb-2 px-1" id="photosListToolbar" hidden>
          <span class="photos-student-count badge rounded-pill text-bg-primary px-3 py-2" id="photosStudentCount" aria-live="polite"></span>
          <span class="text-muted small">Choose file or <strong>Capture</strong>, then <strong>Save</strong> each row.</span>
        </div>
        <div class="table-sticky-wrap table-responsive photos-table-scroll">
          <table class="table table-hover mb-0 photos-table" id="studentsTable">
            <thead>
              <tr>
                <th class="sticky-col th-sno">#</th>
                <th class="sticky-col-2 th-name">Student <span class="th-sub text-muted fw-normal d-none d-lg-inline">/ Father</span></th>
                <th class="th-photo-upload">Update photo</th>
                <th class="action-cell th-save d-none d-md-table-cell">Save</th>
              </tr>
            </thead>
            <tbody id="studentsTbody">
              <tr class="photos-empty-row">
                <td colspan="4" class="text-center text-muted py-4 px-3">Select a class or search for a student…</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered photos-camera-dialog" role="document">
          <div class="modal-content photos-camera-content">
            <div class="modal-header py-3 px-3 border-bottom-0">
              <h5 class="modal-title mb-0" id="photoModalLabel">Take photo</h5>
              <button type="button" class="close photos-modal-close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body px-3 pt-0 pb-3">
              <div class="photos-video-shell mb-3">
                <video id="cameraVideo" class="photos-camera-video" playsinline autoplay muted></video>
              </div>
              <div class="photos-camera-actions">
                <button type="button" class="btn btn-secondary w-100 btn-lg photos-btn-flip" id="btnFlipCam">Flip camera</button>
                <button type="button" class="btn btn-primary w-100 btn-lg photos-btn-capture" id="btnTakeSnap">Use this frame</button>
              </div>
              <p class="text-muted small text-center mb-0 mt-3 px-1">Saves the live preview to this student. Then tap <strong>Save</strong> on the list.</p>
            </div>
            <div class="modal-footer border-top-0 pt-0 px-3 pb-3">
              <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <div id="loader-1" class="photos-loader" aria-hidden="true">
        <div class="photos-loader-inner">
          <div class="spinner-border text-primary mb-2" role="status" style="width:3rem;height:3rem;"><span class="visually-hidden">Loading…</span></div>
          <div class="text-dark fw-bold">Loading…</div>
        </div>
      </div>

    </div>
  </div>
</section>

<style>
  /* ---- Page shell (notched phones, small gutters) ---- */
  .student-bulk-photos-page {
    padding-bottom: env(safe-area-inset-bottom, 0);
  }
  .student-bulk-photos-page .photos-filters .photos-label {
    font-size: 0.9rem;
    margin-bottom: 0.35rem;
  }
  .student-bulk-photos-page .photos-touch-control,
  .student-bulk-photos-page #student_search {
    min-height: 48px;
    border-radius: 8px;
    font-size: 16px; /* avoids iOS zoom on focus */
  }
  .student-bulk-photos-page .photos-help { font-size: 0.8125rem; line-height: 1.35; }
  .student-bulk-photos-page .photos-list-toolbar .photos-student-count {
    font-size: 0.875rem;
    font-weight: 600;
  }
  .student-bulk-photos-page .photos-table .th-sub { font-size: 0.75rem; }

  /* Student name + father on separate lines */
  .student-bulk-photos-page .student-identity {
    display: block;
    min-width: 0;
  }
  .student-bulk-photos-page .student-display-name {
    display: block;
    font-weight: 600;
    font-size: 0.95rem;
    line-height: 1.3;
    color: #212529;
    word-break: break-word;
    margin: 0 0 0.25rem;
  }
  .student-bulk-photos-page .student-father-name {
    display: block;
    margin: 0;
    line-height: 1.35;
  }
  .student-bulk-photos-page .student-father-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #868e96;
    margin-bottom: 0.1rem;
  }
  .student-bulk-photos-page .student-father-value {
    display: block;
    font-size: 0.85rem;
    color: #495057;
    word-break: break-word;
  }
  .student-bulk-photos-page .student-father-name--empty .student-father-value {
    font-style: italic;
    color: #adb5bd;
  }

  /* Tabs: horizontal scroll on narrow screens */
  .student-bulk-photos-page .bulk-tabs-wrap {
    margin-left: -0.25rem;
    margin-right: -0.25rem;
  }

  /* ---- Table desktop ---- */
  .student-bulk-photos-page .photos-table-scroll {
    max-height: 70vh;
    overflow: auto !important;
    -webkit-overflow-scrolling: touch;
    border-radius: 10px;
    border: 1px solid #e8ecf1;
    background: #fff;
  }
  .student-bulk-photos-page .photos-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    --sno-w: 48px;
    min-width: 640px;
    margin-bottom: 0;
  }
  .student-bulk-photos-page .photos-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #495057;
    background: #f4f6f9;
    border-bottom: 1px solid #e8ecf1;
    padding: 0.65rem 0.75rem;
    white-space: nowrap;
  }
  .student-bulk-photos-page .photos-table tbody td {
    vertical-align: middle;
    padding: 0.55rem 0.75rem;
    border-top: 1px solid #eef1f5;
    border-bottom: none;
    border-start: none;
    border-end: none;
  }
  .student-bulk-photos-page .photos-table .th-photo-upload { width: auto; }
  .student-bulk-photos-page .photos-table .th-save { width: 88px; }
  .student-bulk-photos-page .photos-table th.sticky-col,
  .student-bulk-photos-page .photos-table td.sticky-col {
    position: sticky;
    left: 0;
    z-index: 6;
    background: #fff;
    width: var(--sno-w);
    min-width: var(--sno-w);
    max-width: var(--sno-w);
    text-align: center;
  }
  @media (min-width: 768px) {
    .student-bulk-photos-page .photos-table th.sticky-col-2,
    .student-bulk-photos-page .photos-table td.sticky-col-2 {
      position: sticky;
      left: var(--sno-w);
      z-index: 5;
      background: #fff;
    }
    .student-bulk-photos-page .photos-table .th-name,
    .student-bulk-photos-page .photos-table .student-name-cell {
      border-end: 1px solid #e9ecef;
      min-width: 160px;
      max-width: 260px;
    }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row:nth-child(even) td {
      background-color: #fafbfc;
    }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row:hover td {
      background-color: #f0f7ff;
    }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row.is-saved td {
      animation: photosRowSaved 1.2s ease;
    }
  }
  @keyframes photosRowSaved {
    0% { box-shadow: inset 0 0 0 2px #28a745; }
    100% { box-shadow: inset 0 0 0 0 transparent; }
  }
  .student-bulk-photos-page .photos-table thead th.sticky-col,
  .student-bulk-photos-page .photos-table thead th.sticky-col-2 {
    z-index: 12;
    background: #f4f6f9;
  }
  @media (min-width: 768px) {
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row:nth-child(even) td.sticky-col,
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row:nth-child(even) td.sticky-col-2 {
      background-color: #fafbfc;
    }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row:hover td.sticky-col,
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row:hover td.sticky-col-2 {
      background-color: #f0f7ff;
    }
    .student-bulk-photos-page .photo-thumb,
    .student-bulk-photos-page .photo-thumb--empty {
      width: 64px;
      height: 64px;
    }
    .student-bulk-photos-page .student-father-label {
      display: inline;
      font-size: 0.75rem;
      text-transform: none;
      letter-spacing: 0;
      margin-right: 0.25rem;
    }
    .student-bulk-photos-page .student-father-label::after { content: ":"; }
    .student-bulk-photos-page .student-father-value {
      display: inline;
      font-size: 0.8rem;
    }
    .student-bulk-photos-page .student-father-name {
      display: block;
      margin-top: 0.15rem;
    }
  }

  /* Thumbnail */
  .student-bulk-photos-page .photo-thumb {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    display: block;
  }
  .student-bulk-photos-page .photo-thumb--empty {
    width: 56px;
    height: 56px;
    border-radius: 8px;
    font-size: 10px;
    text-align: center;
    padding: 4px;
    line-height: 1.2;
    margin: 0 auto;
  }
  /* Combined photo upload panel (current + file + capture) */
  .student-bulk-photos-page .photo-upload-panel {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
  }
  .student-bulk-photos-page .photo-upload-panel__current {
    flex: 0 0 auto;
  }
  .student-bulk-photos-page .photo-upload-panel__actions {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
  }
  .student-bulk-photos-page .photo-upload-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
  }
  .student-bulk-photos-page .fileInputPhoto {
    font-size: 0.8125rem;
    border-radius: 6px;
    padding: 0.35rem 0.5rem;
    height: auto;
    min-height: 34px;
  }
  .student-bulk-photos-page .td-photo-upload { padding-top: 0.5rem; padding-bottom: 0.5rem; }
  .student-bulk-photos-page .td-save { text-align: center; }
  .student-bulk-photos-page .savePhotoBtn--desktop { min-width: 72px; }

  /* Empty / colspan rows */
  .student-bulk-photos-page tr.photos-empty-row td,
  .student-bulk-photos-page #studentsTable tbody td[colspan] {
    text-align: center;
    padding: 1.25rem 0.75rem !important;
  }
  .student-bulk-photos-page tr.photos-empty-row td::before,
  .student-bulk-photos-page #studentsTable tbody td[colspan]::before {
    display: none !important;
    content: none !important;
  }

  /* ---- Mobile: compact cards ---- */
  @media (max-width: 767.98px) {
    .student-bulk-photos-page .container-fluid { padding-left: 0.5rem; padding-right: 0.5rem; }
    .student-bulk-photos-page .photos-table-scroll {
      max-height: none;
      overflow: visible !important;
      border: 0;
      background: transparent;
    }
    .student-bulk-photos-page .photos-table {
      min-width: 100% !important;
      table-layout: auto;
    }
    .student-bulk-photos-page .photos-table thead { display: none !important; }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row {
      display: block;
      border: 1px solid #e3e8ef;
      border-radius: 12px;
      margin-bottom: 0.65rem;
      background: #fff;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
      overflow: hidden;
    }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row.has-pending-file {
      border-color: #4dabf7;
      box-shadow: 0 0 0 2px rgba(77,171,247,0.2);
    }
    /* Only name + photo cells — hide # and desktop Save column */
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row td.sno-cell,
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row td.td-save,
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row td.action-cell {
      display: none !important;
    }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row td {
      display: block;
      width: 100% !important;
      max-width: 100% !important;
      border: 0;
      position: static !important;
      left: auto !important;
      padding: 0;
    }
    .student-bulk-photos-page .photos-table tbody tr.student-photo-row td::before {
      display: none !important;
      content: none !important;
    }
    /* Card header: student on line 1, father on line 2 */
    .student-bulk-photos-page .student-name-cell--header {
      padding: 0.65rem 0.85rem 0.55rem !important;
      background: #f4f7fb;
      border-bottom: 1px solid #e8ecf1 !important;
    }
    .student-bulk-photos-page .student-display-name {
      font-size: 1rem;
      line-height: 1.25;
      margin-bottom: 0.35rem;
    }
    .student-bulk-photos-page .student-father-name {
      display: block;
      margin-top: 0;
    }
    .student-bulk-photos-page .student-father-label {
      display: block;
      font-size: 0.65rem;
      margin-bottom: 0.12rem;
    }
    .student-bulk-photos-page .student-father-label::after { content: none; }
    .student-bulk-photos-page .student-father-value {
      display: block;
      font-size: 0.875rem;
      font-weight: 500;
      color: #495057;
      line-height: 1.3;
    }
    /* Photo row: thumb left, upload right */
    .student-bulk-photos-page .td-photo-upload {
      padding: 0.6rem 0.85rem 0.7rem !important;
      border-bottom: 0 !important;
    }
    .student-bulk-photos-page .photo-upload-panel {
      flex-direction: row;
      align-items: flex-start;
      gap: 0.65rem;
    }
    .student-bulk-photos-page .photo-upload-panel__current::before,
    .student-bulk-photos-page .photo-upload-panel__actions::before {
      display: none !important;
      content: none !important;
    }
    .student-bulk-photos-page .photo-upload-panel__current {
      flex: 0 0 72px;
    }
    .student-bulk-photos-page .photo-upload-panel__actions {
      flex: 1 1 auto;
      min-width: 0;
      gap: 0.35rem;
    }
    .student-bulk-photos-page .photo-thumb,
    .student-bulk-photos-page .photo-thumb--empty {
      width: 72px;
      height: 72px;
      font-size: 10px;
      margin: 0;
    }
    .student-bulk-photos-page .fileInputPhoto {
      min-height: 38px;
      font-size: 14px;
      padding: 0.3rem 0.45rem;
    }
    .student-bulk-photos-page .photo-upload-btns {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.35rem;
    }
    .student-bulk-photos-page .photo-upload-btns .btn {
      min-height: 40px;
      font-size: 0.8125rem;
      padding: 0.35rem 0.5rem;
      margin: 0;
    }
    .student-bulk-photos-page .savePhotoBtn--inline {
      grid-column: 1 / -1;
      min-height: 42px;
      font-weight: 600;
    }
  }

  /* ---- Camera modal ---- */
  .student-bulk-photos-page .photos-camera-dialog {
    margin: 0.5rem;
    max-width: 520px;
    width: calc(100% - 1rem);
  }
  @media (max-width: 575.98px) {
    .student-bulk-photos-page .photos-camera-dialog {
      margin: 0;
      max-width: none;
      width: 100%;
      min-height: 100%;
      display: flex;
      align-items: flex-end;
      padding: 0;
    }
    .student-bulk-photos-page .photos-camera-content {
      border-radius: 16px 16px 0 0;
      max-height: 96vh;
      overflow-y: auto;
      width: 100%;
    }
  }
  .student-bulk-photos-page .photos-video-shell {
    background: #111;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    max-height: min(52vh, 420px);
  }
  .student-bulk-photos-page .photos-camera-video {
    width: 100%;
    height: auto;
    max-height: min(52vh, 420px);
    display: block;
    background: #000;
  }
  .student-bulk-photos-page .photos-camera-actions {
    display: flex;
    flex-direction: column;
  }
  .student-bulk-photos-page .photos-camera-actions .photos-btn-flip { margin-bottom: 0.5rem; }
  @media (min-width: 576px) {
    .student-bulk-photos-page .photos-camera-actions {
      flex-direction: row;
      justify-content: center;
      flex-wrap: wrap;
    }
    .student-bulk-photos-page .photos-camera-actions .photos-btn-flip {
      margin-bottom: 0;
      margin-right: 0.5rem;
    }
    .student-bulk-photos-page .photos-camera-actions .btn {
      width: auto;
      min-width: 160px;
    }
  }
  .student-bulk-photos-page .photos-modal-close {
    font-size: 1.75rem;
    padding: 0.5rem;
    line-height: 1;
    min-width: 44px;
    min-height: 44px;
  }

  /* ---- Loader overlay ---- */
  .student-bulk-photos-page .photos-loader {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    inset: 0;
    z-index: 10050;
    background: rgba(255,255,255,0.88);
    padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
    display: none;
    align-items: center;
    justify-content: center;
  }
  .student-bulk-photos-page .photos-loader.is-visible {
    display: flex !important;
  }
  .student-bulk-photos-page .photos-loader-inner {
    text-align: center;
    padding: 1rem;
  }

  /* Select2 mobile width */
  .select2-photos-dropdown { z-index: 10060 !important; }
  .student-bulk-photos-page .select2-container { width: 100% !important; max-width: 100%; }
</style>

<script>
(function () {
  'use strict';

  const URL_DATA        = "<?= base_url('admin/students_bulk_photos/data') ?>";
  const URL_SAVE        = "<?= base_url('admin/students_bulk_photos/save_photo') ?>";
  const URL_SEARCH_NAME = "<?= base_url('admin/students_bulk_photos/search-by-name') ?>";

  const CSRF_NAME = "<?= csrf_token() ?>";
  let   CSRF_HASH = "<?= csrf_hash() ?>";

  let currentParentId = null;

  function photosLoader(show) {
    var $l = $("#loader-1");
    if (show) {
      $l.addClass("is-visible").attr("aria-hidden", "false");
    } else {
      $l.removeClass("is-visible").attr("aria-hidden", "true");
    }
  }

  function getCampusId() {
    const $camp = $('#campus_id');
    return ($camp.length && $camp.val()) ? $camp.val() : "<?= (int) session('campus_id') ?>";
  }

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

  function updateStudentCount() {
    const n = $('#studentsTable tbody tr.student-photo-row').length;
    const $toolbar = $('#photosListToolbar');
    const $badge = $('#photosStudentCount');
    if (n > 0) {
      $toolbar.prop('hidden', false);
      $badge.text(n === 1 ? '1 student' : n + ' students');
    } else {
      $toolbar.prop('hidden', true);
      $badge.text('');
    }
  }

  function refreshCsrfFromXhr(xhr) {
    const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
    if (newToken) CSRF_HASH = newToken;
  }

  /** Expect HTML rows; handle JSON (e.g. permission). Returns false if response was an error JSON. */
  function applyStudentsDataResponse(resText, xhr) {
    refreshCsrfFromXhr(xhr);
    const raw = (resText == null) ? '' : String(resText);
    const trimmed = raw.trim();
    if (trimmed.charAt(0) === '{') {
      try {
        const j = JSON.parse(trimmed);
        if (j && j.success === false && j.msg) {
        $('#studentsTbody').html(
          '<tr class="photos-empty-row"><td colspan="4" class="text-center text-danger">' + $('<div/>').text(j.msg).html() + '</td></tr>'
          );
          window.toastr && toastr.error(j.msg);
          return false;
        }
      } catch (e) { /* fall through — treat as HTML */ }
    }
    $('#studentsTbody').html(raw || '<tr class="photos-empty-row"><td colspan="4" class="text-center text-info">No students found.</td></tr>');
    renumberRows();
    updateStudentCount();
    return true;
  }

  function loadByClass() {
    if (currentParentId) return loadByParent(currentParentId);
    const cls_sec_id = $('#cls_sec_id').val();
    if (!cls_sec_id) {
      $('#studentsTbody').html('<tr class="photos-empty-row"><td colspan="4" class="text-center text-muted">Select a class to view students…</td></tr>');
      updateStudentCount();
      return;
    }
    photosLoader(true);
    $.ajax({
      url: URL_DATA,
      type: "POST",
      dataType: 'text',
      data: { cls_sec_id: cls_sec_id, campus_id: getCampusId(), [CSRF_NAME]: CSRF_HASH },
      success: function (resText, _s, xhr) {
        applyStudentsDataResponse(resText, xhr);
        photosLoader(false);
      },
      error: function (xhr) {
        photosLoader(false);
        const st = xhr.status || 0;
        const hint = (xhr.responseText || '').replace(/<[^>]+>/g, ' ').trim().slice(0, 180);
        const extra = hint ? (' — ' + $('<div/>').text(hint).html()) : '';
        $('#studentsTbody').html(
          '<tr class="photos-empty-row"><td colspan="4" class="text-center text-danger">Failed to load students (HTTP ' + st + ').' + extra + '</td></tr>'
        );
        window.toastr && toastr.error('Could not load list (HTTP ' + st + '). Confirm POST route exists: ' + URL_DATA);
      }
    });
  }

  function loadByParent(parentId) {
    photosLoader(true);
    $.ajax({
      url: URL_DATA,
      type: "POST",
      dataType: 'text',
      data: { parent_id: parentId, campus_id: getCampusId(), [CSRF_NAME]: CSRF_HASH },
      success: function (resText, _s, xhr) {
        const ok = applyStudentsDataResponse(resText, xhr);
        photosLoader(false);
        if (ok) window.toastr && toastr.info('Loaded students for selected family.');
      },
      error: function (xhr) {
        photosLoader(false);
        const st = xhr.status || 0;
        const hint = (xhr.responseText || '').replace(/<[^>]+>/g, ' ').trim().slice(0, 180);
        const extra = hint ? (' — ' + $('<div/>').text(hint).html()) : '';
        $('#studentsTbody').html(
          '<tr><td colspan="4" class="text-center text-danger">Failed to load (HTTP ' + st + ').' + extra + '</td></tr>'
        );
        window.toastr && toastr.error('Could not load list (HTTP ' + st + ').');
      }
    });
  }

  $(function () {
    let activeRow = null;
    let stream = null;
    let useFacing = "user";
    const $modal = $('#photoModal');
    const $video = $('#cameraVideo');

    async function startCamera() {
      stopCamera();
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        window.toastr && toastr.info('Camera not supported on this device. Please choose a file instead.');
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
        window.toastr && toastr.error('Camera permission denied or unavailable. Please choose a file instead.');
      }
    }
    function stopCamera() {
      if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
      if ($video.length) $video[0].srcObject = null;
    }
    function openPhotoModalForRow($row) {
      activeRow = $row;
      stopCamera();
      $modal.modal('show');
    }
    /** Copy current video frame into the row file input and preview; close modal. */
    function applyVideoFrameToRow() {
      if (!activeRow) return;
      if (!$video.length || !$video[0].srcObject) {
        return window.toastr && toastr.error('Camera not started.');
      }
      const v = $video[0];
      const c = document.createElement('canvas');
      c.width = v.videoWidth || 1280;
      c.height = v.videoHeight || 720;
      if (!c.width || !c.height) {
        return window.toastr && toastr.warning('Wait for the camera preview, then try again.');
      }
      c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
      c.toBlob(function (blob) {
        if (!blob) return window.toastr && toastr.error('Could not capture image.');
        const file = new File([blob], 'capture.jpg', { type: 'image/jpeg', lastModified: Date.now() });
        const dt = new DataTransfer();
        dt.items.add(file);
        const $input = activeRow.find('.fileInputPhoto[name="profile_photo"]');
        if ($input.length) $input[0].files = dt.files;
        const url = URL.createObjectURL(file);
        const $prev = activeRow.find('.photoPreviewExisting');
        if ($prev.length && $prev.is('img')) {
          $prev.attr('src', url);
        } else {
          activeRow.find('.photo-upload-panel__current').html(
            '<img class="photoPreviewExisting photo-thumb" src="' + url + '" alt="">'
          );
        }
        activeRow.addClass('has-pending-file');
        $modal.modal('hide');
        stopCamera();
        window.toastr && toastr.success('Photo captured. Press Save on this row to upload.');
      }, 'image/jpeg', 0.92);
    }

    $('#studentsTable').on('change', '.fileInputPhoto', function () {
      const $row = $(this).closest('tr');
      if (this.files && this.files.length) {
        $row.addClass('has-pending-file');
      } else {
        $row.removeClass('has-pending-file');
      }
    });

    $('#studentsTable').on('click', '.btnCapturePhoto', function () {
      openPhotoModalForRow($(this).closest('tr'));
    });
    $modal.on('shown.bs.modal', function () {
      startCamera();
    });
    $('#btnTakeSnap').on('click', applyVideoFrameToRow);
    $('#btnFlipCam').on('click', function () {
      useFacing = (useFacing === 'user') ? 'environment' : 'user';
      stopCamera();
      startCamera();
    });
    $modal.on('hidden.bs.modal', function () {
      stopCamera();
      activeRow = null;
    });

    $('#studentsTable').on('click', '.savePhotoBtn', function () {
      const $row = $(this).closest('tr');
      const input = $row.find('[name="profile_photo"]')[0];
      if (!input || !input.files || !input.files.length) {
        return window.toastr && toastr.warning('Select or capture a photo before saving.');
      }
      const fd = new FormData();
      fd.append('student_id', $row.find('[name="student_id"]').val());
      fd.append('profile_photo', input.files[0]);
      fd.append(CSRF_NAME, CSRF_HASH);

      const $btn = $(this);
      $btn.prop('disabled', true);
      photosLoader(true);

      $.ajax({
        url: URL_SAVE,
        type: "POST",
        data: fd,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function (res, _st, xhr) {
          const newToken = xhr.getResponseHeader && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
          if (newToken) CSRF_HASH = newToken;
          photosLoader(false);
          $btn.prop('disabled', false);
          if (res && res.success) {
            window.toastr && toastr.success(res.msg || 'Saved.');
            if (res.profile_photo_url) {
              const $cell = $row.find('.photo-upload-panel__current');
              const $img = $row.find('.photoPreviewExisting');
              if ($img.length) {
                $img.attr('src', res.profile_photo_url);
              } else {
                $cell.html('<img class="photoPreviewExisting photo-thumb" src="' + res.profile_photo_url + '" alt="">');
              }
            }
            $(input).val('');
            $row.removeClass('has-pending-file').addClass('is-saved');
            setTimeout(function () { $row.removeClass('is-saved'); }, 1400);
          } else {
            let m = (res && res.msg) || 'Save failed.';
            if (res && res.errors) m += ' ' + JSON.stringify(res.errors);
            if (res && res.detail) m += ' — ' + res.detail;
            window.toastr && toastr.error(m, '', { timeOut: 20000, extendedTimeOut: 5000 });
          }
        },
        error: function (xhr) {
          photosLoader(false);
          $btn.prop('disabled', false);
          let m = 'Could not reach server or invalid response.';
          try {
            const j = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
            if (j && j.msg) {
              m = j.msg;
              if (j.detail) m += ' — ' + j.detail;
            } else if (xhr.status) {
              m = 'Request failed (HTTP ' + xhr.status + ').';
            }
          } catch (e) { /* keep default */ }
          window.toastr && toastr.error(m, '', { timeOut: 20000 });
        }
      });
    });

    $('#student_search').select2({
      placeholder: 'Search student by name',
      minimumInputLength: 2,
      allowClear: true,
      width: '100%',
      dropdownParent: $('body'),
      dropdownCssClass: 'select2-photos-dropdown',
      ajax: {
        url: URL_SEARCH_NAME,
        dataType: 'json',
        delay: 250,
        cache: true,
        data: function (params) {
          return { q: params.term, limit: 20, cls_sec_id: $('#cls_sec_id').val() || '', campus_id: getCampusId() };
        },
        processResults: function (data) {
          return { results: data && data.results ? data.results : [] };
        }
      }
    });

    $('#student_search').on('select2:select', function (e) {
      const data = e.params.data || {};
      if (!data.parent_id) return window.toastr && toastr.warning('Selected student has no parent_id.');
      currentParentId = data.parent_id;
      loadByParent(currentParentId);
    });
    $('#student_search').on('select2:clear select2:unselect', function () {
      currentParentId = null;
      if ($('#cls_sec_id').val()) loadByClass();
      else {
        $('#studentsTbody').html('<tr class="photos-empty-row"><td colspan="4" class="text-center text-muted">Select a class or search for a student…</td></tr>');
        updateStudentCount();
      }
    });

    $(document).on('change', '#cls_sec_id, #campus_id', function () {
      currentParentId = null;
      $('#student_search').val(null).trigger('change');
      if ($('#cls_sec_id').is(this)) loadByClass();
    });

    if ($('#cls_sec_id').val()) loadByClass();
  });
})();
</script>

<?= $this->endSection() ?>
