<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
if (is_array($_FILES ?? null) && ! empty($_FILES['userImage']['tmp_name']) && is_uploaded_file($_FILES['userImage']['tmp_name'])) {
    $targetName = basename((string) $_FILES['userImage']['name']);
    if ($targetName !== '' && $targetName !== '.' && $targetName !== '..') {
        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR;
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $targetPath = $uploadDir . $targetName;
        if (move_uploaded_file($_FILES['userImage']['tmp_name'], $targetPath)) {
            ?>
<img class="image-preview upload-preview" src="<?= esc(base_url('uploads/' . $targetName)) ?>" alt="" />
            <?php
        }
    }
}
?>

<?= $this->endSection() ?>
