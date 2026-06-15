<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" 
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

<style>
.event-card {
    position: relative;
    transition: all 0.2s ease-in-out;
    cursor: grab;
    font-size: 0.85rem;
    padding: 0.75rem !important;
}
.event-card:active { cursor: grabbing; }
.event-card:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

/* Small circular order badge at top-left */
.event-order-badge {
    position: absolute;
    top: 4px;
    left: 4px;
    background: #343a40;
    color: #fff;
    border-radius: 999px;
    font-size: 0.7rem;
    padding: 2px 6px;
    line-height: 1;
}
</style>

<div class="container mt-4">
    <h3 class="mb-3 text-center">Arrange Sports Events (Drag & Drop)</h3>

    <div class="alert alert-info small text-center">
        Drag cards to reorder events. Click clock icon to set event time.
    </div>

    <div id="eventList" class="row g-2 g-md-3">
        <?php foreach ($events as $i => $event): ?>
            <!-- 6 cards per row on large screens -->
            <div class="col-6 col-md-4 col-lg-2 event-item" data-id="<?= $event['event_id'] ?>">
                <div class="card event-card">

                    <!-- order number (by DOM order, will be refreshed by JS) -->
                    <span class="event-order-badge"><?= $i + 1 ?></span>

                    <div class="p-2">
                        <h6 class="fw-bold mb-1"><?= esc($event['event_name']) ?></h6>
                        <p class="mb-1"><b>Gender:</b> <?= ucfirst(esc($event['gender'])) ?></p>
                        <p class="mb-1">
                            <b>Age:</b> <?= esc($event['min_age']) ?> - <?= esc($event['max_age']) ?>
                        </p>
                        <p class="mb-1">
                            <b>Time:</b>
                            <span id="time_<?= $event['event_id'] ?>">
                                <?= $event['event_time'] ? esc($event['event_time']) : 'Not Set' ?>
                            </span>
                        </p>

                        <button class="btn btn-sm btn-primary setTimeBtn mt-1"
                                data-id="<?= $event['event_id'] ?>"
                                data-time="<?= esc($event['event_time']) ?>">
                            ⏰ Set Time
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button id="saveOrder" class="btn btn-success mt-4 w-100">
        Save Order
    </button>
</div>

<!-- Time Modal -->
<div class="modal fade" id="timeModal">
    <div class="modal-dialog">
        <form id="timeForm" class="modal-content">
            <div class="modal-header">
                <h5>Set Event Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="event_id">

                <label class="form-label">Event Time</label>
                <input type="time" id="event_time" class="form-control" required>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
function refreshOrderBadges() {
    $('#eventList .event-item').each(function (idx) {
        $(this).find('.event-order-badge').text(idx + 1);
    });
}

new Sortable(document.getElementById('eventList'), {
    animation: 150,
    ghostClass: 'bg-light',
    onEnd: function () {
        refreshOrderBadges();
    }
});

// initial numbering (in case PHP index & DOM differ later)
$(document).ready(function () {
    refreshOrderBadges();
});

// Save new order
$("#saveOrder").click(function () {
    let order = [];

    $(".event-item").each(function () {
        order.push($(this).data("id"));
    });

    $.post("<?= base_url('admin/sports/events/order/save') ?>", {
        order: order,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function (res) {
        alert("Order updated successfully!");
    });
});

// Open time modal
$(document).on('click', ".setTimeBtn", function () {
    $("#event_id").val($(this).data("id"));
    $("#event_time").val($(this).data("time") || '');
    new bootstrap.Modal(document.getElementById('timeModal')).show();
});

// Save time
$("#timeForm").submit(function (e) {
    e.preventDefault();

    $.post("<?= base_url('admin/sports/events/order/time') ?>", {
        id: $("#event_id").val(),
        event_time: $("#event_time").val(),
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function () {
        $("#time_" + $("#event_id").val()).text($("#event_time").val());
        alert("Time updated!");
        $("#timeModal").modal("hide");
    });
});
</script>

<?= $this->endSection() ?>
