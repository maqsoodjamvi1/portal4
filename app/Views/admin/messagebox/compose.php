<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
  <!-- Content Wrapper. Contains page content -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Compose</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Compose</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <a href="#/messages" class="btn btn-primary btn-block mb-3">Back to Inbox</a>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Folders</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                  <li class="nav-item active">
                    <a href="#" class="nav-link">
                      <i class="fas fa-inbox"></i> Inbox
                      <span class="badge bg-primary float-right">12</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-envelope"></i> Sent
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-file-alt"></i> Drafts
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="fas fa-filter"></i> Junk
                      <span class="badge bg-warning float-right">65</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-trash-alt"></i> Trash
                    </a>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
           
            <!-- /.card -->
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">Compose New Message</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
            <?php
helper('form');

echo form_open(site_url(route_to('admin_messages_save')), ['role'=>'form','id'=>'user-edit-form']);
?>
                <div class="form-group">
                   <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="sections[]" value="all" class="form-control" > All Students </label>
                  <?php foreach ($campusSections as $key => $value) { ?>
                    <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="sections[]" value="<?php echo $value['cls_sec_id']; ?>" class="form-control"  require="require"> <?php echo $value['sectionclassname']; ?> </label>
                  <?php } ?>
                 
                </div>
                <div class="form-group" style="clear:both;">
                   <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="father_contact" class="form-control" required > Father Contact </label>
                   <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="mother_contact" class="form-control" required> Mother Contact </label>
                   <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="contacts[]" value="emergency_contact" class="form-control" required > Emergency Contact </label>
                </div>
                <div class="form-group" style="clear:both;">
                   <label style="display: block;float: left;margin-right: 10px;"> <input type="checkbox" name="unique_sms[]" value="1" class="form-control" > Unique sms to parent </label>
                </div>
                <div class="form-group">
                    <textarea name="message" id="compose-textarea" class="form-control" rows="4"></textarea>
                    <input type="button" value="First Name" onclick="formatText ('first_name');" /> 
    <input type="button" value="Last Name" onclick="formatText ('last_name');" /> 
    <input type="button" value="Father Name" onclick="formatText ('father_name');" /> 
    <input type="button" value="Date" onclick="formatText ('date');" /> 
    <input type="button" value="Class" onclick="formatText ('class');" /> 
   <script type="text/javascript">
  function formatText(tag) {
   var Field = document.getElementById('compose-textarea');
   var val = Field.value;
   var selected_txt = val.substring(Field.selectionStart, Field.selectionEnd);
   var before_txt = val.substring(0, Field.selectionStart);
   var after_txt = val.substring(Field.selectionEnd, val.length);
   Field.value += '{' + tag + '}';
}
</script>
                    <!--  <small>Template Variables: {first_name}, {first_name}, {class}, {date}, {father_name} </small> -->
                </div>
                <div class="form-group">
                  <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
                  <button type="reset" class="btn btn-default">Reset</button>
                  <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
                </div>
                <?php echo form_close();?>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
<!-- jQuery -->
<script type="text/javascript">
$(function(){
  $('.clockpicker').clockpicker();
  $(".select2").select2({closeOnSelect:false});
  $('[data-mask]').inputmask();

$('#user-edit-form').validate({
    rules:{
      sections:{
        required:true,
      },
      contacts:{
        required:true,
      }
    },
    messages:{
      sections:{
        required:'Section is Required',
      },
      contacts:{
        required:'Contacts is Required',
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
        location.href = '#/messages';
       
      }else{
        toastr.error(json.msg);
      }
      return false;
    }
  });     
});
</script>  
<?= $this->endSection() ?>