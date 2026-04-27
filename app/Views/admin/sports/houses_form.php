<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><?= !empty($row) ? 'Edit House' : 'Add House' ?></h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/houses') ?>">Sports Houses</a></li>
          <li class="breadcrumb-item active"><?= !empty($row) ? 'Edit' : 'Add' ?></li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
 <div class="row">
  <div class="col-lg-8">
   <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">House Info</h3></div>
    <form id="houseForm">
      <?= csrf_field() ?>
      <input type="hidden" name="house_id" value="<?= esc($row['house_id'] ?? '') ?>">
      <div class="card-body">
        <div class="form-group">
          <label>House Name</label>
          <input type="text" name="house_name" class="form-control" value="<?= esc($row['house_name'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
          <label>Color (HEX)</label>
          <input type="text" name="color_hex" class="form-control" placeholder="#0d6efd" value="<?= esc($row['color_hex'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status" class="form-control">
            <option value="1" <?= !empty($row) && $row['status']=='1' ? 'selected':'' ?>>Active</option>
            <option value="0" <?= !empty($row) && $row['status']=='0' ? 'selected':'' ?>>Inactive</option>
          </select>
        </div>
      </div>
      <div class="card-footer">
        <a href="<?= base_url('admin/sports/houses') ?>" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
   </div>
  </div>
 </div>
</section>

<script>
$(function(){
  $('#houseForm').on('submit', function(e){
    e.preventDefault();
    $.post("<?= base_url('admin/sports/houses/save') ?>", $(this).serialize(), function(res){
      if(res.ok){ toastr.success('Saved'); window.location = "<?= base_url('admin/sports/houses') ?>"; }
      else { toastr.error('Failed'); }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>