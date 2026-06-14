<?php
/**
 * Global admin menu search (command palette).
 *
 * @var list<array{key: string, label: string, url: string, icon: string, section: string}> $adminNavIndex
 */
$adminNavIndex = $adminNavIndex ?? [];
?>
<div class="admin-command-palette no-print" id="adminCommandPalette" hidden>
  <div class="admin-command-palette__backdrop" data-cmd-close aria-hidden="true"></div>
  <div class="admin-command-palette__panel" role="dialog" aria-modal="true" aria-labelledby="adminCommandPaletteLabel">
    <label id="adminCommandPaletteLabel" class="visually-hidden" for="adminCommandInput">Search menu and pages</label>
    <div class="admin-command-palette__head">
      <i class="fas fa-search" aria-hidden="true"></i>
      <input type="search"
             id="adminCommandInput"
             class="form-control border-0 shadow-none"
             placeholder="<?= esc(lang('SchoolSetup.command_palette_search')) ?>"
             autocomplete="off"
             spellcheck="false">
      <kbd class="admin-command-palette__kbd d-none d-md-inline">Esc</kbd>
    </div>
    <ul class="admin-command-palette__results list-unstyled mb-0" id="adminCommandResults" role="listbox"></ul>
    <p class="admin-command-palette__hint text-muted small mb-0 px-3 py-2" id="adminCommandHint">
      <?= esc(lang('SchoolSetup.command_palette_hint')) ?>
    </p>
  </div>
</div>
<script>
window.ADMIN_NAV_INDEX = <?= json_encode(array_values($adminNavIndex), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
</script>
