<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        提示信息
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> 首页</a></li>
        <li class="active">提示信息</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
 
<div class="alert alert-success alert-dismissible">
                <h4><i class="icon fa fa-check"></i> 提示</h4>
               操作成功
              </div>
      

      
    </section>
    <!-- /.content -->

<?= $this->endSection() ?>