<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
/* Page layout for printing */
@page {
    size: A4;
    margin: 10mm !important;
}

body {
    margin: 10mm;
}

/* Main styles */
.solution-box {
    display: none;
    margin-top: 4px;
    padding: 8px 10px;
    background: #f0fdf4;
    border: 1px solid #22c55e;
    border-radius: 6px;
    font-size: 0.95em;
}

/* MCQ Options - Compact horizontal layout */
.q-options-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    padding-left: 0;
    margin: 6px 0 2px 0;
}

.q-option {
    padding: 5px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: white;
    font-size: 12pt !important; /* Changed from 0.95em to 12pt */
    min-width: 60px;
    text-align: left;
    flex: 1;
    max-width: 250px;
}

.q-option.is-correct {
    background: #f0fdf4 !important;
    border: 2px solid #000 !important;
    font-weight: 700 !important;
    position: relative;
    padding-left: 30px;
}

.q-option.is-correct::before {
    content: "✓";
    position: absolute;
    left: 8px;
    font-weight: bold;
    color: #000;
    font-size: 12pt; /* Added font-size */
}
/* True/False inline styling */
.tf-inline {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-weight: 600;
    margin-left: 12px;
    font-size: 0.9em;
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

/* Match Columns - Print friendly */
.match-container {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin: 8px 0;
}

.match-column {
    flex: 1;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 10px;
    background: #f9fafb;
}

.match-item {
    padding: 6px 8px;
    margin-bottom: 6px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-size: 0.9em;
    min-height: 30px;
    display: flex;
    align-items: center;
}

.match-number {
    display: inline-block;
    width: 22px;
    height: 22px;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 22px;
    margin-right: 8px;
    font-size: 0.85em;
    font-weight: bold;
}

/* Math and other elements */
.math-box {
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    margin: 6px 0;
    font-family: monospace;
    font-size: 0.95em;
}

.fill-blank {
    display: inline-block;
    padding: 0 6px;
    border-bottom: 1px dashed #4b5563;
    min-width: 60px;
    margin: 0 3px;
}

.fill-blank.solution {
    background: #dbeafe;
    border-bottom: 2px solid #1d4ed8;
    font-weight: 600;
}

.question-card {
    margin-bottom: 2px;
    padding-bottom: 2px;
    break-inside: avoid;
}

.question-header {
    margin-bottom: 4px;
}

/* PRINT SPECIFIC STYLES */
@media print {
    /* Hide unnecessary elements */
    #toggleSolutionWrap,
    .btn,
    .text-center {
        display: none !important;
    }
    
    /* Show all correct answers when printing */
    .solution-box {
        display: block !important;
        background: #fff !important;
        border: 1px solid #000 !important;
        margin-top: 4px;
    }
    
     .q-option {
        font-size: 12pt !important;
        background: white !important;
        border: 1px solid #000 !important;
    }
    
    .q-option.is-correct {
        background: #fff !important;
        border: 2px solid #000 !important;
        font-size: 12pt !important;
    }
    
    .q-option.is-correct::before {
        font-size: 12pt !important;
    }
    
    
    .tf-inline.is-correct {
        background: #fff !important;
        border: 2px solid #000 !important;
        font-weight: 700;
        padding: 2px 10px;
    }
    
    .tf-inline,
    .fill-blank.solution {
        display: inline-block !important;
        visibility: visible !important;
    }
    
    /* Ensure match lines print correctly */
    .match-container {
        page-break-inside: avoid;
    }
    
    /* Show match connectors in print */
    .match-connector-print {
        display: block !important;
        margin: 6px 0;
        font-size: 0.9em;
    }
    
    .match-connector-screen {
        display: none !important;
    }
    
    /* Remove backgrounds for printing */
    .q-option,
    .match-item,
    .math-box {
        background: white !important;
        border: 1px solid #000 !important;
    }
    
    .match-column {
        border: 1px solid #000;
        background: white !important;
    }
    
    /* Compact spacing for print */
    .question-card {
        margin-bottom: 6px;
        padding-bottom: 2px;
    }
    
    /* Remove all hover effects */
    .question-card:hover {
        box-shadow: none;
    }
    
    /* Page margins */
    body {
        margin: 10mm !important;
    }
}

/* Match connector solution for screen */
.match-connector-screen {
    display: none;
    text-align: center;
    margin: 8px 0;
    font-weight: 600;
    color: #3b82f6;
}

/* Utility classes */
.mb-1 { margin-bottom: 4px !important; }
.mb-2 { margin-bottom: 8px !important; }
.mt-1 { margin-top: 4px !important; }
.mt-2 { margin-top: 8px !important; }

/* Header styles */
.quiz-header {
    text-align: center;
    border: 2px solid #000;
    padding: 10px;
    margin-bottom: 15px;
    page-break-inside: avoid;
}

.quiz-subject {
    font-size: 1.2em;
    font-weight: bold;
    margin-bottom: 5px;
    color: #1e40af;
}

.quiz-class {
    font-size: 1.8em;
    font-weight: bold;
    margin-bottom: 8px;
}

.quiz-topics {
    font-size: 2.2em;
    font-weight: bold;
    margin-bottom: 8px;
}

.quiz-meta {
    font-size: 1.2em;
    color: #666;
}

/* Question text size */
.question-text {
    font-size: 1.5em;
    line-height: 1.4;
}

/* Compact layout */
.compact-layout .question-card {
    margin-bottom: 0;
    padding-bottom: 0;
}
</style>

<!-- ===== QUIZ HEADER ===== -->
<div class="quiz-header">
    <div class="quiz-subject"><?= esc($quiz->subject_name ?? 'Urdu') ?></div>
    <div class="quiz-class"><?= esc($quiz->cls_sec_name ?? 'Grade 2 - A') ?></div>
    <div class="quiz-topics"><?= esc($quiz->title) ?></div>
    <div class="quiz-meta">
        Total Questions: <?= count($questions ?? []) ?> | 
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

<div class="compact-layout">
<?php 
$qNo = 1; 
foreach ($questions as $q): 
    $type = strtolower($q->type ?? $q->question_type);
?>
<div class="question-card">
    <div class="p-1">
        <!-- QUESTION HEADER - Compact -->
        <div class="question-header">
            <div class="d-flex justify-content-between align-items-start">
                <div style="flex: 1;">
                    <strong style="font-size: 1.5em;">Q<?= $qNo++ ?>.</strong>
                    <span class="question-text"><?= esc($q->question) ?></span>
                    
                    <!-- TRUE/FALSE Answer inline -->
                    <?php if ($type === 'tf'): ?>
                    <?php 
                        $tfAnswer = strtolower($q->answer_text ?? $q->correct_option ?? '');
                        $isTFTrue = ($tfAnswer === 'true' || $tfAnswer === 't' || $tfAnswer === '1');
                    ?>
                    <span class="solution-box tf-inline <?= $isTFTrue ? 'tf-true is-correct' : 'tf-false is-correct' ?>">
                        <?= $isTFTrue ? 'True' : 'False' ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ================= MCQ SINGLE ================= -->
        <?php if ($type === 'mcq'): ?>
        <?php
            $correct = $q->correct_option ?? $q->correct_answer ?? '';
            if (empty($correct) && isset($q->answer_text)) {
                $correct = $q->answer_text;
            }
        ?>
        <ul class="q-options-row">
            <?php foreach (['A','B','C','D'] as $opt):
                $field = 'option_' . strtolower($opt);
                if (!empty($q->$field)): 
            ?>
            <li class="q-option <?= ($correct === $opt) ? 'is-correct' : '' ?>">
                <strong><?= $opt ?>.</strong> <?= esc($q->$field) ?>
            </li>
            <?php endif; endforeach; ?>
        </ul>
        <?php endif; ?>

        <!-- ================= MCQ MULTI ================= -->
        <?php if ($type === 'mcq_multi'): ?>
        <?php 
            $corrects = [];
            
            // Parse from options_json based on your structure
            if (isset($q->options_json) && !empty($q->options_json)) {
                $optionsData = is_string($q->options_json) ? json_decode($q->options_json, true) : $q->options_json;
                
                // Check for correct_multi in the JSON structure
                if (isset($optionsData['correct_multi']) && is_array($optionsData['correct_multi'])) {
                    $corrects = $optionsData['correct_multi'];
                } 
                // Fallback to correct_options
                elseif (isset($optionsData['correct_options'])) {
                    $corrects = (array)$optionsData['correct_options'];
                }
            }
            
            // Fallback to direct fields
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
            
            // Ensure all corrects are uppercase and trimmed
            $corrects = array_map(function($item) {
                return strtoupper(trim($item));
            }, (array)$corrects);
            
            $corrects = array_values(array_unique($corrects));
        ?>
        <ul class="q-options-row">
            <?php foreach (['A','B','C','D'] as $opt):
                $field = 'option_' . strtolower($opt);
                if (!empty($q->$field)): 
                    $isCorrect = in_array($opt, $corrects);
            ?>
            <li class="q-option <?= $isCorrect ? 'is-correct' : '' ?>">
                <strong><?= $opt ?>.</strong> <?= esc($q->$field) ?>
            </li>
            <?php endif; endforeach; ?>
        </ul>
        <?php endif; ?>

        <!-- ================= FILL IN BLANK ================= -->
        <?php if ($type === 'fill'): ?>
        <?php
            $questionText = $q->question;
            $displayText = preg_replace('/_{3,}/', '<span class="fill-blank">_____</span>', $questionText);
        ?>
        <div style="margin: 4px 0;">
            <?= $displayText ?>
            <span class="solution-box fill-blank solution">
                <?= esc($q->answer_text ?? $q->correct_option ?? '') ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- ================= SHORT ANSWER ================= -->
        <?php if ($type === 'short'): ?>
        <div class="solution-box" style="background: #fef3c7; border-color: #f59e0b; font-size: 0.9em;">
            <strong>Answer:</strong> <?= esc($q->answer_text ?? $q->correct_option ?? '') ?>
        </div>
        <?php endif; ?>

        <!-- ================= MATH PROBLEM ================= -->
        <?php if ($type === 'math'): ?>
        <div class="math-box mb-1">
            <?= nl2br(esc($q->question)) ?>
        </div>
        
        <?php if (!empty($q->option_a)): ?>
        <?php
            $mathCorrect = $q->correct_option ?? '';
        ?>
        <ul class="q-options-row">
            <?php foreach (['A','B','C','D'] as $opt):
                $field = 'option_' . strtolower($opt);
                if (!empty($q->$field)):
            ?>
            <li class="q-option <?= ($mathCorrect === $opt) ? 'is-correct' : '' ?>">
                <strong><?= $opt ?>.</strong> <?= esc($q->$field) ?>
            </li>
            <?php endif; endforeach; ?>
        </ul>
        <?php endif; ?>
        
        <div class="solution-box">
            <strong>Solution:</strong> <?= esc($q->answer_text ?? '') ?>
        </div>
        <?php endif; ?>

        <!-- ================= MATCH THE COLUMNS ================= -->
        <?php if ($type === 'match'): ?>
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
                $leftItems = [];
                $rightItems = [];
                
                foreach ($pairs as $pair) {
                    $leftItems[] = $pair['left'] ?? '';
                    $rightItems[] = $pair['right'] ?? '';
                }
                
                $leftItems = array_filter($leftItems);
                $rightItems = array_filter($rightItems);
                
                // Shuffle right column for display
                $displayRight = $rightItems;
                shuffle($displayRight);
                
                // Store correct connections
                $connections = [];
                foreach ($pairs as $index => $pair) {
                    $rightIndex = array_search($pair['right'], $displayRight);
                    if ($rightIndex !== false) {
                        $connections[] = [
                            'left' => $index,
                            'right' => $rightIndex,
                            'leftText' => $pair['left'],
                            'rightText' => $pair['right']
                        ];
                    }
                }
        ?>
        
        <div class="match-container">
            <div class="match-column">
                <div style="text-align:center; font-weight:600; margin-bottom:6px; font-size:0.9em;">Column A</div>
                <?php foreach ($leftItems as $index => $left): ?>
                <div class="match-item">
                    <span class="match-number"><?= $index + 1 ?></span>
                    <?= esc($left) ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="match-column">
                <div style="text-align:center; font-weight:600; margin-bottom:6px; font-size:0.9em;">Column B</div>
                <?php foreach ($displayRight as $index => $right): ?>
                <div class="match-item">
                    <span class="match-number"><?= chr(65 + $index) ?></span>
                    <?= esc($right) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Print version connectors -->
        <div class="solution-box match-connector-print">
            <?php foreach ($connections as $conn): ?>
            <div style="margin: 2px 0; font-size: 0.9em;">
                <strong><?= $conn['left'] + 1 ?>.</strong> <?= esc($conn['leftText']) ?> 
                ↔ 
                <strong><?= chr(65 + $conn['right']) ?>.</strong> <?= esc($conn['rightText']) ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Screen version connectors -->
        <div class="solution-box match-connector-screen">
            <div style="margin-bottom: 4px;"><strong>Correct Matching:</strong></div>
            <?php foreach ($connections as $conn): ?>
            <div style="margin: 2px 0; font-size: 0.9em;">
                <strong><?= $conn['left'] + 1 ?>.</strong> <?= esc($conn['leftText']) ?> 
                ↔ 
                <strong><?= chr(65 + $conn['right']) ?>.</strong> <?= esc($conn['rightText']) ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php else: ?>
        <div style="color: #dc2626; font-size: 0.9em; margin: 6px 0;">
            Match data not available
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Explanation for all types (if exists) -->
        <?php if (!empty($q->explanation)): ?>
        <div class="solution-box" style="font-size: 0.9em; margin-top: 4px;">
            <strong>Explanation:</strong> <?= esc($q->explanation) ?>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ===== JS FOR TOGGLE SOLUTION ===== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleSolution');
    
    function updateSolutionDisplay() {
        const show = toggle.checked;
        const boxes = document.querySelectorAll('.solution-box');
        boxes.forEach(box => {
            box.style.display = show ? 'block' : 'none';
        });
        
        // For match questions, show/hide connector lines
        const matchConnectors = document.querySelectorAll('.match-connector-screen');
        matchConnectors.forEach(connector => {
            connector.style.display = show ? 'block' : 'none';
        });
        
        // Also show correct MCQ options
        if (show) {
            document.querySelectorAll('.q-option.is-correct').forEach(opt => {
                opt.style.display = 'flex';
            });
        }
    }
    
    if (toggle) {
        // Initial state - hide all answers
        document.querySelectorAll('.solution-box').forEach(box => {
            box.style.display = 'none';
        });
        
        // Hide match connectors initially
        document.querySelectorAll('.match-connector-screen').forEach(connector => {
            connector.style.display = 'none';
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
    <button onclick="location.href=location.pathname+'?showAnswers=true'" 
            class="btn btn-outline-success btn-sm ms-2">
        👁️ Show All Answers
    </button>
    <small class="d-block text-muted mt-1" style="font-size: 0.85em;">
        When printing, correct answers will be shown automatically
    </small>
</div>

<?= $this->endSection() ?>