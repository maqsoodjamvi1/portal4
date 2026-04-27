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
#houses-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
  gap:16px;
}

/* House card */
.house-card{
  position:relative;
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#fff;
  box-shadow:0 2px 10px rgba(0,0,0,.03);
  overflow:hidden;
  transition:box-shadow .15s ease, transform .1s ease;
}
.house-card:hover{
  transform:translateY(-1px);
  box-shadow:0 10px 24px rgba(0,0,0,.06);
}

/* Color bar at top uses the house color */
.house-colorbar{
  height:8px;width:100%;background:#94a3b8; /* fallback */
}

/* Header with name + status */
.house-header{
  display:flex;align-items:center;justify-content:space-between;
  padding:12px 14px 0 14px;
}
.house-title{font-weight:700;font-size:16px;margin:0;}
.house-meta{display:flex;align-items:center;gap:10px;}
.badge-dot{display:inline-block;width:12px;height:12px;border-radius:50%;
  border:1px solid rgba(0,0,0,.1);}

/* Body area: color + counts + actions */
.house-body{padding:12px 14px 14px 14px;}

.color-row{
  display:flex;align-items:center;gap:8px;margin-bottom:10px;
  font-size:13px;color:#374151;
}
.color-chip{
  width:18px;height:18px;border-radius:6px;border:1px solid rgba(0,0,0,.08);display:inline-block;
}

