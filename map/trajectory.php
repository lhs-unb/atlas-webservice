<!doctype html>
<html lang="pt">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">

    <link rel="stylesheet" href="leaflet/leaflet.css" />
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="leaflet/leaflet.js"></script>
    <script src="leaflet/leaflet.ajax.min.js"></script>  
</head>
<body>
<div class="splash-container"> <!-- id="map" style="top: 5em; height: 77%">-->
    <div id="map" style="width:600px; height: 550px"></div>
</div>

<script>
	var map = L.map('map', {
		center: [-5, -20],
		zoom: 2
	});

	// Basemaps
	// For the entire list, see https://leaflet-extras.github.io/leaflet-providers/preview/

	var Esri_WorldPhysical = L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Physical_Map/MapServer/tile/{z}/{y}/{x}', {
		attribution: 'Tiles &copy; Esri &mdash; Source: US National Park Service',
		maxZoom: 8
	});

	var Hydda_Base = L.tileLayer('http://{s}.tile.openstreetmap.se/hydda/base/{z}/{x}/{y}.png', {
		attribution: 'Tiles courtesy of <a href="http://openstreetmap.se/" target="_blank">OpenStreetMap Sweden</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	});

	var Esri_WorldTerrain = L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Terrain_Base/MapServer/tile/{z}/{y}/{x}', {
		attribution: 'Tiles &copy; Esri &mdash; Source: USGS, Esri, TANA, DeLorme, and NPS',
		maxZoom: 8
	});


	Hydda_Base.addTo(map);

	// Style
	var birth = {
		radius: 4,
		fillColor: "#fff",
		color: "#000",
		weight: 1.5,
		fillOpacity: 0.1
	};
	var education = {
		radius: 4,
		fillColor: "#0033cc",
		color: "#000",
		weight: 1,
		fillOpacity: 0.1
	};
	var post = {
		radius: 4,
		fillColor: "#cc0000",
		color: "#000",
		weight: 1,
		fillOpacity: 0.1
	};
	var death = {
		radius: 4,
		fillColor: "#000",
		color: "#fff",
		weight: 1,
		fillOpacity: 0.1
	};
	var travel = {
		fillColor: "#000",
		color: "#fff",
		weight: 1,
		strokeOpacity: 1
	};
	
	geojson = new L.GeoJSON.AJAX("data/transformer.php?file=<?php echo $_GET["file"]; ?>", {
		pointToLayer: function (feature, latlng) {
				console.log(feature.properties)
			if(feature.properties.type == 'Nascimento')		
				return L.circleMarker(latlng, birth);
			if(feature.properties.type == 'Estudo')		
				return L.circleMarker(latlng, education);
			if(feature.properties.type == 'Posto')		
				return L.circleMarker(latlng, post);
			if(feature.properties.type == 'Morte')		
				return L.circleMarker(latlng, death);
			if(feature.properties.type == 'Viagem') {
				return L.circleMarker(latlng, travel);
			}
		},
		onEachFeature: function(feature, layer) {
			// binding popup
			if (feature.properties.place) {
				if(feature.properties.description)
					desc = feature.properties.description;
				else
					desc = "";
					
				layer.bindPopup(feature.properties.date +"<br><strong>" + feature.properties.place +"</strong><br>" + feature.properties.type + "<br>" + desc);
			}
		}
	});
	
	geojson.addTo(map);
			
</script>


</body>
</html>
