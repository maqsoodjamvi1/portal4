<?php
helper('url');
$path = trim(service('uri')->getPath(), '/');
$isActive = static fn (string $needle): bool => $needle !== '' && str_starts_with($path, trim($needle, '/'));
?>
<nav class="parent-portal-bottomnav d-md-none no-print" aria-label="Quick navigation">
  <a href="<?= base_url('student/dashboard') ?>" class="<?= $isActive('student/dashboard') ? 'active' : '' ?>">
    <i class="fas fa-home"></i><span>Home</span>
  </a>
  <a href="<?= base_url('student/fees') ?>" class="<?= $isActive('student/fees') ? 'active' : '' ?>">
    <i class="fas fa-money-bill-wave"></i><span>Fees</span>
  </a>
  <a href="<?= base_url('student/attendance') ?>" class="<?= $isActive('student/attendance') ? 'active' : '' ?>">
    <i class="fas fa-calendar-check"></i><span>Attendance</span>
  </a>
  <a href="<?= base_url('student/results') ?>" class="<?= $isActive('student/results') ? 'active' : '' ?>">
    <i class="fas fa-chart-line"></i><span>Results</span>
  </a>
  <a href="<?= base_url('student/quizzes') ?>" class="<?= $isActive('student/quizzes') ? 'active' : '' ?>">
    <i class="fas fa-question-circle"></i><span>Quizzes</span>
  </a>
</nav>
<style>
.parent-portal-bottomnav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1030;
  display: flex;
  background: #fff;
  border-top: 1px solid #dee2e6;
  padding: 0.25rem 0;
  box-shadow: 0 -4px 12px rgba(0,0,0,0.06);
}
.parent-portal-bottomnav a {
  flex: 1;
  text-align: center;
  font-size: 0.65rem;
  color: #64748b;
  padding: 0.35rem 0.15rem;
  text-decoration: none;
}
.parent-portal-bottomnav a i {
  display: block;
  font-size: 1.1rem;
  margin-bottom: 0.15rem;
}
.parent-portal-bottomnav a.active {
  color: #4f46e5;
}
@media (min-width: 768px) {
  .parent-portal-bottomnav { display: none !important; }
}
body:not(.quiz-attempt-page) .content-wrapper {
  padding-bottom: 4.5rem;
}
@media (min-width: 768px) {
  body:not(.quiz-attempt-page) .content-wrapper { padding-bottom: 0; }
}
</style>