.counts{
  display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px;
}
.count-card{
  border:1px solid #e5e7eb;border-radius:10px;padding:8px;text-align:center;background:#fafafa;
}
.count-card .label{font-size:11px;color:#6b7280;line-height:1;}
.count-card .num{font-weight:800;font-size:18px;margin-top:4px;}

.actions{display:flex;justify-content:space-between;align-items:center;gap:8px;}
.actions .btn{padding:6px 10px;}

.status-pill{font-size:11px;padding:4px 8px;border-radius:999px;}
.status-active{background:#e8f5e9;color:#1b5e20;}
.status-inactive{background:#eceff1;color:#455a64;}

.members-wrap{ margin-top:10px; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; display:none; }
.members-head{ background:#f8fafc; padding:8px 12px; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:space-between;}
.members-body{ padding:10px; }
.members-grid{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.members-col{ border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
.members-col .col-title{ background:#f1f5f9; padding:6px 10px; font-weight:700; font-size:12px; }
.members-col table{ width:100%; margin:0; border-collapse:collapse; }
.members-col th, .members-col td{ padding:6px 8px; font-size:12px; border-bottom:1px solid #eef2f7; }
.members-col th{ text-transform:uppercase; letter-spacing:.02em; font-weight:700; background:#fafafa; }
.badge-soft{ background:#eef2ff; border:1px solid #e0e7ff; padding:2px 6px; border-radius:999px; font-size:11px; }
.btn-members{ display:none; } /* default hidden until toggle is ON */
.show-members .btn-members{ display:inline-block; } /* reveal when toggle enabled */

.members-table th.sno,
.members-table td.sno {
  width: 36px;      /* fits 2 digits perfectly */
  text-align: center;
  white-space: nowrap;
}

.members-table th.count,
.members-table td.count {
  width: 28px;      /* fits single digit */
  text-align: center;
  white-space: nowrap;
}

.members-table th.name,
.members-table td.name {
  width: auto;
  max-width: 100%;
  word-break: break-word;
}


.member-card {
  display:flex; flex-direction:column; align-items:center; gap:8px;
  border:1px solid #e5e7eb; border-radius:10px; padding:10px; background:#fff;
  box-shadow:0 1px 6px rgba(0,0,0,.03);
}
.member-avatar { position:relative; width:64px; height:64px; border-radius:12px; overflow:hidden; background:#f3f4f6; }
.member-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
.member-sno {
  position:absolute; bottom:-6px; left:-6px; width:24px; height:24px; border-radius:50%;
  background:#111827; color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center;
  border:2px solid #fff;
}

.member-main { width:100%; text-align:center; }
.member-name { font-weight:700; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.member-line { font-size:11px; color:#6b7280; }
.member-count {
  display:inline-flex; align-items:center; justify-content:center;
  width:22px; height:22px; border-radius:50%; background:#eef2ff; border:1px solid #e0e7ff;
  font-size:12px; font-weight:800; margin-top:2px;
}

.member-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }

.member-meta { font-size:11px; color:#6b7280; margin-top:2px; display:flex; gap:8px; white-space:nowrap; }
.member-meta .dot::before { content:"• "; margin-right:2px; }

.member-info-line {
  font-size: 12px;
  color: #000;          /* <-- BLACK TEXT */
  font-weight: 600;
  display:flex;
  justify-content:center;
  gap:6px;              /* small space between items */
  white-space:nowrap;
}
.member-info-line span::after {
  content:"·";
  margin-left:6px;
}
.member-info-line span:last-child::after {
  content:""; /* remove last dot */
}


.member-meta .meta-item {
    line-height: 1.1;
    font-size: 11px;
}
.member-meta .meta-item i {
    font-size: 12px;
    margin-bottom: 2px;
}
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
      <div class="d-flex align-items-center justify-content-end mb-3">
  <div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" id="toggleShowMembers">
    <label class="form-check-label" for="toggleShowMembers">Show members</label>
  </div>
</div>
      <!-- Cards render here -->
      <div id="houses-grid"></div>

      <!-- Empty state -->
      <div id="empty-state" class="text-center text-muted" style="display:none;">
        <div class="p-4" style="border:1px dashed #cbd5e1;border-radius:12px;">No houses found.</div>
      </div>
    </div>
   </div>
  </div>
 </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function safe(v, d=''){ return (v===undefined || v===null) ? d : v; }
function escHtml(str){
  return String(str).replace(/[&<>"'`=\/]/g, s => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
  }[s]));
}



function renderHouseCard(row){
  const id        = Number(safe(row.house_id, 0));
  const name      = safe(row.house_name, 'Untitled House');
  const colorCode = (safe(row.color_code, '').trim() || '#94a3b8');
  const status    = Number(safe(row.status, 0)) === 1 ? 1 : 0;

  const male      = Number(safe(row.male_count, 0))   || 0;
  const female    = Number(safe(row.female_count, 0)) || 0;
  const total     = Number(safe(row.total_students, male + female)) || (male + female);

  const statusHtml = status
    ? '<span class="status-pill status-active">Active</span>'
    : '<span class="status-pill status-inactive">Inactive</span>';

  return `
    <div class="house-card" data-id="${id}">
      <div class="house-colorbar" style="background:${colorCode}"></div>

      <div class="house-header">
        <h3 class="house-title">${escHtml(name)}</h3>
        <div class="house-meta">
          <span class="badge-dot" title="${escHtml(colorCode)}" style="background:${colorCode}"></span>
          ${statusHtml}
        </div>
      </div>

      <div class="house-body">
        <div class="color-row">
          <span class="color-chip" style="background:${colorCode}"></span>
          <span><strong>Color:</strong> ${escHtml(colorCode)}</span>
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
  <a class="btn btn-sm btn-primary" href="<?= base_url('admin/sports/houses/edit') ?>/${id}">
    <i class="fas fa-edit"></i> Edit
  </a>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-info btn-members" data-id="${id}">
      <i class="fas fa-users"></i> Show Members
    </button>
    <button class="btn btn-sm btn-warning toggle" data-id="${id}">
      <i class="fas fa-toggle-on"></i> Toggle
    </button>
  </div>
</div>

<div class="members-wrap" id="members-${id}">
  <div class="members-head">
    
    
  </div>
  <div class="members-body">
    <div class="members-grid">
      <div class="members-col">
        <div class="col-title">Male</div>
        <div class="col-body"><!-- table inject here --></div>
      </div>
      <div class="members-col">
        <div class="col-title">Female</div>
        <div class="col-body"><!-- table inject here --></div>
      </div>
    </div>
  </div>
</div>
      </div>
    </div>
  `;
}


function renderGrid(rows){
  const grid  = document.getElementById('houses-grid');
  const empty = document.getElementById('empty-state');
  grid.innerHTML = '';

  if (!rows || !rows.length){
    empty.style.display = '';
    return;
  }
  empty.style.display = 'none';

  const frag = document.createDocumentFragment();
  rows.forEach(r => {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = renderHouseCard(r);
    frag.appendChild(wrapper.firstElementChild);
  });
  grid.appendChild(frag);
}

function loadHouses(){
  fetch("<?= base_url('admin/sports/houses/data') ?>", {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body: `${encodeURIComponent(CSRF_NAME)}=${encodeURIComponent(CSRF_HASH)}`
  })
  .then(r => r.json())
  .then(resp => renderGrid((resp && resp.data) ? resp.data : []))
  .catch(() => toastr.error('Failed to load houses'));
}
document.addEventListener('DOMContentLoaded', () => {
  loadHouses();

  const grid = document.getElementById('houses-grid');

  // Ensure the "Show members" switch exists; if not, inject it.
  let toggleUI = document.getElementById('toggleShowMembers');
  if (!toggleUI) {
    const host = document.createElement('div');
    host.className = 'd-flex align-items-center justify-content-end mb-3';
    host.innerHTML = `
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="toggleShowMembers">
        <label class="form-check-label" for="toggleShowMembers">Show members</label>
      </div>`;
    const gridParent = document.getElementById('houses-grid')?.parentElement;
    if (gridParent) gridParent.insertBefore(host, gridParent.firstChild);
    toggleUI = document.getElementById('toggleShowMembers');
  }

  if (toggleUI) {
    toggleUI.addEventListener('change', () => {
      document.body.classList.toggle('show-members', toggleUI.checked);
    });
  }

  if (grid) {
    // Toggle status (event delegation)
    grid.addEventListener('click', (e) => {
      const btn = e.target.closest('.toggle');
      if (!btn) return;

      const id = btn.getAttribute('data-id');
      const body = new URLSearchParams();
      body.append('house_id', id);
      body.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

      fetch("<?= base_url('admin/sports/houses/toggle') ?>", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest' // IMPORTANT for isAJAX()
        },
        body: body.toString()
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

    // Show members
    grid.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-members');
      if (!btn) return;

      const id = btn.getAttribute('data-id');
      const slot = document.getElementById(`members-${id}`);
      if (slot && slot.style.display === 'block') {
        slot.style.display = 'none';
        return;
      }

      const body = new URLSearchParams();
      body.append('house_id', id);
      body.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

      fetch("<?= base_url('admin/sports/houses/members') ?>", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest' // IMPORTANT for isAJAX()
        },
        body: body.toString()
      })
      .then(r => r.json())
      .then(res => {
        if (res && res.ok) {
          renderMembersInto(id, { male: res.male || [], female: res.female || [] });
        } else {
          toastr.error(res && res.msg ? res.msg : 'Failed to load members');
        }
      })
      .catch(() => toastr.error('Failed to load members'));
    });
  }
});



// function renderMembersInto(cardId, data){
//   const wrap = document.getElementById(`members-${cardId}`);
//   if(!wrap) return;
//   const cols = wrap.querySelectorAll('.members-col .col-body');
//   if (cols[0]) cols[0].innerHTML = cardsHTML(data.male);
//   if (cols[1]) cols[1].innerHTML = cardsHTML(data.female);
//   wrap.style.display = 'block';
// }

function renderMembersInto(houseId, data) {
    const slot = document.getElementById(`members-${houseId}`);
    if (!slot) {
        console.error(`members-${houseId} container not found`);
        return;
    }

    const safeCount = arr => Array.isArray(arr) ? arr.length : 0;

    let html = `
        <div class="p-2">
            <h6 class="mb-2">
                <i class="fas fa-female text-pink"></i>
                Girls (${safeCount(data.female)})
            </h6>
            <div class="row">
    `;

    // ------- Girls -------
    (data.female || []).forEach(st => {
        const ageText    = roundedAgeYears(st);
        const eventCount = parseInt(st.event_count ?? 0, 10) || 0;
        const clsSec     = `${st.class}-${st.section}`;

        html += `
            <div class="col-md-6 col-sm-6 col-12 mb-2">
                <div class="border rounded p-1 small">
                    <strong>${st.name}</strong>

                    <div class="d-flex mt-1 text-muted small member-meta">
                        <div class="flex-fill text-center meta-item">
                            <i class="fas fa-birthday-cake d-block"></i>
                            <span>${ageText || '-'}</span>
                        </div>
                        <div class="flex-fill text-center meta-item">
                            <i class="fas fa-school d-block"></i>
                            <span>${clsSec}</span>
                        </div>
                        <div class="flex-fill text-center meta-item">
                            <i class="fas fa-running d-block"></i>
                            <span>(${eventCount})</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += `
            </div>

            <h6 class="mt-3 mb-2">
                <i class="fas fa-male text-primary"></i>
                Boys (${safeCount(data.male)})
            </h6>
            <div class="row">
    `;

    // ------- Boys -------
    (data.male || []).forEach(st => {
        const ageText    = roundedAgeYears(st);
        const eventCount = parseInt(st.event_count ?? 0, 10) || 0;
        const clsSec     = `${st.class}-${st.section}`;

        html += `
            <div class="col-md-6 col-sm-6 col-12 mb-2">
                <div class="border rounded p-1 small">
                    <strong>${st.name}</strong>

                    <div class="d-flex mt-1 text-muted small member-meta">
                        <div class="flex-fill text-center meta-item">
                            <i class="fas fa-birthday-cake d-block"></i>
                            <span>${ageText || '-'}</span>
                        </div>
                        <div class="flex-fill text-center meta-item">
                            <i class="fas fa-school d-block"></i>
                            <span>${clsSec}</span>
                        </div>
                        <div class="flex-fill text-center meta-item">
                            <i class="fas fa-running d-block"></i>
                            <span>(${eventCount})</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += `</div></div>`;

    slot.innerHTML = html;
    slot.style.display = 'block';
}

function roundedAgeYears(rowOrJq) {
  // 1) New usage: pass the student object from AJAX (with age_years)
  if (rowOrJq && !rowOrJq.jquery && typeof rowOrJq === 'object') {
    // If API already gave us the rounded age in years
    if (rowOrJq.hasOwnProperty('age_years')) {
      const age = rowOrJq.age_years;
      if (age === null || age === undefined || age === '') return '';
      const n = parseInt(age, 10);
      if (isNaN(n)) return '';
      return n + 'y';
    }

    // Fallback if you ever send DOB + db_status in the object later
    const dbStatus = parseInt(rowOrJq.db_status || "0", 10);
    let dobStr = '';
    if (dbStatus === 1 && rowOrJq.date_of_birth_age) {
      dobStr = rowOrJq.date_of_birth_age;
    } else {
      dobStr = rowOrJq.date_of_birth;
    }
    return calcRoundedAgeFromDob(dobStr);
  }

  // 2) Backward compatibility: old usage with jQuery row
  const $row = rowOrJq;
  if (!$row || !$row.find) return '';

  const dbStatus = parseInt($row.find('input[name="db_status"]').val() || "0", 10);

  let dobStr = "";
  if (dbStatus === 1) {
    dobStr = $row.find('input[name="date_of_birth_age"]').val();
  } else {
    dobStr = $row.find('input[name="date_of_birth"]').val();
  }

  return calcRoundedAgeFromDob(dobStr);
}

/**
 * Helper: calculates age "Xy" with rounding rule:
 * - 0–5 months  => keep year
 * - 6–11 months => year + 1
 */
function calcRoundedAgeFromDob(dobStr) {
  if (!dobStr) return '';

  const dob = new Date(dobStr);
  if (isNaN(dob)) return '';

  const now = new Date();
  let years = now.getFullYear() - dob.getFullYear();
  let m = now.getMonth() - dob.getMonth();
  let d = now.getDate() - dob.getDate();

  let monthsDiff = m + (d < 0 ? -1 : 0);

  if (m < 0 || (m === 0 && d < 0)) {
    years--;
    monthsDiff = (12 + m) + (d < 0 ? -1 : 0);
  }

  if (monthsDiff >= 6) years++;

  return years + 'y';
}


function cardsHTML(list){
  const BASE = '<?= rtrim(base_url(), "/") ?>';
  const rows = (list||[]).map((r,i)=>{
    const sno  = String(i+1).padStart(2,'0');
    const cnt  = Number(r.participation_count || 0);
    const name = escHtml(r.full_name || '');
    const klass = escHtml(r.class_short || '');
    const age  = roundedAgeYears(r.date_of_birth || r.dob || '');
    
    const img  = (r.profile_photo && String(r.profile_photo).trim() !== '')
      ? `${BASE}/uploads/${escHtml(r.profile_photo)}`
      : `${BASE}/uploads/avatar_student.png`;

    return `
      <div class="member-card">
        
        <div class="member-avatar">
          <img src="${img}" alt="${name}">
          <div class="member-sno">${sno}</div>
        </div>

        <div class="member-main">

          <div class="member-name" title="${name}">${name}</div>

          <div class="member-info-line">
            <span>${klass || ''}</span>
            <span>${age || ''}</span>
            <span>${cnt}</span>
          </div>

        </div>
      </div>`;
  }).join('');

  return `<div class="member-cards">${rows || '<div class="text-muted">No members</div>'}</div>`;
}

</script>

<?= $this->endSection() ?>
