<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Terms Session',
    'icon' => 'fas fa-calendar',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Terms Session', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
         <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item "><a class="nav-link active" href="<?php echo base_url('admin/terms_session');?>">Terms Session</a></li>
				<li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/terms_session/add') ;?>">Add Terms Session</a></li>
			</ul>
			<div class="card-body">
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							 <th nowrap>Session</th>
							<th nowrap>Term</th>
						   	<th nowrap>Start Date</th>
							<th nowrap>Start End</th>
							<th nowrap>Operation</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table></div></div>
            </div>
        	</div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
  </section>
    <!-- /.content -->
 <script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){

	$('#users-datatable thead tr').clone(true).appendTo( '#users-datatable thead' );
    $('#users-datatable thead tr:eq(1) th').each( function (i) {
        var title = $(this).text();
        $(this).html( '<input type="text" style="width:90px" placeholder=" '+title+'" />' );
        $( 'input', this ).on( 'keyup change', function () {
            if ( table.column(i).search() !== this.value ) {
                table
                    .column(i)
                    .search( this.value )
                    .draw();
            }
        });
    });
  
  var termsessionid = <?= json_encode($member_termsessionid ?? null); ?>;  
	var table = $('#users-datatable').DataTable({
		deferRender: true,
		"searching": true,
		bFilter: false,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/terms_session/data');?>',
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
			{data:'session'},
			{data:'term_name'},
			{data:'start_date'},
			{data:'end_date'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
					if(data == termsessionid){
						  html += '<a href="<?php echo base_url('admin/terms_session/edit/id/'); ?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-edit icon-pencil"></i></a>';
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
				   "<?php echo base_url('admin/ajax/setboolattribute');?>",
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

<?= $this->endSection() ?>