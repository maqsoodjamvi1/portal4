<nav class="navbar navbar-inverse sidebar" role="navigation">
    <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#bs-sidebar-navbar-collapse-1">
        <span class="visually-hidden">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div> 
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-sidebar-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li ><a href="<?php print site_url() ?>profile">Profile<span style="font-size:16px;" class="float-end d-none d-sm-inline-block showopacity fas fa-user"></span></a></li>
        <li class="active"><a href="/appointment_slip">Appointment Slip<span style="font-size:16px;" class="float-end d-none d-sm-inline-block showopacity fas fa-calendar-alt"></span></a></li>
        <li class="active"><a href="#">New Student<span style="font-size:16px;" class="float-end d-none d-sm-inline-block showopacity fas fa-calendar-alt"></span></a></li>
        <li class="active"><a href="#">Print Challan<span style="font-size:16px;" class="float-end d-none d-sm-inline-block showopacity fas fa-calendar-alt"></span></a></li>
        <li class="active"><a href="#">Result<span style="font-size:16px;" class="float-end d-none d-sm-inline-block showopacity fas fa-calendar-alt"></span></a></li>
        <li>
          <a href="<?php print site_url() ?>setting" >Change Password <span style="font-size:16px;" class="float-end d-none d-sm-inline-block showopacity fas fa-cog"></span></a>
        </li>
        <li><a href="<?php print site_url() ?>auth/logout">Log Out<span style="font-size:16px;" class="float-end d-none d-sm-inline-block showopacity fas fa-sign-out-alt"></span></a></li>
        </ul>
    </div>
  </div>
</nav>