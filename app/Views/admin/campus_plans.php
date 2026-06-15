<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
   <?= view('components/page_header', [
    'title' => 'Campus Plans',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Campus Plans', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
         <div class="card card-primary card-outline card-tabs">
    		<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/campus_plans') ?>">Campus Plans</a></li>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/campus_plans/add') ?>">Add Campus Plan</a></li>
			</ul>
		<div class="card-body">
		<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="campus-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>INVOICE #</th>
							<th nowrap>Student Limit</th>
							<th nowrap>Fee Limit</th>
							<th nowrap>Bill Amount</th>
							<th nowrap>Bill Expiry</th>
							<th nowrap>Paid Date</th>
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
    </section>
    <!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	var table = $('#campus-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/campus_plans/data'); ?>',
			type:'post',
			data:function(d){
			}
		},
		columns:[
			{
				data:'id',
				className:'select-checkbox',
				render:function(data, type, row){
					return row.sr_no;
				}
			},
			{
				data:'id',
				className:'select-checkbox',
				render:function(data, type, row){
					return row.campus_id+'-'+data;
				}
			},
			{data:'no_of_students'},
			{data:'max_fee'},
			{data:'bill_amount'},
			{data:'expiry'},
			{data:'paid_date'},
			{data:'status'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
					// html += '<a href="<?php echo '#/campus_plans?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fasfa-pencil"></i> Edit Bill</a>';
					html += '<a href="<?php echo '#/campus_bill?id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Print Bill</a>';
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

					   id:rowid//,

				   },

				   function(data){

					   if(data=='success'){

						   toastr.success('change success');

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