<?php

header('Content-type: text/plain; charset=utf-8');
require_once("safe.php");


if(isset($_GET['help']) || !isset($_GET['class'])) {
	?>
# Digital Atlas of Portuguese America
@ http://lhs.unb.br/atlas

This script offers a direct access to the geographic data and metadata produced, 
stored, and published by the Digital Atlas of Portuguese America, based on the 
categories under which geographic features are stored.


== Parameters ==

= Mandatory

class (integer, comma-separated) - refers to the categories that are attached 
to a particular feature. For a comprehensive list of these categories, see 
http://lhs.unb.br/webservice/categories.php?key=XXXXXXXXXX. Users can 
provides more than one category using comma-separated values (like 185,195,132).

= Optional

type (text, within options) - describes the type of geographic feature - point, 
line, polygon - you want to access and the possible values are “ponto”, “linha”, 
“poligono” (in Portuguese). 

init (4-digit integer, YYYY) - Initial year of the search range.

end (4-digit integer, YYYY) - Final year of the search range.

obs ('true' or 'false', default 'true') - includes a field with observations
regarding the features listed.

output (options: json, csv or tsv, default json) - chooses the format of the output.


== Example ==

http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']; ?>?type=ponto&class=185,186&init=1580&end=1640


== Changelog ==

4 Aug 2018
- Create standard output routine

14 Mar 2018
- Added field "observacoes" (observation) and a new parameter
- Create alternative output (csv)

18 Jan 2018
- Rename file to list.php (atlas_class.php is not deprecated)
- Added filter for dates

13 Nov 2017
- Removed authentication system
- Created validation for data without geography feature (will keep the data, but include a "blank" geometry

<?php
	die;
}

$obs = (!isset($_GET['obs'])) ? 'true' : $_GET['obs'];
$obs_add = ($obs == 'true') ? ', observacoes as obs' : '';

$class = "{". $_GET['class'] ."}";

$type = (!isset($_GET['type'])) ? 'ponto' : $_GET['type'];

if(!in_array($type, array('linha','ponto','poligono')))
	die("Invalid geometry type");

$vals = array($type, $class);

// prepare WHERE statement
$where = "";
$order = count($vals);

if(isset($_GET['init']) && $init = $_GET['init']) {
	$where .= "AND termino >= \$". ($order+1);
	$vals[$order] = $init;
	$order++;
}

if(isset($_GET['end']) && $end = $_GET['end']) {
	$where .= " AND inicio <= \$". ($order+1);
	$vals[$order] = $end;
	$order++;
}

# Build SQL SELECT statement and return the geometry as a GeoJSON element in EPSG: 4326
$sql = "
	SELECT codigo, nome, inicio, termino, periodo, hierarquia, classificacaomirim, classificacaoassu, ST_AsGeoJSON(". $type .") AS geojson ". $obs_add ."
		FROM busca_geral
		WHERE tipo_de_geometria = $1
			AND status = 2
			AND (classificacaomirim = ANY (\$2) OR classificacaoassu = ANY (\$2))
			AND $1 IS NOT NULL
			". $where ."
			ORDER BY nome";
			
# Try query or error
$rs = pg_query_params($conn, $sql, $vals);
if (!$rs) {
    echo "An SQL error occured.\n";
    exit;
}

require_once('output.php');

echo $return;
?>

