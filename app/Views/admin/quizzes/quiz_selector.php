<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<style>
:root{
  --bg: #f7fbff;
  --card: #ffffff;
  --brand: #6759ff;
  --brand-2:#00c4ff;
  --ink:#223;
}
body{background:var(--bg)}

/* Quiz grid 5 per row on desktop */
.quiz-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
}

/* Make it responsive */
@media (max-width: 1199px) {
  .quiz-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}
@media (max-width: 991px) {
  .quiz-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 767px) {
  .quiz-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 575px) {
  .quiz-grid {
    grid-template-columns: 1fr;
  }
}

/* Quiz card fine-tuning */
.quiz-card {
  background:#f9fbff;
  border-radius:14px;
  padding:12px;
  margin-bottom:0;
  cursor:pointer;
  border:2px solid transparent;
  transition:.2s;
  font-size:12px;
}
.quiz-card:hover {
  transform:translateY(-2px);
  box-shadow:0 4px 12px rgba(0,0,0,.06);
}
.quiz-card.active {
  border-color:var(--brand);
  background:#eef1ff;
}

.quiz-title {
  font-weight:700;
  font-size:13px;
  color:#222;
  margin-bottom:4px;
}

.quiz-meta-line {
  font-size:11px;
  color:#666;
}

.quiz-topics {
  font-size:11px;
  margin-top:4px;
}

.quiz-stat-grid {
  display:grid;
  grid-template-columns: repeat(2, 1fr);
  gap:4px;
  margin-top:6px;
}

.quiz-stat {
  background:#fff;
  border-radius:8px;
  padding:4px 6px;
  border:1px solid #e3e7ff;
}

.quiz-stat-label {
  display:block;
  font-size:10px;
  color:#777;
}
.quiz-stat-value {
  font-size:11px;
  font-weight:700;
  color:#222;
}

.quiz-questions {
  margin-top:6px;
  font-size:10px;
  line-height:1.3;
}

.selector-topbar{
  background: linear-gradient(90deg, var(--brand) 0%, var(--brand-2) 100%);
  padding: .9rem 1rem; color:#fff;
  box-shadow:0 3px 14px rgba(0,0,0,.08);
  position: sticky; top:0; z-index:1020;
}
.sel-title{font-weight:800;margin:0;font-size:1.25rem}

.sel-card{
  background:var(--card); border-radius:20px;
  box-shadow:0 8px 24px rgba(103,89,255,0.12);
  padding:16px; margin-bottom:18px;
}

/* Quizzes */

/* Play button (global – kept if needed later) */
.btn-play{
  border-radius:999px; padding:.7rem 1.3rem;
  background:linear-gradient(90deg, var(--brand), var(--brand-2));
  color:#fff; font-weight:700; border:0;
  box-shadow:0 5px 18px rgba(103,89,255,0.25);
}

.student-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr); /* ← FIXED 5 CARDS PER ROW */
  gap: 12px;
  margin-top: 8px;
}

/* Section titles for groups */
.divider-title{
  margin: 10px 0 4px;
  font-weight:700;
  color:var(--brand);
  font-size:0.95rem;
}

/* Card */
.student-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 18px;
  width: 100%;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  transition: .25s ease;
  display: flex;
  flex-direction: column;
  position: relative;
}

.student-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 28px rgba(0,0,0,0.12);
}

/* Header subject badge */
.subject-badge {
  position: absolute;
  top: 10px;
  right: 12px;
  background: #e8f1ff;
  padding: 4px 10px;
  border-radius: 50px;
  font-size: 11px;
  font-weight: 700;
  color: #4a4aff;
}

/* Avatar */
.student-photo {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #eef1ff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  font-size: 28px;
  font-weight: 700;
  color: var(--brand);
  margin: 0 auto 10px;
}
.student-photo img{
  width:100%;
  height:100%;
  object-fit:cover;
}

/* Name */
.student-name {
  font-weight: 700;
  font-size: 17px;
  color: #222;
  text-align: center;
  margin-bottom: 6px;
}

/* Meta info lines */
.meta-row {
  font-size: 14px;
  color: #444;
  margin: 4px 0;
  display: flex;
  align-items: center;
  gap: 6px;
}
.meta-row i {
  color: #6759ff;
}

