<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<section class="content-header">
      <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>
             Students
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Students</li>
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
				<li class="nav-item"><a class="nav-link" href="<?= base_url('admin/a_students/add') ?>">Add Student</a></li>
        <li  class="nav-item" ><a  class="nav-link <?php if($status == 1){ ?> active <?php } ?>" href="<?php echo '#/a_students?status=1';?>">  Current</a></li>
        <li class="nav-item"><a class="nav-link <?php if($status == 2){ ?> active <?php } ?>" href="<?php echo '#/a_students?status=2';?>">  Suspended</a></li>
        <li class="nav-item"><a class="nav-link <?php if($status == 3){ ?> active <?php } ?>" href="<?php echo '#/a_students?status=3';?>">  Dropped</a></li>
        <li class="nav-item"><a class="nav-link <?php if($status == 4){ ?> active <?php } ?>" href="<?php echo '#/a_students?status=4';?>">  Pending</a></li>
			</ul>
      <div class="card-body">
      <div class="row">
        <div class="col-lg-12">
              <form id="form-filter" class="form-inline">
                <div class="col-lg-3">
                    <select class="form-control select2" name="student_id" id="student_id" style="height: 24px;width: 100%;">
		                   <option value="0">Select Student</option>   
		                </select>
		            </div>
                <div class="col-lg-3">
                  <select class="form-control select2" name="parent_id" id="parent_id" style="height: 24px;width: 100%;" >
                   	<option value="0">Select Parent</option>   
                	</select>
                </div>
               
					      <div class="col-lg-3">
                  <button type="button" id="btn-filter" style="float:left;line-height:12px;height: 24px;" class="btn btn-primary">Filter</button>
                  <button type="button" id="btn-reset"  style="float:left;line-height:12px;height: 24px;" class="btn btn-default">Reset</button>
                </div>
                </form>
        </div>
        </div>
        <br>
    <table class="table table-striped table-bordered table-hover" id="students-datatable"  style="font-size:10px;width: 100%;">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Picture</th>
							<th style="width: 55px !important;">Reg No</th>
							<th nowrap>Name</th>
              <th nowrap>F Name</th>
							<th nowrap>Gender</th>
              <th nowrap>Age</th>
              <th nowrap>Class</th>
              <th  style="width: 13% !important;">Address</th>
					   <th style="width: 70px !important;">Operation</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
      </div>
    </div>
  </div>
    </div>
    <!-- /.box-body -->
    </div>
    <!-- /.box -->
    </div>
    </div>
    </section>
    <div class="modal fade" id="makeCurrent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title pull-left" id="exampleModalLabel">Update Status</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <form>
            <input type="hidden" name="studentID" id="studentID">
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Discount:</label>
            <input type="text" class="form-control" name="discount" id="discount">
          </div>
             <div class="form-group">
                <label for="class">Section <span class="required">*</span></label>
                    <select class="form-control select2"  name="cls_secID" id="cls_secID" required="required">
                       <option value="0">Select Section</option>
                      <?php if(isset($sectionsclassinfo)){
                      foreach ($sectionsclassinfo as  $secionvalue) { ?>
                      <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
                      <?php } ?>
                      <?php } ?>
                    </select>
              </div>
 		       </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" id="updateStatus" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </div>
    <style type="text/css">
    	table.table-bordered th:last-child, table.table-bordered td:last-child{width: 50px;}
    </style>
    <!-- /.content -->
<script type="text/javascript">
$(function(){
	var table = $('#students-datatable').DataTable({
	  dom: 'Bfrtip',
		buttons: [
		'colvis', 'csv', 'excel',
		 {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: [ 0,2,3,4,5,6,7,8,9,10 ]
                }
            },
		],
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		
		"processing": true, //Feature control the processing indicator.
    "serverSide": true, //Feature control DataTables' server-side processing mode.
    "ordering": false,
    "order": [], //Initial no order.
		"pageLength": 200,
		 "searching": false,
		ajax:{
			url:'<?= base_url('admin/a_students/data') . '?status=' . $_GET['status'] ?>',
			type:'post',

			data:function(d){
			 	d.status = $('#status').val();
        d.student_id = $('#student_id').val();
        d.cls_sec_id = $('#cls_sec_id').val();
        d.parent_id = $('#parent_id').val();
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
			{data:'profile_photo'},
			{data:'reg_no'},
			{data:'name'},	
			{data:'f_name'},
      {data:'gender'},
      {data:'age'},
			{data:'class'},
      {data:'address'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					//console.log(row);
					var html = '';
          html += '<div class="btn-group"><button type="button" class="btn btn-default btn-sm">Action</button><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown"><span class="sr-only">Toggle Dropdown</span></button><div class="dropdown-menu" role="menu">';
          html += '<a href="<?php echo '#/a_students?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs dropdown-item"><i class="fas fa-pencil-alt"></i> Edit</a>';
          html += '<a href="<?php echo '#/a_fee_chalan_single?m=add&id=';?>' + data + '" title="Fee Chalan" class="btn btn-default btn-xs dropdown-item"><i class="fas fa-file-invoice"></i> Chalan</a>';
          html += '</div></div>'; 
		      html += '<div></div>';
	 
		return html;
				}

			}

		]});

	 $('#btn-filter').click(function(){ //button filter event click
        table.ajax.reload();  //just reload table
    });


    $('#btn-reset').click(function(){ //button reset event click
        $('#student_id').select2("val", "0");
        $('#parent_id').select2("val", "0");
        $('#cls_sec_id').prop('selectedIndex',0);
        $('#status').prop('selectedIndex',1);
        table.ajax.reload();  //just reload table

    });

   $("#parent_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=a_students&m=get_parentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term:term,
            }
        },
       processResults: function (response) {
        console.log(response);
              return {
                 results: response
              };
           },
           cache: true
    }
 });
$("#cls_sec_id").select2({minimumInputLength: 2});
 $("#student_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=a_students&m=get_studentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term: term,
                status:<?php echo $_GET['status']  ?>
            }
        },
       processResults: function (response) {
        console.log(response);
              return {
                 results: response
              };
           },
           cache: true
    }
 });  

});

</script>
<script>
$('#updateStatus').click(function(){
 	var studentID = $('#studentID').val();
 	var discount = $('#discount').val();
 	var cls_secID = $('#cls_secID').val();
 	 	
 	  $.ajax({
      url: 'admin.php?c=ajax&m=a_updatestudentstatus',
      type: 'POST',
      data:{studentID: studentID,discount:discount,cls_secID:cls_secID}, 
      success:function(res){
 		       $('#updateStatus').html('Updated Successfully');
           location.reload(); 
          }
      });
 });
			
$('#updateStatus').on('hidden.bs.modal', function () { 
    location.reload();
});

$('#makeCurrent').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var discount = button.data('discount') // Extract info from data-* attributes
  var studentID = button.data('id')
  // var feeAmount = button.data('feeamount');
  // var paiddate = $('#datepicker2').val();
  // $('#PaidDate').val(paiddate);
  // var student_id = button.data('student_id');
  // var fine = button.data('fine');
  // if(fine){
  // 	$('#feeFine').show();
  // }
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('#discount').val(discount)
  modal.find('#studentID').val(studentID)
  // modal.find('#feeAmount').val(feeAmount)
  // modal.find('#ChalanID').val(recipient)
  // modal.find('#studentID').val(student_id)
  // modal.find('#fineamount').val(fine)
  // modal.find('#PaidAmount').val(feeAmount)
});

</script>

<?= $this->endSection() ?>