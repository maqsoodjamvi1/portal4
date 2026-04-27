<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container mt-4">
    <h3>Edit Objective (Class: <?= $objective->class_id ?>, Subject: <?= $objective->subject_id ?>)</h3>
    
    <?php if(validation_errors()): ?>
        <div class="alert alert-danger"><?= validation_errors() ?></div>
    <?php endif; ?>
    
    
    <?= form_open(site_url('Top_level_planning_gradewise/update/'.$objective->tlp_id)) ?>
        <div class="form-group">
            <textarea name="objective" class="form-control" rows="6" style="direction: <?= in_array($objective->subject_name, ['Urdu','Islamiat','Nazra']) ? 'rtl' : 'ltr' ?>"><?= htmlspecialchars($objective->objective) ?></textarea>
        </div>
        
        <input type="hidden" name="class_id" value="<?= $objective->class_id ?>">
        <input type="hidden" name="subject_id" value="<?= $objective->subject_id ?>">
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
        </button>

        <a href="<?= site_url('Top_level_planning_gradewise') ?>" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
    <?= form_close() ?>
</div>

<?= $this->endSection() ?>