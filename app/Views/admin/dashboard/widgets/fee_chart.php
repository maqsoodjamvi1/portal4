<div class="card">
    <div class="card-header">
        <h3 class="card-title">Fee Collection</h3>
    </div>
    <div class="card-body">
        <canvas id="feeChart"></canvas>
    </div>
</div>

<script>
new Chart(document.getElementById('feeChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($feeChart['months']) ?>,
        datasets: [
            {
                label: 'Paid',
                data: <?= json_encode($feeChart['paid']) ?>
            },
            {
                label: 'Unpaid',
                data: <?= json_encode($feeChart['unpaid']) ?>
            }
        ]
    }
});
</script>