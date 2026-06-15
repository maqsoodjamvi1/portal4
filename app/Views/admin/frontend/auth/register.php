<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<div class="container-fluid ">
  <div class="container">
    <h1 class="main_top_heading" style="text-align: center;margin-top: 50px;margin-bottom: 0;">STUDENTS' ADMISSIONS ... ACADEMIC SESSION 2023-24</h1>
  </div>
</div>
<div class="container-fluid ">
  <div class="container">
    <h1 class="main_top_heading" style="text-align: center;">REGISTRATION FORM</h1>
  </div>
</div>
<div class="container">
  <!-- <div class="row">
    <div class="col-lg-12">
        <div class="form-group float-end">                    
            <span class="small ">Already Registered User?</span> <a href="<?php print site_url(); ?>signin" class="small">Click here to login</a>
        </div>
    </div>                
  </div> -->
	<div class="row" style="margin-bottom:30px;">
	</div>
	<div class="row" style="margin-bottom:50px;">
    <div class="col-md-12"> 
       <b style="
    text-align: left;
    display: block;
"><span style="padding-left:15px;
    font-size: 20px;
    text-transform: uppercase;
    text-align: left;
">Please pay attention</span> </b>
  <ul style="padding-left:15px;">
  <li><span style="font-size:18px;">Provide correct information only. <br>(Any wrong data, entered now, may leads to cancellation of admission at any stage.)<br></span></li>
    <br><li><span style="font-size:18px;"> Fill in the given Registration Form and then click the <b>'Enter'</b> button to get the auto-generated Appointment Slip.</span></li>
<br><br>
</ul>
<div id="errors"></div>

<?php //echo form_open('auth/actionCreate'); ?>
<?php echo form_open('auth/actionCreate', 'role="form" autocomplete="off" class="form-horizontal" id="register-system-form"'); ?>
<div class="">
<div class="col-lg-6 mb-5"> Select Campus in which Admission is Required</div>
<div class="col-lg-6 mb-5">
  <div class="form-group"> 
    <select required class="form-control" id="campus" name="campus" >
    <option value="">Select Campus</option>
    <?php foreach ($campus_data as $key => $campus_value) { ?>
      <?php if($campus_value['campus_id'] != 18){ ?>
        <option value="<?php echo $campus_value['campus_id']; ?>"><?php echo $campus_value['campus_name'];   ?></option>
      <?php } ?>
    <?php } ?>
    </select>
  </div>
</div>
</div>
<div class="">
<div class="col-lg-6"> Select Level in which Admission is Required</div>
<div class="col-lg-6">
  <div class="form-group">
    <select class="form-control" name="class" id="class" required></select>
  </div>
</div>
</div>
<div style="clear:both;" class="card border-primary">
    <div class="card-header bg-primary text-white">Candidate's Particulars</div>
    <div class="card-body">
      <div class="">
      <div class="col-lg-6">Candidate's Name:</div>
      <div class="col-lg-6">
        <div class="form-group">
          <input class="form-control" type="text" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || event.charCode == 32" name="name" required>
        </div>
        </div> 
      </div>
      <div class="">
      <div class="col-lg-6"> Gender of the Candidate:</div>
      <div class="col-lg-6">
        <div class="form-group">  
          <div class="candidate_genders">
            <label><input type="radio" name="gender" required value="b"> Boy</label>
            <label><input type="radio" name="gender" required value="g"> Girl</label>
          </div>
        </div>
      </div>
      </div>
      <div style="display:none;" class="studentCnic">
        <div class="col-lg-6">
          <b>CNIC #:</b> <br>(Candidate's CNIC)
        </div>
        <div class="col-lg-6">
          <div class="form-group">
            <input class="form-control" type="text" id="student_cnic"  name="student_cnic" required data-inputmask='"mask": "99999-9999999-9"' data-mask>
          </div>
        </div>
      </div>

      <div class="">
        <div class="col-lg-6">
        Candidate's Date of Birth:
        </div>
        <div class="col-lg-6">
          <div id="dateOfBirth">
               <div class="form-group">
                  <div class='input-group date' id='datetimepicker3'>
                     <input name="dob" type='text' class="form-control" autocomplete="off" required />
                     <span class="input-group-text">
                     <span class="fas fa-calendar-alt"></span>
                     </span>
                  </div>
               </div>
              <script type="text/javascript">
                 $(function () {
                     $('#datetimepicker3').datetimepicker({
                       viewMode: 'years',
                       format: 'YYYY-MM-DD'
                     });
                 });
              </script>
          </div>
      </div>
      </div>

    </div>
