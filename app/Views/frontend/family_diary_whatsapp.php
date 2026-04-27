<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fab fa-whatsapp mr-1"></i> Family Diary — WhatsApp</h3>
    </div>
    <div class="card-body">
      <?php helper('url'); ?>

      <?php if (!empty($sectionsclassinfo)): ?>
        <div class="form-group">
          <label>Class Sections</label>
          <select class="form-control" id="cls_sec_id">
            <option value="">All</option>
            <?php foreach ($sectionsclassinfo as $s): ?>
              <option value="<?= esc($s['section_id'] ?? '') ?>">
                <?= esc($s['sectionclassname'] ?? '') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <table class="table table-bordered table-striped" id="fdwTable" style="width:100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Father / Students</th>
            <th>Father Contact</th>
            <th>WhatsApp</th>
            <th>Mother Contact</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>

<script>
$(function(){
  const table = $('#fdwTable').DataTable({
    processing: true,
    serverSide: true,
    searching: true,
    ajax: {
      url: "<?= base_url('frontend/family_diary_whatsapp/data') ?>",
      type: "POST",
      data: function (d) {
        <?php if (function_exists('csrf_token')): ?>
          d['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
        <?php endif; ?>
        d.cls_sec_id = $('#cls_sec_id').val() || '';
      }
    },
    columns: [
      { data: 'id',         orderable: false },
      { data: 'f_name' },
      { data: 'f_contacts' },
      { data: 'w_contacts', orderable: false },
      { data: 'm_contacts' }
    ]
  });

  $(document).on('change', '#cls_sec_id', function(){
    table.ajax.reload();
  });
});
</script>

<?= $this->endSection() ?>
