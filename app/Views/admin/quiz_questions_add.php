<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $id = ''; 
  if(!empty($_GET['id'])){
   $id = $_GET['id']; 
  }
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Quiz Question',
    'icon' => 'fas fa-plus-circle',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Quiz Questions', 'url' => base_url('admin/quiz_questions')],
        ['label' => 'Add', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">	
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link active" href="<?= base_url('admin/quiz_questions') ?>">Quiz Questions</a></li>
			</ul>
		<div class="card-body">
		<div class="col-lg-12">
        <table class="table table-striped table-bordered table-hover" id="users-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap style="max-width: 30px;">#</th>
							<th nowrap >Class</th>
							<th nowrap>Categories</th>
							<th nowrap>Topic</th>
						    <th nowrap style="max-width: 250px">Topic Skill</th>
							<th nowrap >Operation</th>
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
        } );
    } );
 

	var table = $('#users-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo site_url('c=quiz_questions_add&m=data&id='.$_GET['id']); ?>',
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
			{data:'class'},
			{data:'cat_name'},
			{data:'topic'},
			{data:'topic_skill'},
			
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
						  html += '<a href="<?php echo '#/topic_skills?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-edit icon-pencil"></i></a>';
						  
						   html += '<a target="_blank" href="<?php echo '#/topic_skills_view_buttons?topic_skill_id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"> View/Add Questions </a>';   
						  
						  if(row.issys == '1'){

						  }else{
							  //html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php //echo site_url('c=subject_cat&m=delete&id=');?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-secondary btn-sm"><i class="fa fa-trash icon-trash"></i></a>';
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