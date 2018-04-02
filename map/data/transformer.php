<?php

header('Content-type: text/plain; charset=utf-8');
require_once("conn.php");

if(!isset($_GET["file"]))
	die("out");

$json = file_get_contents($_GET["file"]);
$json_data = json_decode($json,true);

$json_data["type"] = "FeatureCollection";

foreach($json_data["points"] as $key => $point) {
	$sql = "SELECT id, nome, ST_X((ST_Dump(ponto)).geom) AS lng , ST_Y((ST_Dump(ponto)).geom) AS lat FROM localidades
			WHERE id = ". $point['place_id'];
	$res = pg_query($sql);
	$data = pg_fetch_array($res, 0, PGSQL_ASSOC);
	
	$json_data["features"][$key]["type"] = "Feature";
	$json_data["features"][$key]["properties"] = $point;	
	$json_data["features"][$key]["properties"]['place'] = $data['nome'];
	
	
	$json_data["features"][$key]["geometry"]["type"] = "Point";
	$json_data["features"][$key]["geometry"]["coordinates"] = array($data['lng'], $data['lat']);
}


foreach($json_data["travels"] as $key => $travel) {
	$prev_stop = "";
	
	foreach($travel['itinerary'] as $j => $stop) {
		$new_key = count($json_data["features"]);	
		
		$sql = "SELECT id, nome, ST_X((ST_Dump(ponto)).geom) AS lng , ST_Y((ST_Dump(ponto)).geom) AS lat FROM localidades
				WHERE id = ". $stop;
		$res = pg_query($sql);
		$data = pg_fetch_array($res, 0, PGSQL_ASSOC);
		
		if(!empty($prev_stop)) {			
			$json_data["features"][$new_key]["type"] = "Feature";
			$json_data["features"][$new_key]["properties"] = $travel;
			$json_data["features"][$new_key]["properties"]["stretch"] = "trecho ". ($j);
			
			$json_data["features"][$new_key]["geometry"]["type"] = "LineString";
			$json_data["features"][$new_key]["geometry"]["coordinates"] = array($prev_stop, array($data['lng'], $data['lat']));

		}		
		$prev_stop = array($data['lng'], $data['lat']);
	}
}

unset($json_data["points"]);
unset($json_data["travels"]);

$json = json_encode($json_data, JSON_NUMERIC_CHECK);

echo $json;
die;
?>