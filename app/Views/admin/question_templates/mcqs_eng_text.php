<table id="myTable" class=" table order-list">
    <thead>
        <tr>
	       <td><div style="text-align: center;font-weight: bold;">Questions</div></td>
         <td><div style="text-align: center;font-weight: bold;">Hint</div></td>
        </tr>
    </thead>
    <tbody>
        <tr>
		    <td style="border: 4px solid blue; border-end: 0 none !important;">
          <input type="hidden" name="optionscount[]" value="1" />
	            <textarea rows="3" class="form-control editor" name="question_text0" placeholder="Question" id="question_text" style="margin-bottom: 4px;"></textarea>
         <div class="col-sm-6"><input type="text" name="option_text00" id="option_text0" class="form-control" placeholder="Correct Option" style="margin-bottom: 5px;" /></div> 
         <div class="col-sm-6"><input type="text" name="option_text10" id="option_text1" class="form-control" placeholder="Alternate Option 1" style="margin-bottom: 5px;" /></div>     
         </td>     
         <td  style="border: 4px solid blue;border-start: 0 none !important;border-end: 0 none !important;"> <textarea class="form-control editor" name="hint_text0" placeholder="Hint" rows="3" id="hint_text"></textarea>
         <div class="col-sm-6"> <input type="text" name="option_text20" id="option_text3" class="form-control" placeholder="Alternate Option 2" style="margin-bottom: 5px;" /></div>
         <div class="col-sm-6"><input type="text" name="option_text30" id="option_text4" class="form-control" placeholder="Alternate Option 3" style="margin-bottom: 5px;" /></div>  
         </td>
         <td style="border: 4px solid blue; border-start: 0 none !important;"><a class="deleteRow"></a></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
        <td colspan="5" style="text-align: left;">
              <input type="button" class="btn btn-lg w-100 btn-primary"  id="addrow" value="Add Question" />
         </td>
        </tr>
        <tr>
        </tr>
    </tfoot>
</table>
<script type="text/javascript">
    $(document).ready(function () {
    var counter = 1;
    $("#addrow").on("click", function () {
        var newRow = $("<tr>");
        var cols = "";
        cols += '<td style="border: 4px solid blue; border-end: 0 none !important;"><input type="hidden" name="optionscount[]" value="1" /> <textarea class="form-control editor2" name="question_text'+ counter +'" placeholder="Question" id="question_text" style="margin-bottom: 4px;"></textarea><div class="col-sm-6"><input type="text" name="option_text0'+ counter +'" id="option_text0" class="form-control" placeholder="Correct Option" style="margin-bottom: 5px;" /></div><div class="col-sm-6"><input type="text" name="option_text1'+ counter +'" id="option_text1" class="form-control" placeholder="Alternate Option 1" style="margin-bottom: 5px;" /></div></td><td style="border: 4px solid blue; border-end: 0 none !important;border-start: 0 none !important;"><textarea class="form-control editor2" name="hint_text'+ counter +'" placeholder="Hint" id="hint_text"></textarea><div class="col-sm-6"><input type="text" name="option_text2'+ counter +'" id="option_text2" class="form-control" placeholder="Alternate Option 2" style="margin-bottom: 5px;" /></div><div class="col-sm-6"><input type="text" name="option_text3'+ counter +'" id="option_text3" class="form-control" placeholder="Alternate Option 3" style="margin-bottom: 5px;" /></div></td>';
       
        cols += '<td style="border: 4px solid blue; border-start: 0 none !important;"><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
        newRow.append(cols);
        $("table.order-list").append(newRow);
        
        counter++;
});
$("table.order-list").on("click", ".ibtnDel", function (event) {
        $(this).closest("tr").remove();       
        counter -= 1
    });
});
</script>