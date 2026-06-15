<?php
/**
 * Form field with Bootstrap 5 validation hooks.
 *
 * @var string       $name
 * @var string       $label
 * @var string       $type       text|email|select|textarea|hidden|password|number
 * @var string|null  $id
 * @var string|null  $value
 * @var bool         $required
 * @var string|null  $help
 * @var string|null  $error
 * @var array        $options    For select: [['value'=>'','label'=>'']]
 * @var string       $colClass
 * @var string       $inputClass
 * @var string       $attrs      Extra attributes on input
 */
$name = $name ?? '';
$label = $label ?? '';
$type = $type ?? 'text';
$id = $id ?? $name;
$value = $value ?? '';
$required = !empty($required);
$help = $help ?? null;
$error = $error ?? null;
$options = $options ?? [];
$colClass = $colClass ?? 'form-group';
$inputClass = $inputClass ?? 'form-control';
$attrs = $attrs ?? '';
$invalid = $error ? ' is-invalid' : '';
$reqMark = $required ? ' <span class="text-danger">*</span>' : '';
$reqAttr = $required ? ' required' : '';
?>
<div class="<?= esc($colClass, 'attr') ?> sms-form-field">
  <?php if ($type !== 'hidden' && $label !== ''): ?>
    <label class="sms-label" for="<?= esc($id, 'attr') ?>"><?= esc($label) ?><?= $reqMark ?></label>
  <?php endif; ?>

  <?php if ($type === 'select'): ?>
    <select name="<?= esc($name, 'attr') ?>" id="<?= esc($id, 'attr') ?>" class="<?= esc($inputClass, 'attr') ?><?= $invalid ?>"<?= $reqAttr ?> <?= $attrs ?>>
      <?php foreach ($options as $opt): ?>
        <option value="<?= esc($opt['value'] ?? '', 'attr') ?>" <?= (string)($opt['value'] ?? '') === (string)$value ? 'selected' : '' ?>>
          <?= esc($opt['label'] ?? '') ?>
        </option>
      <?php endforeach; ?>
    </select>
  <?php elseif ($type === 'textarea'): ?>
    <textarea name="<?= esc($name, 'attr') ?>" id="<?= esc($id, 'attr') ?>" class="<?= esc($inputClass, 'attr') ?><?= $invalid ?>"<?= $reqAttr ?> <?= $attrs ?>><?= esc($value) ?></textarea>
  <?php else: ?>
    <input type="<?= esc($type, 'attr') ?>" name="<?= esc($name, 'attr') ?>" id="<?= esc($id, 'attr') ?>"
           class="<?= esc($inputClass, 'attr') ?><?= $invalid ?>" value="<?= esc($type === 'password' ? '' : $value, 'attr') ?>"<?= $reqAttr ?> <?= $attrs ?>>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="invalid-feedback d-block"><?= esc($error) ?></div>
  <?php endif; ?>
  <?php if ($help): ?>
    <small class="form-text text-muted"><?= esc($help) ?></small>
  <?php endif; ?>
</div>
