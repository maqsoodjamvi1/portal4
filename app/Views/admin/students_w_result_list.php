<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<?= view('components/page_header', [
    'title' => 'Students Whatsapp Result List',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students Whatsapp List', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
    <div class="row">
      <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
      <ul class="nav nav-tabs">     
        <li  class="nav-item" ><a  class="nav-link <?php if($status == 1){ ?> active <?php } ?>" href="<?php echo '#/students_contact_list?status=1';?>">  Current</a></li>
        <li class="nav-item"><a class="nav-link <?php if($status == 2){ ?> active <?php } ?>" href="<?php echo '#/students_contact_list?status=2';?>">  Suspended</a></li>
        <li class="nav-item"><a class="nav-link <?php if($status == 3){ ?> active <?php } ?>" href="<?php echo '#/students_contact_list?status=3';?>">  Dropped</a></li>
        <li class="nav-item"><a class="nav-link <?php if($status == 4){ ?> active <?php } ?>" href="<?php echo '#/students_contact_list?status=4';?>">  Pending</a></li>
      </ul>
    <div class="card-body">
    <div class="row">
      <div class="col-lg-12">
        <form id="form-filter" class="d-flex flex-wrap align-items-center">
                <div class="col-lg-2">
                    <select class="form-control select2" name="student_id" id="student_id" style="height: 24px;width: 100%;">
                       <option value="0">Select Student</option>   
                    </select>
                </div>
                <div class="col-lg-2">
                  <select class="form-control select2" name="parent_id" id="parent_id" style="height: 24px;width: 100%;" >
                    <option value="0">Select Parent</option>   
                  </select>
                </div>
               <div class="col-lg-2">
                    <select class="form-control select2"  name="cls_sec_id" id="cls_sec_id" required="required" style="height: 24px;width: 100%;">
                       <option value="0">Select Section</option>
                      <?php if(isset($sectionsclassinfo)){
                      foreach ($sectionsclassinfo as  $secionvalue) { ?>
                      <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
                      <?php } ?>
                      <?php } ?>
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="exam_id" id="exam_id" class="form-control"> 
                    <?php  foreach ($exams as $key => $exam) { ?>
                            <option value="<?php echo $exam->eid; ?>"><?php echo $exam->exam_name; ?></option>
                    <?php } ?>
                    </select>
                </div>
                <div class="col-lg-3">
                  <button type="button" id="btn-filter" style="float:left;line-height:12px;height: 24px;" class="btn btn-primary">Filter</button>
                  <button type="button" id="btn-reset"  style="float:left;line-height:12px;height: 24px;" class="btn btn-secondary">Reset</button>
                </div>
        </form>
    </div>
  </div>
  <br>
    <table class="table table-striped table-bordered table-hover" id="students-datatable" width="100%" style="font-size:13px;">
					<thead>
						<tr style="vertical-align: middle;">
							<th nowrap>#</th>
							<th nowrap>Name</th>
                            <!-- <th nowrap>F Name</th>
							<th nowrap>Class</th> -->
                            <th nowrap>Send </th>
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
                    columns: [ 1,2,3,4,5,6,7,8 ]
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
			url:'<?= base_url('admin/students_w_result_list/data') . '?status=' . $_GET['status'] ?>&exam_id='+$('#exam_id').val(),
			type:'post',

			data:function(d){
			 	d.status = $('#status').val();
                d.student_id = $('#student_id').val();
                d.cls_sec_id = $('#cls_sec_id').val();
                d.parent_id = $('#parent_id').val();
                d.exam_id = $('#exam_id').val();
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
			// {data:'name'},	
            {
                data:'name',
                className:'select-checkbox',
                render:function(data, type, row){
                    return data+' ('+row.class+') C/O '+row.f_name; 
                }
            },
			// {data:'f_name'},
         	// {data:'class'},
            {data:'w_contacts'}
	
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
        url: 'admin.php?c=students_contact_list&m=get_parentinfo', 
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
        url: 'admin.php?c=students_contact_list&m=get_studentinfo', 
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
  
  
  var modal = $(this)
  modal.find('#discount').val(discount)
  modal.find('#studentID').val(studentID)
  
});

</script>

<?= $this->endSection() ?>