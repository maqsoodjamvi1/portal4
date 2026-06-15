<?php

/** @var array<string, mixed> $config */

$h = $config['header'] ?? [];

$hasBrand = !empty($h['school_logo_url']) || !empty($h['school_name']) || !empty($h['school_campus']);

$hasMetaPrimary = !empty($h['subject']) || !empty($h['class_label']) || !empty($h['total_marks']);

$hasMetaSecondary = !empty($h['exam_date']) || !empty($h['exam_time']) || !empty($h['duration']);

?>

<div class="qp-paper-header">

  <?php if ($hasBrand): ?>

    <div class="qp-header-top">

      <div class="qp-logo-cell">

        <?php if (!empty($h['school_logo_url'])): ?>

          <img src="<?= esc($h['school_logo_url'], 'attr') ?>" alt="" class="qp-school-logo">

        <?php endif; ?>

      </div>

      <div class="qp-school-heading-row">

        <?php if (!empty($h['school_name'])): ?>

          <h1 class="qp-school"><?= esc($h['school_name']) ?></h1>

        <?php endif; ?>

        <?php if (!empty($h['school_campus'])): ?>

          <div class="qp-school-sub"><?= esc($h['school_campus']) ?></div>

        <?php endif; ?>

      </div>

      <div class="qp-logo-spacer" aria-hidden="true"></div>

    </div>

  <?php endif; ?>

  <?php if (!empty($h['title'])): ?>

    <div class="qp-title"><?= esc($h['title']) ?></div>

  <?php endif; ?>

  <?php if ($hasMetaPrimary): ?>

    <div class="qp-meta-primary">

      <?php if (!empty($h['subject'])): ?>

        <span class="qp-meta-item"><strong>Subject:</strong> <?= esc($h['subject']) ?></span>

      <?php endif; ?>

      <?php if (!empty($h['class_label'])): ?>

        <span class="qp-meta-item"><strong>Class:</strong> <?= esc($h['class_label']) ?></span>

      <?php endif; ?>

      <?php if (!empty($h['total_marks'])): ?>

        <span class="qp-meta-item"><strong>Marks:</strong> <?= esc($h['total_marks']) ?></span>

      <?php endif; ?>

    </div>

  <?php endif; ?>

  <?php if ($hasMetaSecondary): ?>

    <div class="qp-meta-secondary">

      <?php if (!empty($h['exam_date'])): ?>

        <span class="qp-meta-item"><strong>Date:</strong> <?= esc($h['exam_date']) ?></span>

      <?php endif; ?>

      <?php if (!empty($h['exam_time'])): ?>

        <span class="qp-meta-item"><strong>Time:</strong> <?= esc($h['exam_time']) ?></span>

      <?php endif; ?>

      <?php if (!empty($h['duration'])): ?>

        <span class="qp-meta-item"><strong>Duration:</strong> <?= esc($h['duration']) ?></span>

      <?php endif; ?>

    </div>

  <?php endif; ?>

  <?php if (!empty($h['show_name']) || !empty($h['show_roll']) || !empty($h['show_section'])): ?>

    <div class="qp-student-fields">

      <?php if (!empty($h['show_name'])): ?><span>Name: _________________________</span><?php endif; ?>

      <?php if (!empty($h['show_roll'])): ?><span>Roll No: __________</span><?php endif; ?>

      <?php if (!empty($h['show_section'])): ?><span>Section: __________</span><?php endif; ?>

    </div>

  <?php endif; ?>

  <?php if (!empty($h['instructions'])): ?>

    <div class="qp-instructions"><strong>Instructions:</strong> <?= nl2br(esc($h['instructions'])) ?></div>

  <?php endif; ?>

</div>
