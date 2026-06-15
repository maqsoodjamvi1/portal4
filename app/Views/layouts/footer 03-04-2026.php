
      <footer class="main-footer">
        <div class="float-end d-none d-sm-inline-block">
          <b>Version</b> 2.3.1
        </div>
        <strong>Copyright &copy; 2008- <?php echo date('Y'); ?> <a href="#">TIME Soft Solution</a>.</strong>
      </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
    <li><a href="#control-sidebar-sites-tab" data-bs-toggle="tab"><i class="fa fa-home"></i></a></li>
  </ul>
  
  </aside>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->

</div>
<!-- ./wrapper -->
<script type="text/javascript">
  $('body').on('keydown', 'input, select', function(e) {
    if (e.key === "Enter") {
        var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
        focusable = form.find('input,a,select,button,textarea').filter(':visible');
        next = focusable.eq(focusable.index(this)+1);
        if (next.length) {
            next.focus();
        } else {
            form.submit();
        }
        return false;
    }
});
</script>
<script type="text/javascript">   
</script>
</body>
</html>
