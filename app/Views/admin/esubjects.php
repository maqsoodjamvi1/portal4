<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               ESubjects
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">ESubjects</li>
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
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/esubjects') ?>">E Subjects</a></li>
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/esubjects/add') ?>">Add E Subject</a></li>
			</ul>
		<div class="card-body">
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Subject Name</th>
							<th nowrap>Short Name</th>
							<th nowrap>Detail</th>
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
	var table = $('#users-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/esubjects/data'); ?>',
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
			{data:'subject'},
			{data:'short_name'},
			{data:'detail'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
						  html += '<a href="<?php echo '#/subjects?m=add&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fa fa-edit icon-pencil"></i></a>';
						 // html += '<a href="<?php echo '#/users?m=edit_password&user_id=';?>' + data + '" title="change password" class="btn btn-default btn-xs"><i class="fa fa-lock icon-pencil"></i></a>';
						  if(row.issys == '1'){


						  }else{

							  //html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/users/delete&id='); ?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-default btn-xs"><i class="fa fa-trash icon-trash"></i></a>';

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