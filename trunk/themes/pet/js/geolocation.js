function getLocation() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(showPosition);
	} else {
		console.log("Geolocation is not supported by this browser.");
	}
}

function showPosition(position) {
	var sendInfo = {
		latitude : position.coords.latitude,
		longitude : position.coords.longitude,
		url : window.location.href,
	};

	$.ajax({
		type : "POST",
		url : window.location.href + "../../../pet/saveLocation",
		dataType : "json",
		data : sendInfo,
		success : function(result) {
			console.log('success');
			console.log(result.url);
			console.log(result.code);
			console.log(result.latitude);
			console.log(result.longitude);
		},
		error : function(e) {
			console.log('error');
		}
	});
}