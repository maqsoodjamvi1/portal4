<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-clipboard-list"></i> Events & Participants Report</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Reports</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<style>
/* ================== SCREEN STYLES ================== */

/* ----- basic tile position context ----- */
.std-tile{
  position: relative;
}

/* Position badge – top right of student card */
.std-pos-badge{
  position:absolute;
  top:2px;
  right:2px;
  padding:1px 4px;
  border-radius:999px;
  font-size:8px;
  line-height:1;
  display:inline-flex;
  align-items:center;
  gap:2px;
  color:#fff;
  box-shadow:0 1px 2px rgba(0,0,0,.25);
}
.std-pos-badge i{ font-size:8px; }

/* Colors for 1st / 2nd / 3rd */
.pos-gold  { background:#f59e0b; }
.pos-silver{ background:#9ca3af; }
.pos-bronze{ background:#b45309; }

/* --------- POSITION ROW (select + delete) --------- */

.std-pos-control{
  margin-top:8px;
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
}

/* main “Set position” select – looks like a pill button */
.std-pos-select{
  flex:1 1 auto;
  min-width:0;
  height:34px;
  padding:4px 30px 4px 34px;        /* left room for medal icon, right for arrow */
  border-radius:999px;
  border:1px solid #cbd5e1;
  background-color:#f8fafc;
  font-size:11px;
  line-height:1.2;
  color:#0f172a;
  cursor:pointer;
  appearance:none;
  -webkit-appearance:none;
  -moz-appearance:none;
  position:relative;
  transition:border-color .15s, box-shadow .15s, background-color .15s;

  /* right-side arrow + small medal dot on left */
  background-image:
    linear-gradient(45deg, transparent 50%, #64748b 50%),
    linear-gradient(135deg, #64748b 50%, transparent 50%),
    radial-gradient(circle at 12px 50%, #f59e0b 0, #f59e0b 3px, transparent 4px);
  background-position:
    calc(100% - 16px) 50%,
    calc(100% - 10px) 50%,
    12px 50%;
  background-size:5px 5px, 5px 5px, 10px 10px;
  background-repeat:no-repeat;
}

/* placeholder / neutral state for value "0" */
.std-tile[data-pos="0"] .std-pos-select{
  color:#64748b;
  background-color:#eef2ff;
}

/* color hints when 1st/2nd/3rd */
.std-tile[data-pos="1"] .std-pos-select{
  border-color:#f59e0b;
  background-color:#fffbeb;
}
.std-tile[data-pos="2"] .std-pos-select{
  border-color:#9ca3af;
  background-color:#f9fafb;
}
.std-tile[data-pos="3"] .std-pos-select{
  border-color:#b45309;
  background-color:#fef3c7;
}

/* focus / hover */
.std-pos-select:focus{
  outline:none;
  border-color:#6366f1;
  background-color:#eff6ff;
  box-shadow:0 0 0 2px rgba(99,102,241,0.18);
}
.std-pos-select:hover{
  border-color:#94a3b8;
}
.std-pos-select option{ font-size:11px; }

/* big, easy-to-tap delete button */
.std-pos-clear{
  flex:0 0 38px;
  width:38px;
  height:38px;
  border-radius:999px !important;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:0;
}
.std-pos-clear i{
  font-size:16px;
}

/* Print: show badge, hide controls row completely */
@media print {
  .std-pos-badge{
    top:0;
    right:0;
    padding:0 2px;
    font-size:5px;
    box-shadow:none;
  }
  .std-pos-badge i{ font-size:5px; }

  .std-pos-control{
    display:none !important;
  }
}

/* ---------- Header / layout ---------- */

.event-head-left{
  display:flex;
  flex-direction:column;
}
.event-subline{
  font-size:11px;
  color:#6b7280;
  margin-top:2px;
}

.report-wrap{
  display:flex;
  flex-direction:column;
  gap:16px;
}

/* Event card */

.event-card{
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#fff;
  overflow:hidden;
  box-shadow:0 2px 10px rgba(0,0,0,.03);
}
.event-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:12px 16px;
  border-bottom:1px solid #eef1f5;
  background:#fafafa;
  cursor:pointer; /* expand/collapse */
}
.event-title{
  font-size:16px;
  font-weight:700;
  margin:0;
}
.event-meta{
  display:flex;
  gap:10px;
  color:#6b7280;
  font-size:12px;
  flex-wrap:wrap;
  align-items:center;
}
.pill{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:4px 10px;
  border:1px solid #e5e7eb;
  border-radius:999px;
  background:#fff;
  font-size:12px;
}
.event-body{
  padding:12px 16px;
}

/* expand/collapse */
.event-card.collapsed .event-body{ display:none; }
.event-toggle-icon{
  font-size:11px;
  color:#64748b;
}

/* ---------- Houses / teams layout (screen) ---------- */

.houses-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
  gap:14px;
}
.house-card{
  border:1px solid #eef1f5;
  border-radius:12px;
  overflow:hidden;
  background:#fff;
}
.house-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:8px 10px;
  background:#fcfcfd;
  border-bottom:1px solid #eef1f5;
}
.house-left{
  display:flex;
  align-items:center;
  gap:8px;
}
.dot{
  width:12px;
  height:12px;
  border-radius:50%;
  border:1px solid rgba(0,0,0,.08);
}
.house-count{
  font-size:12px;
  color:#6b7280;
}

/* Student grid on screen */
.house-members,
.team-members{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(150px,1fr));
  gap:10px;
  padding:8px;
}

/* Student tile */

.std-tile{
  border:1px solid #dcdfe3;
  border-radius:8px;
  background:#fff;
  padding:8px 6px;
  text-align:center;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:4px;
  box-shadow:0 1px 3px rgba(0,0,0,.04);
}

.std-tile .std-photo{
  width:60px;
  height:60px;
  border-radius:50%;
  overflow:hidden;
  background:#f3f4f6;
  margin-bottom:4px;
  border:2px solid #e5e7eb;
}
.std-tile .std-photo img{
  width:100%; height:100%;
  object-fit:cover; display:block;
}

/* reserve 2 lines for name so selects line up */
.std-line-1{
  font-weight:600;
  font-size:11px;
  line-height:1.2;
  text-align:center;
  min-height:2.4em;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}
.std-line-2{
  font-size:10px;
  color:#6b7280;
  letter-spacing:.1px;
  text-align:center;
}

/* Teams */

.teams-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
  gap:14px;
}
.team-card{
  border:1px solid #eef1f5;
  border-radius:12px;
  overflow:hidden;
  background:#fff;
}
.team-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:8px 10px;
  background:#fcfcfd;
  border-bottom:1px solid #eef1f5;
}
.team-left{
  display:flex;
  align-items:center;
  gap:8px;
}
.captain-badge{
  color:#2563eb;
  font-weight:600;
}
.total-line{
  margin-top:8px;
  font-weight:700;
  color:#111827;
}

