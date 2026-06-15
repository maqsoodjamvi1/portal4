<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php $id = 0; ?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
<!-- Content Header (Page header) -->
<style>
@media print
{
.pagebreak { page-break-before: always; }
}
th{ text-align: center; }
.select2-container--default .select2-selection--single, .select2-selection .select2-selection--single{
  border: 1px solid #d2d6de;
   
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    right: 3px;
}
.table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td {
   border: 1px solid #000 !important;
    vertical-align: middle;
    font-weight: normal;
}
.leftdate{
  font-weight: normal;
  text-align: left;
}
.rightdata{
  font-weight: normal;
  text-align: right;
}
.form-group{
    margin-bottom: 0px;
}
</style>
 <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>
               Pay Fee Chalan 2
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Pay Fee Chalan 2</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
      <div class="card-body">
      <div class="tab-content">
			<?php
			echo form_open('c=fee_chalan_pay2&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
      <div class="row">
       <div class="form-group col-lg-4">
        <label>Date Paid:</label>
           <div class="input-group date" id="datepicker2" data-target-input="nearest">
              <input style="height:30px;" type="text" id="datePaid" name="paid_date" autocomplete="off" class="form-control datetimepicker-input" data-bs-target="#datepicker2"/>
              <span class="input-group-text" data-bs-target="#datepicker2" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
          </div>
          <!-- /.input group -->
        </div>	
        <div class="col-lg-4">
            <label for="class">Class</label><br>
            <select class="form-control select2"  name="cls_sec_id" id="cls_sec_id" required="required" style="height: 30px !important;width: 100%;padding: 0;">
                  <option value="0">Select Section</option>
                  <?php if(isset($sectionsclassinfo)){
                  foreach ($sectionsclassinfo as  $secionvalue) { ?>
                  <option value="<?php echo $secionvalue['section_id']; ?>"><?php echo $secionvalue['sectionclassname']; ?></option>
                  <?php } ?>
                  <?php } ?>
            </select>
       </div>
       <div class="form-group col-lg-4">
          <label for="class">Reg No</label><br>
          <input type="text" style="height: 30px !important;width: 100%;padding: 0 5px;" class="form-control select2" name="reg_no" id="reg_no" >
        </div>  
        </div>
        <div class="row">		  
        <div class="form-group col-lg-4">
          <label for="class">Student Name</label><br>
          <select class="form-control select2" name="student_id" id="student_id" >
             <option value="0">Select Student</option>   
          </select>
        </div>	
        <div class="form-group col-lg-4">
          <label for="class">Parent Name</label><br>
          <select class="form-control select2" name="parent_id" id="parent_id" >
             <option value="0">Select Parent</option>   
          </select>
        </div>  
        <div class="form-group col-lg-4">
          <label for="class">Family ID</label><br>
          <input type="text" style="height: 30px !important;width: 100%;padding: 0 5px;" class="form-control select2" name="parent_id" id="parent_id" >
        </div>  
      </div>
      <div id="feetypeinfo" style="">
      <table style="width: 200px;margin-bottom: 30px;">
           <tbody id="tbody">
           </tbody>
      </table>
      </div>
          <?php echo form_close();?>
         </div>
      </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->
  </div>
  </div>
   </div>
</section>
<!-- /.content -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
 
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="<?php echo base_url();?>resource/js/jquery.autocomplete.js"></script>
<script>
function selectStudent() {
 	var reg_no = $( "#reg_no" ).val(); 
 }
</script>
<script type="text/javascript">
$(function(){    
 //var cls_sec_id = $("#cls_sec_id").val();
 
$("#student_id").select2({
   
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=fee_chalan_pay2&m=get_studentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term,page) {
            return {
                    term: term, //search term
                    flag:  $("#cls_sec_id").val(),
                    page: page // page number
                  };
        },
        processResults: function (response) {
          return {
             results: response
          };
        },
        cache: true
    } 

});

$("#parent_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=fee_chalan_pay2&m=get_parentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term:term,
            }
        },
       processResults: function (response) {
            //console.log(response);
              return {
                 results: response
              };
       },
       cache: true
    }
 });

$('#parent_id').keyup(function(){
    var parent_id = $('#parent_id').val();

    $.ajax({
        url: 'admin.php?c=fee_chalan_pay2&m=get_students_list',
        type: "POST",
        data:{parent_id: parent_id},
        success:function(res){
         if(res){
           $("#feetypeinfo").html(res);
         }else{
           $("#feetypeinfo").html("Record Not Found"); 
         }
      
        }
    });
});

$('#reg_no').keyup(function(){
    var reg_no = $('#reg_no').val();

    $.ajax({
        url: 'admin.php?c=fee_chalan_pay2&m=get_students_list',
        type: "POST",
        data:{reg_no: reg_no},
        success:function(res){
         if(res){
           $("#feetypeinfo").html(res);
         }else{
           $("#feetypeinfo").html("Record Not Found"); 
         }
      
        }
    });
});

$('#parent_id').on('select2:select', function (e) {
    var data = e.params.data;
    var parent_id = data.id;

    $.ajax({
            url: 'admin.php?c=fee_chalan_pay2&m=get_students_list',
            type: "POST",
            data:{parent_id: parent_id},
            success:function(res){
             if(res){
               $("#feetypeinfo").html(res);
             }else{
               $("#feetypeinfo").html("Record Not Found"); 
             }
          
         }
    });
});

$('#student_id').on('select2:select', function (e) {
    var data = e.params.data;
    var student_id = data.id;

    $.ajax({
            url: 'admin.php?c=fee_chalan_pay2&m=get_students_list',
            type: "POST",
            data:{student_id: student_id},
            success:function(res){
             if(res){
               $("#feetypeinfo").html(res);
             }else{
               $("#feetypeinfo").html("Record Not Found"); 
             }
          
         }
         });
});

$('[data-mask]').inputmask();
var dateNow = new Date();
$('#datepicker2').datetimepicker({
  format: 'DD/MM/YYYY',
	defaultDate:moment(dateNow).hours(0).minutes(0).seconds(0).milliseconds(0) 
});
});
</script>

<?= $this->endSection() ?>