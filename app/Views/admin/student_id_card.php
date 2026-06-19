<?= $this->extend('layouts/admin_template') ?>

<?= $this->section('content') ?>
<?= view('components/page_header', [
    'title' => 'Student ID Card',
    'subtitle' => 'Generate print-ready student cards by class, section, status, or targeted reprint IDs.',
    'icon' => 'fas fa-id-card',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'url' => base_url('admin/students')],
        ['label' => 'ID Card', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card sms-card sms-index-card card-primary card-outline idcard-generator">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-id-card-alt me-2"></i>
        Card Generator
      </h3>
      <div class="card-tools">
        <span class="badge bg-primary">Front and back print set</span>
      </div>
    </div>

    <div class="card-body">
      <div class="sms-section-note mb-3">
        <i class="fas fa-info-circle"></i>
        Use class and section filters for whole batches, or paste student IDs for lost-card reprints.
      </div>

      <div class="idcard-generator__filters no-print">
        <div class="row g-3">
          <div class="col-xl-3 col-md-6">
            <label for="class_id" class="form-label">Class</label>
            <select class="form-select" id="class_id">
              <option value="">All Classes</option>
              <?php foreach (($classes ?? []) as $classId => $className): ?>
                <option value="<?= (int) $classId ?>"><?= esc($className) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-xl-3 col-md-6">
            <label for="cls_sec_id" class="form-label">Section</label>
            <select class="form-select" id="cls_sec_id">
              <option value="">All Sections</option>
              <?php foreach (($sectionsclassinfo ?? []) as $section): ?>
                <option value="<?= (int) ($section['cls_sec_id'] ?? 0) ?>" data-class-id="<?= (int) ($section['class_id'] ?? 0) ?>">
                  <?= esc($section['sectionclassname'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-xl-2 col-md-4">
            <label for="statusFilter" class="form-label">Student status</label>
            <select class="form-select" id="statusFilter">
              <option value="active">Active Students</option>
              <option value="new">New Admissions</option>
              <option value="all">All Students</option>
            </select>
          </div>

          <div class="col-xl-4 col-md-8">
            <label for="student_ids" class="form-label">Specific student IDs</label>
            <input type="text" class="form-control" id="student_ids" placeholder="Example: 1201, 1208, 1210">
            <div class="form-text">Optional for small batch reprints and lost-card replacements.</div>
          </div>
        </div>

        <div class="idcard-generator__actions">
          <div class="idcard-generator__selection" id="selectionSummary">
            All classes · All sections · Active Students
          </div>
          <div class="idcard-generator__action-buttons">
            <button type="button" class="btn btn-outline-secondary" id="resetFilters">
              <i class="fas fa-undo-alt me-1"></i> Reset
            </button>
            <button type="button" class="btn btn-primary" id="generateCards">
              <i class="fas fa-id-card me-1"></i> Generate Cards
            </button>
            <button type="button" class="btn btn-success" id="printCards" hidden>
              <i class="fas fa-print me-1"></i> Print Cards
            </button>
          </div>
        </div>
      </div>

      <div class="idcard-generator__status no-print">
        <span class="idcard-generator__status-label">Output</span>
        <span id="cardMeta" class="text-muted">Generate cards to preview the print layout.</span>
      </div>

      <div class="idcard-generator__result-shell">
        <div id="cardsLoader" class="idcard-generator__loader d-none">
          <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
          <span>Generating student cards...</span>
        </div>
        <div id="cardsResult" class="idcard-generator__result"></div>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<style>
  .idcard-generator__filters {
    padding: 1rem 1.05rem;
    margin-bottom: 1rem;
    border: 1px solid #dbe6f0;
    border-radius: 12px;
    background: linear-gradient(180deg, #fbfdff 0%, #f3f8fc 100%);
  }

  .idcard-generator__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem 1rem;
    margin-top: 1rem;
    padding-top: 0.95rem;
    border-top: 1px solid #dbe6f0;
  }

  .idcard-generator__selection {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.42rem 0.72rem;
    border-radius: 999px;
    background: #edf5fb;
    border: 1px solid #d7e7f4;
    color: #49667d;
    font-size: 0.82rem;
    font-weight: 700;
  }

  .idcard-generator__action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    justify-content: flex-end;
  }

  .idcard-generator__action-buttons .btn {
    min-width: 9rem;
  }

  .idcard-generator__status {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem 0.8rem;
    margin-bottom: 1rem;
  }

  .idcard-generator__status-label {
    display: inline-flex;
    align-items: center;
    padding: 0.34rem 0.62rem;
    border-radius: 999px;
    background: #e9f6ef;
    color: #0f7a56;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .idcard-generator__result-shell {
    position: relative;
    min-height: 9rem;
    padding: 1rem;
    border: 1px solid #dbe6f0;
    border-radius: 14px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbfe 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
  }

  .idcard-generator__loader {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
    border-radius: 14px;
    background: rgba(248, 251, 254, 0.92);
    color: #35536c;
    font-weight: 700;
  }

  .idcard-generator__result {
    min-height: 7rem;
  }

  .idcard-generator__result .id-card-debug {
    margin-bottom: 1rem;
  }

  @media (max-width: 991.98px) {
    .idcard-generator__actions,
    .idcard-generator__action-buttons {
      justify-content: flex-start;
    }

    .idcard-generator__action-buttons .btn {
      min-width: 0;
      flex: 1 1 11rem;
    }
  }

  @media print {
    .main-sidebar,
    .main-header,
    .content-header,
    .breadcrumb,
    .no-print,
    .main-footer {
      display: none !important;
    }

    .content-wrapper {
      margin-left: 0 !important;
    }

    .content,
    .container-fluid,
    .card,
    .card-body,
    .idcard-generator__result-shell {
      padding: 0 !important;
      margin: 0 !important;
      border: 0 !important;
      box-shadow: none !important;
      background: #fff !important;
    }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
  (function () {
    const $class = $('#class_id');
    const $section = $('#cls_sec_id');
    const $status = $('#statusFilter');
    const $studentIds = $('#student_ids');
    const $generate = $('#generateCards');
    const $print = $('#printCards');
    const $reset = $('#resetFilters');
    const $loader = $('#cardsLoader');
    const $result = $('#cardsResult');
    const $meta = $('#cardMeta');
    const $selectionSummary = $('#selectionSummary');

    function setBusy(isBusy) {
      $loader.toggleClass('d-none', !isBusy);
      $generate.prop('disabled', isBusy);
      $reset.prop('disabled', isBusy);
      $print.prop('disabled', isBusy);
    }

    function updateSelectionSummary() {
      const classText = $class.find('option:selected').text().trim() || 'All Classes';
      const sectionText = $section.find('option:selected').text().trim() || 'All Sections';
      const statusText = $status.find('option:selected').text().trim() || 'Students';
      $selectionSummary.text(classText + ' · ' + sectionText + ' · ' + statusText);
    }

    function filterSections() {
      const selectedClass = String($class.val() || '');

      $section.find('option').each(function () {
        const $option = $(this);
        const optionClass = String($option.data('class-id') || '');

        if (!$option.val()) {
          $option.prop('hidden', false);
          return;
        }

        $option.prop('hidden', !!selectedClass && optionClass !== selectedClass);
      });

      if ($section.find('option:selected').prop('hidden')) {
        $section.val('');
      }

      updateSelectionSummary();
    }

    function resetFilters() {
      $class.val('');
      $section.val('');
      $status.val('active');
      $studentIds.val('');
      filterSections();
      $result.empty();
      $print.attr('hidden', 'hidden');
      $meta.text('Filters reset. Generate cards to preview the print layout.');
    }

    function generate() {
      setBusy(true);
      $result.empty();
      $print.attr('hidden', 'hidden');
      $meta.text('Generating cards...');

      $.ajax({
        url: '<?= site_url('admin/student_id_card/data_vertical') ?>',
        type: 'POST',
        data: {
          class_id: $class.val(),
          cls_sec_id: $section.val(),
          status: $status.val(),
          student_ids: $studentIds.val().trim()
        },
        success: function (res) {
          $result.html(res);
          const count = $result.find('.id-card-pair').length;

          if (count > 0) {
            $meta.text(count + ' card set(s) generated and ready for print.');
            $print.removeAttr('hidden');
          } else {
            $meta.text('No students matched the selected filters.');
          }
        },
        error: function () {
          $result.html('<div class="alert alert-danger mb-0">Unable to generate cards. Please try again.</div>');
          $meta.text('Generation failed.');
        },
        complete: function () {
          setBusy(false);
        }
      });
    }

    $class.on('change', filterSections);
    $section.on('change', updateSelectionSummary);
    $status.on('change', updateSelectionSummary);
    $studentIds.on('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        generate();
      }
    });

    $generate.on('click', generate);
    $reset.on('click', resetFilters);
    $print.on('click', function () {
      window.print();
    });

    filterSections();
    $meta.text('Choose your filters, then generate the current print set.');
  })();
</script>
<?= $this->endSection() ?>
