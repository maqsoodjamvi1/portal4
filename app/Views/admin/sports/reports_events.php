<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Events & Participants Report',
    'icon' => 'fas fa-clipboard-list',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Reports', 'active' => true],
    ],
]) ?>

<style>
/* ================== NAME & LINES ================== */

/* Student name: max 2 lines, wraps nicely, fixed height */
.std-line-1 {
  font-weight: 600;
  font-size: 0.95rem;
  line-height: 1.1rem;

  white-space: normal;
  word-wrap: break-word;
  word-break: break-word;

  display: -webkit-box;
  -webkit-line-clamp: 2;      /* max 2 lines */
  -webkit-box-orient: vertical;

  min-height: 2.2rem;         /* 2 × line-height */
  overflow: hidden;           /* hide after 2 lines */
  color: #000;
}

/* Age + class line (single line) */
.std-line-2 {
  font-size: 10px;
  line-height: 1.1;
  height: 16px;               /* fixed height */
  overflow: hidden;
  color: #000;
  letter-spacing: .1px;
}

/* Events line (inside tile) */
.std-line-3 {
  height: 70px;               /* fixed events area height */
  overflow-y: auto;
  width: 100%;
  text-align: left;
  padding-left: 4px;
  font-size: 9px;
  line-height: 1.2;
  white-space: nowrap;
  color: #000;
}

/* Position badge – top right of student card */
.std-pos-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  padding: 1px 4px;
  border-radius: 999px;
  font-size: 8px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  gap: 2px;
  color: #fff;
  box-shadow: 0 1px 2px rgba(0,0,0,.25);
}
.std-pos-badge i {
  font-size: 8px;
}

/* Colors for 1st / 2nd / 3rd */
.pos-gold   { background:#f59e0b; }
.pos-silver { background:#9ca3af; }
.pos-bronze { background:#b45309; }

/* ================== LAYOUT & EVENT CARDS ================== */

.event-head-left {
  display: flex;
  flex-direction: column;
}

.event-subline {
  font-size: 11px;
  color: #6b7280;
  margin-top: 2px;
}

.report-wrap {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.report-wrap.grid-mode {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); /* cards for ~2 students */
  gap: 16px;
}

/* Event card */
.event-card {
  border: 1px solid #000;
  border-radius: 14px;
  background: #fff;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,.03);
}

.event-title {
  font-size: 16px;
  font-weight: 700;
  margin: 0;
}

.event-meta {
  display: flex;
  gap: 10px;
  color: #6b7280;
  font-size: 12px;
  flex-wrap: wrap;
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  background: #fff;
  font-size: 12px;
}

.event-body {
  padding: 12px 16px;
}

/* Event type pills */
.pill-type-individual {
  background: #dcfce7;
  border-color: #bbf7d0;
  color: #166534;
}
.pill-type-team {
  background: #e0f2fe;
  border-color: #bfdbfe;
  color: #1d4ed8;
}

.pill-event-running {
  background:#fee2e2;
  border-color:#fecaca;
  color:#b91c1c;
}
.pill-event-jump {
  background:#fef3c7;
  border-color:#fde68a;
  color:#92400e;
}
.pill-event-throw {
  background:#e0f2fe;
  border-color:#bfdbfe;
  color:#1d4ed8;
}
.pill-event-fun {
  background:#ecfdf5;
  border-color:#bbf7d0;
  color:#047857;
}
.pill-event-other {
  background:#f3f4f6;
  border-color:#e5e7eb;
  color:#374151;
}

/* ================== HOUSES & STUDENT TILES ================== */

.houses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 14px;
}

.house-card {
  border: 1px solid #eef1f5;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
}

.house-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  background: #fcfcfd;
  border-bottom: 1px solid #eef1f5;
}

.house-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 1px solid rgba(0,0,0,.08);
}

.house-count {
  font-size: 12px;
  color: #6b7280;
}

.house-members,
.team-members {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 6px;
}

/* Student tile: 2 per row */
.std-tile {
  flex: 0 0 calc(50% - 8px);
  max-width: calc(50% - 8px);
  border: 1px solid #000;
  border-radius: 8px;
  background: #fff;
  padding: 8px 6px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  gap: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
  overflow: hidden;
  color: #000;
  position: relative;
}

