<?php
/**
 * Customize Menu modal — grouped by sidebar section with collapsible panels.
 *
 * @var array $sections Built menu sections from AdminMenuBuilder
 */

helper('permission');

$cleanLabel = static function (string $label): string {
    return trim(preg_replace('/^[\s\-\|├└─]+/u', '', $label) ?? '');
};

$childPermitted = static function (array $item): bool {
    if (!empty($item['header'])) {
        return false;
    }
    if (!empty($item['perms'])) {
        return array_reduce($item['perms'], static fn ($ok, $p) => $ok || hasPermission($p), false);
    }
    return true;
};

$sectionVisible = static function (array $sec) use ($childPermitted): bool {
    $visible = $sec['visible'] ?? true;
    if (is_callable($visible)) {
        $visible = $visible();
    }
    if (!$visible) {
        return false;
    }
    if (empty($sec['children'])) {
        return true;
    }
    foreach ($sec['children'] as $c) {
        if (!empty($c['header'])) {
            return true;
        }
        if ($childPermitted($c)) {
            return true;
        }
    }
    return false;
};

$renderPrefsItem = static function (array $item) use (&$renderPrefsItem, $cleanLabel, $childPermitted): void {
    if (!empty($item['header'])) {
        $label = $cleanLabel((string) ($item['label'] ?? ''));
        if ($label !== '') {
            echo '<div class="menu-prefs-subhead">' . esc($label) . '</div>';
        }
        return;
    }

    if (!$childPermitted($item)) {
        return;
    }

    $key = $item['key'] ?? null;
    if ($key) {
        $label = $cleanLabel((string) ($item['label'] ?? $key));
        $icon  = !empty($item['icon']) ? $item['icon'] : 'far fa-circle';
        $searchLabel = strtolower($label);

        echo '<label class="menu-prefs-item" data-item-row data-key="' . esc($key, 'attr') . '" data-label="' . esc($searchLabel, 'attr') . '">';
        echo '  <input class="menu-item-toggle" type="checkbox" id="mi_' . esc($key, 'attr') . '" data-key="' . esc($key, 'attr') . '" checked>';
        echo '  <span class="menu-prefs-item__icon"><i class="' . esc($icon) . '"></i></span>';
        echo '  <span class="menu-prefs-item__label" title="' . esc($label, 'attr') . '">' . esc($label) . '</span>';
        echo '  <span class="menu-prefs-item__switch" aria-hidden="true"></span>';
        echo '</label>';
    }

    if (!empty($item['children'])) {
        foreach ($item['children'] as $child) {
            $renderPrefsItem($child);
        }
    }
};

