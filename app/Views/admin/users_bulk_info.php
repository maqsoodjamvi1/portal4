<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$assignableRoles = $assignableRoles ?? [];
$today = date('Y-m-d');
$csrfName = csrf_token();
$csrfHash = csrf_hash();
?>

<?= view('components/page_header', [
    'title' => 'Bulk employee info',
    'icon' => 'fas fa-users-cog',
    'subtitle' => 'Each employee opens as a full-width card with sections. Choose which fields to include in Save, then load the list. Use the full form for login, roles, subjects, and class-teacher setup.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employees', 'url' => base_url('admin/users')],
        ['label' => 'Bulk', 'active' => true],
    ],
]) ?>

<div class="container-fluid">
  <div class="card card-primary card-outline shadow-sm">
    <div class="card-header p-0 border-bottom-0">
      <ul class="nav nav-tabs" id="bulkEmpTabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="tab-edit-link" data-bs-toggle="tab" href="#tab-edit" role="tab">Edit existing</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="tab-add-link" data-bs-toggle="tab" href="#tab-add" role="tab">Quick add (batch)</a>
        </li>
      </ul>
    </div>

    <div class="tab-content">
      <!-- ——— Edit ——— -->
      <div class="tab-pane fade show active p-3" id="tab-edit" role="tabpanel">
        <div class="row">
          <div class="col-lg-3 col-md-6 mb-3">
            <label for="filter_status"><strong>Status</strong></label>
            <select id="filter_status" class="form-control">
              <option value="1" selected>Active</option>
              <option value="0">Dropped</option>
              <option value="all">All</option>
            </select>
          </div>
          <div class="col-lg-3 col-md-6 mb-3">
            <label for="filter_q"><strong>Search</strong></label>
            <input type="text" id="filter_q" class="form-control" placeholder="Name, username, email, CNIC…">
          </div>
          <div class="col-lg-6 mb-3">
            <label for="emp_pick"><strong>Or pick one employee</strong></label>
            <select id="emp_pick" class="form-control" style="width:100%"></select>
            <small class="text-muted">Type to search; clears other filters for a single row.</small>
          </div>
        </div>

        <div class="border rounded bg-light p-3 mb-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <strong>Fields to show &amp; save</strong>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-primary" id="col_all">All</button>
              <button type="button" class="btn btn-outline-secondary" id="col_none">None</button>
              <button type="button" class="btn btn-outline-info" id="col_common">Common</button>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="small text-uppercase text-muted mb-1">Basic</div>
              <?php
              $colsBasic = [
                ['first_name', 'First name'],
                ['last_name', 'Last name'],
                ['designation', 'Designation'],
                ['cnic', 'CNIC'],
                ['f_name', 'Father name'],
                ['dob', 'Date of birth'],
                ['gender', 'Gender'],
                ['marital_status', 'Marital status'],
                ['qualification', 'Qualification'],
                ['experience', 'Experience'],
                ['skills', 'Skills'],
                ['address', 'Address'],
              ];
              foreach ($colsBasic as $c) : ?>
                <div class="form-check form-check d-inline-block me-3 mb-1">
                  <input type="checkbox" class="form-check-input upd-col" id="col_<?= esc($c[0]) ?>" value="<?= esc($c[0]) ?>" data-def-common="1">
                  <label class="form-check-label" for="col_<?= esc($c[0]) ?>"><?= esc($c[1]) ?></label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="col-md-4">
              <div class="small text-uppercase text-muted mb-1">Contact</div>
              <?php
              $colsContact = [
                ['mobile_no', 'Mobile'],
                ['mobile_no2', 'Alt mobile'],
                ['emergency_contact_person', 'Emergency person'],
                ['emergency_contact_no', 'Emergency no'],
              ];
              foreach ($colsContact as $c) : ?>
                <div class="form-check form-check d-inline-block me-3 mb-1">
                  <input type="checkbox" class="form-check-input upd-col" id="col_<?= esc($c[0]) ?>" value="<?= esc($c[0]) ?>" data-def-common="1">
                  <label class="form-check-label" for="col_<?= esc($c[0]) ?>"><?= esc($c[1]) ?></label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="col-md-4">
              <div class="small text-uppercase text-muted mb-1">Bank &amp; employment</div>
              <?php
              $colsRest = [
                ['bank_name', 'Bank name'],
                ['account_title', 'Account title'],
                ['account_number', 'Account no'],
                ['branch_code', 'Branch code'],
                ['bank_address', 'Bank address'],
                ['joining_date', 'Joining date'],
                ['salary', 'Basic salary'],
                ['contract_type', 'Contract type'],
                ['salary_payment_method', 'Pay method'],
                ['contract_start', 'Contract start'],
                ['contract_end', 'Contract end'],
              ];
              foreach ($colsRest as $c) : ?>
                <div class="form-check form-check d-inline-block me-3 mb-1">
                  <input type="checkbox" class="form-check-input upd-col" id="col_<?= esc($c[0]) ?>" value="<?= esc($c[0]) ?>" data-def-common="<?= in_array($c[0], ['joining_date', 'salary'], true) ? '1' : '0' ?>">
                  <label class="form-check-label" for="col_<?= esc($c[0]) ?>"><?= esc($c[1]) ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="mb-2">
          <button type="button" class="btn btn-primary" id="btn_load_rows"><i class="fas fa-sync"></i> Load employees</button>
          <span id="load_status" class="text-muted ms-2"></span>
        </div>

        <div id="emp_bulk_cards" class="emp-bulk-cards">
          <div class="alert alert-info mb-0">
            Choose fields above, then click <strong>Load employees</strong>. Each person appears as a card you can scroll through vertically.
          </div>
        </div>
      </div>

      <!-- ——— Quick add ——— -->
      <div class="tab-pane fade p-3" id="tab-add" role="tabpanel">
        <?php if (empty($assignableRoles)) : ?>
          <div class="alert alert-warning">No assignable roles for your account. You cannot use quick batch add until roles are configured.</div>
        <?php else : ?>
          <p class="text-muted">Add up to <strong>25</strong> hires per submit. Each new hire is a <strong>card</strong> (no wide table). Login, CNIC, at least one role, and matching passwords are required.</p>
          <div class="mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_hire_row"><i class="fas fa-plus"></i> Add hire card</button>
            <button type="button" class="btn btn-sm btn-success" id="btn_save_batch_new"><i class="fas fa-cloud-upload-alt"></i> Create all</button>
          </div>
          <div id="newHireBody" class="new-hire-stack"></div>
          <div id="batch_new_result" class="small mt-2"></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
      <a href="<?= base_url('admin/users/add') ?>" class="btn btn-secondary"><i class="fas fa-user-plus"></i> Standard add form</a>
      <a href="<?= base_url('admin/users') ?>" class="btn btn-link">Back to employee list</a>
    </div>
  </div>