.std-tile-with-events {
  min-height: 180px;
}

.std-tile-no-events {
  min-height: auto;
}

/* Photo inside tile */
.std-tile .std-photo {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  overflow: hidden;
  background: #f3f4f6;
  margin-bottom: 4px;
  border: 2px solid #000;
}
.std-tile .std-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* ================== TEAMS & TOTALS ================== */

.teams-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 14px;
}

.team-card {
  border: 1px solid #000;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
}

.team-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  background: #fcfcfd;
  border-bottom: 1px solid #eef1f5;
}

.team-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.captain-badge {
  color: #2563eb;
  font-weight: 600;
}

.total-line {
  margin-top: 8px;
  font-weight: 700;
  color: #111827;
}

/* ================== PRINT (A4) ================== */

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
    width: 100% !important;
    border: 1px solid #bbb;
    border-radius: 8px;
    box-shadow: none !important;
    margin-bottom: 8px !important;
    padding: 4px !important;
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .event-head {
    padding: 4px 6px !important;
  }

  .event-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: #000;
  }

  .pill,
  .pill-event-running,
  .pill-event-jump,
  .pill-event-throw,
  .pill-event-fun,
  .pill-event-other {
    background: #fff !important;
    border: 1px solid #000 !important;
    color: #000 !important;
  }

  .event-meta .pill {
    padding: 1px 4px !important;
    font-size: 8px !important;
  }

  /* Default (ALL houses) – 4 house cards per row inside each event */
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
    font-size: 12px;
    color: #000;
  }

  .std-photo {
    width: 26px !important;
    height: 26px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    border: 1px solid #aaa !important;
    margin-bottom: 2px !important;
  }

  .std-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .std-line-1 {
    font-size: 7px !important;
    line-height: 1.1 !important;
    min-height: unset !important;  /* let print pack tighter */
  }

  .std-line-2 {
    font-size: 6px !important;
    line-height: 1.1 !important;
    height: auto !important;
  }

  .std-pos-badge {
    top: 0;
    right: 0;
    padding: 0 2px;
    font-size: 5px;
    box-shadow: none;
  }
  .std-pos-badge i {
    font-size: 5px;
  }

  .total-line {
    margin-top: 8px;
    font-weight: 700;
    color: #000;
  }

  /* ===========================
     SPECIAL: single house selected
     (we add .grid-mode in JS)
     -> 4 EVENTS per row on A4
     =========================== */

  /* 4 event-cards per row */
  .report-wrap.grid-mode {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 6px !important;
  }

  /* event card fits its grid cell */
  .report-wrap.grid-mode .event-card {
    width: 100% !important;
    margin-bottom: 4px !important;
  }

  /* inside those cards, only 1 house column
     (since you filtered to a single house) */
  .report-wrap.grid-mode .houses-grid {
    grid-template-columns: 1fr !important;
  }

  .std-delete-btn {
    display: none !important;
  }
}

.event-head-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.event-head-row .event-title-inline {
  font-weight: 700;
  font-size: 14px;
}

.event-head-row span {
  white-space: nowrap;
}


.std-delete-btn {
  position: absolute;
  top: 2px;
  left: 2px;
  border: none;
  background: transparent;
  padding: 0;
  color: #ef4444;
  cursor: pointer;
  line-height: 1;
}

.std-delete-btn i {
  font-size: 10px;
}

.std-delete-btn:hover {
  transform: scale(1.1);
}

/* Hide delete icon while printing */
@media print {
  
}
</style>


<section class="content">
  <div class="card card-primary card-outline">
    <div class="card-header">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <strong>Report</strong>
        <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0">

          <div class="form-check me-3">
            <input class="form-check-input" type="checkbox" id="filterShowPhoto" checked>
            <label class="form-check-label" for="filterShowPhoto">
              Show profile photo
            </label>
          </div>

          <div class="form-check me-3">
            <input class="form-check-input" type="checkbox" id="filterShowEvents">
            <label class="form-check-label" for="filterShowEvents">
              Show participation events
            </label>
          </div>

          <button class="btn btn-sm btn-secondary ms-md-2 mt-2 mt-md-0" onclick="window.print()">
            <i class="fas fa-print"></i> Print
          </button>
        </div>
      </div>
    </div>
