<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<script src="<?php echo base_url();?>resource/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <?= view('components/page_header', [
    'title' => 'School Timing Type',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'School Timing Type', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
        	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/school_timming_type') ?>">School Timing Type</a></li>
				<li class="nav-item"><a class="nav-link " href="<?= base_url('admin/school_timming_type/add') ?>">Add School Timming Type</a></li> 
			</ul>
			<div class="card-body">
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Type Name</th>
							<th nowrap>Short Name</th>
				            <th nowrap>Status</th>
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
	var table = $('#users-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},

		ajax:{
			url:'<?php echo base_url('admin/school_timming_type/data'); ?>',
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
	  {data:'type_name'},
      {data:'short_name'},
      {
		data:'status',
		render:function(data, type, row){

		var status_1 = '';
		if(data == '1') status_1 = 'checked="checked"';
		return '<input type="checkbox" ' + status_1 + ' class="switch-small switchchk"  data-on-text="Active" data-off-text="Inactive" data-table="school_timing_types" data-field="status" data-size="mini" data-pk="' + row.id + '" />';


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
		   "<?php echo base_url('admin/ajax/setboolattributeSchoolType'); ?>",
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
				   location.reload();
			   }else{
				   toastr.error('change error');
			   }
		   });
		}
	});
		}
	});
});
</script>

<?= $this->endSection() ?>