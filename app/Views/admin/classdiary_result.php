<?php
// Helper to plain-text a diary cell
$renderPlain = static function($html) {
    $plain = trim(preg_replace(
        '/\s+/u',
        ' ',
        html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')
    ));
    return esc($plain);
};

// Collect unique classes & subjects for filters
$allClasses  = [];   // [id => label]
$allSubjects = [];   // [label => true]

// Build a safe ID for a class (prefer cls_sec_id if present)
$mkClassId = static function($row) {
    if (!empty($row['cls_sec_id'])) return 'c'.$row['cls_sec_id'];
    $name = $row['class_full_name'] ?? $row['class'] ?? 'class';
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    return 'c'.trim($slug, '-');
};

foreach ($data as $row) {
    $classId   = $mkClassId($row);
    $className = $row['class_full_name'] ?? $row['class'] ?? 'Class';
    $allClasses[$classId] = $className;

    foreach ($row['result'] as $subject => $_) {
        if (is_string($subject) && $subject !== '') {
            $allSubjects[$subject] = true;
        }
    }
}
ksort($allClasses);
$allSubjects = array_keys($allSubjects);
sort($allSubjects);

// Get report option filters from controller data
$showHomework = $showHomework ?? true;
$showClasswork = $showClasswork ?? true;
$showAudio = $showAudio ?? true;
$showVideo = $showVideo ?? true;
$showPicture = $showPicture ?? true;
$showQuiz = $showQuiz ?? true;
$showActivities = $showActivities ?? true;
$showBagPack = $showBagPack ?? true;
?>

<?php if (empty($data)): ?>
  <div class="alert alert-info mb-0">No diary entries for this week.</div>
