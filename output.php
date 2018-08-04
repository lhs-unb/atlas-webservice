<?php
$output = (!isset($_GET['output'])) ? 'json' : $_GET['output'];
$return = '';

// JSON
if($output == "json") {
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
		$return .= $rowOutput;
	}
	$return = '{ "type": "FeatureCollection", "features": [ ' . $return . ' ]}';
}
else {
	$count = 0;
	$spacer = ($output == 'tsv') ? "	" : ",";
	
	while ($row = pg_fetch_assoc($rs)) {
		// get header
		if($count == 0)
			$header = array_keys($row);
		
		$line = '';
		foreach($row as $r) {
			if($output == 'tsv')
				$line .= $r . $spacer;
			else
				$line .= "\"". str_replace("\"", "\"\"", $r) ."\"". $spacer;
		}
		$line = substr($line, 0, -1). "\r\n";
		$return .= $line;
		$count++;
	}
	
	// header
	$line = '';
	foreach($header as $h) {
		$line .= $h . $spacer;
	}
	$return = substr($line,0,-1) ."\r\n". $return;
}