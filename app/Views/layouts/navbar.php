<header class="main-header">
  <a href="<?= site_url('/') ?>" class="logo">
    <span class="logo-mini">CXP</span>
    <span class="logo-lg">CXPCMS</span>
  </a>

  <nav class="navbar navbar-static-top">
    <a href="javascript:;" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </a>

    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="<?= base_url('resource/adminlte/dist/img/user2-160x160.jpg') ?>" class="user-image" alt="User Image">
            <span class="hidden-xs"><?= esc(session('admin_username')) ?></span>
          </a>
          <ul class="dropdown-menu">
            <li class="user-header">
              <img src="<?= base_url('resource/adminlte/dist/img/user2-160x160.jpg') ?>" class="img-circle" alt="User Image">
              <p>
                <?= esc(session('admin_username')) ?>
                <small>Join Time: <?= esc(session('reg_time')) ?></small>
              </p>
            </li>
            <li class="user-footer">
              <div class="pull-left">
                <a href="<?= site_url('admin/profile') ?>" class="btn btn-default btn-flat"><i class="fa fa-gear"></i> Profile</a>
              </div>
              <div class="pull-right">
                <a href="<?= site_url('logout') ?>" class="btn btn-default btn-flat"><i class="fa fa-sign-out"></i> Logout</a>
              </div>
            </li>
          </ul>
        </li>
        <li>
          <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
        </li>
      </ul>
    </div>
  </nav>
</header>