<?php else: ?>

  <!-- Toolbar (hidden on print) -->
  <div class="no-print mb-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
      <h4 class="mb-0">Weekly Diary</h4>
      <div class="btn-group">
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>

    <!-- Filters - Only Class and Subject -->
    <div class="card p-2 mb-2">
      <div class="row">
        <!-- Class Filters -->
        <div class="col-md-6">
          <div class="mb-1 d-flex justify-content-between align-items-center">
            <strong><i class="fas fa-chalkboard"></i> Classes</strong>
            <div>
              <button type="button" class="btn btn-xs btn-link p-0 mr-2" onclick="checkAll('class-filter', true)">Select all</button>
              <button type="button" class="btn btn-xs btn-link p-0" onclick="checkAll('class-filter', false)">Clear</button>
            </div>
          </div>
          <div class="filters-wrap" id="classFilters">
            <?php foreach ($allClasses as $cid => $cname): ?>
              <label class="mr-3 mb-1">
                <input type="checkbox" class="class-filter" value="<?= esc($cid) ?>" checked>
                <span><?= esc($cname) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Subject Filters -->
        <div class="col-md-6">
          <div class="mb-1 d-flex justify-content-between align-items-center">
            <strong><i class="fas fa-book"></i> Subjects</strong>
            <div>
              <button type="button" class="btn btn-xs btn-link p-0 mr-2" onclick="checkAll('subject-filter', true)">Select all</button>
              <button type="button" class="btn btn-xs btn-link p-0" onclick="checkAll('subject-filter', false)">Clear</button>
            </div>
          </div>
          <div class="filters-wrap" id="subjectFilters">
            <?php foreach ($allSubjects as $s): ?>
              <label class="mr-3 mb-1">
                <input type="checkbox" class="subject-filter" value="<?= esc($s) ?>" checked>
                <span><?= esc($s) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="diaryContainer">

    <?php 
    $classCounter = 0;
    $totalClasses = count($data);
    foreach ($data as $i => $row): 
      $classCounter++;
      // Build active dates (show all dates that have any content based on selected report options)
      $hasContentByDate = [];
      
      foreach ($row['result'] as $subject => $byDate) {
          if (!is_array($byDate)) continue;
          foreach ($byDate as $d => $content) {
              $hasClasswork = $showClasswork && isset($content['classwork']) && trim(strip_tags((string)$content['classwork'])) !== '';
              $hasHomework = $showHomework && isset($content['homework']) && trim(strip_tags((string)$content['homework'])) !== '';
              $hasAudio = $showAudio && isset($row['audio_tasks'][$subject][$d]);
              $hasVideo = $showVideo && isset($row['video_tasks'][$subject][$d]);
              $hasPicture = $showPicture && isset($row['picture_tasks'][$subject][$d]);
              $hasQuiz = $showQuiz && isset($row['quiz_tasks'][$subject][$d]);
              $hasActivities = $showActivities && isset($row['activities'][$subject][$d]) && !empty($row['activities'][$subject][$d]);
              $hasBagPack = $showBagPack && ((isset($content['is_book']) && $content['is_book'] == 1) || 
                           (isset($content['is_notebook']) && $content['is_notebook'] == 1));
              
              if ($hasClasswork || $hasHomework || $hasAudio || $hasVideo || $hasPicture || $hasQuiz || $hasActivities || $hasBagPack) {
                  $hasContentByDate[$d] = true;
              }
          }
      }

      $activeDates = [];
      foreach ($row['week_dates'] as $d) {
          if (isset($hasContentByDate[$d])) $activeDates[] = $d;
      }
      if (empty($activeDates)) {
          continue;
      }

      $weekStart   = reset($row['week_dates']);
      $weekEnd     = end($row['week_dates']);
      $classFull   = $row['class_full_name'] ?? $row['class'] ?? 'Class';
      $classId     = $mkClassId($row);
      
      $startDateFormatted = date('d M Y', strtotime($weekStart));
      $endDateFormatted = date('d M Y', strtotime($weekEnd));
      $colCount = 1 + count($activeDates);
      ?>

      <div class="diary-class-block <?= $classCounter < $totalClasses ? 'page-break-after' : '' ?>" data-class-id="<?= esc($classId) ?>" data-class-name="<?= esc($classFull) ?>">
        <table id="diary-table-<?= $i ?>" class="table table-bordered table-sm diary-table mb-4">
          <thead>
            <tr class="class-heading">
              <th class="text-center" colspan="<?= $colCount ?>">
                <div style="font-weight:700;font-size:16px;">
                  Weekly Diary – <?= esc($classFull) ?> (<?= $startDateFormatted ?> – <?= $endDateFormatted ?>)
                </div>
              </th>
            </tr>
            <tr class="date-day-row">
              <th class="subj-col text-left">Subject</th>
              <?php foreach ($activeDates as $d): ?>
                <th class="text-center dd-cell" style="min-width: 180px;">
                  <div style="font-size: 16px; font-weight: 700; text-transform: uppercase;">
                    <?= esc(date('l', strtotime($d))) ?>
                  </div>
                  <div style="font-size: 11px; color: #666;">
                    <?= esc(date('d M Y', strtotime($d))) ?>
                  </div>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($row['result'] as $subject => $byDate): ?>
              <?php
              // Check if this subject has any visible content for the active dates
              $hasVisibleContent = false;
              foreach ($activeDates as $d) {
                  $content = isset($byDate[$d]) ? $byDate[$d] : null;
                  if ($content) {
                      if ($showClasswork && isset($content['classwork']) && trim(strip_tags((string)$content['classwork'])) !== '') $hasVisibleContent = true;
                      if ($showHomework && isset($content['homework']) && trim(strip_tags((string)$content['homework'])) !== '') $hasVisibleContent = true;
                  }
                  if ($showAudio && isset($row['audio_tasks'][$subject][$d])) $hasVisibleContent = true;
                  if ($showVideo && isset($row['video_tasks'][$subject][$d])) $hasVisibleContent = true;
                  if ($showPicture && isset($row['picture_tasks'][$subject][$d])) $hasVisibleContent = true;
                  if ($showQuiz && isset($row['quiz_tasks'][$subject][$d])) $hasVisibleContent = true;
                  if ($showActivities && isset($row['activities'][$subject][$d]) && !empty($row['activities'][$subject][$d])) $hasVisibleContent = true;
                  if ($showBagPack && $content && ((isset($content['is_book']) && $content['is_book'] == 1) || (isset($content['is_notebook']) && $content['is_notebook'] == 1))) $hasVisibleContent = true;
              }
              if (!$hasVisibleContent) continue;
              ?>
              <tr data-subject="<?= esc($subject) ?>">
                <td class="subj-col" style="font-weight: 600; background-color: #fef9e6; vertical-align: middle;">
                  <?= esc($subject) ?>
                </td>
                
                <?php foreach ($activeDates as $d): ?>
                  <td class="dd-cell">
                    <?php 
                    $content = isset($byDate[$d]) ? $byDate[$d] : null;
                    $hasAnyContent = false;
                    ?>
                    
                    <!-- Bag Pack Section -->
                    <?php if ($showBagPack && $content): 
                        $hasBook = isset($content['is_book']) && $content['is_book'] == 1;
                        $hasNotebook = isset($content['is_notebook']) && $content['is_notebook'] == 1;
                        if ($hasBook || $hasNotebook):
                            $hasAnyContent = true;
                    ?>
                      <div class="bagpack-section mb-2">
                        <strong>🎒 Bring:</strong><br>
                        <?php if ($hasBook): ?>
                          <span class="badge badge-info" style="background-color: #17a2b8; color: white; padding: 2px 6px; margin: 2px; display: inline-block;">
                            📖 Book
                          </span>
                        <?php endif; ?>
                        <?php if ($hasNotebook): ?>
                          <span class="badge badge-success" style="background-color: #28a745; color: white; padding: 2px 6px; margin: 2px; display: inline-block;">
                            📓 Notebook
                          </span>
                        <?php endif; ?>
                      </div>
                    <?php endif; 
                    endif; ?>
                    
                    <!-- Class Work -->
                    <?php if ($showClasswork && $content && isset($content['classwork']) && trim(strip_tags((string)$content['classwork'])) !== ''):
                        $hasAnyContent = true;
                    ?>
                      <div class="classwork-section mb-2">
                        <strong class="text-success">📚 Class Work:</strong><br>
                        <?= $renderPlain($content['classwork']) ?>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Home Work -->
                    <?php if ($showHomework && $content && isset($content['homework']) && trim(strip_tags((string)$content['homework'])) !== ''):
                        $hasAnyContent = true;
                    ?>
                      <div class="homework-section mb-2">
                        <strong class="text-primary">📝 Home Work:</strong><br>
                        <?= $renderPlain($content['homework']) ?>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Audio Task -->
                    <?php if ($showAudio && isset($row['audio_tasks'][$subject][$d])): 
                        $hasAnyContent = true;
                        $audioTask = $row['audio_tasks'][$subject][$d];
                    ?>
                      <div class="audio-section mb-2">
                        <strong class="text-info">🎧 Audio Task:</strong><br>
                        <?= !empty($audioTask['caption']) ? esc($audioTask['caption']) : 'Listen to the assigned audio' ?>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Video Task -->
                    <?php if ($showVideo && isset($row['video_tasks'][$subject][$d])): 
                        $hasAnyContent = true;
                        $videoTask = $row['video_tasks'][$subject][$d];
                    ?>
                      <div class="video-section mb-2">
                        <strong class="text-danger">📹 Video Task:</strong><br>
                        <?= !empty($videoTask['caption']) ? esc($videoTask['caption']) : 'Watch the assigned video' ?>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Picture Task -->
                    <?php if ($showPicture && isset($row['picture_tasks'][$subject][$d])): 
                        $hasAnyContent = true;
                        $pictureTask = $row['picture_tasks'][$subject][$d];
                    ?>
                      <div class="picture-section mb-2">
                        <strong class="text-warning">🖼️ Picture Task:</strong><br>
                        <?= !empty($pictureTask['caption']) ? esc($pictureTask['caption']) : 'Complete the picture activity' ?>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Quiz Section -->
                    <?php if ($showQuiz && isset($row['quiz_tasks'][$subject][$d])): 
                        $hasAnyContent = true;
                        $quiz = $row['quiz_tasks'][$subject][$d];
                    ?>
                      <div class="quiz-section mb-2">
                        <div class="quiz-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 12px; border-radius: 8px;">
                          <div style="font-weight: bold; margin-bottom: 5px;">
                            <i class="fas fa-question-circle"></i> 📋 Quiz: <?= esc($quiz->title) ?>
                          </div>
                          <div style="font-size: 10px; display: flex; gap: 12px; flex-wrap: wrap; margin-top: 5px;">
                            <?php if ($quiz->time_limit_sec > 0): ?>
                              <span><i class="fas fa-clock"></i> ⏱️ <?= floor($quiz->time_limit_sec / 60) ?> min</span>
                            <?php endif; ?>
                            <span><i class="fas fa-redo"></i> 🔄 Max Attempts: <?= $quiz->max_attempts ?></span>
                            <span><i class="fas fa-question"></i> ❓ Questions: <?= $quiz->questions_count ?></span>
                          </div>
                          <?php if (!empty($quiz->instructions)): ?>
                            <div style="font-size: 10px; margin-top: 5px; opacity: 0.9; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 5px;">
                              📌 <strong>Instructions:</strong> <?= esc(substr($quiz->instructions, 0, 100)) ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Activities Section -->
                    <?php if ($showActivities && isset($row['activities'][$subject][$d]) && !empty($row['activities'][$subject][$d])): 
                        $hasAnyContent = true;
                        $activities = $row['activities'][$subject][$d];
                    ?>
                      <div class="activities-section mb-2">
                        <strong style="color: #6f42c1;">🎯 Classroom Activities:</strong>
                        <?php if (is_array($activities)): ?>
                          <ul style="margin-bottom: 0; padding-left: 20px; margin-top: 5px;">
                            <?php foreach ($activities as $activity): ?>
                              <li style="margin-bottom: 8px; font-size: 11px;">
                                <strong>📌 <?= esc($activity['name'] ?? 'Activity') ?></strong>
                                <?php if (!empty($activity['type'])): ?>
                                  <span class="badge badge-secondary ml-1" style="font-size: 9px;"><?= esc($activity['type']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($activity['duration_minutes'])): ?>
                                  <span class="badge badge-light ml-1" style="font-size: 9px;">⏱️ <?= $activity['duration_minutes'] ?> min</span>
                                <?php endif; ?>
                                <?php if (!empty($activity['description'])): ?>
                                  <br><small style="color: #666;"><?= esc(substr($activity['description'], 0, 80)) ?></small>
                                <?php endif; ?>
                              </li>
                            <?php endforeach; ?>
                          </ul>
                        <?php else: ?>
                          <?= esc($activities) ?>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if (!$hasAnyContent): ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>

  </div>

