<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){

		$header = 'Edit Fee Chalan';
		$id = $info->chalan_type_id;
		$chalan_type_name = $info->chalan_type_name;
		$chalan_type_detail = $info->chalan_type_detail;

	}else{
		$header = 'Generate Fee Chalan';
		$id = '';
		$chalan_type_name = '';
		$chalan_type_detail = '';

	}
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Generate Fee Chalan</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#/">Dashboard</a></li>
          <li class="breadcrumb-item active">Generate Fee Chalan</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="#/fee_chalan">Fee Chalan</a></li>
            <li class="nav-item"><a class="nav-link active" href="#/fee_chalan?m=add">Generate Fee Chalan</a></li>
          </ul>
        </div>
        <div class="card-body">
          <form role="form" id="user-edit-form" method="post" accept-charset="utf-8">
            <?= form_hidden('id', ''); ?>
            <div class="col-md-12 bg">
              <div id="loader-1" class="overlay" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
            </div>

            <!-- Fee Type Selection -->
            <div class="form-group">
              <label class="d-block mb-3">Select Fee Types to Include</label>
              
              <?php
              // Define fee type groups
              $feeGroups = [
                's_flag' => [
                  'label' => 'School Fees',
                  'types' => $fee_type_info ?? [],
                ],
                'a_flag' => [
                  'label' => 'Academy Fees',
                  'types' => $a_fee_type_info ?? [],
                ],
                't_flag' => [
                  'label' => 'Transport Fees',
                  'types' => $t_fee_type_info ?? [],
                ],
                'h_flag' => [
                  'label' => 'Hostel Fees',
                  'types' => $h_fee_type_info ?? [],
                ],
              ];

              foreach ($feeGroups as $flag => $group):
                if ($campusInfo->$flag != 1) continue;
              ?>
              <fieldset class="mb-4">
                <legend class="h6"><?= $group['label'] ?></legend>
                <div class="row">
                  <?php if (!empty($group['types'])): ?>
                    <?php foreach ($group['types'] as $fee_type): ?>
                      <div class="col-lg-2 mb-3">
                        <div class="icheck-primary d-inline">
                          <input type="checkbox" name="fee_type_name[]" 
                                 id="ft_<?= $fee_type->fee_type_id ?>" 
                                 value="<?= $fee_type->fee_type_id ?>">
                          <label for="ft_<?= $fee_type->fee_type_id ?>">
                            <?= htmlspecialchars($fee_type->fee_type_name) ?>
                          </label>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <div class="col-12">
                      <div class="alert alert-warning">No fee types available for this category</div>
                    </div>
                  <?php endif; ?>
                </div>
              </fieldset>
              <?php endforeach; ?>
            </div>

            <!-- Date and Amount Fields -->
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label>Issue Date:</label>
                  <div class="input-group date" id="datepicker2" data-target-input="nearest">
                    <input type="text" name="issue_date" autocomplete="off" 
                           class="form-control datetimepicker-input" 
                           data-toggle="datetimepicker" data-target="#datepicker2">
                    <div class="input-group-append" data-target="#datepicker2" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="form-group">
                  <label>Due Date:</label>
                  <div class="input-group date" id="datepicker" data-target-input="nearest">
                    <input type="text" name="due_date" autocomplete="off" 
                           class="form-control datetimepicker-input" 
                           data-toggle="datetimepicker" data-target="#datepicker">
                    <div class="input-group-append" data-target="#datepicker" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="form-group">
                  <label>Fee Month:</label>
                  <input type="month" class="form-control" 
                         name="fee_month" 
                         value="<?= date('Y-m') ?>">
                </div>
              </div>

              <div class="col-sm-6">
                <div class="form-group">
                  <label>Fine Month:</label>
                  <input type="month" class="form-control" name="fine_month">
                </div>
              </div>
            </div>

            <!-- Chalan Messages -->
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label>Chalan Header Message</label>
                  <div class="input-group mb-3">
                    <input type="text" class="form-control" id="chalan_h_msg" 
                           name="chalan_h_msg" 
                           value="<?= htmlspecialchars($campusInfo->chalan_h_msg ?? '') ?>">
                    <div class="input-group-append">
                      <button class="btn btn-primary" id="btn_h_msg" type="button">
                        Save Message
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="form-group">
                  <label>Chalan Footer Message</label>
                  <div class="input-group mb-3">
                    <input type="text" class="form-control" id="chalan_f_msg" 
                           name="chalan_f_msg" 
                           value="<?= htmlspecialchars($campusInfo->chalan_f_msg ?? '') ?>">
                    <div class="input-group-append">
                      <button class="btn btn-primary" id="btn_f_msg" type="button">
                        Save Message
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Fine Settings -->
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label for="fine_type">Fine Type</label>
                  <select name="fine_type" class="form-control">
                    <option value="per_day_fine" <?= ($campusInfo->fine_type ?? '') === 'per_day_fine' ? 'selected' : '' ?>>Per Day Fine</option>
                    <option value="fixed_fine" <?= ($campusInfo->fine_type ?? '') === 'fixed_fine' ? 'selected' : '' ?>>Fixed Fine</option>
                  </select>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="form-group">
                  <label>Fine Amount</label>
                  <div class="input-group mb-3">
                    <input type="number" class="form-control" id="late_fee_fine" 
                           name="late_fee_fine" 
                           value="<?= htmlspecialchars($campusInfo->late_fee_fine ?? 0) ?>">
                    <div class="input-group-append">
                      <button class="btn btn-primary" id="btn_late_fee" type="button">
                        Save Fine
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="col-sm-12">
              <div class="form-group">
                <button type="submit" id="submitBtn" class="btn btn-primary mr-2">
                  Generate Fee Chalan
                </button>
                <button type="button" class="btn btn-default" onclick="history.go(-1);">
                  Cancel
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- JavaScript remains unchanged -->
<!-- /.content -->
<script type="text/javascript">
$(function(){

		$("#btn_h_msg").click(function(){
				var chalan_h_msg = $('#chalan_h_msg').val();
	       $.ajax({
	            url:'<?php echo base_url('admin/fee_chalan/updateChalanSetting'); ?>', 
	            type: "POST",
	            data:{chalan_h_msg:chalan_h_msg},
	            success:function(res){
	            	var json = $.parseJSON(res);
	 			   				if(json.success){
										toastr.success(json.msg);
									}else{
										toastr.error('Update Error');
									}
				 			}
	 	});
	});

	$("#btn_f_msg").click(function(){
		   var chalan_f_msg = $('#chalan_f_msg').val();
	       $.ajax({
	            url:'<?php echo base_url('admin/fee_chalan/updateChalanSetting'); ?>', 
	            type: "POST",
	            data:{chalan_f_msg:chalan_f_msg},
	            success:function(res){
	            	var json = $.parseJSON(res);
	   				if(json.success){
						toastr.success(json.msg);
					}else{
						toastr.error('Update Error');
					}
	 			}
	 	     });
	});	

	$("#btn_late_fee").click(function(){
				var late_fee_fine = $('#late_fee_fine').val();
	       $.ajax({
	            url:'<?php echo base_url('admin/fee_chalan/updateChalanSetting'); ?>', 
	            type: "POST",
	            data:{late_fee_fine:late_fee_fine},
	            success:function(res){
	            	var json = $.parseJSON(res);
	 			   				if(json.success){
										toastr.success(json.msg);
									}else{
										toastr.error('Update Error');
									}
				 			}
	 	});
	});	

	var myDate = new Date(new Date().getTime()+(10*24*60*60*1000));
  //Date picker
    $('#datepicker').datetimepicker({
      format: 'DD/MM/YYYY',
      defaultDate:myDate
    });
	 $('#datepicker2').datetimepicker({
       format: 'DD/MM/YYYY',
       defaultDate:'now'
    });


 });
