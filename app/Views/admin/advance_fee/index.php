<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$rows = $rows ?? [];
$grandTotal = 0.0;
foreach ($rows as $r) {
    $grandTotal += (float) ($r->amount ?? 0);
}
?>

<?= view('components/page_header', [
    'title' => 'Update Advance Fee',
    'icon' => 'fas fa-piggy-bank',
    'subtitle' => 'Students with a non-zero advance balance. Edit amounts and save. Setting 0 removes the student from this list.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Update Advance Fee', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid">
    <div class="card sms-card card-primary card-outline">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="card-title mb-0">
          <i class="fas fa-piggy-bank me-1"></i>
          Advance balances
          <span class="badge text-bg-info ms-2"><?= count($rows) ?> student(s)</span>
        </h3>
        <div class="card-tools d-flex align-items-center flex-wrap" style="gap:8px;">
          <input type="search" id="advanceSearch" class="form-control form-control-sm" style="width:220px;" placeholder="Search name, reg no, class…" />
          <button type="button" id="btnSaveAdvance" class="btn btn-success btn-sm">
            <i class="fas fa-save me-1"></i> Save changes
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if ($rows === []) : ?>
          <div class="p-4 text-center text-muted">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p class="mb-0">No students have advance fee balance right now.</p>
            <p class="small mb-0">Use <a href="<?= base_url('admin/fee-chalan-pay') ?>">Pay Fee Chalan</a> to deposit advance fee.</p>
          </div>
        <?php else : ?>
          <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0" id="advanceFeeTable">
              <thead class="table-light">
                <tr>
                  <th style="width:48px;">#</th>
                  <th>Student</th>
                  <th>Reg #</th>
                  <th>Class</th>
                  <th class="text-end" style="min-width:140px;">Advance (Rs)</th>
                  <th>Paid date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $i => $row) :
                    $classLabel = trim((string) $row->class_name);
                    if (! empty($row->section_name)) {
                        $classLabel = $classLabel === ''
                            ? (string) $row->section_name
                            : $classLabel . ' - ' . $row->section_name;
                    }
                    $paidDisplay = '';
                    if (! empty($row->paid_date) && $row->paid_date !== '0000-00-00') {
                        $paidDisplay = date('d M Y', strtotime($row->paid_date));
                    }
                ?>
                  <tr data-search="<?= esc(strtolower(
                      ($row->student_name ?? '') . ' ' .
                      ($row->reg_no ?? '') . ' ' .
                      $classLabel
                  ), 'attr') ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($row->student_name) ?></td>
                    <td><?= esc($row->reg_no ?? '') ?></td>
                    <td><?= esc($classLabel ?: '—') ?></td>
                    <td>
                      <input type="number"
                             class="form-control form-control-sm text-end advance-amount-input"
                             name="balance[<?= (int) $row->student_id ?>]"
                             data-student-id="<?= (int) $row->student_id ?>"
                             data-chalan-id="<?= (int) $row->chalan_id ?>"
                             value="<?= esc(number_format((float) $row->amount, 2, '.', '')) ?>"
                             min="0" step="0.01" />
                    </td>
                    <td class="text-muted small"><?= esc($paidDisplay ?: '—') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot class="bg-light">
                <tr>
                  <th colspan="4" class="text-end">Total advance on page</th>
                  <th class="text-end" id="advancePageTotal">Rs <?= number_format($grandTotal, 2) ?></th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
(function () {
  const saveUrl = <?= json_encode(base_url('admin/advance-fee/save')) ?>;
  const csrfName = <?= json_encode(csrf_token()) ?>;
  const csrfHash = <?= json_encode(csrf_hash()) ?>;

  function recalcTotal() {
    let sum = 0;
    document.querySelectorAll('.advance-amount-input').forEach(function (inp) {
      const row = inp.closest('tr');
      if (row && row.style.display === 'none') return;
      const v = parseFloat(inp.value);
      if (!isNaN(v) && v > 0) sum += v;
    });
    const el = document.getElementById('advancePageTotal');
    if (el) el.textContent = 'Rs ' + sum.toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  const search = document.getElementById('advanceSearch');
  if (search) {
    search.addEventListener('input', function () {
      const q = this.value.trim().toLowerCase();
      document.querySelectorAll('#advanceFeeTable tbody tr').forEach(function (tr) {
        const hay = tr.getAttribute('data-search') || '';
        tr.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
      });
      recalcTotal();
    });
  }

  document.querySelectorAll('.advance-amount-input').forEach(function (inp) {
    inp.addEventListener('input', recalcTotal);
  });

  const btn = document.getElementById('btnSaveAdvance');
  if (!btn) return;

  btn.addEventListener('click', function () {
    const balances = {};
    document.querySelectorAll('.advance-amount-input').forEach(function (inp) {
      const sid = inp.getAttribute('data-student-id');
      if (!sid) return;
      const v = parseFloat(inp.value);
      balances[sid] = isNaN(v) || v < 0 ? 0 : Math.round(v * 100) / 100;
    });

    if (Object.keys(balances).length === 0) {
      if (typeof toastr !== 'undefined') toastr.warning('Nothing to save.');
      return;
    }

    btn.disabled = true;
    const postData = { balances: JSON.stringify(balances) };
    postData[csrfName] = csrfHash;

    $.ajax({
      url: saveUrl,
      method: 'POST',
      data: postData,
      dataType: 'json'
    }).done(function (res) {
      if (res.success) {
        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Saved.');
        window.location.reload();
      } else {
        if (typeof toastr !== 'undefined') toastr.warning(res.message || 'Save failed.');
      }
    }).fail(function () {
      if (typeof toastr !== 'undefined') toastr.error('Request failed.');
    }).always(function () {
      btn.disabled = false;
    });
  });
})();
</script>

<?= $this->endSection() ?>
