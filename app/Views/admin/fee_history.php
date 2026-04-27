<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
  <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
              Fee History
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Fee History</li>
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
					<strong>Student Name:</strong><br />
					<?php   echo $studentsinfo->first_name." ".$studentsinfo->last_name."<br>"; ?>
					<strong>UnPaid Total:</strong> <?php echo $unpaidtotal->total; ?>
					<br>
					<br>
          <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>Paid Date</th>
							<th nowrap>Amount</th>
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
     </div>
	 </div>
  </section>
  <!-- /.content -->
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	var table = $('#users-datatable').DataTable({
		deferRender: true,
		 "pageLength": 50,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo site_url('c=fee_history&m=data&student_id='.$student_id);?>',
			type:'post',
			data:function(d){
				//d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
			}
		},
		columns:[
			{data:'paiddate'},
			{data:'amount'},
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
					fieldval = 1;
				}else{
					fieldval = 0;
				}

				$.post(
				   "<?php echo base_url('admin/ajax/setboolattribute'); ?>",
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