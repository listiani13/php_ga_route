<?php
	require_once 'Individu.php';
	require_once 'Utils.php';
	/**
	* 
	*/
	class Generasi
	{
		
		
		function __construct($pops, $time)
		{
			define('MUTATION_RATE', 0.04);
			define('CROSSOVER_RATE', 0.5);
			$this->population = $pops;
			$this->time = $time;

		}
		function runGA()
		{
			$utils = new Utils();

			$population = $this->generatePops();
			// $a = $utils->array_flatten($population[1]);
			$fitness_collection = [];
			foreach ($population as $line) {
				array_push($fitness_collection, end($line));
			}
			$available_to_mutate = MUTATION_RATE*$this->population;
			// for development purpose ! << DELETED SOON >>
			if ($available_to_mutate < 1) {
				$available_to_mutate = 1;
			}
			for ($i=0; $i < $available_to_mutate; $i++) { 
				$random_pops_index = mt_rand(1,($this->population-1));
				
				// $pops_mutated = $this->mutationSwap($population[$random_pops_index]);
				echo "Population that has been mutated : <br>";
				echo "Pops before <br>";
				var_dump($population[$random_pops_index]);
				echo "Pops after <br>";
				var_dump($this->abab($population[$random_pops_index]));
			}

		}
		public function abab($array) { 
		  if (!is_array($array)) { 
		    return FALSE; 
		  } 
		  $result = array(); 
		  foreach ($array as $key => $value) { 
		    if (is_array($value)) { 
		      $result = array_merge($result, $this->abab($value)); 
		    } 
		    else { 
		      $result[$key] = $value; 
		    } 
		  } 
		  return $result; 
		} 
		function generatePops()
		{
			$kromosom = new Individu($this->time);
			$population = [];
			// $fitness_collection = [];
			for ($i=0; $i < $this->population; $i++) { 
				array_push($population, $kromosom->generateChrom());
			}
			// foreach ($population as $line) {
			// 	array_push($fitness_collection, end($line));
			// }
			// var_dump(array_map(function($line){ return $line[sizeof($this)]; }), $population);
			// $selected_index = $this->selectionRW($fitness_collection);
			// echo "<br> selected_index : $selected_index";
			// echo "<br> isinya : ";
			// var_dump($fitness_collection[$selected_index]);
			// return $selected_index;
			return $population;
		}
		public function selectionRW($fitness)
		{
			$utils = new Utils();
			echo "Fitness :";
			var_dump($fitness);

			$total_fitness = 0;
			foreach ($fitness as $line) {
				$total_fitness += $line;
			}
			echo "<br>Total Fitness: $total_fitness";
			$random_float_num = $this->random_float(0,$total_fitness);
			echo "<br> Random Float Num: $random_float_num";
			$partial_sum = 0;
			$i = 0;
			foreach ($fitness as $line) {
				$partial_sum+=$line;
				if ($partial_sum >= $random_float_num) {
					return $i;
				}
				$i++;
			}
		}
		public function crossoverPM($chrom_parent1, $chrom_parent2)
		{
			
		}
		public function mutationSwap($chrom)
		{
			$length = sizeof($chrom);
			echo "<br>Panjang : $length<br>";
			// -5 = -1 (karena array indexnya mulai dari 0) -4 (karena 4 index terakhir isinya ngurah rai yg ga bs diganggu gugat)
			$r1 = mt_rand(4,$length-5);
			$r2 = mt_rand(4,$length-5);
			while ($r1 == $r2) {
				$r2 = mt_rand(4,$length-5);
			}
			$temp = $chrom[$r1];
			$chrom[$r1]=$chrom[$r2];
			$chrom[$r2]=$temp;
			return $chrom;
		}
		function random_float ($min,$max) {
		    return ($min + lcg_value()*(abs($max - $min)));
		}
	}
	$generasi = new Generasi(3,4);
	$start = microtime(true);
	echo "<pre>"; 
	print_r($generasi->runGA());
	echo "</pre>";
	$time_elapsed_secs = microtime(true) - $start;
	echo "<br>Exec Time ".$time_elapsed_secs;

?>