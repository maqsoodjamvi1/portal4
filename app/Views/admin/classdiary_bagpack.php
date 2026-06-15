<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Bag Pack Planner',
    'icon' => 'fas fa-suitcase',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Class Diary', 'url' => base_url('admin/classdiary-view')],
        ['label' => 'Bag Pack', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card sms-card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">Select Week &amp; Class</h3>
        </div>
        <div class="card-body">

          <div class="row">
            <!-- Term Session -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Term Session</label>
                <select name="term_id" id="term_id" class="form-control">
                  <?php foreach ($terms_session_info as $ts): ?>
                    <option value="<?= (int)$ts->term_session_id ?>"
                      <?= $ts->term_session_id == ($default_term_session_id ?? 0) ? 'selected' : '' ?>>
                      <?= esc($ts->term_name) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Term Weeks -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Term Weeks</label>
                <select name="term_weeks" id="term_weeks" class="form-control">
                  <?php foreach ($term_weeks_info as $w): ?>
                    <option value="<?= (int)$w->term_weeks_id ?>"
                      <?= $w->term_weeks_id == ($default_term_weeks_id ?? 0) ? 'selected' : '' ?>>
                      <?= esc($w->week_name) ?>
                      (<?= esc(date('d M', strtotime($w->start_date))) ?> - <?= esc(date('d M', strtotime($w->end_date))) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Class Section -->
            <div class="col-md-3">
              <div class="form-group">
                <label>Class Section</label>
                <select class="form-control select2" name="section_id" id="section_id">
                  <option value="">Select Section</option>
                  <?php if (!empty($sectionsclassinfo)): ?>
                    <?php foreach ($sectionsclassinfo as $sec): ?>
                      <option value="<?= (int)$sec['cls_sec_id'] ?>">
                        <?= esc($sec['sectionclassname']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <!-- WHAT TO SHOW FILTERS -->
            <div class="col-md-3">
              <label>Show</label>
              <div class="border rounded p-2">
                <div class="form-check form-check">
                  <input type="checkbox" class="form-check-input" id="show_homework" checked>
                  <label class="form-check-label" for="show_homework">
                    Weekly Diary – Homework
                  </label>
                </div>
                <div class="form-check form-check mt-1">
                  <input type="checkbox" class="form-check-input" id="show_classwork" checked>
                  <label class="form-check-label" for="show_classwork">
                    Weekly Diary – Class work
                  </label>
                </div>
                <div class="form-check form-check mt-1">
                  <input type="checkbox" class="form-check-input" id="show_bagpack" checked>
                  <label class="form-check-label" for="show_bagpack">
                    Bag Pack
                  </label>
                </div>
              </div>
            </div>
          </div><!-- /.row -->

          <div class="row mt-2">
            <div class="col-md-3 ms-auto">
              <button type="button" id="btnShowBagPack" class="btn btn-primary w-100">
                <i class="fas fa-eye me-1"></i> Show Weekly View
              </button>
            </div>
          </div>

          <hr>

          <div id="bagpackResult">
            <p class="text-muted mb-0">
              Select a week and class section, choose what you want to show
              (<b>Homework</b>, <b>Class work</b>, <b>Bag Pack</b>), then click
              <b>Show Weekly View</b>.
            </p>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<script>
const CD_CSRF_NAME = "<?= csrf_token() ?>";
let   CD_CSRF_HASH = "<?= csrf_hash() ?>";

// Reload Term Weeks when Term Session changes
function refreshTermWeeksOptions(termSessionId) {
  if (!termSessionId) {
    $('#term_weeks').html('<option value="">Select Term Week</option>');
    return;
  }

  const payload = {};
  payload[CD_CSRF_NAME] = CD_CSRF_HASH;
  payload['term_session_id'] = termSessionId;

  $.post("<?= base_url('admin/classdiary/term-weeks-options') ?>", payload, function(html, status, xhr) {
    const newToken = xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token');
    if (newToken) {
      CD_CSRF_HASH = newToken;
    }
    $('#term_weeks').html(html);
  }).fail(function(xhr){
    console.error('[bagpack] term-weeks-options error', xhr.status, xhr.responseText);
    $('#term_weeks').html('<option value="">Failed to load weeks</option>');
  });
}

$(function(){
  $('#term_id').on('change', function(){
    const termSessionId = $(this).val();
    refreshTermWeeksOptions(termSessionId);
  });
});

(function(){
  'use strict';

  const URL_GET_BAGPACK = "<?= base_url('admin/classdiary/get-bagpack') ?>";
  const CSRF_NAME = "<?= csrf_token() ?>";
  let   CSRF_HASH = "<?= csrf_hash() ?>";

  function addCsrf(data){
    if (!data) data = {};
    if (CSRF_NAME && CSRF_HASH) data[CSRF_NAME] = CSRF_HASH;
    return data;
  }

  function refreshCsrfFromXHR(xhr){
    const t = xhr && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
    if (t) CSRF_HASH = t;
  }

  function showBagPack(){
    const termWeeksId = $('#term_weeks').val();
    const sectionId   = $('#section_id').val();

    const showHomework = $('#show_homework').is(':checked') ? 1 : 0;
    const showClasswork = $('#show_classwork').is(':checked') ? 1 : 0;
    const showBagpack = $('#show_bagpack').is(':checked') ? 1 : 0;

    if (!termWeeksId){
      alert('Please select a Term Week.');
      return;
    }
    if (!sectionId){
      alert('Please select a Class Section.');
      return;
    }
    if (!showHomework && !showClasswork && !showBagpack) {
      alert('Please select at least one option (Homework, Class work, or Bag Pack).');
      return;
    }

    $('#bagpackResult').html('<div class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Loading…</div>');

    $.ajax({
      url: URL_GET_BAGPACK,
      type: 'POST',
      data: addCsrf({
        term_weeks: termWeeksId,
        section_id: sectionId,
        show_homework: showHomework,
        show_classwork: showClasswork,
        show_bagpack: showBagpack
      }),
      success: function(html, status, xhr){
        refreshCsrfFromXHR(xhr);
        $('#bagpackResult').html(html || '<div class="alert alert-info">No data.</div>');
      },
      error: function(xhr){
        $('#bagpackResult').html('<div class="alert alert-danger">Failed to load weekly view (HTTP '+xhr.status+').</div>');
      }
    });
  }

  $(function(){
    $('#btnShowBagPack').on('click', function(e){
      e.preventDefault();
      showBagPack();
    });
  });

})();
</script>

<?= $this->endSection() ?>
