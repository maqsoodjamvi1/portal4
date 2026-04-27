<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php

	if(isset($info)){



			$header = 'Edit Class Subjects';

			$id = $info->cs_id;

			$class_id = $info->class_id;

			$subject_id = intval($info->subject_id);

			

		}else{

			$header = 'Add Class Subjects';

			$id = 0;

			$class_id = '';

			$subject_id = '';

		}

?>

<!-- Content Header (Page header) -->



<section class="content-header">

  <h1> Class Subjects <small></small> </h1>

  <ol class="breadcrumb">

    <li><a href="<?= base_url('admin/dashboard') ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>

    <li class="active">Class Subjects</li>

  </ol>

</section>

<!-- Main content -->

<section class="content">

  <div class="row">

    <div class="col-xs-12">

      <div class="nav-tabs-custom">

        <ul class="nav nav-tabs">

          <li><a href="<?= base_url('admin/class_subjects') ?>">Class Subjects</a></li>

          <?php if($id == ''){ ?>

          <li class="active"><a href="<?= base_url('admin/class_subjects/add') ?>"><?php echo $header;?></a></li>

          <?php }else{ ?>

          <li class="active"><a href="<?php echo '#/class_subjects?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>

          <?php } ?>

        </ul>

        <div class="tab-content">

          <?php

			echo form_open('c=class_subjects&m=save', 'role="form" id="class-subjects-edit-form"');

			echo form_hidden('id', $id);

			?>

          <div class="form-group">

            <label for="class">Class</label>

            <select class="form-control" name="class_id">

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

            <label for="class">Subjects</label>

            <?php if(isset($subjectinfo)){

				foreach ($subjectinfo as  $subjectvalue) { ?>

            		<label class="form-control">

            		<input type="checkbox" name="subjects[]"  value="<?php echo $subjectvalue->sid?>"  /> <?php echo $subjectvalue->subject_name?>

            		</label>

            	<?php } ?>

            <?php } ?>

          </div>

          <div class="form-group">

            <button type="submit" class="btn btn-primary">Save</button>

            <button type="reset" class="btn btn-default">Reset</button>

            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>

          </div>

          <?php echo form_close();?> </div>

      </div>

    </div>

  </div>

</section>

<!-- /.content -->

<script type="text/javascript">

$(function(){

	$('#class-subjects-edit-form').validate({

		

	});

	$('#class-subjects-edit-form').ajaxForm({

		beforeSubmit:function(formData, jqForm, options){

			return $('#class-subjects-edit-form').valid();

		},

		success:function(responseText, statusText, xhr, form){

			var json = $.parseJSON(responseText);

			if(json.success){

				toastr.success(json.msg);

				<?php

				if($id == ''){

					?>

					location.href = '#/class_subjects';

					<?php

				}else{

					?>

					location.href = '#/class_subjects?m=edit&id=<?php echo $id;?>&after=edit';

					<?php

				}

				?>

			}else{

				toastr.error(json.msg);

			}

			return false;

		}

	});

});

</script>

<?= $this->endSection() ?>