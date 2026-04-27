<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<?php
  $activeId     = (int) ($activeStudentId ?? 0);
  $feeSummary   = $feeSummary ?? ['total_outstanding' => 0, 'by_student' => []];
  $byStudent    = $feeSummary['by_student'] ?? [];
  $totalDue     = (float) ($feeSummary['total_outstanding'] ?? 0);
  $sportsEvents = $sportsEvents ?? [];
  $parentName   = $name ?? '';
  $quizzes      = $quizzes ?? [];
  $children     = $children ?? [];

  // Group quizzes by student
  $quizzesByStudent = [];
  $quizCounter = 1;
  
  foreach ($quizzes as $quiz) {
      $studentId = (int) ($quiz['student_id'] ?? 0);
      $quizStatus = $quiz['quiz_status'] ?? 'live';
      
      if ($quizStatus === 'closed') continue;
      
      if (!isset($quizzesByStudent[$studentId])) {
          $quizzesByStudent[$studentId] = [
              'student_info' => [
                  'student_id' => $studentId,
                  'name' => $quiz['student_name'] ?? ($quiz['student_full_name'] ?? ''),
                  'class' => trim(($quiz['class_name'] ?? '') . ' ' . ($quiz['section_name'] ?? '')),
                  'profile_photo' => $quiz['student_photo_url'] ?? base_url('resource/img/avatar-student.png'),
                  'reg_no' => $quiz['reg_no'] ?? ''
              ],
              'quizzes' => [],
              'stats' => [
                  'total_quizzes' => 0,
                  'total_attempts' => 0,
                  'available_attempts' => 0
              ]
          ];
      }
      
      // Add sequential number to each quiz
      $quiz['sno'] = $quizCounter++;
      $quizzesByStudent[$studentId]['quizzes'][] = $quiz;
      
      $attempts = (int)($quiz['attempts_count'] ?? 0);
      $maxAttempts = (int)($quiz['max_attempts'] ?? 0);
      
      $quizzesByStudent[$studentId]['stats']['total_quizzes']++;
      $quizzesByStudent[$studentId]['stats']['total_attempts'] += $attempts;
      
      if ($maxAttempts === 0) {
          $quizzesByStudent[$studentId]['stats']['available_attempts'] += 999;
      } else {
          $quizzesByStudent[$studentId]['stats']['available_attempts'] += max(0, $maxAttempts - $attempts);
      }
  }
?>

<style>
/* ===== COMPACT QUIZ LAYOUT ===== */
.compact-quiz-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
  margin-top: 20px;
}

/* Student Card */
.student-quiz-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  margin-bottom: 20px;
}

.student-header-compact {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  padding: 18px 20px;
  color: white;
  display: flex;
  align-items: center;
  gap: 15px;
}

.student-avatar-compact {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 3px solid rgba(255, 255, 255, 0.3);
  object-fit: cover;
}

.student-info-compact {
  flex: 1;
}

.student-name-compact {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 4px;
}

.student-class-compact {
  font-size: 0.9rem;
  opacity: 0.9;
}

.student-stats-compact {
  display: flex;
  gap: 15px;
  padding: 15px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
}

.student-stat-item {
  text-align: center;
  flex: 1;
}

.student-stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  display: block;
  line-height: 1;
}

.student-stat-label {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 4px;
}

/* COMPACT QUIZ LIST - Main Improvement */
.compact-quiz-list {
  padding: 0;
}

.quiz-item-compact {
  padding: 15px 20px;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.2s ease;
}

.quiz-item-compact:hover {
  background: #f8fafc;
}

.quiz-item-compact:last-child {
  border-bottom: none;
}

/* Quiz Header Row - Compact & Clear */
.quiz-header-compact {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 2px solid #f1f5f9;
}

.quiz-sno {
  min-width: 40px;
  height: 40px;
  background: #4f46e5;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
}

.quiz-subject-compact {
  flex: 1;
}

.quiz-title-main {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.quiz-title-main i {
  color: #4f46e5;
}

.quiz-topics {
  font-size: 0.85rem;
  color: #64748b;
  margin-top: 2px;
}

.quiz-attempts-badge {
  min-width: 100px;
  text-align: right;
}

.attempts-remaining {
  display: inline-block;
  padding: 6px 12px;
  background: #ecfdf5;
  color: #059669;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
  border: 1px solid #a7f3d0;
}

.attempts-unlimited {
  background: #eff6ff;
  color: #3b82f6;
  border-color: #bfdbfe;
}

/* Quiz Details Row */
.quiz-details-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.quiz-meta-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  color: #64748b;
}

