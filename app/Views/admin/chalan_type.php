<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />

    <!-- Content Header (Page header) -->

    <section class="content-header">

      <h1>

        Chalan Type

        <small></small>

      </h1>

      <ol class="breadcrumb">

        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <li class="active">Chalan Type</li>

      </ol>

    </section>

    <!-- Main content -->

    <section class="content">

      <div class="row">

        <div class="col-xs-12">

          <div class="nav-tabs-custom">

			<ul class="nav nav-tabs">

					<li class="active"><a href="<?= base_url('admin/chalan_type') ?>">Chalan Type</a></li>

					<li><a href="<?= base_url('admin/chalan_type/add') ?>">Add Chalan Type</a></li>

				</ul>

				<div class="tab-content table-responsive no-padding"><div class="col-xs-12">

              <table class="table table-striped table-bordered table-hover" id="chalan-type-datatable" width="100%">

					<thead>

						<tr>

							<th nowrap>#</th>

							<th nowrap>Chalan Type Name</th>

							<th nowrap>Chalan Type Detail</th>

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



	var table = $('#chalan-type-datatable').DataTable({

		deferRender: true,

		select:{

			style:'single',

			blurable: true

		},

		ajax:{

			url:'<?php echo base_url('admin/chalan_type/data'); ?>',

			type:'post',

			data:function(d){

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

			{data:'chalan_type_name'},

			{data:'chalan_type_detail'},

			{

				data:'id',

				sortable:false,

				render:function(data, type, row){

					var html = '';

					html += '<div class="btn-group">';

						  html += '<a href="<?php echo '#/chalan_type?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fa fa-edit icon-pencil"></i></a>';

						

						  if(row.issys == '1'){



						  }else{

							  html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/users/delete&id='); ?>' + data + '\',\'chalan-type-datatable\');" title=" delete" class="btn btn-default btn-xs"><i class="fa fa-trash icon-trash"></i></a>';

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