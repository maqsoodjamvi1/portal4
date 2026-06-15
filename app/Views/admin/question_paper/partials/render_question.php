<?php
/**
 * @var array<string, mixed> $q
 * @var string $qRoman Roman numeral label (e.g. i, ii, iii); empty when unnumbered (pair alternative)
 * @var bool $showNumber Whether to show the question number prefix
 * @var string $extraClass Optional CSS class on the card
 * @var string $qMarkLabel Per-question marks label when enabled
 * @var bool $showAnswers
 * @var array<string, mixed> $layout
 */
$type = strtolower((string) ($q['question_type'] ?? 'mcq'));
$mcqInline = !empty($layout['mcq_inline']);
$descAnswerSpace = !empty($layout['descriptive_answer_space'])
    || (!array_key_exists('descriptive_answer_space', $layout)
        && (int) ($layout['descriptive_lines'] ?? 0) > 0);
$descLines = $descAnswerSpace
    ? max(1, min(12, (int) ($layout['descriptive_lines'] ?? 6)))
    : 0;
$isUrdu = !empty($q['question']) && preg_match('/[\x{0600}-\x{06FF}]/u', (string) $q['question']);
$showNumber = !isset($showNumber) || $showNumber;
$extraClass = trim((string) ($extraClass ?? ''));
$mcqBold = $type === 'mcq' || $type === 'mcq_multi';
?>
<div class="qp-question-card<?= $extraClass !== '' ? ' ' . esc($extraClass) : '' ?>">
  <div class="qp-q-head<?= $mcqBold ? ' qp-q-head-mcq' : '' ?>">
    <?php if ($showNumber && ($qRoman ?? '') !== ''): ?>
      <strong class="qp-q-num"><?= esc($qRoman) ?>.</strong>
    <?php endif; ?>
    <?php if (($q['question_media'] ?? 'text') === 'image' && !empty($q['question_image_public_url'])): ?>
      <div class="qp-q-img mb-1">
        <img src="<?= esc($q['question_image_public_url'], 'attr') ?>" alt="" class="img-fluid" style="max-height:180px;">
      </div>
    <?php endif; ?>
    <?php if (!empty($q['question'])): ?>
      <span class="qp-q-text <?= $isUrdu ? 'qp-urdu' : '' ?>"><?= esc((string) $q['question']) ?></span>
    <?php endif; ?>
    <?php if (!empty($qMarkLabel)): ?>
      <span class="qp-q-marks"> (<?= esc($qMarkLabel) ?> marks)</span>
    <?php endif; ?>
  </div>

  <?php if ($type === 'mcq' || $type === 'mcq_multi'): ?>
    <?php
      $correct = strtoupper(trim((string) ($q['correct_option'] ?? 'A')));
      $correctSet = [];
      if ($type === 'mcq_multi' && is_array($q['correct_options'] ?? null)) {
          foreach ($q['correct_options'] as $co) {
              $correctSet[strtoupper((string) $co)] = true;
          }
      }
    ?>
    <?php if ($mcqInline): ?>
      <p class="qp-mcq-inline-line">
        <?php foreach (['A', 'B', 'C', 'D'] as $opt):
            $field = 'option_' . strtolower($opt);
            $text  = trim((string) ($q[$field] ?? ''));
            if ($text === '') {
                continue;
            }
            $isCor = $type === 'mcq'
                ? ($correct === $opt)
                : isset($correctSet[$opt]);
        ?>
          <span class="qp-mcq-opt <?= ($showAnswers && $isCor) ? 'qp-correct' : '' ?>"><strong><?= $opt ?>.</strong> <?= esc($text) ?></span>
        <?php endforeach; ?>
      </p>
    <?php else: ?>
      <ul class="qp-mcq-list">
        <?php foreach (['A', 'B', 'C', 'D'] as $opt):
            $field = 'option_' . strtolower($opt);
            $text  = trim((string) ($q[$field] ?? ''));
            if ($text === '') {
                continue;
            }
            $isCor = $type === 'mcq'
                ? ($correct === $opt)
                : isset($correctSet[$opt]);
        ?>
          <li class="qp-mcq-opt <?= ($showAnswers && $isCor) ? 'qp-correct' : '' ?>">
            <strong><?= $opt ?>.</strong> <?= esc($text) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  <?php elseif ($type === 'tf'): ?>
    <?php if ($showAnswers): ?>
      <?php $tf = strtolower(trim((string) ($q['answer_text'] ?? ''))); ?>
      <div class="qp-ans-box">Answer: <strong><?= $tf === 'true' ? 'True' : 'False' ?></strong></div>
    <?php else: ?>
      <div class="qp-tf-blanks">( &nbsp; ) True &nbsp;&nbsp; ( &nbsp; ) False</div>
    <?php endif; ?>

  <?php elseif ($type === 'fill'): ?>
    <?php if ($showAnswers): ?>
      <div class="qp-ans-box">Answer: <?= esc((string) ($q['answer_text'] ?? '')) ?></div>
    <?php else: ?>
      <div class="qp-fill-line">Answer: _________________________________</div>
    <?php endif; ?>

  <?php elseif ($type === 'short'): ?>
    <?php if ($showAnswers): ?>
      <div class="qp-ans-box">Answer: <?= esc((string) ($q['answer_text'] ?? '')) ?></div>
    <?php else: ?>
      <div class="qp-short-lines">
        <div class="qp-line"></div><div class="qp-line"></div>
      </div>
    <?php endif; ?>

  <?php elseif ($type === 'descriptive'): ?>
    <?php if ($showAnswers): ?>
      <div class="qp-ans-box qp-descriptive-key">
        <div class="small text-muted mb-1">Model answer (guideline)</div>
        <div style="white-space:pre-wrap;"><?= esc((string) ($q['answer_text'] ?? '')) ?></div>
      </div>
    <?php elseif ($descLines > 0): ?>
      <div class="qp-desc-lines">
        <?php for ($i = 0; $i < $descLines; $i++): ?>
          <div class="qp-line"></div>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

  <?php elseif ($type === 'match'): ?>
    <?php $pairs = $q['match_pairs'] ?? []; ?>
    <?php if (!empty($pairs) && is_array($pairs)): ?>
      <div class="qp-match-grid">
        <div class="qp-match-col">
          <?php foreach ($pairs as $pi => $p): ?>
            <div class="qp-match-item"><span class="qp-match-n"><?= $pi + 1 ?>.</span> <?= esc((string) ($p['left'] ?? '')) ?></div>
          <?php endforeach; ?>
        </div>
        <div class="qp-match-col">
          <?php
            $rights = array_values($pairs);
            if (!$showAnswers) {
                $shuffled = $rights;
                shuffle($shuffled);
                $rights = $shuffled;
            }
            foreach ($rights as $pi => $p):
          ?>
            <div class="qp-match-item"><span class="qp-match-n"><?= chr(65 + $pi) ?>.</span> <?= esc((string) ($p['right'] ?? '')) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if ($showAnswers): ?>
        <div class="qp-ans-box small mt-1">
          <?php foreach ($pairs as $pi => $p): ?>
            <div><?= ($pi + 1) ?>. <?= esc((string) ($p['left'] ?? '')) ?> → <?= esc((string) ($p['right'] ?? '')) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>
