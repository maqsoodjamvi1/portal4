<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-edit"></i> Edit Questions for:
            <small><?= esc($quiz->title ?? 'Untitled Quiz') ?></small>
        </h1>
        <a href="<?= site_url('admin/quizzes') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Quizzes
        </a>
    </div>
</section>

<section class="content">
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('msg')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    
    <!-- Quiz Info Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Quiz Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Title:</strong><br>
                    <?= esc($quiz->title ?? 'N/A') ?>
                </div>
                <div class="col-md-3">
                    <strong>Class:</strong><br>
                    <?= esc($quizInfo->class_name ?? 'N/A') ?> - <?= esc($quizInfo->section_name ?? 'N/A') ?>
                </div>
                <div class="col-md-3">
                    <strong>Subject:</strong><br>
                    <?= esc($quizInfo->subject_name ?? $quizInfo->subject_short_name ?? 'N/A') ?>
                </div>
                <div class="col-md-3">
                    <strong>Questions:</strong><br>
                    <?= count($questions) ?> question(s)
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Questions Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Questions</h3>
            <div class="card-tools">
                <button type="button" id="btnAddQuestion" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Question
                </button>
            </div>
        </div>
        
       <form action="<?= site_url('admin/quizzes/update-questions/' . $quizId) ?>" 
      method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="card-body">
                <!-- Class/Subject/Topic Selection -->
                
                   
                <!-- Questions Container -->
                <div id="questionList">
                    <?php if (empty($questions)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No questions found for this quiz.
                        </div>
                    <?php else: ?>
                        <?php foreach ($questions as $index => $q): ?>
                            <?php 
                                // Parse JSON data
                                $correctMulti = [];
                                $matchPairs = [];
                                
                                if ($q['question_type'] === 'mcq_multi' && !empty($q['options_json'])) {
                                    $json = json_decode($q['options_json'], true);
                                    $correctMulti = $json['correct_multi'] ?? [];
                                } elseif ($q['question_type'] === 'match' && !empty($q['options_json'])) {
                                    $json = json_decode($q['options_json'], true);
                                    $matchPairs = is_array($json) ? $json : [];
                                }
                            ?>
                            
                            <div class="card mb-3 question-card" data-qidx="<?= $index ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Question #<?= $index + 1 ?></strong>
                                    <div>
                                        <select name="questions[<?= $index ?>][question_type]" 
                                                class="form-control form-control-sm q-type">
                                            <option value="mcq" <?= $q['question_type'] === 'mcq' ? 'selected' : '' ?>>MCQ</option>
                                            <option value="mcq_multi" <?= $q['question_type'] === 'mcq_multi' ? 'selected' : '' ?>>MCQ (Multi)</option>
                                            <option value="tf" <?= $q['question_type'] === 'tf' ? 'selected' : '' ?>>True/False</option>
                                            <option value="fill" <?= $q['question_type'] === 'fill' ? 'selected' : '' ?>>Fill</option>
                                            <option value="short" <?= $q['question_type'] === 'short' ? 'selected' : '' ?>>Short</option>
                                            <option value="match" <?= $q['question_type'] === 'match' ? 'selected' : '' ?>>Match</option>
                                        </select>
                                        
                                        <button type="button" class="btn btn-light btn-sm ml-2 btn-move-up">?</button>
                                        <button type="button" class="btn btn-light btn-sm btn-move-down">?</button>
                                        <button type="button" class="btn btn-danger btn-sm ml-2 btn-remove">×</button>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <!-- Hidden IDs -->
                                    <input type="hidden" name="questions[<?= $index ?>][id]" value="<?= $q['id'] ?>">
                                    <input type="hidden" name="questions[<?= $index ?>][existing_image]" value="<?= $q['question_image'] ?>">
                                    
                                    <!-- Question Mode -->
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>Question Mode</label>
                                            <select name="questions[<?= $index ?>][question_media]" class="form-control form-control-sm q-media">
                                                <option value="text" <?= ($q['question_media'] ?? 'text') === 'text' ? 'selected' : '' ?>>Text</option>
                                                <option value="image" <?= ($q['question_media'] ?? 'text') === 'image' ? 'selected' : '' ?>>Image</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Question Text/Image -->
                                    <div class="form-group q-text-wrap" style="<?= ($q['question_media'] ?? 'text') === 'image' ? 'display:none;' : '' ?>">
                                        <label>Question (Text)</label>
                                        <textarea name="questions[<?= $index ?>][question]" 
                                                  class="form-control q-text" rows="3"><?= esc($q['question'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group q-image-wrap" style="<?= ($q['question_media'] ?? 'text') === 'text' ? 'display:none;' : '' ?>">
                                        <label>Question Image</label>
                                        <input type="file" 
                                               class="form-control-file q-image" 
                                               name="questions[<?= $index ?>][question_image]" 
                                               accept="image/*">
                                        <small class="text-muted">JPG/PNG/WEBP – Max 2MB</small>
                                        
                                        <?php if (!empty($q['question_image'])): ?>
                                            <div class="mt-2">
                                                <img src="<?= base_url($q['question_image']) ?>" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 150px;">
                                                <small class="d-block text-muted">Current image</small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <textarea name="questions[<?= $index ?>][question_image_alt]" 
                                                  class="form-control mt-2" 
                                                  rows="2"
                                                  placeholder="Optional: image alt text"><?= esc($q['question_image_alt'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <!-- Difficulty -->
                                    <div class="form-group">
                                        <label>Difficulty</label>
                                        <select name="questions[<?= $index ?>][difficulty]" class="form-control form-control-sm">
                                            <option value="easy" <?= ($q['difficulty'] ?? 'normal') === 'easy' ? 'selected' : '' ?>>Easy</option>
                                            <option value="normal" <?= ($q['difficulty'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                                            <option value="hard" <?= ($q['difficulty'] ?? 'normal') === 'hard' ? 'selected' : '' ?>>Hard</option>
                                        </select>
                                    </div>
                                    
                                    <!-- MCQ Options -->
                                    <div class="q-block q-mcq q-mcq_multi" style="<?= !in_array($q['question_type'], ['mcq', 'mcq_multi']) ? 'display:none;' : '' ?>">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>A</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="questions[<?= $index ?>][option_a]" 
                                                       value="<?= esc($q['option_a'] ?? '') ?>">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>B</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="questions[<?= $index ?>][option_b]" 
                                                       value="<?= esc($q['option_b'] ?? '') ?>">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>C</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="questions[<?= $index ?>][option_c]" 
                                                       value="<?= esc($q['option_c'] ?? '') ?>">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>D</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="questions[<?= $index ?>][option_d]" 
                                                       value="<?= esc($q['option_d'] ?? '') ?>">
                                            </div>
                                        </div>
                                        
                                        <!-- Single Correct (for MCQ) -->
                                        <div class="mcq-single-correct" style="<?= $q['question_type'] === 'mcq_multi' ? 'display:none;' : '' ?>">
                                            <label>Correct Option</label>
                                            <select name="questions[<?= $index ?>][correct_option]" class="form-control">
                                                <option value="A" <?= ($q['correct_option'] ?? '') === 'A' ? 'selected' : '' ?>>A</option>
                                                <option value="B" <?= ($q['correct_option'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                                                <option value="C" <?= ($q['correct_option'] ?? '') === 'C' ? 'selected' : '' ?>>C</option>
                                                <option value="D" <?= ($q['correct_option'] ?? '') === 'D' ? 'selected' : '' ?>>D</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Multiple Correct (for MCQ Multi) -->
                                        <div class="mcq-multi-correct" style="<?= $q['question_type'] !== 'mcq_multi' ? 'display:none;' : '' ?>">
                                            <label>Correct Options (Multiple)</label>
                                            <div>
                                                <label><input type="checkbox" name="questions[<?= $index ?>][correct_multi][]" value="A" <?= in_array('A', $correctMulti) ? 'checked' : '' ?>> A</label><br>
                                                <label><input type="checkbox" name="questions[<?= $index ?>][correct_multi][]" value="B" <?= in_array('B', $correctMulti) ? 'checked' : '' ?>> B</label><br>
                                                <label><input type="checkbox" name="questions[<?= $index ?>][correct_multi][]" value="C" <?= in_array('C', $correctMulti) ? 'checked' : '' ?>> C</label><br>
                                                <label><input type="checkbox" name="questions[<?= $index ?>][correct_multi][]" value="D" <?= in_array('D', $correctMulti) ? 'checked' : '' ?>> D</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- True/False -->
                                    <div class="q-block q-tf" style="<?= $q['question_type'] !== 'tf' ? 'display:none;' : '' ?>">
                                        <label>Answer</label>
                                        <select name="questions[<?= $index ?>][answer_text]" class="form-control">
                                            <option value="True" <?= ($q['answer_text'] ?? '') === 'True' ? 'selected' : '' ?>>True</option>
                                            <option value="False" <?= ($q['answer_text'] ?? '') === 'False' ? 'selected' : '' ?>>False</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Fill/Short -->
                                    <div class="q-block q-fill q-short" style="<?= !in_array($q['question_type'], ['fill', 'short']) ? 'display:none;' : '' ?>">
                                        <label>Expected Answer</label>
                                        <input type="text" 
                                               name="questions[<?= $index ?>][answer_text]" 
                                               class="form-control" 
                                               value="<?= esc($q['answer_text'] ?? '') ?>">
                                    </div>
                                    
                                    <!-- Match -->
                                    <div class="q-block q-match" style="<?= $q['question_type'] !== 'match' ? 'display:none;' : '' ?>">
                                        <div class="d-flex align-items-center mb-2">
                                            <label class="mr-3">Match Pairs (Left ? Right)</label>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="questions[<?= $index ?>][is_drag]" 
                                                       value="1" 
                                                       <?= ($q['is_drag'] ?? 0) ? 'checked' : '' ?>>
                                                <label class="form-check-label">Draggable</label>
                                            </div>
                                        </div>
                                        
                                        <div class="match-pairs">
                                            <?php if (!empty($matchPairs)): ?>
                                                <?php foreach ($matchPairs as $pairIdx => $pair): ?>
                                                    <div class="form-row mb-2">
                                                        <div class="col">
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   name="questions[<?= $index ?>][match_pairs][<?= $pairIdx ?>][left]" 
                                                                   placeholder="Left" 
                                                                   value="<?= esc($pair['left'] ?? '') ?>">
                                                        </div>
                                                        <div class="col">
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   name="questions[<?= $index ?>][match_pairs][<?= $pairIdx ?>][right]" 
                                                                   placeholder="Right" 
                                                                   value="<?= esc($pair['right'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-auto">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">×</button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="form-row mb-2">
                                                    <div class="col">
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               name="questions[<?= $index ?>][match_pairs][0][left]" 
                                                               placeholder="Left">
                                                    </div>
                                                    <div class="col">
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               name="questions[<?= $index ?>][match_pairs][0][right]" 
                                                               placeholder="Right">
                                                    </div>
                                                    <div class="col-auto">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">×</button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-add-pair mt-2">+ Add Pair</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update All Questions
                </button>
            </div>
        </form>
    </div>
</section>

<style>
.question-card {
    border-left: 4px solid #007bff;
}
.q-image-wrap img {
    max-height: 150px;
    object-fit: contain;
}
.btn-remove-pair {
    padding: 0.25rem 0.5rem;
}
</style>

<script>
$(document).ready(function() {
    let questionCount = <?= count($questions) ?>;
    
    // Add new question
    $('#btnAddQuestion').click(function() {
        const index = questionCount++;
        const html = `
            <div class="card mb-3 question-card" data-qidx="${index}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Question #${index + 1}</strong>
                    <div>
                        <select name="questions[${index}][question_type]" class="form-control form-control-sm q-type">
                            <option value="mcq">MCQ</option>
                            <option value="mcq_multi">MCQ (Multi)</option>
                            <option value="tf">True/False</option>
                            <option value="fill">Fill</option>
                            <option value="short">Short</option>
                            <option value="match">Match</option>
                        </select>
                        
                        <button type="button" class="btn btn-light btn-sm ml-2 btn-move-up">?</button>
                        <button type="button" class="btn btn-light btn-sm btn-move-down">?</button>
                        <button type="button" class="btn btn-danger btn-sm ml-2 btn-remove">×</button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Hidden ID field -->
                    <input type="hidden" name="questions[${index}][id]" value="0">
                    
                    <!-- Question Mode -->
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Question Mode</label>
                            <select name="questions[${index}][question_media]" class="form-control form-control-sm q-media">
                                <option value="text">Text</option>
                                <option value="image">Image</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Question Text/Image -->
                    <div class="form-group q-text-wrap">
                        <label>Question (Text)</label>
                        <textarea name="questions[${index}][question]" class="form-control q-text" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group q-image-wrap d-none">
                        <label>Question Image</label>
                        <input type="file" class="form-control-file q-image" name="questions[${index}][question_image]" accept="image/*">
                        <small class="text-muted">JPG/PNG/WEBP – Max 2MB</small>
                        <textarea name="questions[${index}][question_image_alt]" class="form-control mt-2" rows="2" placeholder="Optional: image alt text"></textarea>
                    </div>
                    
                    <!-- Difficulty -->
                    <div class="form-group">
                        <label>Difficulty</label>
                        <select name="questions[${index}][difficulty]" class="form-control form-control-sm">
                            <option value="easy">Easy</option>
                            <option value="normal">Normal</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                    
                    <!-- MCQ Options (hidden by default) -->
                    <div class="q-block q-mcq q-mcq_multi d-none">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>A</label>
                                <input type="text" class="form-control" name="questions[${index}][option_a]">
                            </div>
                            <div class="form-group col-md-6">
                                <label>B</label>
                                <input type="text" class="form-control" name="questions[${index}][option_b]">
                            </div>
                            <div class="form-group col-md-6">
                                <label>C</label>
                                <input type="text" class="form-control" name="questions[${index}][option_c]">
                            </div>
                            <div class="form-group col-md-6">
                                <label>D</label>
                                <input type="text" class="form-control" name="questions[${index}][option_d]">
                            </div>
                        </div>
                        
                        <div class="mcq-single-correct">
                            <label>Correct Option</label>
                            <select name="questions[${index}][correct_option]" class="form-control">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        
                        <div class="mcq-multi-correct d-none">
                            <label>Correct Options (Multiple)</label>
                            <div>
                                <label><input type="checkbox" name="questions[${index}][correct_multi][]" value="A"> A</label><br>
                                <label><input type="checkbox" name="questions[${index}][correct_multi][]" value="B"> B</label><br>
                                <label><input type="checkbox" name="questions[${index}][correct_multi][]" value="C"> C</label><br>
                                <label><input type="checkbox" name="questions[${index}][correct_multi][]" value="D"> D</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other question types (hidden) -->
                    <div class="q-block q-tf d-none">
                        <label>Answer</label>
                        <select name="questions[${index}][answer_text]" class="form-control">
                            <option value="True">True</option>
                            <option value="False">False</option>
                        </select>
                    </div>
                    
                    <div class="q-block q-fill q-short d-none">
                        <label>Expected Answer</label>
                        <input type="text" name="questions[${index}][answer_text]" class="form-control">
                    </div>
                    
                    <div class="q-block q-match d-none">
                        <div class="d-flex align-items-center mb-2">
                            <label class="mr-3">Match Pairs (Left ? Right)</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="questions[${index}][is_drag]" value="1">
                                <label class="form-check-label">Draggable</label>
                            </div>
                        </div>
                        <div class="match-pairs">
                            <div class="form-row mb-2">
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm" name="questions[${index}][match_pairs][0][left]" placeholder="Left">
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control form-control-sm" name="questions[${index}][match_pairs][0][right]" placeholder="Right">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">×</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-add-pair mt-2">+ Add Pair</button>
                    </div>
                </div>
            </div>
        `;
        
        $('#questionList').append(html);
        renumberQuestions();
    });
    
    // Question media toggle
    $(document).on('change', '.q-media', function() {
        const card = $(this).closest('.question-card');
        const isImage = $(this).val() === 'image';
        
        card.find('.q-text-wrap').toggle(!isImage);
        card.find('.q-image-wrap').toggle(isImage);
        
        if (isImage) {
            card.find('.q-text').removeAttr('required');
        } else {
            card.find('.q-text').attr('required', 'required');
        }
    });
    
    // Question type change
    $(document).on('change', '.q-type', function() {
        const card = $(this).closest('.question-card');
        const type = $(this).val();
        const index = card.data('qidx');
        
        // Hide all question blocks
        card.find('.q-block').addClass('d-none');
        
        // Show appropriate block
        if (type === 'mcq' || type === 'mcq_multi' || type === 'match') {
            card.find('.q-' + type).removeClass('d-none');
            
            // Toggle MCQ single/multi
            if (type === 'mcq_multi') {
                card.find('.mcq-single-correct').addClass('d-none');
                card.find('.mcq-multi-correct').removeClass('d-none');
            } else if (type === 'mcq') {
                card.find('.mcq-single-correct').removeClass('d-none');
                card.find('.mcq-multi-correct').addClass('d-none');
            }
            
            // For match, ensure at least one pair
            if (type === 'match' && card.find('.match-pairs .form-row').length === 0) {
                addMatchPair(card, index);
            }
        } else {
            // For tf/fill/short
            const selector = type === 'tf' ? '.q-tf' : 
                           (type === 'fill' || type === 'short') ? '.q-fill.q-short' : '';
            card.find(selector).removeClass('d-none');
        }
    });
    
    // Add match pair
    $(document).on('click', '.btn-add-pair', function() {
        const card = $(this).closest('.question-card');
        const index = card.data('qidx');
        addMatchPair(card, index);
    });
    
    // Remove match pair
    $(document).on('click', '.btn-remove-pair', function() {
        if ($(this).closest('.match-pairs').find('.form-row').length > 1) {
            $(this).closest('.form-row').remove();
        }
    });
    
    // Move question up
    $(document).on('click', '.btn-move-up', function() {
        const card = $(this).closest('.question-card');
        const prev = card.prev('.question-card');
        if (prev.length) {
            card.insertBefore(prev);
            renumberQuestions();
        }
    });
    
    // Move question down
    $(document).on('click', '.btn-move-down', function() {
        const card = $(this).closest('.question-card');
        const next = card.next('.question-card');
        if (next.length) {
            card.insertAfter(next);
            renumberQuestions();
        }
    });
    
    // Remove question
    $(document).on('click', '.btn-remove', function() {
        if ($('#questionList .question-card').length > 1) {
            $(this).closest('.question-card').remove();
            renumberQuestions();
        } else {
            alert('At least one question is required.');
        }
    });
    
    // Subject dropdown based on class
    $('#edit_class_id').change(function() {
        const classId = $(this).val();
        if (!classId) return;
        
        $.get('<?= site_url('admin/question-bank/subjects') ?>?class_id=' + classId, function(data) {
            const subjects = Array.isArray(data) ? data : (data.subjects || []);
            const select = $('#edit_subject_id');
            select.html('<option value="">-- Select Subject --</option>');
            
            subjects.forEach(function(s) {
                select.append(new Option(s.subject_name, s.subject_id));
            });
        }).fail(function() {
            $('#edit_subject_id').html('<option value="">-- Select Subject --</option>');
        });
    });
    
   
    // Helper functions
    function addMatchPair(card, index) {
        const pairCount = card.find('.match-pairs .form-row').length;
        const html = `
            <div class="form-row mb-2">
                <div class="col">
                    <input type="text" class="form-control form-control-sm" name="questions[${index}][match_pairs][${pairCount}][left]" placeholder="Left">
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" name="questions[${index}][match_pairs][${pairCount}][right]" placeholder="Right">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">×</button>
                </div>
            </div>
        `;
        card.find('.match-pairs').append(html);
    }
    
    function renumberQuestions() {
        $('#questionList .question-card').each(function(index) {
            $(this).find('strong').text('Question #' + (index + 1));
            $(this).data('qidx', index);
            
            // Update all input names with new index
            $(this).find('[name]').each(function() {
                const oldName = $(this).attr('name');
                if (oldName && oldName.includes('questions[')) {
                    const newName = oldName.replace(/questions\[\d+\]/, 'questions[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }
});
</script>

<?= $this->endSection() ?>