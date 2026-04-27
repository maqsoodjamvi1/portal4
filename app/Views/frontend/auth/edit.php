<?php require_once(APPPATH.'/views/templates/sidebar.php'); ?>
<div class="main">
<div class="row page-content">
    <div class="col-lg-12">
        <h2>Profile</h2>
        <?php if (validation_errors()) { ?>
            <div class="alert alert-danger">
                <?php echo validation_errors(); ?>
            </div>
        <?php } ?>
        <?php echo form_open('auth/editUser'); ?>
        <?php //print_r($userInfo); exit; ?>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user-o"></i>
                        </span>
                        <input type="text" name="f_name" class="form-control" id="first-name" placeholder="Father Name" value="<?php print $userInfo['full_name']; ?>">
                    </div>
                </div>
            </div>
           
        </div>
        <div class="row">                 
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </span>
                        <input type="text" disabled="disabled" name="email" class="form-control" id="email" placeholder="Email" value="<?php print $userInfo['father_email']; ?>">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </span>
                        <input type="text" name="contact_no" class="form-control" id="contact-no" placeholder="Contact No" value="<?php print $userInfo['father_contact']; ?>">
                    </div>
                </div>
            </div>
        </div>        
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        <input type="text" name="address" class="form-control" id="address" placeholder="Address" value="<?php print $userInfo['address_line1']; ?>">
                    </div>
                </div>
            </div>      
            <div class='col-lg-6'>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        <input type='text' class="form-control" name="dob" id="dob" placeholder="DOB: DD-MM-YYYY" value="<?php //print $userInfo['dob']; ?>">                    
                    </div>
                </div>
            </div>

        </div>
        <div class="row"> 
            <div class="col-lg-12">
                <div class="form-group pull-right">
                    <button type="submit" id="register" class="btn btn-info">Register</button>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?> 
</div>
</div>