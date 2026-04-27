<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<?php
  // === Back-end inputs you can pass from controller ===
  // $defaultTemplate: string key saved per campus/user (e.g. 'three_copy', 'single_copy', ...)
  // Fallback to 'three_copy' if nothing is saved
  $defaultTemplate = $defaultTemplate ?? 'three_copy';

  // Central list of templates (title + route)
  $templates = [
    'entries'      => ['title' => 'Fee Entries',                        'url' => base_url('admin/print-fee-chalan')],
    'thermal'      => ['title' => 'Thermal Copy',                       'url' => base_url('admin/print-fee-chalan/thermal-copy')],
    'single_copy'  => ['title' => 'Single Copy',                        'url' => base_url('admin/print-fee-chalan/single-copy')],
    'three_copy'   => ['title' => 'Three Copies (PDF)',                 'url' => base_url('admin/print-fee-chalan/pdf')],
    'no_discount'  => ['title' => 'Without Discount',                   'url' => base_url('admin/print-fee-chalan/without-discount')],
    'familywise'   => ['title' => 'Family-wise',                        'url' => base_url('admin/print-fee-chalan/familywise')],
    'family_single'=> ['title' => 'Family-wise (Single Copy)',          'url' => base_url('admin/print-fee-chalan/familywise/single-copy')],
    'hostel'       => ['title' => 'Hostel Fee',                         'url' => base_url('admin/print-fee-chalan/hostel')],
    'with_header'  => ['title' => 'With Header',                        'url' => base_url('admin/print-fee-chalan/with-header')],
  ];

  // Ensure default key exists, else fallback
  if (!array_key_exists($defaultTemplate, $templates)) {
      $defaultTemplate = 'three_copy';
  }
?>

<!-- Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
      <div>
        <h1 class="mb-0">Print Fee Chalan</h1>
        <small class="text-muted">Choose a template to preview/print. Your default will be remembered.</small>
      </div>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Fee Chalan</li>
      </ol>
    </div>
  </div>
</section>