<?php endif; ?>

<style>
  .filters-wrap {
    max-height: 180px;
    overflow: auto;
    border: 1px dashed #e5e5e5;
    padding: .25rem .5rem;
  }
  .filters-wrap label { cursor: pointer; font-weight: 400; }
  .btn-xs { font-size: .78rem; }

  .diary-table {
    border-collapse: collapse !important;
    table-layout: auto !important;
    width: 100%;
    font-size: 12px;
  }
  
  .diary-table th,
  .diary-table td {
    padding: 8px 10px !important;
    line-height: 1.4 !important;
    vertical-align: top !important;
    border: 1px solid #ddd;
  }

  .diary-table .class-heading th {
    text-align: center !important;
    font-weight: 700;
    background: #f8f9fa;
    padding: 12px !important;
  }

  .diary-table .subj-col {
    width: 120px !important;
    text-align: left !important;
    font-weight: 600;
    background-color: #fef9e6;
  }

  .diary-table thead .dd-cell {
    text-align: center !important;
    background-color: #f5f5f5;
    vertical-align: middle !important;
  }
  
  .diary-table tbody .dd-cell {
    text-align: left !important;
    background-color: #fff;
  }
  
  .diary-table tbody .dd-cell .bagpack-section,
  .diary-table tbody .dd-cell .classwork-section,
  .diary-table tbody .dd-cell .homework-section,
  .diary-table tbody .dd-cell .audio-section,
  .diary-table tbody .dd-cell .video-section,
  .diary-table tbody .dd-cell .picture-section,
  .diary-table tbody .dd-cell .quiz-section,
  .diary-table tbody .dd-cell .activities-section {
    font-size: 11px;
    line-height: 1.4;
  }
  
  .diary-table tbody .dd-cell .bagpack-section {
    background-color: #fff3e0;
    padding: 5px;
    border-radius: 4px;
    margin-bottom: 8px;
  }
  
  .quiz-card {
    transition: transform 0.2s;
  }
  
  .activities-section ul {
    margin-top: 4px;
    margin-bottom: 0;
  }
  
  .activities-section li {
    margin-bottom: 5px;
  }

  /* Page break after each class diary */
  .page-break-after {
    page-break-after: always;
    break-after: page;
  }
  
  /* Page break before first class if needed */
  .page-break-before {
    page-break-before: always;
    break-before: page;
  }
  
  /* Avoid breaking inside tables */
  .diary-table {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  @media print {
    /* Hide all filter sections and buttons */
    .no-print, 
    .filters-wrap, 
    .card.p-2, 
    .btn-group,
    .btn-group .btn,
    button,
    .no-print * {
      display: none !important;
    }
    
    /* Remove background colors for print */
    .diary-table .class-heading th { 
      background: #fff !important; 
    }
    
    .diary-table .subj-col {
      background-color: #fff !important;
    }
    
    /* Ensure page breaks work */
    .page-break-after {
      page-break-after: always;
      break-after: page;
    }
    
    /* Print in landscape */
    @page {
      size: A4 landscape;
      margin: 1.5cm;
    }
    
    /* Ensure tables don't break inside */
    .diary-table {
      break-inside: avoid;
      page-break-inside: avoid;
    }
    
    /* Keep borders visible */
    .diary-table th,
    .diary-table td {
      border: 1px solid #ddd !important;
    }
  }
</style>

<script>
  function checkAll(cls, checked) {
    document.querySelectorAll('input.'+cls).forEach(cb => { cb.checked = !!checked; });
    applyFilters();
  }

  function applyFilters() {
    const selectedClasses = Array.from(document.querySelectorAll('input.class-filter:checked')).map(i => i.value);
    const selectedSubjects = Array.from(document.querySelectorAll('input.subject-filter:checked')).map(i => i.value);
    
    const blocks = Array.from(document.querySelectorAll('.diary-class-block'));

    blocks.forEach(block => {
      const classId = block.getAttribute('data-class-id') || '';
      const table = block.querySelector('table');
      let anyRowVisible = false;

      // Class visibility
      const classMatch = selectedClasses.length ? selectedClasses.includes(classId) : true;
      if (!classMatch) {
        block.style.display = 'none';
        return;
      }

      // Subject rows visibility
      Array.from(table.querySelectorAll('tbody tr')).forEach(tr => {
        const subj = tr.getAttribute('data-subject') || '';
        const rowMatch = !selectedSubjects.length || selectedSubjects.includes(subj);
        tr.style.display = rowMatch ? '' : 'none';
        if (rowMatch) anyRowVisible = true;
      });

      block.style.display = anyRowVisible ? '' : 'none';
    });
  }

  // Bind filter change events
  document.addEventListener('change', function(e){
    if (e.target.classList.contains('class-filter') || 
        e.target.classList.contains('subject-filter')) {
      applyFilters();
    }
  });

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', function(){
    applyFilters();
  });
</script>