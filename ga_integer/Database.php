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
	public function selectObjekBaliSelatan()
	{
		$utils = new Utils();
		$sql = "SELECT dest_id FROM dest WHERE dest_area = 'A'";
		$query = $this->db->query($sql);
		$res = $query->fetchAll(\PDO::FETCH_NUM);
		// return $res;
		return $utils->array_flatten($res);
	}
	public function selectObjekBaliUtara()
	{
		$utils = new Utils();
		$sql = "SELECT dest_id FROM dest";
		$query = $this->db->query($sql);
		$res = $query->fetchAll(\PDO::FETCH_NUM);
		// return $res;
		return $utils->array_flatten($res);
	}
}

?>