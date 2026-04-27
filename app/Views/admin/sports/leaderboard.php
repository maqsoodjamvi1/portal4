<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Sports Leaderboard</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports</a></li>
          <li class="breadcrumb-item active">Leaderboard</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<style>
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.section-title{font-weight:700;margin:.5rem 0}
.cards-grid{display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:12px}
@media(min-width:576px){.cards-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:992px){.cards-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
.card-item{display:flex;gap:12px;border:1px solid #e5e7eb;border-radius:14px;padding:12px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.03);position:relative}
.card-photo{width:72px;height:72px;border-radius:10px;object-fit:cover}
.card-main{flex:1;min-width:0}
.card-name{font-weight:700;margin:0;font-size:15px;color:#0f172a}
.card-sub{font-size:12px;color:#334155}
.house-chip{display:inline-flex;align-items:center;gap:6px;font-weight:700;justify-content:center}
.house-dot{display:inline-block;width:10px;height:10px;border-radius:50%}
.badge{background:#eef2f7;border:1px solid #e5e7eb;border-radius:999px;padding:2px 8px}
.rank-badge{position:absolute;right:8px;top:8px;border-radius:999px;padding:3px 10px;border:1px solid #e5e7eb;background:#fff;font-weight:700;font-size:12px;display:flex;align-items:center;gap:6px}
.rank-badge i{opacity:.9}
.house-card{display:flex;gap:12px;align-items:center}
.house-left{width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800}
.house-right{flex:1}
.house-name{font-weight:800;margin:0}
.house-stats{font-size:12px;color:#334155;margin-top:4px;display:flex;gap:8px;flex-wrap:wrap}
.num{font-weight:700}
.tab-switch{display:flex;gap:6px;margin-top:10px}
.tab-switch .btn{padding:6px 10px}
.hint{color:#64748b}
.pills{display:flex;gap:6px;flex-wrap:wrap;margin-top:6px}
.pill{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:2px 8px;font-size:12px}
</style>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <div class="filter-bar">
        <label class="mb-0">Event</label>
        <select id="event_id" class="form-control" style="max-width:260px">
          <option value="0">— All Events —</option>
          <?php foreach (($events ?? []) as $e): ?>
            <option value="<?= (int)$e['event_id'] ?>">
              <?= esc(($e['event_name'] ?? 'Event').' '.($e['event_date'] ?? '')) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label class="mb-0">House</label>
        <select id="house_id" class="form-control" style="max-width:220px">
          <option value="0">— All Houses —</option>
          <?php foreach (($houses ?? []) as $h): ?>
            <option value="<?= (int)$h['house_id'] ?>"><?= esc($h['house_name'] ?? '') ?></option>
          <?php endforeach; ?>
        </select>

        <div class="tab-switch">
          <button id="tabHouse" class="btn btn-primary btn-sm"><i class="fas fa-flag-checkered"></i> House Leaderboard</button>
          <button id="tabStudent" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-graduate"></i> Student Leaderboard</button>
        </div>

        <button id="btnReload" class="btn btn-secondary btn-sm">Reload</button>
      </div>
      <div class="mt-2 text-muted" style="font-size:12px">
        Points mapping: 1st = <b><?= (int)$p1 ?></b>, 2nd = <b><?= (int)$p2 ?></b>, 3rd = <b><?= (int)$p3 ?></b>
      </div>
    </div>

    <div class="card-body">
      <h5 class="section-title" id="secTitle">House Leaderboard</h5>
      <div id="cards" class="cards-grid"></div>
      <div id="hint" class="hint">Use filters and Reload to update the leaderboard.</div>
    </div>
  </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';
let currentTab = 'house'; // 'house' | 'student'

function esc(s){return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]))}
function imgUrl(ph){return ph ? '<?= base_url('uploads/') ?>'+String(ph).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>'}
function ageYears(dob){
  if(!dob) return '';
  const d=new Date(dob); if(isNaN(d)) return '';
  const n=new Date(); let m=(n.getFullYear()-d.getFullYear())*12+(n.getMonth()-d.getMonth());
  if(n.getDate()<d.getDate()) m--; let y=Math.floor(m/12), mm=m%12; if(mm>6) y++;
  return y+' Yr';
}
function ordinal(n){const s=["th","st","nd","rd"], v=n%100; return n+(s[(v-20)%10]||s[v]||s[0])}
function posLabel(n){return (n===1?'1st':(n===2?'2nd':(n===3?'3rd':String(n))))}

function renderHouse(rows){
  const box = document.getElementById('cards');
  if(!rows || !rows.length){ box.innerHTML=''; document.getElementById('hint').style.display=''; return; }
  document.getElementById('hint').style.display='none';

  const html = rows.map((r,idx)=>{
    const color = r.color_code || '#0ea5e9';
    // rank (1,2,3...) shown top-right with trophy icon
    return `
      <div class="card-item">
        <div class="rank-badge"><i class="fas fa-trophy"></i> ${ordinal(idx+1)}</div>
        <div class="house-card">
          <div class="house-left" style="background:${esc(color)}">${esc((r.house_name||'')[0]||'H')}</div>
          <div class="house-right">
            <div class="house-name">${esc(r.house_name||'—')}</div>
            <div class="house-stats">
              <span>Total Points: <span class="num">${esc(r.total_points||0)}</span></span>
              <span>1st: <span class="num">${esc(r.firsts||0)}</span></span>
              <span>2nd: <span class="num">${esc(r.seconds||0)}</span></span>
              <span>3rd: <span class="num">${esc(r.thirds||0)}</span></span>
              <span>Podiums: <span class="num">${esc(r.podiums||0)}</span></span>
            </div>
          </div>
        </div>
      </div>`;
  }).join('');
  box.innerHTML = html;
}

function renderStudents(rows){
  const box = document.getElementById('cards');
  if(!rows || !rows.length){ box.innerHTML=''; document.getElementById('hint').style.display=''; return; }
  document.getElementById('hint').style.display='none';

  const html = rows.map((r,idx)=>{
    const full = esc(((r.first_name||'')+' '+(r.last_name||'')).trim()) || ('ID '+r.student_id);
    const meta = [r.class_short||'', ageYears(r.date_of_birth||'')].filter(Boolean).join(' • ');
    const houseChip = `<span class="house-chip"><span class="house-dot" style="background:${esc(r.color_code||'#999')}"></span>${esc(r.house_name||'')}</span>`;
    const pills = `
      <span class="pill"><b>${esc(r.total_points||0)}</b> pts</span>
      <span class="pill">1st: <b>${esc(r.firsts||0)}</b></span>
      <span class="pill">2nd: <b>${esc(r.seconds||0)}</b></span>
      <span class="pill">3rd: <b>${esc(r.thirds||0)}</b></span>
      <span class="pill">Podiums: <b>${esc(r.podiums||0)}</b></span>
    `;

    return `
      <div class="card-item">
        <div class="rank-badge"><i class="fas fa-trophy"></i> ${ordinal(idx+1)}</div>
        <img class="card-photo" src="${imgUrl(r.profile_photo)}" alt="">
        <div class="card-main">
          <div class="card-name">${full}</div>
          <div class="card-sub">${esc(meta)}</div>
          <div class="pills">${pills}</div>
          <div class="pills">${houseChip}</div>
        </div>
      </div>`;
  }).join('');
  box.innerHTML = html;
}

function reload(){
  document.getElementById('hint').style.display='none';
  document.getElementById('cards').innerHTML = '<div class="hint">Loading…</div>';

  const event_id = Number(document.getElementById('event_id').value||0);
  const house_id = Number(document.getElementById('house_id').value||0);
  const url = currentTab==='house'
      ? '<?= base_url('admin/sports/leaderboard/house') ?>'
      : '<?= base_url('admin/sports/leaderboard/students') ?>';

  fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
    body: new URLSearchParams({[CSRF_NAME]: CSRF_HASH, event_id, house_id})
  })
  .then(r => r.json())
  .then(res => {
    if(!res || !res.ok){ toastr?.error?.('Failed to load'); return; }
    if(currentTab==='house'){ renderHouse(res.rows||[]); }
    else { renderStudents(res.rows||[]); }
  })
  .catch(()=> toastr?.error?.('Failed'));
}

document.getElementById('btnReload').addEventListener('click', reload);
document.getElementById('event_id').addEventListener('change', reload);
document.getElementById('house_id').addEventListener('change', reload);

document.getElementById('tabHouse').addEventListener('click', ()=>{
  currentTab='house';
  document.getElementById('secTitle').innerText='House Leaderboard';
  document.getElementById('tabHouse').classList.replace('btn-outline-primary','btn-primary');
  document.getElementById('tabStudent').classList.replace('btn-primary','btn-outline-primary');
  reload();
});
document.getElementById('tabStudent').addEventListener('click', ()=>{
  currentTab='student';
  document.getElementById('secTitle').innerText='Student Leaderboard';
  document.getElementById('tabStudent').classList.replace('btn-outline-primary','btn-primary');
  document.getElementById('tabHouse').classList.replace('btn-primary','btn-outline-primary');
  reload();
});

document.addEventListener('DOMContentLoaded', reload);
</script>

<?= $this->endSection() ?>
