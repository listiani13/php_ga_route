<?php
	include 'koneksi.php';
	$lat = '-8.816568';
	$lng = '115.092211';
	$sql = "SELECT *, MIN( 6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(" . $lng . ") ) + sin( radians(" . $lat . ") ) * sin( radians( lat ) ) ) ) AS distance FROM dest WHERE dest_id != 1 HAVING distance < 15 ";
	echo "SQL: ".$sql;
	// Get distance radius
	//SELECT *, ( 6371 * acos( cos( radians(-8.718953) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(115.174789) ) + sin( radians(-8.718953) ) * sin( radians( lat ) ) ) ) AS distance FROM dest HAVING distance < 15
?>
