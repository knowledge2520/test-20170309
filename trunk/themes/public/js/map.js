var Map = function () {

    var initMap = function () {
        function initialize() {
            var mapCanvas = document.getElementById('map-canvas');
            var myLatlng = new google.maps.LatLng(10.800934, 106.648085);
            var mapOptions = {
                scrollwheel: false,
                center: myLatlng,
                zoom: 16,
                mapTypeId: google.maps.MapTypeId.ROADMAP

            }
            var map = new google.maps.Map(mapCanvas, mapOptions);
            var marker = new google.maps.Marker({
                position: myLatlng,
                map: map,
                title: 'Location'
            });

        }
        google.maps.event.addDomListener(window, 'resize', initialize);
        google.maps.event.addDomListener(window, 'load', initialize)
    }
    return {

        //main function to initiate the module
        init: function () {
            initMap();
        }

    };

}();
