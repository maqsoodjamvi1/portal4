<?php
/**
 * @var list<array<string, mixed>> $questions
 * @var array<string, mixed> $section
 * @var array<string, mixed> $layout
 * @var bool $showAnswers
 * @var string $qMarkLabel
 */
$choice = $layout['descriptive_choice'] ?? [];
$svc    = new \App\Libraries\QuestionPaperService();
$items  = $svc->buildDescriptiveDisplayItems($questions, $choice);
$openPair = false;

foreach ($items as $item) {
    if (($item['type'] ?? '') === 'or') {
        echo '<div class="qp-or-divider"><span>OR</span></div>';
        continue;
    }

    $q = $item['q'] ?? null;
    if (!is_array($q)) {
        continue;
    }

    $showNumber = !isset($item['show_number']) || !empty($item['show_number']);
    $roman      = $showNumber ? \App\Libraries\QuestionPaperService::toRoman((int) ($item['roman'] ?? 0)) : '';
    $isPairLead = $showNumber && !empty($item['pair_part']);

    if ($isPairLead) {
        if ($openPair) {
            echo '</div>';
        }
        echo '<div class="qp-pair-card">';
        $openPair = true;
    } elseif ($openPair && $showNumber && empty($item['pair_part'])) {
        echo '</div>';
        $openPair = false;
    }

    echo view('admin/question_paper/partials/render_question', [
        'q'           => $q,
        'qRoman'      => $roman,
        'showNumber'  => $showNumber,
        'qMarkLabel'  => $qMarkLabel,
        'showAnswers' => $showAnswers,
        'layout'      => $layout,
        'extraClass'  => (!$showNumber && $openPair) ? 'qp-pair-alt' : '',
    ]);
}
if ($openPair) {
    echo '</div>';
}
