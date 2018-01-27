<?php
	require_once 'Individu.php';
	require_once 'Database.php';
	require_once 'Utils.php';
	/**
	* 
	*/
// TODO : Buat supaya array binarynya bisa fleksibel
// DONE : Verifikasi jaraknya masuk akal ga
	class Generasi
	{
		function __construct($pops, $time, $cities_visited)
		{
			define('MUTATION_RATE', 0.04);
			define('CROSSOVER_RATE', 0.5);
			// Belum termasuk jam
			$this->digit = 5;
			define('BATAS_AWAL', $this->digit * 2 -1);
			$this->utils = new Utils();
			$this->population = $pops;
			$this->cities_visited = $cities_visited;
			$this->time = $time;
			$this->velocity = 40;
			
			$this->database = new Database();
			// TODO: Benerin ini biar objek wisatanya pake radius
			if ($this->time <= 6) {
				$this->objek_wisata = $this->database->selectObjekWisataAreaA();
			}
			else {
				$this->objek_wisata = $this->database->selectObjekWisataAll();
			}
		}

		public function runGAAll($counter)
		{
			$first_pop = '';
			for ($i=1; $i <= $counter; $i++) { 
				try {
					echo "Generasi ke- $i<br>"
					."****************************************************************************<br>";
					$first_pop = $this->runGA($first_pop);
				}
				catch (Exception $e) {
					echo $e->getMessage();
					break;
				}
			}
			if ($first_pop != null || $first_pop != '') {
				echo "<br> Optimal Solution: <br>";
				var_dump($first_pop);
				$arr_sel_pop = array_slice($first_pop, 0, -1);
				// $a = sizeof($arr_sel_pop)-1;
				$b = sizeof($arr_sel_pop);
				$chrom_int = [];
				$i = 0;
				while ($i < $b) {
					$binary_array = array_slice($first_pop, $i, $this->digit);
					$dec = $this->utils->bintodec($binary_array);
					array_push($chrom_int, $dec);
					$i+=$this->digit;
				}
				$json_final = [];
				array_push($json_final, ["destinasi" =>  $chrom_int], ["total_minutes" => 1/end($first_pop) ]);
				echo json_encode($json_final);
			}
			
		}
		
		public function runGA($first_pop)
		{
			// // Inisiasi Kromosom
			$utils = new Utils();
			
			try {
				$population = $this->generatePops($first_pop);
				// include 'population_test.php';
				$fitness_collection = [];
				foreach ($population as $line) {
					array_push($fitness_collection, end($line));
				}
				
				// Print out current pops
				// echo $this->my_print_r2($population);
				echo "Populasi Sudah Diinisialisasi <br>=====================================<br><br>";
				// var_dump($fitness_collection);
				// var_dump($population);
				
				// ###################################################################
				// // Crossover
				// echo "Crossover<br>=====================================<br><br>";
				// $population = $this->crossover($population);
				// echo $this->my_print_r2($population);

				// ###################################################################
				// Mutasi
				echo "Mutation<br>=================================<br><br>";
				$population = $this->mutation($population);
				// echo $this->my_print_r2($population);

				// Seleksi Alam
				echo "Seleksi Alam dan yang terpilih jeng jeng<br>=================================<br><br>";
				$selected_pops = $population[$this->selection($population)];
				return $selected_pops;
				// echo $this->my_print_r2($selected_pops);
				// $fit = 1/end($selected_pops);
				// $waktu_kunjung = $this->cities_visited * 45;
				// $mins_allowed = $this->time *60;
				// $total_hrs = $waktu_kunjung + $fit;
				// echo "Menit Perjalanan: ".$fit."<br>";
				// echo "Waktu Kunjung:".$waktu_kunjung."mins<br>";
				// echo "Total:".$total_hrs." menit<br>";
				// echo "Total Hours Allowed:".$mins_allowed."mins<br>";
				// // return $selected_pops;
				// $arr_sel_pop = array_slice($selected_pops, 0, -1);
				// // $a = sizeof($arr_sel_pop)-1;
				// $b = sizeof($arr_sel_pop);
				// $chrom_int = [];
				// $i = 0;
				// // for ($i=0; $i < $b; $i+4) { 
				// // 	$binary_array = array_slice($first_pop, $i, 4);
				// // 	$dec = $this->utils->bintodec($binary_array);
				// // 	array_push($chrom_int, $dec);
				// // 	$i+=4;
				// // }
				// while ($i < $b) {
				// 	// $utils = new Utils();
				// 	$binary_array = array_slice($selected_pops, $i, $this->digit);
				// 	$dec = $this->utils->bintodec($binary_array);
				// 	array_push($chrom_int, $dec);
				// 	$i+=4;
				// }
				// $json_final = [];
				// array_push($json_final, ["destinasi" =>  $chrom_int], ["total_minutes" => 1/end($selected_pops) ]);
				// echo json_encode($json_final);
				
				return $population[0];
			} catch (Exception $e) {
				throw new Exception('Tidak ditemukan solusi');
				// echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
			
		}

		function generatePops($first_pop)
		{
			$database = new Database();
			// jika waktu ketersediaan kurang dari 6 jam
			// if ($this->time <= 6) {
			// 	$objek_wisata = $database->selectObjekBaliSelatan();
			// }
			// else{
			// 	$objek_wisata = $database->selectObjekWisataAreaA();
			// }
			$kromosom = new Individu($this->time,$this->cities_visited,$this->objek_wisata, $this->digit);
			$population = [];
			if ($first_pop!== '') {
				$population[0] = $first_pop;
				$pops_counter = $this->population - 1;
			}
			else{
				$pops_counter = $this->population;
			}

			for ($i=0; $i < $pops_counter; $i++) {
				$kromosom_baru = $kromosom->generateChrom();
				if ($kromosom_baru !== false) {
					array_push($population, $kromosom_baru);
				}
				else{
					throw new Exception('Tidak ditemukan solusi!');
				}
			}
			return $population;
		}

		/* GA Operators*/
		function crossover($population)
		{
			$available_to_xo = CROSSOVER_RATE*$this->population;
			echo "<br>Jumlah Kromosom yang di XOR :".$available_to_xo."<br>";
			for ($i=0; $i < $available_to_xo; $i+2) { 
				$rand_index_1 = mt_rand(1,($this->population-1));
				$rand_index_2 = mt_rand(1,($this->population-1));
				while ($rand_index_1 == $rand_index_2) {
					$rand_index_2 = mt_rand(1,($this->population-1));
				}
				echo "Indeks Yang Terpilih : $rand_index_1 dan $rand_index_2 Indeks: $i<br>";
				$offsprings = $this->crossoverPM($population[$rand_index_1],$population[$rand_index_2]);
				return $population;
			}
		}

		function crossoverPM($chrom_parent1, $chrom_parent2)
		{
			// DONE 1: Kerjain Crossovernya = sekarang pake parent yg random
			echo "Chrom Parent 1:".$this->my_print_r2($chrom_parent1)."<br>Chrom Parent 2:".$this->my_print_r2($chrom_parent2)."<br>";
			$length_chrom1 = sizeof($chrom_parent1);
			$length_chrom2 = sizeof($chrom_parent2);
			if ($length_chrom1 > $length_chrom2) {
				$end = $length_chrom2-6;
				$parent_selected = $chrom_parent2;
			}
			else{
				$end = $length_chrom1-6;
				$parent_selected = $chrom_parent1;
			}
			$r1 = mt_rand(BATAS_AWAL,$end);
			$r2 = mt_rand(BATAS_AWAL,$end);
			while ($r1 == $r2 || $r2 < $r1) {
				$r2 = mt_rand(BATAS_AWAL,$end);
			}
			$length_selected = $r2-$r1;
			$chrom1_slice = array_slice($chrom_parent1,$r1,$length_selected);
			$chrom2_slice = array_slice($chrom_parent2,$r1,$length_selected);
			// echo "r1 = $r1 r2 = $r2 | Chrom Slice 1:".$this->my_print_r2($chrom1_slice)."<br> Chrom Slice 2:".$this->my_print_r2($chrom2_slice)."<br>";
			// DONE 2: replace chrom1 slice ke chrom2 slice dan sebaliknya. 
			$index_slice = 0;
			$i = $r1;
			$lengthplusone = $length_selected + 1;
			while ($i <$r2 ) {
				// echo "chrom_parent1 ke $i ($chrom_parent1[$i]) diganti dengan chrom2_slice ke $index_slice - ($chrom2_slice[$index_slice])<br>chrom_parent2 ke $i ($chrom_parent2[$i]) diganti dengan chrom1_slice ke $index_slice - ($chrom1_slice[$index_slice])<br>";
				$chrom_parent1[$i] = $chrom2_slice[$index_slice];
				$chrom_parent2[$i] = $chrom1_slice[$index_slice];
				$index_slice ++;
				$i++;
			}
			$offsprings = [$chrom_parent1,$chrom_parent2];
			$z = 0;
			foreach ($offsprings as $line) {
				$verifikasi = $this->verifikasiBin($line);
				if ($verifikasi === false) {
					// echo "Lolos verifikasi parent ke - $z! Alhamdulilah ya ukhti<br>";
					$krom = $this->generateNewFitness($line);
					// echo "<br>New Chrom Parent + Fitness:".$this->my_print_r2($krom)."<br>";
				}
				else{
					// echo "Tidak Lolos verifikasi parent ke - $z! Tapi ndapapa ada yang baru<br>";
					$krom = $verifikasi;
					$krom = $this->generateNewFitness($krom);
					// echo "<br>New Chrom Parent + Fitness:".$this->my_print_r2($krom)."<br>";
				}
				$offsprings[$z] = $krom;
				$z++;
			}
			return $offsprings;
		}
		function mutation($population)
		{
			$available_to_mutate = MUTATION_RATE*$this->population;
			// for development purpose ! << DELETED SOON >>
			// if ($available_to_mutate < 1) {
			// 	$available_to_mutate = 1;
			// }
			for ($i=0; $i < $available_to_mutate; $i++) { 
				// DEBUG
				// $random_pops_index = mt_rand(1,($this->population-1));
				$random_pops_index = array_rand($population);
				// echo "Populasi ke-$random_pops_index before : <br>";
				// echo $this->my_print_r2($population[$random_pops_index]);
				// echo "Population that has been mutated : <br>";
				$pops_mutated = $this->mutationSwap($population[$random_pops_index]);
				// DONE : itung fitness setelah dia berubah
				$verifikasi = $this->verifikasiBin($pops_mutated);
				if (!$verifikasi) {
					// echo "Lolos verifikasi hasil mutasinya! Alhamdulilah ya ukhti<br><br>";
					$pops_mutated = $this->generateNewFitness($pops_mutated);
				}else{
					// echo "Tidak Lolos verifikasi hasil mutasinya!  Tapi ndapapa ada yang baru<br>";
					// echo "Individu baru: <br>";
					// var_dump($verifikasi);
					$pops_mutated = $this->generateNewFitness($verifikasi);
				}
				
				// Timpa populasi lama dengan yang dimutasi
				$population[$random_pops_index] = $pops_mutated;
			}
			return $population;
		}
		function selection($population)
		{
			$fitness_collection = [];
			foreach ($population as $line) {
				array_push($fitness_collection, end($line));
			}
			$selected_index = $this->selectionRW($fitness_collection);
			return $selected_index;
			// echo "<br> selected_index : $selected_index";
			// echo "<br> isinya : ";
			// echo $this->my_print_r2($population[$selected_index]);
		}

		/* GA Operators */
		
		
		function mutationSwap($chrom)
		{
			$length = sizeof($chrom);
			// time + origin
			$start = $this->digit*2;
			$end = $length-$this->digit-2;
			// -5 = -1 (karena array indexnya mulai dari 0) -1 karena diambil fitness-4 (karena 4 index terakhir isinya ngurah rai yg ga bs diganggu gugat)
			// echo "start $start end $end ";
			$r1 = mt_rand($start,$end);
			$r2 = mt_rand($start,$end);
			while ($r1 == $r2) {
				$r2 = mt_rand($start,$end);
			}
			// echo "Tuker $r1 dengan $r2<br>";
			$temp = $chrom[$r1];
			$chrom[$r1]=$chrom[$r2];
			$chrom[$r2]=$temp;
			return $chrom;
		}

		function selectionRW($fitness)
		{
			$utils = new Utils();
			// DEBUG
			// echo "Fitness Collection:";
			// var_dump($fitness);
			$total_fitness = 0;
			foreach ($fitness as $line) {
				$total_fitness += $line;
			}
			// echo "<br>Total Fitness: $total_fitness";
			$random_float_num = $this->random_float(0,$total_fitness);
			// echo "<br>Random Float Num: $random_float_num";
			$partial_sum = 0;
			$i = 0;
			foreach ($fitness as $line) {
				$partial_sum+=$line;
				// echo "<br>partial sum: $partial_sum <br>";
				if ($partial_sum >= $random_float_num) {
					// echo "index selected for this generation by roulette wheel= $i<br>";
					return $i;
				}
				$i++;
			}
		}
		
		
//====================== UTILS =============================\\
		function generateNewFitness($chromosom)
		{
			// var_dump($chromosom);
			$last_index = sizeof($chromosom)-1;
			$utils = new Utils();
			$binary = "";
			$waypoints = "";
			$chrom_int = [];
			$i = 0;
			$a = sizeof($chromosom)-1;
			$b = sizeof($chromosom);
			while ($i < $b) {
				if ($i!=$a) {
					$binary_array = array_slice($chromosom, $i, $this->digit);
					$dec = $utils->bintodec($binary_array);
					array_push($chrom_int, $dec);
				}
				$i+=$this->digit;
			}
			$distance = $utils->getDistance($chrom_int);
			$total_distance = sprintf("%.1f",$distance);
			$total_minutes = ($total_distance/$this->velocity)*60;
			$fitness = sprintf("%.10f", 1/$total_minutes);
			$chromosom[$last_index] = $fitness;
			return $chromosom;
		}
		public function verifikasiBin($chromosom)
		{
			// echo "Dilakukan proses verifikasi pada kromosom berikut ini<br>------------------<br>";
			// echo $this->my_print_r2($chromosom);
			$i = 0;
			$a = sizeof($chromosom)-1;
			$b = sizeof($chromosom);
			$batas_akhir = $a-$this->digit;
			$ar_int = [];
			$failed_index_counter = 0;

			while ($i < $b) {
				// cek dimana $i bukan di posisi array yg fitness dan bukan di posisi origin dan destinasi akhir
				if ($i!=$a && $i>BATAS_AWAL) {
					$binary_array = array_slice($chromosom, $i, $this->digit);
					$dec = $this->utils->bintodec($binary_array);
					// check apakah dia ga di index = 0 dan ga batas akhir
					if ($i!=0&&$i!==$batas_akhir&& $i > BATAS_AWAL) {
						// jika dia = 1 atau dia = 0
						if ($dec==1 || $dec == 0) {
							$failed_index = $failed_index_counter;
							break;
						}
						// check ada kota yang sama
						$same_dest_index = $this->utils-> checkIfCitySame($ar_int,$dec);
						if ($same_dest_index!== false) {
							// echo "<br>Kota ke - $same_dest_index double <br>";
							$failed_index = $same_dest_index;
							// echo $this->my_print_r2($ar_int);
							break;
						}
						array_push($ar_int, $dec);
						if (!$this->utils->verifikasi($dec)&&$dec!=0) {
							$failed_index = $failed_index_counter;
							// echo "<br>Tidak ditemukan objek wisata ke- $failed_index_counter - ($dec)<br>";
							// echo $this->my_print_r2($chromosom);
							break;
						}
						$failed_index_counter ++;
					}
				}
				$i+=$this->digit;
			}
			$new_chromosom = $chromosom;
			
			// kota pertama digit 5 : ganti dari 10-14 $failed_index = 0
			// kota kedua digit 4: ganti dari 12-15 1
			// 5(digit)*(2+0 (failed index))

			// fixing broken chromosome
			if (isset($failed_index)) {
				// echo "Ditemukan failed binary pada destinasi ke-($failed_index), melakukan penggantian sparepart<br>------------------";
				// // kalo tanpa jam
				// // BARU SAMPE SINI 
				$failed_index = $this->digit*($failed_index+2);
				$failed_index_end = $failed_index+$this->digit;
				// echo "1) Generate destinasi baru<br>";
				// $last_index_objek = sizeof($this->objek_wisata)-1;
				// $index_randomized_city = mt_rand(0,$last_index_objek);
				$index_randomized_city 	= array_rand($this->objek_wisata);
				$city_check = $this->notZeroOrOne($this->objek_wisata[$index_randomized_city]);
				$randomized_city = $this->checkIfSame($ar_int,$city_check,$this->objek_wisata);
				// echo "- Destinasi Baru = $randomized_city<br>";
				$new_city_binary = $this->utils->dectobin($randomized_city, $this->digit);
				$new_city_binary_index = 0;
				// echo "Binary Destinasi<br>";
				// var_dump($new_city_binary);
				// echo "<br>2) Ganti binary tik tok<br>";
				while ($failed_index < $failed_index_end) {
					$chromosom[$failed_index] = $new_city_binary[$new_city_binary_index];
					$failed_index ++;
					$new_city_binary_index ++;
				}
				// echo $this->my_print_r2($chromosom);
				// echo "<br>3) Yayy! Brand new kromosom, hopefully sudah benar yak!<br>";
				// check again before passing it
				$newChromosom = $this->verifikasiBin($chromosom);
				if ($newChromosom) {
					$chromosom = $newChromosom;
				}
				// echo $this->my_print_r2($chromosom);
				return $chromosom;
			}
			// var_dump($chromosom);
			# Apabila benar / lolos verifikasi
			return false;
		}
		public function notZeroOrOne($city){
			if ($city == 0 || $city == 1) {
				// echo "CAUGHT $city <br>";
				$index_randomized_city = array_rand($this->objek_wisata);
				$newCity = $this->objek_wisata[$index_randomized_city];
				$newCity = $this->notZeroOrOne($newCity);
			}
			else {
				$newCity = $city;
			}
			return $newCity;
		}
		function random_float ($min,$max) {
			return ($min + lcg_value()*(abs($max - $min)));
		}
		
		public function uji()
		{
			var_dump($this->verifikasiBin(["0","0","0","1","0","0","0","1","1","0","1","1","0","0","0","1","0.0081400081"]));
		}
		function my_print_r2 ($x) {
		  return json_encode($x)."<br>";
		}
		public function bintodec($arr_bin)
		{
			$bin = implode("", $arr_bin);
			$dec = bindec($bin);
			return $dec;
		}
		function checkIfSame($arr,$dest, $selection)
		{
			if(array_search($dest, $arr)!== FALSE){
				$l_index = sizeof($selection)-1;
				$new_index = mt_rand(0,$l_index);
				$new_dest = $this->checkIfSame($arr, $selection[$new_index], $selection);
				return $new_dest;
			}
			else{
				return $dest;
			}
		}
	}
	$berapa_populasi = 50;
	$waktu = 11;
	$yang_mau_dikunjungi = 2;
	$generasi = new Generasi($berapa_populasi,$waktu,$yang_mau_dikunjungi);
	$start = microtime(true);
	echo "<pre>"; 
	// maks cuma bisa 15, kalo >15 ga kuat komp nya
	print_r($generasi->runGAAll(50));
	// print_r($generasi->verifikasiBin(["0","0","1","0","1","0","0","0","0","1","0","0","0","0","1","0","0","0","0","1","0.0050543341"]));
	echo "</pre>";
	$time_elapsed_secs = microtime(true) - $start;
	echo "<br>Exec Time ".$time_elapsed_secs;
	// DONE : VERIFIKASI SETELAH MUTASI DAN CROSSOVER
?>