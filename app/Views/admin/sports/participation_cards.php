<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Student Participation',
    'icon' => 'fas fa-id-badge',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Events', 'url' => base_url('admin/sports/events')],
        ['label' => 'Participation', 'active' => true],
    ],
]) ?>

<style>
.filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.cards-grid{display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:12px}
@media(min-width:576px){.cards-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:992px){.cards-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
.card-item{display:flex;gap:12px;border:1px solid #e5e7eb;border-radius:14px;padding:10px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.03)}
.card-photo{width:72px;height:72px;border-radius:10px;object-fit:cover}
.card-main{flex:1;min-width:0}
.card-house{font-weight:700;text-align:center;font-size:12px;color:#0f172a;margin-bottom:2px}
.house-dot{display:inline-block;width:10px;height:10px;border-radius:50%;vertical-align:middle;margin-right:6px}
.card-name{font-weight:700;margin:0;font-size:14px;color:#0f172a}
.card-sub{font-size:12px;color:#334155}
.card-events{margin-top:6px;font-size:12px;color:#111827}
.card-events .label{font-weight:600;margin-right:6px;color:#0f172a}
.badge{background:#eef2f7;border:1px solid #e5e7eb;border-radius:999px;padding:2px 8px}
.hint{color:#64748b}

.event-boxes {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 4px;
}
.event-box {
  background: #eef2f7;
  border: 1px solid #d0d7de;
  border-radius: 8px;
  padding: 4px 8px;
  font-size: 12px;
  color: #334155;
  width: fit-content;
}
</style>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <div class="filter-bar">
        <label class="mb-0">Class Section</label>
        <select id="cls_sec_id" class="form-control" style="max-width:260px">
          <option value="0">� All Sections �</option>
          <?php foreach (($sections ?? []) as $s): ?>
            <option value="<?= (int)$s['cls_sec_id'] ?>">
              <?= esc(($s['class_short'] ?? '') . ' - ' . ($s['section_name'] ?? '')) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button id="btnReload" class="btn btn-secondary">Reload</button>
      </div>
    </div>

    <div class="card-body">
      <div id="cards" class="cards-grid"></div>
      <div id="hint" class="hint">Pick a class section or keep �All Sections�.</div>
    </div>
  </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function esc(s){return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]))}
function imgUrl(ph){return ph ? '<?= base_url('uploads/') ?>'+String(ph).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>'}
function ageYears(dob){
  if(!dob) return '';
  const d=new Date(dob); if(isNaN(d)) return '';
  const n=new Date();
  let m=(n.getFullYear()-d.getFullYear())*12+(n.getMonth()-d.getMonth());
  if(n.getDate()<d.getDate()) m--;
  let y=Math.floor(m/12), mm=m%12;
  if(mm>6) y++;
  return y+' Yr';
}

function renderCards(rows){
  const box = document.getElementById('cards');
  if(!rows || !rows.length){
    box.innerHTML = '';
    document.getElementById('hint').style.display = '';
    return;
  }
  document.getElementById('hint').style.display = 'none';

  const html = rows.map(r=>{
    const full = esc(((r.first_name||'')+' '+(r.last_name||'')).trim()) || ('ID '+r.student_id);
    const meta = [r.class_short||'', ageYears(r.date_of_birth||''), (r.participation_count||0)+' events'].filter(Boolean).join(' � ');
   const events = (r.events_array || []).map(ev => `<div class="event-box">${esc(ev)}</div>`).join('');
    return `
      <div class="card-item">
        <img class="card-photo" src="${imgUrl(r.profile_photo)}" alt="">
        <div class="card-main">
          <div class="card-name">${full}</div>
          <div class="card-sub">${meta}</div>
          <div class="event-boxes">${events}</div>
        </div>
      </div>`;
  }).join('');

  box.innerHTML = html;
}

function reload(){
  const cls_sec_id = Number(document.getElementById('cls_sec_id').value||0);
  document.getElementById('cards').innerHTML = '<div class="hint">Loading�</div>';
  fetch('<?= base_url('admin/sports/participation-report/data') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
    body: new URLSearchParams({[CSRF_NAME]: CSRF_HASH, cls_sec_id})
  })
  .then(r => r.json())
  .then(res => {
    if(!res || !res.ok){ toastr?.error?.('Failed to load'); return; }
    renderCards(res.rows||[]);
  })
  .catch(()=> toastr?.error?.('Failed'));
}

document.getElementById('btnReload').addEventListener('click', reload);
document.getElementById('cls_sec_id').addEventListener('change', reload);
document.addEventListener('DOMContentLoaded', reload);
</script>

<?= $this->endSection() ?>
