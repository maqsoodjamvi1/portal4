<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="index.html" class="site_title"><i class="fa fa-paw"></i> <span>TIME School!</span></a>
        </div>
        <div class="clearfix"></div>
        <!-- menu profile quick info -->
        <div class="profile clearfix">
            <!--<div class="profile_pic">
                <img src="<?=base_url('assets/images/isyana.jpg')?>" alt="..." class="img-circle profile_img">
            </div>-->
            <div class="profile_info">
			<?php //print_r($data); ?>
			<?php $full_name = $username = $this->session->userdata['f_name']; ?>
                <span>Welcome,</span>
                <h2><?php echo $full_name; ?></h2>
            </div>
            <div class="clearfix"></div>
        </div>
		 <div class="profile clearfix">
            <!--<div class="profile_pic">
                <img src="<?=base_url('assets/images/isyana.jpg')?>" alt="..." class="img-circle profile_img">
            </div>-->
            <div class="profile_info">
			        <span>Student,</span>
			    <h2><?php echo $this->session->userdata['student_name'];?></h2>
            </div>
            <div class="clearfix"></div>
        </div>
		
        <!-- /menu profile quick info -->
        <br />
        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
				<?php //foreach($data as $student){ ?>
				<?php //$full_name = $student['first_name']." ".$student['last_name']; ?>
					<?php //$student_id = $student['student_id'] ?>
                    <!--<li><a href="?student_id=<?php echo $student_id; ?>"><i class="fa fa-dashboard"></i> <?php echo $full_name; ?> </a></li>-->
					<?php //} ?>
					
					<li><a href="<?=base_url('home')?>"><i class="fa fa-dashboard"></i> Dashboard </a></li>
                    <li><a href="<?=base_url('students')?>"><i class="fa fa-user"></i> Profile </a></li>			
					<li><a href="<?=base_url('fee')?>"><i class="fa fa-credit-card"></i> Fee </a></li>					
					<li><a href="<?=base_url('result')?>"><i class="fa fa-list-alt"></i> Result </a></li>
					<li><a href="<?=base_url('datesheet')?>"><i class="fa fa-list-alt"></i> Datesheet </a></li>							
					<li><a href="<?=base_url('timetable')?>"><i class="fa fa-list-alt"></i> Time Table </a></li>			
					<li><a href="<?=base_url('dairy')?>"><i class="fa fa-list-alt"></i> Dairy </a></li>							
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->
    </div>
</div>