function fetch_select(val)
{
    var url = $('#url-get-type').val();
    $.ajax({
     type: 'post',
     url: url,
     data: {
       'type':val
     },
     dataType: 'json',
     success: function (response) {
        // console.log(response); 
        var text = "";
        var x;
        var i = 0;
        var name = "breed";
        if(response.type == 'Others'){         
            $("#pet-breed").attr("name", "");   
            $("#pet-breed-other").attr("name", name);
        }else{
            for (x in response.data) {
                if(i == 0){
                    text += "<option selected value="+x+">"+response.data[x]+"</option>";
                }else{
                    text += "<option value="+x+">"+response.data[x]+"</option>";
                }
                
                i++;
            }
            $("#pet-breed-other").attr("name", "");
            $("#pet-breed").html(text).attr("name", name);

            $("#pet-breed").select2('destroy');
            $("#pet-breed").select2({ 
                maximumSelectionSize: 1
            });
        }

//              text = '<select class="form-control" id="childcare_type" name="'+name+'">' + text + '</select>';
//                 document.getElementById("childcare_type").outerHTML=text; 
     }
   });
}

$(document).ready(function(){   
    $("#pet-type").on("change", function(e) {
        var val = $(this).val();
        var url = $('#url-get-type').val();

        if (val == 'Others') {
            //$("#childcare_type").attr("name","location");
            $("#pet-breed-selected").hide();
            $("#pet-breed-input-text").show();
        } else {
            //$("#childcare_type").attr("name","supportServiceType");
            $("#pet-breed-input-text").hide();
            $("#pet-breed-selected").show();
        }

        fetch_select(val);
    });    
    $("#pet-type").trigger("change");  

    $("#pet-breed").select2({ maximumSelectionSize: 1});


    $(".form_datetime").datetimepicker({
        format: 'dd/mm/yyyy hh:ii:ss',
        autoclose: true,
        todayBtn: true,
        minuteStep: 5,
        todayHighlight: true,
        showMeridian: true
    });
});