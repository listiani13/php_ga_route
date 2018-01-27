<?php
	$arr_data = [];
	$city_length = 11;
	for ($i=0; $i < $city_length; $i++) { 
		$arr_isi = [];
		for ($j=0; $j < $city_length; $j++) { 
			if ($i === $j) {
				$response = 0;
			}
			else{
				echo "Input jarak kota ke $i to $j - ";
				$stdin = fopen('php://stdin', 'r');
				$response = fgets($stdin);
				$arr_isi["c$j"] = $response;
			}
			// array_push($arr_isi, $response);
		}
		$arr_data["c$i"] = $arr_isi;
		// array_push($arr_data, $arr_isi);
	}
	$json = json_encode($arr_data);
	// $json_dec = json_decode($json);
	// $isi = var_export($json, true);
	// https://stackoverflow.com/questions/4230170/replacing-r-n-newline-characters-after-running-json-encode
	$myfile = fopen("D:\\json_jarak.json", "w");
	$a = fwrite($myfile, $json);
	if ($a) {
		echo "Success";
	}
?>