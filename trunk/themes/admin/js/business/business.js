var Business = function () {

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
    		$('#form_searchResult').submit();
    	});
    	
    	//confirm button delete
//    	$('input[name="btn_delete"]').on('click', function(e){
//    	    var $form=$(this).closest('form'); 
//    	    e.preventDefault();
//    	    $('#confirm').modal({ backdrop: 'static', keyboard: false })
//    	        .one('click', '#delete', function() {
//    	        	console.log($form)
//    	            $form.trigger('submit'); // submit the form
//    	        });
//    	        // (one. is not a typo of on.)
//    	});
    	
    }
    return {

        //main function to initiate the module
        init: function () {
        	initListPage();
        }

    };

}();