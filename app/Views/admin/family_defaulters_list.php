<?php $uiNeedsDataTables = true; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?php 
  $status = ''; 
  if(!empty($_GET['status'])){
   $status = $_GET['status']; 
  }
?>
<link rel="stylesheet" href="<?php echo base_url();?>resource/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css" />
  <?= view('components/page_header', [
    'title' => 'Defaulter Family List',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Defaulter Family List', 'active' => true],
    ],
]) ?>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-lg-12">
          <div class="card card-primary card-outline card-tabs">
          <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs"> 
            <li class="nav-item"><a class="nav-link active"  href="<?php echo '#/family_defaulters_list?status=1';?>">Defaulter Family List</a></li>    
        <li  class="nav-item" ><a  class="nav-link" href="<?php echo '#/students_defaulters_list?status=1';?>">  Current Defaulters </a></li>
        <li class="nav-item"><a class="nav-link <?php if($status == 3){ ?> active <?php } ?>" href="<?php echo '#/students_defaulters_list?status=3';?>">  Dropped Defaulters</a></li>
        
      </ul>
          <div class="card-body">
          <div class="row">
              <div class="col-lg-12">
                <form id="form-filter" class="d-flex flex-wrap align-items-center">
                <div class="col-lg-2">
                    <select class="form-control select2" name="student_id" id="student_id" style="height: 24px;width: 100%;">
                       <option value="0">Select Student</option>   
                    </select>
                </div>
                <div class="col-lg-2">
                  <select class="form-control select2" name="parent_id" id="parent_id" style="height: 24px;width: 100%;" >
                    <option value="0">Select Parent</option>   
                  </select>
                </div>
               <div class="col-lg-2">
                    <?php $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');?>
                    <select class="form-control select2" id="month" name="month">
                        <option value="">Select Fee Month</option>
                        <?php
                            foreach ($months as $num => $name) {
                                printf('<option value="%u">%s</option>', $num, $name);
                            }
                        ?>
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-control select2" id="fee_type" name="fee_type" style="max-width: 100%;">
                        <option value="">Select Fee Type</option>
                        <?php
                            foreach ($fee_types as $value) {
                                printf('<option value="%u">%s</option>', $value->fee_type_id, $value->fee_type_name);
                            }
                        ?>
                    </select>
                </div>
                <div class="col-lg-3">
                  <button type="button" id="btn-filter" style="float:left;line-height:12px;height: 24px;" class="btn btn-primary">Filter</button>
                  <button type="button" id="btn-reset"  style="float:left;line-height:12px;height: 24px;" class="btn btn-secondary">Reset</button>
                </div>
                </form>
    </div>
  </div>
  <br>
    <table class="table table-striped table-bordered table-hover" id="students-datatable" width="100%" style="font-size:12px;">
        <thead>
            <tr>
                <th nowrap>#</th>
                <th style="width:170px;">Student</th>
                <th nowrap>Father</th>
                <th nowrap>Father Contact </th>
                <th nowrap>Mother Contacts </th>
                <th>Current Month<?php //echo date('M Y'); ?></th>
                <th >Previous Balance</th>
                <th>Total</th>
                <th style="width:80px;">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
         <tfoot>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th id="total"></th>
            <th></th>
            
         </tr>
        </tfoot>
    </table>
</div></div>
</div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <style type="text/css">
        table.table-bordered th:last-child, table.table-bordered td:last-child{width: 50px;}
    </style>
    <!-- /.content -->
