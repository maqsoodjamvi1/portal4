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
      $parts = [];
      if ($diff->y > 0) $parts[] = $diff->y . 'Y';
      if ($diff->m > 0) $parts[] = $diff->m . 'M';
      if (!$parts) $parts[] = '0M';
      return count($parts) > 1 ? ($parts[0] . ' + ' . $parts[1]) : $parts[0];
    } catch (Throwable $e) { return ''; }
  }
}

// Photo URL (fallback avatar)
if (!function_exists('student_photo_url')) {
  function student_photo_url(?string $photo): string {
    if ($photo && trim($photo) !== '') {
      return base_url('uploads/' . ltrim($photo, '/'));
    }
    return base_url('resource/img/avatar-student.png');
  }
}
?>

<style>
/* Grid */
.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 14px;
}

/* Card base */
.member-card {
  position: relative;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 18px 14px 12px;
  text-align: center;
  transition: box-shadow .2s ease, transform .1s ease, border-color .2s ease;
  min-height: 230px;
}
.member-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0,0,0,.06);
}

/* Captain highlight */
.member-card.captain {
  border-color: #0ea5e9;            /* info-ish */
  box-shadow: 0 6px 22px rgba(14,165,233,.18);
}
.member-card .ribbon {
  position: absolute;
  top: 10px;
  left: -6px;
  background: #0ea5e9;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-top-right-radius: 10px;
  border-bottom-right-radius: 10px;
}

/* Avatar */
.member-avatar {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  object-fit: cover;
  display: inline-block;
  box-shadow: 0 0 0 2px rgba(0,0,0,.06);
  margin-bottom: 10px;
}

/* Text blocks */
.member-name {
  font-weight: 700;
  font-size: 15px;
  line-height: 1.2;
}
.member-age {
  display: inline-block;
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}
.member-class {
  font-size: 12px;
  color: #374151;
  margin-top: 6px;
}

/* Delete button (top-right) */
.member-actions {
  position: absolute;
  top: 8px;
  right: 8px;
}
.member-actions .btn {
  padding: 4px 8px;
}

/* Empty state */
.empty-state {
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 22px;
  text-align: center;
  color: #64748b;
}

/* Right column form tweaks */
.select2-container--default .select2-selection--single {
  height: 38px;
  border-radius: 8px;
}
</style>

<?= view('components/page_header', [
    'title' => 'Team Members: ' . ($team['team_name'] ?? ''),
    'icon' => 'fas fa-user-friends',
    'subtitle' => implode(' · ', array_filter([
        !empty($team['house_name']) || !empty($team['house_id'])
            ? 'House: ' . ($team['house_name'] ?? $team['house_id'])
            : null,
        !empty($team['event_name']) ? 'Event: ' . $team['event_name'] : null,
    ])) ?: null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Events', 'url' => base_url('admin/sports/events')],
        ['label' => 'Teams', 'url' => base_url('admin/sports/teams/event/' . ($team['event_id'] ?? ''))],
        ['label' => 'Members', 'active' => true],
    ],
]) ?>

<section class="content">
 <div class="row">
  <!-- LEFT: Members grid -->
  <div class="col-lg-7">
    <div class="card card-outline card-info">
      <div class="card-header">
        <h3 class="card-title">Team Members</h3>
      </div>
      <div class="card-body">
        <?php if (empty($members)): ?>
          <div class="empty-state">
            <i class="fas fa-users-slash"></i> No members yet. Use the form on the right to add students.
          </div>
        <?php else: ?>
          <div class="members-grid">
            <?php
              // Ensure captain(s) rendered first
              usort($members, function($a,$b){
                return (int)($b['is_captain'] ?? 0) <=> (int)($a['is_captain'] ?? 0);
              });
              foreach ($members as $m):
                $name = trim(($m['first_name'] ?? '').' '.($m['last_name'] ?? ''));
                if ($name === '') $name = 'ID '.(int)($m['student_id'] ?? 0);

                $age  = age_str($m['date_of_birth'] ?? null);
                $cs   = trim(($m['class_name'] ?? '') . ((($m['section_name'] ?? '') !== '') ? (' - ' . $m['section_name']) : ''));
                $photoUrl = student_photo_url($m['profile_photo'] ?? null);
                $isCaptain = !empty($m['is_captain']);
            ?>
              <div class="member-card <?= $isCaptain ? 'captain' : '' ?>">
                <?php if ($isCaptain): ?>
                  <div class="ribbon"><i class="fas fa-star"></i> Captain</div>
                <?php endif; ?>

                <div class="member-actions">
                  <button class="btn btn-sm btn-danger del" title="Remove" data-id="<?= (int)$m['stm_id'] ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>

                <img class="member-avatar" src="<?= esc($photoUrl) ?>" alt="photo">

                <div class="member-name"><?= esc($name) ?></div>
                <?php if ($age !== ''): ?>
                  <div class="member-age"><?= esc($age) ?></div>
                <?php endif; ?>

                <div class="member-class">
                  <?= $cs !== '' ? esc($cs) : '-' ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT: Add member -->
  <div class="col-lg-5">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Add Member</h3></div>
      <div class="card-body">
        <div class="form-group">
          <label>Student</label>
          <select id="student_id" class="form-control select2" style="width:100%"></select>
          <small class="form-text text-muted">Options show Name — Age | Class–Section.</small>
        </div>
        <div class="form-group">
          <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" id="is_captain" value="1">
            <label class="form-check-label" for="is_captain">Mark as Captain</label>
          </div>
        </div>
        <button id="addMember" class="btn btn-success">
          <i class="fas fa-user-plus"></i> Add
        </button>
      </div>
    </div>
  </div>
 </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';
const TEAM_ID   = '<?= (int)$team['team_id'] ?>';

$(function(){
  $('.select2').select2({width:'100%'});

  // Populate dropdown from your studentsOptions endpoint (already excluding taken-in-event and same-team).
  $.post("<?= base_url('admin/ajax/students-options') ?>", {
    [CSRF_NAME]: CSRF_HASH,
    team_id: TEAM_ID
  }, function(opts){
    $('#student_id').html(opts);
  }, 'html');

  $('#addMember').on('click', function(){
    const sid = $('#student_id').val();
    if(!sid){ toastr.warning('Please select a student'); return; }
    $.post("<?= base_url('admin/sports/team-members/add') ?>", {
      [CSRF_NAME]: CSRF_HASH,
      team_id: TEAM_ID,
      student_id: sid,
      is_captain: $('#is_captain').is(':checked') ? 1 : 0
    }, function(res){
      if(res.ok){ toastr.success('Member added'); location.reload(); }
      else { toastr.error(res.msg || 'Failed'); }
    }, 'json');
  });

  // Delete
  $(document).on('click', '.del', function(){
    const id = $(this).data('id');
    if(!confirm('Remove this member?')) return;
    $.post("<?= base_url('admin/sports/team-members/delete') ?>", {
      [CSRF_NAME]: CSRF_HASH,
      stm_id: id
    }, function(res){
      if(res.ok){ toastr.success('Removed'); location.reload(); }
      else { toastr.error(res.msg || 'Failed'); }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
