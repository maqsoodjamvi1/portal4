<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

<?php
// Age helper: "8Y + 6M"
if (!function_exists('age_str')) {
  function age_str(?string $dob): string {
    if (!$dob) return '';
    try {
      $d = new DateTime($dob);
      $now = new DateTime('today');
      if ($d > $now) return '';

      $diff = $d->diff($now);
      $y = (int)$diff->y;
      $m = (int)$diff->m;

      // Rounding rule:
      if ($m >= 6) {
        $y += 1;
      }

      return "{$y}Y";
    } catch (Throwable $e) {
      return '';
    }
  }
}
// Photo URL fallback
if (!function_exists('student_photo_url')) {
  function student_photo_url(?string $photo): string {
    if ($photo && trim($photo) !== '') {
      return base_url('uploads/' . ltrim($photo, '/'));
    }
    return base_url('resource/img/avatar-student.png'); // adjust if needed
  }
}
?>

<style>
/* House container card */

.house-title {
  font-weight: 700; margin: 0;
}
.house-count {
  font-size: 12px; color: #64748b;
}
/* Grid of students inside a house */
.students-grid {
  padding: 14px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
}
/* Student card */

.student-name { font-weight: 700; font-size: 14px; line-height: 1.2; }
.student-age  { font-size: 12px; color: #6b7280; margin-top: 2px; }
.student-class{ font-size: 12px; color: #374151; margin-top: 6px; }
/* Delete button in card corner */
.card-actions { position: absolute; top: 8px; right: 8px; }
.card-actions .btn { padding: 3px 6px; }
.empty-state {
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  color: #64748b;
}

.house-card{
  display:inline-block;           /* key: no full row */
  vertical-align: top;
  border:1px solid #e5e7eb;
  border-radius:14px;
  margin:10px 12px 14px 0;
  background:#fff;
  box-shadow:0 2px 10px rgba(0,0,0,.03);
  padding:12px 12px 10px;
  width:auto;                     /* key */
  max-width:100%;
}

/* Header inside house card */
.house-header{
  display:flex;align-items:center;gap:10px;justify-content:space-between;
  margin-bottom:8px;border-bottom:1px solid #f1f5f9;padding-bottom:6px;
}

/* Students laid out in a single horizontal strip; width = sum of children */
.students-strip{
  display:flex;
  gap:10px;
  align-items:stretch;
  flex-wrap:nowrap;               /* key: card width grows by #participants */
}

/* Student card size */
.student-card{
  width:160px;                    /* fixed small card */
  position:relative;
  text-align:center;
  border:1px solid #e5e7eb;
  border-radius:12px;
  padding:12px 10px 10px;
  background:#fff;
  min-height:200px;
  transition:box-shadow .15s ease, transform .1s ease;
}
.student-card:hover{ transform: translateY(-1px); box-shadow:0 8px 22px rgba(0,0,0,.06); }

.student-avatar{
  width:82px;height:82px;border-radius:50%;object-fit:cover;
  display:inline-block;box-shadow:0 0 0 2px rgba(0,0,0,.06);margin-bottom:8px;
}

/* S.No bubble overlay */
.member-sno{
  position:absolute; top:8px; left:8px;
  width:28px; height:28px; line-height:28px; border-radius:999px;
  background:#111827; color:#fff; font-weight:700; font-size:12px;
}

/* Name under picture */
.student-name{
  font-weight:700;font-size:13px;line-height:1.15;margin-top:2px;
}

/* Single-line meta: class short, age, count — all black */
.student-meta{
  margin-top:6px;
  font-size:12px;
  color:#000;                     /* black */
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
</style>

<?= view('components/page_header', [
    'title' => 'Event Entries',
    'icon' => 'fas fa-list-ol',
    'subtitle' => $event['event_name'] ?? null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Events', 'url' => base_url('admin/sports/events')],
        ['label' => 'Entries', 'active' => true],
    ],
]) ?>

<?php $isTeam = (strtolower($event['event_type'] ?? '') === 'team'); ?>

<section class="content">
  <!-- TOP: Add Participant (unchanged from your version) -->
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Add Participant (Type: <?= esc(strtoupper($event['event_type'] ?? '')) ?>)</h3>
        </div>
        <div class="card-body">
          <?php if ($isTeam): ?>
            <div class="row">
              <div class="form-group col-md-6">
                <label>Team</label>
                <select id="team_id" class="form-control select2" style="width:100%">
                  <option value="">-- Select Team --</option>
                  <?php foreach(($teams ?? []) as $t): ?>
                    <option value="<?= (int)$t['team_id'] ?>"><?= esc($t['team_name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                  Need a new team? <a href="<?= base_url('admin/sports/teams/event/'.$event['event_id']) ?>">Create here</a>.
                </small>
              </div>
            </div>
          <?php else: ?>
            <!-- INDIVIDUAL MODE filters: Event -> House -> Student -->
            <div class="row">
              <div class="form-group col-md-4">
                <label>Event</label>
                <select id="event_id" class="form-control select2" style="width:100%">
                  <option value="<?= (int)$event['event_id'] ?>">
                    <?= esc($event['event_name']) ?> <?= !empty($event['gender']) ? '('.esc(strtolower($event['gender'])).')' : '' ?>
                  </option>
                </select>
                <small class="form-text text-muted">Select event first — gender filters eligible students.</small>
              </div>
              <div class="form-group col-md-4">
                <label>House</label>
                <select id="house_id" class="form-control select2" style="width:100%">
                  <option value="">-- Select House --</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label>Student</label>
                <select id="student_id" class="form-control select2" style="width:100%">
                  <option value="">-- Select Student --</option>
                </select>
                <small class="form-text text-muted">
                  Shows only students of selected house with matching gender who haven’t participated in the selected event.
                </small>
              </div>
            </div>
          <?php endif; ?>

          <button id="addEntry" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
        </div>
      </div>
    </div>
  </div>

  <!-- BOTTOM: ENTRIES — cards grouped by House (INDIVIDUAL) or keep table (TEAM) -->
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-outline card-info">
        <div class="card-header"><h3 class="card-title">Entries</h3></div>
        <div class="card-body">
          <?php if ($isTeam): ?>
            <!-- Keep your team table (teams don’t need student cards here) -->
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Team</th>
                    <th>House</th>
                    <th width="80">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i=1; foreach(($entries??[]) as $e): ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><?= esc($e['team_name'] ?? '-') ?></td>
                      <td><?= esc($e['house_name'] ?? $e['house_id']) ?></td>
                      <td>
                        <button class="btn btn-sm btn-danger del" data-id="<?= (int)$e['entry_id'] ?>">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($entries)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No entries yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <?php
              // Group students by house
              $byHouse = [];
              foreach (($entries ?? []) as $e) {
                $hid = (int)($e['house_id'] ?? 0);
                $hname = trim((string)($e['house_name'] ?? 'House '.$hid));
                if (!isset($byHouse[$hid])) $byHouse[$hid] = ['name' => $hname, 'items' => []];
                $byHouse[$hid]['items'][] = $e;
              }
            ?>

            <?php if (empty($byHouse)): ?>
              <div class="empty-state"><i class="fas fa-users"></i> No entries yet.</div>
            <?php else: ?>
              <?php foreach ($byHouse as $hid => $bucket): ?>
                <div class="house-card">
                  <div class="house-header">
                    <h5 class="house-title mb-0"><?= esc($bucket['name']) ?></h5>
                    <div class="house-count"><?= count($bucket['items']) ?> student(s)</div>
                  </div>

                  <div class="students-grid">
      <?php foreach ($bucket['items'] as $e): 
  $name = trim(($e['first_name'] ?? '').' '.($e['last_name'] ?? ''));
  if ($name === '') $name = 'ID '.(int)($e['student_id'] ?? 0);

  $age       = age_str($e['date_of_birth'] ?? null);                // "13Y" (rounded by ≥/≤ rule you set)
  $classShort= trim((string)($e['class_short'] ?? ''));             // from controller alias
  $count     = (int)($e['participation_count'] ?? 0);               // from controller subquery
  $photoUrl  = student_photo_url($e['profile_photo'] ?? null);

  // single black line: CLASS AGE COUNT (always show count, even 0)
  $metaParts = [];
  if ($classShort !== '') $metaParts[] = $classShort;
  if ($age !== '')        $metaParts[] = $age;
  $metaParts[] = (string)$count;
  $meta = implode(' ', $metaParts);
?>
  <div class="student-card">
    <div class="card-actions">
      <button class="btn btn-sm btn-danger del" title="Remove" data-id="<?= (int)$e['entry_id'] ?>">
        <i class="fas fa-trash"></i>
      </button>
    </div>

    <img class="student-avatar" src="<?= esc($photoUrl) ?>" alt="photo">
    <div class="student-name"><?= esc($name) ?></div>
    <div class="student-meta" title="<?= esc($meta) ?>"><?= esc($meta) ?></div>
  </div>
<?php endforeach; ?>                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';
const PAGE_EVENT_ID = '<?= (int)($eventId ?? $event['event_id']) ?>';
const TYPE = '<?= esc(strtolower($event['event_type'] ?? '')) ?>'; // 'team' | 'individual'

$(function(){
  $('.select2').select2({ width:'100%' });

  if (TYPE !== 'team') {
    // Load individual events
    $.post("<?= base_url('admin/sports/events/data') ?>", { [CSRF_NAME]: CSRF_HASH }, function(resp){
      const data = (resp && resp.data) ? resp.data : [];
      let opts = '';
      data
        .filter(e => String(e.event_type).toLowerCase() === 'individual')
        .sort((a,b)=> String(a.event_name).localeCompare(String(b.event_name)))
        .forEach(e => {
          const sel = (String(e.event_id) === String(PAGE_EVENT_ID)) ? 'selected' : '';
          const g   = (e.gender || '').toLowerCase();
          opts += `<option value="${e.event_id}" ${sel}>${e.event_name}${g ? ' ('+g+')' : ''}</option>`;
        });
      $('#event_id').html(opts).trigger('change');
    }, 'json');

    // Load houses
    $.post("<?= base_url('admin/sports/houses/data') ?>", { [CSRF_NAME]: CSRF_HASH }, function(resp){
      let opts = '<option value="">-- Select House --</option>';
      (resp.data || []).forEach(r => opts += `<option value="${r.house_id}">${r.house_name}</option>`);
      $('#house_id').html(opts);
    }, 'json');

    // Load students for individual event (gender-aware, excluding participants)
    function loadStudents() {
      const evId  = $('#event_id').val();
      const house = $('#house_id').val();
      if (!evId || !house) {
        $('#student_id').html('<option value="">-- Select Student --</option>');
        return;
      }
      $.post("<?= base_url('admin/ajax/individual-students-options') ?>", {
        [CSRF_NAME]: CSRF_HASH,
        event_id: evId,
        house_id: house
      }, function(html){
        $('#student_id').html(html);
      }, 'html');
    }
    $('#event_id').on('change', loadStudents);
    $('#house_id').on('change', loadStudents);
  }

  // Add entry
  $('#addEntry').on('click', function(){
    const payload = { [CSRF_NAME]: CSRF_HASH };

    if (TYPE === 'team') {
      payload.event_id = '<?= (int)($eventId ?? $event['event_id']) ?>';
      payload.team_id  = $('#team_id').val();
      if (!payload.team_id) { toastr.warning('Please select a team'); return; }
    } else {
      payload.event_id   = $('#event_id').val();
      payload.house_id   = $('#house_id').val();
      payload.student_id = $('#student_id').val();
      if (!payload.event_id)   { toastr.warning('Please select an event'); return; }
      if (!payload.house_id)   { toastr.warning('Please select a house'); return; }
      if (!payload.student_id) { toastr.warning('Please select a student'); return; }
    }

    $.post("<?= base_url('admin/sports/entries/add') ?>", payload, function(res){
      if (res.ok) { toastr.success('Added'); location.reload(); }
      else { toastr.error(res.msg || 'Failed'); }
    }, 'json');
  });

  // Delete entry
  $(document).on('click', '.del', function(){
    const id = $(this).data('id');
    if (!confirm('Remove this participant?')) return;
    $.post("<?= base_url('admin/sports/entries/delete') ?>", {
      [CSRF_NAME]: CSRF_HASH,
      entry_id: id
    }, function(res){
      if (res.ok) { toastr.success('Removed'); location.reload(); }
      else { toastr.error(res.msg || 'Failed'); }
    }, 'json');
  });
});

function roundedAgeYears(dob) {
  if (!dob) return '';
  const d = new Date(dob);
  if (isNaN(d)) return '';
  const now = new Date();
  // diff in months
  let months = (now.getFullYear() - d.getFullYear())*12 + (now.getMonth() - d.getMonth());
  if (now.getDate() < d.getDate()) months -= 1; // approximate borrow
  const years = Math.floor(months/12);
  const remM  = months % 12;
  return (remM > 6) ? (years+1)+'Y' : years+'Y';
}
function photoURL(p){
  if (p && String(p).trim() !== '') return '<?= base_url('uploads/') ?>'+String(p).replace(/^\/+/,'');
  return '<?= base_url('resource/img/avatar-student.png') ?>';
}

function cardsHTML(list){
  const rows = (list||[]).map((r,i)=>{
    const sno   = String(i+1).padStart(2,'0');
    const name  = escHtml(r.full_name || `${r.first_name||''} ${r.last_name||''}`.trim());
    const klass = escHtml(r.class_short || r.class_short_name || r.class_name || '');

    // you already return "nY" (e.g. "4Y") from roundedAgeYears
    const ageRaw = roundedAgeYears(r.date_of_birth || r.dob || ''); // e.g. "4Y"
    const ageNum = ageRaw ? parseInt(String(ageRaw).replace(/\D+/g,''), 10) : null;
    const ageFmt = Number.isFinite(ageNum) ? `${ageNum} Yr` : '';   // "4 Yr"

    // count (always show, even if 0)
    let cntRaw = r.participation_count;
    let cnt = (cntRaw === undefined || cntRaw === null || cntRaw === '') ? 0 : Number(cntRaw);
    if (Number.isNaN(cnt)) cnt = 0;

    const img = escHtml(photoURL(r.profile_photo || ''));

    // Exactly: "PG - 4 Yr - 3"
    const meta = [klass, ageFmt, String(cnt)].filter(Boolean).join(' - ');

    return `
      <div class="student-card">
        <div class="member-sno">${sno}</div>
        <img class="student-avatar" src="${img}" alt="${name}">
        <div class="student-name" title="${name}">${name}</div>
        <div class="student-meta" title="${meta}">${meta}</div>
      </div>`;
  }).join('');

  return rows ? `<div class="students-strip">${rows}</div>`
              : `<div class="text-muted">No members</div>`;
}
</script>

<?= $this->endSection() ?>
