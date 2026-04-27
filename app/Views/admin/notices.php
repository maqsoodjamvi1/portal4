<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Notices
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Notices</li>
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
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
			<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/notices') ?>">Notices</a></li>
			<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/notices/add') ?>">Add Notices</a></li>
			</ul>
		<div class="card-body">
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="classes-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Notice Name</th>
							<th nowrap>Notice Date</th>
							<th nowrap>Notice Detail</th>
							<th nowrap>Notice Audio</th>
							<th nowrap>Status</th>
							<th nowrap>Operation</th>
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
  	</div>
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
			url:'<?php echo base_url('admin/notices/data'); ?>',
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
			{data:'notice_name'},
			{data:'notice_date'},
      		{data:'notice_detail'},
      		{data:'notice_audio'},
      			{
				data:'status',
				render:function(data, type, row){
					var status_1 = '';
					if(data == '1') status_1 = 'checked="checked"';
					return '<label class="switch"><input type="checkbox" ' + status_1 + ' class="switch-small switchchk"  data-on-text="Active" data-off-text="Diactive" data-table="notices" data-field="status" data-size="mini" data-pk="' + row.id + '" /></label>';		
				}
			},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
					html += '<a href="<?php echo '#/notices?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fa fa-edit icon-pencil"></i></a>';
					html += '</div>';
					return html;
				}
			}
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
				   "<?php echo base_url('admin/ajax/setboolattributenotice'); ?>",
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