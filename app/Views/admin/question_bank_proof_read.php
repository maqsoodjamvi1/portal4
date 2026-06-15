<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('pageStyles') ?>
<style>
  @media (max-width: 767.98px) {
    .qb-proof-page .sms-page-header__title {
      font-size: 1.2rem;
      line-height: 1.3;
    }
    .qb-proof-page .sms-page-header__subtitle {
      font-size: .875rem;
      word-break: break-word;
    }
    .qb-proof-page .sms-page-header .breadcrumb {
      float: none !important;
      padding-left: 0;
      margin-top: .35rem;
      font-size: .85rem;
    }
    .qb-proof-page .sms-page-header .row.mb-2 > [class*="col-"] {
      margin-bottom: .5rem;
    }
    .qb-proof-page .sms-page-header .row.mb-2 > [class*="col-"]:last-child {
      margin-bottom: 0;
    }
  }

  /* Print Questions Only Mode - hides answers and options */
@media print {
    body.print-questions-only .qb-proof-ans,
    body.print-questions-only .qb-mcq-options-line,
    body.print-questions-only .qb-proof-fill-paren,
    body.print-questions-only .qb-proof-tf-ico,
    body.print-questions-only .qb-proof-ans,
    body.print-questions-only .qb-opt-correct,
    body.print-questions-only .qb-proof-head-actions,
    body.print-questions-only .qb-proof-btn-edit,
    body.print-questions-only .qb-proof-btn-delete,
    body.print-questions-only .qb-proof-difficulty,
    body.print-questions-only .qb-proof-type-heading-icon {
        display: none !important;
    }

    /* Keep question text, keep blank spaces for answers */
    body.print-questions-only .qb-proof-fill-line {
        margin-bottom: 0.5rem;
    }

    body.print-questions-only .qb-proof-fill-stem {
        display: inline;
    }

    /* Add blank line for fill-in-the-blank answers */
    body.print-questions-only .qb-proof-fill-line::after {
        content: "______________";
        display: inline-block;
        margin-left: 0.5rem;
        letter-spacing: 2px;
    }

    /* For descriptive questions, add answer space */
    body.print-questions-only .qb-proof-q {
        margin-bottom: 1rem;
    }

    body.print-questions-only .qb-proof-q::after {
        content: "";
        display: block;
        margin-top: 1rem;
        border-bottom: 1px dashed #ccc;
        width: 100%;
    }

    /* For MCQ, show options without marking correct one */
    body.print-questions-only .qb-proof-item .qb-mcq-options-line {
        display: flex !important;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    body.print-questions-only .qb-proof-item .qb-opt {
        display: inline-block;
        margin-right: 1rem;
        background: none !important;
        padding: 0 !important;
    }

    body.print-questions-only .qb-proof-item .qb-opt-correct {
        background: none !important;
        font-weight: normal !important;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$meta       = $meta ?? [];
$labels     = $labels ?? [];
$questions  = $questions ?? [];
$scope      = trim(($labels['class_name'] ?? '') . (isset($labels['subject_name']) && $labels['subject_name'] !== '' ? ' · ' . $labels['subject_name'] : '') . (isset($labels['topic_name']) && $labels['topic_name'] !== '' ? ' · ' . $labels['topic_name'] : ''));

$editUrl = site_url('admin/question-bank/form')
    . '?class_id=' . (int) ($filter_class_id ?? 0)
    . '&subject_id=' . (int) ($filter_subject_id ?? 0)
    . '&topic_id=' . (int) ($filter_topic_id ?? 0)
    . '&load=1';

// Hierarchy: class → subject → topic → questions (no repeated topic labels on cards)
$proofHierarchy = [];
if ($questions !== []) {
    usort($questions, static function (array $a, array $b): int {
        $ca = (int) ($a['class_id'] ?? 0);
        $cb = (int) ($b['class_id'] ?? 0);
        if ($ca !== $cb) {
            return $ca <=> $cb;
        }
        $sa = (int) ($a['subject_id'] ?? 0);
        $sb = (int) ($b['subject_id'] ?? 0);
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }
        $ta = (int) ($a['topic_id'] ?? 0);
        $tb = (int) ($b['topic_id'] ?? 0);
        if ($ta !== $tb) {
            return $ta <=> $tb;
        }
        $ia = (int) ($a['id'] ?? 0);
        $ib = (int) ($b['id'] ?? 0);

        return $ia <=> $ib;
    });

    $tree = [];
    foreach ($questions as $q) {
        $cid = (int) ($q['class_id'] ?? 0);
        $sid = (int) ($q['subject_id'] ?? 0);
        $tid = (int) ($q['topic_id'] ?? 0);
        if (!isset($tree[$cid])) {
            $tree[$cid] = [
                'class_id'   => $cid,
                'class_name' => (string) ($q['class_name'] ?? ('Class #' . $cid)),
                'subjects'   => [],
            ];
        }
        if (!isset($tree[$cid]['subjects'][$sid])) {
            $tree[$cid]['subjects'][$sid] = [
                'subject_id'   => $sid,
                'subject_name' => (string) ($q['subject_name'] ?? ('Subject #' . $sid)),
                'topics'       => [],
            ];
        }
        if (!isset($tree[$cid]['subjects'][$sid]['topics'][$tid])) {
            $tree[$cid]['subjects'][$sid]['topics'][$tid] = [
                'topic_id'   => $tid,
                'topic_name' => (string) ($q['topic_name'] ?? ('Topic #' . $tid)),
                'questions'  => [],
            ];
        }
        $tree[$cid]['subjects'][$sid]['topics'][$tid]['questions'][] = $q;
    }

    ksort($tree);
    foreach ($tree as &$classRow) {
        ksort($classRow['subjects']);
        $classRow['subjects'] = array_values($classRow['subjects']);
        foreach ($classRow['subjects'] as &$subRow) {
            ksort($subRow['topics']);
            $subRow['topics'] = array_values($subRow['topics']);
        }
        unset($subRow);
    }
    unset($classRow);
    $proofHierarchy = array_values($tree);

    // Per topic: group questions by type (single heading per type under topic)
    $typeRank = ['mcq' => 1, 'mcq_multi' => 2, 'tf' => 3, 'fill' => 4, 'short' => 5, 'descriptive' => 6, 'match' => 7];
    foreach ($proofHierarchy as &$classRow) {
        foreach ($classRow['subjects'] as &$subRow) {
            foreach ($subRow['topics'] as &$topRow) {
                $bucket = [];
                foreach ($topRow['questions'] as $q) {
                    $t = strtolower((string) ($q['question_type'] ?? 'other'));
                    if (!isset($bucket[$t])) {
                        $bucket[$t] = [];
                    }
                    $bucket[$t][] = $q;
                }
                $groups = [];
                foreach ($bucket as $t => $qs) {
                    $groups[] = ['type' => $t, 'questions' => $qs];
                }
                usort($groups, static function (array $a, array $b) use ($typeRank): int {
                    return ($typeRank[$a['type']] ?? 99) <=> ($typeRank[$b['type']] ?? 99);
                });
                $topRow['type_groups'] = $groups;
            }
        }
    }
    unset($classRow, $subRow, $topRow);
}

$qbProofTypeLabels = [
    'mcq'       => 'MCQ (single correct)',
    'mcq_multi' => 'MCQ (multiple correct)',
    'tf'        => 'True / False',
    'fill'      => 'Fill in the blank',
    'short'       => 'Short answer',
    'descriptive' => 'Descriptive (model answer)',
    'match'       => 'Match pairs',
    'other'     => 'Other',
];
?>

<?php
$proofActions = '';
if (! empty($questions)) {
    $proofActions .= '<div class="btn-group me-2" role="group">';
    $proofActions .= '<button type="button" class="btn btn-outline-secondary btn-sm" id="printStandardBtn"><i class="fas fa-print"></i> Print with solutions</button>';
    $proofActions .= '<button type="button" class="btn btn-outline-secondary btn-sm" id="printQuestionsOnlyBtn"><i class="fas fa-print"></i> <i class="fas fa-question-circle"></i> Print questions only</button>';
    $proofActions .= '</div>';
}
$proofActions .= '<a href="' . esc(site_url('admin/question-bank/overview'), 'attr') . '" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Overview</a> ';
$proofActions .= '<a href="' . esc($editUrl, 'attr') . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit form</a>';
?>
<div class="no-print qb-proof-page">
<?= view('components/page_header', [
    'title' => 'Question bank — proof read',
    'icon' => 'fas fa-spell-check',
    'subtitle' => $scope !== '' ? $scope : 'Filtered list',
    'actionsHtml' => '<div class="d-flex flex-wrap justify-content-start justify-content-sm-end w-100 w-sm-auto no-print" style="gap:.5rem;">' . $proofActions . '</div>',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Question bank', 'url' => site_url('admin/question-bank/overview')],
        ['label' => 'Proof read', 'active' => true],
    ],
]) ?>
</div>

<section class="content qb-proof-page" id="qb-proof-print-area">
  <?php if (!empty($questions)): ?>
  <div class="qb-proof-print-banner">
    <h2 class="qb-proof-print-banner-title">Question bank — proof read</h2>
    <p class="qb-proof-print-banner-scope"><?= esc($scope !== '' ? $scope : 'Filtered list') ?></p>
    <p class="qb-proof-print-banner-meta small text-muted mb-0">
      <?= (int) ($meta['returned'] ?? 0) ?> of <?= (int) ($meta['total'] ?? 0) ?> question(s)
      <?php if (!empty($meta['truncated'])): ?>
        · First <?= (int) ($meta['limit'] ?? 500) ?> only
      <?php endif; ?>
      · Printed <?= date('d M Y, H:i') ?>
    </p>
  </div>
  <?php endif; ?>

  <div class="card mb-3 no-print">
    <div class="card-body py-2">
      <span class="small text-muted">
        Showing <strong><?= (int) ($meta['returned'] ?? 0) ?></strong> of <strong><?= (int) ($meta['total'] ?? 0) ?></strong> question(s)
        <?php if (!empty($meta['truncated'])): ?>
          <span class="badge text-bg-warning ms-1">First <?= (int) ($meta['limit'] ?? 500) ?> only — narrow scope on overview for smaller sets</span>
        <?php endif; ?>
      </span>
    </div>
  </div>

  <?php if (empty($questions)): ?>
    <div class="alert alert-light text-center">No questions for this filter.</div>
  <?php else: ?>
    <style>
      .qb-proof-class-title {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0 0 1rem 0;
        padding-bottom: .5rem;
        border-bottom: 2px solid #dee2e6;
        color: #212529;
      }
      .qb-proof-subject-block {
        margin: 0 0 1.25rem 1rem;
        padding-left: .75rem;
        border-start: 3px solid #17a2b8;
      }
      .qb-proof-subject-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 .75rem 0;
        color: #0c5460;
      }
      .qb-proof-topic-block {
        margin: 0 0 1.5rem 0;
        padding-left: .75rem;
      }
      .qb-proof-topic-title {
        font-size: .95rem;
        font-weight: 700;
        margin: 0 0 .75rem 0;
        color: #495057;
      }
      .qb-proof-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: stretch;
      }
      .qb-proof-grid--compact {
        align-items: start;
      }
      @media (max-width: 991px) {
        .qb-proof-grid { grid-template-columns: 1fr; }
      }
      .qb-proof-item {
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        padding: 1rem;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        height: 100%;
        text-align: left;
      }
      .qb-proof-item--compact {
        padding: .4rem .65rem .45rem;
        height: auto;
        align-self: start;
      }
      .qb-proof-head--compact {
        margin-bottom: .25rem !important;
      }
      .qb-proof-tf-line {
        margin: 0;
        padding: 0;
        font-size: 1rem;
        line-height: 1.35;
        text-align: left;
      }
      .qb-proof-tf-stem {
        white-space: pre-wrap;
      }
      .qb-proof-tf-ico {
        margin-left: .35rem;
        font-size: 1.05rem;
        vertical-align: -0.06em;
      }
      .qb-proof-tf-ico--true {
        color: #28a745;
      }
      .qb-proof-tf-ico--false {
        color: #dc3545;
      }
      .qb-proof-fill-line {
        margin: 0;
        padding: 0;
        font-size: 1rem;
        line-height: 1.35;
        text-align: left;
      }
      .qb-proof-fill-stem {
        white-space: pre-wrap;
      }
      .qb-proof-fill-paren {
        font-weight: 600;
        color: #155724;
        white-space: pre-wrap;
      }
      .qb-mcq-options-line {
        display: flex;
        flex-wrap: nowrap;
        align-items: baseline;
        gap: 0 .75rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: .15rem;
        margin-bottom: .35rem;
        font-size: .95rem;
        white-space: nowrap;
      }
      .qb-mcq-options-line .qb-opt {
        flex: 0 0 auto;
      }
      .qb-mcq-sep {
        color: #adb5bd;
        user-select: none;
      }
      .qb-opt-correct {
        background-color: #b8dfc4;
        border-radius: .3rem;
        padding: .08rem .4rem;
        font-weight: 700;
        box-shadow: inset 0 0 0 1px rgba(0, 80, 40, .18);
      }
      .qb-proof-type-heading {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: 1.12rem;
        font-weight: 700;
        line-height: 1.35;
        color: #212529;
        margin: 1.35rem 0 .85rem 0;
        padding: .55rem .75rem .6rem .85rem;
        background: #f1f3f5;
        border-start: 4px solid #495057;
        border-radius: 0 .25rem .25rem 0;
        box-shadow: 0 1px 0 rgba(0, 0, 0, .04);
      }
      .qb-proof-type-heading .qb-proof-type-heading-icon {
        flex: 0 0 auto;
        font-size: 1rem;
        opacity: .55;
      }
      .qb-proof-type-heading:first-of-type {
        margin-top: .35rem;
      }
      .qb-proof-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .35rem;
      }
      .qb-proof-head-actions {
        margin-left: auto;
        display: flex;
        flex-wrap: wrap;
        gap: .25rem;
      }
      .qb-proof-print-banner {
        display: none;
      }

      /* Mobile / small screens */
      @media (max-width: 767.98px) {
        #qb-proof-print-area {
          overflow-x: hidden;
        }

        #qb-proof-print-area .card.mb-4 {
          margin-bottom: 1rem !important;
        }

        #qb-proof-print-area .card.mb-4 > .card-body {
          padding: .75rem .65rem;
        }

        #qb-proof-print-area .card.mb-3 > .card-body {
          padding: .65rem .75rem;
        }

        .qb-proof-class-title {
          font-size: 1.1rem;
          margin: 0 0 .75rem 0;
          padding-bottom: .4rem;
          line-height: 1.3;
        }

        .qb-proof-subject-block {
          margin: 0 0 1rem 0;
          padding-left: .45rem;
          border-start-width: 2px;
        }

        .qb-proof-subject-title {
          font-size: 1rem;
          margin-bottom: .6rem;
          line-height: 1.3;
        }

        .qb-proof-topic-block {
          margin-bottom: 1rem;
          padding-left: 0;
        }

        .qb-proof-topic-title {
          font-size: .9rem;
          margin-bottom: .55rem;
          line-height: 1.35;
        }

        .qb-proof-type-heading {
          font-size: .92rem;
          margin: .85rem 0 .55rem 0;
          padding: .4rem .5rem;
          line-height: 1.35;
        }

        .qb-proof-grid {
          gap: .65rem;
        }

        .qb-proof-item {
          padding: .7rem .65rem;
          min-width: 0;
          overflow-wrap: anywhere;
          word-break: break-word;
        }

        .qb-proof-head {
          width: 100%;
          align-items: flex-start;
        }

        .qb-proof-head-actions {
          margin-left: 0;
          width: 100%;
          justify-content: flex-start;
          margin-top: .35rem;
        }

        .qb-proof-head-actions .btn {
          min-width: 2.5rem;
          min-height: 2.5rem;
          padding: .4rem .55rem;
        }

        .qb-proof-q {
          font-size: .95rem !important;
          line-height: 1.45 !important;
        }

        .qb-proof-ans {
          padding: .65rem !important;
          margin-top: .5rem !important;
          font-size: .9rem !important;
        }

        .qb-mcq-options-line {
          flex-wrap: wrap;
          white-space: normal;
          overflow-x: visible;
          gap: .35rem 0;
          padding-bottom: 0;
        }

        .qb-mcq-options-line .qb-opt {
          flex: 1 1 100%;
          width: 100%;
          padding: .3rem .4rem;
          border-radius: .25rem;
          background: #f8f9fa;
          line-height: 1.35;
        }

        .qb-mcq-sep {
          display: none;
        }

        .qb-proof-tf-line,
        .qb-proof-fill-line {
          font-size: .95rem;
          line-height: 1.45;
        }

        .qb-proof-item img.img-fluid {
          max-width: 100% !important;
          width: 100%;
          max-height: 200px !important;
          height: auto !important;
          object-fit: contain;
        }

        #qbProofEditModal .modal-dialog {
          max-width: none;
          width: auto;
          margin: .5rem;
        }

        #qbProofEditModal .modal-body {
          padding: .75rem;
        }

        #qbProofEditModal .row > [class*="col-"] {
          flex: 0 0 100%;
          max-width: 100%;
        }
      }

      @media (max-width: 575.98px) {
        .qb-proof-class-title .fas,
        .qb-proof-subject-title .fas,
        .qb-proof-topic-title .fas {
          display: none;
        }

        .qb-proof-class-title,
        .qb-proof-subject-title,
        .qb-proof-topic-title {
          padding-left: 0;
        }
      }

      @media print {
        .main-sidebar,
        .main-header,
        .main-footer,
        .control-sidebar,
        .no-print,
        .qb-proof-difficulty,
        .qb-proof-btn-edit,
        .qb-proof-btn-delete,
        .qb-proof-head-actions {
          display: none !important;
        }

        html,
        body {
          background: #fff !important;
          margin: 0 !important;
          padding: 0 !important;
          width: 100% !important;
          min-height: 0 !important;
        }

        body {
          font-size: 12pt;
          line-height: 1.35;
          color: #000 !important;
          -webkit-print-color-adjust: exact !important;
          print-color-adjust: exact !important;
        }

        .wrapper,
        .content-wrapper,
        .content-header,
        .content,
        .content > .container-fluid,
        .container-fluid {
          background: #fff !important;
          margin: 0 !important;
          margin-left: 0 !important;
          padding: 0 !important;
          width: 100% !important;
          max-width: 100% !important;
          min-height: 0 !important;
          box-shadow: none !important;
        }

        @page {
          size: A4 portrait;
          margin: 18mm 14mm 20mm 14mm;
        }

        #qb-proof-print-area {
          width: 100% !important;
          max-width: 100% !important;
          margin: 0 !important;
          padding: 0 !important;
          box-sizing: border-box !important;
        }

        .qb-proof-print-banner {
          display: block !important;
          margin: 0 0 8mm 0;
          padding: 0 0 3mm 0;
          border-bottom: 1px solid #212529;
        }

        .qb-proof-print-banner-title {
          font-size: 16pt;
          font-weight: 700;
          margin: 0 0 2mm 0;
          line-height: 1.25;
        }

        .qb-proof-print-banner-scope {
          margin: 0 0 1.5mm 0;
          font-size: 13pt;
          line-height: 1.3;
        }

        .qb-proof-print-banner-meta {
          font-size: 11pt !important;
          line-height: 1.3;
          color: #333 !important;
        }

        .card.mb-4 {
          border: none !important;
          box-shadow: none !important;
          margin: 0 !important;
        }

        .card.mb-4 > .card-body {
          padding: 0 !important;
        }

        .qb-proof-class-title {
          font-size: 14pt;
          margin: 5mm 0 2mm 0 !important;
          padding-bottom: 1.5mm;
          border-bottom-width: 1px;
          line-height: 1.25;
          break-after: avoid;
          page-break-after: avoid;
        }

        .qb-proof-subject-block {
          margin: 0 0 3mm 4mm !important;
          padding-left: 3mm;
          border-start-width: 2px;
        }

        .qb-proof-subject-title {
          font-size: 12pt;
          margin: 0 0 .2rem 0 !important;
          line-height: 1.2;
        }

        .qb-proof-topic-block {
          margin: 0 0 4mm 0 !important;
          padding-left: 3mm;
        }

        .qb-proof-topic-title {
          font-size: 11.5pt;
          margin: 0 0 .2rem 0 !important;
          line-height: 1.2;
        }

        .qb-proof-class-title .fas,
        .qb-proof-subject-title .fas,
        .qb-proof-topic-title .fas,
        .qb-proof-type-heading-icon {
          display: none !important;
        }

        .qb-proof-type-heading {
          font-size: 11.5pt;
          margin: .35rem 0 .2rem 0 !important;
          padding: .15rem .4rem .15rem .45rem;
          border-start-width: 3px;
          box-shadow: none;
          line-height: 1.2;
          break-after: avoid;
          page-break-after: avoid;
        }

        .qb-proof-type-heading:first-of-type {
          margin-top: .15rem !important;
        }

        .qb-proof-grid {
          display: grid !important;
          grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
          gap: 2mm 4mm !important;
          align-items: start !important;
        }

        .qb-proof-item {
          break-inside: avoid;
          page-break-inside: avoid;
          height: auto !important;
          align-self: start !important;
          padding: 1.5mm 0 2mm 0 !important;
          margin: 0 0 1mm 0 !important;
          border: none !important;
          border-bottom: 0.25pt solid #ccc !important;
          border-radius: 0 !important;
          box-shadow: none !important;
          background: transparent !important;
        }

        .qb-proof-item--compact {
          padding: .08rem .2rem !important;
        }

        .qb-proof-item:not(.qb-proof-item--compact) .qb-proof-head {
          display: inline;
          margin: 0 !important;
        }

        .qb-proof-item:not(.qb-proof-item--compact) .qb-proof-head .badge {
          margin-right: .15rem;
        }

        .qb-proof-item:not(.qb-proof-item--compact) .qb-proof-q {
          display: inline;
        }

        .qb-proof-item:not(.qb-proof-item--compact) .qb-proof-q::after {
          content: '';
          display: block;
          margin-bottom: .05rem;
        }

        .qb-proof-head,
        .qb-proof-head--compact {
          margin: 0 0 .05rem 0 !important;
          font-size: 9.5pt !important;
          line-height: 1.15;
          gap: .2rem;
        }

        .qb-proof-head .badge {
          font-size: 9pt;
          padding: .1rem .25rem;
          line-height: 1.1;
        }

        .qb-proof-q {
          font-size: 11pt !important;
          line-height: 1.25 !important;
          margin: 0 !important;
          padding: 0 !important;
        }

        .qb-proof-ans {
          margin: .08rem 0 0 0 !important;
          padding: 0 !important;
          background: transparent !important;
          font-size: 10.5pt !important;
          line-height: 1.2 !important;
        }

        .qb-mcq-options-line {
          flex-wrap: wrap;
          white-space: normal;
          overflow: visible;
          margin: 0 !important;
          padding: 0 !important;
          gap: 0 .35rem;
          font-size: 10.5pt;
          line-height: 1.2;
        }

        .qb-opt-correct {
          padding: .02rem .2rem;
        }

        .qb-proof-tf-line,
        .qb-proof-fill-line {
          font-size: 11pt;
          line-height: 1.25;
        }

        .qb-proof-tf-ico {
          font-size: 11pt;
          margin-left: .2rem;
        }

        .qb-proof-item .mb-1,
        .qb-proof-item .mb-2 {
          margin-bottom: .1rem !important;
        }

        /* Override screen inline font sizes when printing */
        .qb-proof-head[style],
        .qb-proof-q[style],
        .qb-proof-ans[style] {
          font-size: inherit !important;
        }
      }
    </style>

    <?php $qNum = 0; ?>
    <?php foreach ($proofHierarchy as $classRow): ?>
      <div class="card mb-4">
        <div class="card-body">
          <h2 class="qb-proof-class-title">
            <i class="fas fa-layer-group text-muted me-2"></i><?= esc($classRow['class_name']) ?>
          </h2>

          <?php foreach ($classRow['subjects'] as $subRow): ?>
            <div class="qb-proof-subject-block">
              <h3 class="qb-proof-subject-title">
                <i class="fas fa-book text-muted me-2"></i><?= esc($subRow['subject_name']) ?>
              </h3>

              <?php foreach ($subRow['topics'] as $topRow): ?>
                <div class="qb-proof-topic-block">
                  <h4 class="qb-proof-topic-title">
                    <i class="fas fa-tag text-muted me-2"></i><?= esc($topRow['topic_name']) ?>
                  </h4>

                  <?php foreach ($topRow['type_groups'] ?? [] as $tg): ?>
                    <?php
                      $gType = (string) ($tg['type'] ?? 'other');
                      $gLabel = $qbProofTypeLabels[$gType] ?? ucfirst($gType);
                      ?>
                    <h5 class="qb-proof-type-heading">
                      <i class="fas fa-list-alt qb-proof-type-heading-icon" aria-hidden="true"></i>
                      <span><?= esc($gLabel) ?></span>
                    </h5>

                    <?php
                      $compactGroup = in_array($gType, ['tf', 'fill', 'short'], true);
                      ?>
                    <div class="qb-proof-grid<?= $compactGroup ? ' qb-proof-grid--compact' : '' ?>">
                      <?php foreach ($tg['questions'] as $q): ?>
                        <?php
                        ++$qNum;
                          $type = strtolower((string) ($q['question_type'] ?? ''));
                          $compactType = in_array($type, ['tf', 'fill', 'short'], true);
                          ?>
                        <div class="qb-proof-item<?= $compactType ? ' qb-proof-item--compact' : '' ?>" data-qid="<?= (int) ($q['id'] ?? 0) ?>">
                          <div class="qb-proof-head mb-2<?= $compactType ? ' qb-proof-head--compact' : '' ?>" style="font-size:.85rem;color:#6c757d;">
                            <span class="badge text-bg-secondary">#<?= $qNum ?></span>
                            <span class="badge text-bg-light border qb-proof-difficulty no-print"><?= esc((string) ($q['difficulty'] ?? 'normal')) ?></span>
                            <div class="qb-proof-head-actions no-print">
                              <button type="button" class="btn btn-outline-primary btn-sm qb-proof-btn-edit" data-qid="<?= (int) ($q['id'] ?? 0) ?>" title="Edit question">
                                <i class="fas fa-edit"></i>
                              </button>
                              <button type="button" class="btn btn-outline-danger btn-sm qb-proof-btn-delete" data-qid="<?= (int) ($q['id'] ?? 0) ?>" title="Delete question">
                                <i class="fas fa-trash"></i>
                              </button>
                            </div>
                          </div>

                          <?php if (($q['question_media'] ?? 'text') === 'image' && !empty($q['question_image_public_url'])): ?>
                            <div class="<?= $compactType ? 'mb-1' : 'mb-2' ?>">
                              <img src="<?= esc($q['question_image_public_url'], 'attr') ?>" alt="" class="img-fluid rounded border qb-proof-q-img" style="max-height:220px;">
                            </div>
                          <?php endif; ?>

                          <?php if ($type === 'tf'): ?>
                            <?php
                              $tfAns = strtolower(trim((string) ($q['answer_text'] ?? '')));
                              $tfLine = (string) ($q['question'] ?? '');
                              $tfIsTrue  = in_array($tfAns, ['true', 't', '1', 'yes'], true);
                              $tfIsFalse = in_array($tfAns, ['false', 'f', '0', 'no'], true);
                              ?>
                            <div class="qb-proof-tf-line">
                              <?php if ($tfLine !== ''): ?>
                                <span class="qb-proof-tf-stem"><?= esc($tfLine) ?></span>
                              <?php else: ?>
                                <span class="text-muted"><em>Image / no text stem.</em></span>
                              <?php endif; ?>
                              <?php if ($tfIsTrue): ?>
                                <i class="fas fa-check qb-proof-tf-ico qb-proof-tf-ico--true" title="Answer: True" aria-label="Answer: True"></i>
                              <?php elseif ($tfIsFalse): ?>
                                <i class="fas fa-times qb-proof-tf-ico qb-proof-tf-ico--false" title="Answer: False" aria-label="Answer: False"></i>
                              <?php else: ?>
                                <span class="text-muted small" title="<?= esc($tfAns !== '' ? 'Answer: ' . $tfAns : 'No answer') ?>">?</span>
                              <?php endif; ?>
                            </div>

                          <?php elseif (in_array($type, ['fill', 'short'], true)): ?>
                            <?php
                              $fillAns = trim((string) ($q['answer_text'] ?? ''));
                              $fillQ   = (string) ($q['question'] ?? '');
                              ?>
                            <div class="qb-proof-fill-line">
                              <?php if ($fillQ !== ''): ?>
                                <span class="qb-proof-fill-stem"><?= esc($fillQ) ?></span><?php if ($fillAns !== ''): ?><span class="qb-proof-fill-paren"> (<?= esc($fillAns) ?>)</span><?php endif; ?>
                              <?php else: ?>
                                <span class="text-muted"><em>Image / no text stem.</em></span><?php if ($fillAns !== ''): ?><span class="qb-proof-fill-paren"> (<?= esc($fillAns) ?>)</span><?php endif; ?>
                              <?php endif; ?>
                            </div>

                          <?php elseif ($type === 'descriptive'): ?>
                            <?php if (!empty($q['question'])): ?>
                              <div class="qb-proof-q" style="font-size:1rem;line-height:1.5;white-space:pre-wrap;"><?= esc((string) $q['question']) ?></div>
                            <?php elseif (($q['question_media'] ?? 'text') === 'image'): ?>
                              <p class="text-muted mb-2"><em>Image question — see preview above.</em></p>
                            <?php endif; ?>
                            <?php $descAns = trim((string) ($q['answer_text'] ?? '')); ?>
                            <?php if ($descAns !== ''): ?>
                              <div class="qb-proof-ans mt-2 p-3 rounded" style="background:#f8f9fa;font-size:.95rem;">
                                <div class="text-muted small mb-1"><strong>Model answer (guideline)</strong> — for teacher reference; students compare their own work.</div>
                                <div style="white-space:pre-wrap;line-height:1.55;"><?= esc($descAns) ?></div>
                              </div>
                            <?php endif; ?>

                          <?php else: ?>
                            <?php if (!empty($q['question'])): ?>
                              <div class="qb-proof-q" style="font-size:1rem;line-height:1.5;white-space:pre-wrap;"><?= esc((string) $q['question']) ?></div>
                            <?php elseif (($q['question_media'] ?? 'text') === 'image'): ?>
                              <p class="text-muted mb-0"><em>Image question — see preview above.</em></p>
                            <?php endif; ?>

                            <div class="qb-proof-ans mt-3 p-3 rounded" style="background:#f8f9fa;font-size:.95rem;">
                              <?php if ($type === 'mcq'): ?>
                                <?php
                                  $correctLetter = strtoupper(trim((string) ($q['correct_option'] ?? 'A')));
                                  if (!in_array($correctLetter, ['A', 'B', 'C', 'D'], true)) {
                                      $correctLetter = 'A';
                                  }
                                  ?>
                                <div class="qb-mcq-options-line">
                                  <span class="qb-opt<?= $correctLetter === 'A' ? ' qb-opt-correct' : '' ?>"><strong>A</strong> <?= esc((string) ($q['option_a'] ?? '')) ?></span>
                                  <span class="qb-mcq-sep">·</span>
                                  <span class="qb-opt<?= $correctLetter === 'B' ? ' qb-opt-correct' : '' ?>"><strong>B</strong> <?= esc((string) ($q['option_b'] ?? '')) ?></span>
                                  <span class="qb-mcq-sep">·</span>
                                  <span class="qb-opt<?= $correctLetter === 'C' ? ' qb-opt-correct' : '' ?>"><strong>C</strong> <?= esc((string) ($q['option_c'] ?? '')) ?></span>
                                  <span class="qb-mcq-sep">·</span>
                                  <span class="qb-opt<?= $correctLetter === 'D' ? ' qb-opt-correct' : '' ?>"><strong>D</strong> <?= esc((string) ($q['option_d'] ?? '')) ?></span>
                                </div>

                              <?php elseif ($type === 'mcq_multi'): ?>
                                <?php
                                  $correctSet = [];
                                  foreach ($q['correct_options'] ?? [] as $co) {
                                      $correctSet[strtoupper(trim((string) $co))] = true;
                                  }
                                  ?>
                                <div class="qb-mcq-options-line">
                                  <span class="qb-opt<?= isset($correctSet['A']) ? ' qb-opt-correct' : '' ?>"><strong>A</strong> <?= esc((string) ($q['option_a'] ?? '')) ?></span>
                                  <span class="qb-mcq-sep">·</span>
                                  <span class="qb-opt<?= isset($correctSet['B']) ? ' qb-opt-correct' : '' ?>"><strong>B</strong> <?= esc((string) ($q['option_b'] ?? '')) ?></span>
                                  <span class="qb-mcq-sep">·</span>
                                  <span class="qb-opt<?= isset($correctSet['C']) ? ' qb-opt-correct' : '' ?>"><strong>C</strong> <?= esc((string) ($q['option_c'] ?? '')) ?></span>
                                  <span class="qb-mcq-sep">·</span>
                                  <span class="qb-opt<?= isset($correctSet['D']) ? ' qb-opt-correct' : '' ?>"><strong>D</strong> <?= esc((string) ($q['option_d'] ?? '')) ?></span>
                                </div>

                              <?php elseif ($type === 'match'): ?>
                                <?php $pairs = $q['match_pairs'] ?? []; ?>
                                <?php if (!empty($pairs) && is_array($pairs)): ?>
                                  <?php foreach ($pairs as $p): ?>
                                    <div><?= esc((string) ($p['left'] ?? '')) ?> → <?= esc((string) ($p['right'] ?? '')) ?></div>
                                  <?php endforeach; ?>
                                <?php else: ?>
                                  <span class="text-muted">(no pairs)</span>
                                <?php endif; ?>

                              <?php else: ?>
                                <div><strong>Answer:</strong> <?= esc((string) ($q['answer_text'] ?? '')) ?></div>
                              <?php endif; ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<?php if (!empty($questions)): ?>
  <?= view('admin/partials/question_bank_proof_edit_modal') ?>
  <script>
    window.QB_PROOF_EDIT = {
      questionUrl: <?= json_encode(site_url('admin/question-bank/question')) ?>,
      saveUrl: <?= json_encode(site_url('admin/question-bank/save-ajax')) ?>,
      deleteUrl: <?= json_encode(site_url('admin/question-bank/delete')) ?>,
      topicsUrl: <?= json_encode(site_url('admin/question-bank/topics')) ?>,
      csrfName: <?= json_encode(csrf_token()) ?>,
      csrfHash: <?= json_encode(csrf_hash()) ?>
    };
  </script>
  <script src="<?= base_url('assets/js/question_bank_proof_edit.js') ?>?v=3"></script>
<?php endif; ?>


<script>
(function() {
    // Print standard mode (with solutions)
    const printStandardBtn = document.getElementById('printStandardBtn');
    // Print questions only mode (without solutions)
    const printQuestionsOnlyBtn = document.getElementById('printQuestionsOnlyBtn');

    if (printStandardBtn) {
        printStandardBtn.addEventListener('click', function() {
            // Remove questions-only class if present
            document.body.classList.remove('print-questions-only');
            window.print();
        });
    }

    if (printQuestionsOnlyBtn) {
        printQuestionsOnlyBtn.addEventListener('click', function() {
            // Add class to hide answers/solutions
            document.body.classList.add('print-questions-only');
            window.print();
            // Remove class after print dialog closes (optional)
            setTimeout(function() {
                document.body.classList.remove('print-questions-only');
            }, 1000);
        });
    }
})();
</script>
<?= $this->endSection() ?>
