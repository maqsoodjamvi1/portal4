<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-flag"></i> Sports Houses</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Sports Houses</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<style>
/* Grid wrapper for house cards */
#houses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

/* House card */
.house-card {
  position: relative;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 2px 10px rgba(0,0,0,.03);
  overflow: hidden;
  transition: box-shadow .15s ease, transform .1s ease;
}
.house-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(0,0,0,.06);
}

/* Color bar at top uses the house color */
.house-colorbar {
  height: 8px;
  width: 100%;
  background: #94a3b8; /* fallback */
}

/* Header with name + status */
.house-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 14px 0 14px;
}
.house-title {
  font-weight: 700; font-size: 16px; margin: 0;
}
.house-meta {
  display: flex; align-items: center; gap: 10px;
}
.badge-dot {
  display: inline-block; width: 12px; height: 12px; border-radius: 50%;
  border: 1px solid rgba(0,0,0,.1);
}

/* Body area: color + counts + actions */
.house-body {
  padding: 12px 14px 14px 14px;
}

.color-row {
  display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
  font-size: 13px; color: #374151;
}
.color-chip {
  width: 18px; height: 18px; border-radius: 6px; border: 1px solid rgba(0,0,0,.08);
  display: inline-block;
}

.counts {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  margin-bottom: 12px;
}
.count-card {
  border: 1px solid #e5e7eb; border-radius: 10px; padding: 8px;
  text-align: center; background: #fafafa;
}
.count-card .label { font-size: 11px; color: #6b7280; line-height: 1; }
.count-card .num   { font-weight: 800; font-size: 18px; margin-top: 4px; }

.actions {
  display: flex; justify-content: space-between; align-items: center; gap: 8px;
}
.actions .btn { padding: 6px 10px; }

.status-pill {
  font-size: 11px; padding: 4px 8px; border-radius: 999px;
}
.status-active { background: #e8f5e9; color: #1b5e20; }
.status-inactive { background: #eceff1; color: #455a64; }
</style>

<section class="content">
 <div class="row">
  <div class="col-lg-12">
   <div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
      <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/sports/houses') ?>">List</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/sports/houses/add') ?>">Add House</a></li>
      </ul>
    </div>
    <div class="card-body">
      <!-- Cards go here -->
      <div id="houses-grid"></div>
      <!-- Empty state -->
      <div id="empty-state" class="text-center text-muted" style="display:none;">
        <div class="p-4" style="border:1px dashed #cbd5e1; border-radius:12px;">
          No houses found.
        </div>
      </div>
    </div>
   </div>
  </div>
 </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function safe(val, def='') { return (val === null || val === undefined) ? def : val; }
function escHtml(str) {
  return String(str).replace(/[&<>"'`=\/]/g, s => ({
    '&': '&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;', '/':'&#x2F;', '`':'&#x60;', '=':'&#x3D;'
  }[s]));
}

function renderHouseCard(row) {
  const id         = safe(row.house_id, 0);
  const name       = safe(row.house_name, 'Untitled House');
  const colorHex   = safe(row.color_code, '').trim() || '#94a3b8';
  const status     = Number(safe(row.status, 0)) === 1 ? 1 : 0;

  // counts (accept either *_count or compute)
  const male   = Number(safe(row.male_count, safe(row.male, 0))) || 0;
  const female = Number(safe(row.female_count, safe(row.female, 0))) || 0;
  let total    = Number(safe(row.total_count, safe(row.total, 0)));
  if (!total) total = male + female;

  const statusHtml = status
    ? '<span class="status-pill status-active">Active</span>'
    : '<span class="status-pill status-inactive">Inactive</span>';

  return `
    <div class="house-card" data-id="${id}">
      <div class="house-colorbar" style="background:${colorHex}"></div>

      <div class="house-header">
        <h3 class="house-title">${escHtml(name)}</h3>
        <div class="house-meta">
          <span class="badge-dot" title="${escHtml(colorHex)}" style="background:${colorHex}"></span>
          ${statusHtml}
        </div>
      </div>

      <div class="house-body">
        <div class="color-row">
          <span class="color-chip" style="background:${colorHex}"></span>
          <span><strong>Color:</strong> ${escHtml(colorHex)}</span>
        </div>

        <div class="counts">
          <div class="count-card">
            <div class="label">Total</div>
            <div class="num">${total}</div>
          </div>
          <div class="count-card">
            <div class="label">Male</div>
            <div class="num">${male}</div>
          </div>
          <div class="count-card">
            <div class="label">Female</div>
            <div class="num">${female}</div>
          </div>
        </div>

        <div class="actions">
          <div>
            <a class="btn btn-sm btn-primary" href="<?= base_url('admin/sports/houses/edit') ?>/${id}">
              <i class="fas fa-edit"></i> Edit
            </a>
          </div>
          <div>
            <button class="btn btn-sm btn-warning toggle" data-id="${id}">
              <i class="fas fa-toggle-on"></i> Toggle
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
}

function renderGrid(list) {
  const grid = document.getElementById('houses-grid');
  const empty = document.getElementById('empty-state');
  grid.innerHTML = '';

  if (!list || !list.length) {
    empty.style.display = '';
    return;
  }
  empty.style.display = 'none';

  const frag = document.createDocumentFragment();
  list.forEach(row => {
    const div = document.createElement('div');
    div.innerHTML = renderHouseCard(row);
    frag.appendChild(div.firstElementChild);
  });
  grid.appendChild(frag);
}

function loadHouses() {
  fetch("<?= base_url('admin/sports/houses/data') ?>", {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body: encodeURI(`${CSRF_NAME}=${encodeURIComponent(CSRF_HASH)}`)
  })
  .then(r => r.json())
  .then(resp => {
    const rows = (resp && resp.data) ? resp.data : [];

    // Optional: if your endpoint can be extended to include counts, great.
    // If not, rows will still render with 0s.
    renderGrid(rows);
  })
  .catch(() => {
    toastr.error('Failed to load houses');
  });
}

document.addEventListener('DOMContentLoaded', () => {
  loadHouses();

  // Toggle click (event delegation)
  document.getElementById('houses-grid').addEventListener('click', (e) => {
    const btn = e.target.closest('.toggle');
    if (!btn) return;

    const id = btn.getAttribute('data-id');
    const payload = new URLSearchParams();
    payload.append('house_id', id);
    payload.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch("<?= base_url('admin/sports/houses/toggle') ?>", {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: payload.toString()
    })
    .then(r => r.json())
    .then(res => {
      if (res && res.ok) {
        toastr.success('Status changed');
        loadHouses();
      } else {
        toastr.error(res && res.msg ? res.msg : 'Failed to update status');
      }
    })
    .catch(() => toastr.error('Failed to update status'));
  });
});
</script>

<?= $this->endSection() ?>
