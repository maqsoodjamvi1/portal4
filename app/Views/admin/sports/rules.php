<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Scoring Rules',
    'icon' => 'fas fa-trophy',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Sports Scoring', 'active' => true],
    ],
]) ?>

<section class="content">
 <div class="row">
  <div class="col-lg-8">
   <div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Points by Position</h3></div>
    <form id="rulesForm">
      <?= csrf_field() ?>
      <div class="card-body" id="ruleRows">
        <?php if(!empty($rows)): foreach($rows as $r): ?>
          <div class="row mb-2 rule">
            <div class="col-md-3"><input class="form-control" name="position[]" type="number" value="<?= (int)$r['position'] ?>" placeholder="Position"></div>
            <div class="col-md-3"><input class="form-control" name="points[]" type="number" value="<?= (int)$r['points'] ?>" placeholder="Points"></div>
            <div class="col-md-2"><button class="btn btn-danger w-100 remove" type="button"><i class="fas fa-trash"></i></button></div>
          </div>
        <?php endforeach; else: ?>
          <div class="row mb-2 rule">
            <div class="col-md-3"><input class="form-control" name="position[]" type="number" value="1"></div>
            <div class="col-md-3"><input class="form-control" name="points[]" type="number" value="5"></div>
            <div class="col-md-2"><button class="btn btn-danger w-100 remove" type="button"><i class="fas fa-trash"></i></button></div>
          </div>
          <div class="row mb-2 rule">
            <div class="col-md-3"><input class="form-control" name="position[]" type="number" value="2"></div>
            <div class="col-md-3"><input class="form-control" name="points[]" type="number" value="3"></div>
            <div class="col-md-2"><button class="btn btn-danger w-100 remove" type="button"><i class="fas fa-trash"></i></button></div>
          </div>
          <div class="row mb-2 rule">
            <div class="col-md-3"><input class="form-control" name="position[]" type="number" value="3"></div>
            <div class="col-md-3"><input class="form-control" name="points[]" type="number" value="1"></div>
            <div class="col-md-2"><button class="btn btn-danger w-100 remove" type="button"><i class="fas fa-trash"></i></button></div>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-footer">
        <button id="addRow" class="btn btn-secondary" type="button"><i class="fas fa-plus"></i> Add Row</button>
        <button class="btn btn-primary float-end" type="submit"><i class="fas fa-save"></i> Save</button>
      </div>
    </form>
   </div>
  </div>
 </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

$(function(){
  $('#addRow').on('click', function(){
    $('#ruleRows').append(`
      <div class="row mb-2 rule">
        <div class="col-md-3"><input class="form-control" name="position[]" type="number" placeholder="Position"></div>
        <div class="col-md-3"><input class="form-control" name="points[]" type="number" placeholder="Points"></div>
        <div class="col-md-2"><button class="btn btn-danger w-100 remove" type="button"><i class="fas fa-trash"></i></button></div>
      </div>
    `);
  });
  $('#ruleRows').on('click','.remove', function(){ $(this).closest('.rule').remove(); });

  $('#rulesForm').on('submit', function(e){
    e.preventDefault();
    $.post("<?= base_url('admin/sports/rules/save') ?>", $(this).serialize(), function(res){
      if(res.ok){ toastr.success('Saved'); } else { toastr.error('Failed'); }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
