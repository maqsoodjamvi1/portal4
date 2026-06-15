<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
	if(isset($info)){		
			$header = 'Edit Employee Timing';
			$id = 0;
			$class_id = '';
			$tid = '';
			$subject_id = '';
			
		}else{
			$header = 'Add Employee Timing';
			$id = 0;
			$class_id = '';
			$tid = '';
			$subject_id = '';
	}
?>
<?= view('components/page_header', [
    'title' => 'Employee Timing',
    'icon' => 'fas fa-user-clock',
    'subtitle' => $header ?? null,
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Employee Timing', 'active' => true],
    ],
]) ?>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
		   <div class="card card-primary card-outline card-tabs">
        	<div class="card-header p-0 pt-1 border-bottom-0">
        	<div class="card-body">		
			<div class="tab-content">
			<?php
				echo form_open(base_url('admin/emp_timing/save'), 'role="form" id="user-edit-form"');
				echo form_hidden('id', (string)$id);
			?>
			<div class="col-md-12 bg">
		        <div id="loader-1" class="overlay text-center" style="display: none;"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>
		      </div>
			<div id="timetablearea" class="timetablearea">	
			</div>
          <div class="form-group">
            <button type="submit" id="submitBtn" class="btn btn-primary">Save</button>
			<button type="reset" class="btn btn-secondary">Reset</button>
			<button type="button" class="btn btn-secondary" onclick="history.go(-1);">Cancel</button>
          </div>
            <?php echo form_close();?>
			</div>
		  </div>
        </div>
      </div>
  	</div>
    </div>
    </section>
    <!-- /.content -->
<script type="text/javascript">
$(function () {
    function hideEmpTimingLoader() {
        $('#loader-1').hide();
    }

    function initEmpTimingClockpickers() {
        if ($.fn.clockpicker) {
            $('#timetablearea .clockpicker').clockpicker();
        }
    }

    function bindEmpTimingBulkActions() {
        $('#timetablearea').off('change.empTiming');

        $('#timetablearea').on('change.empTiming', '.emp-timing-set-col', function () {
            if (!this.checked) {
                return;
            }
            var day = $(this).data('day');
            var firstUser = $(this).data('first-user');
            var checkin = $('#' + day + '_' + firstUser + '_checkin_date').val();
            var checkout = $('#' + day + '_' + firstUser + '_checkout_date').val();
            $('.clockpicker_' + day).val(checkin);
            $('.clockpickercheckout_' + day).val(checkout);
        });

        $('#timetablearea').on('change.empTiming', '.emp-timing-set-off', function () {
            if (!this.checked) {
                return;
            }
            var day = $(this).data('day');
            var fallback = '';
            $('#timetablearea .clockpicker_' + day).each(function () {
                var v = ($(this).val() || '').trim();
                if (v) {
                    fallback = v;
                    return false;
                }
            });
            if (!fallback) {
                if (window.toastr) {
                    toastr.warning('Set check-in on one row first, or enter a time.');
                }
                this.checked = false;
                return;
            }
            $('#timetablearea .clockpicker_' + day).each(function () {
                var $cin = $(this);
                var id = $cin.attr('id') || '';
                var coutId = id.replace('_checkin_date', '_checkout_date');
                var cin = ($cin.val() || '').trim() || fallback;
                $cin.val(cin);
                if (coutId) {
                    $('#' + coutId).val(cin);
                }
            });
            this.checked = false;
        });

        $('#timetablearea').on('change.empTiming', '.emp-timing-set-row', function () {
            if (!this.checked) {
                return;
            }
            var userId = $(this).data('user-id');
            var checkin = $('#Monday_' + userId + '_checkin_date').val();
            var checkout = $('#Monday_' + userId + '_checkout_date').val();
            $('.clockpicker_' + userId).val(checkin);
            $('.clockpickercheckout_' + userId).val(checkout);
        });
    }

    function loadEmpTimingGrid() {
        $('#loader-1').show();
        $.ajax({
            url: '<?= base_url('admin/emp_timing/data') ?>',
            type: 'POST',
            dataType: 'html',
            data: {},
            success: function (res) {
                $('#timetablearea').html(res);
                initEmpTimingClockpickers();
                bindEmpTimingBulkActions();
            },
            error: function (xhr) {
                var msg = 'Could not load employee timing grid.';
                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    msg = xhr.responseJSON.msg;
                }
                $('#timetablearea').html("<div class='alert alert-danger mb-0'>" + msg + '</div>');
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                }
            },
            complete: hideEmpTimingLoader
        });
    }

    loadEmpTimingGrid();

    $('#user-edit-form').validate({});

    $('#user-edit-form').ajaxForm({
        beforeSubmit: function () {
            $('#submitBtn').html('Saving...').prop('disabled', true);
            return $('#user-edit-form').valid();
        },
        success: function (responseText) {
            $('#submitBtn').html('Save').prop('disabled', false);
            var json = typeof responseText === 'object' ? responseText : $.parseJSON(responseText);
            if (json.success) {
                toastr.success(json.msg);
            } else {
                toastr.error(json.msg || 'Save failed.');
            }
        },
        error: function () {
            $('#submitBtn').html('Save').prop('disabled', false);
            toastr.error('Save request failed. Please try again.');
        }
    });
});
</script>
<?= $this->endSection() ?>
