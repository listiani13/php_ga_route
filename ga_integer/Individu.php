<?php
	require_once('Database.php');
	require_once('Utils.php');
	/**
	* Individu represents each chromosomes which contain genes
	*/
	class Individu
	{
		function __construct($time,$cities_amount, $objek_wisata)
		{
			// define('API_KEY', 'AIzaSyBTE9O-ina1ZgUJgu9P4kN66etZyjErqYw');
			// $this->chrom_length = mt_rand(1,$time-1);
			$this->time = $time;
			$this->chrom_length = $cities_amount;
			$this->id_origin = 1;
			$this->velocity = 40;
			$this->objek_wisata = $objek_wisata;
			$this->waktu_kunjung = 60;
			$this->utils = new Utils();
			$this->digit = 4;
		}
		/**
		 * generateChrom builds the chromosomes
		 * @return array $chrom_binary
		 */
		public function generateChrom()
		{
			static $recursion_depth = 0;
			// $chrom_binary = [];
			$chrom_int = [];
			// $chrom_details = [];
			$origin_binary = $this->utils->dectobin($this->id_origin, $this->digit);
			// $chrom_binary = array_merge($chrom_binary, $origin_binary);
			array_push($chrom_int, $this->id_origin);
			// $last_index_objek = sizeof($this->objek_wisata)-1;
			for ($i=0; $i < $this->chrom_length; $i++) { 
				// $index_randomized_city = mt_rand(0,$last_index_objek);
				$index_randomized_city 	= array_rand($this->objek_wisata);
				$randomized_city 		= $this->checkIfSame($chrom_int,$this->objek_wisata[$index_randomized_city],$this->objek_wisata);
				array_push($chrom_int, $randomized_city);
				// $chrom_binary = array_merge($chrom_binary, $this->utils->dectobin($randomized_city, $this->digit));
			}
			array_push($chrom_int, $this->id_origin);
			// $chrom_binary = array_merge($chrom_binary, $origin_binary);
			$fitness = $this->generateFitnessFunction($chrom_int);
			if ($fitness !== FALSE) {
				# Jika lulus verifikasi fitness
				array_push($chrom_int, $fitness);
			}
			else{
				$recursion_depth ++;
				if ($recursion_depth < 7000) {
					$chrom_int = $this->generateChrom();
					// $chrom_binary = $this->generateChrom();
				}
				else
				{
					$recursion_depth = 0;
					return false;
				}
			}
			return $chrom_int;
		}
		public function generateChromDetails()
		{
			
		}
		public function generateFitnessFunction($cities)
		{
			$utils = new Utils();
			$total_distance = $utils->getDistance($cities);
			$total_minutes = ($total_distance/$this->velocity)*60;
			$minutes_allowed = ($this->time * 60)-($this->waktu_kunjung*$this->chrom_length);
			if ($total_minutes > $minutes_allowed) {
				return false;
			}
			else {
				$fitness = sprintf("%.10f", 1/$total_minutes);
				return $fitness;
			}
		}

		function checkIfSame($arr,$dest, $selection)
		{
			if(array_search($dest, $arr)!== FALSE){
				// echo "Ada yang sama! dest = $dest <br>";
				$l_index = sizeof($selection)-1;
				$new_index = mt_rand(0,$l_index);
				$new_dest = $this->checkIfSame($arr, $selection[$new_index], $selection);
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
	

	// $time = 4;
	// $cities_amount = 2;
	// $start = microtime(true);
	// $cities = [1,2,3,4,6,7,8,10,11];
	// $kromosom = new Individu($time,$cities_amount,$cities);
	// echo "<pre>"; 
	// for ($i=0; $i < 50; $i++) { 
	// 	echo "Ke -".$i."<br>";
	// 	print_r($kromosom->generateChrom()); 
	// }
	// // print_r($kromosom->generateChrom()); 
	// echo "</pre>";
	// $time_elapsed_secs = microtime(true) - $start;
	// echo "<br>Exec Time ".$time_elapsed_secs;
	// DONE 1: Convert Integer to Binary 4 Bits
	// DONE 2: Build DB consists of longitude and latitude
 	// DONE 3: Get Longitude and Latitude to getDistance(city1, city2) (Pretty sure needs to learn about Maps API)
 	// DONE 4: generateFitnessFunction()
 	// DONE 5: TEST!
 	// HALFDONETODO 6: Bikin validasi if 4 jam cuma bisa bali selatan
 	// DONE 7: Bikin supaya efisien dengan cara ilangin si curl, ganti jadi JSON
 	// TODO 8: Tempelin sama waktu!
?>