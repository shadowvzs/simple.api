<?php

	namespace Model;

	class Connection 
	{
		private $con;
		public function __construct() {
			$this->open();
		}
		public function open()
		{

			$DB = [
				"HOST" => 'localhost',	
				"USER" => 'root',		
				"PASSWORD" => 'root',	
				"DATABASE" => "my_db"
			];

			$this->con = new \mysqli(
				$DB['HOST'],
				$DB['USER'],
				$DB['PASSWORD'],
				$DB['DATABASE']
			);

			if ($this->con->connect_error) {
				\Controller\App::error("Connection failed: ".$this->con->connect_error);
			} 

			$this->con->set_charset("utf8");
		}

		public function prepare($sql)
		{
			return $this->con->prepare($sql);
		}

		public function execute($sql, $flags = "", $values = [], $mode = "select")
		{

			$stmt = $this->prepare($sql);
			if (count($values) > 0) {
				$stmt->bind_param($flags, ...$values);
			}

			$stmt->execute();
			if ($mode === "select") {
				$rows = [];
				$result = $stmt->get_result();
				while($row = $result->fetch_assoc()) {
				    $rows[] = $row;
				}
				return $rows;
			} else {
				return $stmt->affected_rows > 0;
			}

		}


		public function inserted_id() {
			return $this->con->insert_id;
		}

		public function close()
		{
			$this->con->close();
		}
	}

?>