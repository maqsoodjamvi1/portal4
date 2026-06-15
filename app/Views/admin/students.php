<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $status = '';
  if (!empty($_GET['status'])) {
      $status = $_GET['status'];
  }
?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<?= view('components/page_header', [
    'title' => 'Students',
    'icon' => 'fas fa-user-graduate',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'active' => true],
    ],
]) ?>

<?php ob_start(); ?>
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="student_id" class="report-label">Student</label>
        <select class="form-control form-control-sm select2" name="student_id" id="student_id" style="width:100%;">
            <option value="0">Select Student</option>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="parent_id" class="report-label">Parent</label>
        <select class="form-control form-control-sm select2" name="parent_id" id="parent_id" style="width:100%;">
            <option value="0">Select Parent</option>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="cls_sec_id" class="report-label">Section</label>
        <select class="form-control form-control-sm select2" name="cls_sec_id" id="cls_sec_id" required style="width:100%;">
            <option value="0">Select Section</option>
            <option value="all">All Classes</option>
            <?php if (!empty($sectionsclassinfo)) : ?>
                <?php foreach ($sectionsclassinfo as $secionvalue) : ?>
                    <option value="<?= esc($secionvalue['cls_sec_id']) ?>">
                        <?= esc($secionvalue['class_name'] . ' - ' . $secionvalue['section_name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="min_age" class="report-label">Min age</label>
        <input type="number" name="min_age" class="form-control form-control-sm" id="min_age" placeholder="Min Age">
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="max_age" class="report-label">Max age</label>
        <input type="number" name="max_age" class="form-control form-control-sm" id="max_age" placeholder="Max Age">
    </div>
    <div class="col-lg-2 col-md-4 col-sm-12 mb-2 d-flex align-items-end">
        <button type="button" id="btn-filter" class="btn btn-primary btn-sm me-1">Filter</button>
        <button type="button" id="btn-reset" class="btn btn-secondary btn-sm">Reset</button>
    </div>
</div>
<?php $filterBodyHtml = ob_get_clean(); ?>

<?php ob_start(); ?>
<div id="totalStudents" class="text-end mb-2 small fw-bold"></div>
<table class="table table-striped table-bordered table-hover mb-0" id="students-datatable" style="font-size:10px;width:100%;">
    <thead>
        <tr>
            <th nowrap>#</th>
            <th nowrap>Picture</th>
            <th style="width:100px;">Reg No</th>
            <th style="width:130px;">Name</th>
            <th style="width:165px;">CNIC</th>
            <th nowrap>Gender</th>
            <th nowrap>Age</th>
            <th nowrap>Class</th>
            <th style="width:13%;">Address</th>
            <th style="width:70px;">Operation</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<?php $tableHtml = ob_get_clean(); ?>

<section class="content">
    <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link <?= $status == 1 ? 'active' : '' ?>" href="<?= base_url('admin/students?status=1') ?>">Current</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 2 ? 'active' : '' ?>" href="<?= base_url('admin/students?status=2') ?>">Suspended</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 3 ? 'active' : '' ?>" href="<?= base_url('admin/students?status=3') ?>">Dropped</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 4 ? 'active' : '' ?>" href="<?= base_url('admin/students?status=4') ?>">Pending</a></li>
            </ul>
        </div>
        <div class="card-body pt-3">
            <?= view('components/filter_card', [
                'title' => 'Filters',
                'bodyHtml' => $filterBodyHtml,
                'cardClass' => 'card sms-filter-card report-filter-card mb-3',
            ]) ?>
            <?= view('components/data_table_card', [
                'title' => 'Students',
                'icon' => 'fas fa-user-graduate',
                'tableHtml' => $tableHtml,
                'cardClass' => 'card sms-card mb-0',
            ]) ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
    <div class="modal fade sms-modal sms-modal--fullscreen-sm" id="viewSibling" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

    <div class="modal fade sms-modal sms-modal--fullscreen-sm" id="makeCurrent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="updateStatus" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<style>
  table.table-bordered th:last-child,
  table.table-bordered td:last-child { width: 50px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(function(){
	   var table = $('#students-datatable').DataTable({
	   dom: 'Bfrtip',
		buttons: [
		'colvis', 'csv',
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
			url:'<?= base_url('admin/students/data') . '?status=' . $_GET['status'] ?>',
			type:'post',
			data:function(d){
			 	d.status = $('#status').val();
                d.student_id = $('#student_id').val();
                d.cls_sec_id = $('#cls_sec_id').val();
                d.parent_id = $('#parent_id').val();
                d.min_age = $('#min_age').val();
                d.max_age = $('#max_age').val();
			}
		},
		columns:[
			{
				data:'sr_id',
				className:'select-checkbox',
				render:function(data, type, row){
					return data;
				}
			},
			{data:'profile_photo'},
			{data:'reg_no'},
			{
                data:'name',
                className:'select-checkbox',
                render:function(data, type, row){
                    return data+' c/o '+row.f_name;
                }
            },
            {
                data:'std_cnic',
                render:function(data, type, row){
                    return '<small>Std CNIC: '+data+'<br> F CNIC: '+row.father_cnic+'</small>';
                }
            },	
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
          html += '<div class="btn-group"><button type="button" class="btn btn-secondary btn-sm">Action</button><button type="button" class="btn btn-secondary dropdown-toggle dropdown-icon" data-bs-toggle="dropdown"><span class="visually-hidden">Toggle Dropdown</span></button><div class="dropdown-menu" role="menu">';
          if(<?php echo $_GET['status']; ?> == 1){
                html += '<a href="<?php echo base_url('admin/students/edit?id=');?>' + data + '" title="edit" class="btn btn-secondary btn-sm dropdown-item"><i class="fas fa-pencil-alt"></i> Edit</a>';
            }

          html += '<a href="<?php echo base_url('admin/profile-student?id='); ?>' + data + '" title="edit" class="btn btn-secondary btn-sm dropdown-item"><i class="fas fa-pencil-alt"></i> Profile</a>';

          html += '<a href="<?php echo base_url('admin/leaving-certificate/edit?id='); ?>' + data + '" title="School Leaving Certificate" class="btn btn-secondary btn-sm dropdown-item"><i class="fas fa-certificate"></i> SLC </a>';

          html += '<a href="<?php echo base_url('admin/leaving-certificate2/edit?id='); ?>' + data + '" title="School Leaving Certificate" class="btn btn-secondary btn-sm dropdown-item"><i class="fas fa-certificate"></i> SLC 2 </a>';

          html += '<a href="<?php echo base_url('admin/fee-chalan-single/add?id=');?>' + data + '" title="Fee Chalan" class="btn btn-secondary btn-sm dropdown-item"><i class="fas fa-file-invoice"></i> Chalan</a>';

          html += '<a href="<?php echo base_url('admin/fee-chalan-sibling?parent_id='); ?>' + row.parent_id + '" title="Fee Chalan" class="btn btn-secondary btn-sm dropdown-item"><i class="fas fa-file-invoice"></i> Sibling Chalan</a>';

           html += '<a href="#" data-bs-toggle="modal" style="font-size: .75rem !important;" class="btn btn-secondary btn-sm dropdown-item" id="#viewSibling' + data + '" data-bs-target="#viewSibling" data-discount="' + row.discounted_amount + '"  data-id="' + row.parent_id + '" class="btn btn-secondary btn-sm"><i class="fa fa-check" aria-hidden="true"></i> View Sibling</a>';

           // html += '<a href="<?php echo '#/students?m=delete&id=';?>' + data + '" title="Delete" class="btn btn-danger btn-sm dropdown-item"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>';
           html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo base_url('admin/students/delete&id='); ?>' + data + '\',\'students-datatable\');" title=" delete" class="btn btn-danger btn-sm dropdown-item"><i class="fa fa-trash icon-trash"></i> Delete</a>';
         
          html += '</div></div>'; 
		if(<?php echo $_GET['status']; ?> == 3){
			html += '<button data-bs-toggle="modal" class="btn btn-primary btn-sm makeCurrent" id="#makeCurrent' + data + '" data-bs-target="#makeCurrent" data-classfee ="' + row.class_fee + '"  data-id="' + row.id + '" class="btn btn-secondary btn-sm"><i class="fa fa-check" aria-hidden="true"></i> Make Current</button>';
		}

        html += '<div></div>';
	 
		return html;
				}

			}

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
        $('#status').prop('selectedIndex',1);
        $('#status').prop('selectedIndex',1);
        $('#min_age').prop('value','');
        $('#max_age').prop('value','');
        table.ajax.reload();  //just reload table

    });

   $("#parent_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        
        url: "<?= base_url('admin/students_bulk_info/get_parentinfo') ?>",
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
        
        url: "<?= base_url('admin/students/get_studentinfo') ?>",
        

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
 	var student_fee = $('#student_fee').val();
    var classFee = $('#classFee').val();    
 	var cls_secID = $('#cls_secID').val();
 	 	
 	  $.ajax({
      
      url: "<?= base_url('admin/students_bulk_info/updatestudentstatus') ?>",

      type: 'POST',
      data:{studentID: studentID,student_fee:student_fee,classFee:classFee,cls_secID:cls_secID}, 
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
      
      url: "<?= base_url('admin/students_bulk_info/get-sibling') ?>",
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
</script>
<?= $this->endSection() ?>