<select id="house_id" class="form-control">
    <option value="">-- Select House --</option>
    <option value="0">Select ALL</option>
    <?php foreach (($houses ?? []) as $h): ?>
      <option value="<?= (int)$h['house_id'] ?>"><?= esc($h['house_name']) ?></option>
    <?php endforeach; ?>
</select>
    <div class="card-body">
      <div id="report" class="report-wrap"></div>
      <div id="empty" class="text-center text-muted"
           style="display:none;border:1px dashed #cbd5e1;border-radius:12px;padding:16px;">
        No data.
      </div>
    </div>
  </div>
</section>

<script>
/* --- CSRF --- */
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

/* filter flags */
let SHOW_PHOTO  = true;
let SHOW_EVENTS = false;

/* NEW: currently selected house (0 = ALL) */
let CURRENT_HOUSE_ID = 0;



/* cached data and global stats */
let REPORT_DATA          = [];
let STUDENT_EVENT_COUNT  = {}; // sid => count
let STUDENT_EVENT_NAMES  = {}; // sid => [names...]

/* --- helpers --- */
function esc(s){
  return String(s ?? '').replace(/[&<>"'`=\/]/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;",
    '/':'&#x2F;','`':'&#x60;','=':'&#x3D;'
  }[c]));
}
function cap(s){
  s = (s||'').toLowerCase();
  return s ? s[0].toUpperCase() + s.slice(1) : '';
}
function iconGender(g){
  g = (g||'').toLowerCase();
  if(g === 'male')   return '<i class="fas fa-mars" title="Male"></i>';
  if(g === 'female') return '<i class="fas fa-venus" title="Female"></i>';
  return '<i class="fas fa-venus-mars" title="Mixed"></i>';
}
function colorSafe(c){
  if(!c) return '#e5e7eb';
  c = String(c).trim();
  if(!c.startsWith('#')) c = '#'+c;
  return c;
}

/* JS version of ageRoundedYears() */
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

function positionLabel(posNum){
  switch (posNum) {
    case 1: return '1st';
    case 2: return '2nd';
    case 3: return '3rd';
    default: return '';
  }
}

/* ======= Build per-student participation stats from REPORT_DATA ======= */
/* For each student_id:
   - COUNT distinct event_name
   - COLLECT list of event_name
*/
function buildStudentStats(events){
  STUDENT_EVENT_COUNT = {};
  STUDENT_EVENT_NAMES = {};

  if (!Array.isArray(events)) return;

  events.forEach(ev => {
    const evName = ev.event_name || '';
    if (!evName) return;

    const type = String(ev.event_type || '').toLowerCase();

    if (type === 'team') {
      (ev.teams || []).forEach(team => {
        (team.members || []).forEach(m => {
          const sid = parseInt(m.student_id || 0, 10);
          if (!sid) return;
          if (!STUDENT_EVENT_NAMES[sid]) {
            STUDENT_EVENT_NAMES[sid] = new Set();
          }
          STUDENT_EVENT_NAMES[sid].add(evName);
        });
      });
    } else {
      (ev.houses || []).forEach(h => {
        (h.students || []).forEach(m => {
          const sid = parseInt(m.student_id || 0, 10);
          if (!sid) return;
          if (!STUDENT_EVENT_NAMES[sid]) {
            STUDENT_EVENT_NAMES[sid] = new Set();
          }
          STUDENT_EVENT_NAMES[sid].add(evName);
        });
      });
    }
  });

  Object.keys(STUDENT_EVENT_NAMES).forEach(k => {
    const arr = Array.from(STUDENT_EVENT_NAMES[k]);
    STUDENT_EVENT_NAMES[k] = arr;
    STUDENT_EVENT_COUNT[k] = arr.length;
  });
}

