<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Vocabulary Print</title>

<style>
@page {
  size: A4 portrait;
  margin: 10mm;
}

body {
  font-family: "Times New Roman", serif;
  color: #000;
  margin: 0;
}

/* HEADER */
.header {
  text-align: center;
  margin-bottom: 10px;
  border-bottom: 1px solid #000;
  padding-bottom: 5px;
}

.header h2 {
  margin: 0;
  font-size: 18pt;
}

.header p {
  margin: 2px 0;
  font-size: 11pt;
}

/* TOPIC */
.topic {
  margin-top: 10px;
  page-break-inside: avoid;
}

.topic-title {
  font-weight: bold;
  font-size: 13pt;
  border-bottom: 1px solid #000;
  margin-bottom: 5px;
}

/* GRID (PRINT SAFE) */
.grid {
  font-size: 0; /* remove spacing */
}

.card {
  display: inline-block;
  width: 32%;
  margin: 0.5%;
  vertical-align: top;
  border: 1px solid #000;
  padding: 5px;

  page-break-inside: avoid;
  break-inside: avoid;
}

/* WORD HEADER */
.word {
  font-weight: bold;
  font-size: 12pt;
  border-bottom: 1px solid #000;
  margin-bottom: 4px;
}

/* FIELDS */
.field {
  font-size: 10pt;
  margin-bottom: 3px;
}

.label {
  font-weight: bold;
}

/* FORCE BLACK TEXT */
* {
  color: #000 !important;
  background: #fff !important;
}
</style>

</head>
<body>

<div class="header">
  <h2>Vocabulary Report</h2>
  <p><strong>Class:</strong> <?= esc($class_name) ?> |
     <strong>Subject:</strong> <?= esc($subject_name) ?></p>
  <p><strong>Total Words:</strong> <?= count($items) ?></p>
</div>

<?php
$grouped = [];
foreach ($items as $row) {
  $grouped[$row->topic_name][] = $row;
}
?>

<?php foreach ($grouped as $topic => $words): ?>
  <div class="topic">
    <div class="topic-title"><?= esc($topic) ?></div>

    <div class="grid">
      <?php foreach ($words as $i => $w): ?>
        <div class="card">

          <div class="word">
            <?= ($i+1) ?>. <?= esc($w->word) ?>
            (<?= esc($w->part_of_speech) ?>)
          </div>

          <?php if($w->meaning_en): ?>
          <div class="field"><span class="label">EN:</span> <?= esc($w->meaning_en) ?></div>
          <?php endif; ?>

          <?php if($w->meaning_ur): ?>
          <div class="field"><span class="label">UR:</span> <?= esc($w->meaning_ur) ?></div>
          <?php endif; ?>

          <?php if($w->example_sentence): ?>
          <div class="field"><span class="label">Ex:</span> <?= esc($w->example_sentence) ?></div>
          <?php endif; ?>

          <?php if($w->synonyms): ?>
          <div class="field"><span class="label">Syn:</span> <?= esc($w->synonyms) ?></div>
          <?php endif; ?>

          <?php if($w->antonyms): ?>
          <div class="field"><span class="label">Ant:</span> <?= esc($w->antonyms) ?></div>
          <?php endif; ?>

          <?php if($w->syllables): ?>
          <div class="field"><span class="label">Syll:</span> <?= esc($w->syllables) ?></div>
          <?php endif; ?>

        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<script>
window.onload = function() {
  window.print();
}
</script>

</body>
</html>