<!-- Main -->
<section class="content">
  <div class="container-fluid">

    <!-- Template Picker -->
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>
          <strong class="mr-2">Template Picker</strong>
          <span class="badge badge-light border">Default: <span id="current-default-template" class="font-weight-600"><?= esc($templates[$defaultTemplate]['title']) ?></span></span>
        </div>
        <div class="d-none d-md-flex align-items-center">
          <div class="custom-control custom-switch mr-2">
            <input type="checkbox" class="custom-control-input" id="rememberChoiceSwitch" checked>
            <label class="custom-control-label" for="rememberChoiceSwitch">Remember my choice</label>
          </div>
          <button id="btnOpenTemplate" class="btn btn-primary">
            <i class="fas fa-external-link-alt mr-1"></i> Open Template
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Mobile compact select -->
        <div class="d-md-none mb-3">
          <label class="mb-1 font-weight-600">Select Template</label>
          <select id="templateSelect" class="form-control">
            <?php foreach ($templates as $key => $tpl): ?>
              <option value="<?= esc($key) ?>" <?= $key === $defaultTemplate ? 'selected' : '' ?>>
                <?= esc($tpl['title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="mt-2 d-flex align-items-center justify-content-between">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="rememberChoiceSwitchMobile" checked>
              <label class="custom-control-label" for="rememberChoiceSwitchMobile">Remember</label>
            </div>
            <button id="btnOpenTemplateMobile" class="btn btn-primary btn-sm">
              <i class="fas fa-external-link-alt mr-1"></i> Open
            </button>
          </div>
        </div>

        <!-- Card Grid (desktop/tablet) -->
        <div class="row d-none d-md-flex">
          <?php foreach ($templates as $key => $tpl): ?>
            <div class="col-lg-3 col-md-4 mb-3">
              <label class="template-card w-100 mb-0">
                <input type="radio" name="template" class="template-radio" value="<?= esc($key) ?>" <?= $key === $defaultTemplate ? 'checked' : '' ?> />
                <div class="template-card-inner">
                  <div class="template-icon"><i class="far fa-file-alt"></i></div>
                  <div class="template-title"><?= esc($tpl['title']) ?></div>
                  <div class="template-desc">Click to select this template.</div>
                </div>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Footer actions (sticky on mobile) -->
      <div class="card-footer bg-light d-flex align-items-center justify-content-between">
        <div class="text-muted small">
          Tip: You can change your default anytime.
        </div>
        <div class="d-none d-md-flex align-items-center">
          <button id="btnSaveDefault" class="btn btn-outline-secondary mr-2">
            <i class="far fa-star mr-1"></i> Set as Default
          </button>
          <button id="btnOpenTemplateFooter" class="btn btn-primary">
            <i class="fas fa-external-link-alt mr-1"></i> Open Template
          </button>
        </div>
      </div>
    </div>

    <!-- Data table -->
    <div class="card card-primary card-outline">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div><i class="fas fa-table mr-2 text-primary"></i><strong>Chalan Records</strong></div>
        <div class="text-muted small">Server-side • Searchable Columns</div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-striped table-bordered mb-0" id="users-datatable" width="100%">
            <thead>
              <tr>
                <th nowrap>Student</th>
                <th nowrap>Fee Type</th>
                <th nowrap>Fee Month</th>
                <th nowrap>Amount</th>
                <th nowrap>Status</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- Styles -->
<style>
  .template-card { cursor: pointer; }
  .template-card input[type="radio"]{ display:none; }
  .template-card-inner{
    border:1px solid #dbe4f0; border-radius:12px; padding:16px;
    background:#fff; transition:all .2s ease; height: 148px;
    display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center;
  }
  .template-card-inner:hover{ box-shadow:0 8px 22px rgba(0,0,0,.06); transform: translateY(-1px); }
  .template-card input:checked + .template-card-inner{
    border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15);
  }
  .template-icon{ font-size:28px; color:#2563eb; margin-bottom:8px; }
  .template-title{ font-weight:700; color:#0f172a; }
  .template-desc{ font-size:12px; color:#64748b; }

  .font-weight-600{ font-weight:600; }
</style>

<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<style>
  .template-card { cursor: pointer; }
  .template-card input[type="radio"]{ display:none; }
  .template-card-inner{
    border:1px solid #dbe4f0; border-radius:12px; padding:16px;
    background:#fff; transition:all .2s ease; height: 148px;
    display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center;
  }
  .template-card-inner:hover{ box-shadow:0 8px 22px rgba(0,0,0,.06); transform: translateY(-1px); }
  .template-card input:checked + .template-card-inner{
    border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15);
  }
  .template-icon{ font-size:28px; color:#2563eb; margin-bottom:8px; }
  .template-title{ font-weight:700; color:#0f172a; }
  .template-desc{ font-size:12px; color:#64748b; }

  .font-weight-600{ font-weight:600; }
</style>

<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script>
(function() {
  // --- Config passed from PHP to JS (DECLARE ONCE)
  const templates  = <?= json_encode($templates, JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
  const defaultKey = "<?= esc($defaultTemplate) ?>";
  const persistUrl = "<?= base_url('admin/print-fee-chalan/set-default-template') ?>"; // POST route to remember default
  const dataUrl    = "<?= base_url('admin/print-fee-chalan/data') ?>";                 // DataTables source
  const csrfName   = "<?= esc(csrf_token()) ?>";
  const csrfHash   = "<?= esc(csrf_hash()) ?>";

  // --- Helpers
  function getSelectedKey() {
    const r = document.querySelector('.template-radio:checked');
    if (r) return r.value;
    const s = document.getElementById('templateSelect');
    return s ? s.value : defaultKey;
  }

  function openTemplate() {
    const key = getSelectedKey();
    const tpl = templates[key];
    if (tpl && tpl.url) window.location.href = tpl.url;
  }

  function saveDefault(key, rememberOnServer = true) {
    // Update label immediately
    const label = document.getElementById('current-default-template');
    if (label) label.textContent = (templates[key] && templates[key].title) ? templates[key].title : key;

    // LocalStorage backup
    try { localStorage.setItem('fee_chalan_default_template', key); } catch(e){}

    if (!rememberOnServer) return Promise.resolve({ ok: true, localOnly: true });

    // ---- build a plain object; avoid computed props inline ----
    const payload = {};
    payload[csrfName] = csrfHash;      // dynamic CSRF key
    payload.template_key = key;

    return fetch(persistUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    })
    .then(r => r.ok ? r.json() : { ok:false })
    .catch(() => ({ ok:false }));
  }

  function loadDefaultFromLocal() {
    try {
      const k = localStorage.getItem('fee_chalan_default_template');
      return (k && templates[k]) ? k : defaultKey;
    } catch(e){ return defaultKey; }
  }

  // --- Events (Desktop)
  document.querySelectorAll('.template-radio').forEach(el => {
    el.addEventListener('change', () => {
      const key = el.value;
      // ❗ Do NOT assign to optional chain; guard normally:
      { const s = document.getElementById('templateSelect'); if (s) s.value = key; } // sync mobile select
    });
  });

  document.getElementById('btnOpenTemplate')?.addEventListener('click', openTemplate);
  document.getElementById('btnOpenTemplateFooter')?.addEventListener('click', openTemplate);

  document.getElementById('btnSaveDefault')?.addEventListener('click', () => {
    const key = getSelectedKey();
    const remember = (document.getElementById('rememberChoiceSwitch')?.checked ?? true);
    saveDefault(key, remember).then(res => {
      if (window.toastr) {
        res?.ok ? toastr.success('Default template saved.') : toastr.error('Could not save default template.');
      }
    });
  });

  // --- Events (Mobile)
  document.getElementById('templateSelect')?.addEventListener('change', (e) => {
    const key = e.target.value;
    const r   = document.querySelector(`.template-radio[value="${key}"]`);
    if (r) r.checked = true; // sync desktop radios
  });

  document.getElementById('btnOpenTemplateMobile')?.addEventListener('click', openTemplate);

  // --- On load: ensure default selected (server > localStorage fallback)
  (function initDefault() {
    const localDefault = loadDefaultFromLocal();
    const key = templates[defaultKey] ? defaultKey : localDefault;

    // Set both UI controls
    const r = document.querySelector(`.template-radio[value="${key}"]`);
    if (r) r.checked = true;
    { const s = document.getElementById('templateSelect'); if (s) s.value = key; } // guard before assigning

    const label = document.getElementById('current-default-template');
    if (label) label.textContent = templates[key]?.title || key;
  })();

  // === DataTable (kept your features) ===
  $(function() {
    // Header column filters
    $('#users-datatable thead tr').clone(true).appendTo('#users-datatable thead');
    $('#users-datatable thead tr:eq(1) th').each(function(i) {
      const title = $(this).text();
      $(this).html('<input type="text" class="form-control form-control-sm" placeholder="'+title+'" />');
      $('input', this).on('keyup change', function() {
        if (table.column(i).search() !== this.value) table.column(i).search(this.value).draw();
      });
    });

    // Table init
    window.table = $('#users-datatable').DataTable({
      deferRender: true,
      searching: true,
      select: { style: 'single', blurable: true },
      processing: true,
      serverSide: true,
      ajax: { url: dataUrl, type: 'POST' },
      orderCellsTop: true,
      fixedHeader: true,
      columns: [
        { data: 'student_name', render: (data, type, row) => (data || '-') + '<br><small class="text-muted">'+(row['reg_no'] || '')+'</small>' },
        { data: 'fee_name'  },
        { data: 'fee_month' },
        { data: 'amount'    },
        { data: 'status'    }
      ],
      fnDrawCallback: function() {
        $(".switchchk").bootstrapSwitch({
          onSwitchChange: function(e, state) {
            const fieldval  = state ? 1 : 0;
            const $el       = $(e.currentTarget);
            const tablename = $el.attr('data-table');
            const fieldname = $el.attr('data-field');
            const rowid     = $el.attr('data-pk');
            $.post("<?= base_url('admin/ajax/set-bool-attribute') ?>", {
              act: 'upsort', tbname: tablename, tbfield: fieldname, tbfieldvalue: fieldval, id: rowid
            }, function(resp) {
              if (window.toastr) (resp === 'success') ? toastr.success('Change saved') : toastr.error('Change failed');
            });
          }
        });
      }
    });
  });
})();
</script>

<?= $this->endSection() ?>
