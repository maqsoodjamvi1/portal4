<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<?= view('components/page_header', [
    'title' => 'Class Diary',
    'icon' => 'fas fa-book-open',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Class Diary', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
					<li><a href="<?= base_url('admin/classdairy/add') ?>">Add  Class Diary</a></li>
					<li class="active"><a href="<?= base_url('admin/classdairy_view') ?>">Daily Diary</a></li>
					<li><a href="<?= base_url('admin/classdairy_audio') ?>">Class Diary Audio</a></li>
					<li><a href="<?= base_url('admin/classdairy_audio/add') ?>">Add Class Diary Audio</a></li>
					<li  class="nav-item"><a class="nav-link" href="<?= base_url('admin/classdairy_otherdetail_view') ?>">Diary Other Detail</a></li>
				</ul>
				<div class="tab-content table-responsive no-padding"><div class="col-12">
              <table class="table table-striped table-bordered table-hover" id="classdairy-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Class</th>
             				<th nowrap>Subject</th>
             				<th nowrap>Week</th>
							<th nowrap>Day</th>
							<th nowrap style="max-width: 250px">Diary</th>
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
 <style type="text/css">
   	tr th:first-child{
   	max-width:20px;padding: 0px; margin:0px; 	
   	}
   	tr th:first-child input[type="text"]{

   		width: 10px !important
   	}

   	tr th:last-child{
   	max-width:20px;padding: 0px; margin:0px; 	
   	}
   	tr th:last-child input[type="text"]{

   		width: 10px !important
   	}
   </style>      
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){

	$('#classdairy-datatable thead tr').clone(true).appendTo( '#classdairy-datatable thead' );
    $('#classdairy-datatable thead tr:eq(1) th').each( function (i) {
        var title = $(this).text();
        $(this).html( '<input type="text" style="width:90px" placeholder=" '+title+'" />' );
 
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
		dom: 'Bfrtip',
		buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5' 
        ],
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/classdairy/data'); ?>',
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
			{data:'subject_name'},
			{data:'week_name'},
			{data:'date1'},
      		{data:'detail'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
						  html += '<a href="<?php echo '#/classdairy?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-edit icon-pencil"></i></a>';
						   if(row.issys == '1'){

						  }else{
							  html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/users/delete&id='); ?>' + data + '\',\'classdairy-datatable\');" title=" delete" class="btn btn-secondary btn-sm"><i class="fa fa-trash icon-trash"></i></a>';
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