<?php
$formId = $formId ?? 'reportFilterForm';
$method = $method ?? 'post';
$title = $title ?? 'Filter Report';
$fields = $fields ?? [];
$actions = $actions ?? [];
$formAttrs = $formAttrs ?? '';
$cardClass = $cardClass ?? 'card report-filter-card no-print';
?>

<div class="<?= esc($cardClass, 'attr') ?>">
  <div class="card-header">
    <h3 class="card-title"><?= esc($title) ?></h3>
  </div>
  <div class="card-body">
    <form id="<?= esc($formId, 'attr') ?>" method="<?= esc($method, 'attr') ?>" <?= $formAttrs ?>>
      <div class="row">
        <?php foreach ($fields as $field): ?>
          <?php
            $type = $field['type'] ?? 'text';
            $id = $field['id'] ?? '';
            $name = $field['name'] ?? '';
            $label = $field['label'] ?? '';
            $value = $field['value'] ?? '';
            $required = !empty($field['required']) ? 'required' : '';
            $placeholder = $field['placeholder'] ?? '';
            $colClass = $field['col_class'] ?? 'col-md-4 mb-2';
            $colStyle = $field['col_style'] ?? '';
            $class = $field['class'] ?? 'form-control';
            $attrs = $field['attrs'] ?? '';
            $options = $field['options'] ?? [];
            $rawHtml = $field['html'] ?? '';
          ?>
          <div class="<?= esc($colClass, 'attr') ?>" style="<?= esc($colStyle, 'attr') ?>">
            <?php if ($label !== ''): ?>
              <label class="report-label" for="<?= esc($id, 'attr') ?>"><?= esc($label) ?></label>
            <?php endif; ?>

            <?php if ($type === 'raw'): ?>
              <?= $rawHtml ?>
            <?php elseif ($type === 'select'): ?>
              <select
                id="<?= esc($id, 'attr') ?>"
                name="<?= esc($name, 'attr') ?>"
                class="<?= esc($class, 'attr') ?>"
                <?= $required ?>
                <?= $attrs ?>
              >
                <?php foreach ($options as $opt): ?>
                  <option value="<?= esc($opt['value'] ?? '', 'attr') ?>" <?= ((string)($opt['value'] ?? '') === (string)$value) ? 'selected' : '' ?>>
                    <?= esc($opt['label'] ?? '') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($type === 'month'): ?>
              <input
                type="month"
                id="<?= esc($id, 'attr') ?>"
                name="<?= esc($name, 'attr') ?>"
                class="<?= esc($class, 'attr') ?>"
                value="<?= esc($value, 'attr') ?>"
                placeholder="<?= esc($placeholder, 'attr') ?>"
                <?= $required ?>
                <?= $attrs ?>
              >
            <?php else: ?>
              <input
                type="<?= esc($type, 'attr') ?>"
                id="<?= esc($id, 'attr') ?>"
                name="<?= esc($name, 'attr') ?>"
                class="<?= esc($class, 'attr') ?>"
                value="<?= esc($value, 'attr') ?>"
                placeholder="<?= esc($placeholder, 'attr') ?>"
                <?= $required ?>
                <?= $attrs ?>
              >
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <?php foreach ($actions as $action): ?>
          <?php
            $btnType = $action['type'] ?? 'button';
            $btnId = $action['id'] ?? '';
            $btnClass = $action['class'] ?? 'btn btn-primary btn-block';
            $btnLabel = $action['label'] ?? 'Apply';
            $btnCol = $action['col_class'] ?? 'col-md-4 mb-2';
            $btnAttrs = $action['attrs'] ?? '';
            $btnIcon = $action['icon'] ?? '';
          ?>
          <div class="<?= esc($btnCol, 'attr') ?> d-flex align-items-end">
            <button
              type="<?= esc($btnType, 'attr') ?>"
              id="<?= esc($btnId, 'attr') ?>"
              class="<?= esc($btnClass, 'attr') ?>"
              <?= $btnAttrs ?>
            >
              <?php if ($btnIcon !== ''): ?><i class="<?= esc($btnIcon, 'attr') ?>"></i><?php endif; ?>
              <?= esc($btnLabel) ?>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </form>
  </div>
</div>
