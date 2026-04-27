<section class="section2">
    <div class="table-box">
        <table class="table" style="margin-bottom:0px;">
            <thead>
                <tr class="header">
                    <th></th>
                    <?php if (!empty($wp_objectives_info)) : ?>
                        <?php foreach ($wp_objectives_info as $wp_objectives_value) : ?>
                            <th style="text-align:center;">
                                <input type="hidden" name="subjects[]" value="<?= esc($wp_objectives_value->obj_id) ?>" />
                                <?= esc($wp_objectives_value->objective) ?>
                            </th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($subjectclassinfo)) : ?>
                    <?php foreach ($subjectclassinfo as $subjectvalue) : ?>
                        <tr>
                            <th style="line-height:1;">
                                <input type="hidden" name="sections[]" value="<?= esc($subjectvalue['subject_id']) ?>" />
                                <?= esc($subjectvalue['subject_name']) ?>
                            </th>
                            <?php if (!empty($wp_objectives_info)) : ?>
                                <?php foreach ($wp_objectives_info as $wp_objectives_value) : ?>
                                    <td style="text-align:center;vertical-align:middle;padding:3px 8px;line-height:1;">
                                        <input type="checkbox"
                                            class="setSecSub setlock_<?= esc($class_id) ?> setlock_<?= esc($subjectvalue['subject_id']) ?>_<?= esc($wp_objectives_value->obj_id) ?>"
                                            name="<?= esc($class_id) ?>_<?= esc($subjectvalue['subject_id']) ?>_<?= esc($wp_objectives_value->obj_id) ?>_section_subjects[]"
                                            value="<?= esc($class_id) ?>_<?= esc($subjectvalue['subject_id']) ?>_<?= esc($wp_objectives_value->obj_id) ?>"
                                            <?= (isset($existing_objectives[$subjectvalue['subject_id']][$wp_objectives_value->obj_id]) && $existing_objectives[$subjectvalue['subject_id']][$wp_objectives_value->obj_id] == 1) ? 'checked' : '' ?>
                                        />
                                    </td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
    section { overflow: hidden; }
    .table-box { overflow: auto; height: 500px; }
    table { width: 100%; border-collapse: collapse; }
    table th { padding: 7px; background-color: #ddd; position: sticky; left: 0; }
</style>

<script type="text/javascript">
    $(function () {
        $(".setSecSub").on("change", function () {
            let status = this.checked ? 1 : 0;
            let section_subject_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "<?= base_url('admin/wp-subjects-objectives/update') ?>",
                data: { section_subject_id: section_subject_id, status: status },
                success: function (res) {
                    let json = typeof res === 'object' ? res : $.parseJSON(res);
                    if (json.success) {
                        toastr.success(json.msg);
                    }
                }
            });
        });
    });
</script>