<script type="text/javascript">
    $(function(){

    var table = $('#students-datatable').DataTable({
        dom: 'Bfrtip',
        buttons: ['colvis', 'csv', 'excel', 'print', 'copy', {
            extend: 'pdfHtml5',
            exportOptions: { columns: [1,2,3,4,5,6,7] }
        }],
       processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 200,
        searching: false,
        ajax: {
            url: '<?= base_url('admin/family_defaulters_list/data') ?>',
            type: 'post',
            data: function(d){
               d.status = $('#status').val();
                d.student_id = $('#student_id').val();
                d.parent_id = $('#parent_id').val();
                d.fee_type = $('#fee_type').val();
                d.month = $('#month').val();
            }
        },
        columns:[
            { data: 'id', className: 'select-checkbox' },
            { data: 'name' },
            { data: 'f_name' },
            { data: 'f_contacts' },
            { data: 'm_contacts' },
           // { data:'unpaid_month' },
          //  { data:'previous_balace' },
          //  { data:'total_payable' },
            
            { 
                data: 'unpaid_month',
                render: function (data, type, row) {
                    return parseFloat(data).toFixed(row.decimal_places) + ' ' + row.currency_code;
                }
            },
            { 
                data: 'previous_balace',
                render: function (data, type, row) {
                    return parseFloat(data).toFixed(row.decimal_places) + ' ' + row.currency_code;
                }
            },
    

           { 
                data: 'total_payable',
                render: function (data, type, row) {
                    return parseFloat(data).toFixed(row.decimal_places) + ' ' + row.currency_code;
                }
            },

        {
                data: 'parent_id',
                render: function (data, type, row) {
                    return "<button class='btn btn-success btn-sm pay-all-fee' data-parentid='" + row.parent_id + "'>Pay All</button>";
                }
            }

        ],
        footerCallback: function (row, data, start, end, display) {
        if (data.length > 0) {
            const api = this.api();
            const currencyCode = data[0].currency_code;
            const decimalPlaces = data[0].decimal_places;

            const sumColumn = (colIndex) => api.column(colIndex).data()
                .reduce((a, b) => parseFloat(a) + parseFloat(b), 0);

            const totalCurrent = sumColumn(5);
            const totalPrevious = sumColumn(6);
            const totalPayable = sumColumn(7);

            $(api.column(5).footer()).html(totalCurrent.toFixed(decimalPlaces) + ' ' + currencyCode);
            $(api.column(6).footer()).html(totalPrevious.toFixed(decimalPlaces) + ' ' + currencyCode);
            $(api.column(7).footer()).html(totalPayable.toFixed(decimalPlaces) + ' ' + currencyCode);
        }
    }
    });

    $('#btn-filter').click(function(){
        table.ajax.reload();
    });

    $('#btn-reset').click(function(){
        $('#student_id').select2("val", "0");
        $('#parent_id').select2("val", "0");
        $('#cls_sec_id').prop('selectedIndex',0);
        $('#status').prop('selectedIndex',1);
        table.ajax.reload();
    });

    // ✅ Event delegation for dynamically generated "Pay All" buttons
    $('#students-datatable tbody').on('click', '.pay-all-fee', function () {
        if(confirm('Are you sure you want to pay all pending fees for this parent?')) {
            var parentID = $(this).data('parentid');
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;

            $.ajax({
                url: 'admin.php?c=family_defaulters_list&m=payFeeAll',
                type: 'POST',
                data: {
                    parent_id: parentID,
                    datePaid: formattedDate
                },
                success: function(res) {
                    toastr.success('All fees paid successfully');
                    $('#students-datatable').DataTable().ajax.reload(null, false); // Reload without changing page
                },
                error: function() {
                    toastr.error('Payment failed. Please try again.');
                }
            });
        }
    });

    // Select2 setup for Parent and Student
    $("#parent_id").select2({
        minimumInputLength: 2,
        ajax: {
            url: 'admin.php?c=students&m=get_parentinfo',
            dataType: 'json',
            type: "POST",
            data: function (term) { return { term: term }; },
            processResults: function (response) {
                return { results: response };
            },
            cache: true
        }
    });

    $("#student_id").select2({
        minimumInputLength: 2,
        ajax: {
            url: 'admin.php?c=students&m=get_studentinfo',
            dataType: 'json',
            type: "POST",
            data: function (term) { return { term: term, status: 1 }; },
            processResults: function (response) {
                return { results: response };
            },
            cache: true 
        }
    });

    $("#cls_sec_id").select2({ minimumInputLength: 2 });
});

// $(function(){
//     var table = $('#students-datatable').DataTable({
//       dom: 'Bfrtip',
//         buttons: [
//         'colvis', 'csv', 'excel','print','copy',
//          {
//                 extend: 'pdfHtml5',
//                 exportOptions: {
//                     columns: [ 1,2,3,4,5,6,7 ]
//                 }
//             },
//         ],
//         deferRender: true,
//         select:{
//             style:'single',
//             blurable: true
//         },
        
//     "processing": true, //Feature control the processing indicator.
//     "serverSide": true, //Feature control DataTables' server-side processing mode.
//     "ordering": false,
//     "order": [], //Initial no order.
//         "pageLength": 200,
//          "searching": false,
//         ajax:{

//             type:'post',