/* ---------- MOBILE TWEAKS (full width cards) ---------- */

@media (max-width: 575.98px){
  .houses-grid{
    grid-template-columns:repeat(1,minmax(0,1fr));
  }
  .house-members,
  .team-members{
    grid-template-columns:repeat(1,minmax(0,1fr));
    gap:8px;
  }
  .std-tile{
    padding:10px 8px;
  }
  .std-tile .std-photo{
    width:70px;
    height:70px;
  }
  .std-pos-clear{
    width:40px;
    height:40px;
  }
}

/* ================== PRINT STYLES (A4) ================== */

@page {
  size: A4 portrait;
  margin: 8mm;
}

@media print {

  html, body {
    width: 100%;
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
  }

  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .navbar, .main-footer, .breadcrumb, .card-header, .sidebar {
    display: none !important;
  }

  .content, .content-wrapper {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
  }

  .event-card {
    width: 100%;
    border: 1px solid #bbb;
    border-radius: 8px;
    box-shadow: none !important;
    margin-bottom: 8px !important;
    padding: 4px !important;
    break-inside: avoid;
  }

  .event-head {
    padding: 4px 6px !important;
  }

  .event-title {
    font-size: 12px !important;
    margin: 0 !important;
  }

  .event-meta .pill {
    padding: 1px 4px !important;
    font-size: 8px !important;
  }

  /* 4 house cards per row on print */
  .houses-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 4px !important;
  }

  .house-card {
    border: 1px solid #ccc !important;
    padding: 3px !important;
    border-radius: 6px !important;
    box-shadow: none !important;
    background: #fff !important;
  }

  .house-head {
    padding: 2px 4px !important;
    margin-bottom: 2px !important;
  }

  .house-count {
    font-size: 8px !important;
  }

  /* students: 2 cards per house (=8 per row total) */
  .house-members,
  .team-members {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 3px !important;
    padding: 3px !important;
  }

  .std-tile{
    padding:3px !important;
    border-radius:4px !important;
    border:1px solid #ccc !important;
    box-shadow:none !important;
  }

  .std-photo {
    width: 26px !important;
    height: 26px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    border: 1px solid #aaa !important;
    margin-bottom: 2px !important;
  }

  .std-line-1 {
    font-size: 7px !important;
    line-height: 1.1 !important;
  }

  .std-line-2 {
    font-size: 6px !important;
    line-height: 1.1 !important;
  }

  .total-line {
    font-size: 9px !important;
    margin-top: 4px !important;
  }
}
</style>

