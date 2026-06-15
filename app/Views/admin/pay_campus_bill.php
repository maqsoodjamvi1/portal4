<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
     <?= view('components/page_header', [
    'title' => 'Campus Bills',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Campus Bills', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
<section class="content">
<div class="row">
<div class="col-lg-12">
 <div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">	
	<ul class="nav nav-tabs">
	
	</ul>
	<div class="card-body">
	<div class="col-lg-12">
      <table class="table table-striped table-bordered table-hover" id="campus-datatable" width="100%">
			<thead>
				<tr>
					<th nowrap>#</th>
					<th nowrap>System Name </th>
					<th nowrap>System Plan </th>
					<th nowrap>Installment Plan</th>
					<th nowrap>Max Student Allowed</th>
					<th nowrap>Max Fee</th>
					<th nowrap>Bill Amount</th>
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
<!-- /.content -->
<div class="modal fade" id="makeCurrent" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content"><div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Set Campus Discount</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <form>
          	<input type="hidden" name="billID" id="billID">
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Discount:</label>
            <input type="text" class="form-control" name="discount" id="discount">
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
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	var table = $('#campus-datatable').DataTable({
	deferRender: true,
	select:{
		style:'single',
		blurable: true
	},
	ajax:{
		url:'<?php echo base_url('admin/pay_campus_bill/data'); ?>',
		type:'post',
		data:function(d){
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
		{data:'campus_name'},
		{data:'plan_name'},
		{data:'install_name'},
		{data:'no_of_students'},
		{data:'max_fee'},
		{data:'bill_amount'},
		{
		data:'id',
		sortable:false,
		render:function(data, type, row){
		var html = '';
		html += '<a href="<?php echo '#/campus_bill?id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Print Chalan</a>';
		//if(row.bill_status == 'unpaid'){
			// html += ' <button data-bs-toggle="modal" class="makeCurrent" id="#makeCurrent' + data + '" data-bs-target="#makeCurrent" data-discount="' + row.campus_bill_discount + '"  data-id="' + row.id + '" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Pay Bill</button>';	
			html += '<a style="width:100%;" href="#/bill_amount?m=add&campus_id=' + row.campus_id + '"  style="font-size: .75rem !important;" id="#payBill' + data + '"  data-id="' + row.campus_id + '" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Pay Bill</a>';
		// }else{
		// 	html += ' <a  title="edit" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Paid</a>';
		// }
		html += '<a style="width:100%;" href="#/campus_chalan?campus_id=' + row.campus_id + '"  style="font-size: .75rem !important;" id="#payBill' + data + '"  data-id="' + row.campus_id + '" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i> Create Campus Chalan</a>';
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
var r = confirm("OK! or Concel!");
if (r == true) {
var bill_id = id;	
$.ajax({
    url: 'admin.php?c=pay_campus_bill&m=save',
    type: "POST",
    data:{bill_id: bill_id},
    success:function(res){
		  var json = $.parseJSON(res);
			if(json.success){
				toastr.success(json.msg);
				location.reload();
			}
	}
  });
  } else {
    txt = "You pressed Cancel!";
  }
}
</script>
<script>
$('#updateStatus').click(function(){
 	var bill_id = $('#billID').val();
 	var discount = $('#discount').val();
 	 	
 	  $.ajax({
      url: 'admin.php?c=pay_campus_bill&m=save',
      type: 'POST',
      data:{bill_id: bill_id,discount:discount}, 
      success:function(res){
 		    var json = $.parseJSON(res);
            if(json.success){
                toastr.success(json.msg);
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

$('#makeCurrent').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var discount = button.data('discount') // Extract info from data-* attributes
  var billID = button.data('id')
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
  modal.find('#billID').val(billID)
  // modal.find('#feeAmount').val(feeAmount)
  // modal.find('#ChalanID').val(recipient)
  // modal.find('#studentID').val(student_id)
  // modal.find('#fineamount').val(fine)
  // modal.find('#PaidAmount').val(feeAmount)
});

</script>

<?= $this->endSection() ?>