<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Quiz Question',
    'icon' => 'fas fa-question-circle',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quiz Question', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
					<li class="active"><a href="<?= base_url('admin/question_quiz') ?>"> Questions </a></li>
					<li><a href="<?php echo '#/question_quiz?m=add&quiz_type=1';?>">Add Question </a></li>
				</ul>
				<div class="tab-content table-responsive no-padding"><div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="classdairy-datatable" width="100%">
					<thead>

						<tr>

							<th nowrap>#</th>

							<th nowrap>Class</th>

							<th nowrap>Subject</th>

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



	$('#classdairy-datatable thead tr').clone(true).appendTo( '#classdairy-datatable thead' );

    $('#classdairy-datatable thead tr:eq(1) th').each( function (i) {

        var title = $(this).text();

        $(this).html( '<input type="text" style="width:120px" placeholder=" '+title+'" />' );

 

        $( 'input', this ).on( 'keyup change', function () {

            if ( table.column(i).search() !== this.value ) {

                table

                    .column(i)

                    .search( this.value )

                    .draw();

            }

        } );

    } );



	var table = $('#classdairy-datatable').DataTable({

		deferRender: true,

		select:{

			style:'single',

			blurable: true

		},

		ajax:{

			url:'<?php echo base_url('admin/question_quiz/data'); ?>',

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

			{data:'subject'},

			{data:'question_eng'},

      		{data:'question_ur'},

			{

				data:'id',

				sortable:false,

				render:function(data, type, row){

					var html = '';

					html += '<div class="btn-group">';

						  html += '<a href="<?php echo '#/question_quiz?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-pencil icon-pencil"></i></a>';

						   if(row.issys == '1'){



						  }else{

							  html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/question_quiz/delete&id='); ?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-secondary btn-sm"><i class="fa fa-trash icon-trash"></i></a>';

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