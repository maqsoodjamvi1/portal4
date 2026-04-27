<!-- admin_template.php - CORRECTED (wrapper removed) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Your head content here -->
</head>
<body class="hold-transition sidebar-mini">
    <?= $this->include('layouts/header') ?>  
    
    
    <!-- Per-page styles (load ONLY what you need for this page) -->
    <?= $this->renderSection('pageStyles') ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

      <!-- Optional content header (breadcrumbs, titles set by each page) -->
      <?php if ($this->renderSection('content_header')): ?>
        <section class="content-header">
          <div class="container-fluid">
            <?= $this->renderSection('content_header') ?>
          </div>
        </section>
      <?php endif; ?>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid pt-3">
          <?= $this->renderSection('content') ?>
        </div>
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Page-level modals -->
    <?= $this->renderSection('modals') ?>

    <!-- Per-page scripts (load ONLY what you need for this page) -->
    <?= $this->renderSection('pageScripts') ?>

    <?= $this->include('layouts/footer') ?>
    <!-- wrapper is closed in footer.php -->
</body>
</html>