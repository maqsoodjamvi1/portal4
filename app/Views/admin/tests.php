<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<script src="<?php echo base_url();?>resource/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <!-- Content Header (Page header) -->
  <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-8">
            <h1>
               Tests
            </h1>
          </div>
          <div class="col-sm-4">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Tests</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a  class="nav-link active" href="<?= base_url('admin/tests') ?>"> Tests </a></li>
				<li  class="nav-item"><a  class="nav-link" href="<?= base_url('admin/tests/add') ?>">Add Tests </a></li> 
			</ul>
			<div class="card-body">	
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="classdairy-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>Class</th>
							<th nowrap>Subject</th>
							<th nowrap>Test Series</th>
							<th nowrap>Test Date</th>
							<th nowrap>Total Marks</th>
							<th nowrap>Syllabus</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table></div></div>
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
	 
	var table = $('#classdairy-datatable').DataTable({
		deferRender: true,
		"pageLength": 100,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/tests/data'); ?>',
			type:'post',
			data:function(d){
			//d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
			}
		},
		columns:[	
			{data:'class'},
			{data:'subject'},
			{data:'test_series'},
			{data:'test_date'},
			{data:'total_marks'},
			{data:'syllabus'},		
		],
		fnDrawCallback:function(oSettings){
			$(".switchchk").bootstrapSwitch({
				onSwitchChange:function(e, state){
				var fieldval = state;
				var $element = $(e.currentTarget);
				var tablename = $element.attr('data-table');
				var fieldname = $element.attr('data-field');
				var rowid = $element.attr('data-pk');
				if(fieldval){
					fieldval = 0;
				}else{
					fieldval = 1; 
				}
				$.post(
				   "<?php echo base_url('admin/ajax/setboolIndexable'); ?>",
				   {
					   act:'upsort',
					   tbname:tablename,
					   tbfield:fieldname,
					   tbfieldvalue:fieldval,
					   id:rowid//,
					   // csrf_test_name:$.cookie(CSRF_COOKIE_NAME)
				   
				   },
				   function(data){
					//alert(data);
					   if(data=='success'){
						   toastr.success('change success');
						   
					   }else{
						   toastr.error('change error');
					   }
				   }
				  );	
				}
			});			
		}
	});
});
</script>

<?= $this->endSection() ?>