<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php

	if(isset($info)){



		$header = 'Edit Class Subject Category';

		$id = $info->class_sub_cat_id;

		$class_id = $info->class_id;

		$sub_cat_id = $info->sub_cat_id;



	}else{

		$header = 'Add Class Subject Category';

		$id = '';

		$class_id = '';

		$sub_cat_id = '';



	}

?>

   <!-- Content Header (Page header) -->

    <section class="content-header">

      <h1>

        Class Subject Category

        <small></small>

      </h1>

      <ol class="breadcrumb">

        <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <li class="active">Class Subject Category</li>

      </ol>

    </section>

    <!-- Main content -->

    <section class="content">

      <div class="row">

        <div class="col-xs-12">

		  <div class="nav-tabs-custom">

			<ul class="nav nav-tabs">

				<li><a href="<?= base_url('admin/class_sub_cat') ?>">Class Subject Categories</a></li>

				<?php

				if($id == ''){

					?>

					<li class="active"><a href="<?= base_url('admin/class_sub_cat/add') ?>"><?php echo $header;?></a></li>

					<?php

				}else{

					?>

					<li class="active"><a href="<?php echo '#/class_sub_cat?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>

			   <?php } ?>

			</ul>

			<div class="tab-content">

			<?php

				echo form_open('c=class_sub_cat&m=save', 'role="form" id="class-subject-category-edit-form"');

				echo form_hidden('id', $id);

			?>

                <div class="form-group">

                  <label for="subject_name">Classes</label>

	              <select class="form-control" name="class_id" id="class_id">

	              <?php if(isset($classinfo)){

						foreach ($classinfo as  $sectionvalue) { ?>

	              			<option <?php if($sectionvalue->class_id == $class_id) { ?> selected <?php } ?> value="<?php echo $sectionvalue->class_id?>">

				  			<?php echo $sectionvalue->class_name?>

				 			</option>

	              <?php } ?>

	              <?php	}; ?>

	            </select>

				</div>

				<div class="form-group">

                  <label for="subject_id">Subjects</label>

              	  <select class="form-control" name="subject_id" id="subject_id">

              		<?php if(isset($allsubject_info)){

						foreach ($allsubject_info as  $subjectvalue) { ?>

              				<option  value="<?php echo $subjectvalue->sid?>">

			  				<?php echo $subjectvalue->subject_name?>

			  				</option>

              		<?php } ?>

              		<?php } ?>

            	</select>

				</div>

				<div class="form-group">

                  <label for="subject_name">Class Subject Category</label>

				  <div id="subcatID">

            		  <?php if(isset($sub_category_info)){

							foreach ($sub_category_info as  $subject_category) { 

				 	  ?>

              				<input type="checkbox" name="sub_cat_id[]" <?php if($subject_category->sub_cat_id == $sub_cat_id) { ?> selected <?php } ?> value="<?php echo $subject_category->sub_cat_id?>">

			  				<?php echo $subject_category->cat_name?> <br />

			

              		<?php } ?>

              		<?php } ?>

            	<!--</select>	-->		

				</div>

				</div>

              <div class="form-group">

                <button type="submit" class="btn btn-primary">Save</button>

				<button type="reset" class="btn btn-default">Reset</button>

				<button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>

              </div>

            <?php echo form_close();?>

			</div>

		  </div>

        </div>

      </div>

    </section>

    <!-- /.content -->

<script>

 $("#subject_id").change(function(){

        var class_id = $('#class_id').val();

		var subject_id = $('#subject_id').val();

         $.ajax({

            url: 'admin.php?c=ajax&m=selectClassSubCat',

            type: "POST",

            data:{class_id:class_id,subject_id:subject_id },

            success:function(res){

 			   $("#subcatID").html(res);

			}

         });

    });

</script>	

<script type="text/javascript">

$(function(){

	$('#class-subject-category-edit-form').validate({

		rules:{

			name:{

				required:true,		

			}

		},

		messages:{

			name:{

				required:'Class Subject Category is Required',

			}

		}

	});

	$('#class-subject-category-edit-form').ajaxForm({

		beforeSubmit:function(formData, jqForm, options){

			return $('#class-subject-category-edit-form').valid();

		},

		success:function(responseText, statusText, xhr, form){

			var json = $.parseJSON(responseText);

			if(json.success){

				toastr.success(json.msg);

				<?php

				if($id == ''){

					?>

					location.href = '#/class_sub_cat';

					<?php

				}else{

					?>

					location.href = '#/class_sub_cat?m=edit&id=<?php echo $id;?>&after=edit';

					<?php

				}

				?>

			}else{

				toastr.error(json.msg);

			}

			return false;

		}

	});

})

</script>

<?= $this->endSection() ?>