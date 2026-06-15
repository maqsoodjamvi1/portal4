<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Bulk Create Events',
    'icon' => 'fas fa-layer-group',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Events', 'url' => base_url('admin/sports/events')],
        ['label' => 'Bulk Create', 'active' => true],
    ],
]) ?>

<section class="content">
 <div class="row">
  <div class="col-lg-12">
   <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Top Filters</h3></div>
    <div class="card-body">

      <?= csrf_field() ?>
      <div class="row">
        <div class="form-group col-md-3">
          <label>Event Type</label>
          <select id="f_type" class="form-control">
            <option value="individual">Individual</option>
            <option value="team">Team</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Gender</label>
          <select id="f_gender" class="form-control">
            <option value="mixed">Mixed</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Event Date</label>
          <input type="date" id="f_date" class="form-control">
        </div>
        <div class="form-group col-md-3 d-flex align-items-end">
          <button id="btnFetch" class="btn btn-outline-secondary w-100">Load Existing</button>
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Add Rows</h5>
        <div>
          <button id="btnAddRow" type="button" class="btn btn-sm btn-primary">Add Row</button>
          <button id="btnClear"  type="button" class="btn btn-sm btn-warning ms-1">Clear</button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered" id="rowsTable">
          <thead class="table-light">
            <tr>
              <th width="32">#</th>
              <th>Event Name</th>
              <th width="140">Per House Count</th>
              <th width="120">Min Age</th>
              <th width="120">Max Age</th>
              <th width="60">Del</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="text-end">
        <button id="btnSave" class="btn btn-success"><i class="fas fa-save"></i> Save All</button>
      </div>

    </div>
   </div>
  </div>
 </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function addRow(row={event_id:'', event_name:'', per_house_count:'', min_age:'', max_age:''}) {
  const idx = $('#rowsTable tbody tr').length + 1;
  const tr = `
    <tr>
      <td class="text-center align-middle sno">${idx}</td>
      <td>
        <input type="hidden" name="event_id[]" value="${escapeHtml(row.event_id||'')}">
        <input type="text" name="event_name[]" class="form-control" value="${escapeHtml(row.event_name||'')}" required>
      </td>
      <td><input type="number" name="per_house_count[]" class="form-control" min="0" step="1" value="${escapeHtml(row.per_house_count ?? '')}"></td>
      <td><input type="number" name="min_age[]" class="form-control" min="0" step="1" value="${escapeHtml(row.min_age ?? '')}"></td>
      <td><input type="number" name="max_age[]" class="form-control" min="0" step="1" value="${escapeHtml(row.max_age ?? '')}"></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-danger delRow">&times;</button></td>
    </tr>`;
  $('#rowsTable tbody').append(tr);
  renumber();
}

function renumber(){
  $('#rowsTable tbody .sno').each(function(i){ $(this).text(i+1); });
}

function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[m])); }

$('#btnAddRow').on('click', ()=> addRow());
$('#rowsTable').on('click','.delRow', function(){
  $(this).closest('tr').remove();
  renumber();
});
$('#btnClear').on('click', ()=> { $('#rowsTable tbody').empty(); });

$('#btnFetch').on('click', function(e){
  e.preventDefault();
  const payload = {
    [CSRF_NAME]: CSRF_HASH,
    event_type: $('#f_type').val(),
    gender:     $('#f_gender').val(),
    event_date: $('#f_date').val()
  };
  if (!payload.event_date) { alert('Pick Event Date first'); return; }

  $.post('<?= base_url('admin/sports/events/bulk/fetch') ?>', payload, function(res){
    if (!res || !res.ok) { toastr?.error?.(res?.msg || 'Failed'); return; }
    $('#rowsTable tbody').empty();
    const rows = res.rows || [];
    if (rows.length === 0) {
      toastr?.info?.('No existing events found. Add your own rows.');
      addRow(); // start with one row
      return;
    }
    rows.forEach(r => addRow(r));
    toastr?.success?.(`Loaded ${rows.length} row(s).`);
  }, 'json');
});

$('#btnSave').on('click', function(e){
  e.preventDefault();

  const event_id  = $('input[name="event_id[]"]').map((_,el)=> $(el).val()).get();
  const names  = $('input[name="event_name[]"]').map((_,el)=> $(el).val()).get();
  const phc    = $('input[name="per_house_count[]"]').map((_,el)=> $(el).val()).get();
  const minAge = $('input[name="min_age[]"]').map((_,el)=> $(el).val()).get();
  const maxAge = $('input[name="max_age[]"]').map((_,el)=> $(el).val()).get();

  const payload = {
    [CSRF_NAME]: CSRF_HASH,
    event_type: $('#f_type').val(),
    gender:     $('#f_gender').val(),
    event_date: $('#f_date').val(),
    'event_id[]': event_id,
    'event_name[]': names,
    'per_house_count[]': phc,
    'min_age[]': minAge,
    'max_age[]': maxAge
  };

  if (!payload.event_date) { toastr?.warning?.('Pick Event Date'); return; }
  if (names.length === 0) { toastr?.warning?.('Add at least one row'); return; }

  $.ajax({
    url: '<?= base_url('admin/sports/events/bulk/save') ?>',
    method: 'POST',
    data: payload,
    traditional: true, // important so arrays are not serialized as nested objects
    dataType: 'json',
    success: function(res){
      if (res && res.ok) {
        toastr?.success?.(`Saved ${res.saved} event(s).`);
        // optionally redirect to events list
        // window.location = '<?= base_url('admin/sports/events') ?>';
      } else {
        if (res?.errors) {
  const firstKey = Object.keys(res.errors)[0];
  const err = res.errors[firstKey];
  toastr.error(err?.message || JSON.stringify(err));
} else {
  toastr.error(res?.msg || 'Failed');
}

      }
    },
    error: function(){
      toastr?.error?.('Request failed');
    }
  });
});

// start with one row by default
addRow();
</script>

<?= $this->endSection() ?>
