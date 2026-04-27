<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Subjects Categories Topic
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Subjects Categories Topic</li>
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
					<li class="nav-item"><a  class="nav-link active" href="<?= base_url('admin/subject_cat_topic') ?>">Subject Categories Topic</a></li>
					<li  class="nav-item"><a  class="nav-link" href="<?= base_url('admin/subject_cat_topic/add') ?>">Add Subject Category Topic</a></li>
				</ul>
			<div class="card-body">
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Subject</th>
							<th nowrap>Category</th>
						    <th nowrap>Topic</th>
						    <!-- <th nowrap>Detail</th> -->
						    <th>Ws Count</th>
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
	$('#users-datatable thead tr').clone(true).appendTo( '#users-datatable thead' );
    $('#users-datatable thead tr:eq(1) th').each( function (i) {
        var title = $(this).text();
        $(this).html( '<input type="text" style="width:120px" placeholder=" '+title+'" />' );
        $( 'input', this ).on( 'keyup change', function () {
            if ( table.column(i).search() !== this.value ) {
                table
                    .column(i)
                    .search( this.value )
                    .draw();
            }
        });
    });

	var table = $('#users-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/subject_cat_topic/data'); ?>',
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
			{data:'cat_name'},
			{data:'topic'},
			// {data:'detail'},
			{data:'ws_counts'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group"><button type="button" class="btn btn-default btn-sm">Action</button><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown"><span class="sr-only">Toggle Dropdown</span></button><div style="padding:10px;" class="dropdown-menu" role="menu">';
					html += '<a href="<?php echo '#/subject_cat_topic?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs dropdown-item"><i class="fa fa-edit icon-pencil"></i></a>';

					html += ' <a target="_blank" href="<?php echo '#/question_text_mcqs?m=add&topic_id=';?>' + data + '" title="edit" class="btn btn-default btn-xs dropdown-item"> View/Add Questions </a>'; 
					html += ' <a target="_blank" href="<?php echo '#/worksheet?m=add&topic_id=';?>' + data + '" title="edit" class="btn btn-default btn-xs dropdown-item"> Add worksheet </a>'; 
					html += ' <a target="_blank" href="<?php echo '#/worksheet_info?m=add&topic_id=';?>' + data + '" title="edit" class="btn btn-default btn-xs dropdown-item"> Add Worksheet Info </a>'; 
					html += ' <a target="_blank" href="<?php echo '#/worksheet_meta_info?m=add&topic_id=';?>' + data + '" title="edit" class="btn btn-default btn-xs dropdown-item"> Add Worksheet Meta Info </a>'; 
						  if(row.issys == '1'){

						  }else{
							 // html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php //echo site_url('c=subject_cat&m=delete&id=');?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-default btn-xs dropdown-item"><i class="fa fa-trash icon-trash"></i></a>';

						  }
					html += '</div></div>'; 	    
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