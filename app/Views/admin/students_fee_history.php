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
<style type="text/css">
	.select2-container--default .select2-selection--single, .select2-selection .select2-selection--single {
    border: 1px solid #d2d6de;
    border-radius: 0;
    padding: 6px 12px;
    height: 24px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 18px !important;
    right: 3px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #444;
    line-height: 20px !important;
}
</style>
    <section class="content-header">
      <h1>
        Students
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Students
        </li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="nav-tabs-custom">
			<ul class="nav nav-tabs">			
				<li><a href="<?= base_url('admin/students/add') ?>">Add Student</a></li>
          		<li <?php if($status == 1){ ?>class="active" <?php } ?> ><a href="<?php echo '#/students?status=1';?>">  Current</a></li>
          		<li <?php if($status == 2){ ?>class="active" <?php } ?>><a href="<?php echo '#/students?status=2';?>">  Suspended</a></li>
          		<li <?php if($status == 3){ ?>class="active" <?php } ?>><a href="<?php echo '#/students?status=3';?>">  Dropped</a></li>
          		<li <?php if($status == 4){ ?>class="active" <?php } ?>><a href="<?php echo '#/students?status=4';?>">  Pending</a></li>
			</ul>
<div class="tab-content table-responsive no-padding"><div class="col-xs-12">
<div class="row">
  <?php //echo site_url('c=students&m=data&status='.$_GET['status']); ?>
                <form id="form-filter" class="">
                <div class="col-sm-4">
                  <div class="form-group">
		                <label for="class">Student Name</label><br>
		                <select class="form-control select2" name="student_id" id="student_id" style="height: 24px;">
		                   <option value="0">Select Student</option>   
		                </select>
		            </div>
                </div>
                <div class="col-sm-3">
                  <label for="class">Parent Name</label><br>
                  <select class="form-control select2" name="parent_id" id="parent_id" style="height: 24px;">
                   	<option value="0">Select Parent</option>   
                	</select>
                </div>
               <div class="col-sm-2">
                <label for="class">Section <span class="required">*</span></label>
		                <select class="form-control select2"  name="cls_sec_id" id="cls_sec_id" required="required">
		                   <option value="0">Select Section</option>
		                  <?php if(isset($sectionsclassinfo)){
		                  foreach ($sectionsclassinfo as  $secionvalue) { ?>
		                  <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
		                  <?php } ?>
		                  <?php } ?>
		                </select>
		            </div>
					 
				        <div class="col-sm-2">
                  <button type="button" id="btn-filter" style="float:left;line-height:12px;margin-top: 18px;height: 24px;" class="btn btn-primary">Filter</button>
                  <button type="button" id="btn-reset"  style="float:left;line-height:12px;margin-top: 18px;height: 24px;" class="btn btn-default">Reset</button>
                </div>
                </form>
    </div>
    <table class="table table-striped table-bordered table-hover" id="students-datatable" width="100%" style="font-size:10px;">
					<thead>
						<tr>
							<th nowrap>#</th>
							<th nowrap>Picture</th>
							<th nowrap>Reg No</th>
							<th nowrap>Name</th>
              <th nowrap>F Name</th>
							<th nowrap>Gender</th>
              <th nowrap>Age</th>
              <th nowrap>Class</th>
              <th  style="width: 13% !important;">Address</th>
							<th nowrap>Contacts </th>
							<th nowrap>Pable</th>	
							<th nowrap>Discount</th>		
							<th nowrap>fee</th>							
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
			url:'<?= base_url('admin/students/data') . '?status=' . $_GET['status'] ?>',
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
			{data:'contacts'},
			{data:'payable'},
			{data:'discounted'},
			{data:'projectedfee'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					//console.log(row);
					var html = '';
					html += '<div class="btn-group">';
						  html += '<a href="<?php echo '#/students?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fas fa-pencil-alt"></i> Edit</a>';
					html += '</div>';
					html += '<div><a href="<?php echo '#/leaving_certificate?m=edit&id=';?>' + data + '" title="School Leaving Certificate" class="btn btn-default btn-xs"><i class="fas fa-certificate"></i> SLC </a><br><a href="<?php echo '#/fee_chalan_single?m=add&id=';?>' + data + '" title="Fee Chalan" class="btn btn-default btn-xs"><i class="fas fa-file-invoice"></i> Chalan</a></div>';
		if(<?php echo $_GET['status']; ?> == 3){
			html += '<button data-toggle="modal" class="makeCurrent" id="#makeCurrent' + data + '" data-target="#makeCurrent" data-discount="' + row.discounted_amount + '"  data-id="' + row.id + '" class="btn btn-default btn-xs"><i class="fa fa-check" aria-hidden="true"></i> Make Current</button>';
		}
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
        url: 'admin.php?c=students&m=get_parentinfo', 
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

 $("#student_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=students&m=get_studentinfo', 
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
      url: 'admin.php?c=ajax&m=updatestudentstatus',
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