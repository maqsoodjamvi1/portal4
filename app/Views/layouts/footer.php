<footer class="main-footer no-print">
        <div class="float-end d-none d-sm-inline-block">
          <b>Version</b> 2.3.1
        </div>
        <strong>Copyright &copy; 2008- <?php echo date('Y'); ?> <a href="#">TIME Soft Solution</a>.</strong>
      </footer>

  </div>
<script type="text/javascript">
  // Auto-focus next input on Enter key
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
</body>
</html>