<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />

    <!-- Content Header (Page header) -->

    <section class="content-header">

      <h1>

       Inactive Students

        <small></small>

      </h1>

      <ol class="breadcrumb">

        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <li class="active">Inactive Students

        </li>

      </ol>

    </section>



    <!-- Main content -->

    <section class="content">

      <div class="row">

        <div class="col-12">

          <div class="nav-tabs-custom">

			<ul class="nav nav-tabs">

			<li ><a href="<?= base_url('admin/students') ?>">Students</a></li>

					<li class="active"><a href="<?= base_url('admin/students_inactive') ?>">Inative Students</a></li>

					<li><a href="<?= base_url('admin/students/add') ?>">Add Student</a></li>

				</ul>

				<div class="tab-content table-responsive no-padding"><div class="col-12">

              <table class="table table-striped table-bordered table-hover" id="students-datatable" width="100%" style="font-size:10px;">

					<thead>

						<tr>

							<th nowrap>#</th>

							<th nowrap>Picture</th>

							<th nowrap>Reg No</th>

							<th nowrap>Name</th>

							<th nowrap>F Name</th>

							<th nowrap>Class</th>

							<th nowrap>Campus</th>

							<th nowrap>F Contact </th>

							<th nowrap>M Contact </th>

							<th nowrap>E Contact </th>

							<th nowrap>Status </th>		

							<th nowrap>Payable</th>							

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

    <style type="text/css">

    	table.table-bordered th:last-child, table.table-bordered td:last-child{width: 50px;}



    </style>

    <!-- /.content -->

 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>

<script type="text/javascript">

$(function(){



	var table = $('#students-datatable').DataTable({

		

		deferRender: true,

		select:{

			style:'single',

			blurable: true

		},

		ajax:{

			url:'<?php echo base_url('admin/students_inactive/data'); ?>',

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

			{data:'profile_photo'},

			{data:'reg_no'},

			{data:'name'},	

			{data:'f_name'},

			{data:'class'},

			{data:'campus_name'},

			{data:'father_contact'},

			{data:'mother_contact'},

			{data:'emergency_contact'},

			{data:'stdstatus'},

			{data:'payable'},

			



			{

				data:'id',

				sortable:false,

				render:function(data, type, row){

					var html = '';

					html += '<div class="btn-group">';

						  html += '<a href="<?php echo '#/students?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-pencil icon-pencil"></i></a>';

						 

						  if(row.issys == '1'){



						  }else{

							  html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/students/delete&id='); ?>' + data + '\',\'students-datatable\');" title=" delete" class="btn btn-secondary btn-sm"><i class="fa fa-trash icon-trash"></i></a>';

						  }



					html += '</div>';

					html += '<div><a href="<?php echo '#/leaving_certificate?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">Certificate</div></div>';

					html += '<div><a href="<?php echo '#/fee_chalan_single?m=add&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">Chalan</div></div>';

					return html;

				}

			}

		]

		,

		fnDrawCallback:function(oSettings){

			$(".switchchk").bootstrapSwitch({

				onSwitchChange:function(e, state){



				var fieldval = state;

				var $element = $(e.currentTarget);

				var tablename = $element.attr('data-table');

				var fieldname = $element.attr('data-field');

				var rowid = $element.attr('data-pk');

        //alert(rowid);

				if(fieldval){

					fieldval = 1;

				}else{

					fieldval = 0;

				}

				$.post(

				   "<?php echo base_url('admin/ajax/setboolattribute2'); ?>",

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