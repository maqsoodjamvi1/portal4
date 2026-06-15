<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Math Operations Worksheets',
    'icon' => 'fas fa-calculator',
    'subtitle' => 'Configure digit length & number range — applies to both operands (integers or decimals).',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quizzes', 'url' => base_url('admin/quizzes')],
        ['label' => 'Math Worksheet', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('warning')): ?>
      <div class="alert alert-warning"><?= esc(session()->getFlashdata('warning')) ?></div>
    <?php endif; ?>

    <?php $errors = session('errors') ?? []; ?>
    <?php if (! empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if (empty($tablesReady)): ?>
      <div class="alert alert-warning">
        <i class="fas fa-database"></i> Run database migration <code>2026-06-10-120000_CreateMathWorksheetTables</code> to enable save &amp; library features.
      </div>
    <?php endif; ?>

    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-calculator me-1"></i> Worksheet settings</h3>
        <div>
          <a href="<?= site_url('admin/math-worksheet/library') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-folder-open"></i> Library</a>
        </div>
      </div>

      <form action="<?= site_url('admin/math-worksheet/generate') ?>" method="post" target="_blank" id="mathWorksheetForm">
        <?= csrf_field() ?>

        <div class="card-body">
          <div class="border rounded p-3 mb-3 bg-light">
            <h6 class="fw-bold mb-3"><i class="fas fa-sort-numeric-up me-1"></i> Number settings</h6>

            <div class="row">
              <div class="col-md-3 form-group">
                <label for="number_type">Number type <span class="text-danger">*</span></label>
                <select name="number_type" id="number_type" class="form-control" required>
                  <option value="integer" <?= old('number_type', 'integer') === 'integer' ? 'selected' : '' ?>>Whole numbers</option>
                  <option value="decimal" <?= old('number_type') === 'decimal' ? 'selected' : '' ?>>Decimal numbers</option>
                </select>
              </div>
            </div>

            <?php
              $oldOperandDigits = old('operand_digits', old('operand_a_digits', '2'));
              $oldOperandMin    = old('operand_min', old('operand_a_min', '10'));
              $oldOperandMax    = old('operand_max', old('operand_a_max', '99'));
              $oldWholeDigits   = old('operand_whole_digits', old('operand_a_whole_digits', '2'));
              $oldDecimalDigits = old('operand_decimal_digits', old('operand_a_decimal_digits', '2'));
              $oldDecMin        = old('operand_min', old('operand_a_min', '1.00'));
              $oldDecMax        = old('operand_max', old('operand_a_max', '99.99'));
            ?>

            <div id="integerNumberBlock">
              <p class="mb-2 fw-bold text-secondary">Number range (both operands)</p>
              <div class="row">
                <div class="col-md-4 form-group">
                  <label for="operand_digits">Digits</label>
                  <select name="operand_digits" id="operand_digits" class="form-control operand-digits">
                    <?php foreach ($digitOptions as $val => $label): ?>
                      <option value="<?= (int) $val ?>" <?= (string) $oldOperandDigits === (string) $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4 form-group">
                  <label for="operand_min">Min</label>
                  <input type="number" name="operand_min" id="operand_min" class="form-control" step="1" value="<?= esc($oldOperandMin) ?>">
                </div>
                <div class="col-md-4 form-group">
                  <label for="operand_max">Max</label>
                  <input type="number" name="operand_max" id="operand_max" class="form-control" step="1" value="<?= esc($oldOperandMax) ?>">
                </div>
              </div>
            </div>

            <div id="decimalNumberBlock" class="d-none">
              <p class="mb-2 fw-bold text-secondary">Number range (both operands)</p>
              <div class="row">
                <div class="col-md-3 form-group">
                  <label for="operand_whole_digits">Before decimal</label>
                  <select name="operand_whole_digits" id="operand_whole_digits" class="form-control decimal-whole">
                    <?php for ($d = 1; $d <= 3; $d++): ?>
                      <option value="<?= $d ?>" <?= (string) $oldWholeDigits === (string) $d ? 'selected' : '' ?>><?= $d ?> digit<?= $d > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label for="operand_decimal_digits">After decimal</label>
                  <select name="operand_decimal_digits" id="operand_decimal_digits" class="form-control decimal-frac">
                    <?php for ($d = 0; $d <= 4; $d++): ?>
                      <option value="<?= $d ?>" <?= (string) $oldDecimalDigits === (string) $d ? 'selected' : '' ?>><?= $d ?> place<?= $d !== 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label for="operand_min_dec">Min</label>
                  <input type="number" name="operand_min" id="operand_min_dec" class="form-control decimal-min" step="0.01" value="<?= esc($oldDecMin) ?>">
                </div>
                <div class="col-md-3 form-group">
                  <label for="operand_max_dec">Max</label>
                  <input type="number" name="operand_max" id="operand_max_dec" class="form-control decimal-max" step="0.01" value="<?= esc($oldDecMax) ?>">
                </div>
              </div>
              <small class="text-muted">Same range applies to both numbers in each problem.</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 form-group">
              <label for="layout">Layout <span class="text-danger">*</span></label>
              <select name="layout" id="layout" class="form-control" required>
                <?php foreach ($layoutOptions as $val => $label): ?>
                  <option value="<?= esc($val) ?>" <?= old('layout', 'horizontal') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted" id="layoutHint">Best for 20–40 problems per A4 page.</small>
            </div>

            <div class="col-md-3 form-group">
              <label for="missing_style">Missing field <span class="text-danger">*</span></label>
              <select name="missing_style" id="missing_style" class="form-control" required>
                <?php foreach ($missingStyleOptions as $val => $label): ?>
                  <option value="<?= esc($val) ?>" <?= old('missing_style', 'result') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3 form-group">
              <label for="problem_count">Total problems</label>
              <input type="number" name="problem_count" id="problem_count" class="form-control" min="10" max="100" value="<?= esc(old('problem_count', '40')) ?>" required>
            </div>

            <div class="col-md-3 form-group">
              <label for="per_page">Problems per A4 page</label>
              <select name="per_page" id="per_page" class="form-control" required>
                <?php foreach ($perPageOptions as $val => $label): ?>
                  <option value="<?= (int) $val ?>" <?= (string) old('per_page', '20') === (string) $val ? 'selected' : '' ?>><?= (int) $label ?> per page</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Operations <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap" style="gap:1rem;">
              <?php
                $oldOps = old('operations') ?? ['+', '-'];
                if (! is_array($oldOps)) { $oldOps = ['+', '-']; }
              ?>
              <?php foreach ($operationOptions as $val => $label): ?>
                <div class="form-check form-check">
                  <input type="checkbox" class="form-check-input" name="operations[]"
                         id="op_<?= esc(md5($val)) ?>" value="<?= esc($val) ?>"
                         <?= in_array($val, $oldOps, true) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="op_<?= esc(md5($val)) ?>"><?= esc($label) ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 form-group">
              <label for="operation_mix">Operation mix</label>
              <select name="operation_mix" id="operation_mix" class="form-control">
                <option value="mixed" <?= old('operation_mix', 'mixed') === 'mixed' ? 'selected' : '' ?>>Mixed on one sheet</option>
                <option value="separate" <?= old('operation_mix') === 'separate' ? 'selected' : '' ?>>Grouped by operation</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label for="multiplication_mode">Multiplication</label>
              <select name="multiplication_mode" id="multiplication_mode" class="form-control">
                <option value="random" <?= old('multiplication_mode', 'random') === 'random' ? 'selected' : '' ?>>Use number ranges</option>
                <option value="times_table" <?= old('multiplication_mode') === 'times_table' ? 'selected' : '' ?>>Times-table (2–12)</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label for="division_mode">Division</label>
              <select name="division_mode" id="division_mode" class="form-control">
                <option value="whole" <?= old('division_mode', 'whole') === 'whole' ? 'selected' : '' ?>>Exact quotient</option>
                <option value="remainder" <?= old('division_mode') === 'remainder' ? 'selected' : '' ?>>With remainder (whole numbers)</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label for="worksheet_title">Worksheet title</label>
              <input type="text" name="worksheet_title" id="worksheet_title" class="form-control" maxlength="80" placeholder="Math Operations Worksheet" value="<?= esc(old('worksheet_title', '')) ?>">
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 form-group">
              <label for="bulk_cls_sec_id">Bulk: one per student</label>
              <select name="bulk_cls_sec_id" id="bulk_cls_sec_id" class="form-control">
                <option value="">— Off —</option>
                <?php foreach ($classSections as $cs): ?>
                  <option value="<?= (int) $cs['cls_sec_id'] ?>"><?= esc($cs['class_name'] . ' - ' . $cs['section_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="d-flex flex-wrap" style="gap:1.5rem;">
            <div class="form-check form-check">
              <input type="checkbox" class="form-check-input" name="no_carry" id="no_carry" value="1" <?= old('no_carry') ? 'checked' : '' ?>>
              <label class="form-check-label" for="no_carry">No-carry addition (integers)</label>
            </div>
            <div class="form-check form-check">
              <input type="checkbox" class="form-check-input" name="no_borrow" id="no_borrow" value="1" <?= old('no_borrow') ? 'checked' : '' ?>>
              <label class="form-check-label" for="no_borrow">No-borrow subtraction (integers)</label>
            </div>
            <div class="form-check form-check">
              <input type="checkbox" class="form-check-input" name="no_negative" id="no_negative" value="1" <?= old('no_negative', '1') ? 'checked' : '' ?>>
              <label class="form-check-label" for="no_negative">No negative results (subtraction)</label>
            </div>
            <div class="form-check form-check">
              <input type="checkbox" class="form-check-input" name="answer_key" id="answer_key" value="1" <?= old('answer_key') ? 'checked' : '' ?>>
              <label class="form-check-label" for="answer_key">Include answer key on separate pages</label>
            </div>
            <?php if (! empty($tablesReady)): ?>
            <div class="form-check form-check">
              <input type="checkbox" class="form-check-input" name="save_set" id="save_set" value="1">
              <label class="form-check-label" for="save_set">Save to worksheet library after generating</label>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Generate &amp; Print</button>
          <span class="text-muted small ms-2">Opens in a new tab — A4 portrait.</span>
        </div>
      </form>
    </div>

    <?php if (! empty($savedSets)): ?>
    <div class="card card-outline card-secondary">
      <div class="card-header"><h3 class="card-title">Recent saved worksheets</h3></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Title</th><th>Layout</th><th>Problems</th><th>Date</th><th></th></tr></thead>
          <tbody>
            <?php foreach (array_slice($savedSets, 0, 5) as $set): ?>
            <tr>
              <td><?= esc($set['title'] ?? '') ?></td>
              <td><?= esc($set['layout'] ?? '') ?></td>
              <td><?= (int) ($set['problem_count'] ?? 0) ?></td>
              <td><?= esc($set['created_at'] ?? '') ?></td>
              <td><a href="<?= site_url('admin/math-worksheet/reprint/' . (int) $set['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Re-print</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
(function () {
  const DIGIT_BOUNDS = {
    1: { min: 1, max: 9 },
    2: { min: 10, max: 99 },
    3: { min: 100, max: 999 },
    4: { min: 1000, max: 9999 },
    5: { min: 10000, max: 99999 }
  };

  const numberTypeSel = document.getElementById('number_type');
  const integerBlock = document.getElementById('integerNumberBlock');
  const decimalBlock = document.getElementById('decimalNumberBlock');
  const layoutSel = document.getElementById('layout');
  const perPageSel = document.getElementById('per_page');
  const layoutHint = document.getElementById('layoutHint');
  const divisionMode = document.getElementById('division_mode');

  function decimalBounds(whole, frac) {
    const w = parseInt(whole, 10) || 1;
    const f = parseInt(frac, 10) || 0;
    const wholeMax = Math.pow(10, w) - 1;
    const min = f > 0 ? (1 / Math.pow(10, f)) : 1;
    const max = wholeMax + (f > 0 ? (Math.pow(10, f) - 1) / Math.pow(10, f) : 0);
    return { min: min.toFixed(f), max: max.toFixed(f) };
  }

  function applyIntegerPreset() {
    const digits = parseInt(document.getElementById('operand_digits').value, 10) || 2;
    const bounds = DIGIT_BOUNDS[digits] || DIGIT_BOUNDS[2];
    document.getElementById('operand_min').value = bounds.min;
    document.getElementById('operand_max').value = bounds.max;
  }

  function applyDecimalPreset() {
    const whole = document.getElementById('operand_whole_digits').value;
    const frac = document.getElementById('operand_decimal_digits').value;
    const bounds = decimalBounds(whole, frac);
    const minEl = document.getElementById('operand_min_dec');
    const maxEl = document.getElementById('operand_max_dec');
    if (minEl) minEl.value = bounds.min;
    if (maxEl) maxEl.value = bounds.max;
  }

  function syncNumberType() {
    const isDecimal = numberTypeSel.value === 'decimal';
    integerBlock.classList.toggle('d-none', isDecimal);
    decimalBlock.classList.toggle('d-none', !isDecimal);
    integerBlock.querySelectorAll('input, select').forEach(function (el) { el.disabled = isDecimal; });
    decimalBlock.querySelectorAll('input, select').forEach(function (el) { el.disabled = !isDecimal; });
    if (isDecimal && divisionMode.value === 'remainder') {
      divisionMode.value = 'whole';
    }
    divisionMode.querySelector('option[value="remainder"]').disabled = isDecimal;
  }

  function syncLayout() {
    const isVertical = layoutSel.value === 'vertical';
    layoutHint.textContent = isVertical
      ? 'Best for 12–20 problems per A4 page (5 per row).'
      : 'Best for 20–40 problems per A4 page.';
    Array.from(perPageSel.options).forEach(function (opt) {
      opt.disabled = isVertical && parseInt(opt.value, 10) > 20;
    });
    if (isVertical && parseInt(perPageSel.value, 10) > 20) {
      perPageSel.value = '20';
    }
  }

  document.querySelectorAll('.operand-digits').forEach(function (sel) {
    sel.addEventListener('change', applyIntegerPreset);
  });

  document.querySelectorAll('.decimal-whole, .decimal-frac').forEach(function (sel) {
    sel.addEventListener('change', applyDecimalPreset);
  });

  numberTypeSel.addEventListener('change', syncNumberType);
  layoutSel.addEventListener('change', syncLayout);
  syncNumberType();
  syncLayout();

  document.getElementById('mathWorksheetForm').addEventListener('submit', function (e) {
    const checked = this.querySelectorAll('input[name="operations[]"]:checked');
    if (!checked.length) {
      e.preventDefault();
      alert('Please select at least one operation.');
    }
  });
})();
</script>

<?= $this->endSection() ?>
