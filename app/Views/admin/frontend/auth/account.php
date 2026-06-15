<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<!-- <div class="container-fluid top_banner_sub">
	<div class="container">
		<h1 class="main_top_heading">REGISTRATION FORM ... ACADEMIC SESSION 2023-24</h1>
	</div>
</div> -->
<!-- <div class="container-fluid ">
  <div class="container">
    <h1 class="main_top_heading" style="text-align: center;margin-top: 50px;margin-bottom: 0;">STUDENTS' ADMISSIONS ... ACADEMIC SESSION 2023-24</h1>
  </div>
</div>
<div class="container-fluid ">
  <div class="container">
    <h1 class="main_top_heading" style="text-align: center;">COMING SOON</h1>
  </div>
</div> -->
<?php //exit; ?>
<div class="container">
  <!-- <div class="row">
    <div class="col-lg-12">
        <div class="form-group float-end">                    
            <span class="small ">Already Registered User?</span> <a href="<?php print site_url(); ?>signin" class="small">Click here to login</a>
        </div>
    </div>                
  </div> -->
	<div class="row" style="margin-bottom:125px;">
	</div>
	<div class="row" style="margin-bottom:50px;min-height: 200px;">
    <div class="col-md-12 text-center"> 
      <a class="btn btn-lg btn-primary" href="<?php print site_url(); ?>index.php/signup">New Registration </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; OR &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 <a class="btn btn-lg btn-primary" href="<?php print site_url(); ?>index.php/signin">Login</a>
    </div>
	</div>
</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="<?= base_url('assets/js/datetimepicker-compat.js?v=20260614') ?>"></script>
<script type="text/javascript">
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
        location.href = '<?php echo base_url(); ?>profile';
         
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