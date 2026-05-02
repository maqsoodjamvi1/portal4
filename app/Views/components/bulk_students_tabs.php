<?php
$active = $active ?? '';
$tabs = [
    ['key' => 'names', 'label' => 'Student Names', 'url' => base_url('admin/addbulkstudents/add')],
    ['key' => 'class', 'label' => 'Class Change', 'url' => base_url('admin/studentsbulk')],
    ['key' => 'other', 'label' => 'Other Info', 'url' => base_url('admin/students_bulk_info')],
    ['key' => 'fee', 'label' => 'Fee Info', 'url' => base_url('admin/students_bulk_fee_info')],
    ['key' => 'parent', 'label' => 'Parent Info', 'url' => base_url('admin/studentsbulkparents')],
    ['key' => 'dob', 'label' => 'Date of Birth & BMI', 'url' => base_url('admin/students_bulk_info_date_of_birth')],
    ['key' => 'excel', 'label' => 'Excel Import', 'url' => base_url('admin/studentsbulkcsv/addbulk')],
];
?>
<style>
.bulk-tabs-wrap {
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  border-bottom: 1px solid #e9ecef;
}
.bulk-tabs {
  display: flex;
  flex-wrap: nowrap;
  gap: 6px;
  padding: 8px 8px 0;
  margin: 0;
  list-style: none;
}
.bulk-tabs .nav-link {
  white-space: nowrap;
  border: 1px solid #dbe3ea;
  border-bottom: 0;
  border-radius: 8px 8px 0 0;
  background: #f8fafc;
  color: #334155;
  padding: 8px 12px;
  font-weight: 600;
  font-size: 13px;
}
.bulk-tabs .nav-link.active {
  background: #fff;
  color: #0f172a;
  border-color: #cfd8e3;
  box-shadow: inset 0 -2px 0 #0d6efd;
}
</style>
<div class="bulk-tabs-wrap">
  <ul class="bulk-tabs nav">
    <?php foreach ($tabs as $tab): ?>
      <li class="nav-item">
        <a class="nav-link <?= $active === $tab['key'] ? 'active' : '' ?>" href="<?= $tab['url'] ?>">
          <?= esc($tab['label']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
