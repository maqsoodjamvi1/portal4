<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Registration Form</title>
<!-- <link href="Untitled1.css" rel="stylesheet">
<link href="index.css" rel="stylesheet"> -->
</head>
<body data-gr-c-s-loaded="true">
	
<!-- <div style="text-align:center"><span style="font-size:22px;">Registration Closed!!</span></div> -->
<?php //exit; ?>

<?php echo form_open_multipart('careers/actionCreate', 'role="form" autocomplete="off" class="" id="register-system-form"'); ?>
<input type="hidden" name="session_id" value="<?php echo $sessionid; ?>">
<div class="container" id="container">
<div id="wb_Text1" style="text-align:left;z-index:0;border:0px #C0C0C0 solid;overflow-y:hidden;background-color:transparent;">
<div style="font-family:Arial;font-size:13px;color:#000000;">
<div style="text-align:center"><span style="font-size:22px;"><u>THE PREP SCHOOL </u><br>Job Application Form</span></div>
</div>
</div>
<div class="col-lg-12" id="wb_Form1">
	<div class="row">
		<div class="col-lg-8 form-group col-lg-offset-2">
			<div class="row">
			<div class="col-lg-3 text-end" >
				<label for="Combobox1" id="Label1" style="">Apply Against the </label>
			</div>
			<div class="col-lg-9">
				<select class="col-lg-9 form-control" name="position" size="1" id="Combobox1" style="">
				<!-- <option value="E">M.A/ BS (English)</option> -->
				<!-- <option value="BM">B.Sc (with Maths)</option> -->
				<option selected value="BA">Graduation (Regular)</option>
				<option  value="T">Please Select</option>
				</select>
			</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-3 form-group">
			<!-- <div class="col-lg-3 text-end" > -->
				<label for="Label1" id="Label2" >Name of the Applicant:</label>	
			<!-- </div>	 -->
			<!-- <div class="col-lg-9"> -->
				<input type="text" id="name" name="name" class="form-control" required autocomplete="on" spellcheck="false">
			<!-- </div> -->
		</div>

		<div class="col-lg-3 form-group">
			<!-- <div class="col-lg-3 text-end" > -->
				<label for="Label1" id="Label2" >CNIC #:</label>	
			<!-- </div>	 -->
			<!-- <div class="col-lg-9"> -->
				<input type="text" class="form-control" id="cnic"   name="cnic" value="" autocomplete="on" spellcheck="false" required data-inputmask='"mask": "99999-9999999-9"' data-mask/>
			<!-- </div> -->
		</div>

		<div class="col-lg-3 form-group">
			<!-- <div class="col-lg-3 text-end" > -->
				<label for="Label1" id="Label5" >DOB:</label>
			<!-- </div>	 -->
			<!-- <div class="col-lg-9"> -->
				<input type="date" id="dob" class="form-control"  name="dob" autocomplete="on" spellcheck="false">
			<!-- </div> -->
		</div>
		<div class="col-lg-3 form-group">
				<label for="Label1" id="Label5" > Gender of the Candidate:</label>
				<div class="form-group">  
          <div class="candidate_genders">
           <label><input type="radio" name="gender" required value="m">&nbsp;&nbsp;&nbsp; Male&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <label><input type="radio" name="gender" checked required value="f">&nbsp;&nbsp;&nbsp; Female</label>
          </div>
        </div>
		</div>

	</div>
	<div class="row">
		<div class="col-lg-6 form-group">
			<!-- <div class="col-lg-3 text-end" > -->
				<label for="Label1" id="Label3" >Father`s Name:</label>
			<!-- </div>	 -->
			<!-- <div class="col-lg-9"> -->
				<input type="text" id="fname" class="form-control" name="fname" autocomplete="on" spellcheck="false" required/>
			<!-- </div> -->
		</div>

		<div class="col-lg-3 form-group">
			<!-- <div class="col-lg-3 text-end" > -->
				<label for="Label1" id="Label7" >WhatsApp&nbsp;#</label>	
			<!-- </div>	 -->
			<!-- <div class="col-lg-9"> -->
				<input type="text" id="cell" class="form-control" name="cell" value="" autocomplete="on" spellcheck="false"required/>
			<!-- </div> -->
		</div>

		<div class="col-lg-3 form-group">
			<!-- <div class="col-lg-3 text-end" > -->
				<label for="Label1" id="Label6" >Email ID </label>
			<!-- </div>	 -->
			<!-- <div class="col-lg-9"> -->
				<input type="email" id="email" class="form-control" name="email" autocomplete="on" spellcheck="false" required/>
			<!-- </div> -->
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12 form-group">
			<label for="Label1" id="Label9" >&#x1F3E1; Address:</label>
			<textarea name="rem" id="rem" class="form-control" rows="1" cols="96" spellcheck="true"></textarea>
		</div>
	</div>

<div id="wb_Line1" >
<img style="width: 100%;height: 8px;"  src="http://theprepschool.com.pk/tregt/img0001.png" id="Line1" alt="">
</div>
<div id="wb_Text4" >
<span style="color:#000000;font-family:Arial;font-size:16px;"><strong><u>Academic Qualification</u></strong></span>
</div>	
	<table class="table">
		<tr >
			<th></th>
			<th>Subject(s)</th>
			<th>Educational Institution</th>
			<th>Board / University</th>
			<th>Session</th>
			<th>%age</th>
			<th>Certificate</th>
		</tr>
		<tr>

		<th>Matriculation<input type="hidden" name="qualification[]" value="matric"></th>
			<th><input type="text" id="matrica"  class="form-control" name="subject[matric]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="matricb"  class="form-control" name="institution[matric]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="Editbox22" class="form-control"  name="board[matric]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="number" id="Editbox15" class="form-control"  name="session[matric]" value="" maxlength="10" autocomplete="on" spellcheck="false"></th>
			<th><input type="number" id="matricp" class="form-control"  name="percentage[matric]" value="" maxlength="4" autocomplete="on" spellcheck="false"></th>
			<th><input type="file" id="matricf" class="form-control" name="certificate_matric" value=""  autocomplete="on" spellcheck="false"></th>
		</tr>
		<tr >
			<th>Intermediate<input type="hidden" name="qualification[]" value="inter"></th>
			<th><input type="text" id="intera" class="form-control"  name="subject[inter]" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="Interb" class="form-control"  name="institution[inter]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="Editbox21" class="form-control"  name="board[inter]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="number" id="Editbox16" class="form-control"  name="session[inter]" value="" maxlength="10" autocomplete="on" spellcheck="false"></th>
			<th><input type="number" id="Interp" class="form-control"   name="percentage[inter]" value="" maxlength="4" autocomplete="on" spellcheck="false"></th>
			<th><input type="file" id="interf" class="form-control" name="certificate_inter" value=""  autocomplete="on" spellcheck="false"></th>
		</tr>
		<tr >
			<th>Graduation <br>(2 Yrs)<input type="hidden" name="qualification[]" value="graduation"></th>
			<th><input type="text" id="Graduationa" class="form-control"  name="subject[graduation]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="Graduationb" class="form-control"  name="institution[graduation]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="Editbox20" class="form-control"  name="board[graduation]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="number" id="Editbox17" class="form-control"  name="session[graduation]" value="" maxlength="10" autocomplete="on" spellcheck="false"></th>
			<th><input type="number" id="Graduationp" class="form-control"  name="percentage[graduation]" value="" maxlength="4" autocomplete="on" spellcheck="false"></th>
			<th><input type="file" id="graduationf" class="form-control" name="certificate_graduation" value=""  autocomplete="on" spellcheck="false"></th>
		</tr>
		<tr >
			<th>Post Graduation / BS (4 Years)<input type="hidden" name="qualification[]" value="pgraduation"></th>
			<th><input type="text" id="PGraduationa" class="form-control" name="subject[pgraduation]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="PGraduationb" class="form-control" name="institution[pgraduation]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="text" id="Editbox19" class="form-control" name="board[pgraduation]" value="" autocomplete="on" spellcheck="true"></th>
			<th><input type="number" id="Editbox18" class="form-control" name="session[pgraduation]" value="" maxlength="10" autocomplete="on" spellcheck="false"></th>
			<th><input type="number" id="PGraduationp" class="form-control" name="percentage[pgraduation]" value="" maxlength="4" autocomplete="on" spellcheck="false"></th>
			<th><input type="file" id="PGraduationf" class="form-control" name="certificate_pgraduation" value=""  autocomplete="on" spellcheck="false"></th>
		</tr>
	</table>
	<div id="wb_Line1" >
<img style="width: 100%;height: 8px;"  src="http://theprepschool.com.pk/tregt/img0001.png" id="Line1" alt="">
</div>
<div  id="wb_Text3" >
<span style="color:#000000;font-family:Arial;font-size:16px;margin-left: 5px;"><strong><u>Additional Qualification</u></strong></span>
</div>
<br>
<div class="col-lg-3" id="wb_Checkbox1" >
	<label for="Checkbox1"><input type="checkbox"  id="Checkbox1" name="mphil" value="M.Phil" >&nbsp;&nbsp;&nbsp;&nbsp; M.Phil</label>
</div>
<div class="col-lg-3"  id="wb_Checkbox2" >
	<label for="Checkbox2"><input type="checkbox" id="Checkbox2" name="tefl" value="TEFL" >&nbsp;&nbsp;&nbsp;&nbsp; TEFL</label>
</div>
<div class="col-lg-3"  id="wb_Checkbox3" >
	<label for="Checkbox3"><input type="checkbox"  id="Checkbox3" name="epm" value="EPM" >&nbsp;&nbsp;&nbsp;&nbsp; EPM</label>
</div>
<div class="col-lg-3"  id="wb_Checkbox4" >
	<label for="Checkbox4"><input type="checkbox"  id="Checkbox4" name="med" value="B/MEd" >&nbsp;&nbsp;&nbsp;&nbsp; M.Ed./ B.Ed. / BS Ed.</label>
</div>
<div class="col-lg-3"  id="wb_Checkbox5" >
	<label for="Checkbox5"><input type="checkbox"  id="Checkbox5" name="md" value="montessori diploma" >&nbsp;&nbsp;&nbsp;&nbsp; Montessori Diploma</label>
</div>
<div id="wb_Line1" >
<img style="width: 100%;height: 8px;"  src="http://theprepschool.com.pk/tregt/img0001.png" id="Line1" alt="">
</div>
<br>
<div id="wb_Text4" >
<span style="color:#000000;font-family:Arial;font-size:16px;"><strong><u>Teaching Experience</u></strong></span></div>
<table class="table table-bordered" id="dynamic_field"> 
    <thead>
        <tr>
            <td><label for="subject_name">Institution</label></td>
            <td> <label for="subject_name">Assignment</label></td>
            <td> <label for="subject_name">From</label></td>
            <td> <label for="subject_name">To</label></td>
            <td> <label for="subject_name">Experience Letter</label></td>
        </tr>
    </thead>	
    <tr>  
        <td>
        	<input type="hidden" name="rowscount[]" value="1" />	
        	<input type="hidden" name="id0" value="0">
        	<input type="text" name="institution0"  value="" placeholder="Institution" class="form-control name_list" />
        </td> 
        <td><input type="text" name="assignment0" value="" placeholder="Assignment" class="form-control name_list"  /></td>  
        <td><input type="date" name="from_date0" value="" placeholder="From Date" class="form-control name_list"  /></td> 
        <td><input type="date" name="to_date0" value="" placeholder="To Date" class="form-control name_list"  /></td> 
        <td><input type="file" name="experience_letter0" value=""  class="form-control name_list"  /></td> 
    </tr>

    <tr><td></td><td></td><td></td><td></td><td><button type="button" name="add" id="add" class="btn btn-success">Add More</button></td>  </tr>
</table>  
<input class="btn btn-primary" type="submit" id="submit" name="submit" value="submit" />
</div>
<div id="wb_Line2" >
	<img style="width: 100%;height: 8px;" src="http://theprepschool.com.pk/tregt/img0002.png" id="Line2" alt="">
</div>
</div>
<?php echo form_close();?>
<div id="loader"></div>
<style type="text/css">
	#loader {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  background: rgba(0,0,0,0.75) url(/assets/images/loading2.gif) no-repeat center center;
  z-index: 10000;
}
input[type='checkbox'] {
   
    width: 20px;
    height: 20px;
   
}
label {
    display: inline-flex;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: bold;
    line-height: 25px;
    vertical-align: middle;
}
#cnic-error,.error{font-size: 12px !important;}
#cnic-error a{margin-left: 5px !important;}
</style>
</body></html>

<script type="text/javascript">

$(document).ready(function(){      
  
      var i= 1
      //alert(i);
   
      $('#add').click(function(){  
        
           $('#dynamic_field').append('<tr id="row'+i+'" class="dynamic-added"><td><input type="hidden" name="id'+i+'" value="0"><input type="hidden" name="rowscount[]" value="1" /><input type="text" name="institution'+i+'" placeholder="Institution" class="form-control name_list" required /></td><td><input type="text" name="assignment'+i+'" placeholder="Assignment" class="form-control name_list" required /></td><td><input type="date" name="from_date'+i+'" placeholder="From Date" class="form-control name_list"  /></td><td><input type="date" name="to_date'+i+'" placeholder="To Date" class="form-control name_list"  /></td><td><input type="file" name="experience_letter'+i+'"  class="form-control name_list"  /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove btn-sm">X</button></td></tr>'); 
              i++;   
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
 }); 

 $(function(){
  //$('#max_students').select2();
  //$('#max_fee').select2();  
 $.validator.addMethod('filesize', function (value, element, param) {
    return this.optional(element) || (element.files[0].size <= param * 1000000)
}, 'File size must be less than {0} MB');

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
            url:'<?php echo base_url().'/ajax/check_candiate_cnic?table=recruitment&field=cnic';?>'
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
      certificate_matric:{
      	 	//extension: "jep | jpeg",
          filesize : 1, // here we are working with MB
      },
      certificate_inter:{
      	 	//extension: "jep | jpeg",
          filesize : 1, // here we are working with MB
      },
      certificate_graduation:{
      	 	//extension: "jep | jpeg",
          filesize : 1, // here we are working with MB
      },
      certificate_pgraduation:{
      	 	//extension: "jep | jpeg",
          filesize : 1, // here we are working with MB
      }
    },
    messages:{
      cnic:{
        remote:'CNIC already exists.To reprint slip <a href="https://portal.theprepschool.com.pk/careers/re_print">click here</a>'
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
      $('#loader').show();
    },
    success:function(responseText, statusText, xhr, form){
      //$('#errors').html(responseText);
      $('#loader').hide();
      var json = $.parseJSON(JSON.stringify(responseText));
      if(json.success){
        toastr.success(json.msg);
        location.href = '<?php echo base_url(); ?>/careers/appointment_slip?id='+json.id;
         
      }else{
        toastr.error(json.msg);
      }
      return false;
    }
  });
});
</script>	