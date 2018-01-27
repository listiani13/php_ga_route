<?php
$a = file_get_contents("file:///D:/json_jarak.json");
$b = json_decode($a,true);
var_dump($b['c0']['c1']);
?>