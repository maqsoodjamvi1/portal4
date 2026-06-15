<?php if (! empty($isAdaptive) && ! empty($levelInfo)): ?>
<div class="modal fade" id="adaptiveLevelResultModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius:18px;border:0;">
      <div class="modal-body text-center p-4 p-md-5">
        <div id="adaptiveResultIcon" class="mb-2" style="font-size:3.5rem;"><i class="fas fa-trophy text-success"></i></div>
        <h4 id="adaptiveResultTitle" class="mb-2">Level result</h4>
        <p id="adaptiveResultMessage" class="text-muted mb-4"></p>
        <div class="row mb-4">
          <div class="col-6">
            <div class="p-3 rounded" style="background:#f8fafc;">
              <small class="text-muted d-block">Your score</small>
              <strong id="adaptiveYourScore" class="h4 mb-0">—</strong>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded" style="background:#f8fafc;">
              <small class="text-muted d-block">Required</small>
              <strong id="adaptiveRequiredScore" class="h4 mb-0">—</strong>
            </div>
          </div>
        </div>
        <div id="adaptiveResultActions" class="d-flex flex-wrap justify-content-center" style="gap:8px;"></div>
      </div>
    </div>
  </div>
</div>

<div id="adaptiveLoadingOverlay" class="d-none position-fixed w-100 h-100" style="top:0;left:0;background:rgba(15,23,42,0.55);z-index:9999;align-items:center;justify-content:center;">
  <div class="text-center text-white">
    <div class="spinner-border mb-2" role="status"></div>
    <div id="adaptiveLoadingText">Please wait…</div>
  </div>
</div>
<?php endif; ?>
