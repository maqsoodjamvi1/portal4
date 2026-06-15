<h3>Battles</h3>
<div id="battleData"></div>
<script>
fetch('<?= base_url('student/battles/data') ?>?quiz_id=<?= (int)($quiz_id ?? 0) ?>')
  .then(r=>r.json()).then(d=>{
    document.getElementById('battleData').innerHTML = JSON.stringify(d);
  });
</script>