$(document).ready(function(){
    $("#submit").click(function(){
        var name = $("#name").val();
        var body = $("#body").val();
        var email = $("#email").val();
        var subject = $("#subject").val();
        var base_url = document.getElementById("executeListValue").value;
        if(trim(name) == "" ||
            trim(email) == "" ||
            trim(subject) == "" ||
            trim(body) == "") {
            alert("Please complete all fields");
        } else {
            if(!isEmail(email)) {
                alert("Email appears to be invalid\nPlease check and try again");
                $("#email").focus();
                document.getElementById("email").select();
            } else {
                document.getElementById("submit").disabled=true;
                document.getElementById("submit").value='Please Wait..';
                $.ajax({
                    type: "POST",
                    url: "",
                    data: $("#cmtForm").serialize(),
                    success: function(data) {
                        try {
                            document.getElementById("cmtForm").innerHTML = "<h1><strong>Thank you for contact!</strong></h1>";
                        }
                        catch(e) {
                            alert('Exception while request..');
                            document.getElementById("submit").disabled=false;
                            document.getElementById("submit").value='Send';
                        }
                    },
                    error: function(data){
                        alert('Error while request...');
                        document.getElementById("submit").disabled=false;
                        document.getElementById("submit").value='Send';
                    }
                });

            }
        }
    }) ;
});

function trim(a) {
    return a.replace(/^s*(S*(s+S+)*)s*$/, "$1");
}

function isEmail(a) {
    return (a.indexOf(".") > 0) && (a.indexOf("@") > 0);
}