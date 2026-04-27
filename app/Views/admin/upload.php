<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
 $servername='localhost';
    $username='timeschool';
    $password='time@123';
    $dbname = "timeschool_trial";
    $conn=mysqli_connect($servername,$username,$password,"$dbname");
      if(!$conn){
          die('Could not Connect MySql Server:' .mysql_error());
        }
 
<?php
if(is_array($_FILES)) {
if(is_uploaded_file($_FILES['userImage']['tmp_name'])) {
$sourcePath = $_FILES['userImage']['tmp_name'];
$targetPath = "uploads/".$_FILES['userImage']['name'];
if(move_uploaded_file($sourcePath,$targetPath)) {
?>
<img class="image-preview" src="<?php echo $targetPath; ?>" class="upload-preview" />
<?php
}
}
}
?>

<?= $this->endSection() ?>