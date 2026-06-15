<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php

			if(isset($info)){

				$header = 'Partial Payment';

				

				$id = $info->chalan_id;		

				$student_id = $info->student_id;				

				$issue_date = DateTime::createFromFormat('Y-m-d',$info->issue_date);

				$issue_date = $issue_date->format('d/m/Y');

				$due_date = DateTime::createFromFormat('Y-m-d',$info->due_date);

				$due_date = $due_date->format('d/m/Y');

				$fee_type_id = $info->fee_type_id;

				$fee_month = $info->fee_month;

				$amount = $info->amount;

				$discount = $info->discount;



			}else{

				$header = 'Add Partial Payment';

				$id = '';

				$due_date = '';

				$issue_date = '';

				$fee_month = '';

				$amount = '';

				$discount = '';



			}

			?>
    <?= view('components/page_header', [
    'title' => 'Partial Payment',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Partial Payment', 'active' => true],
    ],
]) ?>


    <!-- Main content -->

    <section class="content">

      <div class="row">

        <div class="col-12">



		  <div class="nav-tabs-custom">

			<div class="tab-content">

				<?php

			echo form_open('c=fee_partial_payment&m=save', 'role="form" id="user-edit-form"');

			echo form_hidden('id', $id);

			echo form_hidden('student_id', $student_id);

			echo form_hidden('fee_type_id', $fee_type_id);

			echo form_hidden('discount', $discount);

			?>

				

		<div class="form-group">

                <label>Issue Date:</label>



                <div class="input-group date">

                  <div class="input-group-text">

                    <i class="fa fa-calendar"></i>

                  </div>

                  <input type="text" class="form-control float-end" id="datepicker2"  readonly="readonly"  value="<?php echo $issue_date; ?>" name="issue_date">

                </div>

                <!-- /.input group -->

              </div>	

		<div class="form-group">

                <label>Due Date:</label>

	        <div class="input-group date">

                  <div class="input-group-text">

                    <i class="fa fa-calendar"></i>

                  </div>

                  <input type="text" class="form-control float-end" id="datepicker"  readonly="readonly" value="<?php echo $due_date; ?>" name="due_date">

                </div>

                <!-- /.input group -->

              </div>			  						

			<div class="form-group">

                <label>Fee Month:</label>

                <div class="input-group date">

                  <div class="input-group-text">

                    <i class="fa fa-calendar"></i>

                  </div>

                  <input type="text" class="form-control float-end" id="datetimepicker1" readonly="readonly" value="<?php echo $fee_month; ?>"  name="fee_month">

				  

                </div>

                <!-- /.input group -->

              </div>

				

<div class="form-group">

                <label>Amount:</label>

                  <input type="hidden" class="form-control float-end" id="amount"  readonly="readonly" value="<?php echo $amount; ?>"  name="amount">

				    <input type="text" class="form-control float-end" id="disc_amount"  readonly="readonly" value="<?php echo ($amount - $discount); ?>"  name="disc_amount">

			    <!-- /.input group -->

              </div>	

			   <br /> <br />		

<div class="form-group">

                <label>Paid Amount:</label>

                  <input type="text" class="form-control float-end" id="paid_amount" value=""  name="paid_amount">

				  <!-- /.input group -->

              </div>			

 <br /> <br />

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

        $(function () {

            $('#datepicker10').datetimepicker({

               format: "MM/YYYY",

    startView: "year", 

    minView: "year"

            });

        });

    </script>

<script type="text/javascript">

$(function(){

  //Date picker

    $('#datepicker').datetimepicker({

       format: 'DD/MM/YYYY'

    })

	 $('#datepicker2').datetimepicker({

        format: 'DD/MM/YYYY'

    })

	<?php

				if($id == ''){

					?>

	$('#user-edit-form').validate({

		rules:{

			fee_month:{

				required:true,

				remote:{

					param:{

						url:'<?php echo base_url('admin/ajax/check_value&table=fee_chalan&field=fee_month'); ?>'

					},

					depends:function(element){

						var id = $(element).attr('id');

						return ($(element).val() !== $('#original' + id).val());

					}

				}

			}

			},

			messages:{

			fee_month:{

				required:'fee month is Required',

				remote:'fee month is exists'

			}

			}

			

	});

		<?php

				}else{

					?>

				$('#user-edit-form').validate({	});

					<?php } ?>

	$('#user-edit-form').ajaxForm({

		beforeSubmit:function(formData, jqForm, options){

			return $('#user-edit-form').valid();

		},

		success:function(responseText, statusText, xhr, form){

			var json = $.parseJSON(responseText);

			if(json.success){

				toastr.success(json.msg);

				<?php

				if($id == ''){

					?>

					location.href = '#/fee_partial_payment?m=edit&chalan_id=<?php echo $id;?>&after=edit';

					<?php

				}else{

					?>

					location.href = '#/fee_partial_payment?m=edit&chalan_id=<?php echo $id;?>&after=edit';

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