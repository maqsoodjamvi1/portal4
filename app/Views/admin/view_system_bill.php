<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<br>
<section class="invoice">
  <style type="text/css">
    .invoice{
         margin: 0px 25px !important;
    }
  </style>
  <!-- title row -->
    <div class="card-header p-0 pt-1 border-bottom-0">
      <div class="card-body">
      <div class="row">
        <div class="col-xs-12">
          <h2 class="page-header">
           
          </h2>
        </div>
        <!-- /.col -->
      </div>
      <!-- info row -->
     
      <!-- /.row -->

      <!-- Table row -->
      <div class="row">
        <div class="col-xs-12 table-responsive">
          <div class="btn btn-primary" style="cursor: pointer;"  onclick="copyDivToClipboard()"> Click to copy </div>
        <div id="copytxt">
          Mobile Application For Parents: <br>
          https://play.google.com/store/apps/details?id=com.tlive.education.portal<br>
          demo Parent log in : 3740555324091<br>
          password: 12345678 <br>
          Click For user manual: https://portal.timesoftsol.com/user_manual/User-Manual-TLive-Education-v3.pdf<br>
          https://portal.timesoftsol.com/admin.php#/<br>
          user name: <?php echo $user_info->username; ?><br>
          password: <?php echo $user_info->wpwd; ?><br>
        </div>
          <table class="table table-striped">
            <tbody>
              <tr><td>User Name</td><td><?php echo $user_info->username; ?></td></tr>
              <tr><td>Password</td><td><?php echo $user_info->wpwd; ?></td></tr>
              <tr><td>Campus Name</td><td><?php echo $campusinfo->campus_name; ?></td></tr>
              <tr>
                <td>Location</td><td><?php echo $campusinfo->location; ?></td>
              </tr>
              <tr>
                <td>Mobile No</td><td><?php echo $campusinfo->mobile_no; ?></td>
              </tr>
              <tr><td>Landline</td><td><?php echo $campusinfo->landline; ?></td></tr>
            <tr>
              <td>Email</td>
              <td><?php echo $user_info->email; ?></td>
            </tr>  
            <tr>
              <th>System Plan</th>
               <td><?php echo $systemPlaninfo->plan_name; ?></td>
             </tr>
            
            <tr>
              <th>Maximum Students</th> <td><?php echo $systemPlaninfo->student_limit; ?></td>
            </tr>
            <tr>
              <th>Maximum Fee</th> <td><?php echo $systemPlaninfo->fee_limit; ?></td>
            </tr>
            <tr>
              <th>Amount</th><th><?php echo $campusbillinfo->bill_amount; ?></th>
            </tr>
            <tr>
              <th>Created Date</th><th><?php echo dateFormat($campusinfo->created_date); ?></th>
            </tr>
            </tbody>
           </table>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
      <div class="row">
        <!-- accepted payments column -->
        <div class="col-lg-6" style="width: 60%;float: right;"> 
         
        </div>
        <!-- /.col -->
        <div class="col-lg-6 pull-right" style="width: 40%;float: right;">
          <p class="lead">Amount Due <?php echo $campusbillinfo->campus_expiry; ?></p>

        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div></div>
    </section>
    <br>

            <script>
                function copyDivToClipboard() {
                    var range = document.createRange();
                    range.selectNode(document.getElementById("copytxt"));
                    window.getSelection().removeAllRanges(); // clear current selection
                    window.getSelection().addRange(range); // to select text
                    document.execCommand("copy");
                    window.getSelection().removeAllRanges();// to deselect
                }
            </script>

<?= $this->endSection() ?>