/* Student TILE (used for both individual & team) */
function studentTile(m, showCaptain=false){
  const sid   = parseInt(m.student_id || 0, 10);
  const full  = esc(((m.first_name||'')+' '+(m.last_name||'')).trim() || ('ID '+(m.student_id||'')));
  const ageRaw = roundedAgeYears(m.date_of_birth||'');   // "7 Yr"
  const age    = ageRaw ? ageRaw.replace(' ', '-') : ''; // "7-Yr"
  const clsS   = classSectionShort(m);
  const img    = photoUrl(m.profile_photo||'');

  const capBadge = (showCaptain && m.is_captain)
    ? ' <span class="captain-badge">(C)</span>'
    : '';

  const posNum   = parseInt(m.position ?? m.result_position ?? 0, 10);
  const posText  = positionLabel(posNum);

  let posHtml = '';
  if (posText) {
    let posClass = 'pos-bronze';
    if (posNum === 1) posClass = 'pos-gold';
    else if (posNum === 2) posClass = 'pos-silver';

    posHtml = `
      <div class="std-pos-badge ${posClass}">
        <i class="fas fa-medal"></i> ${posText}
      </div>
    `;
  }

  const pCount    = (sid && STUDENT_EVENT_COUNT[sid]) ? STUDENT_EVENT_COUNT[sid] : 0;
  const countText = pCount > 0 ? ` (${pCount})` : '';
  const evNamesArr = (sid && STUDENT_EVENT_NAMES[sid])
    ? STUDENT_EVENT_NAMES[sid]
    : [];

  let eventsHtml = '';
  if (SHOW_EVENTS && evNamesArr.length) {
    let lines = evNamesArr.map((ev, i) => `${i+1}) ${ev}`).join('<br>');
    eventsHtml = `<div class="std-line-3">${lines}</div>`;
  }

  const photoHtml = SHOW_PHOTO ? `
      <div class="std-photo">
        <img src="${img}" alt="${full}" onerror="this.src='<?= base_url('resource/img/avatar-student.png') ?>'">
      </div>
    ` : '';

  // NEW: delete button (recycle bin icon)
  const deleteBtnHtml = sid ? `
      <button type="button"
              class="std-delete-btn"
              onclick="deleteStudentAllEvents(${sid})"
              title="Remove this student from all events in this session">
        <i class="fas fa-trash-alt"></i>
      </button>
    ` : '';

  // class changes depending on SHOW_EVENTS
  const tileClass = SHOW_EVENTS
    ? 'std-tile std-tile-with-events'
    : 'std-tile std-tile-no-events';

  return `
    <div class="${tileClass}">
      ${posHtml}
      ${deleteBtnHtml}
      ${photoHtml}
      <div class="std-line-1">${full}${capBadge}</div>
      <div class="std-line-2">
        ${age ? esc(age) + ' ' : ''}${clsS || ''}${countText}
      </div>
      ${eventsHtml}
    </div>
  `;
}

/* ---- INDIVIDUAL event renderer ---- */