</div>

<?php if (!empty($assignableRoles)) : ?>
<div class="d-none" id="tplNewHireWrap">
  <div class="nh-card card card-outline-secondary mb-3">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
      <span class="small text-muted fw-bold text-uppercase">New hire</span>
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-hire" title="Remove">&times;</button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Username</label>
            <input type="text" class="form-control form-control-sm nh-username" placeholder="login" autocomplete="off">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Email</label>
            <input type="email" class="form-control form-control-sm nh-email" autocomplete="off">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Password</label>
            <input type="password" class="form-control form-control-sm nh-pass" autocomplete="new-password">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Confirm password</label>
            <input type="password" class="form-control form-control-sm nh-pass2" autocomplete="new-password">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">First name</label>
            <input type="text" class="form-control form-control-sm nh-fn">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Last name</label>
            <input type="text" class="form-control form-control-sm nh-ln">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">CNIC</label>
            <input type="text" class="form-control form-control-sm nh-cnic" placeholder="35201-1234567-1">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Joining date</label>
            <input type="date" class="form-control form-control-sm nh-join" value="<?= esc($today) ?>">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Mobile</label>
            <input type="text" class="form-control form-control-sm nh-mob">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Salary (PKR)</label>
            <input type="number" step="0.01" class="form-control form-control-sm nh-sal" placeholder="0">
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="form-group mb-2">
            <label class="small mb-0">Designation</label>
            <input type="text" class="form-control form-control-sm nh-des">
          </div>
        </div>
        <div class="col-12">
          <label class="small mb-1 d-block">Roles</label>
          <div class="nh-roles border rounded p-2 bg-light" style="max-height:140px;overflow-y:auto;">
            <?php foreach ($assignableRoles as $role) : ?>
              <label class="small d-block mb-1 fw-normal">
                <input type="checkbox" class="nh-role-cb me-1" value="<?= (int) $role->id ?>"> <?= esc($role->rolename ?? '') ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
  .emp-bulk-cards .emp-bulk-card.border-success { border-start-color: #28a745 !important; }
</style>

<script>
(function () {
  const URL_LOAD = "<?= base_url('admin/users_bulk_info/load_rows') ?>";
  const URL_SAVE = "<?= base_url('admin/users_bulk_info/save_row') ?>";
  const URL_BATCH = "<?= base_url('admin/users_bulk_info/save_batch_new') ?>";
  const URL_SEARCH = "<?= base_url('admin/users_bulk_info/search') ?>";
  const CSRF_NAME = "<?= esc($csrfName) ?>";
  let CSRF_HASH = "<?= esc($csrfHash) ?>";

  function refreshColumns() {
    const on = new Set();
    document.querySelectorAll('.upd-col:checked').forEach(cb => on.add(cb.value));
    document.querySelectorAll('.emp-bulk-card .emp-field-wrap[data-col]').forEach(el => {
      const c = el.getAttribute('data-col');
      if (!c) return;
      el.style.display = on.has(c) ? '' : 'none';
    });
  }

  function appendCsrf(obj) {
    obj[CSRF_NAME] = CSRF_HASH;
    return obj;
  }

  function loadRows() {
    const status = $('#filter_status').val();
    const q = $('#filter_q').val().trim();
    const pick = $('#emp_pick').val();
    $('#load_status').text('Loading…');
    $.post(URL_LOAD, appendCsrf({
      status: status,
      q: q,
      employee_id: pick || ''
    }), function (html) {
      $('#emp_bulk_cards').html(html);
      refreshColumns();
      $('#load_status').text('Done.');
    }).fail(function (xhr) {
      $('#load_status').text('');
      alert(xhr.responseText || 'Load failed');
    });
  }

  function saveRow($card) {
    const fd = new FormData();
    fd.append('id', $card.find('input[name="emp_id"]').val());
    fd.append(CSRF_NAME, CSRF_HASH);
    document.querySelectorAll('.upd-col:checked').forEach(cb => {
      fd.append('selected_fields[]', cb.value);
    });
    const selected = fd.getAll('selected_fields[]');
    if (!selected.length) {
      window.toastr && toastr.warning('Select at least one field above.');
      return;
    }
    selected.forEach(name => {
      const $field = $card.find('[name="' + name + '"]');
      if (!$field.length) return;
      if ($field.is(':checkbox')) {
        fd.append(name, $field.prop('checked') ? '1' : '');
      } else {
        fd.append(name, $field.val() ?? '');
      }
    });

    const $btn = $card.find('.btn-save-emp-row').prop('disabled', true);
    $.ajax({
      url: URL_SAVE,
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json'
    }).done(function (res) {
      if (res && res.success) {
        window.toastr && toastr.success(res.msg || 'Saved');
        $card.addClass('border-success');
        setTimeout(() => $card.removeClass('border-success'), 1400);
      } else {
        window.toastr && toastr.error((res && res.msg) || 'Save failed');
        if (res && res.errors) console.warn(res.errors);
      }
    }).fail(function (xhr) {
      let msg = 'Save failed';
      try { const j = JSON.parse(xhr.responseText); if (j.msg) msg = j.msg; } catch (e) {}
      window.toastr && toastr.error(msg);
    }).always(function () {
      $btn.prop('disabled', false);
    });
  }

  $(function () {
    $('#col_all').on('click', () => { $('.upd-col').prop('checked', true); refreshColumns(); });
    $('#col_none').on('click', () => { $('.upd-col').prop('checked', false); refreshColumns(); });
    $('#col_common').on('click', () => {
      $('.upd-col').prop('checked', false);
      $('.upd-col[data-def-common="1"]').prop('checked', true);
      refreshColumns();
    });
    $('.upd-col').on('change', refreshColumns);
    $('#btn_load_rows').on('click', loadRows);
    $('#emp_bulk_cards').on('click', '.btn-save-emp-row', function () {
      saveRow($(this).closest('.emp-bulk-card'));
    });

    $('#emp_pick').select2({
      placeholder: 'Search employee…',
      allowClear: true,
      minimumInputLength: 2,
      ajax: {
        url: URL_SEARCH,
        dataType: 'json',
        delay: 250,
        data: params => ({ q: params.term, limit: 30 }),
        processResults: data => ({ results: data.results || [] })
      }
    });
    $('#emp_pick').on('change', function () {
      if ($(this).val()) {
        $('#filter_q').val('');
      }
    });

    <?php if (!empty($assignableRoles)) : ?>
    function cloneNewHireRow() {
      const $r = $('#tplNewHireWrap .nh-card').first().clone(true, true);
      $('#newHireBody').append($r);
    }
    $('#btn_add_hire_row').on('click', cloneNewHireRow);
    $('#newHireBody').on('click', '.btn-remove-hire', function () {
      $(this).closest('.nh-card').remove();
    });
    $('#btn_save_batch_new').on('click', function () {
      const rows = [];
      $('#newHireBody .nh-card').each(function () {
        const $tr = $(this);
        const roleIds = $tr.find('.nh-role-cb:checked').map(function () { return parseInt(this.value, 10); }).get();
        rows.push({
          username: $tr.find('.nh-username').val().trim(),
          email: $tr.find('.nh-email').val().trim(),
          password: $tr.find('.nh-pass').val(),
          confirm_password: $tr.find('.nh-pass2').val(),
          first_name: $tr.find('.nh-fn').val().trim(),
          last_name: $tr.find('.nh-ln').val().trim(),
          cnic: $tr.find('.nh-cnic').val().trim(),
          joining_date: $tr.find('.nh-join').val(),
          mobile_no: $tr.find('.nh-mob').val().trim(),
          salary: $tr.find('.nh-sal').val(),
          designation: $tr.find('.nh-des').val().trim(),
          role_ids: roleIds
        });
      });
      if (!rows.length) {
        window.toastr && toastr.info('Add at least one row.');
        return;
      }
      const $btn = $(this).prop('disabled', true);
      $.post(URL_BATCH, appendCsrf({ rows: JSON.stringify(rows) }), function (res) {
        $('#batch_new_result').html('');
        if (res.results) {
          const lines = res.results.map(r => {
            const ok = r.success ? '<span class="text-success">OK</span>' : '<span class="text-danger">Fail</span>';
            return '<div>Row ' + (r.index + 1) + ': ' + ok + ' — ' + (r.msg || '') + (r.id ? ' (id ' + r.id + ')' : '') + '</div>';
          });
          $('#batch_new_result').html(lines.join(''));
        }
        if (res.success) {
          window.toastr && toastr.success(res.msg || 'Created');
        } else {
          window.toastr && toastr.warning(res.msg || 'Some rows failed — see details below.');
        }
        if (res.all_succeeded) {
          $('#newHireBody').empty();
          cloneNewHireRow();
        }
      }, 'json').fail(function () {
        window.toastr && toastr.error('Request failed');
      }).always(function () {
        $btn.prop('disabled', false);
      });
    });
    cloneNewHireRow();
    <?php endif; ?>

    $('#col_common').trigger('click');
  });
})();
</script>

<?= $this->endSection() ?>
