<?php
/** Board prep portal targeting — grade, board, audience */
$grades   = $boardPrepGrades ?? [];
$boards   = $boardPublishers ?? [];
$quiz     = $quiz ?? null;
$supported = ! empty($boardPrepAudienceSupported);
$audience = old('audience', $quiz->audience ?? 'board_prep');
$grade    = old('prep_grade_level', $quiz->prep_grade_level ?? '');
$boardId  = (int) old('prep_board_publisher_id', (int) ($quiz->prep_board_publisher_id ?? 0));
?>
<?php if ($supported || $grades !== []): ?>
<div class="card mb-3 border-info">
  <div class="card-header bg-light py-2">
    <h3 class="card-title mb-0"><i class="fas fa-book-reader text-info me-1"></i> Board prep portal</h3>
  </div>
  <div class="card-body pb-2">
    <p class="text-muted small mb-3">These settings control which students see this quiz on the public board-exam prep portal.</p>
    <div class="row">
      <div class="form-group col-md-4">
        <label for="prep_grade_level">Grade level <span class="text-danger">*</span></label>
        <select class="form-control" id="prep_grade_level" name="prep_grade_level" required>
          <option value="">-- Select grade --</option>
          <?php foreach ($grades as $key => $label): ?>
            <option value="<?= esc($key) ?>" <?= (string) $grade === (string) $key ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group col-md-4">
        <label for="prep_board_publisher_id">Board / publisher <span class="text-danger">*</span></label>
        <select class="form-control" id="prep_board_publisher_id" name="prep_board_publisher_id" required>
          <option value="">-- Select board --</option>
          <?php foreach ($boards as $bp):
            $id = (int) ($bp['id'] ?? 0);
            $code = trim((string) ($bp['short_code'] ?? ''));
            $label = $code !== '' ? $code . ' — ' . ($bp['name'] ?? '') : ($bp['name'] ?? ('Board ' . $id));
          ?>
            <option value="<?= $id ?>" <?= $boardId === $id ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if ($supported): ?>
      <div class="form-group col-md-4">
        <label for="audience">Audience</label>
        <select class="form-control" id="audience" name="audience">
          <option value="board_prep" <?= $audience === 'board_prep' ? 'selected' : '' ?>>Board prep portal only</option>
          <option value="both" <?= $audience === 'both' ? 'selected' : '' ?>>Prep portal + school students</option>
          <option value="school" <?= $audience === 'school' ? 'selected' : '' ?>>School students only</option>
        </select>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
