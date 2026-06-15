<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <?= view('components/page_header', [
    'title' => 'Student Complaints',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Complaints', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">	
			<ul class="nav nav-tabs">
				<li class="nav-item"><a  class="nav-link" href="<?= base_url('admin/students-complaints') ?>">Students Complaints</a></li>
				<li class="nav-item"><a  class="nav-link" href="<?= base_url('admin/students_complaints/add') ?>"> Add Students Complaints </a></li>
			</ul>
		<div class="card-body">
		<div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover" id="students-datatable" width="100%">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Student </th>
							<th nowrap>Class </th>
							<th nowrap>Session </th>
							<th nowrap>Term </th>
							<th nowrap>Date </th>
							<th nowrap>Detail </th>
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
	$('#students-datatable thead tr').clone(true).appendTo( '#students-datatable thead' );
    $('#students-datatable thead tr:eq(1) th').each( function (i) {
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

	var table = $('#students-datatable').DataTable({
			deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/students_complaints/data'); ?>',
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
			{data:'student'},
			{data:'class'},
			{data:'session_name'},
			{data:'term_name'},
			{data:'date'},
			{data:'detail'},
		]
	});
});
</script>

<?= $this->endSection() ?>