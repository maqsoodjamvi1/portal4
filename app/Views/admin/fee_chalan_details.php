<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Fee Chalan
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">
		Fee Chalan</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
					<li class="active"><a href="<?= base_url('admin/fee_chalan') ?>">
					Fee Chalan</a></li>
					<li><a href="<?= base_url('admin/fee_chalan/add') ?>">Add Fee Chalan </a></li>
					<li><a href="<?= base_url('admin/fee_chalan_pdf') ?>">Print Fee Chalan </a></li>
					<li><a href="<?= base_url('admin/fee_chalan_pay') ?>">Pay Fee Chalan </a></li>
				</ul>
				<div class="tab-content table-responsive no-padding"><div class="col-12">
				<div class="row">
				<div class="col-12">
				<div class="form-group">
                <label>Date range:</label>
                <div class="input-group">
                  <div class="input-group-text">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="text" class="form-control float-end" id="reservation">
                </div>
                <!-- /.input group -->
              </div>
			</div>
			   </div>
              <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Fee Type</th>
							<th nowrap>Student </th>
							<th nowrap>Due Date</th>
							<th nowrap>Issue Date</th>
							<th nowrap>Fee Month</th>
							<th nowrap>Amount</th>
							<th nowrap>Discount</th>
							<th nowrap>Status</th>
							<th nowrap>Paid Date</th>
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
<script type="text/javascript" src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>resource/adminlte/plugins/daterangepicker/daterangepicker.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>resource/adminlte/plugins/daterangepicker/daterangepicker.css">
<script type="text/javascript">
$(function(){
$('#reservation').daterangepicker()
	var table = $('#users-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/fee_chalan_details/data'); ?>',
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
			{data:'fee_name'},
			{data:'student_name'},
			{data:'due_date'},
			{data:'issue_date'},
			{data:'fee_month'},
			{data:'amount'},
			{data:'discount'},
			{data:'status'},
			{data:'paiddate'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
						  html += '<a href="<?php echo '#/fee_chalan?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-edit icon-pencil"></i></a>';
						  if(row.issys == '1'){

						  }else{
							  html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/users/delete&id='); ?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-secondary btn-sm"><i class="fa fa-trash icon-trash"></i></a>';

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