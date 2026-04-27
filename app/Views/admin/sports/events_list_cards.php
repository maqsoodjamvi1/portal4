<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-running"></i> Sports Events</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Sports Events</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<style>
#events-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
  gap:16px;
}
.event-card{
  border:1px solid #e5e7eb; border-radius:14px; background:#fff;
  box-shadow:0 2px 10px rgba(0,0,0,.03); overflow:hidden;
  transition:box-shadow .15s ease, transform .1s ease;
}
.event-card:hover{ transform:translateY(-1px); box-shadow:0 10px 24px rgba(0,0,0,.06); }
.event-header{ display:flex; align-items:center; justify-content:space-between; padding:12px 14px; }
.event-title{ font-weight:700; font-size:16px; margin:0; }
.event-sub{ font-size:12px; color:#6b7280; }
.event-body{ padding:0 14px 12px 14px; }

.pills{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px; }
.pill{
  display:inline-flex; align-items:center; gap:6px;
  font-size:12px; border:1px solid #e5e7eb; border-radius:999px; padding:5px 10px; background:#fafafa;
}
.pill .chip{ width:12px; height:12px; border-radius:4px; border:1px solid rgba(0,0,0,.1); }

.house-row{ display:flex; flex-wrap:wrap; gap:8px; }
.house-badge{
  display:flex; align-items:center; gap:6px;
  font-size:12px; border:1px solid #e5e7eb; border-radius:10px; padding:6px 8px; background:#fff;
}
.house-badge .dot{ width:10px; height:10px; border-radius:50%; border:1px solid rgba(0,0,0,.08); }

.actions{ display:flex; gap:8px; padding:10px 14px 14px 14px; }
.actions .btn{ padding:6px 10px; }
</style>

<section class="content">
  <div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
      <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/sports/events') ?>">List</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/sports/events/add') ?>">Add Event</a></li>
      </ul>
    </div>

    <div class="card-body">
      <div id="events-grid"></div>
      <div id="empty-state" class="text-center text-muted" style="display:none;">
        <div class="p-4" style="border:1px dashed #cbd5e1; border-radius:12px;">No events found.</div>
      </div>
    </div>
  </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function escHtml(str){
  return String(str ?? '').replace(/[&<>"'`=\/]/g, s => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
  }[s]));
}
function cap(s){ s = (s||'').toLowerCase(); return s.charAt(0).toUpperCase()+s.slice(1); }
function iconForGender(g){
  const v = (g||'').toLowerCase().trim();
  if (v === 'male')   return '<i class="fas fa-mars" title="Male"></i>';
  if (v === 'female') return '<i class="fas fa-venus" title="Female"></i>';
  return '<i class="fas fa-venus-mars" title="Mixed"></i>';
}

function renderEventCard(ev){
  const id     = ev.event_id;
  const name   = escHtml(ev.event_name || 'Unnamed Event');
  const date   = escHtml(ev.event_date || '');
  const type   = (ev.event_type || '').toLowerCase(); // 'individual' | 'team'
  const gender = (ev.gender || '').toLowerCase();

  // Individual: counts_by_house = [{house_id, house_name, color_code, total}]
  // Team: teams_count, team_members_count
  let statsHtml = '';

  if (type === 'team') {
    const teams = Number(ev.teams_count || 0);
    const mems  = Number(ev.team_members_count || 0);
    statsHtml += `
      <div class="pills" style="margin-bottom:10px;">
        <span class="pill"><i class="fas fa-users"></i> Teams: <strong>${teams}</strong></span>
        <span class="pill"><i class="fas fa-user-friends"></i> Participants: <strong>${mems}</strong></span>
      </div>
    `;
  } else {
    const list = Array.isArray(ev.counts_by_house) ? ev.counts_by_house : [];
    if (list.length) {
      const items = list.map(h => {
        const name = escHtml(h.house_name || ('House #' + h.house_id));
        let cc = (h.color_code || '').trim();
        if (cc && !cc.startsWith('#')) cc = '#'+cc;
        const total = Number(h.total || 0);
        return `<span class="house-badge"><span class="dot" style="background:${cc||'#e5e7eb'}"></span>${name}: <strong>${total}</strong></span>`;
      }).join('');
      statsHtml += `<div class="house-row">${items}</div>`;
    } else {
      statsHtml += `<div class="text-muted" style="font-size:12px;">No participants yet.</div>`;
    }
  }

  return `
    <div class="event-card">
      <div class="event-header">
        <div>
          <h3 class="event-title">${name}</h3>
          <div class="event-sub">
            <i class="far fa-calendar-alt"></i> ${date || '-'}
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <span class="pill" title="${cap(type)}"><i class="fas fa-tag"></i> ${cap(type)}</span>
          <span class="pill" title="${cap(gender)}">${iconForGender(gender)}</span>
        </div>
      </div>

      <div class="event-body">
        ${statsHtml}
      </div>

      <div class="actions">
        <a class="btn btn-sm btn-primary" href="<?= base_url('admin/sports/events/edit') ?>/${id}"><i class="fas fa-edit"></i> Edit</a>
        <a class="btn btn-sm btn-info" href="<?= base_url('admin/sports/managers') ?>/${id}"><i class="fas fa-user-tie"></i> Managers</a>
        <a class="btn btn-sm btn-secondary" href="<?= base_url('admin/sports/entries') ?>/${id}"><i class="fas fa-user-plus"></i> Entries</a>
        <a class="btn btn-sm btn-success" href="<?= base_url('admin/sports/results') ?>/${id}"><i class="fas fa-trophy"></i> Results</a>
      </div>
    </div>
  `;
}

function renderGrid(events){
  const grid = document.getElementById('events-grid');
  const empty = document.getElementById('empty-state');
  grid.innerHTML = '';

  if (!events || !events.length){
    empty.style.display = '';
    return;
  }
  empty.style.display = 'none';

  const frag = document.createDocumentFragment();
  events.forEach(ev => {
    const wrap = document.createElement('div');
    wrap.innerHTML = renderEventCard(ev);
    frag.appendChild(wrap.firstElementChild);
  });
  grid.appendChild(frag);
}

function loadEvents(){
  fetch("<?= base_url('admin/sports/events/data') ?>", {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body:`${encodeURIComponent(CSRF_NAME)}=${encodeURIComponent(CSRF_HASH)}`
  })
  .then(r => r.json())
  .then(resp => {
    const rows = (resp && resp.data) ? resp.data : [];
    renderGrid(rows);
  })
  .catch(() => toastr.error('Failed to load events'));
}

document.addEventListener('DOMContentLoaded', loadEvents);
</script>

<?= $this->endSection() ?>
