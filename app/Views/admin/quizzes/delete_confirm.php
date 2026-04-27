<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-trash-alt"></i> Delete Options for Quiz</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($quiz) && $quiz): ?>
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Quiz Information</h5>
                            <div class="p-3 border rounded bg-white mt-2">
                                <h5 class="text-primary mb-3"><?= esc($quiz->title) ?></h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-graduation-cap"></i> Class:</strong><br>
                                        <?= esc($quiz->class_name) ?> - <?= esc($quiz->section_name) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-book"></i> Subject:</strong><br>
                                        <?= esc($quiz->subject_name) ?>
                                    </div>
                                </div>
                                <?php if (isset($quiz->start_at) || isset($quiz->end_at)): ?>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <strong><i class="fas fa-calendar-alt"></i> Date Range:</strong><br>
                                            <?= !empty($quiz->start_at) ? date('M d, Y', strtotime($quiz->start_at)) : 'N/A' ?> 
                                            to 
                                            <?= !empty($quiz->end_at) ? date('M d, Y', strtotime($quiz->end_at)) : 'N/A' ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Statistics -->
                        <div class="row mb-4">
                            <?php if ($questionCount > 0): ?>
                                <div class="col-md-4">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6><i class="fas fa-question-circle text-warning"></i> Questions</h6>
                                            <h3><?= $questionCount ?></h3>
                                            <small class="text-muted">Total questions in this quiz</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($attemptCount > 0): ?>
                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6><i class="fas fa-users text-info"></i> Attempts</h6>
                                            <h3><?= $attemptCount ?></h3>
                                            <small class="text-muted">Student attempts recorded</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="col-md-4">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <h6><i class="fas fa-exclamation-triangle text-danger"></i> Warning</h6>
                                        <p class="mb-0"><small>All actions are permanent and cannot be undone</small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delete Options -->
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Select Delete Option</h5>
                            <p class="mb-0">Choose what you want to delete:</p>
                        </div>
                        
                        <form method="POST" action="<?= site_url('admin/quizzes/delete-quiz/' . $quiz->quiz_id) ?>" id="deleteForm">
                            <?= csrf_field() ?>
                            
                            <!-- Delete Type Selection -->
                            <div class="form-group">
                                <label class="font-weight-bold">What do you want to delete?</label>
                                <div class="card-options">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="delete_type" 
                                               id="deleteAll" value="all" checked>
                                        <label class="form-check-label" for="deleteAll">
                                            <div class="card border-danger">
                                                <div class="card-body">
                                                    <h5 class="card-title text-danger">
                                                        <i class="fas fa-bomb"></i> Option 1: Delete Everything
                                                    </h5>
                                                    <p class="card-text">
                                                        <strong>Will delete:</strong><br>
                                                        • The quiz itself<br>
                                                        • All <?= $questionCount ?> questions<br>
                                                        • All <?= $attemptCount ?> student results<br>
                                                        <span class="text-danger font-weight-bold">Complete wipe - nothing remains</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="delete_type" 
                                               id="deleteQuestions" value="questions">
                                        <label class="form-check-label" for="deleteQuestions">
                                            <div class="card border-warning">
                                                <div class="card-body">
                                                    <h5 class="card-title text-warning">
                                                        <i class="fas fa-question-circle"></i> Option 2: Delete Questions Only
                                                    </h5>
                                                    <p class="card-text">
                                                        <strong>Will delete:</strong><br>
                                                        • All <?= $questionCount ?> questions<br>
                                                        • All <?= $attemptCount ?> student results<br>
                                                        <strong>Will keep:</strong><br>
                                                        • The quiz structure (can add new questions later)
                                                    </p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="delete_type" 
                                               id="deleteResults" value="results">
                                        <label class="form-check-label" for="deleteResults">
                                            <div class="card border-info">
                                                <div class="card-body">
                                                    <h5 class="card-title text-info">
                                                        <i class="fas fa-chart-bar"></i> Option 3: Delete Results Only
                                                    </h5>
                                                    <p class="card-text">
                                                        <strong>Will delete:</strong><br>
                                                        • All <?= $attemptCount ?> student results<br>
                                                        <strong>Will keep:</strong><br>
                                                        • The quiz structure<br>
                                                        • All <?= $questionCount ?> questions<br>
                                                        <em>Students can retake the quiz</em>
                                                    </p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Confirmation -->
                            <div class="form-group mt-4">
                                <label for="confirmation" class="font-weight-bold">
                                    <i class="fas fa-keyboard"></i> Type the delete option name to confirm:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <span id="confirmationHint" class="font-weight-bold">DELETE ALL</span>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           class="form-control form-control-lg text-center font-weight-bold" 
                                           id="confirmation" 
                                           name="confirmation" 
                                           placeholder="Type option name here" 
                                           required
                                           autocomplete="off"
                                           style="font-size: 1.2rem; letter-spacing: 1px;">
                                </div>
                                <small class="form-text text-muted">
                                    Type exactly: <span id="requiredText" class="font-weight-bold">DELETE ALL</span>
                                </small>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="mt-4 pt-3 border-top">
                                <a href="<?= base_url('admin/quizzes') ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> Cancel & Go Back
                                </a>
                                <button type="submit" class="btn btn-danger btn-lg float-right" id="submitBtn">
                                    <i class="fas fa-trash"></i> Execute Delete
                                </button>
                            </div>
                        </form>
                        
                        <script>
                        // Debug logging
                        console.log("Delete confirmation page loaded");
                        console.log("Quiz ID:", <?= $quiz->quiz_id ?>);
                        console.log("Questions:", <?= $questionCount ?>, "Attempts:", <?= $attemptCount ?>);
                        
                        // Update confirmation text based on selected option
                        const deleteOptions = document.querySelectorAll('input[name="delete_type"]');
                        const confirmationHint = document.getElementById('confirmationHint');
                        const requiredText = document.getElementById('requiredText');
                        const confirmationInput = document.getElementById('confirmation');
                        
                        // Set initial values
                        let requiredConfirmText = 'DELETE ALL';
                        
                        // Update when radio button changes
                        deleteOptions.forEach(option => {
                            option.addEventListener('change', function() {
                                if (this.value === 'all') {
                                    requiredConfirmText = 'DELETE ALL';
                                } else if (this.value === 'questions') {
                                    requiredConfirmText = 'DELETE QUESTIONS';
                                } else if (this.value === 'results') {
                                    requiredConfirmText = 'DELETE RESULTS';
                                }
                                
                                confirmationHint.textContent = requiredConfirmText;
                                requiredText.textContent = requiredConfirmText;
                                confirmationInput.placeholder = 'Type ' + requiredConfirmText + ' here';
                                confirmationInput.value = ''; // Clear previous input
                            });
                        });
                        
                        // Form validation
                        document.getElementById('deleteForm').addEventListener('submit', function(e) {
                            const selectedOption = document.querySelector('input[name="delete_type"]:checked').value;
                            const confirmation = confirmationInput.value.toUpperCase();
                            const submitBtn = document.getElementById('submitBtn');
                            
                            console.log("Form submitted");
                            console.log("Selected option:", selectedOption);
                            console.log("Confirmation entered:", confirmation);
                            console.log("Required confirmation:", requiredConfirmText);
                            
                            // Get the required text based on selected option
                            let requiredText = 'DELETE ALL';
                            if (selectedOption === 'questions') requiredText = 'DELETE QUESTIONS';
                            if (selectedOption === 'results') requiredText = 'DELETE RESULTS';
                            
                            if (confirmation !== requiredText) {
                                e.preventDefault();
                                alert('Please type "' + requiredText + '" exactly to confirm.');
                                confirmationInput.focus();
                                return false;
                            }
                            
                            // Final warning
                            let warningMessage = '';
                            if (selectedOption === 'all') {
                                warningMessage = 'This will delete the ENTIRE QUIZ including all questions and results. This action cannot be undone!\n\nAre you absolutely sure?';
                            } else if (selectedOption === 'questions') {
                                warningMessage = 'This will delete all ' + <?= $questionCount ?> + ' questions and ' + <?= $attemptCount ?> + ' student results. The quiz structure will remain.\n\nAre you sure?';
                            } else if (selectedOption === 'results') {
                                warningMessage = 'This will delete all ' + <?= $attemptCount ?> + ' student results. Students will be able to retake the quiz.\n\nAre you sure?';
                            }
                            
                            if (!confirm(warningMessage)) {
                                e.preventDefault();
                                return false;
                            }
                            
                            // Disable button and show loading
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                            submitBtn.disabled = true;
                            
                            return true;
                        });
                        
                        // Auto-uppercase for convenience
                        confirmationInput.addEventListener('input', function(e) {
                            this.value = this.value.toUpperCase();
                        });
                        </script>
                        
                        <!-- Debug information -->
                        <div class="mt-3 p-2 border rounded bg-light small">
                            <strong>Debug Info:</strong><br>
                            Quiz ID: <?= $quiz->quiz_id ?><br>
                            Questions: <?= $questionCount ?><br>
                            Attempts: <?= $attemptCount ?><br>
                            Form Action: <?= site_url('admin/quizzes/delete-quiz/' . $quiz->quiz_id) ?>
                        </div>
                        
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> Quiz not found!
                        </div>
                        <a href="<?= base_url('admin/quizzes') ?>" class="btn btn-primary">
                            <i class="fas fa-list"></i> Back to Quizzes List
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-options .form-check-input {
    position: absolute;
    left: 15px;
    top: 15px;
    z-index: 1;
}

.card-options .form-check-label {
    display: block;
    cursor: pointer;
}

.card-options .card {
    transition: all 0.3s;
    padding-left: 40px;
}

.card-options .form-check-input:checked + label .card {
    border-width: 3px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-options .form-check-input:checked#deleteAll + label .card {
    border-color: #dc3545;
    background-color: #fff5f5;
}

.card-options .form-check-input:checked#deleteQuestions + label .card {
    border-color: #ffc107;
    background-color: #fffcf5;
}

.card-options .form-check-input:checked#deleteResults + label .card {
    border-color: #17a2b8;
    background-color: #f5fdff;
}
</style>

<?= $this->endSection() ?>