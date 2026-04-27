<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.report-head {display:flex; gap:12px; align-items:center; flex-wrap:wrap;}
.summary-chips {display:flex; gap:10px; flex-wrap:wrap;}
.summary-chips .chip{padding:6px 10px; border-radius:999px; background:#EEF2FF; font-weight:700; font-size:13px;}
.grid {display:grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap:14px;}
.card {
  border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:10px;
  text-align:center; box-shadow:0 2px 10px rgba(0,0,0,.03);
}
.card img{
  display:block;            /* ensure block-level */
  margin:0 auto 8px;        /* <-- centers horizontally */
  float:none !important;    /* in case any global img float leaks in */
  width:86px;
  height:86px;
  border-radius:50%;
  object-fit:cover;
}
.card .name{ font-weight:800; font-size:14px; line-height:1.15; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;}
.card .meta{ font-size:12px; color:#111827; }
.section-title{font-weight:800; margin:18px 0 10px;}
.hr{height:1px; background:#f1f5f9; margin:14px 0;}
@media print {
  .no-print { display: none !important; }
  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .card { break-inside: avoid; }
  .page-break {
    page-break-before: always;
  }
}




#cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 14px;
}

.member .name {
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 2px;
}

.member .meta {
  font-size: 12px;
  color: #555;
}

.card {
  position: relative; /* allow absolute badge */
}

.card .sno-badge{
  position:absolute;
  top:6px;
  left:6px;
  background:#0ea5e9; /* sky blue - you can change */
  color:#fff;
  font-size:11px;
  font-weight:700;
  padding:2px 6px;
  border-radius:6px;
  line-height:1;
}
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-7">
        <h1>House Members Report</h1>
        <div class="text-muted">Select a house to view and print members (ordered by age).</div>
      </div>
      <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/houses') ?>">Houses</a></li>
          <li class="breadcrumb-item active">Members Report</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <div class="report-head">
        <div class="form-inline no-print" style="gap:10px;">
          <label class="mr-2">House</label>
          <select id="house_id" class="form-control">
            <option value="">-- Select House --</option>
            <?php foreach (($houses ?? []) as $h): ?>
              <option value="<?= (int)$h['house_id'] ?>"><?= esc($h['house_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button id="printBtn" type="button" class="btn btn-secondary">Print</button>
        </div>
        <div class="summary-chips" id="summary" style="margin-left:auto;"></div>
      </div>
    </div>

    <div class="card-body" id="reportBody">
      <div class="text-muted">Choose a house to load members.</div>
    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function esc(s){ return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function photoURL(p){ return p && String(p).trim() !== '' ? '<?= base_url('uploads/') ?>'+String(p).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>'; }
function nameOf(r){ return (esc((r.first_name||'')+' '+(r.last_name||'')).trim() || ('ID '+r.student_id)); }
function metaOf(r){
  const age = Number(r.age_years||0) ? (Number(r.age_years)+' Yr') : '';
  const cls = esc(r.class_short||'');
  const cnt = Number(r.participation_count||0);
  const bits = [];
  if (age) bits.push(age);
  if (cls) bits.push(cls);
  bits.push(String(cnt)); // always show participation count
  return bits.join(' • ');
}

function card(r, i){
  return `
    <div class="card">
      <div class="sno-badge">${i}</div>
      <img src="${esc(photoURL(r.profile_photo||''))}" alt="">
      <div class="name" title="${nameOf(r)}">${nameOf(r)}</div>
      <div class="meta" title="${metaOf(r)}">${metaOf(r)}</div>
    </div>`;
}
function renderSection(title, arr){
  if (!arr || !arr.length) return '';
  return `
    <h5 class="section-title">${esc(title)}</h5>
    <div class="grid">
     ${arr.map((r,idx) => card(r, idx+1)).join('')}
    </div>
    <div class="hr"></div>
  `;
}

function renderSummary(sum){
  if (!sum) { $('#summary').empty(); return; }
  $('#summary').html(`
    <div class="chip">Total: ${sum.total||0}</div>
    <div class="chip">Male: ${sum.male||0}</div>
    <div class="chip">Female: ${sum.female||0}</div>
  `);
}

function loadHouse(houseId){
  if (!houseId){ $('#reportBody').html('<div class="text-muted">Choose a house to load members.</div>'); $('#summary').empty(); return; }
  $('#reportBody').html('<div class="text-muted">Loading…</div>');
  $.post('<?= base_url('admin/sports/reports/house-members/data') ?>', {
    [CSRF_NAME]: CSRF_HASH,
    house_id: houseId
  }, function(res){
    if (!res || !res.ok){ $('#reportBody').html('<div class="text-danger">Failed to load.</div>'); return; }
    renderSummary(res.summary);
    let html = '';
if (res.male && res.male.length) {
  html += renderSection('Male', res.male);
}
if (res.female && res.female.length) {
  html += '<div class="page-break"></div>' + renderSection('Female', res.female);
}
    $('#reportBody').html(html || '<div class="text-muted">No members found.</div>');
  }, 'json');
}

$(function(){
  $('#house_id').on('change', function(){ loadHouse($(this).val()); });
  $('#printBtn').on('click', function(){ window.print(); });
});
</script>

<?= $this->endSection() ?>
