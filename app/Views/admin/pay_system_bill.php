<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $system_status = $system_status ?? '';
  $psbBase = base_url('admin/pay_system_bill');
?>
<script src="<?php echo base_url();?>resource/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <?= view('components/page_header', [
    'title' => 'System Bills',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'System Bills', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
 <div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
	<ul class="nav nav-tabs">		
      <li class="nav-item"><a class="nav-link <?php if($system_status == ''){ ?> active <?php } ?>" href="<?= base_url('admin/pay_system_bill') ?>">  All </a></li> 
			<li class="nav-item"><a class="nav-link <?php if($system_status == 'pending'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=pending">  Pending </a></li>	
		  <li  class="nav-item" ><a  class="nav-link <?php if($system_status == 'in_connection'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=in_connection">  In Connection</a></li>

		 <li  class="nav-item" ><a  class="nav-link <?php if($system_status == 'willing'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=willing">  Willing</a></li>
		  
      <li  class="nav-item" ><a  class="nav-link <?php if($system_status == 'paid'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=paid">  Paid</a></li>
		 
		  <li  class="nav-item" ><a  class="nav-link <?php if($system_status == 'not_responding'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=not_responding">Not responding</a></li>
		 
		  <li  class="nav-item" ><a  class="nav-link <?php if($system_status == 'testing'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=testing">Testing</a></li>
      
      <li  class="nav-item" ><a  class="nav-link <?php if($system_status == 'active'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=active">Active</a></li>
      
      <li  class="nav-item" ><a  class="nav-link <?php if($system_status == 'inactive'){ ?> active <?php } ?>" href="<?= $psbBase ?>?system_status=inactive">InActive</a></li>
        
       
		</ul>
<div class="card-body">
	<div class="col-lg-12">
      <table class="table table-striped table-bordered table-hover" id="campus-datatable" width="100%">
			<thead>
				<tr>
					<th nowrap>#</th>
					<th nowrap>System Name </th>
					<th nowrap>Remaing Steps</th>	
					<th nowrap>Message</th>
					<th style="width: 150px;">Operation</th>
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
 <div class="modal fade" id="viewSibling" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Message</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
        	<input type="hidden" name="messagesystemID" id="messagesystemID">
          <textarea id="message" style="height:400px !important;" class="form-control" name="message"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="updateMessage" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
  </div>
  <!-- modal for status change start -->
   <div class="modal fade" id="changeStatus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Status</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
        	<input type="hidden" name="billID" id="billID">
          <select id="status" name="status" class="form-control">
          	<option value="pending">Pending</option>
          	<option value="paid">Paid</option>
          	<option value="in_connection">In Connection</option>
            <option value="willing">Willing</option>
          	<option value="not_responding">Not Responding</option>
          	<option value="testing">Testing</option>
          </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="updateBillStatus" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
  </div>
   <!-- modal for status change start -->
   <!-- modal for login sms start -->
   <div class="modal fade" id="viewLoginSms" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Login SMS</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
        	<input type="hidden" name="smscampusID" id="smscampusID">
          <textarea id="login_message" style="height:400px !important;" class="form-control" name="login_message"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="sendLoginSms" class="btn btn-primary">Send</button>
          </div>
        </div>
      </div>
  </div>
  <!-- modal for login sms start -->

   <!-- modal for login sms start -->
   <div class="modal fade" id="viewReminderSms" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Reminder SMS</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
        	<input type="hidden" name="remindersmscampusID" id="remindersmscampusID">
          <textarea id="reminder_message" style="height:400px !important;" class="form-control" name="reminder_message"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="sendReminderSms" class="btn btn-primary">Send</button>
          </div>
        </div>
      </div>
  </div>
  <!-- modal for login sms start -->
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	
	$('#updateBillStatus').click(function(){
 	var status = $('#status').val();
 	var billID = $('#billID').val();
 	 	
 	  $.ajax({
      url: '<?= $psbBase ?>/updateBillStatus',
      type: 'POST',
      data:{status: status,billID:billID}, 
      success:function(res){
 		    var json = $.parseJSON(res);
            if(json.success){
                toastr.success(json.msg);
            }else{
                toastr.error(json.msg);
            }
          }
      });
 });

	$('#updateMessage').click(function(){
 	var message = $('#message').val();
 	var messagesystemID = $('#messagesystemID').val();
 	 	
 	  $.ajax({
      url: '<?= $psbBase ?>/updateStatusMessage',
      type: 'POST',
      data:{message: message,messagesystemID:messagesystemID}, 
      success:function(res){
 		    var json = $.parseJSON(res);
            if(json.success){
                toastr.success(json.msg);
            }else{
                toastr.error(json.msg);
            }
          }
      });
 });

	$('#changeStatus').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var billID = button.data('id');

  // $.ajax({
  //     url: 'admin.php?c=pay_system_bill&m=getMessage',
  //     type: 'POST',
  //     data:{systemID: systemID}, 
  //     success:function(res){
  //           $('#message').val(res);
  //         }
  //     });
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('#billID').val(billID)
  
});

	$('#viewSibling').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var systemID = button.data('id');

  $.ajax({
      url: '<?= $psbBase ?>/getMessage',
      type: 'POST',
      data:{systemID: systemID}, 
      success:function(res){
            $('#message').val(res);
          }
      });
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('#messagesystemID').val(systemID)
  
});


$('#sendLoginSms').click(function(){
 	var login_message = $('#login_message').val();
 	var smscampusID = $('#smscampusID').val();
 	 	
 	  $.ajax({
      url: '<?= $psbBase ?>/updateLoginSms',
      type: 'POST',
      data:{login_message: login_message,smscampusID:smscampusID}, 
      success:function(res){
 		    var json = $.parseJSON(res);
            if(json.success){
                toastr.success(json.msg);
            }else{
                toastr.error(json.msg);
            }
          }
      });
 });	

