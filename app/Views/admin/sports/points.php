<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'House-wise Points & Position Holders',
    'icon' => 'fas fa-medal',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Events', 'url' => base_url('admin/sports/events')],
        ['label' => 'Points Report', 'active' => true],
    ],
]) ?>

<style>
/* Top totals */
.totals-wrap{display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:10px;margin-bottom:12px}
@media(min-width:768px){.totals-wrap{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1200px){.totals-wrap{grid-template-columns:repeat(4,minmax(0,1fr))}}
.total-card{display:flex;align-items:center;gap:10px;border:1px solid #e5e7eb;border-radius:14px;background:#fff;padding:10px 12px}
.badge-dot{width:10px;height:10px;border-radius:50%}
.total-name{font-weight:700}
.total-points{margin-left:auto;font-weight:800;font-size:18px}

/* Toolbar */
.report-toolbar{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.report-toolbar .grow{flex:1}

/* Main cards wrapper now just block; grouping inside will be grid */
#cards{display:block}

/* Group blocks (for event / position / house headings) */
.group-block{
  margin-bottom:18px;
  page-break-inside:avoid;
}
.group-heading{
  font-weight:700;
  font-size:15px;
  border-bottom:1px solid #cbd5e1;
  padding:4px 0;
  margin-bottom:8px;
}
.group-heading-small{
  font-size:12px;
  color:#64748b;
}

/* Cards grid per group */
.group-cards{
  display:grid;
  grid-template-columns:repeat(1,minmax(0,1fr));
  gap:12px;
}
@media(min-width:768px){.group-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1200px){.group-cards{grid-template-columns:repeat(3,minmax(0,1fr))}}

/* Individual card */
.card-item{
  display:flex;
  gap:12px;
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#fff;
  padding:12px;
}
.card-photo{width:76px;min-width:76px;height:76px;border-radius:12px;object-fit:cover;background:#f1f5f9}
.card-main{flex:1;min-width:0}
.card-name{font-weight:800;font-size:16px;line-height:1.1;margin-bottom:2px}
.card-sub{font-size:12px;color:#475569}
.card-meta{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.pill{font-size:11px;border:1px solid #e5e7eb;background:#f8fafc;border-radius:999px;padding:4px 8px}
.pill.badge{border-color:#dbeafe;background:#eff6ff}
.pill.house{display:flex;align-items:center;gap:6px}
.house-dot{width:10px;height:10px;border-radius:50%}

.empty-hint{color:#64748b}

/* ================== PRINT STYLES (Black & White Friendly) ================== */
/* ================== PRINT STYLES (Black & White + 4 cards per row) ================== */
@media print {

  /* Basic reset for print */
  html, body {
    background:#fff !important;
    margin:0 !important;
    padding:0 !important;
    -webkit-print-color-adjust:exact !important;
    print-color-adjust:exact !important;
  }

  /* Hide layout chrome */
  .main-header,
  .main-sidebar,
  .main-footer,
  .content-header,
  .breadcrumb,
  .report-toolbar,
  .btn,
  #btnReload {
    display:none !important;
  }

  .content-wrapper, .content {
    margin:0 !important;
    padding:0 !important;
  }

  .card {
    box-shadow:none !important;
    border:none !important;
  }

  .card-body {
    padding:0 !important;
  }

  /* Group headings should stand out in B&W */
  .group-heading {
    border-bottom:1px solid #000 !important;
    font-size:13px;
    font-weight:700;
    margin-bottom:6px;
    padding:2px 0;
  }

  /* Small helper line under heading if used */
  .group-heading-small {
    font-size:10px;
  }

  /* Use clear borders for cards so they’re visible on B&W printer */
  .card-item {
    border:1px solid #000 !important;
    border-radius:6px !important;
    page-break-inside:avoid;
    padding:6px 8px !important;   /* more compact for 4-in-a-row */
  }

  /* Make photo + text more compact on paper */
  .card-photo {
    width:48px !important;
    min-width:48px !important;
    height:48px !important;
    border-radius:6px !important;
  }

  .card-name {
    font-size:12px !important;
  }

  .card-sub {
    font-size:10px !important;
  }

  .pill {
    font-size:9px !important;
    padding:2px 6px !important;
  }

  /* Avoid relying only on color for house dots / badges */
  .house-dot,
  .badge-dot {
    background:#fff !important;
    border:1px solid #000 !important;
  }

  /* >>> KEY PART: 4 CARDS PER ROW ON PRINT <<< */
  .group-cards {
    display:grid !important;
    grid-template-columns:repeat(4, minmax(0, 1fr)) !important;
    column-gap:6px !important;
    row-gap:6px !important;
  }
}

</style>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <div class="report-toolbar">
        <div class="grow">
          <label class="mb-0 me-2">Event</label>
          <select id="event_id" class="form-control d-inline-block" style="max-width:420px">
            <option value="0">— All Events —</option>
            <?php foreach (($events ?? []) as $e): ?>
              <option value="<?= (int)$e['event_id'] ?>">
                <?= esc($e['event_name']) ?><?php if(!empty($e['event_date'])): ?> (<?= esc(date('Y-m-d', strtotime($e['event_date']))) ?>)<?php endif; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="mb-0 me-2">Order By</label>
          <select id="order_by" class="form-control d-inline-block" style="width:180px">
            <option value="position">Position</option>
            <option value="event">Event</option>
            <option value="house">House</option>
          </select>
        </div>

        <button id="btnReload" class="btn btn-secondary ms-2">Reload</button>
        <!-- PRINT BUTTON (works fine on B&W printer) -->
        <button type="button" class="btn btn-secondary ms-2" onclick="window.print()">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>

    <div class="card-body">
      <!-- House totals -->
      <div id="totals" class="totals-wrap"></div>

      <!-- Grouped Cards -->
      <div id="cards"></div>

      <div id="hint" class="empty-hint mt-2">Use filters and click “Reload”.</div>
    </div>
  </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function esc(s){
  return String(s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}
function imgUrl(ph){
  return ph
    ? '<?= base_url('uploads/') ?>'+String(ph).replace(/^\/+/,'')
    : '<?= base_url('resource/img/avatar-student.png') ?>';
}
function ageYears(dob){
  if(!dob) return '';
  const d=new Date(dob); if(isNaN(d)) return '';
  const n=new Date();
  let m=(n.getFullYear()-d.getFullYear())*12 + (n.getMonth()-d.getMonth());
  if(n.getDate()<d.getDate()) m--;
  let y=Math.floor(m/12), mm=m%12;
  if(mm>6) y++;
  return y+' Yr';
}

/** Totals (per house) */
function renderTotals(items){
  if(!items || !items.length){
    document.getElementById('totals').innerHTML = '';
    return;
  }
  const html = items.map(x=>{
    const dot = `<span class="badge-dot" style="background:${esc(x.color_code||'#999')}"></span>`;
    return `<div class="total-card">
      ${dot}
      <div class="total-name">${esc(x.house_name||'House')}</div>
      <div class="total-points">${Number(x.total_points||0)}</div>
    </div>`;
  }).join('');
  document.getElementById('totals').innerHTML = html;
}

/** Single card HTML (used inside any group) */
function buildCard(r){
  const full = esc(((r.first_name||'')+' '+(r.last_name||'')).trim()) || ('ID '+r.student_id);
  const meta2 = [
    r.class_short||'',
    ageYears(r.date_of_birth||''),
    ((r.participation_count||0)+' events')
  ].filter(Boolean).join(' • ');

  const houseDot = `<span class="house-dot" style="background:${esc(r.color_code||'#999')}"></span>`;

  return `<div class="card-item">
    <img class="card-photo" src="${imgUrl(r.profile_photo)}" alt="">
    <div class="card-main">
      <div class="card-name">${full}</div>
      <div class="card-sub">${esc(meta2)}</div>
      <div class="card-meta">
        <span class="pill badge">Position: <b>${esc(String(r.position))}</b></span>
        <span class="pill">Event: ${esc(r.event_name||'')}</span>
        <span class="pill house">${houseDot} <b>${esc(r.house_name||'')}</b></span>
      </div>
    </div>
  </div>`;
}

/**
 * Group rows according to selected "Order By"
 *  - event   => heading per Event
 *  - position=> heading per Position
 *  - house   => heading per House
 */
function renderCards(rows){
  const box = document.getElementById('cards');
  if(!rows || !rows.length){
    box.innerHTML = '';
    document.getElementById('hint').style.display = '';
    return;
  }
  document.getElementById('hint').style.display = 'none';

  const orderBy = document.getElementById('order_by').value || 'position';

  // Build grouped map: { headingKey => { label, subLabel, items[] } }
  const groups = new Map();

  rows.forEach(r=>{
    let key    = '';
    let label  = '';
    let sub    = '';

    if(orderBy === 'event'){
      key   = (r.event_name || 'Unknown Event') + '|' + (r.event_id || 0);
      label = 'Event: ' + (r.event_name || 'Unknown Event');
      // optional small sub-line: house/gender/position info not needed here
    } else if(orderBy === 'position'){
      const pos = r.position || '-';
      key   = 'position|' + pos;
      label = 'Position ' + pos;
      sub   = 'All participants holding this position';
    } else { // orderBy === 'house'
      key   = (r.house_name || 'No House') + '|' + (r.house_id || 0);
      label = 'House: ' + (r.house_name || 'No House');
    }

    if(!groups.has(key)){
      groups.set(key, { label, sub, items: [] });
    }
    groups.get(key).items.push(r);
  });

  // Build HTML
  let html = '';
  groups.forEach((grp)=>{
    const cardsHtml = grp.items.map(buildCard).join('');
    html += `
      <div class="group-block">
        <div class="group-heading">
          ${esc(grp.label)}
          ${grp.sub ? `<div class="group-heading-small">${esc(grp.sub)}</div>` : ''}
        </div>
        <div class="group-cards">
          ${cardsHtml}
        </div>
      </div>
    `;
  });

  box.innerHTML = html;
}

function reloadData(){
  const payload = {};
  payload[CSRF_NAME] = CSRF_HASH;
  payload.event_id = Number(document.getElementById('event_id').value||0);
  payload.order_by = document.getElementById('order_by').value||'position';

  document.getElementById('hint').style.display = '';
  document.getElementById('cards').innerHTML = '';
  document.getElementById('totals').innerHTML = '';

  fetch('<?= base_url('admin/sports/reports/points/data') ?>', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body: new URLSearchParams(payload)
  })
  .then(r=>r.json())
  .then(res=>{
    if(!res || !res.ok){ throw new Error('Failed'); }
    renderTotals(res.houseTotals||[]);
    renderCards(res.cards||[]);
  })
  .catch(()=>{ /* silent */ });
}

document.addEventListener('DOMContentLoaded', function(){
  document.getElementById('btnReload').addEventListener('click', reloadData);
  document.getElementById('event_id').addEventListener('change', reloadData);
  document.getElementById('order_by').addEventListener('change', reloadData);
  reloadData();
});
</script>

<?= $this->endSection() ?>
