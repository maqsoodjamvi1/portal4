<?php
/**
 * Filter card — wraps report_filter_bar or custom filter body.
 *
 * @var string       $title
 * @var string       $formId
 * @var string       $method
 * @var array        $fields      Same schema as report_filter_bar
 * @var array        $actions
 * @var string|null  $bodyHtml    If set, renders instead of auto fields
 * @var string       $cardClass
 */
if (!empty($bodyHtml)) {
    $title = $title ?? 'Filters';
    $cardClass = $cardClass ?? 'card sms-filter-card report-filter-card no-print';
    ?>
    <div class="<?= esc($cardClass, 'attr') ?>">
      <div class="card-header"><h3 class="card-title"><?= esc($title) ?></h3></div>
      <div class="card-body"><?= $bodyHtml ?></div>
    </div>
    <?php
    return;
}

echo view('components/report_filter_bar', get_defined_vars());