$('#viewLoginSms').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var campusID = button.data('id');

  $.ajax({
      url: '<?= $psbBase ?>/getLoginSms',
      type: 'POST',
      data:{campusID: campusID}, 
      success:function(res){
            $('#login_message').val(res);
          }
      });
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('#smscampusID').val(campusID)
  
});


$('#sendReminderSms').click(function(){
 	var reminder_message = $('#reminder_message').val();
 	var remindersmscampusID = $('#remindersmscampusID').val();
 	 	
 	  $.ajax({
      url: '<?= $psbBase ?>/updateReminderSms',
      type: 'POST',
      data:{reminder_message: reminder_message,remindersmscampusID:remindersmscampusID}, 
      success:function(res){
 		    var json = $.parseJSON(res);
            if(json.success){
                toastr.success(json.msg);
            }else{
                toastr.error(json.msg);
            }
          }
      });
 });	

$('#viewReminderSms').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var campusID = button.data('id');

  $.ajax({
      url: '<?= $psbBase ?>/getReminderSms',
      type: 'POST',
      data:{campusID: campusID}, 
      success:function(res){
            $('#reminder_message').val(res);
          }
      });
  
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('#remindersmscampusID').val(campusID)
  
});

var table = $('#campus-datatable').DataTable({
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
  processing: true,
  serverSide: true,
	ajax:{
		url:'<?= $psbBase ?>/data',
		type:'post',
		data:function(d){
			d.system_status = '<?= esc($system_status, 'js') ?>';
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
		{
			data:'campus_name',
			render:function(data, type, row){
					return row.system_name+'<br>'+data+' <br>'+row.campus_phone+' <br>'+row.location+' <br>'+row.created_date+' <br>Expiry: '+row.campus_expiry;
			}
		},
		{data:'remaining_steps'},
		{ data:'message'},
		{
		data:'id',
		sortable:false,
		render:function(data, type, row){
		var html = ''; 
		html += '<div class="">';
		html += '<a style="width:100%;" target="_blank" href="<?php echo 'https://portal.timesoftsol.com/admin.php?c=login&username=';?>' + row.username + '&pass='+row.pass+'" title="login" class="btn btn-secondary btn-sm"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</a>';

		html += '<a style="width:100%;" href="<?= $psbBase ?>/view?id=' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> View</a>';

		// if(row.bill_status == 'unpaid'){
		// 	html += ' <a  style="width:100%;" onclick="myFunction(' + data + ')"  title="edit" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Create Live System </a>';
		// }else{
		html += '<a style="width:100%;" onclick="myFunction(' + row.userID + ')"  title="edit" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Reset Password</a>';
		// }

    html += '<a style="width:100%;" href="#" data-bs-toggle="modal" style="font-size: .75rem !important;" id="#changeStatus' + data + '" data-bs-target="#changeStatus" data-id="' + row.id + '" class="btn btn-secondary btn-sm"><i class="fa fa-check" aria-hidden="true"></i> Update Status</a>';

    html += '<a style="width:100%;" href="#" data-bs-toggle="modal" style="font-size: .75rem !important;" id="#viewSibling' + data + '" data-bs-target="#viewSibling" data-id="' + row.system_id + '" class="btn btn-secondary btn-sm"><i class="fa fa-check" aria-hidden="true"></i> Update Message</a>';

    html += '<a style="width:100%;" href="#" data-bs-toggle="modal" style="font-size: .75rem !important;" id="#viewLoginSms' + data + '" data-bs-target="#viewLoginSms" data-id="' + row.campus_id + '" class="btn btn-secondary btn-sm"><i class="fa fa-check" aria-hidden="true"></i> Login SMS</a>';

    html += '<a style="width:100%;" href="#" data-bs-toggle="modal" style="font-size: .75rem !important;" id="#viewReminderSms' + data + '" data-bs-target="#viewReminderSms" data-id="' + row.campus_id + '" class="btn btn-secondary btn-sm"><i class="fa fa-check" aria-hidden="true"></i> Reminder SMS</a>';
    
    html += '<a style="width:100%;" target="_blank" href="https://wa.me/' +row.campus_phone+ '?text=Free trial account of '+row.system_name+'  on TIME Soft Solution. %0a username: '+row.username+' password: '+row.pass +' For Login plz click on https://portal.timesoftsol.com/admin.php Feel free to contact us more detail TIME Soft Solution Islamabad, Pakistan" style="font-size: .75rem !important;"  class="btn btn-secondary btn-sm"><i class="fa fa-check" aria-hidden="true"></i> Whatsapp</a>';

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
	   "<?php echo base_url('admin/ajax/setboolattributeIsTrial'); ?>",
	   {
		   act:'upsort',
		   tbname:tablename,
		   tbfield:fieldname,
		   tbfieldvalue:fieldval,
		   id:rowid//,
	   },
	   function(data){
		   if(data=='success'){
			   toastr.success('change success');
		   }else{
			   toastr.error('change error');
		   }
	   });
}
});
}
});
});
</script>
<script>
function myFunction(id) {
var txt;
var r = confirm("OK! or Cancel!");
if (r == true) {
var userid = id;	
$.ajax({
    url: '<?= $psbBase ?>/reset_password',
    type: "POST",
    data:{userid: userid},
    success:function(res){
		  var json = $.parseJSON(res);
			if(json.success){
				toastr.success(json.msg);
				//location.reload();
			}
	}
  });
  } else {
    txt = "You pressed Cancel!";
  }
}
</script>

<?= $this->endSection() ?>