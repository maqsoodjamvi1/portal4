<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>

<?= view('components/bulk_students_header', [
  'title' => 'Class Change',
  'subtitle' => 'Class Change'
]) ?>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header pb-0">
        <?= view('components/bulk_students_tabs', ['active' => 'class']) ?>
      </div>

    <div class="">
    <div class="col-lg-6 form-group">
      <label for="class"><strong>Class</strong></label><br>
       <select id="filter_cls_sec_id" class="form-control">
  <option value="0">All Class Sections</option>
  <?php foreach ($sectionsclassinfo as $sec): 
        $val  = (int)($sec['cls_sec_id'] ?? $sec['section_id'] ?? 0);
        $text = $sec['sectionclassname']
             ?? (($sec['class_name'] ?? '').' ('.($sec['section_name'] ?? '').')'); ?>
    <option value="<?= esc($val) ?>"><?= esc($text) ?></option>
  <?php endforeach; ?>
</select>
<input type="hidden" name="current_discount" id="current_discount" value="<?= $discounted_amount ?? 0 ?>">
    </div>
    </div>
      <div class="card-body">

      <div id="studentsList"></div>
      </div>
    </div>
  </div>
    </div>
    <!-- /.box-body -->
    </div>
    <!-- /.box -->
    </div>
    </div>
    </section>
    <style type="text/css">
    	table.table-bordered th:last-child, table.table-bordered td:last-child{width: 50px;}
    </style>
    <!-- /.content -->
<script type="text/javascript">
(function(){
  function loadBySection(id){
    // show the loader if present
    var $loader = $("#loader-1");
    if ($loader.length) $loader.removeClass("d-none");

    $.ajax({
      url: "<?= base_url('admin/studentsbulk/data') ?>",
      type: "POST",
      data: {
        cls_sec_id: id || 0,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
      },
      success: function(html){
        $("#studentsList").html(html);
      },
      error: function(){
        toastr && toastr.error ? toastr.error("Failed to load students.") : alert("Failed to load students.");
      },
      complete: function(){
        if ($loader.length) $loader.addClass("d-none");
      }
    });
  }

  // change handler for your filter
  $("#filter_cls_sec_id").on("change", function(){
    loadBySection(this.value);
  });

  // initial load with current selection (or all if empty)
  loadBySection($("#filter_cls_sec_id").val());
})();
</script>

<?= $this->endSection() ?>