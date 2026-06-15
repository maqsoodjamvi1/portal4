<div class="top_nav">
  <div class="nav_menu">
    <nav>
      <div class="nav toggle">
        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
      </div>
      <ul class="nav navbar-nav navbar-right">
	 
        <li class="">
		<?php //print_r($data); ?>
		<?php $full_name = $username = $this->session->userdata['f_name']; ?>
          <a href="javascript:;" class="user-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
           <!-- <img src="<?=base_url('assets/images/isyana.jpg')?>" alt="">--> <?php echo $full_name; ?>
            <span class=" fa fa-angle-down"></span>
          </a>
          <ul class="dropdown-menu dropdown-usermenu">
            <li><a href="javascript:;"> Profile</a></li>
            <!--<li> 
              <a href="javascript:;">
                <span class="badge bg-red float-end">50%</span>
                <span>Settings</span>
              </a>
            </li>
            <li><a href="javascript:;">Help</a></li>-->
            <li><a href="<?=base_url('auth/do_logout')?>"><i class="fa fa-sign-out float-end"></i> Log Out</a></li>
          </ul>
		  
        </li>
		<li class="float-end"> 
		<label style="float:left;padding:13px 15px 0px; line-height:38px;">Select Student</label>
	 <?php
   //print_r($this->session->userdata);
	  $parent_id = $this->session->userdata['parent_id'];	
	  $query = $this->db->get_where('students', array('parent_id'=>$parent_id));
		
		if($query->num_rows() > 0)
            $studentData = $query->result_array();
        else
            $studentData = array();
			
			
	//print_r($studentData);	
	$id = $this->session->userdata['id'];	
 	 ?>
	 <a style="float:left;">
	 <select class="form-control" name="student_id" id="StudentID">
	 <option>Select Student</option>
	 <?php foreach($studentData as $studentinfo){?>
	 <option value="<?php echo $studentinfo['student_id']; ?>" <?php if($studentinfo['student_id'] == $id){ ?> selected="selected" <?php } ?> ><?php echo $studentinfo['first_name']." ".$studentinfo['last_name']; ?></option>
	 <?php } ?>
	 </select>
	 </a>
	  </li>
      </ul>
    </nav>
  </div>
</div>