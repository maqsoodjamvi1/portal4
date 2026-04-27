<?php
$daysName = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

if (empty($sectionsclassinfo)) {
    echo "<div class='btn btn-danger'>No class sections available.</div>";
    return;
}
?>
<table class="table">
    <tr>
        <th></th>
        <?php foreach ($daysName as $day): ?>
            <th style="width: 132px;">
                <input type="hidden" name="dayname[]" value="<?= esc($day) ?>" />
                <?= esc($day) ?><br>
                Set Off <input type="checkbox" id="setclockoff_<?= esc($day) ?>"><br>
                Set to column <input type="checkbox" id="setclock_<?= esc($day) ?>">
            </th>
        <?php endforeach; ?>
    </tr>
    <?php foreach ($sectionsclassinfo as $section): ?>
        <tr>
            <th>
                <?= esc($section['sectionclassname']) ?>
                <input type="hidden" name="section_id[]" value="<?= esc($section['section_id']) ?>" /><br>
                Set to Row <input type="checkbox" id="setclock_<?= esc($section['section_id']) ?>">
            </th>
            <?php foreach ($daysName as $day):
                $school_timings_info = $schoolTimingsInfo[$day][$section['section_id']] ?? null;
                $checkin = $school_timings_info->checkin_timing ?? '';
                $checkout = $school_timings_info->checkout_timing ?? '';
            ?>
            <td>
                <div class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                    <input type="text" class="form-control clockpicker_<?= esc($day) ?> clockpicker_<?= esc($section['section_id']) ?>"
                        placeholder="Check In" name="<?= esc($day) ?>_<?= esc($section['section_id']) ?>_checkin_date"
                        id="<?= esc($day) ?>_<?= esc($section['section_id']) ?>_checkin_date" value="<?= esc($checkin) ?>">
                    <span class="input-group-addon btn btn-default">
                        <span class="far fa-clock"></span>
                    </span>
                </div>
                <div class="input-group clockpicker" data-placement="left" data-align="top" data-autoclose="true">
                    <input type="text" class="form-control clockpickercheckout_<?= esc($day) ?> clockpickercheckout_<?= esc($section['section_id']) ?>"
                        placeholder="Check Out" name="<?= esc($day) ?>_<?= esc($section['section_id']) ?>_checkout_date"
                        id="<?= esc($day) ?>_<?= esc($section['section_id']) ?>_checkout_date" value="<?= esc($checkout) ?>">
                    <span class="input-group-addon btn btn-default">
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
    $('.clockpicker').clockpicker();
    <?php foreach ($daysName as $day): ?>
    $('#setclock_<?= $day ?>').on('click', function() {
        let checkin = $('#<?= $day ?>_<?= $sectionsclassinfo[0]['section_id'] ?>_checkin_date').val();
        let checkout = $('#<?= $day ?>_<?= $sectionsclassinfo[0]['section_id'] ?>_checkout_date').val();
        $('.clockpicker_<?= $day ?>').val(checkin);
        $('.clockpickercheckout_<?= $day ?>').val(checkout);
    });
    $('#setclockoff_<?= $day ?>').on('click', function() {
        $('.clockpicker_<?= $day ?>').val('08:00');
        $('.clockpickercheckout_<?= $day ?>').val('08:00');
    });
    <?php endforeach; ?>

    <?php foreach ($sectionsclassinfo as $section): ?>
    $('#setclock_<?= $section['section_id'] ?>').on('click', function() {
        let checkin = $('#Monday_<?= $section['section_id'] ?>_checkin_date').val();
        let checkout = $('#Monday_<?= $section['section_id'] ?>_checkout_date').val();
        $('.clockpicker_<?= $section['section_id'] ?>').val(checkin);
        $('.clockpickercheckout_<?= $section['section_id'] ?>').val(checkout);
    });
    <?php endforeach; ?>
});
</script>
