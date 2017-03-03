<?php

class DB {

	private $mysqli;
	public $connected;

	public function __construct($db_host, $db_user, $db_pass, $db_name){
		$this->connected = $this->db_connect($db_host, $db_user, $db_pass, $db_name);
	}

	private function db_connect($db_host, $db_user, $db_pass, $db_name){
		$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
		if(!$mysqli->connect_errno){
			$this->mysqli = $mysqli;
			return true;
		}else{
			$dbcnx = new mysqli($dbhost, $db_user, $db_pass);    
  			if ($dbcnx){ // Exist connect with SQL-server. Create database.
  				$query_text = "CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8 COLLATE utf8_general_ci";
  				$dbcnx->query($query_text) or die($dbcnx->connect_error);
  				$dbcnx->close();
  				$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
				if(!$mysqli->connect_errno){
					$this->mysqli = $mysqli;
					return true;
				}else{
					return false;
				}
  			}else{
				return false;
			}
		}
	}

	public function all($db_table, $conditions = []){
		if (!$this->existsTable($db_table)) $this->createTable($db_table);
		$return = array();
		if(is_array($conditions)) {
			$query_text = "SELECT * FROM `{$db_table}`";
			if (count($conditions) > 0) $query_text .= " WHERE ";
			$i = 0;
			foreach ($conditions as $key => $value) {
				$query_text .= $i != 0 ? " AND " : "";
				$query_text .= "`" . $key . "`='" . $value . "'";
				$i++;
			}
			$query = $this->mysqli->query($query_text) or die($this->mysqli->error);
			$return = $query->fetch_all(MYSQLI_ASSOC);
		}else{
			throw new Exception('Should be array as CONDITIONS');
		}
		return $return;
	}

	public function one($db_table, $conditions){
		if (!$this->existsTable($db_table)) $this->createTable($db_table);
		$query_text = "SELECT * FROM `{$db_table}` WHERE ";
			$i = 0;
		$return = array();
		foreach($conditions as $key=>$value){
		   $query_text .= $i!=0 ? " AND " : "";
		   $query_text .= "`" . $key . "`='".$value."'";
		   $i++;
		}
		$query_text .= " LIMIT 1";
		$query = $this->mysqli->query($query_text) or die($this->mysqli->error);
		$return = $query->fetch_array(MYSQLI_ASSOC);
		return $return;
	}

	public function nameID($db_table, $id){
		$result = $this->one('listelems',['type'=>$db_table,'id'=>$id]);
		if (count($result)>0) {
			return $result['name'];
		}else{ // Search in listelems
			$result = $this->one($db_table, ['id'=>$id]);
			if (count($result)>0) {
				return $result['name'];
			}else{ // Search in API
				include __ROOT__."/params.php";
				$class = new ReflectionClass(ucfirst($db_table));
				$Element = $class->newInstance($params, $id, $db_table);
				return $Element->getName();
			}
		}
	}

	public function deleteId($db_table, $id){
		// DELETE FROM `films` WHERE `id`='1'	
		$query_text = "DELETE FROM `{$db_table}` WHERE `id`='{$id}'";
		$query = $this->mysqli->query($query_text);
		return $query;
	}

	public function deleteWhere($db_table, $condition){
		// DELETE FROM `listelems` WHERE `page`='1' AND `type`='films'	
		$query_text = "DELETE FROM `{$db_table}` WHERE ";
		$i = 0;
		foreach($condition as $key=>$value){
		   $query_text .= $i!=0 ? " AND " : "";
		   $query_text .= "`" . $key . "`='".$value."'";
		   $i++;
		}
		$query = $this->mysqli->query($query_text);
		return $query;
	}

	public function save($db_table, $fields, $id){
		// $fields = ARRAY
		// UPDATE `tasks` SET `id`=[value-1],`title`=[value-2],...,`completion`=[value-6] WHERE `id`='1'
		if(is_array($fields)) {
			$query_text = "UPDATE `{$db_table}` SET";
			$i = 0;
			foreach($fields as $key=>$value){
			   $query_text .= $i!=0 ? ", " : " ";
			   $query_text .= "`" . $key . "`='".$value."'";
			   $i++;
			}
			$query_text .= " WHERE `id`='{$id}'";
			$query = $this->mysqli->query($query_text);
		}else{
			throw new Exception('Should be array as FIELDS');
		}
		return $query;
	}

	public function add($db_table, $fields){
		if (!$this->existsTable($db_table)) $this->createTable($db_table);
		// INSERT INTO `products` (`Name`, `Price`) VALUES ('Phone 12345', '324.05')
		if(is_array($fields)) {
			$query_text = "INSERT INTO `{$db_table}` ";
			$listFields = "(`";
			$listValues = "('";
			$i = 0;
			foreach($fields as $key=>$value){
				$listFields .= $i!=0 ? "`, `" : "";;
				$listValues .= $i!=0 ? "', '" : "";;
				$listFields .= $key;
				$listValues .= str_replace("'", "''", $value); // Exampl: "Twi'lek" -> "Twi''lek" (for SQL)
				$i++;
			}
			$listFields .= "`)";
			$listValues .= "')";
			$query_text .= $listFields." VALUES ".$listValues;
			$query = $this->mysqli->query($query_text);
		}else{
			throw new Exception('Should be array as FIELDS');
		}
		return $query;
	}

	public function addArray($db_table, $datarray){
		if (!$this->existsTable($db_table)) $this->createTable($db_table);
		// INSERT INTO `products` (`Name`, `Price`) VALUES ('Phone 12345', '324.05'), ('Phone 47345', '132.35'), ('Fax 777', '871.64')
		$query_text = "INSERT INTO `{$db_table}` (`";
		$numbfield = 0;
		foreach($datarray[0] as $key=>$value){
			$query_text .= $numbfield!=0 ? "`, `" : "";
			$query_text .= $key;
			$numbfield++;
		}
		$query_text .= "`) VALUES ";
		foreach($datarray as $keydata=>$data){
			$query_text .= $keydata!=0 ? ", ('" : "('";
			$numbfield = 0;
			foreach ($data as $key => $value) {
				$query_text .= $numbfield!=0 ? "', '" : "";
				$query_text .= str_replace("'", "''", $value); // Exampl: "Twi'lek" -> "Twi''lek" (for SQL)
				$numbfield++;
			}
			$query_text .= "')";
		}
		$query = $this->mysqli->query($query_text);
		return $query;
	}

	private function createTable($db_table){
		$fquery = file_get_contents("SQL/".$db_table.".sql",'rt');
		$arrayquery = explode(";", $fquery);
		foreach ($arrayquery as $value) {
			if (trim($value) != "") {
				$query = $this->mysqli->query($value);
			}
		}
		return $query;
	}

	private function existsTable($db_table){
        $res = $this->mysqli->query("SELECT * FROM `".$db_table."` LIMIT 1");
        $err_no = $this->mysqli->errno;
		return ($err_no != '1146' && $res = true);
	}

}

?>