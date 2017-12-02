<?php
	/**
	* 
	*/
	class Utils
	{
		
		function __construct()
		{
			
		}
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
		// REF : https://stackoverflow.com/questions/6785355/convert-multidimensional-array-into-single-array
		public function array_flatten($array) { 
		  if (!is_array($array)) { 
		    return FALSE; 
		  } 
		  $result = array(); 
		  foreach ($array as $key => $value) { 
		    if (is_array($value)) { 
		      $result = array_merge($result, array_flatten($value)); 
		    } 
		    else { 
		      $result[$key] = $value; 
		    } 
		  } 
		  return $result; 
		} 
	}
?>