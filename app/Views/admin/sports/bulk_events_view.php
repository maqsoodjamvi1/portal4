<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Bulk Sports Events</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin') ?>">Home</a></li>
          <li class="breadcrumb-item active">Sports</li>
          <li class="breadcrumb-item active">Bulk Events</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    <div class="card card-primary card-outline">
      <div class="card-body">
        <form id="bulkForm" method="post" action="<?= site_url('admin/sports/bulk-events/save') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="campus_id" value="<?= (int)$campusId ?>">
          <input type="hidden" name="session_id" value="<?= (int)$sessionId ?>">

          <div class="row g-2">
            <div class="col-md-3">
              <label class="form-label">Event Type</label>
              <select class="form-control" name="event_type" id="event_type" required>
                <option value="individual">Individual</option>
                <option value="team">Team</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Gender</label>
              <select class="form-control" name="gender" id="gender" required>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="mixed">Mixed</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Event Date</label>
              <input type="date" class="form-control" name="event_date" id="event_date" required>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
              <button type="button" id="btnLoad" class="btn btn-secondary mr-2"><i class="fa fa-download"></i> Load Existing</button>
              <button type="button" id="btnAddRow" class="btn btn-info"><i class="fa fa-plus"></i> Add Row</button>
            </div>
          </div>

          <hr>

          <div class="table-responsive">
            <table class="table table-sm table-bordered" id="eventsTable">
              <thead class="thead-light">
                <tr id="theadRow">
                  <th style="width:30%">Event Name</th>
                  <th style="width:12%">Per House</th>
                  <th style="width:12%">Min Age</th>
                  <th style="width:12%">Max Age</th>
                  <th class="th-team-size d-none" style="width:12%">Team Size</th>
                  <th style="width:10%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody id="rowsBody">
                <!-- rows go here -->
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save All</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</section>
<script>
(function () {
  const BASE_LOAD_URL = "<?= site_url('admin/sports/bulk-events/load') ?>";
  const formEl = document.getElementById('bulkForm');
  const rowsBody = document.getElementById('rowsBody');
  const btnAddRow = document.getElementById('btnAddRow');
  const btnLoad = document.getElementById('btnLoad');
  const isTeam = () => document.getElementById('event_type').value === 'team';

  /* ---------- helpers ----------- */
  function showMsg(type, text) {
    // Prefer Toastr if available
    if (window.toastr) {
      const fn = { success: 'success', error: 'error', danger: 'error', info: 'info', warning: 'warning' }[type] || 'info';
      toastr[fn](text);
      return;
    }
    // Fallback: inline Bootstrap alert (auto dismiss)
    let wrap = document.getElementById('flash-wrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.id = 'flash-wrap';
      wrap.className = 'mb-2';
      formEl.closest('.card-body').prepend(wrap);
    }
    wrap.innerHTML = `
      <div class="alert alert-${type === 'danger' ? 'danger' : type} alert-dismissible fade show" role="alert">
        ${text}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>`;
    setTimeout(() => {
      const a = wrap.querySelector('.alert');
      if (a) a.classList.remove('show');
    }, 3500);
  }

  function clearFieldErrors() {
    rowsBody.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    rowsBody.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
  }

  function markErrors(errors) {
    // errors keys like: existing.12.event_name, new.0.team_size, etc.
    Object.keys(errors || {}).forEach(key => {
      const parts = key.split('.');
      let selector = '';
      if (parts[0] === 'existing') {
        // existing[EVENT_ID][field]
        const eventId = parts[1];
        const field = parts[2];
        selector = `input[name="existing[${eventId}][${field}]"]`;
      } else if (parts[0] === 'new') {
        // new_fieldName[index]
        const idx = parseInt(parts[1], 10);
        const field = parts[2];
        const map = {
          event_name: 'new_event_name[]',
          per_house_count: 'new_per_house_count[]',
          min_age: 'new_min_age[]',
          max_age: 'new_max_age[]',
          team_size: 'new_team_size[]',
        };
        const name = map[field] || '';
        const candidates = Array.from(rowsBody.querySelectorAll(`input[name="${name}"]`));
        const el = candidates[idx];
        if (el) {
          el.classList.add('is-invalid');
          const fb = document.createElement('div');
          fb.className = 'invalid-feedback';
          fb.textContent = errors[key];
          if (!el.parentElement.querySelector('.invalid-feedback')) {
            el.parentElement.appendChild(fb);
          }
        }
        return; // continue next error
      }

      if (selector) {
        const el = rowsBody.querySelector(selector);
        if (el) {
          el.classList.add('is-invalid');
          const fb = document.createElement('div');
          fb.className = 'invalid-feedback';
          fb.textContent = errors[key];
          if (!el.parentElement.querySelector('.invalid-feedback')) {
            el.parentElement.appendChild(fb);
          }
        }
      }
    });

    // Scroll to first error if any
    const firstErr = rowsBody.querySelector('.is-invalid');
    if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function toggleTeamColumn() {
    const th = document.querySelector('.th-team-size');
    const rows = document.querySelectorAll('#rowsBody tr');
    if (!th) return;
    if (isTeam()) {
      th.classList.remove('d-none');
      rows.forEach(r => {
        const cell = r.querySelector('.td-team-size');
        if (cell) cell.classList.remove('d-none');
      });
    } else {
      th.classList.add('d-none');
      rows.forEach(r => {
        const cell = r.querySelector('.td-team-size');
        if (cell) cell.classList.add('d-none');
      });
    }
  }

  function newRowHtml() {
    const teamCol = isTeam() ? `
      <td class="td-team-size">
        <input type="number" name="new_team_size[]" class="form-control form-control-sm" min="1" required>
      </td>` : `<td class="td-team-size d-none">
        <input type="number" name="new_team_size[]" class="form-control form-control-sm" min="1">
      </td>`;
    return `
      <tr class="row-new">
        <td><input type="text" name="new_event_name[]" class="form-control form-control-sm" required minlength="3" placeholder="Event name"></td>
        <td><input type="number" name="new_per_house_count[]" class="form-control form-control-sm" min="1" required></td>
        <td><input type="number" name="new_min_age[]" class="form-control form-control-sm" min="0" required></td>
        <td><input type="number" name="new_max_age[]" class="form-control form-control-sm" min="0" required></td>
        ${teamCol}
        <td class="text-center align-middle">
          <button type="button" class="btn btn-sm btn-danger btn-del-row"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`;
  }

  /* ---------- events: add/delete rows ---------- */
  btnAddRow.addEventListener('click', function () {
    rowsBody.insertAdjacentHTML('beforeend', newRowHtml());
  });

  rowsBody.addEventListener('click', function (e) {
    if (e.target.closest('.btn-del-row')) {
      const tr = e.target.closest('tr.row-new');
      if (tr) tr.remove();
    }
  });

  /* ---------- Load Existing ---------- */
  btnLoad.addEventListener('click', function () {
    const type = document.getElementById('event_type').value;
    const gender = document.getElementById('gender').value;
    const date = document.getElementById('event_date').value;

    if (!type || !gender || !date) {
      showMsg('warning', 'Please select Event Type, Gender and Event Date first.');
      return;
    }

    const fd = new FormData();
    fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    fd.append('event_type', type);
    fd.append('gender', gender);
    fd.append('event_date', date);

    fetch(BASE_LOAD_URL, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(r => r.json())
      .then(j => {
        if (!j.ok) { showMsg('danger', j.msg || 'Failed to load records.'); return; }
        // Remove previously loaded existing rows; keep unsaved "new" rows
        rowsBody.querySelectorAll('tr.row-existing').forEach(x => x.remove());
        const temp = document.createElement('tbody');
        temp.innerHTML = j.html;
        Array.from(temp.children).reverse().forEach(tr => rowsBody.prepend(tr));
        toggleTeamColumn();
        showMsg('info', 'Existing events loaded.');
      })
      .catch(() => showMsg('danger', 'Request failed while loading.'));
  });

  /* ---------- Save (AJAX; stay on same page) ---------- */
  formEl.addEventListener('submit', function (e) {
    e.preventDefault(); // prevent navigation to JSON pretty-print
    clearFieldErrors();

    const fd = new FormData(formEl);
    fetch(formEl.getAttribute('action'), {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(r => r.json())
      .then(j => {
        if (j.ok) {
          showMsg('success', j.msg || 'Saved successfully.');
          // OPTIONAL: after success you can reload existing to reflect inserts:
          // document.getElementById('btnLoad').click();
        } else {
          if (j.errors) {
            markErrors(j.errors);
            showMsg('danger', 'Please fix the highlighted fields.');
          } else {
            showMsg('danger', j.msg || 'Save failed.');
          }
        }
      })
      .catch(() => showMsg('danger', 'Request failed while saving.'));
  });

  /* ---------- misc ---------- */
  document.getElementById('event_type').addEventListener('change', toggleTeamColumn);
  toggleTeamColumn();

  // max_age >= min_age guard
  rowsBody.addEventListener('input', function (e) {
    if (e.target.name && e.target.name.includes('max_age')) {
      const tr = e.target.closest('tr');
      const minI = tr.querySelector('input[name*="min_age"]');
      if (minI && parseInt(e.target.value || '0') < parseInt(minI.value || '0')) {
        e.target.setCustomValidity('Max age must be >= Min age');
      } else {
        e.target.setCustomValidity('');
      }
    }
  });
})();
</script>


<?= $this->endSection() ?>
