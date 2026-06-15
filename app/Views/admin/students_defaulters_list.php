<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php
  $status = '';
  if (!empty($_GET['status'])) {
      $status = $_GET['status'];
  }
?>
<link rel="stylesheet" href="<?= base_url('resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>" />

<?= view('components/page_header', [
    'title' => 'Students Defaulters List',
    'icon' => 'fas fa-user-times',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Students', 'url' => base_url('admin/students')],
        ['label' => 'Defaulters List', 'active' => true],
    ],
]) ?>
<?php ob_start(); ?>
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="student_id" class="report-label">Student</label>
        <select class="form-control form-control-sm select2" name="student_id" id="student_id" style="width:100%;">
            <option value="0">Select Student</option>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="parent_id" class="report-label">Parent</label>
        <select class="form-control form-control-sm select2" name="parent_id" id="parent_id" style="width:100%;">
            <option value="0">Select Parent</option>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="cls_sec_id" class="report-label">Section</label>
        <select class="form-control form-control-sm select2" name="cls_sec_id" id="cls_sec_id" required style="width:100%;">
            <option value="0">Select Section</option>
            <option value="all">All Sections</option>
            <?php if (!empty($sectionsclassinfo)) : ?>
                <?php foreach ($sectionsclassinfo as $secionvalue) : ?>
                    <option value="<?= esc($secionvalue['section_id']) ?>"><?= esc($secionvalue['sectionclassname']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="month" class="report-label">Fee month</label>
        <select class="form-control form-control-sm select2" id="month" name="month">
            <option value="">Select Fee Month</option>
            <?php foreach ($months as $name) : ?>
                <option value="<?= esc($name['id']) ?>"><?= esc($name['value']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="fee_type" class="report-label">Fee type</label>
        <select class="form-control form-control-sm select2" id="fee_type" name="fee_type">
            <option value="">Select Fee Type</option>
            <?php foreach ($fee_types as $value) : ?>
                <option value="<?= (int) $value->fee_type_id ?>"><?= esc($value->fee_type_name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-12 mb-2 d-flex align-items-end">
        <button type="button" id="btn-filter" class="btn btn-primary btn-sm me-1">Filter</button>
        <button type="button" id="btn-reset" class="btn btn-secondary btn-sm">Reset</button>
    </div>
</div>
<?php $filterBodyHtml = ob_get_clean(); ?>

<?php ob_start(); ?>
<table class="table table-striped table-bordered table-hover mb-0" id="students-datatable" width="100%" style="font-size:13px;">
    <thead>
        <tr style="vertical-align: middle;">
            <th nowrap>#</th>
            <th style="width:250px;">F Id</th>
            <th style="width:250px;">F Name</th>
            <th style="width:250px;">Name</th>
            <th style="width:150px;" nowrap>Class</th>
            <th nowrap>Father<br>Contact</th>
            <th id="dynamicCol" style="width:70px;"><?= esc(date('M Y')) ?></th>
            <th style="width:70px;">Previous<br>Balance</th>
            <th style="width:70px;">Total</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr>
            <th></th><th></th><th></th><th></th><th></th><th></th>
            <th id="total"></th><th></th><th></th>
        </tr>
    </tfoot>
</table>
<?php $tableHtml = ob_get_clean(); ?>

<section class="content">
    <div class="card sms-card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link <?= $status == 1 ? 'active' : '' ?>" href="#/students_defaulters_list?status=1">Current Defaulters</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 3 ? 'active' : '' ?>" href="#/students_defaulters_list?status=3">Dropped Defaulters</a></li>
                <li class="nav-item"><a class="nav-link <?= $status == 4 ? 'active' : '' ?>" href="#/family_defaulters_list?status=1">Defaulter Family List</a></li>
            </ul>
        </div>
        <div class="card-body pt-3">
            <?= view('components/filter_card', [
                'title' => 'Filters',
                'bodyHtml' => $filterBodyHtml,
                'cardClass' => 'card sms-filter-card report-filter-card mb-3',
            ]) ?>
            <?= view('components/data_table_card', [
                'title' => 'Defaulters',
                'icon' => 'fas fa-user-times',
                'tableHtml' => $tableHtml,
                'cardClass' => 'card sms-card mb-0',
            ]) ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<style>
  table.table-bordered th:last-child,
  table.table-bordered td:last-child { width: 50px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/api/sum().js"></script>
<script>
   
$(function(){
    var selectedFeeTypeTxt = 'Current<br> Month';
    $('#btn-filter').click(function(){ //button filter event click
        var selectedFeeTypeVal = $('#fee_type').children("option:selected").val();
        if(selectedFeeTypeVal){
            selectedFeeTypeTxt = $('#fee_type').children("option:selected").text();
            //alert("You have selected the selectedFeeTypeTxt - " + selectedFeeTypeTxt);
            $('#dynamicCol').val(selectedFeeTypeTxt);
        }
        
    });
	var table = $('#students-datatable').DataTable({
	  dom: 'Bfrtip',
		buttons: [
		'colvis', 'csv',
		
         {
          text: 'PDF',
          title: 'Defaulters List',
          extend: 'pdfHtml5',
          filename: 'defaulters-list',
          footer: true,
          orientation: 'landscape', //portrait
          pageSize: 'A4', //A3 , A5 , A6 , legal , letter
          exportOptions: {
             columns: [ 1,2,3,4,5,6,7],
          },
        }
		],
		deferRender: true,
		select:{
			style:'single',
			blurable: true
		},
		
    	"processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "ordering": false,
        "order": [], //Initial no order.
		"pageLength": 200,
		 "searching": false,
		ajax:{
			url:'<?= base_url('admin/students_defaulters_list/data') . '?status=' . $_GET['status'] ?>',
			type:'post',

			data:function(d){
			 	d.status = $('#status').val();
                d.student_id = $('#student_id').val();
                d.cls_sec_id = $('#cls_sec_id').val();
                d.parent_id = $('#parent_id').val();
                d.fee_type = $('#fee_type').val();
                d.month = $('#month').val();
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
			//{data:'reg_no'},
            {data:'parent_id'},
            {data:'f_name'},

			{data:'name'},	
			
         	{data:'class'},
            {data:'f_contacts'},
            //{data:'m_contacts'},
            //{ title: "columnName",data:'monthly_unpaid' },

             {
                data:'monthly_unpaid',
                title: selectedFeeTypeTxt,
                className:'select-checkbox',
                render:function(data, type, row){
                    return row.monthly_unpaid;
                }
            },
            {data:'previous_balance'},
         	{data:'payable'},  
          // {data:'w_contacts'}
	
		],
        footerCallback: function ( row, data, start, end, display ) {
   
   
      if(data != ''){
            var api = this.api();
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };    
        // Total over all pages
            var total = api
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                });    
      // Total over all pages
            var total2 = api
                .column( 7 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                });
      // Total over all pages
            var total3 = api
                .column( 8 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                } );
     
           
   // Update footer
            $( api.column(6).footer() ).html(
                Number(total).toFixed(2)
            );
      // Update footer
            $( api.column(7).footer() ).html(
                Number(total2).toFixed(2)
            );
      // Update footer
            $( api.column(8).footer() ).html(
                 Number(total3).toFixed(2)
            );
        }
        }       
         
    });

	 $('#btn-filter').click(function(){ //button filter event click
        table.ajax.reload();  //just reload table
        // var selectedFeeTypeVal = $('#fee_type').children("option:selected").val();
        // if(selectedFeeTypeVal){
        //     var selectedFeeTypeTxt = $('#fee_type').children("option:selected").text();
        //     alert("You have selected the selectedFeeTypeTxt - " + selectedFeeTypeTxt);
        //     $('#dynamicCol').val(selectedFeeTypeTxt);
        // }
        
    });


    $('#btn-reset').click(function(){ //button reset event click
        $('#student_id').select2("val", "0");
        $('#parent_id').select2("val", "0");
        $('#cls_sec_id').prop('selectedIndex',0);
        $('#status').prop('selectedIndex',1);
        table.ajax.reload();  //just reload table

    });

   $("#parent_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=students_defaulters_list&m=get_parentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term:term,
            }
        },
       processResults: function (response) {
        console.log(response);
              return {
                 results: response
              };
           },
           cache: true
    }
 });
 //$("#cls_sec_id").select2({minimumInputLength: 2});
 $("#student_id").select2({
    minimumInputLength: 2,
    tags: [],
    ajax: {
        url: 'admin.php?c=students_defaulters_list&m=get_studentinfo', 
        dataType: 'json',
        type: "POST",
        quietMillis: 50,
        data: function (term) {
            return {
                term: term,
                status:<?php echo $_GET['status']  ?>
            }
        },
       processResults: function (response) {
        console.log(response);
              return {
                 results: response
              };
           },
           cache: true
    }
});
</script>
<?= $this->endSection() ?>