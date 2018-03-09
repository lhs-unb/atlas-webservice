<?php
header('Content-type: text/plain; charset=utf-8');
require_once("safe.php");


if(isset($_GET['help']) || !isset($_GET['phrase'])) {
	?>
# Digital Atlas of Portuguese America
@ http://lhs.unb.br/atlas

This scripts allows the requester to search a specific location in the data
published he Digital Atlas of Portuguese America. 


== Parameters ==

= Mandatory

phrase (text) - Name or partial name of the place that are being searched. 


= Optional

init (4-digit integer, YYYY) - Initial year of the search range.

end (4-digit integer, YYYY) - Final year of the search range.

limit (integer, default=10) - Number of entries that the service will return.

class (integer, comma-separated) - refers to the categories that are attached 
to a particular feature. For a comprehensive list of these categories, see 
http://lhs.unb.br/webservice/categories.php. Users can provide more 
than one category using comma-separated values (like 185,195,132).


== Example ==

http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']; ?>?class=185,186&init=1580&end=1640&phrase=São Paulo


<?php
	die;
}

$query = "%".$_GET['phrase']."%";
$limit = (isset($_GET['limit'])) ? $_GET['limit'] : 10;

$vals = array($query, $limit); 

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

if(isset($_GET['class']) && $class = $_GET['class']) {
	$where .= " AND (classificacaomirim = ANY (\$". ($order+1) .") OR classificacaoassu = ANY (\$". ($order+1) ."))";
	$vals[$order] = "{". $class ."}";
	$order++;
}

$sql = "SELECT DISTINCT nome, codigo, hierarquia, inicio, termino, observacoes FROM busca_geral
			WHERE status = 2
			AND LOWER(nome) LIKE LOWER($1)
			". $where ."
			ORDER BY nome, inicio
			LIMIT $2
			";

/*
//debug function for pg_query_params()
$debug = preg_replace_callback( 
        '/\$(\d+)\b/',
        function($match) use ($vals) { 
            $key=($match[1]-1); return ( is_null($vals[$key])?'NULL':pg_escape_literal($vals[$key]) ); 
        },
        $sql);

echo "$debug";
*/

$rs = pg_query_params($conn, $sql, $vals);

if (!$rs) {
    echo "An SQL error occured.\n";
    exit;
}

$output = "";
while ($row = pg_fetch_assoc($rs)) {
	$output .= "{\"id\" : ". $row['codigo'] .",\"name\" : \"". escapeJsonString($row['nome']) ."\",\"init\" : ". $row['inicio'] .",\"end\" : ". $row['termino'] .", \"obs\" : ". $row['observacoes'] ."},";
}

$output = "[". substr($output, 0, -1) . "]";

echo $output;
?>