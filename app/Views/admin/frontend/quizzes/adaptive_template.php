<?= $this->extend('frontend/layouts/master_portal') ?>
<?= $this->section('content') ?>

<style>
/* Adaptive-specific styles */
.level-header {
    background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.level-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.level-progress {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 10px;
    margin-top: 10px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    margin-bottom: 5px;
}

.level-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

/* Modal styles */
.adaptive-modal .modal-content {
    border-radius: 20px;
    overflow: hidden;
    border: none;
}

.level-result-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.level-passed {
    color: #10b981;
}

.level-failed {
    color: #ef4444;
}

.retry-btn {
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    border: none;
    color: white;
}

.next-level-btn {
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
}

.complete-btn {
    background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%);
    border: none;
    color: white;
}
</style>

<!-- Level Header -->
<div class="level-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="level-title">
                <i class="fas fa-layer-group"></i>
                <?= esc($levelInfo->level_name ?? 'Level') ?> 
                <span class="level-badge">Level <?= $levelInfo->level_no ?? 1 ?></span>
            </h1>
            <p class="mb-0 small opacity-90">Adaptive Learning Quiz</p>
        </div>
        <div class="text-end">
            <div class="small">Passing: <?= $levelInfo->min_pass_percentage ?? 70 ?>%</div>
            <div class="small">Questions: <?= $progress['total'] ?? 0 ?></div>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="level-progress">
        <div class="progress-label">
            <span>Progress</span>
            <span><?= $progress['answered'] ?? 0 ?>/<?= $progress['total'] ?? 0 ?> answered</span>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-success" 
                 style="width: <?= $progress['percentage'] ?? 0 ?>%"
                 role="progressbar"></div>
        </div>
    </div>
</div>

<!-- Existing Quiz Topbar (keep your existing topbar HTML) -->
<section class="quiz-topbar">
    <!-- Your existing topbar code here -->
    <!-- Just add level indicator if needed -->
</section>

<!-- Quiz Content (your existing quiz form) -->
<section class="content">
    <form action="<?= base_url('student/quizzes/submit-level') ?>" method="post" id="adaptiveAttemptForm">
        <?= csrf_field() ?>
        <input type="hidden" name="attempt_id" value="<?= (int)$attemptId ?>">
        
        <!-- Your existing question blocks code -->
        <div class="quiz-wrap">
            <?php $qNo = 1; $index = 0; ?>
            <?php foreach ($qq as $row): ?>
                <!-- Your existing question rendering code -->
            <?php endforeach; ?>
        </div>
        
        <!-- Footer with adaptive-specific buttons -->
        <div class="quiz-footer">
            <div class="container-fluid px-2">
                <div class="d-flex justify-content-center align-items-center" style="gap:.75rem">
                    <button type="button" class="btn btn-light btn-pill btn-prev" id="btnPrev">? Previous</button>
                    
                    <?php if ($progress['answered'] < $progress['total']): ?>
                        <button type="button" class="btn btn-next btn-pill" id="btnNext" data-mode="next">Next ?</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-success btn-pill" id="btnSubmitLevel">Submit Level ?</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</section>

<!-- Level Result Modal -->
<div class="modal fade adaptive-modal" id="levelResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div id="levelResultIcon" class="level-result-icon">
                    <!-- Icon will be set by JS -->
                </div>
                <h3 id="levelResultTitle" class="mb-3"></h3>
                <p id="levelResultMessage" class="text-muted"></p>
                
                <div class="row mt-4">
                    <div class="col-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted">Your Score</small>
                                <h2 id="yourScore" class="mb-0">--%</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <small class="text-muted">Required</small>
                                <h2 id="requiredScore" class="mb-0">--%</h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4" id="levelResultActions">
                    <!-- Buttons will be added by JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Adaptive Quiz JavaScript
