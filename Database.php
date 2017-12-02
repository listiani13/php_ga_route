<?php
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
}
?>