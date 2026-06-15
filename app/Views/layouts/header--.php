<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>CXPCMS</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <!-- CSS Assets -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/datatables/dataTables.bootstrap.css') ?>">
  <link rel="stylesheet" href="<?= base_url('resource/artdialog/css/ui-dialog.css') ?>">
  <link rel="stylesheet" href="<?= base_url('resource/toastr/toastr.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/plugins/select2/select2.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/AdminLTE.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('resource/adminlte/dist/css/skins/_all-skins.min.css') ?>">
  <?= $this->include('layouts/scripts') ?>
  <style>
    body { font-size: 13px; }
    .no-padding { padding: 10px 0 !important; }
  </style>

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <script>
    const BASE_URL = '<?= base_url() ?>';
    const RELA_PATH = './';
  </script>
</head>
<body class="hold-transition skin-blue-light fixed sidebar-mini">
<div class="wrapper">
