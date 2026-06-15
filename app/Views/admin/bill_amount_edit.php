<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
$header = isset($info) ? 'Edit Bill Amount' : 'Add Bill Amount';
$campus_id = (int) ($campus_id ?? session('member_campusid') ?? 0);
?>
    <?= view('components/page_header', [
    'title' => 'Bill Amount',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Bill Amount', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		  <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
      <div class="card-body">
			<div class="tab-content">
			<?php
			echo form_open(base_url('admin/bill_amount/save'), 'role="form" id="user-edit-form"');
			echo form_hidden('campus_id', (string) $campus_id);
			?>
			<div class="">
			<div class="col-lg-12">
	        	<div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
	    	</div>
			<div id="feeamountarea" class="feeamountarea"></div>
              <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary me-2">Save</button>
								<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
              </div>
            <?php echo form_close();?>
			</div>
		  </div>
        </div>
      </div>
       </div>
      </div>
    </section>
    <!-- /.content -->
<script type="text/javascript">
$(function(){
    var campusId = <?= (int) $campus_id ?>;

    function hideLoader() {
        $("#loader-1").css("display", "none");
    }

    function loadBillAmountGrid() {
        if (campusId < 1) {
            hideLoader();
            $("#feeamountarea").html('<p class="text-danger">Campus is not selected. Choose a campus from the header, then reload this page.</p>');
            return;
        }

        $("#loader-1").css("display", "block");
        $.ajax({
            url: '<?= base_url('admin/bill_amount/data') ?>',
            type: 'POST',
            data: {
                campus_id: campusId,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function (res) {
                $("#feeamountarea").html(res);
                hideLoader();
            },
            error: function () {
                hideLoader();
                $("#feeamountarea").html('<p class="text-danger">Could not load bill amounts. Please refresh and try again.</p>');
                if (typeof toastr !== 'undefined') {
                    toastr.error('Could not load bill amounts.');
                }
            }
        });
    }

    loadBillAmountGrid();

$('#user-edit-form').validate({
	rules:{
		amount:{
			required:true,
		}
	},
	messages:{
		amount:{
			required:'Bill amount is required',
			}
	}
});
$('#user-edit-form').ajaxForm({
		beforeSubmit:function(formData, jqForm, options){
			return $('#user-edit-form').valid();
			$('#submitBtn').html("Saving");
      		$('#submitBtn').prop('disabled', true);
		},
		success:function(responseText, statusText, xhr, form){
			$('#submitBtn').html("Save");
      		$('#submitBtn').prop('disabled', false);
			var json = $.parseJSON(responseText);
			if(json.success){
				toastr.success(json.msg);
				location.reload();			
			}else{
				toastr.error(json.msg);
			}
			return false;
		}
	});
});
</script>

<?= $this->endSection() ?>
