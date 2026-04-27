<?php
// Inputs from controller:
// $range      = ['start' => 'Y-m-d', 'end' => 'Y-m-d', 'term_name' => '...']
// $exam_start = 'Y-m-d' | null
// $exam_end   = 'Y-m-d' | null
// $days_map   = ['YYYY-MM-DD' => '1'|'0', ...]  // existing saved toggles (edit)

$termStartYmd = !empty($range['start']) ? date('Y-m-d', strtotime($range['start'])) : '';
$termEndYmd   = !empty($range['end'])   ? date('Y-m-d', strtotime($range['end']))   : '';

// Prefill exam fields: prefer existing exam dates (edit); otherwise term span (add)
$examStartValue = !empty($exam_start) ? date('Y-m-d', strtotime($exam_start)) : $termStartYmd;
$examEndValue   = !empty($exam_end)   ? date('Y-m-d', strtotime($exam_end))   : $termEndYmd;

// Days map for JS
$daysMapJson = json_encode($days_map ?? [], JSON_UNESCAPED_SLASHES);
?>

<div id="termDateRangeFields">
  <div class="form-row">
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

  <!-- Counters -->
  <div class="d-flex flex-wrap mb-2" id="examDaysCounters" style="gap:1rem;">
    <div><strong>Total days:</strong> <span id="cntTotal">0</span></div>
    <div><strong>On:</strong> <span id="cntOn">0</span></div>
    <div><strong>Off:</strong> <span id="cntOff">0</span></div>
  </div>

  <!-- Date list -->
  <div id="examDaysList" class="border rounded p-2" style="max-height:260px; overflow:auto;"></div>

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
  const listEl = document.getElementById('examDaysList');
  const cTotal = document.getElementById('cntTotal');
  const cOn    = document.getElementById('cntOn');
  const cOff   = document.getElementById('cntOff');

  // Preset map from server for EDIT: {'YYYY-MM-DD':'1'|'0', ...}
  const preset = <?php echo $daysMapJson ?: '{}' ?>;

  if (!s || !e || !listEl) return;

  function clampToBounds(input) {
    if (!input.value) return;
    if (boundsStart && input.value < boundsStart) input.value = boundsStart;
    if (boundsEnd   && input.value > boundsEnd)   input.value = boundsEnd;
  }
  function toYMD(d){const y=d.getFullYear(),m=(d.getMonth()+1+'').padStart(2,'0'),dd=(d.getDate()+'').padStart(2,'0');return `${y}-${m}-${dd}`;}
  function toDMY(d){const dd=(d.getDate()+'').padStart(2,'0'),mm=(d.getMonth()+1+'').padStart(2,'0'),yy=d.getFullYear();return `${dd}-${mm}-${yy}`;}
  const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

  function buildList() {
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

    // render list
    listEl.innerHTML = '';
    let total=0, on=0, off=0;

    if (!s.value || !e.value) {
      cTotal.textContent='0'; cOn.textContent='0'; cOff.textContent='0';
      return;
    }
    const start = new Date(s.value + 'T00:00:00');
    const end   = new Date(e.value + 'T00:00:00');

    for (let d = new Date(start); d <= end; d.setDate(d.getDate()+1)) {
      total++;
      const ymd = toYMD(d);
      const dmy = toDMY(d);
      const day = dayNames[d.getDay()];

      // default ON; if preset exists, honor it
      const isOn = (Object.prototype.hasOwnProperty.call(preset, ymd) ? (preset[ymd] === '1') : true);

      const row = document.createElement('div');
      row.className = 'd-flex align-items-center py-1 border-bottom';
      row.style.gap = '1rem';

      const left = document.createElement('div');
      left.className = 'flex-grow-1';
      left.innerHTML = `<strong>${dmy}</strong> <span class="text-muted">(${day})</span>`;

      const right = document.createElement('div');
      right.innerHTML = `
        <div class="form-check">
          <input type="checkbox" class="form-check-input day-toggle" id="day_${ymd}" data-date="${ymd}" ${isOn ? 'checked' : ''}>
          <label class="form-check-label" for="day_${ymd}">${isOn ? 'Day On' : 'Day Off'}</label>
          <input type="hidden" name="exam_days[${ymd}]" value="${isOn ? '1' : '0'}">
        </div>
      `;

      row.appendChild(left);
      row.appendChild(right);
      listEl.appendChild(row);
      if (isOn) on++; else off++;
    }

    cTotal.textContent = total.toString();
    cOn.textContent    = on.toString();
    cOff.textContent   = off.toString();

    // change handlers
    listEl.querySelectorAll('.day-toggle').forEach(cb => {
      cb.addEventListener('change', function(){
        const date = this.getAttribute('data-date');
        const hidden = this.closest('.form-check').querySelector(`input[type="hidden"][name="exam_days[${date}]"]`);
        if (hidden) hidden.value = this.checked ? '1' : '0';

        // counters
        const all = listEl.querySelectorAll('.day-toggle').length;
        const onCount = listEl.querySelectorAll('.day-toggle:checked').length;
        cTotal.textContent = all.toString();
        cOn.textContent    = onCount.toString();
        cOff.textContent   = (all - onCount).toString();

        // label
        const lbl = this.nextElementSibling;
        if (lbl && lbl.tagName === 'LABEL') lbl.textContent = this.checked ? 'Day On' : 'Day Off';
      });
    });
  }

  // initial + changes
  buildList();
  s.addEventListener('input', buildList);
  e.addEventListener('input', buildList);
  s.addEventListener('change', buildList);
  e.addEventListener('change', buildList);

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
