<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Student Registration',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Student Registration', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
    <div class="row">
    <div class="col-lg-12">
    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
		<ul class="nav nav-tabs">			
		<li  class="nav-item" ><a  class="nav-link" href="<?= base_url('admin/student_registration') ?>">  Registered Students</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/student_registration/add') ?>">Add Student</a></li>
    	</ul>
      <div class="card-body">
      <div class="row">
           
        </div>
        </div>
        <br>
        <div class="col-lg-12">
            <div id="totalStudents" class="text-end" style="font-size: 12px;color: #000;font-weight: bold;"></div>
        <table  class="table table-striped table-bordered table-hover" id="students-datatable"  style="font-size:9px !important;width: 100%; color: #000;font-weight: normal;">
			<thead>
				<tr>
					<th style="width:70px !important;">#</th>
					<!-- <th style="width: 120px !important;"></th> -->
					<th style="width:130px;" nowrap>Student Info</th>
                    <th style="width: 200px !important;" nowrap>Father Name</th>
                    <!-- <th style="width:110px;"></th> -->
                    <th style="width: 150px !important;" nowrap>Cell</th>
					<!-- <th style="width: 90px !important;">Cell#2</th> -->
                    <!-- <th nowrap>Gender</th> -->
                    <!-- <th style="width: 90px !important;">Dob</th> -->
                    <th style="width: 110px !important;" nowrap>Email</th>
                   <!--  <th nowrap>Appointment <br>Time</th>
                    <th nowrap>Grade</th> -->
                    <!-- <th style="width: 135px !important;">Operation</th> -->
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
        </div>
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
    <div class="modal fade" id="viewSibling" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Sibling</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div id="SiblingInfo">
          </div>
          </div>
          <div class="modal-footer">
            
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="makeCurrent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Update Status</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <form>
            <input type="hidden" name="studentID" id="studentID">
            <input type="hidden" class="form-control" name="classFee" id="classFee">
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Student Fee:</label>
            <input type="text" class="form-control" name="student_fee" id="student_fee">
          </div>
            
 		 </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="updateStatus" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </div>
    <style type="text/css">
    	table.table-bordered th:last-child, table.table-bordered td:last-child{width: 50px;}
        .table-bordered td, .table-bordered th {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
            font-size: 16px !important;
            color: #000;
            font-weight: normal;
        }
    </style>
    <!-- /.content -->
<script type="text/javascript">
$(function(){
	var table = $('#students-datatable').DataTable({
	  dom: 'Bfrtip',
		buttons: [
		'colvis', 'csv',
		{
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: [ 0,1,2,3,4,5,6,7,8,9,10 ]
                },
                orientation: 'landscape'
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
		"pageLength": 50,
		"searching": false,
		ajax:{
			url:'<?= base_url('admin/student_registration/data') ?>',
			type:'post',
			data:function(d){
			 	// d.phase_id = $('#phase_id').val();
                // d.panel_id = $('#panel_id').val();
                // d.class_id = $('#class_id').val();
                // d.parent_id = $('#parent_id').val();
                // d.min_age = $('#min_age').val();
                // d.max_age = $('#max_age').val();
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
			// {data:'reg_no'},
			{
                data:'name',
                className:'select-checkbox',
                render:function(data, type, row){
                    return data+ '<br> Arid No: ' +row.arid_no+'<br> Discipline: '+row.discipline+'<br> Semester: '+row.semester+'<br> Section: '+row.section;
                }
            },
            // {data:'f_name'},
            {
                data:'father_name',
                render:function(data, type, row){
                    return data;
                }
            },
            {
                data:'mobile_no',
                render:function(data, type, row){
                    return data;
                }
            },	
            // {data:'contact1'},
            // {data:'contact2'},
            {data:'email'},
			// {data:'date_of_birth'},
            // {data:'age'},
            // {data:'appointment_time'},
            // {data:'grade'},
			

		]});

	 $('#btn-filter').click(function(){ //button filter event click
        table.ajax.reload();  //just reload table
     });

    table.on('draw', function (data) {
        $('#totalStudents').html('No Of Students: '+table.page.info().recordsDisplay);
    });
     

    $('#btn-reset').click(function(){ //button reset event click
        $('#student_id').select2("val", "0");
        $('#parent_id').select2("val", "0");
        $('#cls_sec_id').prop('selectedIndex',0);
        // $('#status').prop('selectedIndex',1);
        // $('#status').prop('selectedIndex',1);
        $('#min_age').prop('value','');
        $('#max_age').prop('value','');
        table.ajax.reload();  //just reload table

    });

   $("#parent_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=student_registration&m=get_parentinfo', 
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

//$("#cls_sec_id").select2({minimumInputLength: 2});

$("#student_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=student_registration&m=get_studentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term: term,
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
 	var student_fee = $('#student_fee').val();
    var classFee = $('#classFee').val();    
 	 	
 	  $.ajax({
      url: 'admin.php?c=student_registration&m=updatestudentselectedstatus',
      type: 'POST',
      data:{studentID: studentID,student_fee:student_fee,classFee:classFee}, 
      success:function(res){
 		    var json = $.parseJSON(res);
            if(json.success){
                toastr.success(json.msg);
                //location.href = '#/students?status=1';
                location.reload();
            }else{
                toastr.error(json.msg);
            }
          }
      });
 });
			
$('#updateStatus').on('hidden.bs.modal', function () { 
    location.reload();
});

$('#viewSibling').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var parentID = button.data('id')
 

  
   $.ajax({
      url: 'admin.php?c=students&m=getSibling',
      type: 'POST',
      data:{parentID: parentID}, 
      success:function(res){
            $('#SiblingInfo').html(res);
          }
      });

  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  
});

$('#makeCurrent').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var classfee = button.data('classfee') // Extract info from data-* attributes
  var studentID = button.data('id')
  
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('#student_fee').val(classfee)
  modal.find('#classFee').val(classfee);
  modal.find('#studentID').val(studentID)
  
});

$(function(){
  $('#class_id').on('change', function() {
  var class_id = $( "#class_id" ).val();
  $.ajax({
            url: 'admin.php?c=ajax&m=selectPhase',
            type: "POST",
            data:{class_id: class_id, },
            success:function(res){
        // alert(res);      
         $("#phase_id").html(res);
         }
         });
  })
});

$(function(){
  $('#phase_id').on('change', function() {
  var phase_id = $( "#phase_id" ).val();
  var class_id = $( "#class_id" ).val();
  $.ajax({
            url: 'admin.php?c=ajax&m=selectPanel',
            type: "POST",
            data:{phase_id:phase_id,class_id: class_id, },
            success:function(res){
        // alert(res);      
         $("#panel_id").html(res);
         }
         });
  })
});
</script>

<?= $this->endSection() ?>