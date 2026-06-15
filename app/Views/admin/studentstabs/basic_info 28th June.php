  <!-- Begin Admission Form Container -->
<div class="container-fluid px-3">
  <div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Student Admission Form</h5>
    </div>
    <div class="card-body">
      
      <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success">
      <?= session()->getFlashdata('success') ?>
  </div>
<?php endif; ?>

      <?= form_open(base_url('admin/students/save_basicinfo'), ['id' => 'students-basic-form']) ?>
      <?php echo form_hidden('id', $id); ?>
      <?php echo form_hidden('campus_id', $campus_id); ?>
      <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>"  /> 

      <div class="row">
        <!-- Registration No -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="reg_no">Registration No</label>
          <input type="text" readonly class="form-control form-control-sm" name="reg_no" id="reg_no" value="<?php echo $reg_no; ?>">
        </div>

        <!-- First Name -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="first_name">First Name <span class="text-danger">*</span></label>
          <input type="text" required class="form-control form-control-sm" name="first_name" id="first_name" value="<?php echo $first_name; ?>">
        </div>

        <!-- Last Name -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="last_name">Last Name</label>
          <input type="text" class="form-control form-control-sm" name="last_name" id="last_name" value="<?php echo $last_name; ?>">
        </div>

        <!-- Gender -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label>Gender <span class="text-danger">*</span></label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="gender" value="male" id="male" <?php if($gender == "male"){ echo "checked"; } ?>>
            <label class="form-check-label" for="male">Male</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="gender" value="female" id="female" <?php if($gender == "female"){ echo "checked"; } ?>>
            <label class="form-check-label" for="female">Female</label>
          </div>
        </div>

        <!-- Student CNIC -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="student_cnic">Student CNIC <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" name="student_cnic" id="student_cnic" value="<?php echo $student_cnic; ?>" data-inputmask='"mask": "99999-9999999-9"' data-mask>
        </div>

        <!-- Father CNIC -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="father_cnic">Father CNIC <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" required name="father_cnic" id="father_cnic" value="<?php echo $father_cnic; ?>" onkeyup="checkfathercnic()" data-inputmask='"mask": "99999-9999999-9"' data-mask>
          <small><a href="#" data-bs-toggle="modal" class="btn btn-link btn-sm p-0" data-bs-target="#createParent" data-id="<?php echo $id; ?>">Update Parent Info</a></small>
        </div>

        <!-- Father Name -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="f_name">Father Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" name="f_name" id="f_name" value="<?php echo $f_name; ?>">
        </div>

        <!-- Religion -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="religion">Religion</label>
          <input type="text" class="form-control form-control-sm" name="religion" id="religion" value="<?php echo empty($religion) ? 'Islam' : $religion; ?>">
        </div>

        <!-- Caste -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="caste">Caste</label>
          <input type="text" class="form-control form-control-sm" name="caste" id="caste" value="<?php echo $caste; ?>">
        </div>

        <!-- GR No -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="gr_no">G.R #</label>
          <input type="text" class="form-control form-control-sm" name="gr_no" id="gr_no" value="<?php echo $gr_no; ?>">
        </div>

        <!-- GR Date -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label>G.R Date <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" name="gr_date" id="datepicker_gr" value="<?php echo $gr_date; ?>">
        </div>

        <!-- Admission Date -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label>Date of Admission <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" name="date_of_admission" id="datepicker_admission" value="<?php echo $date_of_admission; ?>">
        </div>

        <!-- Section -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="section_id">Section <span class="text-danger">*</span></label>
          <select class="form-control form-control-sm" name="section_id" id="section_id" required>
            <option value="">Select Section</option>
            <?php foreach ($sectionsclassinfo as $sectionvalue): ?>
              <option value="<?php echo $sectionvalue['section_id']; ?>" <?php if($sectionvalue['section_id'] == $section_id) echo "selected"; ?>>
                <?php echo $sectionvalue['sectionclassname']; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Class Fee -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="class_fee">Class Fee <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" name="class_fee" id="class_fee" value="<?php echo $classesfee; ?>" required>
        </div>

        <!-- Student Fee -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="discounted_amount">Student Fee</label>
          <input type="text" class="form-control form-control-sm" name="discounted_amount" id="discounted_amount" value="<?php echo ($classesfee - $discounted_amount); ?>">
        </div>

        <!-- Transport Fee -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="transport_fee">Transport Fee</label>
          <input type="text" class="form-control form-control-sm" name="transport_fee" id="transport_fee" value="<?php echo ($transportfee - $transport_discount); ?>">
        </div>

        <!-- Fee Plan -->
        <div class="col-md-6 col-lg-3 mb-3">
          <label for="fee_plan">Student Fee Plan</label>
          <select class="form-control form-control-sm" name="fee_plan">
            <option value="0" <?php echo $fee_plan == 0 ? "selected" : ""; ?>>Monthly</option>
            <?php foreach ($fee_plans as $value): ?>
              <option value="<?php echo $value->plan_id; ?>" <?php if($value->plan_id == $fee_plan) echo "selected"; ?>>
                <?php echo $value->plan_name; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

  <!-- Form Buttons -->
      <div class="text-end mt-4">
        <button type="submit" id="submitBtn" class="btn btn-success">Save</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
        <button type="button" class="btn btn-danger" onclick="history.back();">Cancel</button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

     
