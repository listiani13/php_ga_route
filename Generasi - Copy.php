<?php
	require_once 'Individu.php';
	require_once 'Database.php';
	require_once 'Utils.php';
	/**
	* 
	*/
	class Generasi
	{
		function __construct($pops, $time, $cities_visited)
		{
			define('MUTATION_RATE', 0.04);
			define('CROSSOVER_RATE', 0.5);
			// Belum termasuk jam
			define('BATAS_AWAL', 4);
			$this->utils = new Utils();
			$this->population = $pops;
			$this->cities_visited = $cities_visited;
			$this->time = $time;
			$this->velocity = 40;

		}
		
		public function runGA()
		{
			$utils = new Utils();
			$population = $this->generatePops();
			$fitness_collection = [];
			foreach ($population as $line) {
				array_push($fitness_collection, end($line));
			}
			echo "Populasi Old<br>";
			echo $this->my_print_r2($population);

			// Crossover
			echo "Crossover<br>=====================================<br><br>";
			$population = $this->crossover($population);
			// echo $this->my_print_r2($population);

			// Mutasi
			echo "<br><br>Mutation<br>=================================<br><br>";
			$population = $this->mutation($population);
			// echo $this->my_print_r2($population);

			// Seleksi Alam
			echo "<br><br>Seleksi Alam dan yang terpilih jeng jeng<br>=================================<br><br>";
			echo $this->my_print_r2($population[$this->selection($population)]);

		}
		function generatePops()
		{
			$database = new Database();
			// jika waktu ketersediaan kurang dari 6 jam
			if ($this->time <= 6) {
				$objek_wisata = $database->selectObjekBaliSelatan();
			}
			$kromosom = new Individu($this->time,$this->cities_visited,$objek_wisata);
			$population = [];
			for ($i=0; $i < $this->population; $i++) { 
				array_push($population, $kromosom->generateChrom());
			}
			return $population;
		}

		/* GA Operators*/
		function crossover($population)
		{
			$available_to_xo = CROSSOVER_RATE*$this->population;
			for ($i=0; $i < $available_to_xo; $i+2) { 
				$rand_index_1 = mt_rand(1,($this->population-1));
				$rand_index_2 = mt_rand(1,($this->population-1));
				while ($rand_index_1 == $rand_index_2) {
					$rand_index_2 = mt_rand(1,($this->population-1));
				}
				echo "Indeks Yang Terpilih : $rand_index_1 dan $rand_index_2 Indeks: $i<br>";
				$offsprings = $this->crossoverPM($population[$rand_index_1],$population[$rand_index_2]);
				// DONE : itung fitness setelah dia berubah
				$population[$rand_index_1] = $this->generateNewFitness($offsprings[0]);
				$population[$rand_index_2] = $this->generateNewFitness($offsprings[1]);
				return $population;
			}
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
				// TRIAL
				if (!$this->verifikasiBin($pops_mutated)) {
					echo "Kromosom hasil mutasi ga lolos verifikasi, mutasi ulang<br>";
					$this->mutation($population);
				}
				$pops_mutated = $this->generateNewFitness($pops_mutated);
				// DEBUG
				echo $this->my_print_r2($pops_mutated);
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
			echo "r1 = $r1 r2 = $r2 | Chrom Slice 1:".$this->my_print_r2($chrom1_slice)."<br> Chrom Slice 2:".$this->my_print_r2($chrom2_slice)."<br>";
			// DONE 2: replace chrom1 slice ke chrom2 slice dan sebaliknya. 
			$index_slice = 0;
			$i = $r1;
			$lengthplusone = $length_selected + 1;
			while ($i <$r2 ) {
				echo "chrom_parent1 ke $i ($chrom_parent1[$i]) diganti dengan chrom2_slice ke $index_slice ($chrom2_slice[$index_slice])<br>chrom_parent2 ke $i ($chrom_parent2[$i]) diganti dengan chrom1_slice ke $index_slice $chrom1_slice[$index_slice])<br>";
				$chrom_parent1[$i] = $chrom2_slice[$index_slice];
				$chrom_parent2[$i] = $chrom1_slice[$index_slice];
				$index_slice ++;
				$i++;
			}
			$chrom_parent1 = $this->generateNewFitness($chrom_parent1);
			$chrom_parent2 = $this->generateNewFitness($chrom_parent2);
			echo "New Chrom Parent 1:".$this->my_print_r2($chrom_parent1)."<br> New Chrom Parent 2:".$this->my_print_r2($chrom_parent2)."<br>";
			$offsprings = [$chrom_parent1,$chrom_parent2];
			return $offsprings;
		}
		
		function mutationSwap($chrom)
		{
			$length = sizeof($chrom);
			$start = 4;
			$end = $length-7;
			// -5 = -1 (karena array indexnya mulai dari 0) -1 karena diambil fitness-4 (karena 4 index terakhir isinya ngurah rai yg ga bs diganggu gugat)
			$r1 = mt_rand($start,$length-$end);
			$r2 = mt_rand($start,$length-$end);
			while ($r1 == $r2) {
				$r2 = mt_rand($start,$length-$end);
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
					$binary_array = array_slice($chromosom, $i, 4);
					$dec = $utils->bintodec($binary_array);
					array_push($chrom_int, $dec);
				}
				$i+=4;
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
			$i = 0;
			$a = sizeof($chromosom)-1;
			$b = sizeof($chromosom);
			$batas_akhir = $a-4;
			$ar_int = [];
			while ($i < $b) {
				// check apakah current $i merupakan bagian fitness?
				if ($i!=$a) {
					$binary_array = array_slice($chromosom, $i, 4);
					$dec = $this->utils->bintodec($binary_array);
					if ($i!=0&&$i!==$batas_akhir) {
						// echo "Kota ke $i = $dec<br>";
						// var_dump($this->utils-> checkIfCitySame($ar_int,$dec));
						if (!$this->utils-> checkIfCitySame($ar_int,$dec)) {
							echo "<br>Kota ke - $dec double <br>";
							var_dump($ar_int);
							return false;
						}
						array_push($ar_int, $dec);
						// var_dump($ar_int);
						if (!$this->utils->verifikasi($dec)) {
							echo "<br>Tidak ditemukan objek wisata ke $dec<br>";
							var_dump($chromosom);
							return false;
						}
					}
					
				}
				$i+=4;
			}
			var_dump($chromosom);
			return true;
		}
		function random_float ($min,$max) {
		    return ($min + lcg_value()*(abs($max - $min)));
		}
		
		public function uji()
		{
			var_dump($this->verifikasiBin(["0","0","0","1","0","0","1","1","1","0","1","1","0","0","0","1","0.0081400081"]));
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
	}
	$generasi = new Generasi(4,4,2);
	$start = microtime(true);
	echo "<pre>"; 
	print_r($generasi->runGA());
	echo "</pre>";
	$time_elapsed_secs = microtime(true) - $start;
	echo "<br>Exec Time ".$time_elapsed_secs;
	// TODO : VERIFIKASI SETELAH MUTASI DAN CROSSOVER
	// WHY WE NEED TO VERIF NAPA GA PAKE PERMUTASI AJA YAK? HUFT
?>