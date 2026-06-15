<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
    <?= view('components/page_header', [
    'title' => 'Topic Skills',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Topic Skills', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
          	<div class="card-header p-0 pt-1 border-bottom-0">
		<div class="card-body">
		<div class="col-lg-12">
		<?php $topic_id = $data['id']; ?>
		<table class="table">
          <tr>
            <td><b>Subject:</b> <?php echo $data['subject']; ?></td>
            <td><b>Category:</b> <?php echo $data['cat_name']; ?></td>
            <td><b>Topic:</b> <?php echo $data['topic']; ?></td>
          </tr>
        </table>
	<h2>Add Quiz Questions</h2>
    <a target="_blank" href="<?php echo '#/question_text_mcqs?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">MCQS Eng</a> 
	<a target="_blank" href="<?php echo '#/eng_text_tf?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Eng Text T/F</a>
	<a target="_blank" href="<?php echo '#/eng_text_fb?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Eng F/B</a>
	<a target="_blank" href="<?php echo '#/urdu_text_mcqs?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">MCQS Urdu</a>
	<a target="_blank" href="<?php echo '#/urdu_text_fb?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Urdu F/B</a>
	<a target="_blank" href="<?php echo '#/urdu_text_tf?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Urdu T/F</a>
	<a target="_blank" href="<?php echo '#/question_bank_gk?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Eng Short Questions</a>
	<a target="_blank" href="<?php echo '#/question_bank_gk_ur?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Urdu Short Questions</a>
	<h2>Add Study Material</h2>
	<a target="_blank" href="<?php echo '#/video_lecture?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Add Video Lecture</a> 
	<a target="_blank" href="<?php echo '#/audio_lecture?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Add Audio Lecture</a>
	<a target="_blank" href="<?php echo '#/worksheet?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Add Work Sheet</a>
	<a target="_blank" href="<?php echo '#/lesson_plan?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Add Lesson Plan</a>
	<a target="_blank" href="<?php echo '#/activity?m=add&topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Add Activity</a>
	<h2>View Quiz Questions</h2>
	<a target="_blank" href="<?php echo '#/question_text_mcqs?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">MCQS Eng</a> 
	
	<a target="_blank" href="<?php echo '#/eng_text_tf?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Eng Text T/F</a>
	<a target="_blank" href="<?php echo '#/eng_text_fb?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Eng F/B</a>

	<a target="_blank" href="<?php echo '#/urdu_text_mcqs?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">MCQS Urdu</a>

	<a target="_blank" href="<?php echo '#/urdu_text_fb?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Urdu F/B</a>

	<a target="_blank" href="<?php echo '#/urdu_text_tf?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Urdu T/F</a>	 
	<a target="_blank" href="<?php echo '#/question_bank_gk?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Eng Short Questions</a>	

	<a target="_blank" href="<?php echo '#/question_bank_gk_ur?topic_id='.$topic_id;?>" title="edit" class="btn btn-primary btn-xl">Urdu Short Questions</a>	 		 

	</div></div>
</div>
<!-- /.box-body -->
</div>
<!-- /.box -->
</div>
</div>
</section>
<!-- /.content -->
<script src="<?php echo base_url();?>resource/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(function(){
	var table = $('#users-datatable').DataTable({
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		ajax:{
			url:'<?php echo base_url('admin/topic_skills/data'); ?>',
			type:'post',
			data:function(d){
				//d.csrf_test_name = $.cookie(CSRF_COOKIE_NAME);
			}
		},
		columns:[
			{
				data:'id',
				className:'select-checkbox',
				render:function(data, type, row){
					return data;
				}
			},
			{data:'class'},
			{data:'subject'},
			{data:'cat_name'},
			{data:'topic'},
			{data:'topic_skill'},
			{
				data:'id',
				sortable:false,
				render:function(data, type, row){
					var html = '';
					html += '<div class="btn-group">';
						  html += '<a href="<?php echo '#/topic_skills?m=edit&id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm"><i class="fa fa-pencil icon-pencil"></i></a>';

						   html += '<a target="_blank" href="<?php echo '#/question_text_mcqs?m=add&topic_skill_id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">MCQS Eng</a>'; 

						     html += '<a target="_blank" href="<?php echo '#/eng_text_tf?m=add&topic_skill_id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">Eng Text T/F</a>'; 

						   html += '<a target="_blank" href="<?php echo '#/eng_text_fb?m=add&topic_skill_id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">Eng F/B</a>'; 

						   html += '<a target="_blank" href="<?php echo '#/urdu_text_mcqs?m=add&topic_skill_id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">MCQS Urdu</a>'; 

						   html += '<a target="_blank" href="<?php echo '#/urdu_text_fb?m=add&topic_skill_id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">Urdu F/B</a>'; 

						   html += '<a target="_blank" href="<?php echo '#/urdu_text_tf?m=add&topic_skill_id=';?>' + data + '" title="edit" class="btn btn-secondary btn-sm">Urdu T/F</a>'; 

						  

						  if(row.issys == '1'){



						  }else{

							  //html += '<a href="javascript:;" onclick="del_confirm(\'notice\', \'Are you sure delete this record\', \'<?php //echo site_url('c=subject_cat&m=delete&id=');?>' + data + '\',\'users-datatable\');" title=" delete" class="btn btn-secondary btn-sm"><i class="fa fa-trash icon-trash"></i></a>';

						  }



					html += '</div>';

					return html;

				}

			}

		],

		fnDrawCallback:function(oSettings){

			$(".switchchk").bootstrapSwitch({

				onSwitchChange:function(e, state){

				var fieldval = state;

				var $element = $(e.currentTarget);

				var tablename = $element.attr('data-table');

				var fieldname = $element.attr('data-field');

				var rowid = $element.attr('data-pk');

				if(fieldval){

					fieldval = 1;

				}else{

					fieldval = 0;

				}

				$.post(

				   "<?php echo base_url('admin/ajax/setboolattribute'); ?>",

				   {

					   act:'upsort',

					   tbname:tablename,

					   tbfield:fieldname,

					   tbfieldvalue:fieldval,

					   id:rowid//,

					   // csrf_test_name:$.cookie(CSRF_COOKIE_NAME)



				   },

				   function(data){

					//alert(data);

					   if(data=='success'){

						   toastr.success('change success');



					   }else{

						   toastr.error('change error');

					   }

				   }

				  );

				}

			});

		}

	});

});

</script>

<?= $this->endSection() ?>