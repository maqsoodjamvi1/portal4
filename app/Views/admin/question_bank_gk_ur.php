<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />

    <!-- Content Header (Page header) -->

    <section class="content-header">

      <h1>

        Question Bank GK

        <small></small>

      </h1>

      <ol class="breadcrumb">

        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <li class="active"> Question Bank GK</li>

      </ol>

    </section>

    <!-- Main content -->

    <section class="content" style="direction: rtl;">

      <div class="row">

        <div class="col-xs-12">

          <div class="nav-tabs-custom">

			<div class="tab-content table-responsive no-padding"><div class="col-xs-12">

        <table class="table">

          <tr>

            <td><b>Class:</b> <?php echo $data[0]['class_name']; ?></td>

            <td><b>Subject:</b> <?php echo $data[0]['subject']; ?></td>

            <td><b>Category:</b> <?php echo $data[0]['subject_category']; ?></td>

            <td><b>Topic:</b> <?php echo $data[0]['topic']; ?></td>

            <td><b>Skills:</b> <?php echo $data[0]['topic_skills']; ?></td>

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

<script type="text/javascript">

$(function(){

});

</script>

<?= $this->endSection() ?>