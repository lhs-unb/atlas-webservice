<?php
header('Content-type: text/plain; charset=utf-8');
require_once("safe.php");


if(isset($_GET['help']) || !isset($_GET['ids'])) {
	?>
# Digital Atlas of Portuguese America
@ http://lhs.unb.br/atlas

This scripts allows the requester to fetch information about a specific 
location in the data published he Digital Atlas of Portuguese America based
on the ID (or set of IDs).


== Parameters ==

= Mandatory

ids (integer, comma-separated) - Name or partial name of the place that are being searched. 

= Optional

type (text, within options) - describes the type of geographic feature - point, 
line, polygon - you want to access and the possible values are “ponto”, “linha”, 
“poligono” (in Portuguese). 

obs ('true' or 'false', default 'true') - includes a field with observations
regarding the features listed.

output (options: json, csv or tsv, default json) - chooses the format of the output.


== Example ==

http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']; ?>?ids=1027,1303&type=ponto&output=json&obs=true

== Changelog ==

4 Aug 2018
- Creation of the script

<?php
	die;
}
// Observation
$obs = (!isset($_GET['obs'])) ? 'true' : $_GET['obs'];
$obs_add = ($obs == 'true') ? ', observacoes as obs' : '';

// Data
$ids = "{". $_GET['ids'] ."}";
$type = (!isset($_GET['type'])) ? 'ponto' : $_GET['type'];

$vals = array($type, $ids);

$sql = "SELECT DISTINCT nome, codigo, hierarquia, inicio, termino, ST_AsGeoJSON(". $type .") AS geojson ". $obs_add ." 
			FROM busca_geral
			WHERE status = 2
				AND (codigo = ANY (\$2))
				AND tipo_de_geometria = $1
				AND $1 IS NOT NULL
			ORDER BY nome, inicio";

// require_once('debug.php');

$rs = pg_query_params($conn, $sql, $vals);

if (!$rs) {
    echo "An SQL error occured.\n";
    exit;
}

require_once('output.php');
echo $return;
?>