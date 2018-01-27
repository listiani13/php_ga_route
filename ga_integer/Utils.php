<?php
require_once('Database.php');
	/**
	* 
	*/
	class Utils
	{
		
		function __construct()
		{
			// define('API_KEY', 'AIzaSyBTE9O-ina1ZgUJgu9P4kN66etZyjErqYw');
		}
		public function dectobin($dec, $digit)
		{
			$bin = sprintf( "%0".$digit."d", decbin($dec));
			$arr_bin = str_split($bin);
			return $arr_bin;
		}
		public function bintodec($arr_bin)
		{
			$bin = implode("", $arr_bin);
			$dec = bindec($bin);
			return $dec;
		}
		public function getDestinationName($id)
		{
			$database = new Database();
			$name = $database->selectData($id)->fetch();
			$name_url = str_replace(" ", "+", $name);
			return $name_url['dest_name'];
			// $row = $stmt->fetch();
		}
		public function getDistance($cities)
		{
			$json_jarak = file_get_contents("file:///D:/json_jarak.json");
			$json_jarak = json_decode($json_jarak,true);
			$total_jarak = 0;
			$ukuran		= sizeof($cities);
			for ($i=0; $i < $ukuran; $i++) { 
				if ($i!=($ukuran-1)) {
					$a = $i+1;
					$origin = $cities[$i];
					$dest 	= $cities[$a];
					$total_jarak +=$json_jarak["c$origin"]["c$dest"];
				}
			}
			return $total_jarak;
		}
		public function verifikasi($num)
		{
			$json_jarak = file_get_contents("file:///D:/json_jarak.json");
			$json_jarak = json_decode($json_jarak,true);
			$total_jarak = 0;
			if (!isset($json_jarak["c$num"])) {
				return false;
			}
			return true;
		}
		public function array_flatten($array) { 
		  if (!is_array($array)) { 
		    return FALSE; 
		  } 
		  $result = array(); 
		  foreach ($array as $key => $value) { 
		    if (is_array($value)) { 
		      $result = array_merge($result, $this->array_flatten($value)); 
		    } 
		    else { 
		      $result[$key] = $value; 
		    } 
		  } 
		  return $result; 
		} 
		function checkIfCitySame($arr,$dest)
		{
			$founded_index = array_search($dest, $arr);
			if($founded_index !== false){
				return $founded_index;
			}
			return false;
		}
		/*public function getDistance($origin,$waypoints)
		{
			// origin and destinations is same
			// REFERENCE : https://stackoverflow.com/questions/11038282/easiest-way-to-implode-a-two-dimensional-array
			$url = "https://maps.googleapis.com/maps/api/directions/json?origin=$origin&destination=$origin&waypoints=$waypoints&key=".API_KEY;
			echo "$url<br>";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$result = file_get_contents($url);
			$response_a = json_decode($response);
			// in meters
			$legs_arr = $response_a->routes[0]->legs;
			$total_distance = 0;
			foreach ($legs_arr as $line) {
				$total_distance+=$line->distance->value;
			}
			// $total_distance = 170;
			return $total_distance*0.001;
		}*/
		// REF : https://stackoverflow.com/questions/6785355/convert-multidimensional-array-into-single-array
		
	}
?>