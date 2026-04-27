<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/datatables/dataTables.bootstrap4.min.css') ?>">

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Enter Results</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('admin/sports/events') ?>">Sports Events</a></li>
          <li class="breadcrumb-item active">Results</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
 <div class="row">
  <div class="col-lg-5">
    <div class="card card-outline card-info">
      <div class="card-header"><h3 class="card-title">Participants</h3></div>
      <div class="card-body table-responsive p-0" style="max-height:520px">
        <table class="table table-striped table-sm">
          <thead><tr><th>#</th><th>Name/Team</th><th>House</th><th>Pick</th></tr></thead>
          <tbody id="entryRows">
            <?php $i=1; foreach(($entries??[]) as $e): $name = $e['student_id']? trim(($e['first_name']??'').' '.($e['last_name']??'')) : $e['team_name']; ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= esc($name) ?></td>
                <td><?= esc($e['house_id']) ?></td>
                <td>
                  <button class="btn btn-xs btn-outline-primary pick" 
                          data-student="<?= (int)$e['student_id'] ?>" 
                          data-team="<?= esc($e['team_name']) ?>" 
                          data-house="<?= (int)$e['house_id'] ?>">Pick</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Positions</h3></div>
      <div class="card-body">
        <form id="resultForm">
          <?= csrf_field() ?>
          <input type="hidden" name="event_id" value="<?= (int)$eventId ?>">
          <div id="positionRows">
            <?php
              // Pre-fill existing results if any
              $prefill = $existing ?? [];
              $maxPos = 3; // adjust as per rules if you want
              for($p=1; $p<=$maxPos; $p++):
                $row = array_values(array_filter($prefill, fn($r)=> (int)$r['position']===$p ))[0] ?? null;
            ?>
              <div class="border rounded p-2 mb-2 position-row" data-pos="<?= $p ?>">
                <div class="d-flex align-items-center">
                  <span class="badge badge-secondary mr-2" style="min-width:34px">#<?= $p ?></span>
                  <input type="hidden" name="positions[<?= $p ?>][position]" value="<?= $p ?>">
                  <input type="hidden" class="house_id" name="positions[<?= $p ?>][house_id]" value="<?= esc($row['house_id'] ?? '') ?>">
                  <input type="hidden" class="student_id" name="positions[<?= $p ?>][student_id]" value="<?= esc($row['student_id'] ?? '') ?>">
                  <input type="text" class="form-control form-control-sm mr-2 winner_text" placeholder="Student/Team" 
                         value="<?= esc($row['student_id'] ? '' : ($row['team_name'] ?? '')) ?>" 
                         name="positions[<?= $p ?>][team_name]">
                  <label class="mb-0 mr-2">Tie</label>
                  <input type="checkbox" name="positions[<?= $p ?>][rank_shared]" value="1" <?= !empty($row['rank_shared'])?'checked':'' ?>>
                  <button type="button" class="btn btn-sm btn-outline-danger ml-2 clear">Clear</button>
                </div>
                <small class="text-muted pick-hint">Tip: click “Pick” on the left to fill this row.</small>
              </div>
            <?php endfor; ?>
          </div>
          <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Save Results</button>
        </form>
      </div>
    </div>
  </div>
 </div>
</section>

<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';
let currentPos = 1;

$(function(){
  // Clicking "Pick" fills the next empty position
  $('#entryRows').on('click','.pick', function(){
    const s = $(this).data('student')||'';
    const t = $(this).data('team')||'';
    const h = $(this).data('house')||'';
    // find first empty row
    const $row = $('.position-row').filter(function(){
      return !$(this).find('.house_id').val();
    }).first();
    if($row.length===0){ toastr.info('All positions filled'); return; }
    $row.find('.house_id').val(h);
    $row.find('.student_id').val(s);
    if(!s){ $row.find('.winner_text').val(t); } else { $row.find('.winner_text').val(''); }
  });

  $('.position-row').on('click','.clear', function(){
    const $r = $(this).closest('.position-row');
    $r.find('.house_id').val('');
    $r.find('.student_id').val('');
    $r.find('.winner_text').val('');
    $r.find('input[type=checkbox]').prop('checked', false);
  });

  $('#resultForm').on('submit', function(e){
    e.preventDefault();
    $.post("<?= base_url('admin/sports/results/save') ?>", $(this).serialize(), function(res){
      if(res.ok){ toastr.success('Saved'); } else { toastr.error('Failed'); }
    }, 'json');
  });
});
</script>

<?= $this->endSection() ?>
