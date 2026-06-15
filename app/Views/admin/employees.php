<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Employees',
    'icon' => 'fas fa-id-badge',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employees', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
					<li class="active"><a href="<?= base_url('admin/employees') ?>">Employees</a></li>
					<li><a href="<?= base_url('admin/employees/add') ?>">Add Employees</a></li>
				</ul>
				<div class="tab-content table-responsive no-padding"><div class="col-12">
              <table class="table table-striped table-bordered table-hover" id="employees-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>First Name</th>
							<th nowrap>Last Name</th>
							<th nowrap>Status</th>
							<th nowrap>Mobile No</th>
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
    </section>
    <!-- /.content -->
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	var table = $('#employees-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/employees/data'); ?>',
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
			{data:'first_name'},
			{data:'last_name'},
			{
				data:'status',
				render:function(data, type, row){

					var status_1 = '';
					if(data == '1') status_1 = 'checked="checked"';
					return '<input type="checkbox" ' + status_1 + ' class="switch-small switchchk"  data-on-text="Active" data-off-text="Diactive" data-table="employees" data-field="status" data-size="mini" data-pk="' + row.id + '" />';
				}
			},
			
 	    {data:'mobile_no'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
						  html += '<a href="<?php echo '#/employees?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-edit icon-pencil"></i></a>';
						
						  if(row.issys == '1'){

						  }else{
							  html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/employees/delete&id='); ?>' + data + '\',\'employees-datatable\');" title=" delete" class="btn btn-secondary btn-sm"><i class="fa fa-trash icon-trash"></i></a>';
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
				   "<?php echo base_url('admin/ajax/setboolattributeteachers'); ?>",
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

<?= $this->endSection() ?>