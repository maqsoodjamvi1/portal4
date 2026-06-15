<?php if (! empty($isAdaptive) && ! empty($levelInfo)): ?>
<style>
#adaptiveResultSheet {
  position: fixed;
  inset: 0;
  z-index: 20000;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
#adaptiveResultSheet.is-open {
  display: flex !important;
}
#adaptiveResultSheet .adaptive-sheet-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
}
#adaptiveResultSheet .adaptive-sheet-card {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 420px;
  background: #fff;
  border-radius: 18px;
  padding: 1.5rem 1.25rem;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.25);
  max-height: 90vh;
  overflow-y: auto;
  pointer-events: auto;
}
#adaptiveResultSheet .adaptive-sheet-card button {
  pointer-events: auto;
  cursor: pointer;
  touch-action: manipulation;
}
#adaptiveLoadingOverlay {
  position: fixed;
  inset: 0;
  z-index: 19999;
  display: none;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.55);
  pointer-events: none;
}
#adaptiveLoadingOverlay.is-active {
  display: flex !important;
  pointer-events: auto;
}
body.adaptive-result-open .quiz-footer {
  pointer-events: none;
}
</style>

<div id="adaptiveResultSheet" role="dialog" aria-modal="true" aria-labelledby="adaptiveResultTitle" hidden>
  <div class="adaptive-sheet-backdrop"></div>
  <div class="adaptive-sheet-card">
    <div id="adaptiveResultIcon" style="font-size:3.5rem;" class="mb-2 text-center text-success">
      <i class="fas fa-trophy"></i>
    </div>
    <h4 id="adaptiveResultTitle" class="mb-2 text-center">Level result</h4>
    <p id="adaptiveResultMessage" class="text-muted mb-4 text-center"></p>
    <div class="row mb-4">
      <div class="col-6">
        <div class="p-3 rounded bg-light text-center">
          <small class="text-muted d-block">Your score</small>
          <strong id="adaptiveYourScore" class="h4 mb-0">-</strong>
        </div>
      </div>
      <div class="col-6">
        <div class="p-3 rounded bg-light text-center">
          <small class="text-muted d-block">Required</small>
          <strong id="adaptiveRequiredScore" class="h4 mb-0">-</strong>
        </div>
      </div>
    </div>
    <div id="adaptiveResultActions" class="d-flex flex-wrap justify-content-center" style="gap:8px;"></div>
  </div>
</div>

<div id="adaptiveLoadingOverlay" aria-hidden="true">
  <div class="text-center text-white">
    <div class="spinner-border mb-2" role="status"></div>
    <div id="adaptiveLoadingText">Please wait...</div>
  </div>
</div>
<?php endif; ?>
