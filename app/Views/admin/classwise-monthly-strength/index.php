<!-- app/Views/admin/classwise_monthly_strength/index.php -->
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Monthly Student Strength (by Session)',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Monthly Student Strength (by Session)', 'active' => true],
    ],
]) ?>


<section class="content">
  <?php if (!empty($error)): ?>
    <div class="alert alert-warning mb-0"><?= esc($error) ?></div>
  <?php else: ?>

    <div class="card card-primary card-outline">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-bordered mb-0" id="strength-grid" style="min-width: 720px;">
            <thead class="table-dark">
              <tr>
                <th class="sticky-col" style="min-width:160px;">Month</th>
                <?php foreach ($sessions as $s): ?>
                  <?php
                    $sid   = (int) $s['session_id'];
                    $label = $s['label'] ?? ($s['session_name'] ?? ('Session '.$sid));
                    $title = trim(($s['session_name'] ?? '').' | '.($s['start_date'] ?? '').' → '.($s['end_date'] ?? ''));
                  ?>
                  <th class="text-end" title="<?= esc($title) ?>"><?= esc($label) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>

            <tbody>
              <?php
                // Prepare column totals
                $colTotals = [];
                foreach ($sessions as $s) {
                  $colTotals[(int)$s['session_id']] = 0;
                }
              ?>

              <?php foreach ($labels as $i => $monthName): ?>
                <tr>
                  <td class="sticky-col fw-bold"><?= esc($monthName) ?></td>
                  <?php foreach ($sessions as $s): ?>
                    <?php
                      $sid = (int) $s['session_id'];
                      $val = (int) ($grid[$i][$sid] ?? 0);
                      $colTotals[$sid] += $val;
                    ?>
                    <td class="text-end"><?= number_format($val) ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>

            <tfoot>
              <tr class="bg-light fw-bold">
                <td class="sticky-col">Totals</td>
                <?php foreach ($sessions as $s): ?>
                  <?php $sid = (int) $s['session_id']; ?>
                  <td class="text-end"><?= number_format($colTotals[$sid] ?? 0) ?></td>
                <?php endforeach; ?>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="card-footer py-2 text-muted small">
        Counts represent distinct students per fee month that fall inside each session’s date range.
      </div>
    </div>

  <?php endif; ?>
</section>

<style>
  /* Sticky header & first column for better usability on wide tables */
  #strength-grid thead th {
    position: sticky; top: 0; z-index: 3; background: #343a40; color: #fff;
  }
  #strength-grid .sticky-col {
    position: sticky; left: 0; z-index: 4; background: #f8f9fa;
  }
  @media (max-width: 768px) {
    #strength-grid { font-size: .9rem; }
  }
</style>



<?= $this->endSection() ?>
