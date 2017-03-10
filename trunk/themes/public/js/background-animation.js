var Animation = function () {

    var initAnimation = function () {
        var x = 0;
        var y = 0;
        //cache a reference to the banner
        var banner = $("#parallax");

        // set initial banner background position
        banner.css('backgroundPosition', x + 'px' + ' ' + y + 'px');
        var i =0;
        // scroll up background position every 90 milliseconds
        var timer = window.setInterval(function() {
            banner.css("backgroundPosition", x + 'px' + ' ' + y + 'px');
            y--;
            //x--;
            if(y === -100) clearInterval(timer);
            //if you need to scroll image horizontally -
            // uncomment x and comment y

        }, 50);

        //var scrollSpeed = 90;
        //
        //// set the default position
        //var current = 0;
        //
        //// set the direction
        //var direction = 'h';
        //
        //function bgscroll(){
        //
        //    // 1 pixel row at a time
        //    current -= 1;
        //
        //    // move the background with backgrond-position css properties
        //    $('#parallax').css("backgroundPosition", (direction == 'h') ? current+"px 0" : "0 " + current+"px");
        //
        //}

        //Calls the scrolling function repeatedly
        //setInterval(bgscroll, scrollSpeed);

    }
    return {

        //main function to initiate the module
        init: function () {
            initAnimation();
        }

    };

}();