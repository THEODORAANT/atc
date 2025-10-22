if (typeof(ATC) == 'undefined') {
	ATC	= {};
	ATC.UI	= {};
}

ATC.UI.Maps = function()
{
	var init = function() {
		
		var map = L.map('map').setView([51.505, -0.09], 5);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		    maxZoom: 18
		}).addTo(map);

		var markers = L.markerClusterGroup({
			maxClusterRadius: 40
		});
		
		for (var i = 0; i < addressPoints.length; i++) {
			var a = addressPoints[i];
			var title = a[2]+' '+a[3];
			var marker = L.marker(L.latLng(a[0], a[1]), { title: title });
			marker.bindPopup(title);
			markers.addLayer(marker);
		}

		map.addLayer(markers);


	};
	
	return {
		init: init
	};
	
}();

jQuery(function($) { ATC.UI.Maps.init(); });
