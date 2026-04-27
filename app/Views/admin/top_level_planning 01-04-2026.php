<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<style type="text/css">
  div.dataTables_wrapper div.dataTables_filter{
    text-align: left !important; 
  }
</style>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Scheme of Studies
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Scheme of Studies</li>
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
      				<!-- First tab removed -->
      				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning/add') ?>">Add Scheme of Studies</a></li>
      				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_sections/add') ?>">Add Scheme of Studies Classwise</a></li>
      				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_subject/add') ?>">Add Scheme of Studies Subjectwise</a></li>
      				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/top_level_planning_gradewise') ?>">Grade Wise View</a></li>
      			 </ul>
	<div class="card-body">
    <div class="col-lg-12">
    <table class="table table-striped table-bordered table-hover" id="classes-datatable" width="100%">
			<thead>
				60<tr
					<th nowrap>Session</th>
					<th nowrap>Class</th> 
					<th style="max-width: 630px" nowrap>Objective</th>
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
$('#classes-datatable thead tr').clone(true).appendTo( '#classes-datatable thead' );
    $('#classes-datatable thead tr:eq(1) th').each( function (i) {
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
 var table = $('#classes-datatable').DataTable({
	dom: 'Bfrtip',
	buttons: [
        'copyHtml5',
        'excelHtml5',
        'csvHtml5',
        'pdfHtml5' 
    ],
	deferRender: true,
	"pageLength": 200,
	select:{
		style:'single',
		blurable: true
	},
	ajax:{
		url:'<?php echo base_url('admin/top_level_planning/data'); ?>',
		type:'post',
		data:function(d){
			//d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
		}
	},
	columns:[
		
		{
			data:'session_name',
			render:function(data, type, row){
				return data+' ('+row.term_name+')';
			}
		},
		{
			data:'class_name',
			render:function(data, type, row){
				return data+' ('+row.subject+')';
			}
		},
		{data:'objective'}
		]
	});
});
</script>

<?= $this->endSection() ?>