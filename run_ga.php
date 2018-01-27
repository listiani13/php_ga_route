<?php
	$generasi = new Generasi(4,4);
	$start = microtime(true);
	echo "<pre>"; 
	print_r($generasi->runGA());
	echo "</pre>";
	$time_elapsed_secs = microtime(true) - $start;
	echo "<br>Exec Time ".$time_elapsed_secs;
?>