<?php
/** @var array<int, array{week_label: string, days: array<int, array{date: ?string, weekday: string, day_num: ?int, code: ?string, in_session: bool}>}> $weeks */
$weeks = $weeks ?? [];

$codeClass = static function (?string $c): string {
    $c = $c ?? '';
    switch ($c) {
        case 'P':
            return 'att-code att-code--p';
        case 'A':
            return 'att-code att-code--a';
        case 'L':
            return 'att-code att-code--l';
        case 'LC':
            return 'att-code att-code--lc';
        case 'EL':
            return 'att-code att-code--el';
        default:
            return 'att-code att-code--empty';
    }
};
?>

<?php if (empty($weeks)): ?>
  <div class="alert alert-info text-center mb-0">
    <i class="far fa-calendar-times fa-2x mb-2 d-block text-muted"></i>
    No attendance weeks found.
  </div>
<?php else: ?>
  <div class="att-weeks-stack">
    <?php foreach ($weeks as $week): ?>
      <div class="att-week-card card border-0 shadow-sm mb-3">
        <div class="card-header bg-light border-0 py-2 px-3">
          <strong class="text-dark">
            <i class="fas fa-layer-group me-2 text-primary"></i><?= esc($week['week_label'] ?? '') ?>
          </strong>
        </div>
        <div class="card-body py-3 px-2">
          <div class="att-week-row">
            <?php foreach ($week['days'] ?? [] as $d): ?>
              <div class="att-day-cell <?= empty($d['in_session']) ? 'att-day-cell--off' : '' ?>">
                <div class="att-day-head"><?= esc($d['weekday'] ?? '') ?></div>
                <?php if (! empty($d['in_session'])): ?>
                  <div class="att-day-num"><?= (int) ($d['day_num'] ?? 0) ?></div>
                  <div class="<?= $codeClass($d['code'] ?? null) ?>" title="<?= esc($d['date'] ?? '') ?>">
                    <?= esc($d['code'] ?? '—') ?>
                  </div>
                <?php else: ?>
                  <div class="att-day-num text-muted">—</div>
                  <div class="att-code att-code--empty att-code--dim">—</div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

