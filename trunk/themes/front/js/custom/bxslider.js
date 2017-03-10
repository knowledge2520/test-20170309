var Slider = function () {

    var initSlider = function () {
        $('.bxslider').bxSlider({
            mode: 'fade',
            auto: true,
            controls: true
        });
    }
    return {

        //main function to initiate the module
        init: function () {
            initSlider();
        }

    };

}();