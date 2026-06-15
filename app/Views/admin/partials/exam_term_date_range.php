<?php
// Inputs from controller:
// $range      = ['start' => 'Y-m-d', 'end' => 'Y-m-d', 'term_name' => '...']
// $exam_start = 'Y-m-d' | null
// $exam_end   = 'Y-m-d' | null
// $days_map   = ['YYYY-MM-DD' => '1'|'0', ...]  // existing saved toggles (edit)

$termStartYmd = !empty($range['start']) ? date('Y-m-d', strtotime($range['start'])) : '';
$termEndYmd   = !empty($range['end'])   ? date('Y-m-d', strtotime($range['end']))   : '';

$isEdit = !empty($exam_start) || !empty($exam_end);

if ($isEdit) {
  $examStartValue = !empty($exam_start) ? date('Y-m-d', strtotime($exam_start)) : $termStartYmd;
  $examEndValue   = !empty($exam_end)   ? date('Y-m-d', strtotime($exam_end))   : $termEndYmd;
} else {
  // Add mode default: last 15 days of selected term (inclusive)
  $examEndValue = $termEndYmd;
  $examStartValue = $termStartYmd;
  if ($termStartYmd && $termEndYmd) {
    $endTs = strtotime($termEndYmd);
    $start15Ts = strtotime('-14 days', $endTs);
    $termStartTs = strtotime($termStartYmd);
    $examStartValue = date('Y-m-d', max($start15Ts, $termStartTs));
  }
}

// Days map for JS
$daysMapJson = json_encode($days_map ?? [], JSON_UNESCAPED_SLASHES);
?>

<div id="termDateRangeFields">
  <div class="row">
    <div class="form-group col-md-6 mb-2">
      <label for="exam_start_date">
        <?= isset($range['term_name']) ? esc($range['term_name']).' — ' : '' ?>Exam Start Date
      </label>
      <input type="date"
             class="form-control"
             id="exam_start_date"
             name="exam_start_date"
             value="<?= esc($examStartValue) ?>"
             min="<?= esc($termStartYmd) ?>"
             max="<?= esc($termEndYmd) ?>"
             required>
    </div>
    <div class="form-group col-md-6 mb-2">
      <label for="exam_end_date">Exam End Date</label>
      <input type="date"
             class="form-control"
             id="exam_end_date"
             name="exam_end_date"
             value="<?= esc($examEndValue) ?>"
             min="<?= esc($termStartYmd) ?>"
             max="<?= esc($termEndYmd) ?>"
             required>
    </div>
  </div>

  <div class="alert alert-light border mb-2 py-2 px-3">
    <div class="small mb-0">
      By default, all days in the selected range are <strong>On</strong>.
      Click a day button to mark it <strong>Off</strong>.
    </div>
  </div>

  <!-- Counters -->
  <div class="d-flex flex-wrap mb-2" id="examDaysCounters" style="gap:1rem;">
    <div><strong>Total days:</strong> <span id="cntTotal">0</span></div>
    <div><strong>On:</strong> <span id="cntOn">0</span></div>
    <div><strong>Off:</strong> <span id="cntOff">0</span></div>
  </div>

  <div class="mb-2 d-flex flex-wrap" style="gap:.5rem;">
    <button type="button" id="markAllOn" class="btn btn-sm btn-outline-success">Mark all On</button>
    <button type="button" id="markAllOff" class="btn btn-sm btn-outline-secondary">Mark all Off</button>
    <button type="button" id="markWeekendOff" class="btn btn-sm btn-outline-dark">Weekend Off</button>
  </div>

  <!-- Weekly grid -->
  <div id="examDaysGrid" class="border rounded p-2 bg-white" style="max-height:420px; overflow:auto;"></div>

  <!-- hidden bounds -->
  <input type="hidden" id="term_start_hidden" value="<?= esc($termStartYmd) ?>">
  <input type="hidden" id="term_end_hidden"   value="<?= esc($termEndYmd) ?>">
</div>

