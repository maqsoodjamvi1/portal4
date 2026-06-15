<?php  require_once(APPPATH.'/views/templates/sidebar.php'); ?>
<div class="main">
<div class="row page-content">
    <div class="col-lg-12">
        <h2>Setting</h2>
        <?php if (validation_errors()) { ?>
            <div class="alert alert-danger">
                <?php echo validation_errors(); ?>
            </div>
        <?php } ?>
        <?php if (!empty($this->input->get('msg')) && $this->input->get('msg') == 1) { ?>
            <div class="alert alert-danger">
                Please Enter Your Valid Information.
            </div>
        <?php } ?>
        <?php echo form_open('auth/actionChangePwd'); ?>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input type="password" name="change_pwd_password" class="form-control" id="change-pwd-password" placeholder="Password">
                    </div>
                </div>
            </div>      
            <div class="col-lg-6">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input type="password" name="change_pwd_confirm_password" class="form-control" id="change-pwd-confirm-password" placeholder="Confirm Password">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group float-end">
                    <button type="submit" id="chnage-pwd" class="btn btn-warning">Save</button>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
</div>