  <?php echo form_open_multipart('c=a_students&m=save_studentssubjects', 'role="form" id="students-edit-form-studentSubjects"');
        echo form_hidden('id', $id);
        echo form_hidden('campus_id', $campus_id);
  ?>
      <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>"  /> 
      <div class="row">

     <?php foreach($groups as $group){
      $ssInfo = '';
      $gt_id = '';
      if(isset($_GET['id'])){
       
       $ssInfo = $this->db->query("select * from a_student_subjects where gt_id = ".$group['gt_id']." AND student_id=".$info->student_id." AND session_id=".$sessionData['sessionid'])->row();
       if($ssInfo){
        $gt_id = $ssInfo->gt_id;
       }
      }
      
     ?>     	
        <div class="col-lg-3">
          <div class="form-group">
            <label for="reg_no">
            <input type="checkbox" <?php if($gt_id == $group['gt_id']){ ?> checked <?php } ?> readonly class="form-control" name="gt_id[]"  value="<?php echo $group['gt_id'];?>">
            <?php echo $group['class_name'] .' ('.$group['subject_name'].' '.$group['group_name'].')'; ?>
            </label> 
           
            <input type="text" placeholder="Discount Amount" <?php if($gt_id == $group['gt_id']){ ?> value="<?php echo $ssInfo->discount_amount; ?>" <?php } ?>  class="form-control" name="discount_amount[<?php echo $group['gt_id']; ?>]">
            
            
          </div>
        </div>
    <?php } ?>    
       
      </div> 
      <div class="row">
      <div class="col-lg-12 noprint">
        <div class="form-group">
          <button type="submit" id="submitBtn" class="btn btn-primary studentsubmit">Save</button>
          <button type="reset" class="btn btn-default">Reset</button>
          <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
        </div>
      </div>
      </div>
    <?php echo form_close();?>
 <script>
$(function(){
 
  $('[data-mask]').inputmask();
  var dateNow = new Date();
  $('#datepicker').datetimepicker({
      format: 'L',
      defaultDate:moment(dateNow)
    });
  
  $('#datepicker2').datetimepicker({
      format: 'L'
    });

  $('#students-edit-form-studentSubjects').validate({
    
   
  });

  $('#students-edit-form-studentSubjects').ajaxForm({
    beforeSubmit:function(formData, jqForm, options){
    //return $('#students-edit-form-studentSubjects').valid();
    $('#submitBtn').html("Ajax Request is Processing!");
    $('#submitBtn').prop('disabled', true);
   },
   success:function(responseText, statusText, xhr, form){
      $('#submitBtn').html("Submit");
      $('#submitBtn').prop('disabled', false);
      var json = $.parseJSON(responseText);
      console.log(json);
      if(json.success){
        toastr.success(json.msg);
        <?php
        if($id == ''){
          ?>
          location.href = '#/a_students?status=1';
          <?php
          }else{
          ?>
          location.href = '#/a_students?status=1';
          <?php
        }
        ?>
      }else{
        toastr.error(json.msg);
      }
      return false;
    }

});
});
</script>   