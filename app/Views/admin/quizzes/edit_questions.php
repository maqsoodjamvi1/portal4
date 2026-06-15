<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Include necessary libraries -->
<link href="https://unpkg.com/cropperjs/dist/cropper.css" rel="stylesheet">
<script src="https://unpkg.com/cropperjs"></script>

<?= view('components/page_header', [
    'title' => 'Edit Quiz Questions',
    'icon' => 'fas fa-edit',
    'subtitle' => $quiz->title ?? 'Untitled Quiz',
    'actionsHtml' => '<div class="text-sm-right">'
        . '<a href="' . esc(site_url('admin/quizzes'), 'attr') . '" class="btn btn-secondary btn-sm">'
        . '<i class="fas fa-arrow-left"></i> Back to Quizzes</a></div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quizzes', 'url' => base_url('admin/quizzes')],
        ['label' => 'Edit Questions', 'active' => true],
    ],
]) ?>

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
    
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Filter Questions</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Difficulty Filter Dropdown -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Difficulty Level</label>
                        <select class="form-control" id="difficultyFilter">
                            <option value="all">All Difficulties</option>
                            <option value="easy">Easy</option>
                            <option value="normal">Normal</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
                
                <!-- Question Type Multi-Select -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Question Types</label>
                        <select class="form-control" id="typeFilter" multiple style="height: 100px;">
                            <option value="all" selected>All Types</option>
                            <option value="mcq">MCQ Single</option>
                            <option value="mcq_multi">MCQ Multiple</option>
                            <option value="tf">True/False</option>
                            <option value="fill">Fill in Blank</option>
                            <option value="short">Short Answer</option>
                            <option value="match">Matching</option>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple types</small>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-group w-100">
                        <button type="button" id="btnApplyFilter" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" id="btnResetFilter" class="btn btn-secondary w-100 mt-2">
                            <i class="fas fa-redo"></i> Reset Filter
                        </button>
                    </div>
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
                                    <div class="col-md-1">
                                        <h5 class="mb-0">
                                            <span class="badge text-bg-primary">Q<?= $index + 1 ?></span>
                                        </h5>
                                    </div>
                                    
                                    <!-- Question Type -->
                                    <div class="col-md-2">
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
                                    
                                    <!-- Difficulty Dropdown -->
                                    <div class="col-md-2">
                                        <select name="questions[<?= $index ?>][difficulty]" 
                                                class="form-control form-control-sm q-difficulty">
                                            <option value="easy" <?= ($q['difficulty'] ?? 'normal') === 'easy' ? 'selected' : '' ?>>Easy</option>
                                            <option value="normal" <?= ($q['difficulty'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                                            <option value="hard" <?= ($q['difficulty'] ?? 'normal') === 'hard' ? 'selected' : '' ?>>Hard</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Question Mode Toggle -->
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
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
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-light btn-sm btn-move-up" title="Move Up">
                                            <i class="fas fa-arrow-up"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm btn-move-down" title="Move Down">
                                            <i class="fas fa-arrow-down"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Save Button -->
                                    <div class="col-md-2 text-end">
                                        <button type="button" 
                                                class="btn btn-success btn-sm btn-save-question" 
                                                data-question-id="<?= $q['id'] ?>"
                                                data-index="<?= $index ?>"
                                                title="Save This Question">
                                            <i class="fas fa-save"></i> Save
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
                                
                                <!-- MCQ Single/Multiple Options - IMPROVED LAYOUT -->
                                <div class="q-block q-mcq q-mcq_multi" style="<?= !in_array($q['question_type'], ['mcq', 'mcq_multi']) ? 'display:none;' : '' ?>">
                                    <div class="mb-3">
                                        <label class="fw-bold d-block mb-2">Options</label>
                                        
                                        <!-- Option Inputs Row - COMPACT LAYOUT -->
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
                                                <div class="col-md-6 mb-2">
                                                    <div class="input-group">
                                                        <div class="input-group-text p-0" style="border: none; background: none;">
                                                                <?php if ($q['question_type'] === 'mcq'): ?>
                                                                    <div class="form-check form-check me-2">
                                                                        <input type="radio" 
                                                                               id="q<?= $index ?>_correct_<?= strtolower($opt) ?>" 
                                                                               name="questions[<?= $index ?>][correct_option]" 
                                                                               value="<?= $opt ?>" 
                                                                               class="form-check-input"
                                                                               <?= ($q['correct_option'] ?? '') === $opt ? 'checked' : '' ?>>
                                                                        <label class="form-check-label" for="q<?= $index ?>_correct_<?= strtolower($opt) ?>"></label>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="form-check form-check me-2">
                                                                        <input type="checkbox" 
                                                                               id="q<?= $index ?>_multi_<?= strtolower($opt) ?>" 
                                                                               name="questions[<?= $index ?>][correct_multi][]" 
                                                                               value="<?= $opt ?>" 
                                                                               class="form-check-input"
                                                                               <?= in_array($opt, $correctMulti) ? 'checked' : '' ?>>
                                                                        <label class="form-check-label" for="q<?= $index ?>_multi_<?= strtolower($opt) ?>"></label>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <span class="badge text-bg-secondary align-self-center ms-1"><?= $opt ?></span>
                                                            </div>
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
                                
                                <!-- Action Buttons at Bottom -->
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div>
                                        <small class="text-muted">Question ID: <?= $q['id'] ?></small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-question">
                                            <i class="fas fa-trash"></i> Delete Question
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.question-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.card-header.bg-light {
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6;
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
.btn-save-question {
    transition: all 0.3s ease;
}
.btn-save-question:hover {
    transform: scale(1.05);
}
.question-card:not(:visible) {
    display: none !important;
}
#typeFilter option:checked {
    background-color: #007bff;
    color: white;
}
/* Toast styles */
.toast {
    min-width: 250px;
}
/* Compact MCQ layout */
.input-group-text .input-group-text {
    background-color: #f8f9fa;
    border-end: 0;
}
.form-check {
    min-height: auto;
}

.btn-remove-question {
    transition: all 0.3s ease;
}
.btn-remove-question:hover {
    background-color: #dc3545 !important;
    color: white !important;
    transform: scale(1.05);
}
</style>


<div class="modal fade" id="deleteQuestionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Question</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteQuestionText">Are you sure you want to delete this question?</p>
                <div class="alert alert-warning" id="usedElsewhereWarning" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Note:</strong> This question is also used in other quizzes. It will only be removed from this quiz.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
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
                        <div class="col-md-1">
                            <h5 class="mb-0">
                                <span class="badge text-bg-primary">Q${index + 1}</span>
                            </h5>
                        </div>
                        
                        <!-- Question Type -->
                        <div class="col-md-2">
                            <select name="questions[${index}][question_type]" class="form-control form-control-sm q-type">
                                <option value="mcq">MCQ Single</option>
                                <option value="mcq_multi">MCQ Multiple</option>
                                <option value="tf">True/False</option>
                                <option value="fill">Fill in Blank</option>
                                <option value="short">Short Answer</option>
                                <option value="match">Matching</option>
                            </select>
                        </div>
                        
                        <!-- Difficulty Dropdown -->
                        <div class="col-md-2">
                            <select name="questions[${index}][difficulty]" class="form-control form-control-sm q-difficulty">
                                <option value="easy">Easy</option>
                                <option value="normal" selected>Normal</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        
                        <!-- Question Mode Toggle -->
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
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
                                
                                <!-- Image Upload Button -->
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
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-light btn-sm btn-move-up" title="Move Up">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-light btn-sm btn-move-down" title="Move Down">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>
                        
                        <!-- Save Button -->
                        <div class="col-md-2 text-end">
                            <button type="button" 
                                    class="btn btn-success btn-sm btn-save-question" 
                                    data-question-id="0"
                                    data-index="${index}"
                                    title="Save This Question">
                                <i class="fas fa-save"></i> Save
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
                                    <div class="image-editor-container">
                                        <div class="image-preview-container mb-3">
                                            <div class="text-center py-4 border rounded bg-light">
                                                <i class="fas fa-image fa-3x text-muted mb-2"></i><br>
                                                <span class="text-muted">No image selected</span>
                                            </div>
                                        </div>
                                        
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
                                    <div class="form-group">
                                        <label>Image Description (Optional)</label>
                                        <textarea name="questions[${index}][question_image_alt]" 
                                                  class="form-control" 
                                                  rows="3"
                                                  placeholder="Describe the image for accessibility"></textarea>
                                        <small class="text-muted">This helps screen readers describe the image.</small>
                                    </div>
                                    
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
                    
                    <!-- MCQ Options -->
                    <div class="q-block q-mcq q-mcq_multi d-none">
                        <div class="mb-3">
                            <label class="fw-bold d-block mb-2">Options</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-text p-0" style="border: none; background: none;">
                                                <div class="form-check form-check me-2 mcq-single">
                                                    <input type="radio" 
                                                           id="q${index}_correct_a" 
                                                           name="questions[${index}][correct_option]" 
                                                           value="A" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_correct_a"></label>
                                                </div>
                                                <div class="form-check form-check me-2 mcq-multi d-none">
                                                    <input type="checkbox" 
                                                           id="q${index}_multi_a" 
                                                           name="questions[${index}][correct_multi][]" 
                                                           value="A" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_multi_a"></label>
                                                </div>
                                                <span class="badge text-bg-secondary align-self-center ms-1">A</span>
                                            </div>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="questions[${index}][option_a]" 
                                               placeholder="Option A">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-text p-0" style="border: none; background: none;">
                                                <div class="form-check form-check me-2 mcq-single">
                                                    <input type="radio" 
                                                           id="q${index}_correct_b" 
                                                           name="questions[${index}][correct_option]" 
                                                           value="B" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_correct_b"></label>
                                                </div>
                                                <div class="form-check form-check me-2 mcq-multi d-none">
                                                    <input type="checkbox" 
                                                           id="q${index}_multi_b" 
                                                           name="questions[${index}][correct_multi][]" 
                                                           value="B" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_multi_b"></label>
                                                </div>
                                                <span class="badge text-bg-secondary align-self-center ms-1">B</span>
                                            </div>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="questions[${index}][option_b]" 
                                               placeholder="Option B">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-text p-0" style="border: none; background: none;">
                                                <div class="form-check form-check me-2 mcq-single">
                                                    <input type="radio" 
                                                           id="q${index}_correct_c" 
                                                           name="questions[${index}][correct_option]" 
                                                           value="C" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_correct_c"></label>
                                                </div>
                                                <div class="form-check form-check me-2 mcq-multi d-none">
                                                    <input type="checkbox" 
                                                           id="q${index}_multi_c" 
                                                           name="questions[${index}][correct_multi][]" 
                                                           value="C" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_multi_c"></label>
                                                </div>
                                                <span class="badge text-bg-secondary align-self-center ms-1">C</span>
                                            </div>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="questions[${index}][option_c]" 
                                               placeholder="Option C">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-text p-0" style="border: none; background: none;">
                                                <div class="form-check form-check me-2 mcq-single">
                                                    <input type="radio" 
                                                           id="q${index}_correct_d" 
                                                           name="questions[${index}][correct_option]" 
                                                           value="D" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_correct_d"></label>
                                                </div>
                                                <div class="form-check form-check me-2 mcq-multi d-none">
                                                    <input type="checkbox" 
                                                           id="q${index}_multi_d" 
                                                           name="questions[${index}][correct_multi][]" 
                                                           value="D" 
                                                           class="form-check-input">
                                                    <label class="form-check-label" for="q${index}_multi_d"></label>
                                                </div>
                                                <span class="badge text-bg-secondary align-self-center ms-1">D</span>
                                            </div>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="questions[${index}][option_d]" 
                                               placeholder="Option D">
                                    </div>
                                </div>
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
                    
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div>
                            <small class="text-muted">New Question</small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-question">
                                <i class="fas fa-trash"></i> Delete Question
                            </button>
                        </div>
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
        
        card.find('.q-text-wrap').toggle(!isImageMode);
        card.find('.q-image-wrap').toggle(isImageMode);
        card.find('.image-upload-section').toggle(isImageMode);
        
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
                const previewContainer = card.find('.image-preview-container');
                previewContainer.html(`<img src="${imageUrl}" class="img-fluid q-image-preview" id="preview_${index}" style="max-height: 300px; display: block;">`);
                card.find('.editor-tools').removeClass('d-none');
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
            card.find('.editor-tools').removeClass('d-none');
        }
    });
    
    // Remove image
    $(document).on('click', '.btn-remove-image', function() {
        const index = $(this).data('qidx');
        const card = $(`[data-qidx="${index}"]`);
        
        const previewContainer = card.find('.image-preview-container');
        previewContainer.html(`
            <div class="text-center py-4 border rounded bg-light">
                <i class="fas fa-image fa-3x text-muted mb-2"></i><br>
                <span class="text-muted">No image selected</span>
            </div>
        `);
        
        card.find('.editor-tools').addClass('d-none');
        card.find('.q-image-input').val('');
        card.find('.image-data').val('');
    });
    
    // Image rotation
    $(document).on('click', '.btn-rotate-left, .btn-rotate-right', function() {
        const card = $(this).closest('.question-card');
        const index = card.data('qidx');
        const preview = card.find(`#preview_${index}`)[0];
        const degrees = parseInt($(this).data('deg'));
        
        if (preview) {
            const style = window.getComputedStyle(preview);
            const matrix = new WebKitCSSMatrix(style.transform);
            const currentAngle = Math.atan2(matrix.b, matrix.a) * (180 / Math.PI);
            const newAngle = currentAngle + degrees;
            preview.style.transform = `rotate(${newAngle}deg)`;
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
            $(`#cropModal_${index}`).modal('show');
            
            const cropImage = $(`#cropImage_${index}`)[0];
            cropImage.src = preview.src;
            
            setTimeout(() => {
                if (cropper) cropper.destroy();
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
            const canvas = cropper.getCroppedCanvas({
                width: 800,
                height: 600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });
            
            currentCropImage.src = canvas.toDataURL('image/jpeg', 0.9);
            card.find('.image-data').val(canvas.toDataURL('image/jpeg', 0.9));
            $(`#cropModal_${currentCropIndex}`).modal('hide');
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
        
        card.find('.q-block').addClass('d-none');
        
        if (type === 'mcq' || type === 'mcq_multi') {
            card.find('.q-mcq, .q-mcq_multi').removeClass('d-none');
            
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
   // Replace the existing "Remove question" click handler with this:
let deleteQuestionData = null;

$(document).on('click', '.btn-remove-question', function() {
    const button = $(this);
    const card = button.closest('.question-card');
    const questionId = card.find('input[name$="[id]"]').val();
    const index = card.data('qidx');
    const questionText = card.find('.q-text').val() || 'Question ' + (index + 1);
    
    if (!questionId || questionId === '0') {
        // For unsaved questions
        if (confirm('This question has not been saved yet. Remove from list?')) {
            if ($('#questionList .question-card').length > 1) {
                card.remove();
                renumberQuestions();
                filterQuestions();
            } else {
                alert('At least one question is required.');
            }
        }
        return;
    }
    
    // Store data for deletion
    deleteQuestionData = {
        button: button,
        card: card,
        questionId: questionId,
        index: index,
        questionText: questionText.substring(0, 100) + (questionText.length > 100 ? '...' : '')
    };
    
    // Show confirmation modal
    $('#deleteQuestionText').html(`Are you sure you want to delete the following question from the question bank?<br><br><strong>"${deleteQuestionData.questionText}"</strong><br><br>This action cannot be undone.`);
    $('#usedElsewhereWarning').hide();
    $('#deleteQuestionModal').modal('show');
});

// Handle modal confirm button
$('#confirmDeleteBtn').click(function() {
    if (!deleteQuestionData) return;
    
    const { button, card, questionId } = deleteQuestionData;
    const confirmBtn = $(this);
    
    // Show loading state
    const originalHtml = confirmBtn.html();
    confirmBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
    confirmBtn.prop('disabled', true);
    
    $.ajax({
        url: '<?= site_url("admin/quizzes/delete-question") ?>',
        type: 'POST',
        data: {
            quiz_id: <?= $quizId ?>,
            question_id: questionId,
            csrf_token: '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.success) {
                showToast('Question deleted successfully!', 'success');
                
                // Close modal
                $('#deleteQuestionModal').modal('hide');
                confirmBtn.html(originalHtml);
                confirmBtn.prop('disabled', false);
                
                // Remove question card
                if (response.remaining_questions > 0) {
                    card.slideUp(300, function() {
                        $(this).remove();
                        renumberQuestions();
                        filterQuestions();
                        
                        // Update questions count display
                        $('.card-body strong:contains("Questions")').closest('div').find('br').next().text(response.remaining_questions + ' question(s)');
                    });
                } else {
                    window.location.reload();
                }
            } else {
                showToast('Error: ' + response.message, 'error');
                confirmBtn.html(originalHtml);
                confirmBtn.prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            showToast('Failed to delete question. Please try again.', 'error');
            confirmBtn.html(originalHtml);
            confirmBtn.prop('disabled', false);
        }
    });
});

// Reset modal when closed
$('#deleteQuestionModal').on('hidden.bs.modal', function() {
    deleteQuestionData = null;
    $('#confirmDeleteBtn').html('<i class="fas fa-trash"></i> Delete').prop('disabled', false);
});

    // Filter questions
    function filterQuestions() {
        const difficulty = $('#difficultyFilter').val();
        const selectedTypes = $('#typeFilter').val();
        const showAllTypes = selectedTypes.includes('all');
        
        $('.question-card').each(function() {
            const card = $(this);
            const questionType = card.find('.q-type').val();
            const questionDifficulty = card.find('.q-difficulty').val();
            
            let showCard = true;
            
            if (difficulty !== 'all' && questionDifficulty !== difficulty) {
                showCard = false;
            }
            
            if (!showAllTypes && !selectedTypes.includes(questionType)) {
                showCard = false;
            }
            
            if (showCard) {
                card.show();
            } else {
                card.hide();
            }
        });
        
        renumberVisibleQuestions();
    }
    
    // Renumber visible questions
    function renumberVisibleQuestions() {
        let visibleCount = 0;
        $('.question-card:visible').each(function(index) {
            visibleCount++;
            $(this).find('.text-bg-primary').text('Q' + visibleCount);
        });
    }
    
    // Apply filter button
    $('#btnApplyFilter').click(function() {
        filterQuestions();
    });
    
    // Reset filter button
    $('#btnResetFilter').click(function() {
        $('#difficultyFilter').val('all');
        $('#typeFilter').val(['all']);
        $('.question-card').show();
        renumberVisibleQuestions();
    });
    
    // Individual question save
    $(document).on('click', '.btn-save-question', function() {
        const button = $(this);
        const card = button.closest('.question-card');
        const questionId = button.data('question-id');
        const index = button.data('index');
        
        // Prepare form data
        const formData = new FormData();
        formData.append('quiz_id', <?= $quizId ?>);
        formData.append('question_id', questionId);
        formData.append('csrf_token', '<?= csrf_hash() ?>');
        
        // Collect question data
        card.find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            
            if (name && name.includes(`[${index}]`)) {
                if ($(this).attr('type') === 'file') {
                    const file = this.files[0];
                    if (file) formData.append(name, file);
                } else if ($(this).attr('type') === 'checkbox' || $(this).attr('type') === 'radio') {
                    if ($(this).is(':checked')) formData.append(name, value);
                } else if ($(this).is('select[multiple]')) {
                    const values = $(this).val();
                    if (values) values.forEach(val => formData.append(name, val));
                } else {
                    formData.append(name, value);
                }
            }
        });
        
        // Show loading state
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        button.prop('disabled', true);
        
        // AJAX request
        $.ajax({
            url: '<?= site_url("admin/quizzes/update-single-question") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    button.html('<i class="fas fa-check"></i> Saved');
                    button.removeClass('btn-success').addClass('btn-info');
                    
                    if (questionId === 0 && response.question_id) {
                        button.data('question-id', response.question_id);
                        card.find('input[name$="[id]"]').val(response.question_id);
                        card.find('.text-muted').text('Question ID: ' + response.question_id);
                    }
                    
                    showToast('Question saved successfully!', 'success');
                    
                    setTimeout(() => {
                        button.html(originalHtml);
                        button.removeClass('btn-info').addClass('btn-success');
                        button.prop('disabled', false);
                    }, 2000);
                } else {
                    showToast('Error: ' + response.message, 'error');
                    button.html(originalHtml);
                    button.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                showToast('Failed to save question. Please try again.', 'error');
                button.html(originalHtml);
                button.prop('disabled', false);
            }
        });
    });
    
    // Toast notification
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
                <div class="toast-header">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="ms-2 mb-1 close" data-bs-dismiss="toast">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `);
        
        if (type === 'success') {
            toast.find('.toast-header').addClass('bg-success text-white');
        } else if (type === 'error') {
            toast.find('.toast-header').addClass('bg-danger text-white');
        }
        
        $('#toastContainer').append(toast);
        toast.toast('show');
        
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    // Helper function to update image data
    function updateImageData(card, index) {
        const preview = card.find(`#preview_${index}`)[0];
        if (preview && preview.src) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = preview.naturalWidth || preview.width;
            canvas.height = preview.naturalHeight || preview.height;
            
            const style = window.getComputedStyle(preview);
            const transform = style.transform;
            
            ctx.save();
            if (transform && transform !== 'none') {
                const matrix = new WebKitCSSMatrix(transform);
                ctx.transform(matrix.a, matrix.b, matrix.c, matrix.d, matrix.e, matrix.f);
            }
            ctx.drawImage(preview, 0, 0);
            ctx.restore();
            
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
            $(this).find('.text-bg-primary').text('Q' + (index + 1));
            
            $(this).find('[id], [name], [data-qidx]').each(function() {
                if ($(this).attr('id')) {
                    const oldId = $(this).attr('id');
                    const newId = oldId.replace(/q\d+_/, 'q' + index + '_');
                    $(this).attr('id', newId);
                    
                    const label = $(this).next('label');
                    if (label.length && label.attr('for')) {
                        label.attr('for', newId);
                    }
                }
                
                if ($(this).attr('name')) {
                    const oldName = $(this).attr('name');
                    const newName = oldName.replace(/questions\[\d+\]/, 'questions[' + index + ']');
                    $(this).attr('name', newName);
                }
                
                if ($(this).attr('data-qidx')) {
                    $(this).attr('data-qidx', index);
                }
            });
        });
    }
    
    // Initialize on page load
    $(document).ready(function() {
        if ($('#toastContainer').length === 0) {
            $('body').append('<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
        }
    });
});
</script>

<?= $this->endSection() ?>