<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
  <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Family Fee History
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Family Fee History</li>
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
                    <select class="form-control select2"  name="cls_sec_id" id="cls_sec_id" required="required" style="height: 24px;width: 100%;">
                       <option value="0">Select Section</option>
                      <?php if(isset($sectionsclassinfo)){
                      foreach ($sectionsclassinfo as  $secionvalue) { ?>
                      <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
                      <?php } ?>
                      <?php } ?>
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
    <table class="table table-striped table-bordered table-hover" id="students-datatable" width="100%" style="font-size:12px;">
		<thead>
			<tr>
				<th nowrap>#</th>
				<th style="width:170px;">Student<br> Name</th>
  				<th nowrap>Father<br> Name</th>
				<th nowrap>Father<br> Contacts </th>
  				<th nowrap>Mother<br> Contacts </th>
				<th>Monthly<br> Fee</th>
  				<th >Last<br> Paid</th>
  				<th>Current<br> Balance</th>
  			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div></div>
</div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <style type="text/css">
    	table.table-bordered th:last-child, table.table-bordered td:last-child{width: 50px;}
    </style>
    <!-- /.content -->
<script type="text/javascript">
$(function(){
	var table = $('#students-datatable').DataTable({
	  dom: 'Bfrtip',
		buttons: [
		'colvis', 'csv', 'excel','print','copy',
		 {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: [ 1,2,3,4,5,6,7 ]
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
			url:'<?= base_url('admin/family_fee_history/data') ?>',
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
			{data:'name'},	
			{data:'f_name'},
    		{data:'f_contacts'},
      		{data:'m_contacts'},
      		{data:'projectedfee'},
		    {
		        data:'paid_in_month',
		        render:function(data, type, row){
		         return data;
		        }
		    },
		    {data:'payable'}
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
   $("#cls_sec_id").select2({minimumInputLength: 2});
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
                status:1
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