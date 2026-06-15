<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
  $name   = $name ?? 'Student';
?>

<style>
.student-hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px 18px;
  border-radius: 16px;
  background: radial-gradient(circle at top left, #e0f2fe, #eef2ff);
  box-shadow: 0 2px 14px rgba(15, 23, 42, .08);
}
.student-hero-left h4 {
  margin-bottom: 4px;
  font-weight: 700;
  color: #0f172a;
}
.student-hero-left small {
  color: #6b7280;
}
.student-hero-avatar {
  width: 60px;
  height: 60px;
  border-radius: 999px;
  background: linear-gradient(135deg, #6366f1, #22c55e);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 800;
  font-size: 26px;
  box-shadow: 0 3px 10px rgba(15,23,42,.25);
}

/* Main feature cards */
.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
  margin-top: 16px;
}
.feature-card {
  border-radius: 14px;
  padding: 12px 14px;
  background: #ffffff;
  box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform .18s ease, box-shadow .18s ease;
}
.feature-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(15, 23, 42, .10);
}
.feature-icon {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  margin-bottom: 8px;
}
.feature-title {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 4px;
}
.feature-text {
  font-size: 12px;
  color: #6b7280;
}

/* Colors per module */
.bg-results   { background: linear-gradient(135deg,#22c55e,#16a34a); }
.bg-datesheet { background: linear-gradient(135deg,#6366f1,#4f46e5); }
.bg-diary     { background: linear-gradient(135deg,#f97316,#fb923c); }
.bg-time      { background: linear-gradient(135deg,#0ea5e9,#06b6d4); }
.bg-events    { background: linear-gradient(135deg,#ec4899,#f97316); }
.bg-quizzes   { background: linear-gradient(135deg,#a855f7,#6366f1); }

/* Small “today” strip */
.today-strip {
  margin-top: 18px;
  padding: 10px 14px;
  border-radius: 12px;
  background: #f9fafb;
  border: 1px dashed #e5e7eb;
  font-size: 12px;
  color: #4b5563;
}

/* Responsive tweak */
@media (max-width: 575.98px) {
  .student-hero {
    flex-direction: column;
    align-items: flex-start;
  }
  .student-hero-avatar {
    align-self: flex-end;
  }
}
</style>

<div class="card shadow-sm">
  <div class="card-body">

    <!-- Hero -->
    <div class="student-hero mb-3">
      <div class="student-hero-left">
        <h4>Welcome, <?= esc($name) ?> 👋</h4>
        <small>Check your results, quizzes, timetable and more from one place.</small>
      </div>
      <div class="student-hero-avatar">
        <?php
          $initial = mb_substr($name, 0, 1);
          echo esc(mb_strtoupper($initial));
        ?>
      </div>
    </div>

    <!-- Feature cards -->
    <div class="feature-grid">
      <a href="<?= base_url('student/results') ?>" class="text-decoration-none text-reset">
        <div class="feature-card">
          <div>
            <div class="feature-icon bg-results">
              <i class="fa fa-chart-line"></i>
            </div>
            <div class="feature-title">Results</div>
            <div class="feature-text">View your exam and test scores term-wise and subject-wise.</div>
          </div>
        </div>
      </a>

      <a href="<?= base_url('student/datesheet') ?>" class="text-decoration-none text-reset">
        <div class="feature-card">
          <div>
            <div class="feature-icon bg-datesheet">
              <i class="fa fa-calendar-alt"></i>
            </div>
            <div class="feature-title">Datesheet</div>
            <div class="feature-text">See upcoming exam schedule and plan your preparation.</div>
          </div>
        </div>
      </a>

      <a href="<?= base_url('student/diary') ?>" class="text-decoration-none text-reset">
        <div class="feature-card">
          <div>
            <div class="feature-icon bg-diary">
              <i class="fa fa-book-open"></i>
            </div>
            <div class="feature-title">Daily Diary</div>
            <div class="feature-text">Check today’s homework, classwork and teacher notes.</div>
          </div>
        </div>
      </a>

      <a href="<?= base_url('student/timetable') ?>" class="text-decoration-none text-reset">
        <div class="feature-card">
          <div>
            <div class="feature-icon bg-time">
              <i class="fa fa-clock"></i>
            </div>
            <div class="feature-title">Timetable</div>
            <div class="feature-text">See your daily and weekly class timetable in one click.</div>
          </div>
        </div>
      </a>

      <a href="<?= base_url('student/events') ?>" class="text-decoration-none text-reset">
        <div class="feature-card">
          <div>
            <div class="feature-icon bg-events">
              <i class="fa fa-bullhorn"></i>
            </div>
            <div class="feature-title">Events</div>
            <div class="feature-text">Stay updated about school events, trips and activities.</div>
          </div>
        </div>
      </a>

      <a href="<?= base_url('student/quizzes') ?>" class="text-decoration-none text-reset">
        <div class="feature-card">
          <div>
            <div class="feature-icon bg-quizzes">
              <i class="fa fa-question-circle"></i>
            </div>
            <div class="feature-title">Quizzes</div>
            <div class="feature-text">Start practice or graded quizzes assigned by teachers.</div>
          </div>
        </div>
      </a>
    </div>

    <!-- Today strip (you can later bind with real data) -->
    <div class="today-strip mt-3">
      <i class="fa fa-info-circle"></i>
      &nbsp;Tip: Check your datesheet and timetable regularly so you never miss a class or exam.
    </div>

  </div>
</div>

<?= $this->endSection() ?>