</div>
<div style="clear:both;" class="card border-primary">
    <div class="card-header bg-primary text-white">Parent's Particulars</div>
    <div class="card-body">

  <div class="col-lg-6">
  Father's CNIC #: <br>
  <small style="font-size: 12px;">(In case of any other CNIC # the school may cancel the admission at any stage).</small> 
  </div>
   <div class="col-lg-6">
    <div class="form-group">
    <input class="form-control" type="text" id="cnic"  name="cnic" required 
     data-inputmask='"mask": "99999-9999999-9"' data-mask >
    <input type="hidden" id="originalcnic"    value="">
    </div>
  </div>

  <div class="col-lg-6">
  Father's Name:
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || event.charCode == 32" type="text" name="fname" required>
    </div>
  </div> 

  <div class="col-lg-6">
    Cellular Phone Number:
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control" type="text" id="cell" name="cell" required >
      <!-- data-inputmask="'mask': '0399-999999999'"  type = "number" maxlength = "12"  data-mask -->
    </div>
  </div> 

  <div class="col-lg-6">
    Cellular Phone Number (Additional):
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control"  type="text" name="cell2" data-inputmask="'mask': '0399-999999999'"  type = "number" maxlength = "12"  data-mask>
    </div>
   </div> 

  <div class="col-lg-6">
    Landline Number:
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control" type="text" name="landline" data-inputmask="'mask':'051-99999999'"  maxlength="11" minlength="11"  data-mask>
    </div>
  </div> 

  <div class="col-lg-6">
    Address:
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control" type="text"  name="address" required  autocomplete="off" >
   </div>
   </div> 

  <div class="col-lg-6">
    Password:<br>
    <small style="font-size: 12px;">( Please enter at least 8 characters: digit / alphabet)</small>
  </div>
  <div class="col-lg-6">
    <div class="form-group">
      <input class="form-control"  name="password"  type="password" minlength="8" required autocomplete="off">
    </div>
   </div> 
</div>
</div>
<div class="col-lg-12" style="margin-bottom:15px;">
  This Registration Form is NOT to be considered as Admission Form. 
</div>
<div class="col-lg-12">
		    <div class="form-group"> 
          <label for="email" class="col-sm-4 control-label"></label>
          <div class="col-sm-5">
           <button type="submit" style="height: 50px;" class="btn btn-success form-control">Submit</button> 
          </div>
        </div>
        <div class="col-md-4 offset-4"> 
          <p class="alert alert-danger" style="display:none;" id="msgs"></p>
        </div>
</div>
      <?php echo form_close();?>
    </div>
	</div>
</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="<?= base_url('assets/js/datetimepicker-compat.js?v=20260614') ?>"></script>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/maskJquery.js"></script> 
<script type="text/javascript">
(function($) {
$.fn.extend({

        mask2: function(mask, settings){
           //store in data
            this.data('data-mask',mask);
            this.data('data-maskLength', mask.length);
            
            //add data attributes as html markups (optional)
            this.attr('data-mask',mask);
            this.attr('data-maskLength', mask.length);
            
           return this.mask(mask, settings);
        }
});    
})(jQuery);

//$("#cell").mask2("0399-9999999");
//$("#cnic").mask2("99999-9999999-9");

$(document).ready(function() {
  $(window).keydown(function(event){
    if(event.keyCode == 13) {
      event.preventDefault();
      return false;
    }
  });
});

  $(function(){
  //$('#max_students').select2();
  //$('#max_fee').select2();  

  $('#datepicker').datetimepicker({
     format: 'YYYY-MM-DD'
   });
  $('#datepicker2').datetimepicker({
      format: 'YYYY-MM-DD'
  });
    
  $('[data-mask]').inputmask();
  $('#register-system-form').validate({
    rules:{
      campus:{
        required:true,
      },
        cnic:{
        required:true,
        remote:{
          param:{
            url:'<?php echo base_url().'/ajax/check_parent_value?table=parents&field=father_cnicnew';?>'
          },
          depends:function(element){
            var id = $(element).attr('id');
            return ($(element).val() !== $('#original' + id).val());
          }
        }
      },
      email:{
        email:true,
        remote:{
          param:{
            url:'<?php echo base_url().'/ajax/check_parent_email?table=parents&field=father_email';?>' 
          },
          depends:function(element){
            var id = $(element).attr('id');
            return ($(element).val() !== $('#original' + id).val());
          }
        }
      },
    },
    messages:{
      cnic:{
        remote:'CNIC already exists. Login with existing account'
      },
      email:{
        required:'Email is Required',
        email:'Invalid Email',
        remote:'Email is exists'
      },
      campus:{
        required:'campus is required',
      }
    }
  });
  $('#register-system-form').ajaxForm({
    beforeSubmit:function(formData, jqForm, options){
      return $('#register-system-form').valid();
    },
    success:function(responseText, statusText, xhr, form){
      $('#errors').html(responseText);
      var json = $.parseJSON(responseText);
      if(json.success){
        toastr.success(json.msg);
        location.href = '<?php echo base_url(); ?>appointment_slip';
         
      }else{
        toastr.error(json.msg);
      }
      return false;
    }
  });
});
	
$("#campus").change(function(){
    var campus = $('#campus').val();
    
   $.ajax({
        url: '<?php echo base_url(); ?>/ajax/get_campus_classes',
        type: "POST",
        data:{campus:campus},
        success:function(res){
		      $("#class").html(res);
	     }
     });
});

$("#class").change(function(){
  var class_id = $(this).val();
  if(class_id == 4 || class_id == 5){
    $('.studentCnic').show();
  }else{
    $('.studentCnic').hide();
  }

  $.ajax({
        url: '<?php echo base_url(); ?>/ajax/getAgeCriteria',
        type: "POST",
        data:{class_id:class_id},
        success:function(res){
          $("#dateOfBirth").html(res);
       }
  });

  $.ajax({
        url: '<?php echo base_url(); ?>/ajax/getGenderCriteria',
        type: "POST",
        data:{class_id:class_id},
        success:function(res){
          $(".candidate_genders").html(res);
       }
  });

});



</script>