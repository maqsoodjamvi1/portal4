<?= form_open(base_url('/admin/fee-chalan-pay/save'), ['role' => 'form', 'id' => 'partial-pay-form']) ?>
<div class="row">
  <div class="form-group col-lg-4">
    <label>Date Paid:</label>
    <div class="input-group date" id="datepicker2" data-target-input="nearest">
      <input type="text" id="datePaid" name="paid_date" autocomplete="off" class="form-control datetimepicker-input" data-bs-target="#datepicker2" />
      <span class="input-group-text" data-bs-target="#datepicker2" data-bs-toggle="datetimepicker"><i class="fa fa-calendar"></i></span>
    </div>
  </div>
  <div class="col-lg-4">
    <label>Class</label>
    <select class="form-control select2" name="cls_sec_id" id="cls_sec_id" required>
      <option value="0">Select Section</option>
      <?php foreach ($sectionsclassinfo ?? [] as $section): ?>
        <option value="<?= $section['section_id'] ?>"><?= $section['sectionclassname'] ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group col-lg-4">
    <label>Reg No</label>
    <input type="text" class="form-control" name="reg_no" id="reg_no">
  </div>
</div>

<div class="row">
  <div class="form-group col-lg-4">
    <label>Student Name</label>
    <select class="form-control select2" name="student_id" id="student_id">
      <option value="0">Select Student</option>
    </select>
  </div>
  <div class="form-group col-lg-4">
    <label>Parent Name</label>
    <select class="form-control select2" name="parent_id" id="parent_id">
      <option value="0">Select Parent</option>
    </select>
  </div>
  <div class="form-group col-lg-4">
    <label>Family ID</label>
    <input type="text" class="form-control" name="family_id" id="family_id">
  </div>
</div>

<div id="feetypeinfo">
  <table class="table table-bordered"><tbody id="tbody"></tbody></table>
</div>
<?= form_close() ?>