/* ---- TEAM event renderer ---- */
function renderTeamEvent(ev, houseFilter){
  const cat   = eventCategory(ev.event_name); // not used in header now
  let teams   = Array.isArray(ev.teams) ? ev.teams : [];

  const genderText = genderLabel(ev.gender);
  const ageText    = ageRangeLabel(ev);

  // 🔹 Filter teams by selected house (if > 0)
  if (houseFilter && houseFilter > 0) {
    teams = teams.filter(t => parseInt(t.house_id || 0, 10) === houseFilter);
  }

  // No teams for this house => skip event
  if (!teams.length) {
    return '';
  }

  const teamCards = teams.map(t => {
    const membersHtml = Array.isArray(t.members)
      ? t.members.map(m => studentTile(m, true)).join('')
      : '<div class="text-muted p-2">No members.</div>';

    return `
      <div class="team-card">
        <div class="team-head">
          <div class="team-left">
            <span class="dot" style="background:${colorSafe(t.color_code)}"></span>
            <strong>${esc(t.team_name || ('Team #'+(t.team_id || '')))}</strong>
          </div>
          <div class="house-count">
            ${esc(t.house_name || '')}
          </div>
        </div>
        <div class="team-members">
          ${membersHtml}
        </div>
      </div>
    `;
  }).join('');

  const totals = {
    teams: teams.length,
    members: teams.reduce((a, t) => a + (Array.isArray(t.members) ? t.members.length : 0), 0)
  };

  const participants = totals.members;

  const genderAgePart = `(${genderText}) ${ageText || ''}`.trim();

  return `
    <div class="event-card">
      <div class="event-head">
        <div class="event-head-left">
          <div class="event-head-row">
            <span class="event-title-inline">${esc(ev.event_name)}</span>
            ${genderAgePart ? `<span>${esc(genderAgePart)}</span>` : ''}
            ${ev.event_time ? `<span>${esc(ev.event_time)}</span>` : ''}
            <span>${participants}</span>
          </div>
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
  REPORT_DATA = Array.isArray(data) ? data : [];

  const wrap  = document.getElementById('report');
  const empty = document.getElementById('empty');
  wrap.innerHTML = '';

  if(!REPORT_DATA.length){
    empty.style.display = '';
    // make sure no grid-mode if nothing
    wrap.classList.remove('grid-mode');
    return;
  }
  empty.style.display = 'none';

  // 🔹 switch layout depending on filter
  if (CURRENT_HOUSE_ID && CURRENT_HOUSE_ID > 0) {
    wrap.classList.add('grid-mode');      // compact grid, many event cards per row
  } else {
    wrap.classList.remove('grid-mode');   // full-width events (all houses)
  }

  // Build per-student participation stats from all events
  buildStudentStats(REPORT_DATA);

  const frag = document.createDocumentFragment();
  REPORT_DATA.forEach(ev => {
    const html = (String(ev.event_type).toLowerCase()==='team')
      ? renderTeamEvent(ev, CURRENT_HOUSE_ID)
      : renderIndividualEvent(ev, CURRENT_HOUSE_ID);

    if (!html) return; // event skipped if no house/team after filter

    const holder = document.createElement('div');
    holder.innerHTML = html;
    frag.appendChild(holder.firstElementChild);
  });
  wrap.appendChild(frag);
}
function loadReport(){
  fetch("<?= base_url('admin/sports/reports/events/data') ?>", {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body:`${encodeURIComponent(CSRF_NAME)}=${encodeURIComponent(CSRF_HASH)}`
  })
  .then(r => r.json())
  .then(resp => renderReport(resp.data || []))
  .catch(() => {
    document.getElementById('empty').style.display = '';
  });
}

document.addEventListener('DOMContentLoaded', function () {
  loadReport();

  const photoChk  = document.getElementById('filterShowPhoto');
  const eventsChk = document.getElementById('filterShowEvents');
  const houseSel  = document.getElementById('house_id'); // 🔹 define here

  if (photoChk) {
    photoChk.addEventListener('change', function () {
      SHOW_PHOTO = !!this.checked;
      renderReport(REPORT_DATA);
    });
  }

  if (eventsChk) {
    eventsChk.addEventListener('change', function () {
      SHOW_EVENTS = !!this.checked;
      renderReport(REPORT_DATA);
    });
  }

  // 🔹 House filter CHANGE handler
  if (houseSel) {
    houseSel.addEventListener('change', function () {
      const val = this.value;
      // '' or '0' => show ALL houses
      if (val === '' || val === '0') {
        CURRENT_HOUSE_ID = 0;
      } else {
        CURRENT_HOUSE_ID = parseInt(val, 10) || 0;
      }

      // Re-render using same REPORT_DATA with new filter
      renderReport(REPORT_DATA);
    });
  }
});


function deleteStudentAllEvents(studentId){
  if (!studentId) return;

  if (!confirm('Remove this student from ALL sports events in this session?')) {
    return;
  }

  const params = new URLSearchParams();
  params.append(CSRF_NAME, CSRF_HASH);
  params.append('student_id', studentId); // NO session_id needed

  fetch("<?= base_url('admin/sports/reports/events/delete-student') ?>", {
    method: 'POST',
    headers: {
      'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'
    },
    body: params.toString()
  })
  .then(r => r.json())
  .then(resp => {
    if (resp && resp.status === 'ok') {
      loadReport(); // Refresh report so student disappears
    } else {
      alert(resp.message || 'Delete failed.');
    }
  })
  .catch(() => {
    alert('Error while deleting student entries.');
  });
}


function eventCategory(evName){
  const name = (evName || '').toString().toLowerCase();

  // Running races
  if (
    name.includes('meter') ||
    name.includes('race') ||
    name.includes('relay') ||
    name.includes('sprint')
  ){
    return { label: 'Running', cls: 'pill-event-running' };
  }

  // Jump events
  if (
    name.includes('long jump') ||
    name.includes('high jump') ||
    name.includes('jump')
  ){
    return { label: 'Jump', cls: 'pill-event-jump' };
  }

  // Throw / field events
  if (
    name.includes('shot put') ||
    name.includes('discus') ||
    name.includes('javelin') ||
    name.includes('throw')
  ){
    return { label: 'Throw', cls: 'pill-event-throw' };
  }

  // Fun / novelty events
  if (
    name.includes('sack') ||
    name.includes('frog') ||
    name.includes('tug of war') ||
    name.includes('fun') ||
    name.includes('ball') ||
    name.includes('spoon')
  ){
    return { label: 'Fun', cls: 'pill-event-fun' };
  }

  // Fallback
  return { label: 'Other', cls: 'pill-event-other' };
}

function genderLabel(g){
  g = (g || '').toString().toLowerCase();
  if (g === 'male')   return 'Boys';
  if (g === 'female') return 'Girls';
  if (g === 'mixed')  return 'Boys & Girls';
  return 'All';
}

function ageRangeLabel(ev){
  // adjust keys according to your actual JSON
  const from = ev.age_from || ev.min_age || ev.age_min || ev.age_from_years;
  const to   = ev.age_to   || ev.max_age || ev.age_max || ev.age_to_years;

  if (from && to) {
    return `${from}–${to} Yr`;
  }
  if (from) {
    return `${from}+ Yr`;
  }
  return 'All';
}

function renderIndividualEvent(ev, houseFilter){
  let houses = Array.isArray(ev.houses) ? ev.houses : [];
  const cat    = eventCategory(ev.event_name); // not used in header now, but you can keep it

  const genderText = genderLabel(ev.gender);   // "Boys", "Girls", ...
  const ageText    = ageRangeLabel(ev);        // "3–6 Yr" etc.

  // 🔹 Filter by selected house (if > 0)
  if (houseFilter && houseFilter > 0) {
    houses = houses.filter(h => parseInt(h.house_id || 0, 10) === houseFilter);
  }

  // If after filtering no houses left, don't render this event
  if (!houses.length) {
    return '';  // caller will skip
  }

  // Build each HOUSE card with student tiles
  const cards = houses.map(h => {
    const total = (typeof h.total === 'number')
      ? h.total
      : (Array.isArray(h.students) ? h.students.length : 0);

    const tiles = (Array.isArray(h.students) ? h.students : [])
      .map(s => studentTile(s, false))
      .join('');

    return `
      <div class="house-card">
        <div class="house-head">
          <div class="house-left">
            <span class="dot" style="background:${colorSafe(h.color_code)}"></span>
            <strong>${esc(h.house_name || ('House #'+(h.house_id || '')))}</strong>
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

  const overall = houses.reduce((a,h) => a + (
      (typeof h.total === 'number')
        ? h.total
        : (Array.isArray(h.students) ? h.students.length : 0)
    ), 0);

  const participants = (typeof ev.participants_total === 'number')
    ? ev.participants_total
    : overall;

  // 🔹 header row: title | (Gender) Age | time | count
  const genderAgePart = `(${genderText}) ${ageText || ''}`.trim();

  return `
    <div class="event-card">
      <div class="event-head">
        <div class="event-head-left">
          <div class="event-head-row">
            <span class="event-title-inline">${esc(ev.event_name)}</span>
            ${genderAgePart ? `<span>${esc(genderAgePart)}</span>` : ''}
            ${ev.event_time ? `<span>${esc(ev.event_time)}</span>` : ''}
            <span>${participants}</span>
          </div>
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
</script>

<?= $this->endSection() ?>
