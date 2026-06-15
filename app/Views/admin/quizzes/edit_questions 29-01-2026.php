<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Include necessary libraries -->
<link href="https://unpkg.com/cropperjs/dist/cropper.css" rel="stylesheet">
<script src="https://unpkg.com/cropperjs"></script>

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
                                
                                $isImageMode = ($q['question_media'] ?? 'text') === 'image';
                            ?>
                            
                            <div class="card mb-3 question-card" data-qidx="<?= $index ?>">
                                <!-- Header Row with Question Number, Type, Mode, Difficulty -->
                                <div class="card-header bg-light py-2">
                                    <div class="row align-items-center">
                                        <!-- Question Number -->
                                        <div class="col-md-2">
                                            <h5 class="mb-0">
                                                <span class="badge text-bg-primary">Q<?= $index + 1 ?></span>
                                            </h5>
                                        </div>
                                        
                                        <!-- Question Type -->
                                        <div class="col-md-3">
                                            <select name="questions[<?= $index ?>][question_type]" 
                                                    class="form-control form-control-sm q-type">
                                                <option value="mcq" <?= $q['question_type'] === 'mcq' ? 'selected' : '' ?>>MCQ Single</option>
                                                <option value="mcq_multi" <?= $q['question_type'] === 'mcq_multi' ? 'selected' : '' ?>>MCQ Multiple</option>
                                                <option value="tf" <?= $q['question_type'] === 'tf' ? 'selected' : '' ?>>True/False</option>
                                                <option value="fill" <?= $q['question_type'] === 'fill' ? 'selected' : '' ?>>Fill in Blank</option>
                                                <option value="short" <?= $q['question_type'] === 'short' ? 'selected' : '' ?>>Short Answer</option>
                                                <option value="match" <?= $q['question_type'] === 'match' ? 'selected' : '' ?>>Matching</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Difficulty Level -->
                                        <div class="col-md-2">
                                            <div class="difficulty-buttons">
                                                <div class="btn-group btn-group-sm w-100" role="group">
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="questions[<?= $index ?>][difficulty]" 
                                                           id="q<?= $index ?>_easy" 
                                                           value="easy" 
                                                           <?= ($q['difficulty'] ?? 'normal') === 'easy' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-success" for="q<?= $index ?>_easy">
                                                        <i class="fas fa-smile"></i> Easy
                                                    </label>
                                                    
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="questions[<?= $index ?>][difficulty]" 
                                                           id="q<?= $index ?>_normal" 
                                                           value="normal" 
                                                           <?= ($q['difficulty'] ?? 'normal') === 'normal' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-warning" for="q<?= $index ?>_normal">
                                                        <i class="fas fa-meh"></i> Normal
                                                    </label>
                                                    
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="questions[<?= $index ?>][difficulty]" 
                                                           id="q<?= $index ?>_hard" 
                                                           value="hard" 
                                                           <?= ($q['difficulty'] ?? 'normal') === 'hard' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-danger" for="q<?= $index ?>_hard">
                                                        <i class="fas fa-frown"></i> Hard
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Question Mode Toggle and Image Upload -->
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <!-- Question Mode Toggle -->
                                                <div class="btn-group btn-group-sm me-2" role="group">
                                                    <input type="radio" 
                                                           class="btn-check q-media-toggle" 
                                                           name="questions[<?= $index ?>][question_media]" 
                                                           id="q<?= $index ?>_text" 
                                                           value="text" 
                                                           data-qidx="<?= $index ?>"
                                                           <?= !$isImageMode ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-info" for="q<?= $index ?>_text">
                                                        <i class="fas fa-font"></i> Text
                                                    </label>
                                                    
                                                    <input type="radio" 
                                                           class="btn-check q-media-toggle" 
                                                           name="questions[<?= $index ?>][question_media]" 
                                                           id="q<?= $index ?>_image" 
                                                           value="image" 
                                                           data-qidx="<?= $index ?>"
                                                           <?= $isImageMode ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-info" for="q<?= $index ?>_image">
                                                        <i class="fas fa-image"></i> Image
                                                    </label>
                                                </div>
                                                
                                                <!-- Image Upload Button (only shown in image mode) -->
                                                <div class="image-upload-section" style="<?= !$isImageMode ? 'display:none;' : '' ?>">
                                                    <input type="file" 
                                                           class="d-none q-image-input" 
                                                           name="questions[<?= $index ?>][question_image]" 
                                                           accept="image/*" 
                                                           data-qidx="<?= $index ?>">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-secondary btn-browse-image" 
                                                            data-qidx="<?= $index ?>">
                                                        <i class="fas fa-folder-open"></i> Browse
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Move Buttons -->
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-light btn-sm btn-move-up" title="Move Up">
                                                <i class="fas fa-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm btn-move-down" title="Move Down">
                                                <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <!-- Hidden IDs -->
                                    <input type="hidden" name="questions[<?= $index ?>][id]" value="<?= $q['id'] ?>">
                                    <input type="hidden" name="questions[<?= $index ?>][existing_image]" value="<?= $q['question_image'] ?>">
                                    <input type="hidden" name="questions[<?= $index ?>][image_data]" class="image-data" value="">
                                    
                                    <!-- Question Content Area -->
                                    <div class="question-content-area mb-3">
                                        <!-- Text Mode Content -->
                                        <div class="q-text-wrap" style="<?= $isImageMode ? 'display:none;' : '' ?>">
                                            <label class="fw-bold">Question Text</label>
                                            <textarea name="questions[<?= $index ?>][question]" 
                                                      class="form-control q-text" 
                                                      rows="3" 
                                                      placeholder="Enter question text here..."><?= esc($q['question'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <!-- Image Mode Content -->
                                        <div class="q-image-wrap" style="<?= !$isImageMode ? 'display:none;' : '' ?>">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <!-- Image Preview and Editor -->
                                                    <div class="image-editor-container">
                                                        <div class="image-preview-container mb-3">
                                                            <?php if (!empty($q['question_image'])): ?>
                                                                <img src="<?= base_url($q['question_image']) ?>" 
                                                                     class="img-fluid q-image-preview" 
                                                                     id="preview_<?= $index ?>"
                                                                     style="max-height: 300px; display: block;">
                                                            <?php else: ?>
                                                                <div class="text-center py-4 border rounded bg-light">
                                                                    <i class="fas fa-image fa-3x text-muted mb-2"></i><br>
                                                                    <span class="text-muted">No image selected</span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <!-- Editor Tools -->
                                                        <div class="editor-tools d-none">
                                                            <div class="btn-group btn-group-sm mb-2">
                                                                <button type="button" class="btn btn-outline-primary btn-rotate-left" data-deg="-90">
                                                                    <i class="fas fa-undo"></i> Rotate Left
                                                                </button>
                                                                <button type="button" class="btn btn-outline-primary btn-rotate-right" data-deg="90">
                                                                    <i class="fas fa-redo"></i> Rotate Right
                                                                </button>
                                                                <button type="button" class="btn btn-outline-primary btn-flip-horizontal">
                                                                    <i class="fas fa-arrows-alt-h"></i> Flip H
                                                                </button>
                                                                <button type="button" class="btn btn-outline-primary btn-flip-vertical">
                                                                    <i class="fas fa-arrows-alt-v"></i> Flip V
                                                                </button>
                                                                <button type="button" class="btn btn-outline-success btn-crop">
                                                                    <i class="fas fa-crop"></i> Crop
                                                                </button>
                                                                <button type="button" class="btn btn-outline-info btn-reset">
                                                                    <i class="fas fa-sync"></i> Reset
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Crop Modal -->
                                                        <div class="modal fade crop-modal" id="cropModal_<?= $index ?>" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog modal-lg" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Crop Image</h5>
                                                                        <button type="button" class="close" data-bs-dismiss="modal">
                                                                            <span>&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="crop-container">
                                                                            <img src="" class="img-fluid" id="cropImage_<?= $index ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="button" class="btn btn-primary btn-crop-apply">Apply Crop</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <!-- Image Description -->
                                                    <div class="form-group">
                                                        <label>Image Description (Optional)</label>
                                                        <textarea name="questions[<?= $index ?>][question_image_alt]" 
                                                                  class="form-control" 
                                                                  rows="3"
                                                                  placeholder="Describe the image for accessibility"><?= esc($q['question_image_alt'] ?? '') ?></textarea>
                                                        <small class="text-muted">This helps screen readers describe the image.</small>
                                                    </div>
                                                    
                                                    <!-- Image Actions -->
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6 class="card-title">Image Actions</h6>
                                                            <div class="d-grid gap-2">
                                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-image" data-qidx="<?= $index ?>">
                                                                    <i class="fas fa-edit"></i> Edit Image
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-image" data-qidx="<?= $index ?>">
                                                                    <i class="fas fa-trash"></i> Remove Image
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- MCQ Single/Multiple Options -->
                                    <div class="q-block q-mcq q-mcq_multi" style="<?= !in_array($q['question_type'], ['mcq', 'mcq_multi']) ? 'display:none;' : '' ?>">
                                        <div class="mb-3">
                                            <label class="fw-bold d-block mb-2">Options</label>
                                            
                                            <!-- Correct Option Selector Row -->
                                            <div class="row mb-2">
                                                <div class="col-3 text-center">
                                                    <small class="text-muted">Correct Option</small>
                                                </div>
                                                <div class="col-3 text-center">
                                                    <small class="text-muted">Correct Option</small>
                                                </div>
                                                <div class="col-3 text-center">
                                                    <small class="text-muted">Correct Option</small>
                                                </div>
                                                <div class="col-3 text-center">
                                                    <small class="text-muted">Correct Option</small>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-2">
                                                <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                                    <div class="col-3">
                                                        <div class="text-center mb-1">
                                                            <?php if ($q['question_type'] === 'mcq'): ?>
                                                                <div class="form-check form-check d-inline-block">
                                                                    <input type="radio" 
                                                                           id="q<?= $index ?>_correct_<?= strtolower($opt) ?>" 
                                                                           name="questions[<?= $index ?>][correct_option]" 
                                                                           value="<?= $opt ?>" 
                                                                           class="form-check-input"
                                                                           <?= ($q['correct_option'] ?? '') === $opt ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="q<?= $index ?>_correct_<?= strtolower($opt) ?>"></label>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="form-check form-check d-inline-block">
                                                                    <input type="checkbox" 
                                                                           id="q<?= $index ?>_multi_<?= strtolower($opt) ?>" 
                                                                           name="questions[<?= $index ?>][correct_multi][]" 
                                                                           value="<?= $opt ?>" 
                                                                           class="form-check-input"
                                                                           <?= in_array($opt, $correctMulti) ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="q<?= $index ?>_multi_<?= strtolower($opt) ?>"></label>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            
                                            <!-- Option Inputs Row -->
                                            <div class="row">
                                                <?php 
                                                $optionFields = [
                                                    'A' => $q['option_a'] ?? '',
                                                    'B' => $q['option_b'] ?? '',
                                                    'C' => $q['option_c'] ?? '',
                                                    'D' => $q['option_d'] ?? ''
                                                ];
                                                ?>
                                                <?php foreach ($optionFields as $opt => $value): ?>
                                                    <div class="col-3">
                                                        <div class="form-group mb-0">
                                                            <label class="d-block text-center mb-1">
                                                                <span class="badge text-bg-secondary"><?= $opt ?></span>
                                                            </label>
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   name="questions[<?= $index ?>][option_<?= strtolower($opt) ?>]" 
                                                                   value="<?= esc($value) ?>"
                                                                   placeholder="Option <?= $opt ?>">
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- True/False Options -->
                                    <div class="q-block q-tf" style="<?= $q['question_type'] !== 'tf' ? 'display:none;' : '' ?>">
                                        <label class="fw-bold d-block mb-2">Select Correct Answer</label>
                                        <div class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                            <label class="btn btn-outline-success <?= ($q['answer_text'] ?? '') === 'True' ? 'active' : '' ?>">
                                                <input type="radio" 
                                                       name="questions[<?= $index ?>][answer_text]" 
                                                       value="True" 
                                                       autocomplete="off"
                                                       <?= ($q['answer_text'] ?? '') === 'True' ? 'checked' : '' ?>> 
                                                <i class="fas fa-check"></i> True
                                            </label>
                                            <label class="btn btn-outline-danger <?= ($q['answer_text'] ?? '') === 'False' ? 'active' : '' ?>">
                                                <input type="radio" 
                                                       name="questions[<?= $index ?>][answer_text]" 
                                                       value="False" 
                                                       autocomplete="off"
                                                       <?= ($q['answer_text'] ?? '') === 'False' ? 'checked' : '' ?>> 
                                                <i class="fas fa-times"></i> False
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Fill/Short Answer -->
                                    <div class="q-block q-fill q-short" style="<?= !in_array($q['question_type'], ['fill', 'short']) ? 'display:none;' : '' ?>">
                                        <div class="form-group">
                                            <label class="fw-bold">Expected Answer</label>
                                            <input type="text" 
                                                   name="questions[<?= $index ?>][answer_text]" 
                                                   class="form-control" 
                                                   value="<?= esc($q['answer_text'] ?? '') ?>"
                                                   placeholder="Enter expected answer...">
                                        </div>
                                    </div>
                                    
                                    <!-- Matching Pairs -->
                                    <div class="q-block q-match" style="<?= $q['question_type'] !== 'match' ? 'display:none;' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="fw-bold">Matching Pairs</label>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       id="q<?= $index ?>_drag"
                                                       name="questions[<?= $index ?>][is_drag]" 
                                                       value="1" 
                                                       <?= ($q['is_drag'] ?? 0) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="q<?= $index ?>_drag">
                                                    Enable Drag & Drop
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="match-pairs">
                                            <?php if (!empty($matchPairs)): ?>
                                                <?php foreach ($matchPairs as $pairIdx => $pair): ?>
                                                    <div class="row mb-2 align-items-center">
                                                        <div class="col-5">
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="questions[<?= $index ?>][match_pairs][<?= $pairIdx ?>][left]" 
                                                                   placeholder="Left item" 
                                                                   value="<?= esc($pair['left'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-1 text-center">
                                                            <i class="fas fa-arrows-alt-h text-muted"></i>
                                                        </div>
                                                        <div class="col-5">
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   name="questions[<?= $index ?>][match_pairs][<?= $pairIdx ?>][right]" 
                                                                   placeholder="Right item" 
                                                                   value="<?= esc($pair['right'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-1">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="row mb-2 align-items-center">
                                                    <div class="col-5">
                                                        <input type="text" 
                                                               class="form-control" 
                                                               name="questions[<?= $index ?>][match_pairs][0][left]" 
                                                               placeholder="Left item">
                                                    </div>
                                                    <div class="col-1 text-center">
                                                        <i class="fas fa-arrows-alt-h text-muted"></i>
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="text" 
                                                               class="form-control" 
                                                               name="questions[<?= $index ?>][match_pairs][0][right]" 
                                                               placeholder="Right item">
                                                    </div>
                                                    <div class="col-1">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-add-pair mt-2">
                                            <i class="fas fa-plus"></i> Add Pair
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Question Button -->
                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-question">
                                            <i class="fas fa-trash"></i> Delete Question
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Save All Questions
                </button>
            </div>
        </form>
    </div>
</section>

<style>
.question-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.card-header.bg-light {
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6;
}
.btn-check:checked + .btn-outline-success {
    background-color: #28a745;
    color: white;
}
.btn-check:checked + .btn-outline-warning {
    background-color: #ffc107;
    color: #212529;
}
.btn-check:checked + .btn-outline-danger {
    background-color: #dc3545;
    color: white;
}
.btn-check:checked + .btn-outline-info {
    background-color: #17a2b8;
    color: white;
}
.image-preview-container {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    background-color: #f8f9fa;
}
.q-image-preview {
    max-width: 100%;
    height: auto;
    margin: 0 auto;
    display: block;
}
.editor-tools {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    background-color: #fff;
}
.crop-container {
    width: 100%;
    height: 400px;
    overflow: hidden;
}
.option-input-row {
    margin-top: -10px;
}
</style>

<script>
$(document).ready(function() {
    let questionCount = <?= count($questions) ?>;
    let cropper = null;
    let currentCropImage = null;
    let currentCropIndex = null;
    
    // Add new question
    $('#btnAddQuestion').click(function() {
        const index = questionCount++;
        const html = `
            <div class="card mb-3 question-card" data-qidx="${index}">
                <!-- Header Row -->
                <div class="card-header bg-light py-2">
                    <div class="row align-items-center">
                        <!-- Question Number -->
                        <div class="col-md-2">
                            <h5 class="mb-0">
                                <span class="badge text-bg-primary">Q${index + 1}</span>
                            </h5>
                        </div>
                        
                        <!-- Question Type -->
                        <div class="col-md-3">
                            <select name="questions[${index}][question_type]" class="form-control form-control-sm q-type">
                                <option value="mcq">MCQ Single</option>
                                <option value="mcq_multi">MCQ Multiple</option>
                                <option value="tf">True/False</option>
                                <option value="fill">Fill in Blank</option>
                                <option value="short">Short Answer</option>
                                <option value="match">Matching</option>
                            </select>
                        </div>
                        
                        <!-- Difficulty Level -->
                        <div class="col-md-2">
                            <div class="difficulty-buttons">
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <input type="radio" 
                                           class="btn-check" 
                                           name="questions[${index}][difficulty]" 
                                           id="q${index}_easy" 
                                           value="easy" 
                                           checked>
                                    <label class="btn btn-outline-success" for="q${index}_easy">
                                        <i class="fas fa-smile"></i> Easy
                                    </label>
                                    
                                    <input type="radio" 
                                           class="btn-check" 
                                           name="questions[${index}][difficulty]" 
                                           id="q${index}_normal" 
                                           value="normal">
                                    <label class="btn btn-outline-warning" for="q${index}_normal">
                                        <i class="fas fa-meh"></i> Normal
                                    </label>
                                    
                                    <input type="radio" 
                                           class="btn-check" 
                                           name="questions[${index}][difficulty]" 
                                           id="q${index}_hard" 
                                           value="hard">
                                    <label class="btn btn-outline-danger" for="q${index}_hard">
                                        <i class="fas fa-frown"></i> Hard
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question Mode Toggle and Image Upload -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <!-- Question Mode Toggle -->
                                <div class="btn-group btn-group-sm me-2" role="group">
                                    <input type="radio" 
                                           class="btn-check q-media-toggle" 
                                           name="questions[${index}][question_media]" 
                                           id="q${index}_text" 
                                           value="text" 
                                           data-qidx="${index}"
                                           checked>
                                    <label class="btn btn-outline-info" for="q${index}_text">
                                        <i class="fas fa-font"></i> Text
                                    </label>
                                    
                                    <input type="radio" 
                                           class="btn-check q-media-toggle" 
                                           name="questions[${index}][question_media]" 
                                           id="q${index}_image" 
                                           value="image" 
                                           data-qidx="${index}">
                                    <label class="btn btn-outline-info" for="q${index}_image">
                                        <i class="fas fa-image"></i> Image
                                    </label>
                                </div>
                                
                                <!-- Image Upload Button (only shown in image mode) -->
                                <div class="image-upload-section" style="display:none;">
                                    <input type="file" 
                                           class="d-none q-image-input" 
                                           name="questions[${index}][question_image]" 
                                           accept="image/*" 
                                           data-qidx="${index}">
                                    <button type="button" 
                                            class="btn btn-sm btn-secondary btn-browse-image" 
                                            data-qidx="${index}">
                                        <i class="fas fa-folder-open"></i> Browse
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Move Buttons -->
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-light btn-sm btn-move-up" title="Move Up">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-light btn-sm btn-move-down" title="Move Down">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Hidden ID field -->
                    <input type="hidden" name="questions[${index}][id]" value="0">
                    <input type="hidden" name="questions[${index}][image_data]" class="image-data" value="">
                    
                    <!-- Question Content Area -->
                    <div class="question-content-area mb-3">
                        <!-- Text Mode Content -->
                        <div class="q-text-wrap">
                            <label class="fw-bold">Question Text</label>
                            <textarea name="questions[${index}][question]" 
                                      class="form-control q-text" 
                                      rows="3" 
                                      placeholder="Enter question text here..."
                                      required></textarea>
                        </div>
                        
                        <!-- Image Mode Content -->
                        <div class="q-image-wrap d-none">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Image Preview and Editor -->
                                    <div class="image-editor-container">
                                        <div class="image-preview-container mb-3">
                                            <div class="text-center py-4 border rounded bg-light">
                                                <i class="fas fa-image fa-3x text-muted mb-2"></i><br>
                                                <span class="text-muted">No image selected</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Editor Tools -->
                                        <div class="editor-tools d-none">
                                            <div class="btn-group btn-group-sm mb-2">
                                                <button type="button" class="btn btn-outline-primary btn-rotate-left" data-deg="-90">
                                                    <i class="fas fa-undo"></i> Rotate Left
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-rotate-right" data-deg="90">
                                                    <i class="fas fa-redo"></i> Rotate Right
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-flip-horizontal">
                                                    <i class="fas fa-arrows-alt-h"></i> Flip H
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-flip-vertical">
                                                    <i class="fas fa-arrows-alt-v"></i> Flip V
                                                </button>
                                                <button type="button" class="btn btn-outline-success btn-crop">
                                                    <i class="fas fa-crop"></i> Crop
                                                </button>
                                                <button type="button" class="btn btn-outline-info btn-reset">
                                                    <i class="fas fa-sync"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Crop Modal -->
                                        <div class="modal fade crop-modal" id="cropModal_${index}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Crop Image</h5>
                                                        <button type="button" class="close" data-bs-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="crop-container">
                                                            <img src="" class="img-fluid" id="cropImage_${index}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-primary btn-crop-apply">Apply Crop</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <!-- Image Description -->
                                    <div class="form-group">
                                        <label>Image Description (Optional)</label>
                                        <textarea name="questions[${index}][question_image_alt]" 
                                                  class="form-control" 
                                                  rows="3"
                                                  placeholder="Describe the image for accessibility"></textarea>
                                        <small class="text-muted">This helps screen readers describe the image.</small>
                                    </div>
                                    
                                    <!-- Image Actions -->
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Image Actions</h6>
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-image" data-qidx="${index}">
                                                    <i class="fas fa-edit"></i> Edit Image
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-image" data-qidx="${index}">
                                                    <i class="fas fa-trash"></i> Remove Image
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- MCQ Options (hidden by default) -->
                    <div class="q-block q-mcq q-mcq_multi d-none">
                        <div class="mb-3">
                            <label class="fw-bold d-block mb-2">Options</label>
                            
                            <!-- Correct Option Selector Row -->
                            <div class="row mb-2">
                                <div class="col-3 text-center">
                                    <small class="text-muted">Correct Option</small>
                                </div>
                                <div class="col-3 text-center">
                                    <small class="text-muted">Correct Option</small>
                                </div>
                                <div class="col-3 text-center">
                                    <small class="text-muted">Correct Option</small>
                                </div>
                                <div class="col-3 text-center">
                                    <small class="text-muted">Correct Option</small>
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                    <div class="col-3">
                                        <div class="text-center mb-1">
                                            <div class="form-check form-check d-inline-block mcq-single">
                                                <input type="radio" 
                                                       id="q${index}_correct_<?= strtolower($opt) ?>" 
                                                       name="questions[${index}][correct_option]" 
                                                       value="<?= $opt ?>" 
                                                       class="form-check-input">
                                                <label class="form-check-label" for="q${index}_correct_<?= strtolower($opt) ?>"></label>
                                            </div>
                                            <div class="form-check form-check d-inline-block mcq-multi d-none">
                                                <input type="checkbox" 
                                                       id="q${index}_multi_<?= strtolower($opt) ?>" 
                                                       name="questions[${index}][correct_multi][]" 
                                                       value="<?= $opt ?>" 
                                                       class="form-check-input">
                                                <label class="form-check-label" for="q${index}_multi_<?= strtolower($opt) ?>"></label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Option Inputs Row -->
                            <div class="row">
                                <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                    <div class="col-3">
                                        <div class="form-group mb-0">
                                            <label class="d-block text-center mb-1">
                                                <span class="badge text-bg-secondary"><?= $opt ?></span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   name="questions[${index}][option_<?= strtolower($opt) ?>]" 
                                                   placeholder="Option <?= $opt ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- True/False -->
                    <div class="q-block q-tf d-none">
                        <label class="fw-bold d-block mb-2">Select Correct Answer</label>
                        <div class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                            <label class="btn btn-outline-success active">
                                <input type="radio" 
                                       name="questions[${index}][answer_text]" 
                                       value="True" 
                                       autocomplete="off" 
                                       checked> 
                                <i class="fas fa-check"></i> True
                            </label>
                            <label class="btn btn-outline-danger">
                                <input type="radio" 
                                       name="questions[${index}][answer_text]" 
                                       value="False" 
                                       autocomplete="off"> 
                                <i class="fas fa-times"></i> False
                            </label>
                        </div>
                    </div>
                    
                    <!-- Fill/Short -->
                    <div class="q-block q-fill q-short d-none">
                        <div class="form-group">
                            <label class="fw-bold">Expected Answer</label>
                            <input type="text" 
                                   name="questions[${index}][answer_text]" 
                                   class="form-control" 
                                   placeholder="Enter expected answer...">
                        </div>
                    </div>
                    
                    <!-- Match -->
                    <div class="q-block q-match d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="fw-bold">Matching Pairs</label>
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="q${index}_drag"
                                       name="questions[${index}][is_drag]" 
                                       value="1">
                                <label class="form-check-label" for="q${index}_drag">
                                    Enable Drag & Drop
                                </label>
                            </div>
                        </div>
                        
                        <div class="match-pairs">
                            <div class="row mb-2 align-items-center">
                                <div class="col-5">
                                    <input type="text" 
                                           class="form-control" 
                                           name="questions[${index}][match_pairs][0][left]" 
                                           placeholder="Left item">
                                </div>
                                <div class="col-1 text-center">
                                    <i class="fas fa-arrows-alt-h text-muted"></i>
                                </div>
                                <div class="col-5">
                                    <input type="text" 
                                           class="form-control" 
                                           name="questions[${index}][match_pairs][0][right]" 
                                           placeholder="Right item">
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-add-pair mt-2">
                            <i class="fas fa-plus"></i> Add Pair
                        </button>
                    </div>
                    
                    <!-- Delete Question Button -->
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-question">
                            <i class="fas fa-trash"></i> Delete Question
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#questionList').append(html);
        renumberQuestions();
    });
    
    // Question media toggle (Text/Image)
    $(document).on('change', '.q-media-toggle', function() {
        const index = $(this).data('qidx');
        const card = $(`[data-qidx="${index}"]`);
        const isImageMode = $(this).val() === 'image';
        
        // Toggle text/image sections
        card.find('.q-text-wrap').toggle(!isImageMode);
        card.find('.q-image-wrap').toggle(isImageMode);
        
        // Toggle browse button
        card.find('.image-upload-section').toggle(isImageMode);
        
        // Handle required attribute
        if (isImageMode) {
            card.find('.q-text').removeAttr('required');
        } else {
            card.find('.q-text').attr('required', 'required');
        }
    });
    
    // Browse image button
    $(document).on('click', '.btn-browse-image', function() {
        const index = $(this).data('qidx');
        $(`input[data-qidx="${index}"].q-image-input`).click();
    });
    
    // Image file selected
    $(document).on('change', '.q-image-input', function(e) {
        const index = $(this).data('qidx');
        const card = $(`[data-qidx="${index}"]`);
        const file = e.target.files[0];
        
        if (file) {
            if (!file.type.match('image.*')) {
                alert('Please select an image file');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                const imageUrl = event.target.result;
                
                // Update preview
                const previewContainer = card.find('.image-preview-container');
                previewContainer.html(`<img src="${imageUrl}" class="img-fluid q-image-preview" id="preview_${index}" style="max-height: 300px; display: block;">`);
                
                // Show editor tools
                card.find('.editor-tools').removeClass('d-none');
                
                // Store image data
                card.find('.image-data').val(imageUrl);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Edit image button
    $(document).on('click', '.btn-edit-image', function() {
        const index = $(this).data('qidx');
        const card = $(`[data-qidx="${index}"]`);
        const preview = card.find('.q-image-preview')[0];
        
        if (preview && preview.src) {
            // Show editor tools if hidden
            card.find('.editor-tools').removeClass('d-none');
        }
    });
    
    // Remove image
    $(document).on('click', '.btn-remove-image', function() {
        const index = $(this).data('qidx');
        const card = $(`[data-qidx="${index}"]`);
        
        // Reset preview
        const previewContainer = card.find('.image-preview-container');
        previewContainer.html(`
            <div class="text-center py-4 border rounded bg-light">
                <i class="fas fa-image fa-3x text-muted mb-2"></i><br>
                <span class="text-muted">No image selected</span>
            </div>
        `);
        
        // Hide editor tools
        card.find('.editor-tools').addClass('d-none');
        
        // Clear file input
        card.find('.q-image-input').val('');
        
        // Clear image data
        card.find('.image-data').val('');
    });
    
    // Image rotation
    $(document).on('click', '.btn-rotate-left, .btn-rotate-right', function() {
        const card = $(this).closest('.question-card');
        const index = card.data('qidx');
        const preview = card.find(`#preview_${index}`)[0];
        const degrees = parseInt($(this).data('deg'));
        
        if (preview) {
            // Get current transform
            const style = window.getComputedStyle(preview);
            const matrix = new WebKitCSSMatrix(style.transform);
            const currentAngle = Math.atan2(matrix.b, matrix.a) * (180 / Math.PI);
            
            // Apply new rotation
            const newAngle = currentAngle + degrees;
            preview.style.transform = `rotate(${newAngle}deg)`;
            
            // Update image data
            updateImageData(card, index);
        }
    });
    
    // Image flip
    $(document).on('click', '.btn-flip-horizontal', function() {
        const card = $(this).closest('.question-card');
        const index = card.data('qidx');
        const preview = card.find(`#preview_${index}`)[0];
        
        if (preview) {
            const style = window.getComputedStyle(preview);
            const transform = style.transform;
            const scaleX = transform.includes('scaleX(-1)') ? 1 : -1;
            
            preview.style.transform = transform.replace(/scaleX\([^)]*\)/, '') + ` scaleX(${scaleX})`;
            updateImageData(card, index);
        }
    });
    
    $(document).on('click', '.btn-flip-vertical', function() {
        const card = $(this).closest('.question-card');
        const index = card.data('qidx');
        const preview = card.find(`#preview_${index}`)[0];
        
        if (preview) {
            const style = window.getComputedStyle(preview);
            const transform = style.transform;
            const scaleY = transform.includes('scaleY(-1)') ? 1 : -1;
            
            preview.style.transform = transform.replace(/scaleY\([^)]*\)/, '') + ` scaleY(${scaleY})`;
            updateImageData(card, index);
        }
    });
    
    // Crop image
    $(document).on('click', '.btn-crop', function() {
        const card = $(this).closest('.question-card');
        const index = card.data('qidx');
        const preview = card.find(`#preview_${index}`)[0];
        
        if (preview && preview.src) {
            currentCropIndex = index;
            currentCropImage = preview;
            
            // Show crop modal
            $(`#cropModal_${index}`).modal('show');
            
            // Initialize cropper
            const cropImage = $(`#cropImage_${index}`)[0];
            cropImage.src = preview.src;
            
            setTimeout(() => {
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(cropImage, {
                    aspectRatio: NaN,
                    viewMode: 1,
                    autoCropArea: 0.8,
                    responsive: true,
                    restore: true,
                    checkCrossOrigin: false
                });
            }, 100);
        }
    });
    
    // Apply crop
    $(document).on('click', '.btn-crop-apply', function() {
        if (cropper && currentCropImage && currentCropIndex !== null) {
            const card = $(`[data-qidx="${currentCropIndex}"]`);
            
            // Get cropped canvas
            const canvas = cropper.getCroppedCanvas({
                width: 800,
                height: 600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });
            
            // Update preview
            currentCropImage.src = canvas.toDataURL('image/jpeg', 0.9);
            
            // Update image data
            card.find('.image-data').val(canvas.toDataURL('image/jpeg', 0.9));
            
            // Close modal
            $(`#cropModal_${currentCropIndex}`).modal('hide');
            
            // Destroy cropper
            cropper.destroy();
            cropper = null;
        }
    });
    
    // Reset image
    $(document).on('click', '.btn-reset', function() {
        const card = $(this).closest('.question-card');
        const index = card.data('qidx');
        const preview = card.find(`#preview_${index}`)[0];
        
        if (preview) {
            preview.style.transform = '';
            updateImageData(card, index);
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
        if (type === 'mcq' || type === 'mcq_multi') {
            card.find('.q-mcq, .q-mcq_multi').removeClass('d-none');
            
            // Toggle between single and multi selection
            if (type === 'mcq_multi') {
                card.find('.mcq-single').addClass('d-none');
                card.find('.mcq-multi').removeClass('d-none');
                card.find('input[type="radio"]').prop('checked', false);
            } else {
                card.find('.mcq-single').removeClass('d-none');
                card.find('.mcq-multi').addClass('d-none');
                card.find('input[type="checkbox"]').prop('checked', false);
            }
        } else if (type === 'tf') {
            card.find('.q-tf').removeClass('d-none');
        } else if (type === 'fill' || type === 'short') {
            card.find('.q-fill, .q-short').removeClass('d-none');
        } else if (type === 'match') {
            card.find('.q-match').removeClass('d-none');
            
            // Ensure at least one pair exists
            if (card.find('.match-pairs .row').length === 0) {
                addMatchPair(card, index);
            }
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
        const pairsContainer = $(this).closest('.match-pairs');
        if (pairsContainer.find('.row').length > 1) {
            $(this).closest('.row').remove();
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
    $(document).on('click', '.btn-remove-question', function() {
        const card = $(this).closest('.question-card');
        if ($('#questionList .question-card').length > 1) {
            if (confirm('Are you sure you want to delete this question?')) {
                card.remove();
                renumberQuestions();
            }
        } else {
            alert('At least one question is required.');
        }
    });
    
    // Helper function to update image data
    function updateImageData(card, index) {
        const preview = card.find(`#preview_${index}`)[0];
        if (preview && preview.src) {
            // Create canvas to capture transformed image
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Set canvas dimensions
            canvas.width = preview.naturalWidth || preview.width;
            canvas.height = preview.naturalHeight || preview.height;
            
            // Apply transformations to canvas
            const style = window.getComputedStyle(preview);
            const transform = style.transform;
            
            ctx.save();
            
            // Parse and apply transform
            if (transform && transform !== 'none') {
                const matrix = new WebKitCSSMatrix(transform);
                ctx.transform(matrix.a, matrix.b, matrix.c, matrix.d, matrix.e, matrix.f);
            }
            
            // Draw image
            ctx.drawImage(preview, 0, 0);
            ctx.restore();
            
            // Update image data
            card.find('.image-data').val(canvas.toDataURL('image/jpeg', 0.9));
        }
    }
    
    // Helper function to add match pair
    function addMatchPair(card, index) {
        const pairCount = card.find('.match-pairs .row').length;
        const html = `
            <div class="row mb-2 align-items-center">
                <div class="col-5">
                    <input type="text" 
                           class="form-control" 
                           name="questions[${index}][match_pairs][${pairCount}][left]" 
                           placeholder="Left item">
                </div>
                <div class="col-1 text-center">
                    <i class="fas fa-arrows-alt-h text-muted"></i>
                </div>
                <div class="col-5">
                    <input type="text" 
                           class="form-control" 
                           name="questions[${index}][match_pairs][${pairCount}][right]" 
                           placeholder="Right item">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pair">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        card.find('.match-pairs').append(html);
    }
    
    // Helper function to renumber questions
    function renumberQuestions() {
        $('#questionList .question-card').each(function(index) {
            $(this).data('qidx', index);
            
            // Update question number badge
            $(this).find('.text-bg-primary').text('Q' + (index + 1));
            
            // Update all IDs and names with new index
            $(this).find('[id], [name], [data-qidx]').each(function() {
                // Update IDs
                if ($(this).attr('id')) {
                    const oldId = $(this).attr('id');
                    const newId = oldId.replace(/q\d+_/, 'q' + index + '_');
                    $(this).attr('id', newId);
                    
                    // Update label for attribute
                    const label = $(this).next('label');
                    if (label.length && label.attr('for')) {
                        label.attr('for', newId);
                    }
                }
                
                // Update names
                if ($(this).attr('name')) {
                    const oldName = $(this).attr('name');
                    const newName = oldName.replace(/questions\[\d+\]/, 'questions[' + index + ']');
                    $(this).attr('name', newName);
                }
                
                // Update data-qidx
                if ($(this).attr('data-qidx')) {
                    $(this).attr('data-qidx', index);
                }
            });
        });
    }
});
</script>

<?= $this->endSection() ?>