.meta-item i {
  color: #4f46e5;
  font-size: 0.9rem;
}

.quiz-status-compact {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-live-compact {
  background: #dcfce7;
  color: #15803d;
}

.status-upcoming-compact {
  background: #fef3c7;
  color: #92400e;
}

/* Actions Row */
.quiz-actions-compact {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 15px;
}

/* Review Attempts - Eye Icons */
.review-attempts {
  display: flex;
  align-items: center;
  gap: 8px;
}

.review-label {
  font-size: 0.8rem;
  color: #64748b;
}

.review-icons {
  display: flex;
  gap: 5px;
}

.eye-icon-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #e0e7ff;
  color: #4f46e5;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: all 0.2s ease;
  font-size: 0.9rem;
}

.eye-icon-btn:hover {
  background: #4f46e5;
  color: white;
  transform: scale(1.1);
}

/* Action Buttons - Compact */
.action-buttons-compact {
  display: flex;
  gap: 10px;
}

.btn-compact {
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
  text-decoration: none;
}

.btn-view-detail {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
}

.btn-view-detail:hover {
  background: #e2e8f0;
  color: #334155;
}

.btn-play-compact {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.btn-play-compact:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-practice-compact {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.btn-practice-compact:hover {
  background: linear-gradient(135deg, #1d4ed8, #1e40af);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-view-results-compact {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
}

.btn-view-results-compact:hover {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-compact:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Modal Styles for Quiz Details */
.quiz-detail-modal .modal-header {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
}

.topper-card {
  text-align: center;
  padding: 15px;
  border-radius: 10px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
}

.topper-avatar-modal {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  margin-bottom: 10px;
}

.topper-rank-modal {
  font-size: 0.9rem;
  padding: 4px 12px;
  border-radius: 999px;
  font-weight: 700;
  margin-bottom: 8px;
  display: inline-block;
}

.rank-1-modal {
  background: linear-gradient(135deg, #fef3c7, #f59e0b);
  color: #92400e;
}

.rank-2-modal {
  background: linear-gradient(135deg, #e5e7eb, #6b7280);
  color: white;
}

.rank-3-modal {
  background: linear-gradient(135deg, #fcd34d, #f59e0b);
  color: #92400e;
}

.quiz-detail-item {
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

.quiz-detail-label {
  font-weight: 600;
  color: #475569;
  margin-bottom: 5px;
}

.quiz-detail-value {
  color: #1e293b;
}

/* Empty State */
.empty-quizzes-compact {
  padding: 40px 20px;
  text-align: center;
  color: #94a3b8;
}

.empty-quizzes-compact i {
  font-size: 3rem;
  margin-bottom: 15px;
  opacity: 0.5;
}

.empty-quizzes-compact h4 {
  color: #64748b;
  margin-bottom: 10px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .quiz-header-compact {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .quiz-attempts-badge {
    text-align: left;
    width: 100%;
  }
  
  .quiz-details-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .quiz-meta-left {
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .quiz-actions-compact {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .action-buttons-compact {
    width: 100%;
    flex-wrap: wrap;
  }
  
  .btn-compact {
    flex: 1;
    min-width: 120px;
    justify-content: center;
  }
}

/* Keep existing styles from your code */
.summary-row,
.parent-fee-card,
.academic-card,
.sports-events-grid {
  /* Keep all your existing styles */
}
</style>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Parent Dashboard</h4>

      <?php if (!empty($children)): ?>
        <div class="d-flex align-items-center">
          <select class="form-select form-control" style="min-width:260px"
                  onchange="if(this.value){ location.href='<?= base_url('student/switch') ?>/'+this.value }">
            <?php foreach ($children as $c): ?>
              <option value="<?= (int)$c['student_id'] ?>" <?= $activeId === (int)$c['student_id'] ? 'selected' : '' ?>>
                <?= esc($c['name']); ?>  
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
    </div>

    <?php if (empty($children)): ?>
      <div class="alert alert-info">No linked students found for your account.</div>
    <?php else: ?>
      <!-- Your existing summary chips, fee cards, etc. -->
      <!-- ... Keep all your existing summary sections ... -->

      <!-- COMPACT QUIZ SECTION -->
      <?php if (!empty($quizzesByStudent)): ?>
        <hr>
        <h5 class="mb-3">📚 Quiz Overview - All Children</h5>
        <p class="text-muted mb-4">All attemptable quizzes listed by child. Click "View Details" to see more information.</p>

        <div class="compact-quiz-grid">
          <?php foreach ($quizzesByStudent as $studentId => $studentData): ?>
            <?php
              $studentInfo = $studentData['student_info'];
              $studentQuizzes = $studentData['quizzes'];
              $studentStats = $studentData['stats'];
            ?>
            
            <div class="student-quiz-card">
              <!-- Student Header -->
              <div class="student-header-compact">
                <img src="<?= esc($studentInfo['profile_photo']) ?>" class="student-avatar-compact" alt="<?= esc($studentInfo['name']) ?>">
                <div class="student-info-compact">
                  <div class="student-name-compact"><?= esc($studentInfo['name']) ?></div>
                  <div class="student-class-compact">
                    <?= esc($studentInfo['class']) ?> • Reg: <?= esc($studentInfo['reg_no']) ?>
                  </div>
                </div>
              </div>
              
              <!-- Student Stats -->
              <div class="student-stats-compact">
                <div class="student-stat-item">
                  <span class="student-stat-value"><?= $studentStats['total_quizzes'] ?></span>
                  <span class="student-stat-label">Total Quizzes</span>
                </div>
                <div class="student-stat-item">
                  <span class="student-stat-value"><?= $studentStats['available_attempts'] ?></span>
                  <span class="student-stat-label">Available Attempts</span>
                </div>
                <div class="student-stat-item">
                  <span class="student-stat-value"><?= $studentStats['total_attempts'] ?></span>
                  <span class="student-stat-label">Attempts Used</span>
                </div>
              </div>
              
              <!-- Quizzes List -->
              <div class="compact-quiz-list">
                <?php if (!empty($studentQuizzes)): ?>
                  <?php foreach ($studentQuizzes as $quiz): ?>
                    <?php
                      $quizId = (int)($quiz['quiz_id'] ?? 0);
                      $sno = $quiz['sno'] ?? '';
                      $quizStatus = $quiz['quiz_status'] ?? 'live';
                      $attempts = (int)($quiz['attempts_count'] ?? 0);
                      $maxAttempts = (int)($quiz['max_attempts'] ?? 0);
                      $remainingAttempts = max(0, $maxAttempts - $attempts);
                      $timeLimitSec = (int)($quiz['time_limit_sec'] ?? 0);
                      $timeMinutes = $timeLimitSec > 0 ? max(1, round($timeLimitSec / 60)) : 0;
                      $subjectName = $quiz['subject_name'] ?? '';
                      $topics = $quiz['topics'] ?? '';
                      $questionCount = (int)($quiz['questions_count'] ?? 0);
                      $totalMarks = (int)($quiz['total_marks'] ?? 0);
                      $topScorers = $quiz['top_scorers'] ?? [];
                      
                      $isUnlimited = ($maxAttempts === 0);
                      $canPlay = ($quizStatus === 'live' && ($isUnlimited || $remainingAttempts > 0));
                      $canPractice = ($quizStatus === 'live');
                      $hasAttempts = ($attempts > 0);
                    ?>
                    
                    <div class="quiz-item-compact">
                      <!-- Header: S.No + Subject + Remaining Attempts -->
                      <div class="quiz-header-compact">
                        <div class="quiz-sno"><?= $sno ?></div>
                        <div class="quiz-subject-compact">
                          <div class="quiz-title-main">
                            <i class="fa fa-book-open"></i>
                            <?= esc($subjectName) ?>
                          </div>
                          <?php if (!empty($topics)): ?>
                            <div class="quiz-topics">
                              <i class="fa fa-hashtag"></i>
                              <?= esc($topics) ?>
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="quiz-attempts-badge">
                          <span class="attempts-remaining <?= $isUnlimited ? 'attempts-unlimited' : '' ?>">
                            <i class="fa fa-redo"></i>
                            <?= $isUnlimited ? 'Unlimited' : $remainingAttempts . ' left' ?>
                          </span>
                        </div>
                      </div>
                      
                      <!-- Details Row -->
                      <div class="quiz-details-row">
                        <div class="quiz-meta-left">
                          <div class="meta-item">
                            <i class="fa fa-question-circle"></i>
                            <span><?= $questionCount ?> Questions</span>
                          </div>
                          <div class="meta-item">
                            <i class="fa fa-clock"></i>
                            <span><?= $timeMinutes ?> mins</span>
                          </div>
                          <div class="meta-item">
                            <i class="fa fa-star"></i>
                            <span><?= $totalMarks ?> Marks</span>
                          </div>
                        </div>
                        <div class="quiz-status-compact <?= $quizStatus === 'live' ? 'status-live-compact' : 'status-upcoming-compact' ?>">
                          <?= $quizStatus === 'live' ? 'Live Now' : 'Upcoming' ?>
                        </div>
                      </div>
                      
                      <!-- Actions Row -->
                      <div class="quiz-actions-compact">
                        <!-- Review Attempts (Eye Icons) -->
                        <?php if ($hasAttempts && !empty($quiz['attempt_ids'])): ?>
                          <div class="review-attempts">
                            <span class="review-label">Review:</span>
                            <div class="review-icons">
                              <?php foreach ($quiz['attempt_ids'] as $index => $attemptId): ?>
                                <a href="<?= base_url('student/quizzes/review/' . (int)$attemptId) ?>" 
                                   class="eye-icon-btn"
                                   title="Review Attempt <?= $index + 1 ?>">
                                  <i class="fa fa-eye"></i>
                                </a>
                              <?php endforeach; ?>
                            </div>
                          </div>
                        <?php else: ?>
                          <div class="review-attempts">
                            <span class="text-muted">No attempts yet</span>
                          </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons-compact">
                          <!-- View Details Button (Triggers Modal) -->
                          <button type="button" 
                                  class="btn-compact btn-view-detail" 
                                  data-bs-toggle="modal" 
                                  data-bs-target="#quizDetailModal<?= $quizId ?>">
                            <i class="fa fa-info-circle"></i> Details
                          </button>
                          
                          <!-- Play Button 
                          <a href="<?= base_url('student/quizzes/start/' . $quizId . '?sid=' . $studentId) ?>"
                             class="btn-compact btn-play-compact"
                             <?= !$canPlay ? 'disabled style="opacity:0.5;pointer-events:none;"' : '' ?>>
                            <i class="fa fa-play"></i> Play
                          </a>
                          -->
                          
                          <!-- Practice Button -->
                          <a href="<?= base_url('student/quizzes/practice/' . $quizId . '?sid=' . $studentId) ?>"
                             class="btn-compact btn-practice-compact"
                             <?= !$canPractice ? 'disabled style="opacity:0.5;pointer-events:none;"' : '' ?>>
                            <i class="fa fa-dumbbell"></i> Practice
                          </a>
                          
                          <!-- View Results Button -->
                          <?php if ($hasAttempts): ?>
                            <a href="<?= base_url('student/quizzes/results/' . $quizId . '?sid=' . $studentId) ?>"
                               class="btn-compact btn-view-results-compact">
                              <i class="fa fa-chart-bar"></i> Results
                            </a>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Quiz Details Modal -->
                    <div class="modal fade quiz-detail-modal" id="quizDetailModal<?= $quizId ?>" tabindex="-1">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">
                              <i class="fa fa-info-circle me-2"></i>
                              Quiz Details: <?= esc($subjectName) ?>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="row mb-4">
                              <div class="col-md-6">
                                <div class="quiz-detail-item">
                                  <div class="quiz-detail-label">Quiz Duration</div>
                                  <div class="quiz-detail-value">
                                    <i class="fa fa-clock text-primary"></i>
                                    <?= $timeMinutes ?> minutes
                                  </div>
                                </div>
                                <div class="quiz-detail-item">
                                  <div class="quiz-detail-label">Total Questions</div>
                                  <div class="quiz-detail-value">
                                    <i class="fa fa-question-circle text-primary"></i>
                                    <?= $questionCount ?> questions
                                  </div>
                                </div>
                                <div class="quiz-detail-item">
                                  <div class="quiz-detail-label">Total Marks</div>
                                  <div class="quiz-detail-value">
                                    <i class="fa fa-star text-primary"></i>
                                    <?= $totalMarks ?> marks
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="quiz-detail-item">
                                  <div class="quiz-detail-label">Attempts Status</div>
                                  <div class="quiz-detail-value">
                                    <span class="badge bg-<?= $isUnlimited ? 'info' : ($remainingAttempts > 0 ? 'success' : 'warning') ?>">
                                      <?= $attempts ?> of <?= $isUnlimited ? '∞' : $maxAttempts ?> used
                                    </span>
                                  </div>
                                </div>
                                <div class="quiz-detail-item">
                                  <div class="quiz-detail-label">Remaining Attempts</div>
                                  <div class="quiz-detail-value">
                                    <span class="badge bg-<?= $isUnlimited ? 'info' : ($remainingAttempts > 0 ? 'success' : 'danger') ?>">
                                      <?= $isUnlimited ? 'Unlimited' : $remainingAttempts ?>
                                    </span>
                                  </div>
                                </div>
                                <div class="quiz-detail-item">
                                  <div class="quiz-detail-label">Quiz Status</div>
                                  <div class="quiz-detail-value">
                                    <span class="badge bg-<?= $quizStatus === 'live' ? 'success' : 'warning' ?>">
                                      <?= $quizStatus === 'live' ? 'Live Now' : 'Upcoming' ?>
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>
                            
                            <!-- Top 3 Performers -->
                            <?php if (!empty($topScorers)): ?>
                              <div class="mb-4">
                                <h6 class="mb-3">
                                  <i class="fa fa-trophy text-warning"></i>
                                  Top Performers
                                </h6>
                                <div class="row">
                                  <?php foreach (array_slice($topScorers, 0, 3) as $idx => $topper): ?>
                                    <?php
                                      $position = $idx + 1;
                                      $rankClass = 'rank-' . $position . '-modal';
                                      $photoUrl = $topper['photo_url'] ?? base_url('resource/img/avatar-student.png');
                                      $score = $topper['score'] ?? 0;
                                      $topperQuestionCount = $topper['questions_count'] ?? $questionCount;
                                      $scorePercentage = ($topperQuestionCount > 0) ? ($score / $topperQuestionCount) * 100 : 0;
                                    ?>
                                    <div class="col-md-4">
                                      <div class="topper-card">
                                        <img src="<?= esc($photoUrl) ?>" class="topper-avatar-modal" alt="Topper">
                                        <div class="topper-rank-modal <?= $rankClass ?>">#<?= $position ?></div>
                                        <div class="fw-bold"><?= $topper['student_name'] ?? 'Student' ?></div>
                                        <div class="text-success fw-bold mt-2"><?= number_format($scorePercentage, 1) ?>%</div>
                                        <small class="text-muted">Score: <?= $score ?>/<?= $topperQuestionCount ?></small>
                                      </div>
                                    </div>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            <?php endif; ?>
                            
                            <!-- Topics Covered -->
                            <?php if (!empty($topics)): ?>
                              <div class="quiz-detail-item">
                                <div class="quiz-detail-label">Topics Covered</div>
                                <div class="quiz-detail-value"><?= esc($topics) ?></div>
                              </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons in Modal -->
                            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                              <a href="<?= base_url('student/quizzes/start/' . $quizId . '?sid=' . $studentId) ?>"
                                 class="btn btn-success flex-fill <?= !$canPlay ? 'disabled' : '' ?>">
                                <i class="fa fa-play me-2"></i> Start Quiz
                              </a>
                              <a href="<?= base_url('student/quizzes/practice/' . $quizId . '?sid=' . $studentId) ?>"
                                 class="btn btn-primary flex-fill <?= !$canPractice ? 'disabled' : '' ?>">
                                <i class="fa fa-dumbbell me-2"></i> Practice Mode
                              </a>
                              <?php if ($hasAttempts): ?>
                                <a href="<?= base_url('student/quizzes/results/' . $quizId . '?sid=' . $studentId) ?>"
                                   class="btn btn-purple flex-fill">
                                  <i class="fa fa-chart-bar me-2"></i> View Results
                                </a>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="empty-quizzes-compact">
                    <i class="fa fa-inbox"></i>
                    <h4>No Quizzes Available</h4>
                    <p>No quizzes are currently available for this student.</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-light border mt-3">
          <i class="fa fa-info-circle me-2"></i>
          No quizzes found for your children at the moment. Please check back later.
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // Initialize tooltips
  if (typeof $ !== 'undefined' && $.fn.tooltip) {
    $('[data-toggle="tooltip"]').tooltip();
  }
  
  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });
});
</script>

<?= $this->endSection() ?>