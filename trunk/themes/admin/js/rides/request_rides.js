var RequestRides = function () {

    var initListPage = function () {
    	$('.check-all').click(function(event) {  //on click 
            if(this.checked) { // check select status
                $('.checkbox-delete').each(function() { //loop through each checkbox
                	$(this).prop('checked',true);  //select all checkboxes with class "checkbox1"
                    $(this).parent().addClass('checked');  //select all checkboxes with class "checkbox1"
                });
            }else{
                $('.checkbox-delete').each(function() { //loop through each checkbox
                	$(this).prop('checked',false); //deselect all checkboxes with class "checkbox1"            
                    $(this).parent().removeClass('checked');  //select all checkboxes with class "checkbox1"
                });         
            }
        });
    	$('#dataTables_length').change(function(){    		
    		$('#form_listRecords').submit();
    	});
    }
    return {

        //main function to initiate the module
        init: function () {
        	initListPage();
        }

    };

}();