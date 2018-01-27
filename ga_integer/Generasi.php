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
			define('BATAS_AWAL', 1);
			$this->utils = new Utils();
			$this->population = $pops;
			$this->cities_visited = $cities_visited;
			$this->time = $time;
			$this->velocity = 40;
			$this->digit = 4;
			$this->database = new Database();
			if ($this->time <= 6) {
				$this->objek_wisata = $this->database->selectObjekBaliSelatan();
			}
			else {
				$this->objek_wisata = $this->database->selectObjekBaliUtara();
			}
		}

		public function runGAAll($counter)
		{
			$first_pop = '';
			for ($i=0; $i < $counter; $i++) { 
				$first_pop = $this->runGA($first_pop);
			}
			// $first_pop = $this->runGA($first_pop);

			echo "<br> Optimal Solution: <br>";
			var_dump($first_pop);
			// $json_final = [];
			// array_push($json_final, ["destinasi" =>  array_slice($k, 0, -1)], ["total_minutes" => 1/end($first_pop) ]);
			// echo json_encode($json_final);
			
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
				
				###################################################################
				// Crossover
				echo "Crossover<br>=====================================<br><br>";
				$population = $this->crossover($population);
				// echo $this->my_print_r2($population);

				// ###################################################################
				// // Mutasi
				// echo "<br><br>Mutation<br>=================================<br><br>";
				// $population = $this->mutation($population);
				// // echo $this->my_print_r2($population);

				// // Seleksi Alam
				// echo "<br><br>Seleksi Alam dan yang terpilih jeng jeng<br>=================================<br><br>";
				// $selected_pops = $population[$this->selection($population)];
				// echo $this->my_print_r2($selected_pops);
				// $fit = 1/end($selected_pops);
				// $waktu_kunjung = $this->cities_visited * 45;
				// $mins_allowed = $this->time *60;
				// $total_hrs = $waktu_kunjung + $fit;
				// echo "Menit Perjalanan: ".$fit."<br>";
				// echo "Waktu Kunjung:".$waktu_kunjung."mins<br>";
				// echo "Total:".$total_hrs." menit<br>";
				// echo "Total Hours Allowed:".$mins_allowed."mins<br>";
				// $json_final = [];
				// array_push($json_final, ["destinasi" =>  array_slice($selected_pops, 0, -1)], ["total_minutes" => 1/end($selected_pops) ]);
				// echo json_encode($json_final);
				// return $selected_pops;

			} catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
			
		}

		function generatePops($first_pop)
		{
			$database = new Database();
			// jika waktu ketersediaan kurang dari 6 jam
			if ($this->time <= 6) {
				$objek_wisata = $database->selectObjekBaliSelatan();
			}
			else{
				$objek_wisata = $database->selectObjekBaliUtara();
			}
			$kromosom = new Individu($this->time,$this->cities_visited,$objek_wisata);
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
			$i = 0;
			while ($i < $available_to_xo) {
				$rand_index_1 = array_rand($population);
				$rand_index_2 = array_rand($population);
				while ($rand_index_1 == $rand_index_2) {
					$rand_index_2 = array_rand($population);
				}
				echo "Indeks Yang Terpilih : $rand_index_1 dan $rand_index_2 Indeks: $i<br>";
				$offsprings = $this->crossoverPM($population[$rand_index_1],$population[$rand_index_2]);
				return $population;
				$i += 2;
			}
			// for ($i=0; $i < $available_to_xo; $i+2) { 
			// 	#choosing parents
			// 	$rand_index_1 = array_rand($population);
			// 	$rand_index_2 = array_rand($population);
			// 	while ($rand_index_1 == $rand_index_2) {
			// 		$rand_index_2 = array_rand($population);
			// 	}
			// 	echo "Indeks Yang Terpilih : $rand_index_1 dan $rand_index_2 Indeks: $i<br>";
			// 	// $offsprings = $this->crossoverPM($population[$rand_index_1],$population[$rand_index_2]);
			// 	// return $population;
			// }
		}

		function crossoverPM($chrom_parent1, $chrom_parent2)
		{
			// DONE 1: Kerjain Crossovernya = sekarang pake parent yg random
			echo "Chrom Parent 1:".$this->my_print_r2($chrom_parent1)."<br>Chrom Parent 2:".$this->my_print_r2($chrom_parent2)."<br>";
			$length_chrom1 = sizeof($chrom_parent1);
			$length_chrom2 = sizeof($chrom_parent2);
			if ($length_chrom1 > $length_chrom2) {
				$end = $length_chrom2-3;
				$parent_selected = $chrom_parent2;
			}
			else{
				$end = $length_chrom1-3;
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
			echo "r1 = $r1 r2 = $r2 | Chrom Slice 1:".$this->my_print_r2($chrom1_slice)."<br> Chrom Slice 2:".$this->my_print_r2($chrom2_slice)."<br>";
			// DONE 2: replace chrom1 slice ke chrom2 slice dan sebaliknya. 
			$index_slice = 0;
			$i = $r1;
			$lengthplusone = $length_selected + 1;
			while ($i <$r2 ) {
				echo "chrom_parent1 ke $i ($chrom_parent1[$i]) diganti dengan chrom2_slice ke $index_slice - ($chrom2_slice[$index_slice])<br>chrom_parent2 ke $i ($chrom_parent2[$i]) diganti dengan chrom1_slice ke $index_slice - ($chrom1_slice[$index_slice])<br>";
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
					echo "Lolos verifikasi parent ke - $z! Alhamdulilah ya ukhti<br>";
					$krom = $this->generateNewFitness($line);
					echo "<br>New Chrom Parent + Fitness:".$this->my_print_r2($krom)."<br>";
				}
				else{
					echo "Tidak Lolos verifikasi parent ke - $z! Tapi ndapapa ada yang baru<br>";
					$krom = $verifikasi;
					$krom = $this->generateNewFitness($krom);
					echo "<br>New Chrom Parent + Fitness:".$this->my_print_r2($krom)."<br>";
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
			if ($available_to_mutate < 1) {
				$available_to_mutate = 1;
			}
			for ($i=0; $i < $available_to_mutate; $i++) { 
				// DEBUG
				$random_pops_index = mt_rand(1,($this->population-1));
				echo "Populasi ke-$random_pops_index before : <br>";
				echo $this->my_print_r2($population[$random_pops_index]);
				echo "Population that has been mutated : <br>";
				$pops_mutated = $this->mutationSwap($population[$random_pops_index]);
				// DONE : itung fitness setelah dia berubah
				$verifikasi = $this->verifikasiBin($pops_mutated);
				if (!$verifikasi) {
					echo "Lolos verifikasi hasil mutasinya! Alhamdulilah ya ukhti<br>";
					$pops_mutated = $this->generateNewFitness($pops_mutated);
				}else{
					echo "Tidak Lolos verifikasi hasil mutasinya!  Tapi ndapapa ada yang baru<br>";
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
			echo "<br> selected_index : $selected_index";
			// echo "<br> isinya : ";
			// echo $this->my_print_r2($population[$selected_index]);
		}

		/* GA Operators */
		
		
		function mutationSwap($chrom)
		{
			$length = sizeof($chrom);
			$start  = 1;
			$end 	= $length-3;
			// -5 = -1 (karena array indexnya mulai dari 0) -1 karena diambil fitness-4 (karena 4 index terakhir isinya ngurah rai yg ga bs diganggu gugat)
			$r1 = mt_rand($start,$end);
			$r2 = mt_rand($start,$end);
			// $r1 = array_rand($chrom);
			// $r2 = array_rand($chrom);
			while ($r1 == $r2) {
				$r2 = mt_rand($start,$end);
			}
			echo "Tuker $r1 dengan $r2<br>";
			$temp = $chrom[$r1];
			$chrom[$r1]=$chrom[$r2];
			$chrom[$r2]=$temp;
			return $chrom;
		}

		function selectionRW($fitness)
		{
			$utils = new Utils();
			// DEBUG
			echo "Fitness Collection:";
			var_dump($fitness);
			$total_fitness = 0;
			foreach ($fitness as $line) {
				$total_fitness += $line;
			}
			echo "<br>Total Fitness: $total_fitness";
			$random_float_num = $this->random_float(0,$total_fitness);
			echo "<br>Random Float Num: $random_float_num";
			$partial_sum = 0;
			$i = 0;
			foreach ($fitness as $line) {
				$partial_sum+=$line;
				echo "<br>partial sum: $partial_sum <br>";
				if ($partial_sum >= $random_float_num) {
					echo "index selected for this generation by roulette wheel= $i<br>";
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
					array_push($chrom_int, $chromosom[$i]);
				}
				$i++;
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
			echo "Dilakukan proses verifikasi pada, berikut ini merupakan kromosom awal sebelum perombakan <br>------------------<br>";
			echo $this->my_print_r2($chromosom);
			$i = 0;
			$a = sizeof($chromosom)-1;
			$b = sizeof($chromosom);
			$batas_akhir = $a-1;
			$ar_int = [];
			$failed_index_counter = 0;
			while ($i < $b) {
				// cek dimana $i bukan di posisi array yg fitness
				if ($i!=$a) {
					// cek dimana $i bukan di posisi array yang berisi origin dan destinasi akhir
					if ($i!=0&&$i!==$batas_akhir) {
						$dec = $chromosom[$i];
						$same_dest_index = $this->utils-> checkIfCitySame($ar_int,$dec);
						if ($same_dest_index!== false) {
							echo "<br>Kota ke - $same_dest_index double <br>";
							$failed_index = $same_dest_index;
							echo $this->my_print_r2($ar_int);
							break;
						}
						array_push($ar_int, $dec);
						// var_dump($ar_int);
						// if (!$this->utils->verifikasi($dec)&&$dec!=0) {
						// 	$failed_index = $failed_index_counter;
						// 	echo "<br>Tidak ditemukan objek wisata ke- $failed_index_counter - ($dec)<br>";
						// 	echo $this->my_print_r2($chromosom);
						// 	break;
						// }
						$failed_index_counter ++;
					}
				}
				$i++;
			}
			$new_chromosom = $chromosom;
			
			// fixing broken chromosome
			if (isset($failed_index)) {
				echo "Ditemukan failed binary pada destinasi ke-($failed_index), melakukan penggantian sparepart<br>------------------";
				// kalo tanpa jam
				$failed_index = $failed_index+1;
				// $failed_index_end = $failed_index+$this->digit;
				echo "<br>1) Generate destinasi baru<br>";
				// $last_index_objek = sizeof($this->objek_wisata)-1;
				// $index_randomized_city = mt_rand(0,$last_index_objek);
				$index_randomized_city 	= array_rand($this->objek_wisata);
				$randomized_city = $this->checkIfSame($ar_int,$this->objek_wisata[$index_randomized_city],$this->objek_wisata);
				echo "- Destinasi Baru = $randomized_city<br>";
				// $new_city_binary = $this->utils->dectobin($randomized_city, $this->digit);
				// $new_city_binary_index = 0;
				echo "<br>2) Ganti digit tik tok<br>";
				$chromosom[$failed_index] = $randomized_city;
				// while ($failed_index < $failed_index_end) {
				// 	$chromosom[$failed_index] = $new_city_binary[$new_city_binary_index];
				// 	$failed_index ++;
				// 	$new_city_binary_index ++;
				// }
				echo $this->my_print_r2($chromosom);
				echo "<br>3) Yayy! Brand new kromosom, hopefully sudah benar yak!<br>";
				echo $this->my_print_r2($chromosom);
				return $chromosom;
			}
			// var_dump($chromosom);
			# Apabila benar / lolos verifikasi
			return false;
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
	$berapa_populasi = 10;
	$waktu = 4;
	$yang_mau_dikunjungi = 1;
	$generasi = new Generasi($berapa_populasi,$waktu,$yang_mau_dikunjungi);
	$start = microtime(true);
	echo "<pre>"; 
	print_r($generasi->runGA(""));
	echo "</pre>";
	$time_elapsed_secs = microtime(true) - $start;
	echo "<br>Exec Time ".$time_elapsed_secs;
	// DONE : VERIFIKASI SETELAH MUTASI DAN CROSSOVER
	// WHY WE NEED TO VERIF NAPA GA PAKE PERMUTASI AJA ????
	// Kenapa perlu banyak generasi? 1 generasi dan banyak generasi tidak membawa perubahan yg signitifikan
?>