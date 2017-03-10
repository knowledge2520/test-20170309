var Popup = function () {

    var initPopup = function () {
        $( document ).on( "pagecreate", function() {
            $( ".photopopup" ).on({
                popupbeforeposition: function() {
                    var maxHeight = $( window ).height() - 60 + "px";
                    $( ".photopopup img" ).css( "max-height", maxHeight );
                }
            });
        });
    }
    return {

        //main function to initiate the module
        init: function () {
            initPopup();
        }

    };

}();