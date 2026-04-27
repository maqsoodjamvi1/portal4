<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Video Lecture
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active"> Video Lecture</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">

					<li class="active"><a href="<?= base_url('admin/video_lecture') ?>"> Video Lecture </a></li>

					<li><a href="<?= base_url('admin/video_lecture/add') ?>">Add Video Lecture </a></li>

				</ul>

				<div class="tab-content table-responsive no-padding"><div class="col-xs-12">

              <table class="table table-striped table-bordered table-hover" id="activity-datatable" width="100%">

					<thead>

						<tr>

							<th nowrap>#</th>

							<th nowrap>Class</th>

							<th nowrap>Question English</th>

             				<th nowrap>Question Urdu</th>

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

	var table = $('#activity-datatable').DataTable({

		deferRender: true,

		select:{

			style:'single',

			blurable: true

		},

		ajax:{

			url:'<?php echo base_url('admin/video_lecture/data'); ?>',

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

			{data:'class_name'},

			{data:'question_eng'},

      		{data:'question_ur'},

			{

				data:'id',

				sortable:false,

				render:function(data, type, row){

					var html = '';

					html += '<div class="btn-group">';

						  html += '<a href="<?php echo '#/video_lecture?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fa fa-pencil icon-pencil"></i></a>';

						   if(row.issys == '1'){



						  }else{

							  html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/video_lecture/delete&id='); ?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-default btn-xs"><i class="fa fa-trash icon-trash"></i></a>';

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