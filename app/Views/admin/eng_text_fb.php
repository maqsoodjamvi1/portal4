<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>
             Fill in The Blanks
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Fill in The Blanks</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
       <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
			  <div class="card-body">
        <div class="col-lg-12">
        <?php if($data){ ?>  
        <table class="table">
          <tr>
            <td><b>Subject:</b> <?php echo $data[0]['subject']; ?></td>
            <td><b>Category:</b> <?php echo $data[0]['subject_category']; ?></td>
            <td><b>Topic:</b> <?php echo $data[0]['topic']; ?></td>
          </tr>
        </table>
        <?php $i = 1;?>
				<?php foreach ($data as $key => $value) { ?>
				<h4><?php echo "<p>(".$i.")</p> ".$value['question_eng']; ?></h4>
				<ul>
					<?php //foreach($value['questionOptions'] as $value2) { ?>
						<li>_______________________________________________</li>
					<?php //} ?>
				</ul>
        <?php $i++; ?>
				<?php } ?>
        <?php } ?>
			</div>
    </div>
    </div>
    <!-- /.box-body -->
    </div>
    <!-- /.box -->
    </div>
  </div>
</section>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>

<?= $this->endSection() ?>