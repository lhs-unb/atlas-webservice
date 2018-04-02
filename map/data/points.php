<?php
header('Content-type: text/plain; charset=utf-8');
require_once("../../safe.php");

if(!isset($_GET["point"]))
	die("out");

$point = $_GET["point"];

$json_data["type"] = "FeatureCollection";


$sql = "SELECT id, nome, ST_X((ST_Dump(ponto)).geom) AS lng , ST_Y((ST_Dump(ponto)).geom) AS lat, observacao as obs, ano_inicio as ano
			FROM localidades
			WHERE id = ". $point;
$res = pg_query($sql);

$data = pg_fetch_array($res, null, PGSQL_ASSOC);

if(!empty($data)) {	
	$json_data["features"][0]["type"] = "Feature";
	$json_data["features"][0]["properties"]['id'] = $point;	
	$json_data["features"][0]["properties"]['place'] = $data['nome'];
	$json_data["features"][0]["properties"]['obs'] = $data['obs'];
	$json_data["features"][0]["properties"]['ano'] = $data['ano'];


	$json_data["features"][0]["geometry"]["type"] = "Point";
	$json_data["features"][0]["geometry"]["coordinates"] = array($data['lng'], $data['lat']);

	$json = json_encode($json_data, JSON_NUMERIC_CHECK);

	echo $json;
}
else {
	echo "null";
}
die;
?>