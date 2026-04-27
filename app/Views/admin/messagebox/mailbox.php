<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Inbox</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Inbox</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-3">
          

           <a class="nav-link" href="<?= base_url('admin/messages/add') ?>">Compose Message</a>

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
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Labels</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle text-danger"></i>
                    Important
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle text-warning"></i> Promotions
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle text-primary"></i>
                    Social
                  </a>
                </li>
              </ul>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
        <div class="col-md-9">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h3 class="card-title">Inbox</h3>

              <div class="card-tools">
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" placeholder="Search Mail">
                  <div class="input-group-append">
                    <div class="btn btn-primary">
                      <i class="fas fa-search"></i>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-tools -->
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
              
              <div class="table-responsive mailbox-messages">
                <table class="table table-hover table-striped" id="users-datatable" >
                  <thead>
                  <tr>
                    <th nowrap>#</th>
                    <th nowrap>Status</th>
                    <th nowrap>From</th>
                    <th nowrap>Subject</th>
                    <th nowrap>Date</th>
                    <th>Mobile</th>
                    <th nowrap>Operation</th>
                  </tr>
                </thead> 
                  <tbody>
                  </tbody>
                </table>
                <!-- /.table -->
              </div>
              <!-- /.mail-box-messages -->
            </div>
            <!-- /.card-body -->
            <div class="card-footer p-0">
              
            </div>
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
<!-- Page specific script -->
<script>
  // $(function () {
  //   //Enable check and uncheck all functionality
  //   $('.checkbox-toggle').click(function () {
  //     var clicks = $(this).data('clicks')
  //     if (clicks) {
  //       //Uncheck all checkboxes
  //       $('.mailbox-messages input[type=\'checkbox\']').prop('checked', false)
  //       $('.checkbox-toggle .far.fa-check-square').removeClass('fa-check-square').addClass('fa-square')
  //     } else {
  //       //Check all checkboxes
  //       $('.mailbox-messages input[type=\'checkbox\']').prop('checked', true)
  //       $('.checkbox-toggle .far.fa-square').removeClass('fa-square').addClass('fa-check-square')
  //     }
  //     $(this).data('clicks', !clicks)
  //   })

  //   //Handle starring for font awesome
  //   $('.mailbox-star').click(function (e) {
  //     e.preventDefault()
  //     //detect type
  //     var $this = $(this).find('a > i')
  //     var fa    = $this.hasClass('fa')

  //     //Switch states
  //     if (fa) {
  //       $this.toggleClass('fa-star')
  //       $this.toggleClass('fa-star-o')
  //     }
  //   })
  // })
</script>
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script> 
<script type="text/javascript">
$(function(){
  var table = $('#users-datatable').DataTable({
    deferRender: true,
    select:{
      style:'single',
      blurable: true
    },  
    ajax:{
      
      url: "<?= base_url('admin/messages/data') ?>",
      type:'post',
      data:function(d){
        //d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
      }
    },
    columns:[
      {
        data:'id',
        className:'select-checkbox',
        render:function(data, type, row){
          return data;
        }
      },
      {data:'status'},
      {data:'f_name'},
      {data:'message'},
      {data:'sendtime'},
      {data:'mobile'},
    
      {
        data:'id',
        sortable:false,
        render:function(data, type, row){
          var html = '';
          html += '<div class="btn-group">';
              html += '<a href="<?php echo '#/users?m=edit&id=';?>' + data + '" title="edit" class="btn btn-default btn-xs"><i class="fa fa-edit icon-pencil"></i></a>';
             
              //html += '<a href="<?php //echo '#/users?m=edit_password&user_id=';?>' + data + '" title="change password" class="btn btn-default btn-xs"><i class="fa fa-lock icon-pencil"></i></a>';
             
              if(row.issys == '1'){
                
              }else{
               // html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php echo site_url('c=users&m=delete&id=');?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-default btn-xs"><i class="fa fa-trash icon-trash"></i></a>';
              }
              
          html += '</div>';
          return html;
        }
      }
    ],
    fnDrawCallback:function(oSettings){
      $(".switchchk").bootstrapSwitch({
        onSwitchChange:function(e, state){
        var fieldval = state;
        var $element = $(e.currentTarget);
        var tablename = $element.attr('data-table');
        var fieldname = $element.attr('data-field');
        var rowid = $element.attr('data-pk');
        if(fieldval){
          fieldval = 1;
        }else{
          fieldval = 0;
        }
        $.post(
           "<?php echo site_url('c=ajax&m=setboolattribute');?>",
           {
             act:'upsort',
             tbname:tablename,
             tbfield:fieldname,
             tbfieldvalue:fieldval,
             id:rowid//,
             // csrf_test_name:$.cookie(CSRF_COOKIE_NAME)
           
           },
           function(data){
          //alert(data);
             if(data=='success'){
               toastr.success('change success');
               
             }else{
               toastr.error('change error');
             }
           }
          );  
        }
      });     
    }
  });
});
</script>
</body>
</html>
<?= $this->endSection() ?>