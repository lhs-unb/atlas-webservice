<?php

header('Content-type: text/plain; charset=utf-8');
require_once("safe.php");

?>
# Digital Atlas of Portuguese America
@ http://lhs.unb.br/atlas

This script offers a comprehensive list of all categories used to classify and order
the geographic data based on our tagging system. Each feature could be attached to 
an unlimited number of the categories listed below. Most of these categories, however,
haven't been revised, so we recommend contact us to make sure the data you are fetching
is revised.

===================

Categories:
<?php


$sql = "SELECT id, nome FROM classificacoes WHERE pai_id IS NULL ORDER BY nome";

$rs = pg_query($conn, $sql);
if (!$rs) {
    echo "An SQL error occured.\n";
    exit;
}

while ($row = pg_fetch_assoc($rs)) {
	echo $row['id'] ."	". $row['nome'] ."\n";
	
	$sql = "SELECT id, nome FROM classificacoes WHERE pai_id = ". $row['id'] ." ORDER BY nome";
	$rs2 = pg_query($conn, $sql);
		while ($row2 = pg_fetch_assoc($rs2)) {
			echo $row2['id'] ."		". $row2['nome'] ."\n";
		}	
}
?>