<section class="content">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between">
        <strong>Report</strong>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-sm btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div id="report" class="report-wrap"></div>
      <div id="empty" class="text-center text-muted" style="display:none;border:1px dashed #cbd5e1;border-radius:12px;padding:16px;">
        No data.
      </div>
    </div>
  </div>
</section>

<script>
/* --- CSRF + URLs --- */
const CSRF_NAME   = '<?= csrf_token() ?>';
const CSRF_HASH   = '<?= csrf_hash() ?>';
const URL_DATA    = "<?= base_url('admin/sports/reports/events/data') ?>";
const URL_SET_POS = "<?= base_url('admin/sports/results/set-position') ?>";
const URL_CLR_POS = "<?= base_url('admin/sports/results/clear-position') ?>";

/* --- helpers --- */
function esc(s){return String(s??'').replace(/[&<>"'`=\/]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'}[c]))}
function cap(s){s=(s||'').toLowerCase();return s? s[0].toUpperCase()+s.slice(1):''}
function iconGender(g){g=(g||'').toLowerCase();if(g==='male')return '<i class="fas fa-mars" title="Male"></i>';if(g==='female')return '<i class="fas fa-venus" title="Female"></i>';return '<i class="fas fa-venus-mars" title="Mixed"></i>'}
function colorSafe(c){if(!c)return'#e5e7eb';c=String(c).trim();if(!c.startsWith('#'))c='#'+c;return c}

/* age */
function roundedAgeYears(dobStr){
  if(!dobStr) return '';
  const dob = new Date(dobStr);
  if (isNaN(dob)) return '';
  const today = new Date();

  let years = today.getFullYear() - dob.getFullYear();
  const mDiff = today.getMonth() - dob.getMonth();
  const dDiff = today.getDate() - dob.getDate();
  const beforeBirthday = (mDiff < 0) || (mDiff === 0 && dDiff < 0);
  if (beforeBirthday) years -= 1;

  const lastBDYear = today.getFullYear() - (beforeBirthday ? 1 : 0);
  const lastBDay = new Date(lastBDYear, dob.getMonth(), dob.getDate());
  let months = (today.getFullYear() - lastBDay.getFullYear())*12 + (today.getMonth() - lastBDay.getMonth());
  if (today.getDate() < lastBDay.getDate()) months -= 1;

  if (months >= 6) years += 1;
  if (years < 0) years = 0;
  return years + ' Yr';
}

/* Class-Section short like "PG-A" */
function classSectionShort(m){
  const cShort = (m.class_short_name || m.class_short || m.class_name || '').toString().trim();
  const sName  = (m.section_name || m.section_short || '').toString().trim();
  const s1 = sName ? sName.charAt(0).toUpperCase() : '';
  return (cShort && s1) ? `${esc(cShort)}-${esc(s1)}` : esc(cShort || s1);
}

/* Student photo URL (fallback avatar) */
function photoUrl(profile){
  const p = (profile||'').toString().trim();
  if (!p) return '<?= base_url('resource/img/avatar-student.png') ?>';
  return '<?= base_url('uploads') ?>/' + p.replace(/^\/+/, '');
}

/* Position label */
function positionLabel(posNum){
  switch (posNum) {
    case 1: return '1st';
    case 2: return '2nd';
    case 3: return '3rd';
    default: return '';
  }
}

/* Student TILE (used for both individual & team) */
function studentTile(m, showCaptain, eventId){
  const full = esc(((m.first_name||'')+' '+(m.last_name||'')).trim() || ('ID '+(m.student_id||'')));
  const age  = roundedAgeYears(m.date_of_birth||'');
  const clsS = classSectionShort(m);
  const img  = photoUrl(m.profile_photo||'');

  const capBadge = (showCaptain && m.is_captain)
    ? ' <span class="captain-badge">(C)</span>'
    : '';

  const posNum   = parseInt(m.position ?? m.result_position ?? 0, 10) || 0;
  const posText  = positionLabel(posNum);

  let posHtml = '';
  if (posNum > 0) {
    let posClass = 'pos-bronze';
    if (posNum === 1) posClass = 'pos-gold';
    else if (posNum === 2) posClass = 'pos-silver';

    posHtml = `
      <div class="std-pos-badge ${posClass}">
        <i class="fas fa-medal"></i> ${posText}
      </div>
    `;
  }

  return `
    <div class="std-tile"
         data-event-id="${eventId||0}"
         data-student-id="${m.student_id}"
         data-pos="${posNum}">
      ${posHtml}
      <div class="std-photo">
        <img src="${img}" alt="${full}" onerror="this.src='<?= base_url('resource/img/avatar-student.png') ?>'">
      </div>
      <div class="std-line-1">${full}${capBadge}</div>
      <div class="std-line-2">
        ${age ? esc(age) + ' ' : ''}${clsS || ''}
      </div>
      <div class="std-pos-control">
        <select class="std-pos-select">
          <option value="0"${posNum===0?' selected':''}>Set position</option>
          <option value="1"${posNum===1?' selected':''}>1st</option>
          <option value="2"${posNum===2?' selected':''}>2nd</option>
          <option value="3"${posNum===3?' selected':''}>3rd</option>
        </select>
        <button type="button"
                class="btn btn-xs btn-outline-secondary std-pos-clear"
                title="Clear Position">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    </div>
  `;
}

/* ---- INDIVIDUAL event renderer ---- */
function renderIndividualEvent(ev){
  const houses = Array.isArray(ev.houses) ? ev.houses : [];
  const cards = houses.map(h => {
    const total = (typeof h.total === 'number')
      ? h.total
      : (Array.isArray(h.students) ? h.students.length : 0);

    const tiles = (Array.isArray(h.students) ? h.students : [])
      .map(s => studentTile(s,false, ev.event_id))
      .join('');

    return `
      <div class="house-card">
        <div class="house-head">
          <div class="house-left">
            <span class="dot" style="background:${colorSafe(h.color_code)}"></span>
            <strong>${esc(h.house_name||('House #'+(h.house_id||'')))}</strong>
          </div>
          <div class="house-count">
            <i class="fas fa-user"></i> ${total}
          </div>
        </div>
        <div class="house-members">
          ${tiles || '<div class="text-muted">No participants.</div>'}
        </div>
      </div>
    `;
  }).join('');

  const overall = (typeof ev.event_total === 'number')
    ? ev.event_total
    : houses.reduce((a,h)=>a+((typeof h.total==='number')?h.total:(h.students?.length||0)),0);

  const participants = typeof ev.participants_total === 'number'
    ? ev.participants_total
    : overall;

  return `
    <div class="event-card collapsed" data-event-id="${ev.event_id}">
      <div class="event-head">
        <div class="event-head-left">
          <h3 class="event-title">${esc(ev.event_name)}</h3>
          <div class="event-meta">
            <span class="pill">
              <i class="far fa-calendar-alt"></i>
              ${esc(ev.event_date || '')}
            </span>
            ${ev.event_time ? `
            <span class="pill">
              <i class="far fa-clock"></i> ${esc(ev.event_time)}
            </span>` : ''}
            <span class="pill">
              <i class="fas fa-users"></i> ${participants}
            </span>
            <span class="pill">
              <i class="fas fa-tag"></i> ${cap(ev.event_type)}
            </span>
            <span class="pill">
              ${iconGender(ev.gender)}
            </span>
          </div>
        </div>
        <div class="event-toggle-icon">
          <i class="fas fa-chevron-down"></i>
        </div>
      </div>
      <div class="event-body">
        <div class="houses-grid">
          ${cards || '<div class="text-muted">No participants yet.</div>'}
        </div>
      </div>
    </div>
  `;
}

/* ---- TEAM event renderer ---- */
function renderTeamEvent(ev){
  const teams = Array.isArray(ev.teams) ? ev.teams : [];
  const teamCards = teams.map(t => {
    const members = Array.isArray(t.members)
      ? t.members.map(m => studentTile(m,true, ev.event_id)).join('')
      : '<div class="text-muted p-2">No members.</div>';

    return `
      <div class="team-card">
        <div class="team-head">
          <div class="team-left">
            <span class="dot" style="background:${colorSafe(t.color_code)}"></span>
            <strong>${esc(t.team_name||('Team #'+(t.team_id||'')))}</strong>
          </div>
          <div class="house-count">${esc(t.house_name || '')}</div>
        </div>
        <div class="team-members">${members}</div>
      </div>
    `;
  }).join('');

  const totals = {
    teams: (typeof ev.teams_count === 'number') ? ev.teams_count : teams.length,
    members: (typeof ev.members_count === 'number')
      ? ev.members_count
      : teams.reduce((a,t)=>a+(t.members?.length||0),0)
  };

  const participants = typeof ev.participants_total === 'number'
    ? ev.participants_total
    : totals.members;

  return `
    <div class="event-card collapsed" data-event-id="${ev.event_id}">
      <div class="event-head">
        <div class="event-head-left">
          <h3 class="event-title">${esc(ev.event_name)}</h3>
          <div class="event-meta">
            <span class="pill">
              <i class="far fa-calendar-alt"></i>
              ${esc(ev.event_date || '')}
            </span>
            ${ev.event_time ? `
            <span class="pill">
              <i class="far fa-clock"></i> ${esc(ev.event_time)}
            </span>` : ''}
            <span class="pill">
              <i class="fas fa-users"></i> ${participants}
            </span>
            <span class="pill">
              <i class="fas fa-tag"></i> ${cap(ev.event_type)}
            </span>
            <span class="pill">
              ${iconGender(ev.gender)}
            </span>
          </div>
        </div>
        <div class="event-toggle-icon">
          <i class="fas fa-chevron-down"></i>
        </div>
      </div>
      <div class="event-body">
        <div class="teams-grid">
          ${teamCards || '<div class="text-muted">No teams.</div>'}
        </div>
      </div>
    </div>
  `;
}

/* ---- Render + Fetch ---- */
function renderReport(data){
  const wrap = document.getElementById('report');
  const empty = document.getElementById('empty');
  wrap.innerHTML = '';
  if(!data || !data.length){ empty.style.display=''; return; }
  empty.style.display='none';

  const frag = document.createDocumentFragment();
  data.forEach(ev => {
    const html = (String(ev.event_type).toLowerCase()==='team')
      ? renderTeamEvent(ev)
      : renderIndividualEvent(ev);
    const holder = document.createElement('div');
    holder.innerHTML = html;
    frag.appendChild(holder.firstElementChild);
  });
  wrap.appendChild(frag);

  const firstCard = wrap.querySelector('.event-card');
  if (firstCard) firstCard.classList.remove('collapsed');
}

function loadReport(){
  fetch(URL_DATA, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body:`${encodeURIComponent(CSRF_NAME)}=${encodeURIComponent(CSRF_HASH)}`
  })
  .then(r => r.json())
  .then(resp => renderReport(resp.data || []))
  .catch(()=>{ document.getElementById('empty').style.display=''; });
}

/* --- Position save / clear --- */
function savePosition(eventId, studentId, position){
  if(!eventId || !studentId) return;
  const body = new URLSearchParams();
  body.append(CSRF_NAME, CSRF_HASH);
  body.append('event_id', eventId);
  body.append('student_id', studentId);
  body.append('position', position);
  body.append('rank_shared', '0');
  body.append('unit_type', 'individual');

  return fetch(URL_SET_POS, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body: body.toString()
  }).then(r => r.json());
}

function clearPosition(eventId, studentId){
  if(!eventId || !studentId) return;
  const body = new URLSearchParams();
  body.append(CSRF_NAME, CSRF_HASH);
  body.append('event_id', eventId);
  body.append('student_id', studentId);
  body.append('unit_type', 'individual');

  return fetch(URL_CLR_POS, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body: body.toString()
  }).then(r => r.json());
}