//             data:function(d){
//                 d.status = $('#status').val();
//                 d.student_id = $('#student_id').val();
//                 d.cls_sec_id = $('#cls_sec_id').val();
//                 d.parent_id = $('#parent_id').val();
//                 d.fee_type = $('#fee_type').val();
//                 d.month = $('#month').val();
//             }
//         },
//         columns:[
//             {
//                 data:'id',
//                 className:'select-checkbox',
//                 render:function(data, type, row){
//                     return data;
//                 }
//             },
//             {data:'name'},  
//             {data:'f_name'},
//             {data:'f_contacts'},
//             {data:'m_contacts'},
//             {data:'unpaid_month'},
//             {data:'previous_balace'},
//             {data:'total_payable'},
//             {
//                 data:'parent_id',
//                 render:function(data, type, row){
//                     return "<button class='btn btn-success btn-sm pay-all-fee' data-parentid='" + row.parent_id + "'>Pay All</button>";
//                 }
//             },
//         ],
//         footerCallback: function ( row, data, start, end, display ) {
   
   
//       if(data != ''){
//             var api = this.api();
//             // Remove the formatting to get integer data for summation
//             var intVal = function ( i ) {
//                 return typeof i === 'string' ?
//                     i.replace(/[\$,]/g, '')*1 :
//                     typeof i === 'number' ?
//                         i : 0;
//             };    
//         // Total over all pages
//             var total = api
//                 .column( 5 )
//                 .data()
//                 .reduce( function (a, b) {
//                     return intVal(a) + intVal(b);
//                 });    
//       // Total over all pages
//             var total2 = api
//                 .column( 6 )
//                 .data()
//                 .reduce( function (a, b) {
//                     return intVal(a) + intVal(b);
//                 });
//       // Total over all pages
//             var total3 = api
//                 .column( 7 )
//                 .data()
//                 .reduce( function (a, b) {
//                     return intVal(a) + intVal(b);
//                 } );
     
           
//    // Update footer
//             $( api.column(5).footer() ).html(
//                 Number(total).toFixed(2)
//             );
//       // Update footer
//             $( api.column(6).footer() ).html(
//                 Number(total2).toFixed(2)
//             );
//       // Update footer
//             $( api.column(7).footer() ).html(
//                  Number(total3).toFixed(2)
//             );
//         }
//         }  


//     });

//      $('#btn-filter').click(function(){ //button filter event click
//         table.ajax.reload();  //just reload table
//     });


//     $('#btn-reset').click(function(){ //button reset event click
//         $('#student_id').select2("val", "0");
//         $('#parent_id').select2("val", "0");
//         $('#cls_sec_id').prop('selectedIndex',0);
//         $('#status').prop('selectedIndex',1);
//         table.ajax.reload();  //just reload table

//     });

//      $('#payAllFee').click(function(){
//              if(confirm('Are you sure you want to update this?')){
//                 var parentID = $(this).data('parentid');
//                 const today = new Date();
//                 const yyyy = today.getFullYear();
//                 const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
//                 const dd = String(today.getDate()).padStart(2, '0');

//                 const formattedDate = `${yyyy}-${mm}-${dd}`;
//                 var datePaid = formattedDate;
                
//                  $.ajax({
//                     url: 'admin.php?c=fee_chalan_pay&m=payFeeAll',
//                     type: 'POST',
//                     data:{parent_id: parentID,datePaid:datePaid},
//                     success:function(res){
//                      $('#feetypeinfo').html('All Fee Paid Successfully');
//                      toastr.success('Updated Successfully');
//                     }
//                  });

//                 $('#updatediscount').modal('hide');
//             }else{
//                 return false;
//             }
//         }); 

//    $("#cls_sec_id").select2({minimumInputLength: 2});
//    $("#parent_id").select2({
//     minimumInputLength: 2,
//     tags: [],
//     ajax: {
//         url: 'admin.php?c=students&m=get_parentinfo', 
//         dataType: 'json',
//         type: "POST",
//         quietMillis: 50,
//         data: function (term) {
//             return {
//                 term:term,
//             }
//         },
//        processResults: function (response) {
//         console.log(response);
//               return {
//                  results: response
//               };
//            },
//            cache: true
//     }
//  });

//  $("#student_id").select2({
//     minimumInputLength: 2,
//     tags: [],
//     ajax: {
//         url: 'admin.php?c=students&m=get_studentinfo', 
//         dataType: 'json',
//         type: "POST",
//         quietMillis: 50,
//         data: function (term) {
//             return {
//                 term: term,
//                 status:1
//             }
//         },
//        processResults: function (response) {
//         console.log(response);
//               return {
//                  results: response
//               };
//            },
//            cache: true
//     }
//  });  

// });

</script>

<?= $this->endSection() ?>