<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>
    <?= view('components/page_header', [
    'title' => '提示信息',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => '提示信息', 'active' => true],
    ],
]) ?>


    <!-- Main content -->
    <section class="content">
 
<div class="alert alert-success alert-dismissible">
                <h4><i class="icon fa fa-check"></i> 提示</h4>
               操作成功
              </div>
      

      
    </section>
    <!-- /.content -->

<?= $this->endSection() ?>