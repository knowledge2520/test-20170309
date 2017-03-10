
jQuery(document).ready(function() {    
    $("#topic-category").on("change",function(e){
        var type = $('#topic-category').val();
        if(type == 'newsfeed_post'){
       		$("#topic-title").hide();
        }else{
        	$("#topic-title").show();
        }
    });

    $("#topic-category").trigger("change");  
});
