<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Optional: DataTables Buttons CSS (if not globally loaded) -->
<!-- <link rel="stylesheet" href="<?= base_url('resource/datatables/buttons.dataTables.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('resourpgce/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" /> -->

<!-- Content Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="mb-0">Academic Session</h1>
        <small class="text-muted">Manage session years and dates</small>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
          <li class="breadcrumb-item"><a href="<?= base_url('/admin'); ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Academic Session</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header border-0 pb-0">
        <!-- Tabs -->
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" href="<?= base_url('admin/academic_session'); ?>" role="tab">
              Academic Session
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('admin/academic_session/add') ?>">Add Academic Session</a>
          </li>
        </ul>
      </div>

      <div class="card-body pt-3">
        <!-- Toolbar -->
        <div class="row g-2 align-items-end mb-3">
          <div class="col-md-3">
            <label class="mb-1">Search</label>
            <input id="globalSearch" type="text" class="form-control form-control-sm" placeholder="Type to search…">
          </div>
          <div class="col-md-3">
            <label class="mb-1">Start (From)</label>
            <input id="filterStart" type="date" class="form-control form-control-sm" placeholder="YYYY-MM-DD">
          </div>
          <div class="col-md-3">
            <label class="mb-1">End (To)</label>
            <input id="filterEnd" type="date" class="form-control form-control-sm" placeholder="YYYY-MM-DD">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button id="applyFilters" class="btn btn-primary btn-sm mr-2">
              <i class="fas fa-filter mr-1"></i>Apply
            </button>
            <button id="resetFilters" class="btn btn-outline-secondary btn-sm mr-2">
              <i class="fas fa-undo mr-1"></i>Reset
            </button>
            <a href="<?= base_url('admin/academic_session/add') ?>" class="btn btn-success btn-sm">
              <i class="fas fa-plus mr-1"></i>New Session
            </a>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-striped table-hover w-100" id="academic-session-datatable">
            <thead class="thead-light sticky-thead">
              <tr>
                <th style="width:60px">#</th>
                <th>Session Name</th>
                <th>Session Start</th>
                <th>Session End</th>
                <th style="width:120px">Operation</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <!-- Legend / Hints -->
        <div class="mt-3 text-muted small">
          <i class="fas fa-info-circle mr-1"></i>Tip: Use the search box to find sessions quickly. Use date filters to narrow by start/end.
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Optional: DataTables Buttons & Responsive JS (if not globally loaded) -->
<script src="<?= base_url('resource/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
<script>
// ===== Config =====
const endpointData   = '<?= base_url('admin/academic_session/data') ?>';
const sessionId      = <?= json_encode($member_sessionid ?? null); ?>;
const csrfName       = <?= json_encode(csrf_token()) ?>;
const csrfHash       = <?= json_encode(csrf_hash()) ?>;

// ===== Helpers =====
function fmt(dateStr) {
  // Expecting "YYYY-MM-DD" => show as "YYYY-MM-DD" (you can localize if needed)
  return dateStr || '';
}

function printHeader() {
  const printDate = new Date();
  const pad = n => (n < 10 ? '0' + n : n);
  const absolute = printDate.getFullYear() + '-' + pad(printDate.getMonth()+1) + '-' + pad(printDate.getDate())
                   + ' ' + pad(printDate.getHours()) + ':' + pad(printDate.getMinutes());

  const fs = document.getElementById('filterStart').value || '—';
  const fe = document.getElementById('filterEnd').value   || '—';
  return `
    <div style="margin-bottom:8px;">
      <strong>Academic Session</strong><br/>
      <span>Print Date: ${absolute}</span><br/>
      <span>Filter Range: Start ≥ ${fs}, End ≤ ${fe}</span>
    </div>
  `;
}

