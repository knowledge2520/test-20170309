var Map = function () {

    var initMap = function () {
        function initialize() {
            var mapCanvas = document.getElementById('map-canvas');
            var myLatlng = new google.maps.LatLng(1.2978722, 103.7877188);
            var mapOptions = {
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
        google.maps.event.addDomListener(window, 'load', initialize);
    }
    return {

        //main function to initiate the module
        init: function () {
            initMap();
        }

    };

}();
