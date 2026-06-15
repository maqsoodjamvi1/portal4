<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<script src="<?php echo base_url();?>resource/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<?= view('components/page_header', [
    'title' => 'Worksheet Meta Info',
    'icon' => 'fas fa-info-circle',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Worksheets', 'url' => base_url('admin/worksheet')],
        ['label' => 'Meta Info', 'active' => true],
    ],
]) ?>    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a  class="nav-link active" href="<?= base_url('admin/worksheet') ?>"> Worksheet </a></li>
				<!-- <li  class="nav-item"><a  class="nav-link" href="<?php //echo '#/worksheet?m=add';?>">Add Worksheet </a></li> -->
			</ul>
			<div class="card-body">	
			<div class="col-lg-12">
              <table class="table table-striped table-bordered table-hover" id="classdairy-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>Subject</th>
							<th nowrap>Category</th>
							<th nowrap>Topic</th>
							<th nowrap>Title</th>
							<th nowrap>Slug</th>
							<th nowrap>Indexable</th>
							<th nowrap>Description</th>
							<th nowrap>User</th>
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
        });
    });
	var table = $('#classdairy-datatable').DataTable({
		deferRender: true,
		"pageLength": 100,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/worksheet/data'); ?>',
			type:'post',
			data:function(d){
			//d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
			}
		},
		columns:[	
			{data:'subject'},
			{data:'cat_name'},
			{data:'topic'},
			{data:'doc_title'},
			{data:'doc_slug'},
			{
				data:'no_index',
				render:function(data, type, row){
					
					var status_1 = '';
					if(data == '0') status_1 = 'checked="checked"';
					console.log(data);
					return '<label class="switch"><input type="checkbox" ' + status_1 + ' class="switch-small switchchk"  data-on-text="Indexable" data-off-text="No Indexable" data-table="contents" data-field="no_index" data-size="mini" data-pk="' + row.id + '" /></label>';
					
				}
			},
			{data:'description'},
			{data:'user'},
			
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
					fieldval = 0;
				}else{
					fieldval = 1; 
				}
				$.post(
				   "<?php echo base_url('admin/ajax/setboolIndexable'); ?>",
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