(function() {
  // Defensive checks for DataTables + Buttons
  if (!$.fn.DataTable) {
    console.error('DataTables not found. Please ensure DataTables scripts are loaded.');
    return;
  }

  const $table  = $('#academic-session-datatable');
  const $search = $('#globalSearch');

  // Build DataTable
  const dt = $table.DataTable({
    processing: true,
    deferRender: true,
    responsive: true,
    autoWidth: false,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    pageLength: 10,
    order: [[1, 'asc']],
    dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6 text-right'B>>" +
         "<'row'<'col-12'tr>>" +
         "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: (function() {
      if (!$.fn.dataTable.Buttons) return [];
      return [
        { extend: 'copy', className: 'btn btn-outline-secondary btn-sm', title: 'Academic Session' },
        { extend: 'excel', className: 'btn btn-outline-secondary btn-sm', title: 'Academic Session' },
        { extend: 'csv', className: 'btn btn-outline-secondary btn-sm', title: 'Academic Session' },
        {
          extend: 'print',
          className: 'btn btn-outline-secondary btn-sm',
          title: '',
          customize: function (win) {
            const hdr = printHeader();
            const $body = $(win.document.body);
            $body.css('font-size', '12px');
            $body.prepend(hdr);
          }
        }
      ];
    })(),
    ajax: {
      url: endpointData,
      type: 'POST',
      data: function(d) {
        // Attach CSRF + filters
        d[csrfName] = csrfHash;
        d.filter_start = $('#filterStart').val() || '';
        d.filter_end   = $('#filterEnd').val() || '';
      },
      dataSrc: function(res) {
        // Optionally refresh CSRF (if API returns it)
        if (res && res.csrf && res.csrf.hash) {
          // eslint-disable-next-line no-undef
          document.querySelectorAll(`input[name="${res.csrf.token}"]`).forEach(el => el.value = res.csrf.hash);
        }
        return res?.data || res || [];
      },
      error: function() {
        toastr.error('Failed to load sessions. Please try again.');
      }
    },
    columns: [
      // Row number
      {
        data: null,
        className: 'align-middle text-muted',
        orderable: false,
        render: function (data, type, row, meta) {
          return meta.row + 1;
        }
      },
      // Session Name
      {
        data: 'session_name',
        className: 'align-middle font-weight-600',
        render: function(val) {
          return val ? $('<div/>').text(val).html() : '';
        }
      },
      // Start
      {
        data: 'start_date',
        className: 'align-middle',
        render: function(val) { return `<span class="badge badge-light border">${fmt(val)}</span>`; }
      },
      // End
      {
        data: 'end_date',
        className: 'align-middle',
        render: function(val) { return `<span class="badge badge-light border">${fmt(val)}</span>`; }
      },
      // Operation
      {
        data: 'id',
        orderable: false,
        className: 'align-middle',
        render: function(id, type, row) {
          let html = '<div class="btn-group btn-group-sm" role="group">';
          if (row.id == sessionId) {
            html += `<a href="<?= base_url('admin/academic_session/edit?id='); ?>${id}" class="btn btn-outline-primary" title="Edit">
                      <i class="fa fa-edit"></i>
                     </a>`; 
          } else {
            html += `<a href="<?= base_url('admin/academic_session/edit?id='); ?>${id}" class="btn btn-outline-primary" title="Edit">
                      <i class="fa fa-edit"></i>
                     </a>`;
            // Place for future actions (delete, activate, etc.)
            // html += `<button class="btn btn-outline-danger" data-id="${id}" title="Delete"><i class="fa fa-trash"></i></button>`;
          }
          html += '</div>';
          return html;
        }
      }
    ],
    rowCallback: function(row, data) {
      // Highlight current session
      if (data.id == sessionId) {
        $(row).addClass('table-success');
      }
    }
  });

  // Global search
  $search.on('keyup change', function() { dt.search(this.value).draw(); });

  // Filters
  $('#applyFilters').on('click', function() { dt.ajax.reload(); });
  $('#resetFilters').on('click', function() {
    $('#filterStart').val('');
    $('#filterEnd').val('');
    $search.val('');
    dt.search('').ajax.reload();
  });

  // Sticky header (CSS helper)
  const style = document.createElement('style');
  style.innerHTML = `
    .sticky-thead th {
      position: sticky; top: 0; z-index: 1;
      background: #f8f9fa;
    }
    .dataTables_wrapper .dt-buttons .btn {
      margin-left: .25rem;
    }
  `;
  document.head.appendChild(style);
})();
</script>

<?= $this->endSection() ?>
