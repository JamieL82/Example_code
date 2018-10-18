<?php
	
	/****
	*	$credentials = array("servername"=>< servername/ip >,"dbname"=>< database name >,"username"=>< username >, "password"=>< password >);
	*	$DB_class = Data_Singleton::getInstance($credentials); 
	****/
	
	class Sql_Singleton {
	
		private $parameters = array();
		private $sql_query = "";
		private $sql_error = "";
		private $pdo;
		private $type = NULL;
		
		private $insertid = 0;
		private $mysqli;
		private $result;
		private $resultrows;
		
		private static $instance = NULL;
		
		// Create connection constructor
		function __construct($credentials){
			
			//server address / database
			$dsn = "mysql:host=" . $credentials['servername'] . ";dbname=" . $credentials['dbname'] . ";charset=utf8";
			//pdo options
			$opt = array(
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
			);
			
			//generate pdo obj
			$this->pdo = new PDO($dsn, $credentials['username'], $credentials['password'], $opt);
		
		}
		
		public static function getInstance($credentials){
			if(is_null(self::$instance)){
				self::$instance = new self($credentials);
			}
			return self::$instance;
		}
		
		//getters
		
		public function getSql(){
			//return stored sql string
			return $this->sql;
		}
		
		public function getResult(){
			//return stored result
			return $this->result;
		}
		
		public function getLastInsertId(){
			//return last insert ID
			return $this->insertid;
		}
		
		public function getErrorInfo(){
			//return sql error information
			return $this->sql_error;
		}
		
		//general setter fxns
		
		public function addParameter($param){
			//add PDO param value to list
			array_push($this->parameters,$param); 
		}
		
		public function clearData(){
			//clear all params / reset
			$this->data = array();
		}
		
		
		public function runSql($query){
			
			$this->sql_query = $query;
			//run SQL Query
			$stm = $this->pdo->prepare($this->sql_query);
			$result = $stm->execute($this->parameters);
			
			//if query action type not set
			if(!isset($this->type)){
				//then get from query
				$this->setTypeFromSQLString();
			}
			
			switch($this->type){
				case("select"):
					$this->result = $stm->fetchAll();
				break;
				case("update"):
					$this->result = $result;
				break;
				case("insert"):
					//if row affected set result to true.
					$this->result = $stm->rowCount() ? true : false;
					$this->insertid = $this->pdo->lastInsertId();
				break;
				case("delete"):
					$this->result = $result;
				break;
				
			}
			
			$this->sql_error = $stm->errorInfo();
			$this->resultrows = $stm->rowCount();
			$this->parameters = array(); 
			$this->type = NULL;
			
		}
		// prevent multiple sql singletons
		private function __clone(){}
		
		private function setTypeFromSQLString(){
			//split query
			$sql_string_array = explode(" ", $this->sql_query);
			//set type
			$this->type = strtolower($sql_string_array[0]);
		}
		
		
		
	
	}
?>