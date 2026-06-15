<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php

	if(isset($info)){



		$header = 'Edit Chalan Type';

		$id = $info->chalan_type_id;

		$chalan_type_name = $info->chalan_type_name;

		$chalan_type_detail = $info->chalan_type_detail;



	}else{

		$header = 'Add Chalan Type';

		$id = '';

		$chalan_type_name = '';

		$chalan_type_detail = '';



	}

?>
 <?= view('components/page_header', [
    'title' => 'Chalan Type',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Chalan Type', 'active' => true],
    ],
]) ?>


   <!-- Main content -->

    <section class="content">

      <div class="row">

        <div class="col-12">

		  <div class="nav-tabs-custom">

			<ul class="nav nav-tabs">

				<li><a href="<?= base_url('admin/chalan_type') ?>">Chalan Type</a></li>

				<?php

				if($id == ''){

					?>

					<li class="active"><a href="<?= base_url('admin/chalan_type/add') ?>"><?php echo $header;?></a></li>

					<?php

				}else{

					?>

					<li class="active"><a href="<?php echo '#/chalan_type?m=edit&id=' . $id;?>"><?php echo $header;?></a></li>

					<?php

				}

				?>

			</ul>

		   <div class="tab-content">

			<?php

			echo form_open('c=chalan_type&m=save', 'role="form" id="user-edit-form"');

			echo form_hidden('id', $id);

			?>

	<p class="page-header">Basic</p>

        <div class="form-group">

          <label for="subject_name">Fee Type Name</label>

          <input type="text" class="form-control" name="chalan_type_name" id="chalan_type_name" value="<?php echo $chalan_type_name;?>">

		</div>

	  <div class="form-group">

     	<label for="subject_name">Chalan Type Detail</label>

      	<input type="text" class="form-control" name="chalan_type_detail" id="chalan_type_detail" value="<?php echo $chalan_type_detail;?>">

	  </div>

      <div class="form-group">

        <button type="submit" class="btn btn-primary">Save</button>

		<button type="reset" class="btn btn-secondary">Reset</button>

		<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>

      </div>

     <?php echo form_close();?>

	</div>

</div>

</div>

</div>

</section>

<!-- /.content -->

<script type="text/javascript">

$(function(){

	$('#chalan-type-edit-form').validate({

		rules:{

			chalan_type_name:{

				required:true,

				

			}

		},

		messages:{

			chalan_type_name:{

				required:'chalan type name is Required',

				}

		}

	});

	$('#chalan-type-edit-form').ajaxForm({

		beforeSubmit:function(formData, jqForm, options){

			return $('#chalan-type-edit-form').valid();

		},

		success:function(responseText, statusText, xhr, form){

			var json = $.parseJSON(responseText);

			if(json.success){

				toastr.success(json.msg);

				<?php

				if($id == ''){

					?>

					location.href = '#/chalan_type';

					<?php

				}else{

					?>

					location.href = '#/chalan_type?m=edit&id=<?php echo $id;?>&after=edit';

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