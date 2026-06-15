<?php

/**

 * @var list<array<string, mixed>> $questions

 * @var array<string, mixed> $config

 * @var bool $showAnswers

 * @var list<array<string, mixed>>|null $typeSections

 */

$layout = $config['layout'] ?? [];

$svc    = new \App\Libraries\QuestionPaperService();



if (empty($typeSections) && !empty($questions)) {

    $typeSections = $svc->enrichTypeSections(

        $svc->groupByTypeSections($questions),

        $config['section_marks'] ?? []

    );

}



$descChoice = $layout['descriptive_choice'] ?? [];

?>

<?php foreach ($typeSections ?? [] as $si => $section): ?>

  <?php

    $sectionTitle = (string) ($section['title'] ?? ('Section ' . ($section['letter'] ?? '')));

    $secMarks = (float) ($section['section_marks'] ?? 0);

    if ($secMarks > 0) {

        $sectionTitle .= ' (' . \App\Libraries\QuestionPaperService::formatMarksValue($secMarks) . ' marks)';

    }

    $sectionQuestions = $section['questions'] ?? [];

    $qMarkLabel = '';

    if (!empty($layout['show_question_marks'])) {

        $qMarkLabel = (string) ($section['marks_per_question_label'] ?? '');

    }

    $isDescriptive = ($section['type_key'] ?? '') === 'descriptive';

    $choiceNote = $isDescriptive

        ? $svc->descriptiveChoiceSectionNote($descChoice, count($sectionQuestions))

        : '';

  ?>

  <?php if (!empty($layout['page_break_topic']) && $si > 0): ?>

    <div class="qp-page-break"></div>

  <?php endif; ?>

  <div class="qp-section-title"><?= esc($sectionTitle) ?></div>

  <?php if ($choiceNote !== ''): ?>

    <div class="qp-section-choice-note"><?= esc($choiceNote) ?></div>

  <?php endif; ?>

  <?php if ($isDescriptive && ($descChoice['mode'] ?? 'none') === 'pairs' && !empty($descChoice['pairs'])): ?>

    <?= view('admin/question_paper/partials/descriptive_questions_block', [

        'questions'    => $sectionQuestions,

        'section'      => $section,

        'layout'       => $layout,

        'showAnswers'  => $showAnswers,

        'qMarkLabel'   => $qMarkLabel,

    ]) ?>

  <?php else: ?>

    <?php

      $roman = 0;

      foreach ($sectionQuestions as $q):

          $roman++;

    ?>

      <?= view('admin/question_paper/partials/render_question', [

          'q'           => $q,

          'qRoman'      => \App\Libraries\QuestionPaperService::toRoman($roman),

          'qMarkLabel'  => $qMarkLabel,

          'showAnswers' => $showAnswers,

          'layout'      => $layout,

      ]) ?>

    <?php endforeach; ?>

  <?php endif; ?>

<?php endforeach; ?>
