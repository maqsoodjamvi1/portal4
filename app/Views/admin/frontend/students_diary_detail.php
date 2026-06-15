<?php
if(isset($_GET['parent_id'])){
  $parent_id = $_GET['parent_id'];
}else{
  echo "Parent not found";
  exit;
}

if(isset($_GET['campus_id'])){
  $campus_id = $_GET['campus_id'];
}else{
  echo "Parent not found";
  exit;
}
?>

<!-- Main content -->
<section class="container content">
  <div class="row">
    <div class="col-lg-12">
      <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
       	 <div class="card-body">  
        <div class="tab-content">
		  <div class="row">          
		  <div class="col-lg-12">
         <div id="students_list_container" ></div>
		 </div>
		  </div>
		</div>
		</div>
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
<script>
$( document ).ready(function() {
 		$("#loader-1").css("display", "block");
    var parent_id = <?php echo $parent_id; ?>;
    var campus_id = <?php echo $campus_id; ?>;
		
 	      $.ajax({
            url: '/students_diary_detail/get_students_diary',
            type: "POST",
            data:{parent_id:parent_id,campus_id:campus_id},
            success:function(res){
 			   $("#students_list_container").html(res);
 			    $("#loader-1").css("display", "none");
 			  }
         });
 });
</script>