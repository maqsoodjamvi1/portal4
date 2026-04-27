<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               CI Session View
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">CI Session View</li>
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
			
			<div class="card-body"><div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="academic-session-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Date</th>
              				<th nowrap>IP</th>
             				<th nowrap>Campus</th>
             				<th nowrap>User</th>
             				<th nowrap>Operation</th>
             			</tr>
					</thead>
					<tbody>
					</tbody>
				</table></div></div>
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
	var sessionid = <?php echo $this->session->userdata('member_sessionid'); ?>   
	var table = $('#academic-session-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/ci_session_view/data'); ?>',
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
			{data:'timestamp'},
      		{data:'ip_address'},
      		{data:'campusid'},
      		{data:'userid'},
      		{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					 html += '<div class="btn-group">';
					 if(row.id == sessionid){ 
						  html += '<a href="<?php echo '#/academic_session?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fa fa-edit icon-pencil"></i></a>';
						  }else{
							 // html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/users/delete&id='); ?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-default btn-xs"><i class="fa fa-trash icon-trash"></i></a>';
						  }

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
				   "<?php echo base_url('admin/ajax/setboolattribute'); ?>",
				   {
					   act:'upsort',
					   tbname:tablename,
					   tbfield:fieldname,
					   tbfieldvalue:fieldval,
					   id:rowid,
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