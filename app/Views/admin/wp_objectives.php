<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Objectives
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Objectives</li>
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
				<ul class="nav nav-tabs">
					<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/wp_objectives') ?>">Objectives</a></li>
					<li class="nav-item"><a  class="nav-link" href="<?= base_url('admin/wp_objectives/add') ?>">Add Objectives</a></li>
				</ul>
			<div class="card-body">
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="classes-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Objectives</th>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
			</div>
          </div>
        </div>
		</div>	
        <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </section>
    <!-- /.content -->
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	var table = $('#classes-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/wp_objectives/data'); ?>',
			type:'post',
			data:function(d){
				//d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
			}
		},
		columns:[
			{
				data:'id',
				className:'select-checkbox',
				render:function(data, type, row){
					return data;
				}
			},
			{data:'objective'},
		]
	});
});
</script>

<?= $this->endSection() ?>