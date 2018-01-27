<?php
	include 'koneksi.php';
	// $address = "Ngurah+Rai";
	$region = "Indonesia";
	$api_key = "AIzaSyBTE9O-ina1ZgUJgu9P4kN66etZyjErqYw";
	$arr_dest = ['Ngurah Rai','Tanah Lot','Uluwatu Temple','Ulun Danu Bratan Temple','Jl. Raya Penelokan','Kebun Raya Eka Karya','Tirta Empul','Taman Ayun','Air Panas Banjar','Bali Safari and Marine Park','Goa Gajah'];
	
	// var_dump($response_a);
	// echo '<pre>' . var_export($response_a, true) . '</pre>';
	// echo $lat = $response_a->results[0]->geometry->location->lat;
	// echo "<br />";
	// echo $long = $response_a->results[0]->geometry->location->lng;
	$start = microtime(true);
	for ($i=0; $i < sizeof($arr_dest); $i++) { 
		// echo "$arr_dest[$i]";
		$destination = str_replace(" ", "+", $arr_dest[$i]);
		$url = "https://maps.google.com/maps/api/geocode/json?address=$destination&sensor=false&region=$region&key=$api_key";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response);
		$lat = $response_a->results[0]->geometry->location->lat;
		$long = $response_a->results[0]->geometry->location->lng;
		$sql = "INSERT INTO dest (`dest_name`, `lat`, `lng`)
	    	VALUES ('$arr_dest[$i]',$lat, $long)";
	    $res = $conn->exec($sql);
	    if ($res) {
	    	echo "Success! $arr_dest[$i]<br>";
	    }
	}
	$time_elapsed_secs = microtime(true) - $start;
	echo "Exec Time ".$time_elapsed_secs;
?>