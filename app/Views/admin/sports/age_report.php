<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Age-wise Students Report</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports</a></li>
          <li class="breadcrumb-item active">Age-wise Students</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<style>
/* ===== General layout ===== */
.age-wrap{
  display:flex;
  flex-direction:column;
  gap:20px;
}

/* ===== Age section card ===== */
.age-section{
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#ffffff;
  box-shadow:0 2px 10px rgba(0,0,0,.04);
  padding:14px 16px 16px 16px;
  page-break-inside:avoid;
}

.age-header{
  display:flex;
  justify-content:space-between;
  align-items:baseline;
  margin-bottom:10px;
  flex-wrap:wrap;
  gap:6px;
}
.age-title{
  margin:0;
  font-weight:800;
  font-size:18px;
  color:#111827;
}
.age-sub{
  font-size:12px;
  color:#64748b;
}

/* ===== Student cards grid (no horizontal scroll; wrap into rows) ===== */
.stu-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(170px,1fr));
  gap:12px;
}

@media (max-width:576px){
  .stu-grid{
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
}

/* ===== Single student card ===== */
.stu-card{
  border:1px solid #e5e7eb;
  border-radius:12px;
  background:#f9fafb;
  padding:8px 8px 10px 8px;
  display:flex;
  flex-direction:column;
  align-items:center;
  text-align:center;
  page-break-inside:avoid;
}

/* House name + color at top */
.stu-house{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  margin-bottom:4px;
}
.house-dot{
  width:12px;
  height:12px;
  border-radius:50%;
  border:1px solid rgba(0,0,0,.1);
  flex:0 0 12px;
}
.house-name{
  font-size:13px;
  font-weight:700;
  color:#111827;
}

/* Round photo frame */
.stu-photo-wrap{
  width:70px;
  height:70px;
  border-radius:50%;
  overflow:hidden;
  border:3px solid #ffffff;
  box-shadow:0 2px 6px rgba(0,0,0,.15);
  display:flex;
  align-items:center;
  justify-content:center;
  background:#f2f5f9;
  margin:4px auto 4px auto;
}
.stu-photo-wrap img{
  width:100%;
  height:100%;
  object-fit:cover;
  border-radius:50%;
}

/* Name + meta */
.stu-name{
  font-size:14px;
  font-weight:700;
  color:#111827;
  margin-top:4px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  width:100%;
}
.stu-meta{
  font-size:12px;
  color:#64748b;
  margin-top:2px;
}
.stu-meta .dot{
  margin:0 6px;
  color:#cbd5e1;
}

/* Print-friendly tweaks */
@media print{
  body{
    -webkit-print-color-adjust:exact;
    print-color-adjust:exact;
  }
  .content-header,
  .main-header,
  .main-sidebar,
  .main-footer,
  .navbar,
  .btn,
  .breadcrumb{
    display:none !important;
  }
  .content-wrapper{
    margin:0 !important;
    padding:0 !important;
  }
}
</style>

<section class="content">
  <div class="container-fluid">
    <?php
      // Helper to pluralize "event(s)"
      $pluralize = function(int $n, string $word){
        return $n.' '.$word.($n === 1 ? '' : 's');
      };
    ?>

    <?php if (empty($byAge)): ?>
      <div class="alert alert-info">
        No students found for this campus/session.
      </div>
    <?php else: ?>
      <div class="age-wrap">
        <?php foreach ($byAge as $ageYears => $students): ?>
          <?php
            $ageLabel = $ageYears.' Year'.($ageYears == 1 ? '' : 's');
            $totalStudents = count($students);
          ?>
          <div class="age-section">
            <div class="age-header">
              <h3 class="age-title"><?= esc($ageLabel) ?></h3>
              <div class="age-sub">
                <?= $totalStudents ?> student<?= $totalStudents === 1 ? '' : 's' ?>
              </div>
            </div>

            <div class="stu-grid">
              <?php foreach ($students as $stu): ?>
                <?php
                  $fullName = trim(($stu['first_name'] ?? '') . ' ' . ($stu['last_name'] ?? ''));
                  if ($fullName === '') {
                    $fullName = 'ID '.$stu['student_id'];
                  }

                  $classShort = $stu['class_short'] ?? '';
                  $eventsCount = (int) ($stu['events_count'] ?? 0);
                  $metaParts = [];

                  // Age text
                  $metaParts[] = $ageYears.' Yr';

                  if ($classShort !== '') {
                    $metaParts[] = $classShort;
                  }

                  $metaParts[] = $pluralize($eventsCount, 'event');

                  $houseName  = $stu['house_name']  ?? '';
                  $houseColor = $stu['house_color'] ?? '#9ca3af';

                  $profilePhoto = $stu['profile_photo'] ?? '';
                  $imgSrc = $profilePhoto
                    ? base_url('uploads/'.ltrim($profilePhoto, '/'))
                    : base_url('resource/img/avatar-student.png');
                ?>

                <div class="stu-card">
                  <div class="stu-house">
                    <span class="house-dot" style="background: <?= esc($houseColor) ?>;"></span>
                    <span class="house-name">
                      <?= $houseName !== '' ? esc($houseName) : 'No House' ?>
                    </span>
                  </div>

                  <div class="stu-photo-wrap">
                    <img src="<?= esc($imgSrc) ?>" alt="<?= esc($fullName) ?>">
                  </div>

                  <div class="stu-name"><?= esc($fullName) ?></div>

                  <div class="stu-meta">
                    <?= implode('<span class="dot">·</span>', array_map('esc', $metaParts)) ?>
                  </div>
                </div>

              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?= $this->endSection() ?>
