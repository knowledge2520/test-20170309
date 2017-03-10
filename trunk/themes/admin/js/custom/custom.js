var Custom = function () {

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
        
        $('#my_listing').click(function(){    		
    		$('#form_searchResult').submit();
    	}); 
        $('.default-date-picker').datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: true,
            todayBtn:true,
            autoclose:true
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

function set_status(id, url) {
    var status = document.getElementById("status_" +id).value;
    console.log(url);
    //form_searchResult
    $("#form_listRecords").submit(function (e) {
        e.preventDefault(); //STOP default action
        var postData = $(this).serializeArray();

        var postForm = {//Fetch form data
            id: id, //Store name fields value
            status: status,
            url: url,
            set_status: 1,
        };
        var formURL = $(this).attr("action");
        $.ajax(
        {
            url: formURL,
            type: 'POST',
            data: postForm,
            dataType: 'json',
            success: function (response)
            {
                console.log(response);
                $('#dialog').modal('show');
                if(response && response.redirect){
                    html = '<a href="'+window.location.href +'" class="btn default" id="modal-btn-close">Close</a>';
                    $("#modal-footer").html(html);
                    // window.location.href = url;
                }
                //alert('Successful!'); 
                //window.location.href = url;
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                //if fails      
            }
        });
        
        //e.unbind(); //unbind. to stop multiple form submit.
    });

    $("#form_listRecords").submit(); //Submit  the FORM

}