</script>

<script>
    $(document).ready(function () {
        $('#loader').hide();
        $('#user-edit-form').validate({// <- attach '.validate()' to your form
            // Rules for form validation
            rules: {
                first_name: {
                    required: true
                },
                father_name: {
                    required: true
                },
                 cnic: {
                    required: true
                }
            },
            // Messages for form validation
            messages: {
                first_name: {
                    required: 'Enter First Name'
                },
                cnic: {
                    required: 'Enter CNIC'
                },
                father_name: {
                    required: 'Enter Father Name'
                }
            },
            submitHandler: function (form) {

                    var myData = new FormData($("#user-edit-form")[0]);
                    //var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                   // myData.append('_token', CSRF_TOKEN);
                    // myData.append('roles', list_id);


                    swal({
                        title: "Confirm to Generate Chalan",
                        text: "Generate Chalan!",
                        type: "warning",
                        showCancelButton: true,
                        closeOnConfirm: false,
                        showLoaderOnConfirm: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "Yes, Generate Chalan!"
                    }, function () {

                        $.ajax({
                            url: 'admin.php?c=fee_chalan&m=save',
                            type: 'POST',
                            data: myData,
                            dataType: 'json',
                            cache: false,
                            processData: false,
                            contentType: false,
                            beforeSend: function () {
                                $('#loader').show();
                                $("#submit").prop('disabled', true); // disable button
                            },
                            success: function (data) {

                                if (data.type === 'success') {
                                    swal("Done!", "It was succesfully done!", "success");
                                    location.href = '#/fee_chalan';
                                    notify_view(data.type, data.message);
                                    $('#loader').hide();
                                    $("#submit").prop('disabled', false); // disable button
                                    $("html, body").animate({scrollTop: 0}, "slow");
                                    $('#myModal').modal('hide'); // hide bootstrap modal

                                } else if (data.type === 'error') {
                                    if (data.errors) {
                                        $.each(data.errors, function (key, val) {
                                            $('#error_' + key).html(val);
                                        });
                                    }
                                    $("#status").html(data.message);
                                    $('#loader').hide();
                                    $("#submit").prop('disabled', false); // disable button
                                    swal("Error sending!", data.message, "error");

                                }

                            }
                        });
                    });

                
            }
            // <- end 'submitHandler' callback
        });                    // <- end '.validate()'

    });
    </script>

<?= $this->endSection() ?>