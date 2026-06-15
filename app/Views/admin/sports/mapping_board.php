<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- SortableJS for drag & drop (lightweight) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<style>
  .house-board { display: grid; grid-template-columns: 280px 1fr; gap: 1rem; }
  @media (max-width: 992px) { .house-board { grid-template-columns: 1fr; } }
  .house-lanes { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px,1fr)); gap: 1rem; }
  .lane, .unassigned {
    background: #f8f9fa; border: 1px solid #dee2e6; border-radius: .5rem; min-height: 320px;
    display: flex; flex-direction: column;
  }
  .lane-header {
    font-weight: 600; padding: .5rem .75rem; border-bottom: 1px solid #e9ecef;
    display: flex; align-items: center; justify-content: space-between;
  }
  .lane-header .badge { font-size: .85rem; }
  .student-list { padding: .5rem; flex: 1; overflow: auto; }
  .student-card {
    padding: .5rem .6rem; background: #fff; border: 1px solid #e2e2e2; border-radius: .375rem;
    margin-bottom: .5rem; cursor: grab; user-select: none; display: flex; justify-content: space-between;
  }
  .student-card small { color: #6c757d; }
  .sticky-top-strip {
    position: sticky; top: .25rem; z-index: 10; background: #fff; padding: .25rem .5rem; border-radius: .5rem; border: 1px dashed #ddd;
  }
  .badge-counter { padding: .35rem .6rem; border-radius: 999px; }
</style>

<?= view('components/page_header', [
    'title' => 'Assign Students to Houses',
    'icon' => 'fas fa-exchange-alt',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Mapping', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="card card-primary">
    <div class="card-body">
      <div class="row align-items-end">
        <div class="form-group col-md-4">
          <label for="cls_sec_id">Class-Section</label>
          <select id="cls_sec_id" class="form-control">
            <option value="">Select�</option>
            <?php
            // If you have a helper like userClassSections() return [ ['cls_sec_id'=>.., 'class_name'=>.., 'section_name'=>..], ... ]
            if (function_exists('userClassSections')) {
              foreach ((array) userClassSections() as $row) {
                $id   = (int)($row['cls_sec_id'] ?? 0);
                $text = trim(($row['class_name'] ?? '').' - '.($row['section_name'] ?? ''));
                echo '<option value="'.$id.'">'.esc($text).'</option>';
              }
            }
            ?>
          </select>
        </div>

        <div class="form-group col-md-8">
          <div class="sticky-top-strip">
            <div id="topCounts" class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
              <span class="me-2">Counts:</span>
              <span class="badge text-bg-secondary badge-counter" id="count-unassigned">Unassigned: 0</span>
              <span id="houseCountPlace"></span>
            </div>
          </div>
        </div>
      </div>

      <div id="boardHint" class="text-muted mb-2">Select a class-section to load students & houses�</div>

      <div id="houseBoard" class="house-board">
        <!-- Left: Unassigned -->
        <div class="unassigned">
          <div class="lane-header">
            <span>Unassigned</span>
            <span class="badge text-bg-secondary" id="badge-unassigned">0</span>
          </div>
          <div id="list-0" class="student-list" data-house="0"></div>
        </div>

        <!-- Right: Houses grid -->
        <div id="lanes" class="house-lanes"><!-- built dynamically --></div>
      </div>
    </div>
  </div>
</section>

<script>
  const CSRF_NAME = '<?= csrf_token() ?>';
  const CSRF_HASH = '<?= csrf_hash() ?>';

  // Helpers
  const studentCard = (st) => {
    const name = (st.student_name || '').trim();
    const reg  = (st.reg_no || '').trim();
    return $(`
      <div class="student-card" data-id="${st.student_id}">
        <span>${name}${reg ? ' <small>('+reg+')</small>' : ''}</span>
        <i class="fas fa-grip-vertical"></i>
      </div>
    `);
  };

  function buildLanes(houses) {
    const $lanes = $('#lanes').empty();
    const $countsBar = $('#houseCountPlace').empty();
    houses.forEach(h => {
      const color = (h.color_code && h.color_code.trim()) ? h.color_code : '#007bff';
      const lane = $(`
        <div class="lane" id="lane-${h.house_id}">
          <div class="lane-header" style="border-top-left-radius:.5rem;border-top-right-radius:.5rem; background:${color}22;">
            <span><span class="badge" style="background:${color};">${h.short_name || h.house_name}</span></span>
            <span class="badge" id="badge-${h.house_id}" style="background:${color};">0</span>
          </div>
          <div class="student-list" id="list-${h.house_id}" data-house="${h.house_id}"></div>
        </div>
      `);
      $lanes.append(lane);

      const countBadge = $(`
        <span class="badge badge-counter" id="count-house-${h.house_id}" style="background:${color};">
          ${(h.short_name || h.house_name)}: 0
        </span>
      `);
      $countsBar.append(countBadge);
    });

    // Activate Sortable on every list (unassigned + each house)
    enableDnD();
  }

  function updateCounts(counts) {
    // Unassigned
    const un = counts.unassigned || 0;
    $('#badge-unassigned').text(un);
    $('#count-unassigned').text('Unassigned: ' + un);

    // Houses
    Object.keys(counts).forEach(k => {
      if (k === 'unassigned') return;
      $('#badge-' + k).text(counts[k]);
      $('#count-house-' + k).text($('#count-house-' + k).text().replace(/: .*/, ': ' + counts[k]));
    });
  }

  function buildFromResponse(res) {
    // Build lanes first (houses meta)
    buildLanes(res.houses || []);

    // Fill cards
    const $un = $('#list-0').empty();
    (res.unassigned || []).forEach(st => $un.append(studentCard(st)));

    (res.houses || []).forEach(h => {
      const $hl = $(`#list-${h.house_id}`).empty();
      (h.students || []).forEach(st => $hl.append(studentCard(st)));
    });

    // Counters
    updateCounts(res.counts || {});
  }

  function loadBoard() {
    const clsSecId = $('#cls_sec_id').val();
    $('#houseBoard').addClass('opacity-50');
    $('#boardHint').hide();

    if (!clsSecId) {
      $('#lanes').empty();
      $('#list-0').empty();
      updateCounts({unassigned:0});
      $('#houseBoard').removeClass('opacity-50');
      $('#boardHint').show();
      return;
    }

    $.post("<?= base_url('admin/sports/mapping/board') ?>", {
      [CSRF_NAME]: CSRF_HASH,
      cls_sec_id: clsSecId
    }, function (res) {
      if (!res || !res.ok) {
        if (window.toastr) toastr.error(res && res.msg ? res.msg : 'Failed to load board');
        $('#houseBoard').removeClass('opacity-50');
        return;
      }
      buildFromResponse(res);
      $('#houseBoard').removeClass('opacity-50');
    }, 'json');
  }

  function enableDnD() {
    // Enable Sortable for every container with class .student-list
    $('.student-list').each(function() {
      const el = this;
      Sortable.create(el, {
        group: 'houses',
        animation: 150,
        ghostClass: 'bg-warning',
        onAdd: function (evt) {
          const $item = $(evt.item);
          const studentId = parseInt($item.attr('data-id'), 10);
          const targetHouse = parseInt($(evt.to).data('house'), 10); // 0 for unassigned
          const clsSecId = parseInt($('#cls_sec_id').val(), 10) || 0;

          // Persist move
          $.post("<?= base_url('admin/sports/mapping/move') ?>", {
            [CSRF_NAME]: CSRF_HASH,
            student_id: studentId,
            house_id: targetHouse,
            cls_sec_id: clsSecId
          }, function (res) {
            if (!res || !res.ok) {
              if (window.toastr) toastr.error('Failed to update house');
              // revert UI by moving element back
              evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
              return;
            }
            // Update counters
            if (res.counts) updateCounts(res.counts);
            if (window.toastr) toastr.success('Updated');
          }, 'json');
        }
      });
    });
  }

  // Events
  $('#cls_sec_id').on('change', loadBoard);

  // Initial
  // (Leave board empty until a class-section is picked)
</script>

<?= $this->endSection() ?>
