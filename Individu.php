<?php
	require_once('Database.php');
	/**
	* Individu represents each chromosomes which contain genes
	*/
	class Individu
	{
		// public $velocity;
		function __construct($time)
		{
			define('API_KEY', 'AIzaSyBTE9O-ina1ZgUJgu9P4kN66etZyjErqYw');
			$this->chrom_length = mt_rand(1,$time-1);
			$this->id_origin = 1;
			$this->velocity = 40;
		}
		/**
		 * generateChrom builds the chromosomes
		 * @return array $chrom_binary
		 */
		public function generateChrom()
		{
			
			$chrom_binary = [];
			$chrom_int = [];
			$chrom_details = [];
			$waypoints = '';
			$chrom_binary = array_merge($chrom_binary, $this->dectobin($this->id_origin));
			for ($i=0; $i < $this->chrom_length; $i++) { 
				$randomized_city = $this->checkIfSame($chrom_int,mt_rand(2,10));
				$randomized_city_name = $this->getDestinationName($randomized_city);
				// echo "city_$i=".$randomized_city." city_name = $randomized_city_name <br>";
				array_push($chrom_int, $randomized_city);
				$chrom_binary = array_merge($chrom_binary, $this->dectobin($randomized_city));
				$waypoints .=$randomized_city_name."|";
			}
			$fitness = $this->generateFitnessFunction($waypoints);
			$chrom_binary = array_merge($chrom_binary, $this->dectobin($this->id_origin));
			array_push($chrom_binary, $fitness);
			return $chrom_binary;
		}
		public function generateFitnessFunction($waypoints)
		{
			$origin = $this->getDestinationName($this->id_origin);
			$total_distance = sprintf("%.1f",$this->getDistance($origin,$waypoints));
			$total_minutes = ($total_distance/$this->velocity)*60;
			$fitness = sprintf("%.10f", 1/$total_minutes);
			return $fitness;
		}
		function checkIfSame($arr,$dest)
		{
			// if(array_search($dest, array_column($arr, 'dest_id'))!== FALSE)
				if(array_search($dest, $arr)!== FALSE){
				// echo "Ada yang sama! dest = $dest <br>";
				$new_dest = $this->checkIfSame($arr, mt_rand(2,10));
				// echo "diganti jadi $new_dest<br>";
				// var_dump($arr);
				return $new_dest;
			}
			else{
				// echo "Beda! dest = $dest<br>";
				// var_dump($arr);
				return $dest;
			}
		}
		public function getDistance($origin,$waypoints)
		{
			// origin and destinations is same
			// REFERENCE : https://stackoverflow.com/questions/11038282/easiest-way-to-implode-a-two-dimensional-array
			// $waypoints = implode('|', array_map(function($line){ return $line['dest_name']; }, $waypoints));
			// $waypoints = implode("|", $waypoints);
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
			return $total_distance*0.001;
		}
		public function getDestinationName($id)
		{
			$database = new Database();
			$name = $database->selectData($id)->fetch();
			$name_url = str_replace(" ", "+", $name);
			return $name_url['dest_name'];
			// $row = $stmt->fetch();
		}
		/**
		 * Convert integer to array contains binary strings (4 digits)
		 * @param  int $dec 
		 * @return array      arr_bin
		 */
		public function dectobin($dec)
		{
			$bin = sprintf( "%04d", decbin($dec));
			$arr_bin = str_split($bin);
			return $arr_bin;
		}
		public function bintodec($arr_bin)
		{
			$bin = implode("", $arr_bin);
			$dec = bindec($bin);
			return $dec;
		}

	}
	// $time = 9;
	// $start = microtime(true);
	// $kromosom = new Individu($time);
	// echo "<pre>"; 
	// print_r($kromosom->generateChrom()); 
	// echo "</pre>";
	// $time_elapsed_secs = microtime(true) - $start;
	// echo "<br>Exec Time ".$time_elapsed_secs;
	// DONE 1: Convert Integer to Binary 4 Bits
	// DONE 2: Build DB consists of longitude and latitude
 	// DONE 3: Get Longitude and Latitude to getDistance(city1, city2) (Pretty sure needs to learn about Maps API)
 	// DONE 4: generateFitnessFunction()
 	// DONE 5: TEST!
 	// TODO 6: Bikin validasi if 4 jam cuma bisa bali selatan
 	// TODO 7: Bikin supaya efisien dengan cara ilangin si curl
?>