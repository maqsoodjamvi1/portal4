<?php require_once(APPPATH.'/views/templates/sidebar.php'); ?>
<div class="main">
<div class="row page-content">
    <div class="col-lg-12 text-left">
        <!-- <a class="btn btn-info btn-xs" href="<?php print site_url() ?>profile/edit"><i class="fa fa-user"></i> Edit</a>
        <a class="btn btn-warning btn-xs" href="<?php print site_url() ?>setting"><i class="fa fa-gear"></i> Settings</a>
        <a class="btn btn-danger btn-xs" href="<?php print site_url() ?>auth/logout"><i class="fa fa-power-off"></i> Log Out</a> -->
        <div class="row">
            <div class="col-lg-12 col-sm-12">
                <div class="card hovercard">                    
                    <div class="cardheader"> 
                        <div class="avatar">
                            <img alt="<?php print $this->session->userdata('user_name'); ?>" src="user-default.jpg">
                        </div>
                    </div>
                    <div class="card-body info">
                        <div class="title">
                            <a href="<?php print site_url() ?>profile"><?php //print $userInfo['full_name']; ?></a>
                        </div>
                        <div class="desc"> <a target="_blank" rel="noopener"></a></div>    
                        <div class="desc"><?php //print $userInfo['father_email']; ?>, <?php print $userInfo['father_contact']; ?></div>      
                        <div class="desc"><?php //print $userInfo['address_line1']; ?></div>                
                    </div>
                   
                </div>
            </div>
        </div>
    </div>   
</div>
</div>