<!-- Modal for Updating Parent Info -->
<div class="modal fade" id="createParent" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
        <form id="parentInfoUpdate">    
        <div class="modal-header">
          <h5 class="modal-title float-start" id="exampleModalLabel">Update Parent Info</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="studentID" name="student_id">
          <label>Father CNIC</label>
          <input type="text" name="f_cnic" class="form-control" id="f_cnic" data-inputmask='"mask": "99999-9999999-9"' data-mask>
          <label>Father Name</label>
          <input type="text" name="father_name"  class="form-control" id="father_name">
        </div>
        <div class="modal-footer"> 
        <button type="button" id="createNewParent" class="btn btn-primary">Update Parent</button> 
        </div>
        </form>
        </div>
      </div>
    </div>
 <script>
$(function(){

    $('#createNewParent').click(function(){
            
         $.ajax({
            url: 'admin/students/updateParentInfo',
            type: 'POST',
            data:$('#parentInfoUpdate').serialize(),
            success:function(res){
             toastr.success('Updated Successfully');
            }
         });

        //$('#updatediscount').modal('hide');
    });

    $('#createParent').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget) // Button that triggered the modal
      var discount = button.data('discount') // Extract info from data-* attributes
      var studentID = button.data('id')

    $('#f_cnic').keyup(function(){
    var f_cnic = $('#f_cnic').val();

    $.ajax({
            url: '/admin/students/getParentInfo',
            type: "POST",
            data:{f_cnic: f_cnic},
            success:function(res){
             if(res){
               $("#father_name").val(res);
             }else{
                $("#father_name").val('');
             }
          
         }
         });
    });
      // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
      // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
      var modal = $(this)
      modal.find('#discount').val(discount)
      modal.find('#studentID').val(studentID)
      
    });


  $("#section_id").change(function(){
        var section_id = $('#section_id').val();
         $.ajax({
            url: '/admin/ajax/select-class-fee',
            type: "POST",
            data:{section_id:section_id },
            success:function(res){
              $("#class_fee").val(res);
            }
         });
    });

  $('[data-mask]').inputmask();
 
  $('#datepicker2').datetimepicker({
      format: 'DD/MM/YYYY'
    });

});


</script>   
<script>
    $(document).ready(function () {
        $('#loader').hide();
        $('#students-basic-form').validate({// <- attach '.validate()' to your form
            // Rules for form validation
            rules: {
                first_name: {
                    required: true
                },
                f_name: {
                    required: true
                },
                father_cnic: {
                    required: true
                }
            },
            // Messages for form validation
            messages: {
                first_name: {
                    required: 'Enter First Name'
                },
                cnic: {
                    required: 'father CNIC is Required'
                },
                f_name: {
                    required: 'Enter Father Name'
                }
            },
            
            submitHandler: function (form) {
               event.preventDefault();

            var myData = new FormData(form);

            swal({
                title: "Confirm Student Save",
        text: "Do you want to proceed?",
        type: "warning",
                showCancelButton: true,
                 confirmButtonText: "Yes",
                showLoaderOnConfirm: true,
        closeOnConfirm: false
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Assign!"
            }, function () {

                $.ajax({
                    url: '/admin/students/save_basicinfo',
                    type: 'POST',
                    data: myData,
                    dataType: 'json',
                    cache: false,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        $('#loader').show();
                        $("#submitBtn").prop('disabled', true); // disable button
                    },
                    success: function (data) {

                        if (data.type === 'success') {
                            swal("Done!", "It was succesfully done!", "success");
                           // location.href = 'admin/students/edit?id='+data.student_id;

                           // Enable other tabs dynamically
                            $("#tab-contact").removeClass("disabled");
                            $("#tab-general").removeClass("disabled");
                            $("#tab-attachments").removeClass("disabled");
                              $('#custom-tabs a[href="#tab-contact-pane"]').tab('show');
                            notify_view(data.type, data.message);
                            $('#loader').hide(); 
                            $("#submit").prop('disabled', false); // disable button
                            $("html, body").animate({scrollTop: 0}, "slow");
                            $('#myModal').modal('hide'); // hide bootstrap modal

                        } else {
                            if (data.errors) {
                                $.each(data.errors, function (key, val) {
                                    $('#error_' + key).html(val);
                                });
                            }
                            $("#status").html(data.message);
                            $('#loader').hide();
                            $("#submitBtn").prop('disabled', false); // disable button
                            swal("Error sending!", "Please try again", "error");

                        }

                    }
                });
            });      
          }
          // <- end 'submitHandler' callback
      });                    

      // <- end '.validate()'

    });
    </script>