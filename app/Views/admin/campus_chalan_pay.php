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
               Pay Fee Chalan
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
              <li class="breadcrumb-item active">Pay Fee Chalan</li>
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
			echo form_open('c=campus_chalan_pay&m=save', 'role="form" id="user-edit-form"');
			echo form_hidden('id', $id);
			?>
      <div class="row">
       <div class="form-group col-lg-4">
        <label>Date Paid:</label>
           <div class="input-group date" id="datepicker2" data-target-input="nearest">
              <input type="text" id="datePaid" name="paid_date" autocomplete="off" class="form-control datetimepicker-input" data-target="#datepicker2"/>
              <div class="input-group-append" data-target="#datepicker2" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
          </div>
          <!-- /.input group -->
        </div>			  
        <div class="form-group col-lg-4">
          <label for="class">Campus Name</label><br>
          <select class="form-control select2" name="campus_id" id="campus_id" >
             <option value="0">Select Campus</option>   
          </select>
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

$("#campus_id").select2({

    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=campus_chalan_pay&m=get_campusinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term: term
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

$('#campus_id').on('select2:select', function (e) {
    var data = e.params.data;
    var campus_id = data.id;

    $.ajax({
            url: 'admin.php?c=campus_chalan_pay&m=get_campus_list',
            type: "POST",
            data:{campus_id: campus_id},
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