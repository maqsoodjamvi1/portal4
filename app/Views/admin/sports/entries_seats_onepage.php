<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Seats (Per House)</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports Events</a></li>
          <li class="breadcrumb-item active">Seats</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<style>
/* --- GRID LAYOUT FOR HOUSES (RESPONSIVE) --- */
.house-wrap {
  display: grid;
  grid-template-columns: 1fr;        /* mobile: single column */
  gap: 16px;
}

@media (min-width: 576px) {
  .house-wrap { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (min-width: 992px) {
  .house-wrap { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (min-width: 1200px) {
  .house-wrap { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

/* --- HOUSE CARD --- */
.house-card{
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#fff;
  box-shadow:0 2px 10px rgba(0,0,0,.03);
}

/* header */
.house-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding:10px 14px;
  border-bottom:1px solid #f1f5f9;
}

/* allow wrapping on very small screens so content doesn't overlap */
@media (max-width: 575.98px) {
  .house-head{
    flex-wrap:wrap;
    align-items:flex-start;
  }
  .house-head .house-title{
    flex:0 0 100%;
  }
  .house-head .counts{
    margin-left:auto;
  }
  .capbar{
    order:3;
    width:100%;
    margin:6px 0 0;
  }
}

.house-title{ font-weight:700; margin:0; }
.cap-chip{ font-size:12px; color:#475569; }

.cart-area{
  padding:10px 12px 4px;
  min-height:92px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

.cart-item{
  position:relative;
  border:1px solid #e5e7eb;
  border-radius:10px;
  padding:6px 8px;
  display:flex;
  gap:8px;
  align-items:center;
}
.cart-item img{ width:38px; height:38px; border-radius:50%; object-fit:cover; }
.cart-name{ font-size:13px; font-weight:700; line-height:1.15; }
.cart-meta{ font-size:11px; color:#334155; }
.cart-del{
  position:absolute;
  top:-8px;
  right:-8px;
  border:none;
  width:20px;
  height:20px;
  border-radius:999px;
  background:#ef4444;
  color:#fff;
  line-height:20px;
  text-align:center;
  cursor:pointer;
}

.tools{
  padding:6px 12px;
  display:flex;
  gap:8px;
}
.tools .search{ flex:1; }
.tools input[type="text"]{
  width:100%;
  border:1px solid #e5e7eb;
  border-radius:8px;
  padding:6px 8px;
  font-size:13px;
}

/* members grid */
.members{
  padding:0 12px 12px;
  display:grid;
  grid-template-columns: repeat(auto-fill, minmax(160px,1fr));
  gap:10px;
}

/* slightly more forgiving on very small screens */
@media (max-width: 575.98px) {
  .members{
    grid-template-columns: repeat(auto-fill, minmax(140px,1fr));
  }
}

.member{
  border:1px solid #e5e7eb;
  border-radius:12px;
  background:#fff;
  padding:10px;
  text-align:center;
  cursor:pointer;
  transition: box-shadow .15s ease, transform .1s ease;
}
.member:hover{
  transform:translateY(-1px);
  box-shadow:0 8px 22px rgba(0,0,0,.06);
}
.member img{
  width:72px;
  height:72px;
  border-radius:50%;
  object-fit:cover;
  margin-bottom:6px;
}
.member .name{
  font-weight:700;
  font-size:13px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.member .meta{
  font-size:12px;
  color:#111827;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.member.disabled{ opacity:.45; pointer-events:none; }
.capacity-full{ opacity:.6; }
.small-muted{ font-size:12px; color:#64748b; }
.event-mini{ margin:6px 0 14px; color:#334155; font-size:14px; }

/* Seats grid inside each house */
 .seat-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(150px, 1fr)); /* WAS 0, now 150px */
    gap:12px;
    width:100%;
  }

/* Force 2 columns on small screens */
@media (max-width: 575.98px){
  .seat-grid{
    grid-template-columns:repeat(2, minmax(0,1fr));
  }
    .seat-slot{
    padding:10px 8px;
    width:100%;              /* NEW: stretch card */
    box-sizing:border-box;   /* ensures full width */
  }

  .seat-slot img{
    width:40px;
    height:40px;
  }

   .seat-info .name{
    font-size:13px;
  }

  .seat-info .meta{
    font-size:11px;
  }

  .vacant-pill{
    font-size:12px;
    padding:5px 10px;
    margin-left:22px;
  }
}

.seat-slot{
  position:relative;
  border:1px dashed #cbd5e1;
  border-radius:12px;
  background:#fafafa;
  min-height:76px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px;
}

.seat-slot{
  position:relative;
  border:1px dashed #cbd5e1;
  border-radius:12px;
  background:#fafafa;
  min-height:76px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px;
}
.seat-slot.filled{
  border:1px solid #e5e7eb;
  background:#fff;
}
.seat-num{
  position:absolute;
  top:6px;
  left:6px;
  font-size:11px;
  color:#64748b;
  background:#f1f5f9;
  border:1px solid #e5e7eb;
  border-radius:999px;
  padding:2px 6px;
}
.vacant-pill{
  margin-left:26px;
  font-size:12px;
  color:#64748b;
  border:1px dashed #cbd5e1;
  border-radius:999px;
  padding:6px 12px;
  background:#f8fafc;
}
.seat-slot img{
  width:42px;
  height:42px;
  border-radius:50%;
  object-fit:cover;
}
.seat-info{ display:flex; flex-direction:column; }
.seat-info .name{ font-weight:700; font-size:13px; line-height:1.1; }
.seat-info .meta{ font-size:11px; color:#334155; }
.seat-slot .cart-del{ top:-8px; right:-8px; }

/* thin progress bar in the house header */
.capbar{
  flex:1;
  height:6px;
  border-radius:999px;
  background:#eef2f7;
  overflow:hidden;
  margin:0 10px;
}
.capbar > i{
  display:block;
  height:100%;
  background:#4f46e5;
  width:0%;
}
.house-head .counts{
  white-space:nowrap;
  font-size:12px;
  color:#475569;
}

/* Team layout */
.team-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
  gap:10px;
}

/* smaller team cards on tiny screens */
@media (max-width: 575.98px) {
  .team-grid{
    grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
  }
}

.team-card{
  border:1px solid #e5e7eb;
  border-radius:12px;
  background:#fff;
}
.team-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:8px 10px;
  border-bottom:1px solid #eef2f7;
  font-weight:600;
  font-size:13px;
}
.team-cap{ font-size:12px; color:#64748b; }
</style>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Select Event</h3>
    </div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group col-md-8">
          <label>Event</label>
          <select id="event_id" class="form-control">
            <option value="">-- Select Event --</option>
            <?php foreach (($events ?? []) as $e): ?>
              <option value="<?= (int)$e['event_id'] ?>">
                <?= esc($e['event_name']) ?>
                <?php if (!empty($e['gender'])): ?>(<?= esc(strtolower($e['gender'])) ?>)<?php endif; ?>
                <?= !empty($e['event_date']) ? ' - '.esc(date('Y-m-d', strtotime($e['event_date']))) : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-4" style="margin-top:32px">
          <button type="button" id="clearBtn" class="btn btn-secondary btn-block">Clear</button>
        </div>
      </div>

      <!-- Dynamic Event Header -->
      <div id="eventHeader" class="event-mini" style="display:none;"></div>

      <!-- Houses + members container (hidden until event loaded) -->
      <div id="seatsArea" style="display:none;">
        <div id="houses" class="house-wrap"><!-- filled by JS --></div>
      </div>

      <div id="emptyHint" class="text-muted" style="margin-top:10px;">
        Pick an event to start assigning participants.
      </div>
    </div>
  </div>
</section>

<script>
// PRE-GUARD: keep server.js from crashing on pages that don't load some plugins.
(function ($) {
  if (!window.jQuery) return; // layout should load jQuery

  // jQuery Validate safe no-op
  if (typeof $.validator === 'undefined') {
    $.validator = { setDefaults: function(){} };
  } else if (typeof $.validator.setDefaults !== 'function') {
    $.validator.setDefaults = function(){};
  }

  // Select2 v4 safe defaults shim
  if (!$.fn) $.fn = {};
  if (!$.fn.select2) {
    $.fn.select2 = function(){ return this; };
    $.fn.select2.defaults = { set: function(){} };
  } else {
    if (!$.fn.select2.defaults) $.fn.select2.defaults = {};
    if (typeof $.fn.select2.defaults.set !== 'function') {
      $.fn.select2.defaults.set = function(){};
    }
  }

  // Toastr safe no-op
  if (typeof window.toastr === 'undefined') {
    window.toastr = { success:()=>{}, error:()=>{}, warning:()=>{}, info:()=>{} };
  }
})(jQuery);
</script>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

let EVENT_ID = 0;
let EVENT_CAP = 0;
let EVENT_GENDER = '';
let MIN_AGE = 0;
let MAX_AGE = 0;
let EVENT_TYPE = 'individual'; // 'individual' | 'team'
let TEAM_SIZE  = 0;            // seats per team for team events

function esc(s){
  return String(s || '').replace(/[&<>"']/g, m => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  })[m]);
}

function ageYearsRounded(dob){
  if(!dob) return '';
  const d = new Date(dob); if(isNaN(d)) return '';
  const now = new Date();
  let months = (now.getFullYear()-d.getFullYear())*12 + (now.getMonth()-d.getMonth());
  if (now.getDate() < d.getDate()) months -= 1;
  let y = Math.floor(months/12), m = months % 12;
  if (m>6) y += 1;
  return y + ' Yr';
}

function houseCard(h){
  const capDisplay = (EVENT_TYPE === 'team' && TEAM_SIZE>0)
    ? (EVENT_CAP * TEAM_SIZE)
    : EVENT_CAP;

  return `
  <div class="house-card" data-house="${h.house_id}">
    <div class="house-head">
      <h5 class="house-title mb-0">${esc(h.house_name)}</h5>
      <div class="capbar"><i></i></div>
      <div class="counts">
        <span class="sel-count">0</span> / <span class="cap">${capDisplay}</span> selected
      </div>
    </div>

    <div class="cart-area"><!-- seats grid or teams grid will render here --></div>

    <div class="tools">
      <div class="search"><input type="text" class="q" placeholder="Search by name…"></div>
      <button class="btn btn-sm btn-outline-secondary reload">Reload</button>
    </div>

    <div class="members"><div class="small-muted">Type to search and click a student to add.</div></div>
  </div>`;
}

function memberCard(houseId, r, disabled){
  const name = esc((r.first_name||'')+' '+(r.last_name||'')).trim() || ('ID '+r.student_id);
  const cls  = esc(r.class_short || '');
  const age  = ageYearsRounded(r.date_of_birth||'');
  const cnt  = Number(r.participation_count || 0);
  const bits = [];
  if (cls) bits.push(cls);
  if (age) bits.push(age);
  bits.push(String(cnt));
  const meta = bits.join(' • ');
  const img  = esc(r.profile_photo ? '<?= base_url('uploads/') ?>'+String(r.profile_photo).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>');

  return `
    <div class="member ${disabled ? 'disabled':''}" data-house="${houseId}" data-student="${r.student_id}">
      <img src="${img}" alt="">
      <div class="name" title="${name}">${name}</div>
      <div class="meta" title="${meta}">${meta}</div>
    </div>`;
}

function renderCartInto($h, arr){
  const sel = (arr||[]).length;
  const capDisplay = Number($h.find('.cap').text()||'0');

  $h.find('.sel-count').text(sel);
  const pct = capDisplay > 0 ? Math.min(100, Math.round((sel/capDisplay)*100)) : 0;
  $h.find('.capbar > i').css('width', pct + '%');
  if (capDisplay>0 && sel>=capDisplay) $h.find('.house-head').addClass('capacity-full');
  else $h.find('.house-head').removeClass('capacity-full');

  if (EVENT_TYPE !== 'team' || TEAM_SIZE <= 0) {
    const cap = capDisplay;
    const slots = [];
    for (let i=0;i<cap;i++){
      if (i<sel){
        const row = arr[i];
        const name = esc((row.first_name||'')+' '+(row.last_name||'')).trim() || ('ID '+row.student_id);
        const cls  = esc(row.class_short||'');
        const age  = ageYearsRounded(row.date_of_birth||'');
        const meta = [cls, age].filter(Boolean).join(' • ');
        const img  = esc(row.profile_photo ? '<?= base_url('uploads/') ?>'+String(row.profile_photo).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>');
        slots.push(`
          <div class="seat-slot filled" data-seat="${i+1}">
            <span class="seat-num">Seat ${i+1}</span>
            <button class="cart-del" title="Remove" data-entry="${row.entry_id}">×</button>
            <img src="${img}" alt="">
            <div class="seat-info"><div class="name">${name}</div><div class="meta">${meta}</div></div>
          </div>`);
      } else {
        slots.push(`
          <div class="seat-slot vacant" data-seat="${i+1}" title="Seat ${i+1} is vacant">
            <span class="seat-num">Seat ${i+1}</span>
            <div class="vacant-pill">Vacant</div>
          </div>`);
      }
    }
    $h.find('.cart-area').html(`<div class="seat-grid">${slots.join('')}</div>`);
    $h.find('.members .member').toggleClass('disabled', sel >= capDisplay);
    return;
  }

  const teams = EVENT_CAP;
  const size  = TEAM_SIZE;
  const byTeam = {};
  (arr||[]).forEach(r => {
    const t = Number(r.team_id || 0);
    if (!byTeam[t]) byTeam[t] = [];
    byTeam[t].push(r);
  });

  const teamCards = [];
  for (let t=1; t<=teams; t++){
    const rows = byTeam[t] || [];
    const slots = [];
    for (let i=0;i<size;i++){
      if (i<rows.length){
        const row = rows[i];
        const name = esc((row.first_name||'')+' '+(row.last_name||'')).trim() || ('ID '+row.student_id);
        const cls  = esc(row.class_short||'');
        const age  = ageYearsRounded(row.date_of_birth||'');
        const meta = [cls, age].filter(Boolean).join(' • ');
        const img  = esc(row.profile_photo ? '<?= base_url('uploads/') ?>'+String(row.profile_photo).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>');
        slots.push(`
          <div class="seat-slot filled" data-team="${t}" data-seat="${i+1}">
            <span class="seat-num">T${t} • ${i+1}/${size}</span>
            <button class="cart-del" title="Remove" data-entry="${row.entry_id}">×</button>
            <img src="${img}" alt="">
            <div class="seat-info"><div class="name">${name}</div><div class="meta">${meta}</div></div>
          </div>`);
      } else {
        slots.push(`
          <div class="seat-slot vacant" data-team="${t}" data-seat="${i+1}" title="Team ${t}, seat ${i+1} is vacant">
            <span class="seat-num">T${t} • ${i+1}/${size}</span>
            <div class="vacant-pill">Vacant</div>
          </div>`);
      }
    }
    teamCards.push(`
      <div class="team-card">
        <div class="team-head">
          <div>Team ${t}</div>
          <div class="team-cap">${rows.length} / ${size}</div>
        </div>
        <div class="seat-grid">${slots.join('')}</div>
      </div>`);
  }

  if (byTeam[0] && byTeam[0].length){
    const rows = byTeam[0];
    const items = rows.map(row=>{
      const name = esc((row.first_name||'')+' '+(row.last_name||'')).trim() || ('ID '+row.student_id);
      const cls  = esc(row.class_short||'');
      const age  = ageYearsRounded(row.date_of_birth||'');
      const meta = [cls, age].filter(Boolean).join(' • ');
      const img  = esc(row.profile_photo ? '<?= base_url('uploads/') ?>'+String(row.profile_photo).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>');
      return `
        <div class="seat-slot filled">
          <span class="seat-num">Unassigned</span>
          <button class="cart-del" title="Remove" data-entry="${row.entry_id}">×</button>
          <img src="${img}" alt="">
          <div class="seat-info"><div class="name">${name}</div><div class="meta">${meta}</div></div>
        </div>`;
    }).join('');
    teamCards.push(`
      <div class="team-card">
        <div class="team-head">
          <div>Unassigned</div>
          <div class="team-cap">${rows.length}</div>
        </div>
        <div class="seat-grid">${items}</div>
      </div>`);
  }

  $h.find('.cart-area').html(`<div class="team-grid">${teamCards.join('')}</div>`);
  $h.find('.members .member').toggleClass('disabled', sel >= capDisplay);
}

function refreshCart(){
  if (!EVENT_ID) return;
  $.post('<?= base_url('admin/sports/entries/seats/cart') ?>',{
    [CSRF_NAME]: CSRF_HASH, event_id: EVENT_ID
  }, function(res){
    if(!res || !res.ok) return;
    const cart = res.cart || {};
    $('#houses .house-card').each(function(){
      const $h = $(this), hid = Number($h.data('house'));
      renderCartInto($h, cart[hid]||[]);
    });
  }, 'json');
}

function loadMembers($house, q=''){
  const houseId = Number($house.data('house'));
  const $box = $house.find('.members');
  $box.html('<div class="small-muted">Loading…</div>');
  $.post('<?= base_url('admin/sports/entries/seats/members') ?>', {
    [CSRF_NAME]: CSRF_HASH,
    event_id: EVENT_ID,
    house_id: houseId,
    q: q
  }, function(res){
    if(!res || !res.ok){ $box.html('<div class="small-muted">Failed to load.</div>'); return; }
    const cap = Number($house.find('.cap').text()||'0');
    const sel = Number($house.find('.sel-count').text()||'0');
    const full = (cap>0 && sel>=cap);
    const rows = (res.data||[]).map(r => memberCard(houseId, r, full)).join('');
    $box.html(rows || '<div class="small-muted">No students found.</div>');
  }, 'json');
}

function loadEventMeta(eventId){
  $('#eventHeader').hide().empty();
  $('#seatsArea').hide();
  $('#houses').empty();

  if (!eventId) {
    EVENT_ID = 0;
    $('#emptyHint').show();
    return;
  }

  $.post('<?= base_url('admin/sports/entries/seats/meta') ?>', {
    [CSRF_NAME]: CSRF_HASH, event_id: eventId
  }, function(res){
    if(!res || !res.ok){ toastr?.error?.(res?.msg||'Failed'); return; }

    EVENT_ID    = eventId;
    EVENT_CAP   = Number(res.event?.per_house_count || 0);
    EVENT_GENDER= String(res.event?.gender||'');
    MIN_AGE     = Number(res.event?.min_age || 0);
    MAX_AGE     = Number(res.event?.max_age || 0);
    EVENT_TYPE   = String(res.event?.event_type || 'individual').toLowerCase();
    TEAM_SIZE    = Number(res.event?.team_size || 0);

    const bits = [];
    bits.push('Gender: <b>'+esc((EVENT_GENDER||'-').toUpperCase())+'</b>');
    if (EVENT_TYPE === 'team') {
      bits.push('Teams/House: <b>'+EVENT_CAP+'</b>');
      bits.push('Team Size: <b>'+TEAM_SIZE+'</b>');
      bits.push('Total Seats/House: <b>'+ (EVENT_CAP*TEAM_SIZE) +'</b>');
    } else {
      bits.push('Seats/House: <b>'+EVENT_CAP+'</b>');
    }
    if (MIN_AGE>0) bits.push('Min Age: <b>'+MIN_AGE+'</b>');
    if (MAX_AGE>0) bits.push('Max Age: <b>'+MAX_AGE+'</b>');

    $('#eventHeader').html(bits.join(' • ')).show();

    const hs = res.houses||[];
    if (!hs.length){ $('#emptyHint').text('No houses found.').show(); return; }
    $('#houses').html(hs.map(h=>houseCard(h)).join(''));
    $('#emptyHint').hide();
    $('#seatsArea').show();

    const cart = res.cart || {};
    $('#houses .house-card').each(function(){
      const $h = $(this), hid = Number($h.data('house'));
      renderCartInto($h, cart[hid]||[]);
      loadMembers($h, '');
    });
  }, 'json');
}

$(function(){
  $('#event_id').on('change', function(){
    const id = Number($(this).val()||0);
    loadEventMeta(id);
  });

  $('#clearBtn').on('click', function(){
    $('#event_id').val('');
    loadEventMeta(0);
  });

  let t=null;
  $(document).on('input', '.house-card .q', function(){
    const $card = $(this).closest('.house-card');
    const q = $(this).val();
    clearTimeout(t);
    t = setTimeout(()=> loadMembers($card, q), 250);
  });

  $(document).on('click', '.house-card .reload', function(){
    const $card = $(this).closest('.house-card');
    const q = $card.find('.q').val() || '';
    loadMembers($card, q);
  });

  $(document).on('click', '.seat-slot.vacant', function(){
    $(this).closest('.house-card').find('.q').trigger('focus');
  });

  $(document).on('click', '.members .member', function(){
    if (!EVENT_ID) return;
    const $m = $(this);
    if ($m.hasClass('disabled')) return;
    const houseId = Number($m.data('house'));
    const studentId = Number($m.data('student'));
    const $card = $m.closest('.house-card');

    const cap = Number($card.find('.cap').text()||'0');
    const sel = Number($card.find('.sel-count').text()||'0');
    if (cap>0 && sel>=cap) { toastr?.warning?.('House cart is full'); return; }

    $.post('<?= base_url('admin/sports/entries/seats/add') ?>',{
      [CSRF_NAME]: CSRF_HASH,
      event_id: EVENT_ID,
      house_id: houseId,
      student_id: studentId
    }, function(res){
      if (res && res.ok){
        toastr?.success?.('Added');
        refreshCart();
        const q = $card.find('.q').val() || '';
        loadMembers($card, q);
      } else {
        toastr?.error?.(res?.msg || 'Failed');
      }
    }, 'json');
  });

  $(document).on('click', '.cart-del', function(){
    const id = Number($(this).data('entry'));
    if (!id) return;
    $.post('<?= base_url('admin/sports/entries/seats/remove') ?>',{
      [CSRF_NAME]: CSRF_HASH, entry_id: id
    }, function(res){
      if (res && res.ok){
        toastr?.success?.('Removed');
        refreshCart();
      } else {
        toastr?.error?.(res?.msg || 'Failed');
      }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
