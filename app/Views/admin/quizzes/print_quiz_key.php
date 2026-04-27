<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
/* Page layout for printing */
@page {
    size: A4;
    margin: 8mm !important;
}

body {
    margin: 8mm;
    font-family: 'Jameel Noori Nastaleeq', 'Jameel Urdu Nastaleeq', 'Nafees Web Naskh', 'Urdu Naskh Asiatype', sans-serif;
    line-height: 1 !important;
}

/* Main container */
.questions-container {
    width: 100%;
    line-height: 1 !important;
}

/* Topic grid container - each topic gets its own grid */
.topic-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-auto-rows: auto;
    grid-gap: 8px;
    width: 100%;
    margin-bottom: 15px;
}

/* Question card styling */
.question-card {
    page-break-inside: avoid;
    margin: 0 !important;
    padding: 4px !important;
    border: 1px solid #ddd !important;
    border-radius: 4px;
    line-height: 1.2 !important;
    min-height: auto !important;
    background: #fff;
}

/* Table-like borders for print */
@media print {
    .question-card {
        border: 1px solid #000 !important;
        background: white !important;
    }
}

/* Urdu text alignment */
.urdu-text {
    font-family: 'Jameel Noori Nastaleeq', 'Jameel Urdu Nastaleeq', 'Nafees Web Naskh', serif !important;
    font-size: 12pt !important;
    text-align: right !important;
    direction: rtl !important;
    line-height: 1.4 !important;
    unicode-bidi: embed !important;
    display: inline !important;
}

.english-text {
    font-family: Arial, sans-serif;
    font-size: 10pt !important;
    text-align: left;
    direction: ltr;
    line-height: 1.2 !important;
    display: inline !important;
}

/* Correct answer styling - SAME FONT SIZE as question */
.correct-answer-inline {
    display: none !important;
    font-size: inherit !important;
    font-weight: 600;
    background: none !important;
    border: none !important;
    padding: 0 !important;
    margin-left: 8px !important;
}

.correct-answer-inline.show-answer {
    display: inline !important;
}

.correct-answer-inline.urdu {
    font-family: 'Jameel Noori Nastaleeq', 'Jameel Urdu Nastaleeq', serif !important;
    direction: rtl !important;
    text-align: right !important;
    unicode-bidi: embed !important;
    font-size: 12pt !important;
    margin-right: 8px !important;
    margin-left: 0 !important;
}

.correct-answer-inline.english {
    font-family: Arial, sans-serif;
    direction: ltr;
    font-size: 10pt !important;
}

.answer-label {
    font-weight: bold;
    color: #166534;
    margin: 0 4px !important;
    font-size: inherit !important;
}

.urdu-topic {
    font-family: 'Jameel Noori Nastaleeq', 'Jameel Urdu Nastaleeq', serif !important;
    font-size: 16pt !important;
    text-align: center !important;
    direction: rtl !important;
    font-weight: bold !important;
    line-height: 1.2 !important;
    margin: 0 !important;
    padding: 0 !important;
}