class AdaptiveQuizManager {
    constructor(config) {
        this.attemptId = config.attemptId;
        this.quizId = config.quizId;
        this.currentLevel = config.currentLevel;
        this.csrfToken = config.csrfToken;
        this.csrfTokenName = config.csrfTokenName;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Submit Level button
        const submitBtn = document.getElementById('btnSubmitLevel');
        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitCurrentLevel();
            });
        }
        
        // Override final submit if it's a normal submit button
        const oldSubmitBtn = document.querySelector('#btnNext[data-mode="submit"]');
        if (oldSubmitBtn) {
            oldSubmitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitCurrentLevel();
            });
        }
    }
    
    async submitCurrentLevel() {
        // Show loading
        this.showLoading('Evaluating your performance...');
        
        try {
            const formData = new FormData();
            formData.append(this.csrfTokenName, this.csrfToken);
            formData.append('attempt_id', this.attemptId);
            
            const response = await fetch('<?= base_url("student/quizzes/submit-level") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showLevelResult(result);
            } else {
                this.showError(result.message || 'Failed to submit level');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Network error. Please try again.');
        }
    }
    
    showLevelResult(result) {
        const modal = new bootstrap.Modal(document.getElementById('levelResultModal'));
        const iconEl = document.getElementById('levelResultIcon');
        const titleEl = document.getElementById('levelResultTitle');
        const messageEl = document.getElementById('levelResultMessage');
        const yourScoreEl = document.getElementById('yourScore');
        const requiredScoreEl = document.getElementById('requiredScore');
        const actionsEl = document.getElementById('levelResultActions');
        
        // Set scores
        yourScoreEl.textContent = `${result.score.percentage}%`;
        requiredScoreEl.textContent = `${result.min_pass}%`;
        
        if (result.passed) {
            // Passed level
            iconEl.className = 'level-result-icon level-passed';
            iconEl.innerHTML = '<i class="fas fa-trophy"></i>';
            titleEl.textContent = 'Level Completed! ??';
            messageEl.textContent = `Great job! You scored ${result.score.percentage}% and passed this level.`;
            
            // Set actions
            if (result.has_next_level) {
                actionsEl.innerHTML = `
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">
                        Review Answers
                    </button>
                    <button type="button" class="btn next-level-btn" onclick="adaptiveQuiz.moveToNextLevel()">
                        Continue to Next Level <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                `;
            } else {
                // Quiz completed
                actionsEl.innerHTML = `
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">
                        Review Answers
                    </button>
                    <button type="button" class="btn complete-btn" onclick="adaptiveQuiz.completeQuiz()">
                        Complete Quiz <i class="fas fa-check ms-1"></i>
                    </button>
                `;
            }
        } else {
            // Failed level
            iconEl.className = 'level-result-icon level-failed';
            iconEl.innerHTML = '<i class="fas fa-redo"></i>';
            titleEl.textContent = 'More Practice Needed';
            messageEl.textContent = `You scored ${result.score.percentage}%. You need ${result.min_pass}% to pass this level.`;
            
            // Set actions
            actionsEl.innerHTML = `
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">
                    Review Mistakes
                </button>
                <button type="button" class="btn retry-btn" onclick="adaptiveQuiz.retryLevel()">
                    Retry Level <i class="fas fa-redo ms-1"></i>
                </button>
            `;
        }
        
        modal.show();
    }
    
    async moveToNextLevel() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('levelResultModal'));
        modal.hide();
        
        this.showLoading('Moving to next level...');
        
        try {
            const formData = new FormData();
            formData.append(this.csrfTokenName, this.csrfToken);
            formData.append('attempt_id', this.attemptId);
            
            const response = await fetch('<?= base_url("student/quizzes/move-to-next-level") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.redirect;
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Failed to move to next level');
        }
    }
    
    async retryLevel() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('levelResultModal'));
        modal.hide();
        
        this.showLoading('Resetting level...');
        
        try {
            const formData = new FormData();
            formData.append(this.csrfTokenName, this.csrfToken);
            formData.append('attempt_id', this.attemptId);
            
            const response = await fetch('<?= base_url("student/quizzes/retry-current-level") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.redirect;
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Failed to retry level');
        }
    }
    
    async completeQuiz() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('levelResultModal'));
        modal.hide();
        
        this.showLoading('Finalizing quiz...');
        
        try {
            const formData = new FormData();
            formData.append(this.csrfTokenName, this.csrfToken);
            formData.append('attempt_id', this.attemptId);
            
            const response = await fetch('<?= base_url("student/quizzes/complete-adaptive-quiz") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.redirect;
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Failed to complete quiz');
        }
    }
    
    showLoading(message) {
        // Create or show loading overlay
        let loadingEl = document.getElementById('adaptiveLoading');
        if (!loadingEl) {
            loadingEl = document.createElement('div');
            loadingEl.id = 'adaptiveLoading';
            loadingEl.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
            loadingEl.style.background = 'rgba(0,0,0,0.5)';
            loadingEl.style.zIndex = '9999';
            loadingEl.innerHTML = `
                <div class="spinner-border text-white" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="ms-3 text-white">${message}</div>
            `;
            document.body.appendChild(loadingEl);
        }
        loadingEl.style.display = 'flex';
    }
    
    hideLoading() {
        const loadingEl = document.getElementById('adaptiveLoading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    }
    
    showError(message) {
        this.hideLoading();
        alert('Error: ' + message);
    }
}

// Initialize adaptive quiz
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token
    const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]')?.value;
    
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }
    
    // Initialize adaptive quiz manager
    window.adaptiveQuiz = new AdaptiveQuizManager({
        attemptId: <?= (int)$attemptId ?>,
        quizId: <?= (int)$quiz->quiz_id ?>,
        currentLevel: <?= (int)($levelInfo->level_id ?? 0) ?>,
        csrfToken: csrfToken,
        csrfTokenName: '<?= csrf_token() ?>'
    });
    
    // Track progress updates
    const answerInputs = document.querySelectorAll('.answer-input');
    answerInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateProgressDisplay();
        });
    });
    
    function updateProgressDisplay() {
        // Count checked/answered questions
        const totalQuestions = <?= $progress['total'] ?? 0 ?>;
        const answered = document.querySelectorAll('.answer-input:checked, .answer-input[type="text"][value!=""], .answer-input[type="textarea"][value!=""]').length;
        
        // Update progress bar if exists
        const progressBar = document.querySelector('.level-progress .progress-bar');
        const progressLabel = document.querySelector('.level-progress .progress-label span:last-child');
        
        if (progressBar && progressLabel) {
            const percentage = totalQuestions > 0 ? (answered / totalQuestions) * 100 : 0;
            progressBar.style.width = `${percentage}%`;
            progressLabel.textContent = `${answered}/${totalQuestions} answered`;
        }
        
        // Update submit button
        if (answered >= totalQuestions) {
            const nextBtn = document.getElementById('btnNext');
            if (nextBtn && nextBtn.dataset.mode === 'next') {
                nextBtn.remove();
                
                // Add submit level button
                const footer = document.querySelector('.quiz-footer .d-flex');
                if (footer) {
                    const submitBtn = document.createElement('button');
                    submitBtn.type = 'button';
                    submitBtn.className = 'btn btn-success btn-pill';
                    submitBtn.id = 'btnSubmitLevel';
                    submitBtn.textContent = 'Submit Level ?';
                    submitBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.adaptiveQuiz.submitCurrentLevel();
                    });
                    footer.appendChild(submitBtn);
                }
            }
        }
    }
    
    // Initial progress update
    updateProgressDisplay();
});
</script>

<?= $this->endSection() ?>