<script>
(function(){
  const s = document.getElementById('exam_start_date');
  const e = document.getElementById('exam_end_date');
  const boundsStart = document.getElementById('term_start_hidden')?.value || '';
  const boundsEnd   = document.getElementById('term_end_hidden')?.value   || '';
  const gridEl = document.getElementById('examDaysGrid');
  const cTotal = document.getElementById('cntTotal');
  const cOn    = document.getElementById('cntOn');
  const cOff   = document.getElementById('cntOff');
  const markAllOnBtn = document.getElementById('markAllOn');
  const markAllOffBtn = document.getElementById('markAllOff');
  const markWeekendOffBtn = document.getElementById('markWeekendOff');

  // Preset map from server for EDIT: {'YYYY-MM-DD':'1'|'0', ...}
  const preset = <?php echo $daysMapJson ?: '{}' ?>;

  if (!s || !e || !gridEl) return;

  function clampToBounds(input) {
    if (!input.value) return;
    if (boundsStart && input.value < boundsStart) input.value = boundsStart;
    if (boundsEnd   && input.value > boundsEnd)   input.value = boundsEnd;
  }
  function toYMD(d){const y=d.getFullYear(),m=(d.getMonth()+1+'').padStart(2,'0'),dd=(d.getDate()+'').padStart(2,'0');return `${y}-${m}-${dd}`;}
  function toDMY(d){const dd=(d.getDate()+'').padStart(2,'0'),mm=(d.getMonth()+1+'').padStart(2,'0'),yy=d.getFullYear();return `${dd}-${mm}-${yy}`;}
  function toDM(d){const dd=(d.getDate()+'').padStart(2,'0'),mm=(d.getMonth()+1+'').padStart(2,'0');return `${dd}/${mm}`;}
  const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  const mondayFirstCols = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  function getIsoWeekStart(dateObj){
    const d = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
    const day = d.getDay(); // Sun=0
    const diff = day === 0 ? -6 : (1 - day); // shift to Monday
    d.setDate(d.getDate() + diff);
    return d;
  }

  function updateHiddenAndButton(btn, isOn) {
    const ymd = btn.getAttribute('data-date');
    const hidden = gridEl.querySelector(`input[type="hidden"][name="exam_days[${ymd}]"]`);
    if (hidden) hidden.value = isOn ? '1' : '0';
    btn.setAttribute('data-on', isOn ? '1' : '0');
    btn.className = isOn ? 'btn btn-sm btn-success w-100 day-toggle-btn py-0' : 'btn btn-sm btn-outline-secondary w-100 day-toggle-btn py-0';
    btn.textContent = isOn ? 'On' : 'Off';
  }

  function refreshCounters() {
    const allBtns = gridEl.querySelectorAll('.day-toggle-btn');
    const all = allBtns.length;
    let onCount = 0;
    allBtns.forEach(function(b){
      if (b.getAttribute('data-on') === '1') onCount++;
    });
    cTotal.textContent = all.toString();
    cOn.textContent = onCount.toString();
    cOff.textContent = (all - onCount).toString();
  }

  function buildGrid() {
    // bounds + ordering
    clampToBounds(s); clampToBounds(e);
    if (s.value && e.value && e.value < s.value) e.value = s.value;

    e.min = s.value || boundsStart || '';
    s.max = e.value || boundsEnd   || '';

    // validity
    const msgOutOfRange = 'Date must be between ' + (boundsStart || '…') + ' and ' + (boundsEnd || '…');
    const msgEndBeforeStart = 'End date cannot be before start date.';
    if ((boundsStart && s.value < boundsStart) || (boundsEnd && s.value > boundsEnd)) s.setCustomValidity(msgOutOfRange);
    else if (e.value && s.value > e.value) s.setCustomValidity('Start date cannot be after end date.');
    else s.setCustomValidity('');
    if ((boundsStart && e.value < boundsStart) || (boundsEnd && e.value > boundsEnd)) e.setCustomValidity(msgOutOfRange);
    else if (s.value && e.value < s.value) e.setCustomValidity(msgEndBeforeStart);
    else e.setCustomValidity('');

    // render grid
    gridEl.innerHTML = '';

    if (!s.value || !e.value) {
      cTotal.textContent='0'; cOn.textContent='0'; cOff.textContent='0';
      return;
    }
    const start = new Date(s.value + 'T00:00:00');
    const end   = new Date(e.value + 'T00:00:00');

    const allDates = [];
    for (let d = new Date(start); d <= end; d.setDate(d.getDate()+1)) {
      allDates.push(new Date(d));
    }
    if (!allDates.length) {
      cTotal.textContent='0'; cOn.textContent='0'; cOff.textContent='0';
      return;
    }

    const calendarStart = getIsoWeekStart(allDates[0]);
    const calendarEnd = new Date(allDates[allDates.length - 1]);
    const calendarEndDay = calendarEnd.getDay(); // 0..6
    const endShift = calendarEndDay === 0 ? 0 : (7 - calendarEndDay);
    calendarEnd.setDate(calendarEnd.getDate() + endShift);

    const table = document.createElement('table');
    table.className = 'table table-sm table-bordered mb-0';
    table.style.tableLayout = 'fixed';

    const thead = document.createElement('thead');
    const trh = document.createElement('tr');
    mondayFirstCols.forEach(function(name){
      const th = document.createElement('th');
      th.className = 'text-center align-middle';
      th.textContent = name;
      trh.appendChild(th);
    });
    thead.appendChild(trh);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    const dateLookup = {};
    allDates.forEach(function(d){
      dateLookup[toYMD(d)] = true;
    });

    for (let d = new Date(calendarStart); d <= calendarEnd; ) {
      const tr = document.createElement('tr');
      for (let col = 0; col < 7; col++) {
        const cellDate = new Date(d);
        const ymd = toYMD(cellDate);
        const inRange = !!dateLookup[ymd];
        const td = document.createElement('td');
        td.className = 'align-top p-1';
        td.style.height = '72px';

        if (inRange) {
          const dayIndex = cellDate.getDay();
          const isWeekend = (dayIndex === 0 || dayIndex === 6);
          const isOn = (Object.prototype.hasOwnProperty.call(preset, ymd) ? (preset[ymd] === '1') : true);
          const dateTop = document.createElement('div');
          dateTop.className = 'small fw-bold mb-1';
          dateTop.textContent = toDM(cellDate);

          const detail = document.createElement('div');
          detail.className = 'small text-muted mb-1';
          detail.textContent = dayNames[dayIndex];

          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-sm w-100 day-toggle-btn py-0';
          btn.setAttribute('data-date', ymd);
          btn.setAttribute('data-day', dayNames[dayIndex]);
          updateHiddenAndButton(btn, isOn);
          if (isWeekend) {
            btn.setAttribute('data-weekend', '1');
          }
          btn.addEventListener('click', function(){
            const nowOn = this.getAttribute('data-on') === '1';
            updateHiddenAndButton(this, !nowOn);
            refreshCounters();
          });

          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = `exam_days[${ymd}]`;
          hidden.value = isOn ? '1' : '0';

          td.appendChild(dateTop);
          td.appendChild(detail);
          td.appendChild(btn);
          td.appendChild(hidden);
        } else {
          td.className = 'bg-light';
        }
        tr.appendChild(td);
        d.setDate(d.getDate() + 1);
      }
      tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    gridEl.appendChild(table);
    refreshCounters();
  }

  markAllOnBtn?.addEventListener('click', function(){
    gridEl.querySelectorAll('.day-toggle-btn').forEach(function(btn){
      updateHiddenAndButton(btn, true);
    });
    refreshCounters();
  });

  markAllOffBtn?.addEventListener('click', function(){
    gridEl.querySelectorAll('.day-toggle-btn').forEach(function(btn){
      updateHiddenAndButton(btn, false);
    });
    refreshCounters();
  });

  markWeekendOffBtn?.addEventListener('click', function(){
    gridEl.querySelectorAll('.day-toggle-btn').forEach(function(btn){
      if (btn.getAttribute('data-weekend') === '1') {
        updateHiddenAndButton(btn, false);
      }
    });
    refreshCounters();
  });

  // initial + changes
  buildGrid();
  s.addEventListener('input', buildGrid);
  e.addEventListener('input', buildGrid);
  s.addEventListener('change', buildGrid);
  e.addEventListener('change', buildGrid);

  // guard submit
  const form = s.closest('form');
  if (form) {
    form.addEventListener('submit', function(ev){
      if (!s.checkValidity() || !e.checkValidity()) {
        ev.preventDefault();
        form.reportValidity();
      }
    });
  }
})();
</script>