.english-topic {
    font-family: Arial, sans-serif;
    font-size: 14pt !important;
    text-align: center !important;
    direction: ltr;
    font-weight: bold;
    display: block;
    width: 100%;
    line-height: 1.2 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Topic header styling */
.topic-header {
    background: #f0f9ff;
    border: 2px solid #3b82f6;
    padding: 8px 12px !important;
    margin: 15px 0 10px 0 !important;
    border-radius: 4px;
    page-break-after: avoid;
    text-align: center !important;
    width: 100%;
    break-inside: avoid;
    line-height: 1 !important;
}

.topic-header .d-flex {
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}

.topic-header:first-child {
    margin-top: 0 !important;
}

/* Main styles */
.solution-box {
    display: none;
    margin-top: 4px !important;
    padding: 4px 6px !important;
    font-size: 9pt !important;
    border-top: 1px dashed #ccc;
}

.solution-box.show-answer {
    display: block !important;
}

/* Question container */
.question-container {
    display: flex;
    align-items: flex-start;
    line-height: 1.2 !important;
    margin: 0 !important;
    padding: 0 !important;
    min-height: auto !important;
}

.question-container.urdu-layout {
    flex-direction: row-reverse;
    justify-content: flex-end;
    text-align: right;
}

.question-container.english-layout {
    flex-direction: row;
    justify-content: flex-start;
    text-align: left;
}

/* Question number styling */
.question-number {
    font-weight: bold;
    font-size: 10pt !important;
    min-width: 28px !important;
    text-align: center;
    line-height: 1.2 !important;
    padding: 0 !important;
    margin: 0 !important;
    vertical-align: top !important;
}

.question-container.urdu-layout .question-number {
    margin-left: 6px !important;
    margin-right: 0 !important;
}

.question-container.english-layout .question-number {
    margin-right: 6px !important;
    margin-left: 0 !important;
}

/* Question text container */
.question-text-container {
    flex: 1;
    line-height: 1.2 !important;
    margin: 0 !important;
    padding: 0 !important;
    vertical-align: top !important;
}

.question-text {
    display: inline !important;
    line-height: 1.2 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* True/False inline styling */
.tf-inline {
    display: inline-block;
    padding: 1px 8px !important;
    border-radius: 12px;
    font-weight: 600;
    margin-left: 8px !important;
    font-size: inherit !important;
    line-height: 1.2 !important;
    vertical-align: baseline !important;
}

.tf-true {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.tf-false {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Match question answer styling */
.match-answer-box {
    display: none;
    margin-top: 4px !important;
    padding: 4px 6px !important;
    font-size: 9pt !important;
    border-top: 1px dashed #ccc;
}

.match-answer-box.show-answer {
    display: block !important;
}

.match-pair {
    display: block;
    margin: 2px 0;
    line-height: 1.3;
}

.match-connector {
    margin: 0 5px;
    font-weight: bold;
}

/* PRINT SPECIFIC STYLES */
@media print {
    /* Hide unnecessary elements */
    #toggleSolutionWrap,
    .btn,
    .text-center {
        display: none !important;
    }

    body {
        margin: 6mm !important;
        font-size: 9pt !important;
    }
    
    .urdu-topic {
        font-size: 14pt !important;
    }
    
    .topic-header {
        padding: 6px 10px !important;
        margin: 12px 0 8px 0 !important;
        border: 2px solid #000 !important;
        background: white !important;
    }
    
    /* Show all correct answers when printing */
    .solution-box,
    .correct-answer-inline,
    .match-answer-box {
        display: inline !important;
        background: transparent !important;
        border: none !important;
        margin: 0 0 0 5px !important;
        padding: 0 !important;
    }
    
    .match-answer-box {
        display: block !important;
    }
    
    .correct-answer-inline.urdu {
        margin-right: 5px !important;
    }
    
    .urdu-text {
        font-size: 10pt !important;
    }
    
    .english-text {
        font-size: 9pt !important;
    }
    
    .correct-answer-inline.urdu {
        font-size: 10pt !important;
    }
    
    .correct-answer-inline.english {
        font-size: 9pt !important;
    }
    
    .question-number {
        font-size: 9pt !important;
    }
    
    .topic-grid {
        grid-gap: 6px !important;
    }
    
    .question-card {
        border: 1px solid #000 !important;
        background: white !important;
    }
}

/* Header styles */
.quiz-header {
    text-align: center;
    border: 2px solid #000;
    padding: 8px 12px !important;
    margin-bottom: 12px !important;
    page-break-inside: avoid;
    width: 100%;
    line-height: 1 !important;
}

.quiz-subject {
    font-size: 1em !important;
    font-weight: bold;
    margin-bottom: 3px !important;
    color: #1e40af;
}

.quiz-class {
    font-size: 1.3em !important;
    font-weight: bold;
    margin-bottom: 4px !important;
}

.quiz-topics {
    font-size: 1.5em !important;
    font-weight: bold;
    margin-bottom: 4px !important;
}

.quiz-meta {
    font-size: 0.9em !important;
    color: #666;
}
</style>

<!-- ===== QUIZ HEADER ===== -->
<div class="quiz-header">
    <div class="quiz-subject"><?= esc($quiz->subject_name ?? 'Urdu') ?></div>
    <div class="quiz-class"><?= esc($quiz->cls_sec_name ?? 'Grade 2 - A') ?></div>
    <div class="quiz-topics"><?= esc($quiz->title) ?></div>
    <div class="quiz-meta">
        Total Questions: <?= array_sum(array_map(fn($topic) => count($topic['questions']), $topics ?? [])) ?> | 
        Total Topics: <?= count($topics ?? []) ?> | 
        Date: <?= date('d/m/Y') ?>
    </div>
</div>

<!-- ===== SHOW SOLUTION CHECKBOX (Hidden when printing) ===== -->
<div id="toggleSolutionWrap" class="mb-3 p-2 bg-light rounded">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="toggleSolution" style="transform: scale(1.1);">
        <label class="form-check-label ms-2" for="toggleSolution" style="font-size: 0.95em;">
            <strong>Show Correct Answers</strong>
        </label>
    </div>
</div>

<div class="questions-container">
<?php 
$globalQNo = 1;
foreach ($topics as $topicId => $topicData): 
    $topicName = $topicData['topic_name'] ?? 'Uncategorized';
    $topicNameUrdu = $topicData['topic_name_urdu'] ?? '';
    $questions = $topicData['questions'] ?? [];
?>
<!-- ===== TOPIC HEADER ===== -->
<div class="topic-header">
    <div class="d-flex flex-column align-items-center">
        <div class="english-topic">
            <?= $topicName ?>
        </div>
        <?php if (!empty($topicNameUrdu)): ?>
        <div class="urdu-topic">
            <?= $topicNameUrdu ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="text-muted" style="font-size: 0.8em; margin-top: 2px; text-align: center;">
        Questions: <?= count($questions) ?>
    </div>
</div>

<!-- ===== QUESTIONS FOR THIS TOPIC ===== -->
<div class="topic-grid">
<?php 
// We need to process questions in batches of 2 to fill rows properly
$questionPairs = array_chunk($questions, 2);
$pairCount = 0;
foreach ($questionPairs as $pairIndex => $pair): 
    foreach ($pair as $indexInPair => $q): 
        $type = strtolower($q->type ?? $q->question_type);
        $questionLang = $q->question_lang ?? 'english';
        
        // Determine layout based on language
        $textClass = ($questionLang === 'urdu') ? 'urdu-text' : 'english-text';
        $containerClass = ($questionLang === 'urdu') ? 'urdu-layout' : 'english-layout';
        
        // Determine grid placement for ROW-WISE filling
        // For Urdu: Start from RIGHT column and go LEFT
        // For English: Start from LEFT column and go RIGHT
        
        if ($questionLang === 'urdu') {
            // Urdu questions: Q1 in right column, Q2 in left column, Q3 in right, etc.
            $gridColumn = ($indexInPair === 0) ? 2 : 1; // First in pair = right, second = left
        } else {
            // English questions: Q1 in left column, Q2 in right column, Q3 in left, etc.
            $gridColumn = ($indexInPair === 0) ? 1 : 2; // First in pair = left, second = right
        }
        
        // Calculate grid row (each pair is one row)
        $gridRow = $pairIndex + 1;
?>
<div class="question-card" style="grid-column: <?= $gridColumn ?>; grid-row: <?= $gridRow ?>;">
    <div class="p-0">
        <!-- COMPACT QUESTION DISPLAY -->
        <div class="question-container <?= $containerClass ?>">
            <div class="question-number">
                Q<?= $globalQNo++ ?>.
            </div>
            <div class="question-text-container">
                <span class="question-text <?= $textClass ?>">
                    <?= esc($q->question) ?>
                    
                    <!-- ================= MCQ SINGLE - Correct Answer ================= -->
                    <?php if ($type === 'mcq'): ?>
                    <?php
                        $correct = $q->correct_option ?? $q->correct_answer ?? '';
                        $correctText = '';
                        if (!empty($correct)) {
                            $field = 'option_' . strtolower($correct);
                            $correctText = $q->$field ?? '';
                            // Remove option letters
                            $correctText = preg_replace('/^[A-D]\.\s*/i', '', $correctText);
                            $correctText = preg_replace('/^[A-D]\s*/i', '', $correctText);
                        }
                        $answerLangClass = ($questionLang === 'urdu') ? 'urdu' : 'english';
                    ?>
                    <span class="correct-answer-inline mcq <?= $answerLangClass ?>" id="answer-<?= $globalQNo-1 ?>">
                        <?php if ($questionLang === 'urdu'): ?>
                            <span class="answer-label">:</span> جواب
                        <?php else: ?>
                            <span class="answer-label">Answer:</span>
                        <?php endif; ?>
                        <?= esc($correctText) ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- ================= MCQ MULTI - Correct Answers ================= -->
                    <?php if ($type === 'mcq_multi'): ?>
                    <?php 
                        $corrects = [];
                        
                        if (isset($q->options_json) && !empty($q->options_json)) {
                            $optionsData = is_string($q->options_json) ? json_decode($q->options_json, true) : $q->options_json;
                            
                            if (isset($optionsData['correct_multi']) && is_array($optionsData['correct_multi'])) {
                                $corrects = $optionsData['correct_multi'];
                            } 
                            elseif (isset($optionsData['correct_options'])) {
                                $corrects = (array)$optionsData['correct_options'];
                            }
                        }
                        
                        if (empty($corrects)) {
                            if (isset($q->correct_multi) && !empty($q->correct_multi)) {
                                if (is_string($q->correct_multi)) {
                                    $corrects = json_decode($q->correct_multi, true) ?? [$q->correct_multi];
                                } else {
                                    $corrects = (array)$q->correct_multi;
                                }
                            } elseif (isset($q->correct_options) && !empty($q->correct_options)) {
                                if (is_string($q->correct_options)) {
                                    $corrects = json_decode($q->correct_options, true) ?? [$q->correct_options];
                                } else {
                                    $corrects = (array)$q->correct_options;
                                }
                            } elseif (isset($q->correct_option)) {
                                $corrects = [$q->correct_option];
                            }
                        }
                        
                        $corrects = array_map(function($item) {
                            return strtoupper(trim($item));
                        }, (array)$corrects);
                        
                        $corrects = array_values(array_unique($corrects));
                        
                        // Get correct option texts WITHOUT option letters
                        $correctOptionsText = [];
                        foreach ($corrects as $opt) {
                            $field = 'option_' . strtolower($opt);
                            if (!empty($q->$field)) {
                                // Remove any leading option letter and period
                                $optionText = preg_replace('/^[A-D]\.\s*/i', '', $q->$field);
                                $optionText = preg_replace('/^[A-D]\s*/i', '', $optionText);
                                $correctOptionsText[] = esc($optionText);
                            }
                        }
                        
                        $answerLangClass = ($questionLang === 'urdu') ? 'urdu' : 'english';
                    ?>
                    <span class="correct-answer-inline mcq <?= $answerLangClass ?>" id="answer-<?= $globalQNo-1 ?>">
                        <?php if ($questionLang === 'urdu'): ?>
                            <span class="answer-label">:</span> جواب
                        <?php else: ?>
                            <span class="answer-label">Answer:</span>
                        <?php endif; ?>
                        <?= ($questionLang === 'urdu') ? implode('، ', $correctOptionsText) : implode(', ', $correctOptionsText) ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- ================= TRUE/FALSE ================= -->
                    <?php if ($type === 'tf'): ?>
                    <?php 
                        $tfAnswer = strtolower($q->answer_text ?? $q->correct_option ?? '');
                        $isTFTrue = ($tfAnswer === 'true' || $tfAnswer === 't' || $tfAnswer === '1');
                    ?>
                    <span class="correct-answer-inline tf-inline <?= $isTFTrue ? 'tf-true is-correct' : 'tf-false is-correct' ?>" id="answer-<?= $globalQNo-1 ?>">
                        <?= $isTFTrue ? 'True' : 'False' ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- ================= FILL IN BLANK ================= -->
                    <?php if ($type === 'fill'): ?>
                    <?php
                        $answerText = $q->answer_text ?? $q->correct_option ?? '';
                        $answerLangClass = (preg_match('/[\x{0600}-\x{06FF}]/u', $answerText)) ? 'urdu' : 'english';
                    ?>
                    <span class="correct-answer-inline fill <?= $answerLangClass ?>" id="answer-<?= $globalQNo-1 ?>">
                        <?php if ($questionLang === 'urdu'): ?>
                            <span class="answer-label">:</span> جواب
                        <?php else: ?>
                            <span class="answer-label">Ans:</span>
                        <?php endif; ?>
                        <?= esc($answerText) ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- ================= SHORT ANSWER ================= -->
                    <?php if ($type === 'short'): ?>
                    <?php
                        $answerText = $q->answer_text ?? $q->correct_option ?? '';
                        $answerLangClass = (preg_match('/[\x{0600}-\x{06FF}]/u', $answerText)) ? 'urdu' : 'english';
                    ?>
                    <span class="correct-answer-inline short <?= $answerLangClass ?>" id="answer-<?= $globalQNo-1 ?>">
                        <?php if ($questionLang === 'urdu'): ?>
                            <span class="answer-label">:</span> جواب
                        <?php else: ?>
                            <span class="answer-label">Answer:</span>
                        <?php endif; ?>
                        <?= esc($answerText) ?>
                    </span>
                    <?php endif; ?>
                    
                    <!-- ================= MATCH THE COLUMNS ================= -->
                    <?php if ($type === 'match'): ?>
                    <div class="match-answer-box <?= $textClass ?>" id="answer-<?= $globalQNo-1 ?>">
                        <?php if ($questionLang === 'urdu'): ?>
                            <span class="answer-label">:</span> جواب
                        <?php else: ?>
                            <span class="answer-label">Answer:</span>
                        <?php endif; ?>
                        <?php
                            $pairs = [];
                            
                            if (isset($q->options_json) && !empty($q->options_json)) {
                                if (is_string($q->options_json)) {
                                    $pairs = json_decode($q->options_json, true);
                                } elseif (is_array($q->options_json)) {
                                    $pairs = $q->options_json;
                                }
                            }
                            
                            if (empty($pairs) && isset($q->match_pairs) && !empty($q->match_pairs)) {
                                if (is_string($q->match_pairs)) {
                                    $pairs = json_decode($q->match_pairs, true);
                                } elseif (is_array($q->match_pairs)) {
                                    $pairs = $q->match_pairs;
                                }
                            }
                            
                            if (!empty($pairs)): 
                                foreach ($pairs as $index => $pair): 
                                    $left = $pair['left'] ?? $pair['column_a'] ?? '';
                                    $right = $pair['right'] ?? $pair['column_b'] ?? '';
                                    if (!empty($left) && !empty($right)):
                        ?>
                        <div class="match-pair">
                            <?= esc($left) ?> 
                            <span class="match-connector">↔</span> 
                            <?= esc($right) ?>
                        </div>
                        <?php 
                                    endif;
                                endforeach;
                            else: 
                        ?>
                        <div>Match data not available</div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                </span>
            </div>
        </div>

        <!-- ================= MATH PROBLEM ================= -->
        <?php if ($type === 'math'): ?>
        <div class="math-box mb-1">
            <?= nl2br(esc($q->question)) ?>
        </div>
        <?php if (!empty($q->option_a)): ?>
        <?php
            $mathCorrect = $q->correct_option ?? '';
            $correctText = '';
            if (!empty($mathCorrect)) {
                $field = 'option_' . strtolower($mathCorrect);
                $correctText = $q->$field ?? '';
                $correctText = preg_replace('/^[A-D]\.\s*/i', '', $correctText);
                $correctText = preg_replace('/^[A-D]\s*/i', '', $correctText);
            }
            $answerLangClass = (preg_match('/[\x{0600}-\x{06FF}]/u', $correctText)) ? 'urdu' : 'english';
        ?>
        <div class="correct-answer-inline math <?= $answerLangClass ?>" id="answer-<?= $globalQNo-1 ?>">
            <?php if ($questionLang === 'urdu'): ?>
                <span class="answer-label">:</span> جواب
            <?php else: ?>
                <span class="answer-label">Ans:</span>
            <?php endif; ?>
            <?= esc($correctText) ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Explanation for all types -->
        <?php if (!empty($q->explanation)): 
            $isUrduExp = (preg_match('/[\x{0600}-\x{06FF}]/u', $q->explanation));
        ?>
        <div class="solution-box <?= $isUrduExp ? 'urdu-text' : '' ?>" id="explanation-<?= $globalQNo-1 ?>">
            <?php if ($questionLang === 'urdu'): ?>
                <strong>وضاحت:</strong>
            <?php else: ?>
                <strong>Explanation:</strong>
            <?php endif; ?>
            <?= esc($q->explanation) ?>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php 
        $pairCount++;
    endforeach; 
endforeach; ?>
</div><!-- End of topic-grid -->
<?php endforeach; ?>
</div><!-- End of questions-container -->

<!-- ===== JS FOR TOGGLE SOLUTION ===== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleSolution');
    
    function updateSolutionDisplay() {
        const show = toggle.checked;
        
        // Show/hide correct answers inline
        const correctAnswers = document.querySelectorAll('.correct-answer-inline');
        correctAnswers.forEach(answer => {
            if (show) {
                answer.classList.add('show-answer');
                answer.style.display = 'inline';
            } else {
                answer.classList.remove('show-answer');
                answer.style.display = 'none';
            }
        });
        
        // Show/hide solution boxes
        const solutionBoxes = document.querySelectorAll('.solution-box');
        solutionBoxes.forEach(box => {
            if (show) {
                box.classList.add('show-answer');
                box.style.display = 'block';
            } else {
                box.classList.remove('show-answer');
                box.style.display = 'none';
            }
        });
        
        // Show/hide match answer boxes
        const matchBoxes = document.querySelectorAll('.match-answer-box');
        matchBoxes.forEach(box => {
            if (show) {
                box.classList.add('show-answer');
                box.style.display = 'block';
            } else {
                box.classList.remove('show-answer');
                box.style.display = 'none';
            }
        });
    }
    
    if (toggle) {
        // Initial state - hide all answers
        document.querySelectorAll('.correct-answer-inline').forEach(answer => {
            answer.style.display = 'none';
        });
        
        document.querySelectorAll('.solution-box').forEach(box => {
            box.style.display = 'none';
        });
        
        document.querySelectorAll('.match-answer-box').forEach(box => {
            box.style.display = 'none';
        });
        
        // Add event listener
        toggle.addEventListener('change', updateSolutionDisplay);
        
        // Check URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('showAnswers')) {
            toggle.checked = true;
            updateSolutionDisplay();
        }
    }
});
</script>

<!-- ===== PRINT BUTTON ===== -->
<div class="text-center mt-3 mb-4">
    <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
        🖨️ Print Quiz with Answers
    </button>
    <button onclick="toggleSolution.click()" class="btn btn-outline-success btn-sm ms-2">
        👁️ Show/Hide Answers
    </button>
    <small class="d-block text-muted mt-1" style="font-size: 0.85em;">
        When printing, correct answers will be shown automatically
    </small>
</div>

<?= $this->endSection() ?>