/* Play button on card */
.play-btn-bottom {
  align-self: center;
  margin-top: 14px;
  padding: 8px 26px;
  border-radius: 50px;
  background: linear-gradient(90deg, var(--brand), var(--brand-2));
  color: #fff;
  font-weight: 700;
  border: none;
  box-shadow: 0 4px 14px rgba(103,89,255,0.25);
}

/* Score badge */
.score-badge {
  background: #d1f7d6;
  padding: 4px 10px;
  color: #0a8f2f;
  margin-top: 8px;
  border-radius: 8px;
  font-weight: 700;
  text-align: center;
  font-size: 14px;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:6px;
}
.score-badge i{
  color:#0a8f2f;
}

/* Button inside score box */
.btn-view-attempt {
  background: #ffffff;
  border: 1px solid #0a8f2f;
  color: #0a8f2f;
  border-radius: 6px;
  padding: 3px 8px;
  font-size: 12px;
  margin-left: 6px;
  cursor: pointer;
  display:flex;
  align-items:center;
  gap:4px;
  transition:.2s;
}
.btn-view-attempt i{
  color:inherit;
}
.btn-view-attempt:hover {
  background: #0a8f2f;
  color: #ffffff;
}

.quiz-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 14px;
}

.quiz-card {
  padding: 18px;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  transition: 0.25s;
  cursor: pointer;
  position: relative;
}

.quiz-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.quiz-title {
  font-weight: 700;
  font-size: 17px;
  margin-bottom: 6px;
  color: #222;
}

.quiz-meta-line {
  font-size: 13px;
  color: #666;
  margin-bottom: 8px;
}

.stat-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 10px 0;
}

.stat-badge {
  display: flex;
  align-items: center;
  gap: 5px;
  background: #f0f4ff;
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 12px;
  color: #334;
  font-weight: 600;
}

.stat-badge i {
  color: var(--brand);
  font-size: 15px;
}

.topic-tag {
  background: #eaf7ff;
  padding: 4px 8px;
  font-size: 11px;
  border-radius: 6px;
  display: inline-block;
  margin: 2px;
  color: #0d67b5;
  font-weight: 600;
}

.tooltip-custom {
  cursor: help;
  border-bottom: 1px dotted #888;
}


.quiz-progress-wrapper {
  margin-top: 8px;
}

.quiz-progress-label {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #666;
}

