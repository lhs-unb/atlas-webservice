<?php

header('Content-type: text/plain; charset=utf-8');
require_once("safe.php");


if(isset($_GET['help']) || !isset($_GET['class']) || !isset($_GET['type'])) {
	?>
# Digital Atlas of Portuguese America
@ http://lhs.unb.br/atlas

This script offers a direct access to the geographic data and metadata produced, 
stored, and published by the Digital Atlas of Portuguese America, based on the 
categories under which geographic features are stored.


== Parameters ==

= Mandatory
type (text, within options) - describes the type of geographic feature - point, 
line, polygon - you want to access and the possible values are “ponto”, “linha”, 
“poligono” (in Portuguese). 

class (integer, comma-separated) - refers to the categories that are attached 
to a particular feature. For a comprehensive list of these categories, see 
http://lhs.unb.br/webservice/categories.php?key=XXXXXXXXXX. Users can 
provides more than one category using comma-separated values (like 185,195,132).


== Example ==

http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']; ?>?type=ponto&class=185,186


== Changelog ==

13/11/2017
- Removed authentication system
- Created validation for data without geography feature (will keep the data, but include a "blank" geometry)


== TODOs ==

- Allow optional filter based on dates
<?php
	die;
}

function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
  $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
  $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
  $result = str_replace($escapers, $replacements, $value);
  return $result;
}

//$ano = $_GET['ano'];
$class = $_GET['class'];
$type = $_GET['type'];

if(!in_array($type, array('linha','ponto','poligono')))
	die("Invalid geometry type");

# Build SQL SELECT statement and return the geometry as a GeoJSON element in EPSG: 4326
$sql = "
	select nome, codigo, inicio, termino, periodo, hierarquia, classificacaomirim, classificacaoassu, ST_AsGeoJSON(". $type .") as geojson
		from busca_geral
		where tipo_de_geometria = $1
			and status = 2
			and (classificacaomirim in ($2) OR classificacaoassu IN ($2))
			and $1 is not null ";
			
			
/*
			and cast(inicio as int) <= cast(". $ano ." as int)
			and cast(termino as int) >= cast(". $ano ." as int)
";
*/




# Try query or error
$rs = pg_query_params($conn, $sql, array($type, $class));
if (!$rs) {
    echo "An SQL error occured.\n";
    exit;
}
# Build GeoJSON
$output    = '';
$rowOutput = '';
while ($row = pg_fetch_assoc($rs)) {
	$geodata = ($row['geojson'] == "") ? "{}" : $row['geojson'];
    $rowOutput = (strlen($rowOutput) > 0 ? ',' : '') . '{"type": "Feature", "geometry": ' . $geodata . ', "properties": {';
    $props = '';
    $id    = '';
	
    foreach ($row as $key => $val) {
        if ($key != "geojson") {
            $props .= (strlen($props) > 0 ? ',' : '') . '"' . $key . '":"' . escapeJsonString($val) . '"';
        }
        if ($key == "id") {
            $id .= ',"id":"' . escapeJsonString($val) . '"';
        }
    }
    
    $rowOutput .= $props . '}';
    $rowOutput .= $id;
    $rowOutput .= '}';
    $output .= $rowOutput;
}
$output = '{ "type": "FeatureCollection", "features": [ ' . $output . ' ]}';
echo $output;
?>

