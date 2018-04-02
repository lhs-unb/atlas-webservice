<?php

if(isset($_GET['point']))
	$point =  $_GET['point'];
else
	die("cade o ponto?");

//parameters
$marker = (isset($_GET['marker'])) ? $_GET['marker'] : 'point';
$height = (isset($_GET['height'])) ? $_GET['height'] : '550';
$width = (isset($_GET['width'])) ? $_GET['width'] : '600';
$zoom = (isset($_GET['zoom'])) ? $_GET['zoom'] : '4';


?>
<!doctype html>
<html lang="pt">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">

    <link rel="stylesheet" href="leaflet/leaflet.css" />
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="leaflet/leaflet.js"></script>
    <script src="leaflet/leaflet.ajax.min.js"></script>  
    <script src="js/stilo.js"></script>  
</head>
<body>
<div class="splash-container"> <!-- id="map" style="top: 5em; height: 77%">-->
    <div id="map" style="width:<?php echo $width; ?>px; height: <?php echo $height; ?>px"></div>
</div>

<script>
	var map = L.map('map', {
		center: [-16, -58],
		zoom: <?php echo $zoom; ?>
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

	L.Control.Watermark = L.Control.extend({
		onAdd: function(map) {
			var img = L.DomUtil.create('img');
			
			img.src = 'images/atlas-logo.png';
			img.style.width = '160px';
			
			return img;
		},
		
		onRemove: function(map) {
			// Nothing to do here
		}
	});

	L.control.watermark = function(opts) {
		return new L.Control.Watermark(opts);
	}
	
	L.control.watermark({ position: 'topright' }).addTo(map);
	
	
	
	geojson = new L.GeoJSON.AJAX("data/points.php?point=<?php echo $_GET["point"]; ?>", {
		pointToLayer: function (feature, latlng) {
			return L.circleMarker(latlng, <?php echo $marker; ?>);
		},
		onEachFeature: function(feature, layer) {
			console.log(feature.properties)
			if (feature.properties.place) {
				console.log(feature.properties)
				if(feature.properties.description)
					desc = feature.properties.description;
				else
					desc = "";
					
				layer.bindPopup(feature.properties.ano +"<br><strong>" + feature.properties.place +"</strong><br>" + feature.properties.obs);
			}
		}
	});
	
	geojson.addTo(map);
			
</script>


</body>
</html>
