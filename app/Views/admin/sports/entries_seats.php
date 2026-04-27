<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"/>

<?php
// --- Helpers in-view (kept tiny so you can move them to a helper later) ---

if (!function_exists('age_years_round')) {
  function age_years_round(?string $dob): string {
    if (!$dob) return '';
    try {
      $d = new DateTime($dob);
      $now = new DateTime('today');
      if ($d > $now) return '';
      $diff = $d->diff($now);
      $y = (int)$diff->y;
      $m = (int)$diff->m;
      if ($m > 6) $y += 1; // >6 months ? round up
      return $y . ' Yr';
    } catch (Throwable $e) {
      return '';
    }
  }
}

if (!function_exists('student_photo_url')) {
  function student_photo_url(?string $photo): string {
    if ($photo && trim($photo) !== '') return base_url('uploads/' . ltrim($photo,'/'));
    return base_url('resource/img/avatar-student.png');
  }
}
?>

<style>
.house-wrap { display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:16px; }
@media (min-width:1200px){ .house-wrap { grid-template-columns: repeat(4,minmax(0,1fr)); } }

.house-card{
  border:1px solid #e5e7eb; border-radius:14px; background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.03);
}
.house-head{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 14px; border-bottom:1px solid #f1f5f9; }
.house-title{ font-weight:700; margin:0; }
.cap-chip{ font-size:12px; color:#475569; }

.cart-area{ padding:10px 12px 4px; min-height:92px; display:flex; gap:10px; flex-wrap:wrap; }
.cart-item{ position:relative; border:1px solid #e5e7eb; border-radius:10px; padding:6px 8px; display:flex; gap:8px; align-items:center; }
.cart-item img{ width:38px; height:38px; border-radius:50%; object-fit:cover; }
.cart-name{ font-size:13px; font-weight:700; line-height:1.15; }
.cart-meta{ font-size:11px; color:#334155; }
.cart-del{ position:absolute; top:-8px; right:-8px; border:none; width:20px; height:20px; border-radius:999px; background:#ef4444; color:#fff; line-height:20px; text-align:center; cursor:pointer; }

.tools{ padding:6px 12px; display:flex; gap:8px; }
.tools .search{ flex:1; }
.tools input[type="text"]{ width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:6px 8px; font-size:13px; }

.members{ padding:0 12px 12px; display:grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap:10px; }
.member{
  border:1px solid #e5e7eb; border-radius:12px; background:#fff; padding:10px; text-align:center; cursor:pointer;
  transition: box-shadow .15s ease, transform .1s ease;
}
.member:hover{ transform:translateY(-1px); box-shadow:0 8px 22px rgba(0,0,0,.06); }
.member img{ width:72px; height:72px; border-radius:50%; object-fit:cover; margin-bottom:6px; }
.member .name{ font-weight:700; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.member .meta{ font-size:12px; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.member.disabled{ opacity:.45; pointer-events:none; }
.capacity-full{ opacity:.6; }
.small-muted{ font-size:12px; color:#64748b; }
</style>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Assign Participants — <?= esc($event['event_name'] ?? 'Event') ?></h1>
        <div class="small-muted">
          Gender: <b><?= esc(ucfirst($event['gender'] ?? '-')) ?></b> • Seats/House: <b><?= (int)$event['per_house_count'] ?></b>
        </div>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports Events</a></li>
          <li class="breadcrumb-item active">Assign</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Per House Selection</h3>
    </div>
    <div class="card-body">

      <div id="houses" class="house-wrap">
        <?php
          $CAP = (int)($event['per_house_count'] ?? 0);
          $cart = $cart ?? [];
          foreach (($houses ?? []) as $h):
            $hid = (int)$h['house_id'];
            $cur = $cart[$hid] ?? [];
            $count = count($cur);
        ?>
        <div class="house-card" data-house="<?= $hid ?>">
          <div class="house-head <?= ($count >= $CAP && $CAP>0) ? 'capacity-full' : '' ?>">
            <h5 class="house-title mb-0"><?= esc($h['house_name']) ?></h5>
            <div class="cap-chip">
              <span class="sel-count"><?= (int)$count ?></span> / <span class="cap"><?= (int)$CAP ?></span> selected
            </div>
          </div>

          <!-- Cart -->
          <div class="cart-area">
            <?php foreach ($cur as $row):
              $name = trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? ''));
              if ($name==='') $name = 'ID '.$row['student_id'];
              $metaBits = [];
              $cls = trim((string)($row['class_short'] ?? ''));
              if ($cls!=='') $metaBits[] = $cls;
              $age = age_years_round($row['date_of_birth'] ?? null);
              if ($age!=='') $metaBits[] = $age;
              $meta = implode(' • ', $metaBits);
            ?>
            <div class="cart-item" data-entry="<?= (int)$row['entry_id'] ?>">
              <button class="cart-del" title="Remove" data-entry="<?= (int)$row['entry_id'] ?>">×</button>
              <img src="<?= esc(student_photo_url($row['profile_photo'] ?? null)) ?>" alt="">
              <div>
                <div class="cart-name"><?= esc($name) ?></div>
                <div class="cart-meta"><?= esc($meta) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Tools -->
          <div class="tools">
            <div class="search">
              <input type="text" class="q" placeholder="Search by name…">
            </div>
            <button class="btn btn-sm btn-outline-secondary reload">Reload</button>
          </div>

          <!-- Members list -->
          <div class="members">
            <div class="small-muted">Type to search and click a student to add.</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';
const EVENT_ID  = <?= (int)$eventId ?>;

function esc(s){ return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
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

function memberCard(houseId, r, disabled){
  const name = esc((r.first_name||'')+' '+(r.last_name||'')).trim() || ('ID '+r.student_id);
  const cls  = esc(r.class_short || '');
  const age  = ageYearsRounded(r.date_of_birth||'');        // e.g. "12 Yr"
  const cnt  = Number(r.participation_count || 0);          // new: count
  const bits = [];
  if (cls) bits.push(cls);
  if (age) bits.push(age);
  bits.push(String(cnt));                                   // always show count
  const meta = bits.join(' • ');

  const img  = esc(r.profile_photo ? '<?= base_url('uploads/') ?>'+String(r.profile_photo).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>');

  return `
    <div class="member ${disabled ? 'disabled':''}" data-house="${houseId}" data-student="${r.student_id}">
      <img src="${img}" alt="">
      <div class="name" title="${name}">${name}</div>
      <div class="meta" title="${meta}">${meta}</div>
    </div>`;
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

function refreshCart(){
  $.post('<?= base_url('admin/sports/entries/seats/cart') ?>',{
    [CSRF_NAME]: CSRF_HASH,
    event_id: EVENT_ID
  }, function(res){
    if(!res || !res.ok) return;
    const cart = res.cart || {};
    $('#houses .house-card').each(function(){
      const $h = $(this), hid = Number($h.data('house')), cap = Number($h.find('.cap').text()||'0');
      const arr = cart[hid] || [];
      const html = arr.map(row => {
        const name = esc((row.first_name||'')+' '+(row.last_name||'')).trim() || ('ID '+row.student_id);
        const cls  = esc(row.class_short||'');
        const age  = ageYearsRounded(row.date_of_birth||'');
        const meta = [cls, age].filter(Boolean).join(' • ');
        const img  = esc(row.profile_photo ? '<?= base_url('uploads/') ?>'+String(row.profile_photo).replace(/^\/+/,'') : '<?= base_url('resource/img/avatar-student.png') ?>');
        return `
          <div class="cart-item" data-entry="${row.entry_id}">
            <button class="cart-del" title="Remove" data-entry="${row.entry_id}">×</button>
            <img src="${img}" alt="">
            <div>
              <div class="cart-name">${name}</div>
              <div class="cart-meta">${meta}</div>
            </div>
          </div>`;
      }).join('');
      $h.find('.cart-area').html(html);
      $h.find('.sel-count').text(arr.length);
      if (cap>0 && arr.length>=cap) $h.find('.house-head').addClass('capacity-full');
      else $h.find('.house-head').removeClass('capacity-full');

      // Re-disable/enable member list based on capacity
      const full = (cap>0 && arr.length>=cap);
      $h.find('.members .member').toggleClass('disabled', full);
    });
  }, 'json');
}

$(function(){
  // Initial load of members for all houses
  $('#houses .house-card').each(function(){ loadMembers($(this)); });

  // Search per house (debounced)
  let t=null;
  $(document).on('input', '.house-card .q', function(){
    const $card = $(this).closest('.house-card');
    const q = $(this).val();
    clearTimeout(t);
    t = setTimeout(()=> loadMembers($card, q), 250);
  });

  // Manual reload
  $(document).on('click', '.house-card .reload', function(){
    const $card = $(this).closest('.house-card');
    const q = $card.find('.q').val() || '';
    loadMembers($card, q);
  });

  // Click a member => add to cart
  $(document).on('click', '.members .member', function(){
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
        // Also reload member list (so the added one disappears)
        const q = $card.find('.q').val() || '';
        loadMembers($card, q);
      } else {
        toastr?.error?.(res?.msg || 'Failed');
      }
    }, 'json');
  });

  // Remove from cart
  $(document).on('click', '.cart-del', function(){
    const id = Number($(this).data('entry'));
    if (!id) return;
    $.post('<?= base_url('admin/sports/entries/seats/remove') ?>',{
      [CSRF_NAME]: CSRF_HASH,
      entry_id: id
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
