<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />
<style>
  :root{
    --row-h: 26px;
    --row-h-header: 28px;
    --pad-x: 4px;
    --font-main: 11px;
    --font-small: 10px;
    --border: 1px solid #000;
  }
  @media print {
    .pagebreak { page-break-before: always; }
    #user-edit-form { display: none; }
    .no-print { display: none !important; }
  }
  .slip-col { width: 32%; float: left; margin-left: 1%; }
  .chalanwrapper {
    border: var(--border);
    background: #fff;
    font-size: var(--font-main);
    width: 100%;
    float: left;
  }
</style>