document.addEventListener('DOMContentLoaded', function(){
  loadReport();

  // expand / collapse events
  document.addEventListener('click', function(e){
    const head = e.target.closest('.event-head');
    if (!head) return;
    const card = head.closest('.event-card');
    if (card) card.classList.toggle('collapsed');
  });

  // position select
  document.addEventListener('change', function(e){
    if (!e.target.classList.contains('std-pos-select')) return;
    const tile = e.target.closest('.std-tile');
    if (!tile) return;

    const eventId   = parseInt(tile.dataset.eventId || '0', 10);
    const studentId = parseInt(tile.dataset.studentId || '0', 10);
    const pos       = parseInt(e.target.value || '0', 10);

    if (pos === 0) {
      const clearBtn = tile.querySelector('.std-pos-clear');
      if (clearBtn) clearBtn.click();
      return;
    }

    savePosition(eventId, studentId, pos).then(res=>{
      tile.setAttribute('data-pos', String(pos));
      loadReport(); // refresh badges & counts
    }).catch(()=>{});
  });

  // clear button
  // clear button
document.addEventListener('click', function(e){
  // make whole circle + icon clickable
  const btn = e.target.closest('.std-pos-clear');
  if (!btn) return;

  const tile = btn.closest('.std-tile');
  if (!tile) return;

  const eventId   = parseInt(tile.dataset.eventId || '0', 10);
  const studentId = parseInt(tile.dataset.studentId || '0', 10);

  clearPosition(eventId, studentId).then(res=>{
    const selectEl = tile.querySelector('.std-pos-select');
    if (selectEl) selectEl.value = '0';
    tile.setAttribute('data-pos', '0');
    loadReport();
  }).catch(()=>{});
});

});
</script>

<?= $this->endSection() ?>
