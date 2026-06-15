<?php
helper('school');

$daysName = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

if (empty($sectionsclassinfo)) {
    echo "<div class='btn btn-danger'>No class sections available.</div>";
    return;
}

$firstSectionId = (int) ($sectionsclassinfo[0]['section_id'] ?? 0);
$dayStatus = [];
foreach ($daysName as $day) {
    $info = $schoolTimingsInfo[$day][$firstSectionId] ?? null;
    $checkin  = $info->checkin_timing ?? '';
    $checkout = $info->checkout_timing ?? '';
    $dayStatus[$day] = [
        'working' => isSchoolTimingWorkingDay($checkin, $checkout),
        'label'   => schoolTimingDayStatusLabel($checkin, $checkout),
    ];
}
?>
<style>
.st-day-hint { font-size: .7rem; color: #6c757d; font-weight: normal; margin-top: .2rem; }
.st-day-badge { display: inline-block; font-size: .72rem; font-weight: 600; padding: .15rem .45rem; border-radius: .25rem; margin-top: .25rem; }
.st-day-badge--working { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.st-day-badge--off { background: #e9ecef; color: #495057; border: 1px solid #dee2e6; }
.st-timing-cell--off { background: #f8f9fa; }
.st-timing-cell--working { background: #fff; }
</style>
<table class="table school-timing-grid" id="school-timing-grid">
    <tr>
        <th></th>
        <?php foreach ($daysName as $day):
            $st = $dayStatus[$day];
            $badgeClass = $st['working'] ? 'st-day-badge--working' : 'st-day-badge--off';
        ?>
            <th style="width: 132px;" class="st-day-col" data-day="<?= esc($day) ?>">
                <input type="hidden" name="dayname[]" value="<?= esc($day) ?>" />
                <?= esc($day) ?><br>
                <span class="st-day-badge <?= $badgeClass ?>" id="st_badge_<?= esc($day) ?>"><?= esc($st['label']) ?></span>
                <div class="st-day-hint">Working = different in/out · Off = same</div>
                <label class="small mb-0 d-block mt-1">
                    <input type="checkbox" class="st-set-off" id="setclockoff_<?= esc($day) ?>" data-day="<?= esc($day) ?>"> Set Off
                </label>
                <label class="small mb-0 d-block">
                    <input type="checkbox" class="st-set-col" id="setclock_<?= esc($day) ?>" data-day="<?= esc($day) ?>"> Set to column
                </label>
            </th>
        <?php endforeach; ?>
    </tr>
    <?php foreach ($sectionsclassinfo as $section):
        $sectionId = (int) $section['section_id'];
    ?>
        <tr>
            <th>
                <?= esc($section['sectionclassname']) ?>
                <input type="hidden" name="section_id[]" value="<?= esc($sectionId) ?>" /><br>
                <label class="small mb-0">
                    <input type="checkbox" class="st-set-row" id="setclock_<?= esc($sectionId) ?>" data-section-id="<?= esc($sectionId) ?>"> Set to Row
                </label>
            </th>
            <?php foreach ($daysName as $day):
                $school_timings_info = $schoolTimingsInfo[$day][$sectionId] ?? null;
                $checkin  = $school_timings_info->checkin_timing ?? '';
                $checkout = $school_timings_info->checkout_timing ?? '';
                $cellOff  = ! isSchoolTimingWorkingDay($checkin, $checkout);
            ?>
            <td class="st-timing-cell <?= $cellOff ? 'st-timing-cell--off' : 'st-timing-cell--working' ?>"
                data-day="<?= esc($day) ?>" data-section-id="<?= esc($sectionId) ?>">
                <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
                    <input type="text" class="form-control st-checkin clockpicker_<?= esc($day) ?> clockpicker_<?= esc($sectionId) ?>"
                        placeholder="Check In" name="<?= esc($day) ?>_<?= esc($sectionId) ?>_checkin_date"
                        id="<?= esc($day) ?>_<?= esc($sectionId) ?>_checkin_date" value="<?= esc($checkin) ?>"
                        data-day="<?= esc($day) ?>" data-section-id="<?= esc($sectionId) ?>">
                    <span class="input-group-text btn btn-secondary">
                        <span class="far fa-clock"></span>
                    </span>
                </div>
                <div class="input-group clockpicker" data-bs-placement="left" data-align="top" data-autoclose="true">
                    <input type="text" class="form-control st-checkout clockpickercheckout_<?= esc($day) ?> clockpickercheckout_<?= esc($sectionId) ?>"
                        placeholder="Check Out" name="<?= esc($day) ?>_<?= esc($sectionId) ?>_checkout_date"
                        id="<?= esc($day) ?>_<?= esc($sectionId) ?>_checkout_date" value="<?= esc($checkout) ?>"
                        data-day="<?= esc($day) ?>" data-section-id="<?= esc($sectionId) ?>">
                    <span class="input-group-text btn btn-secondary">
                        <span class="far fa-clock"></span>
                    </span>
                </div>
            </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
</table>

<script>
$(function() {
    var firstSectionId = <?= (int) $firstSectionId ?>;

    function normalizeTime(t) {
        t = (t || '').trim();
        if (!t) return '';
        var m = t.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/);
        if (m) {
            return ('0' + m[1]).slice(-2) + ':' + m[2] + ':' + ('0' + (m[3] || '0')).slice(-2);
        }
        return t;
    }

    function isWorkingDay(checkin, checkout) {
        checkin = normalizeTime(checkin);
        checkout = normalizeTime(checkout);
        if (!checkin || !checkout) return false;
        return checkin !== checkout;
    }

    function updateCellStyle($cell) {
        var cin = $cell.find('.st-checkin').val();
        var cout = $cell.find('.st-checkout').val();
        $cell.toggleClass('st-timing-cell--off', !isWorkingDay(cin, cout));
        $cell.toggleClass('st-timing-cell--working', isWorkingDay(cin, cout));
    }

    function updateDayBadge(day) {
        var $first = $('#' + day + '_' + firstSectionId + '_checkin_date');
        var cin = $first.val();
        var cout = $('#' + day + '_' + firstSectionId + '_checkout_date').val();
        var working = isWorkingDay(cin, cout);
        var $badge = $('#st_badge_' + day);
        $badge.text(working ? 'Working' : 'Off')
            .toggleClass('st-day-badge--working', working)
            .toggleClass('st-day-badge--off', !working);
    }

    function updateAllBadges() {
        <?php foreach ($daysName as $day): ?>
        updateDayBadge('<?= $day ?>');
        <?php endforeach; ?>
    }

    $('.clockpicker').clockpicker();

    $('#school-timing-grid').on('change keyup', '.st-checkin, .st-checkout', function() {
        var $cell = $(this).closest('.st-timing-cell');
        updateCellStyle($cell);
        updateDayBadge($(this).data('day'));
    });

    $('#school-timing-grid').on('change', '.st-set-col', function() {
        if (!this.checked) return;
        var day = $(this).data('day');
        var checkin = $('#' + day + '_' + firstSectionId + '_checkin_date').val();
        var checkout = $('#' + day + '_' + firstSectionId + '_checkout_date').val();
        $('.clockpicker_' + day).val(checkin);
        $('.clockpickercheckout_' + day).val(checkout);
        $('.st-timing-cell[data-day="' + day + '"]').each(function() { updateCellStyle($(this)); });
        updateDayBadge(day);
        this.checked = false;
    });

    $('#school-timing-grid').on('change', '.st-set-off', function() {
        if (!this.checked) return;
        var day = $(this).data('day');
        var fallback = '';
        $('.clockpicker_' + day).each(function() {
            var v = ($(this).val() || '').trim();
            if (v) { fallback = v; return false; }
        });
        if (!fallback) {
            if (window.toastr) toastr.warning('Set check-in on one row first, or enter a time.');
            this.checked = false;
            return;
        }
        $('.st-timing-cell[data-day="' + day + '"]').each(function() {
            var $cell = $(this);
            var $cin = $cell.find('.st-checkin');
            var $cout = $cell.find('.st-checkout');
            var cin = ($cin.val() || '').trim() || fallback;
            $cin.val(cin);
            $cout.val(cin);
            updateCellStyle($cell);
        });
        updateDayBadge(day);
        this.checked = false;
    });

    $('#school-timing-grid').on('change', '.st-set-row', function() {
        if (!this.checked) return;
        var sectionId = $(this).data('section-id');
        var checkin = $('#Monday_' + sectionId + '_checkin_date').val();
        var checkout = $('#Monday_' + sectionId + '_checkout_date').val();
        $('.clockpicker_' + sectionId).val(checkin);
        $('.clockpickercheckout_' + sectionId).val(checkout);
        $('.st-timing-cell[data-section-id="' + sectionId + '"]').each(function() { updateCellStyle($(this)); });
        updateAllBadges();
        this.checked = false;
    });

    updateAllBadges();
});
</script>
