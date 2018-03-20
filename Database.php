<?php
require_once 'Utils.php';
class Database{
	public function __construct(){
		$servername = "localhost";
		$username = "root";
		$password = "";
		$this->db = new PDO("mysql:host=$servername;dbname=ga_test", $username, $password);
	}

	public function selectData($id)
	{
		$sql = "SELECT * FROM dest WHERE dest_id = $id LIMIT 1";
		$query = $this->db->query($sql);
		return $query;
	}
	// AREA A : Radius <= 65 km dari ngurah rai
	public function selectObjekWisataAreaA()
	{
		$utils = new Utils();
		$sql = "SELECT dest_id FROM dest WHERE dest_area = 'A'";
		$query = $this->db->query($sql);
		$res = $query->fetchAll(\PDO::FETCH_NUM);
		// return $res;
		return $utils->array_flatten($res);
	}
	// AREA B : Radius <= dan > 65 km dari ngurah rai
	public function selectObjekWisataAll()
	{
		$utils = new Utils();
		$sql = "SELECT dest_id FROM dest";
		$query = $this->db->query($sql);
		$res = $query->fetchAll(\PDO::FETCH_NUM);
		// return $res;
		return $utils->array_flatten($res);
	}

	public function findNearestLocation($lat, $lng)
	{
		$utils = new Utils();
		$sql = "SELECT *, MIN( 6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(" . $lng . ") ) + sin( radians(" . $lat . ") ) * sin( radians( lat ) ) ) ) AS distance FROM dest WHERE dest_id != 1 HAVING distance < 15 ";
		$query = $this->db->query($sql);
		$res = $query->fetchAll(\PDO::FETCH_ASSOC);
		return $utils->array_flatten($res);
	}
}
$db = new Database();
// $lat = '-8.816568';
// $lng = '115.092211';
// var_dump($db->findNearestLocation($lat, $lng));
?>