.quiz-progress {
  height: 6px;
  border-radius: 999px;
  overflow: hidden;
}
.status-chip {
  position: absolute;
  top: 10px;
  right: 12px;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.status-chip i {
  font-size: 12px;
}

/* Live = green, Closed = grey */
.status-chip-live {
  background: #e3f9e5;
  color: #1b7a2a;
}

.status-chip-closed {
  background: #f0f0f0;
  color: #777;
}

/* Dim closed cards */
.quiz-card.closed {
  opacity: 0.6;
  filter: grayscale(0.3);
}
.quiz-card.closed:hover {
  opacity: 0.8;
}

@media (max-width: 1199px) {
  .student-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}
@media (max-width: 991px) {
  .student-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 767px) {
  .student-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 500px) {
  .student-grid {
    grid-template-columns: 1fr;
  }
}

/* Reduce CNIC font and prevent wrapping */
.student-card .meta-row.cnic-row {
    font-size: 12px;          /* smaller */
    white-space: nowrap;      /* prevents line break */
    overflow: hidden;         /* avoids overflow */
    text-overflow: ellipsis;  /* optional: adds … if too long */
}
</style>

<section class="selector-topbar">
  <h1 class="sel-title">🎯 Play Quiz for a Student</h1>
</section>

<div class="container mt-4" style="max-width:900px">

  <!-- Step 1: Class → Section -->
  <div class="sel-card">
    <h5 class="mb-3" style="font-weight:700;color:var(--brand)">Step 1 — Select Class & Section</h5>

    <div class="form-group mb-2">
      <label><strong>Class & Section</strong></label>
      <select id="clsSecSelect" class="form-control">
        <option value="">-- Select Class & Section --</option>
        <?php foreach ($classSections as $cs): ?>
          <option value="<?= $cs['cls_sec_id'] ?>">
            <?= esc($cs['class_name'] . ' ' . $cs['section_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Step 2: Quizzes (directly by Class-Section) -->
  <div class="sel-card d-none" id="quizBox">
    <h5 class="mb-3" style="font-weight:700;color:var(--brand)">
      Step 2 — Select Quiz
    </h5>

    <div id="quizList"></div>
  </div>

  <!-- Step 3: Students -->
  <div class="sel-card d-none" id="studentBox">
    <h5 class="mb-3" style="font-weight:700;color:var(--brand)">
      Step 3 — Select Student to Play Quiz
    </h5>
    <div id="studentList"></div>
  </div>

  <!-- (Optional global play button, not used now) -->
  <div class="text-center mt-4 d-none" id="playArea">
    <button id="playBtn" class="btn-play">▶ Start Quiz</button>
  </div>

</div>

<script>


let selectedClsSec  = null;
let selectedQuiz    = null;

const clsSecSelect = document.getElementById('clsSecSelect');

// Helper: photo URL
function photoUrl(profile){
  const p = (profile || '').toString().trim();
  if (!p) return '<?= base_url('resource/img/avatar-student.png') ?>';
  return '<?= base_url('uploads') ?>/' + p.replace(/^\/+/, '');
}

// Open existing review page (for print)
function viewAttempt(attemptId){
  if (!attemptId) {
    alert('Attempt not found for this student.');
    return;
  }
  const url = "<?= base_url('student/quizzes/review') ?>/" + attemptId;
  window.open(url, "_blank");
}

// Step 1: Class-Section -> Load Quizzes directly
clsSecSelect.addEventListener('change', () => {
  const clsSecId = clsSecSelect.value;
  selectedClsSec = clsSecId;

  // Reset next steps
  selectedQuiz    = null;
  document.getElementById('quizList').innerHTML    = '';
  document.getElementById('studentList').innerHTML = '';
  document.getElementById('quizBox').classList.add('d-none');
  document.getElementById('studentBox').classList.add('d-none');

  if (!clsSecId) return;

  // Load quizzes for cls_sec_id (no subject step)
  loadQuizzesForClassSection(clsSecId);
});

// Load quizzes for selected class-section


function loadQuizzesForClassSection(clsSecId){
  fetch(`<?= site_url('admin/load_quizzes_by_clssec/') ?>${clsSecId}`)
    .then(r => r.json())
    .then(data => {
      const box = document.getElementById('quizList');
      box.innerHTML = '';

      if (!data || !data.length) {
        box.innerHTML = '<p class="text-muted mb-0">No active quizzes found for this class-section.</p>';
      } else {
        box.innerHTML = '<div class="quiz-grid" id="quizGrid"></div>';
        const grid = document.getElementById('quizGrid');

        data.forEach(q => {
          const total    = parseInt(q.total_students || 0, 10);
          const att      = parseInt(q.attempted_students || 0, 10);
          const remain   = parseInt(q.remaining_students || (total - att), 10);

let attemptPct = 0;
let remainPct  = 0;
if (total > 0) {
  attemptPct = Math.round((att / total) * 100);
  if (attemptPct > 100) attemptPct = 100;
  remainPct = 100 - attemptPct;
}

         const status   = q.quiz_status || 'live';     // from PHP
          const remTime  = q.remaining_time || '-';           // e.g. "1d 2h 10m" or "Ended"
          const duration = q.duration_minutes || 0;           // minutes (converted from seconds)
          const statusLabel = status === 'closed' ? 'Closed' : 'Live';
const statusIcon  = status === 'closed' ? 'fa-lock' : 'fa-broadcast-tower';
const statusClass = status === 'closed' ? 'status-chip-closed' : 'status-chip-live';

// For the card wrapper class (add "closed" to dim)
const cardExtraClass = status === 'closed' ? ' closed' : '';
          const topics   = q.topics || '';

          const qTotal   = q.questions_count      || 0;
          const qMcqS    = q.count_mcq_single     || 0;
          const qMcqM    = q.count_mcq_multi      || 0;
          const qTf      = q.count_tf             || 0;
          const qShort   = q.count_short          || 0;
          const qFill    = q.count_fill           || 0;
          const qMatch   = q.count_match          || 0;

grid.innerHTML += `
  <div class="quiz-card${cardExtraClass}" data-id="${q.quiz_id}">
    
    <div class="status-chip ${statusClass}" data-toggle="tooltip" title="${status === 'closed' ? 'Quiz has ended' : 'Quiz is live / accepting attempts'}">
      <i class="fa ${statusIcon}"></i> ${statusLabel}
    </div>

    <div class="quiz-title">${q.title}</div>

    <div class="quiz-meta-line">
      <i class="fa fa-school"></i>
      ${q.class_name || ''} ${q.section_name || ''} &nbsp;|
      <i class="fa fa-book"></i>
      ${q.subject_name || ''}
    </div>

    <div>
      ${(topics || '')
        .split(',')
        .filter(t => t.trim().length)
        .map(t => `<span class="topic-tag">${t.trim()}</span>`)
        .join('')}
    </div>

    <div class="stat-badges">
    
      <div class="stat-badge" data-toggle="tooltip" title="Remaining time before quiz closes">
        <i class="fa fa-hourglass-half"></i> ${remTime}
      </div>
      <div class="stat-badge" data-toggle="tooltip" title="Quiz duration in minutes">
        <i class="fa fa-stopwatch"></i> ${duration}m
      </div>
      <div class="stat-badge" data-toggle="tooltip" title="Total number of questions">
        <i class="fa fa-question-circle"></i> ${qTotal}
      </div>
    </div>

    <div class="stat-badges">
      <div class="stat-badge" data-toggle="tooltip" title="Multiple Choice (Single Answer)">
        <i class="fa fa-dot-circle"></i> ${qMcqS}
      </div>
      <div class="stat-badge" data-toggle="tooltip" title="Multiple Choice (Multiple Answers)">
        <i class="fa fa-tasks"></i> ${qMcqM}
      </div>
      <div class="stat-badge" data-toggle="tooltip" title="True/False Questions">
        <i class="fa fa-check"></i> ${qTf}
      </div>
      <div class="stat-badge" data-toggle="tooltip" title="Short Answer Questions">
        <i class="fa fa-pencil-alt"></i> ${qShort}
      </div>
      <div class="stat-badge" data-toggle="tooltip" title="Fill in the blanks">
        <i class="fa fa-pen-fancy"></i> ${qFill}
      </div>
      <div class="stat-badge" data-toggle="tooltip" title="Match the columns">
        <i class="fa fa-random"></i> ${qMatch}
      </div>
    </div>

    <!-- 👇 New progress bar block -->
    <div class="quiz-progress-wrapper">
      <div class="quiz-progress-label">
        <span>
          <i class="fa fa-users"></i>
          ${total} students
        </span>
        <span>
          ${att}/${total || 0} attempted
        </span>
      </div>
      <div class="progress quiz-progress">
        <div
          class="progress-bar bg-success"
          role="progressbar"
          style="width: ${attemptPct}%;"
          aria-valuenow="${attemptPct}"
          aria-valuemin="0"
          aria-valuemax="100"
          data-toggle="tooltip"
          title="Attempted: ${att} student(s)"
        ></div>
        <div
          class="progress-bar bg-warning"
          role="progressbar"
          style="width: ${remainPct}%;"
          aria-valuenow="${remainPct}"
          aria-valuemin="0"
          aria-valuemax="100"
          data-toggle="tooltip"
          title="Remaining: ${remain} student(s)"
        ></div>
      </div>
    </div>

  </div>
`;

        });
      }

      document.getElementById('quizBox').classList.remove('d-none');

      // Click handler to load students for selected quiz
      document.querySelectorAll('.quiz-card').forEach(card => {
        card.addEventListener('click', () => {
          document.querySelectorAll('.quiz-card').forEach(c => c.classList.remove('active'));
          card.classList.add('active');
          selectedQuiz = card.dataset.id;
          loadStudentsForQuiz(selectedClsSec);
        });
      });
    });
}


// Step 2 (now): Load students for selected class-section after quiz selection
function loadStudentsForQuiz(clsSecId){
  if (!selectedQuiz) return;

  document.getElementById('studentBox').classList.remove('d-none');

  fetch(`<?= base_url('admin/load_students_for_quiz/') ?>${clsSecId}/${selectedQuiz}`)
    .then(r => r.json())
    .then(data => {
      const box = document.getElementById('studentList');
      box.innerHTML = '';

      const hasNotAtt = data.not_attempted && data.not_attempted.length;
      const hasAtt    = data.attempted && data.attempted.length;

      if (!hasNotAtt && !hasAtt) {
        box.innerHTML = '<p class="text-muted mb-0">No students found for this class-section.</p>';
        return;
      }

      if (hasNotAtt) {
        box.innerHTML += `<div class="divider-title">Students (Not Attempted)</div>`;
        box.innerHTML += `<div class="student-grid" id="notAtt"></div>`;
      }
      if (hasAtt) {
        box.innerHTML += `<div class="divider-title mt-3">Students (Attempted)</div>`;
        box.innerHTML += `<div class="student-grid" id="Att"></div>`;
      }

      let notAtt = document.getElementById("notAtt");
      let att    = document.getElementById("Att");

      // Not attempted students
      if (hasNotAtt && notAtt) {
        data.not_attempted.forEach(stu => {
          const avatar = stu.profile_photo
            ? `<img src="${photoUrl(stu.profile_photo)}" alt="photo">`
            : (stu.full_name || '').charAt(0).toUpperCase();

          notAtt.innerHTML += `
            <div class="student-card">

              <span class="subject-badge">
                ${stu.subject_name || ''}
              </span>

              <div class="student-photo">
                ${avatar}
              </div>

              <div class="student-name">${stu.full_name}</div>

                         <div class="meta-row cnic-row">
    <i class="fa fa-id-card"></i>
    <span>${stu.father_cnic || ''}</span>
</div>


              <div class="meta-row">
                <i class="fa fa-phone"></i>
                <span>${stu.whatsapp || ''}</span>
              </div>


              <button class="play-btn-bottom"
                onclick="playQuiz(${stu.student_id}, ${selectedQuiz})">
                ▶ Play
              </button>

            </div>
          `;
        });
      }

      // Attempted students
      if (hasAtt && att) {
        data.attempted.forEach(stu => {
          const avatar = stu.profile_photo
            ? `<img src="${photoUrl(stu.profile_photo)}" alt="photo">`
            : (stu.full_name || '').charAt(0).toUpperCase();

          att.innerHTML += `
            <div class="student-card">

              <span class="subject-badge">
                ${stu.subject_name || ''}
              </span>

              <div class="student-photo">
                ${avatar}
              </div>

              <div class="student-name">${stu.full_name}</div>

              <div class="meta-row">
                <i class="fa fa-school"></i>
                <span>${stu.class_name} ${stu.section_name}</span>
              </div>

              <div class="meta-row">
                <i class="fa fa-book"></i>
                <span>${stu.topic_name || ''}</span>
              </div>

              <div class="meta-row">
                <i class="fa fa-venus-mars"></i>
                <span>${stu.gender || ''}</span>
              </div>

              <div class="meta-row">
                <i class="fa fa-phone"></i>
                <span>${stu.whatsapp || ''}</span>
              </div>

              <div class="score-badge">
                <i class="fa fa-star"></i>
                <span>Score: ${stu.score_obtained}</span>

                <button type="button" class="btn-view-attempt"
                  onclick="viewAttempt(${stu.attempt_id})">
                  <i class="fa fa-print"></i>
                  <span>Review</span>
                </button>
              </div>

            </div>
          `;
        });
      }

      document.getElementById('studentBox').classList.remove('d-none');
    });
}

// Play quiz for student
function playQuiz(studentId, quizId){
  $.ajax({
    url: "<?= site_url('admin/generate-link') ?>",
    type: "POST",
    dataType: "json",
    data: {
      quiz_id: quizId,
      student_id: studentId,
      '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    },
    success: function(res) {
      if (res.success && res.link) {
        window.location.href = res.link;
      } else {
        alert(res.message || 'Failed to generate quiz link.');
      }
    },
    error: function() {
      alert('Server error while generating quiz link.');
    }
  });
}

document.addEventListener("DOMContentLoaded", function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?= $this->endSection() ?>