$countItems = static function (array $items) use (&$countItems, $childPermitted): int {
    $n = 0;
    foreach ($items as $item) {
        if (!empty($item['header'])) {
            continue;
        }
        if (!$childPermitted($item)) {
            continue;
        }
        if (!empty($item['key'])) {
            $n++;
        }
        if (!empty($item['children'])) {
            $n += $countItems($item['children']);
        }
    }
    return $n;
};
?>
<div class="modal fade" id="menuPrefsModal" tabindex="-1" role="dialog" aria-labelledby="menuPrefsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable menu-prefs-modal" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom-0 pb-0">
        <div>
          <h5 class="modal-title mb-1" id="menuPrefsLabel">
            <i class="fas fa-sliders-h text-primary me-2"></i>Customize Menu
          </h5>
          <p class="text-muted small mb-0">Choose which items appear in your sidebar. Changes preview live — click Save to keep them.</p>
        </div>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
      </div>

      <div class="modal-body pt-3">
        <div class="menu-prefs-toolbar">
          <div class="menu-prefs-toolbar__search">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" id="prefsSearch" class="form-control form-control-sm" placeholder="Search menu items..." autocomplete="off">
          </div>
          <div class="menu-prefs-toolbar__actions btn-group btn-group-sm" role="group" aria-label="Bulk actions">
            <button type="button" class="btn btn-outline-secondary" id="prefsExpandAll" title="Expand all sections">Expand</button>
            <button type="button" class="btn btn-outline-secondary" id="prefsCollapseAll" title="Collapse all sections">Collapse</button>
            <button type="button" class="btn btn-outline-secondary" id="prefsShowAll">Show all</button>
            <button type="button" class="btn btn-outline-secondary" id="prefsHideAll">Hide all</button>
            <button type="button" class="btn btn-outline-warning" id="prefsReset">Reset</button>
          </div>
        </div>

        <div class="menu-prefs-summary" id="menuPrefsSummary" aria-live="polite">
          <span class="menu-prefs-summary__stat"><strong id="menuPrefsVisibleCount">0</strong> visible</span>
          <span class="text-muted mx-1">·</span>
          <span class="menu-prefs-summary__stat"><span id="menuPrefsTotalCount">0</span> total</span>
        </div>

        <div id="menuPrefsList" class="menu-prefs-groups">
          <?php foreach ($sections as $sec): ?>
            <?php if (!$sectionVisible($sec)) {
                continue;
            } ?>

            <?php
              $groupKey    = $sec['key'] ?? ('group_' . md5(json_encode($sec['label'] ?? '')));
              $groupLabel  = $cleanLabel((string) ($sec['label'] ?? 'Menu'));
              $groupIcon   = !empty($sec['icon']) ? $sec['icon'] : 'far fa-folder';
              $hasChildren = !empty($sec['children']);
              $isLeaf      = !$hasChildren && !empty($sec['key']);
              $itemCount   = $hasChildren ? $countItems($sec['children']) : ($isLeaf ? 1 : 0);
              if ($hasChildren && !empty($sec['key'])) {
                  $itemCount++;
              }
              if ($itemCount === 0) {
                  continue;
              }
              $searchGroup = strtolower($groupLabel);
            ?>

            <div class="menu-prefs-group<?= $isLeaf ? ' menu-prefs-group--leaf' : '' ?>"
                 data-group-key="<?= esc($groupKey, 'attr') ?>"
                 data-group-label="<?= esc($searchGroup, 'attr') ?>">
              <div class="menu-prefs-group__head">
                <?php if ($isLeaf): ?>
                  <span class="menu-prefs-group__icon"><i class="<?= esc($groupIcon) ?>"></i></span>
                  <span class="menu-prefs-group__title"><?= esc($groupLabel) ?></span>
                  <label class="menu-prefs-group__switch ms-auto" title="Show or hide this item">
                    <input class="menu-item-toggle"
                           type="checkbox"
                           id="mi_<?= esc($sec['key'], 'attr') ?>"
                           data-key="<?= esc($sec['key'], 'attr') ?>"
                           checked>
                    <span class="menu-prefs-item__switch" aria-hidden="true"></span>
                  </label>
                <?php else: ?>
                  <button type="button"
                          class="menu-prefs-group__toggle"
                          aria-expanded="false"
                          aria-controls="menu-prefs-body-<?= esc($groupKey, 'attr') ?>">
                    <span class="menu-prefs-group__icon"><i class="<?= esc($groupIcon) ?>"></i></span>
                    <span class="menu-prefs-group__title"><?= esc($groupLabel) ?></span>
                    <span class="menu-prefs-group__count badge text-bg-light">0/<?= (int) $itemCount ?></span>
                    <i class="fas fa-chevron-down menu-prefs-group__chevron" aria-hidden="true"></i>
                  </button>

                  <?php if ($hasChildren && !empty($sec['key'])): ?>
                    <label class="menu-prefs-group__switch" title="Show or hide entire section">
                      <input class="menu-item-toggle"
                             type="checkbox"
                             id="mi_<?= esc($sec['key'], 'attr') ?>"
                             data-key="<?= esc($sec['key'], 'attr') ?>"
                             checked>
                      <span class="menu-prefs-item__switch" aria-hidden="true"></span>
                    </label>
                  <?php endif; ?>

                  <div class="menu-prefs-group__actions">
                    <button type="button" class="btn btn-link btn-sm prefs-group-show p-0">All</button>
                    <span class="text-muted mx-1">|</span>
                    <button type="button" class="btn btn-link btn-sm prefs-group-hide p-0">None</button>
                  </div>
                <?php endif; ?>
              </div>

              <?php if (!$isLeaf): ?>
              <div class="menu-prefs-group__body collapse" id="menu-prefs-body-<?= esc($groupKey, 'attr') ?>">
                <div class="menu-prefs-items">
                  <?php foreach ($sec['children'] as $child) {
                      $renderPrefsItem($child);
                  } ?>
                </div>
              </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <p class="text-muted small mb-0 mt-3">
          <i class="fas fa-info-circle me-1"></i>
          Hidden items won’t appear in the sidebar. Double-click a sidebar link to pin it under Quick access.
          Preferences sync to your account when you save.
        </p>
      </div>

      <div class="modal-footer border-top bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="menuPrefsCancel">Cancel</button>
        <button type="button" id="menuPrefsSave" class="btn btn-primary">
          <i class="fas fa-save me-1"></i> Save preferences
        </button>
      </div>
    </div>
  </div>
</div>
