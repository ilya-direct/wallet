<?php

class  mysqli_DB{
	public $DB;
	function mysqli_DB(){
		$this->DB=new mysqli('localhost','root','','wallet',3306);
		/* проверяем соединение */
		if (mysqli_connect_errno()) {
			printf("Error description: %s ; Error code: %s ;\n",
				$this->DB->connect_error,$this->DB->connect_errno);
			exit();
		}
		$this->DB->query("SET NAMES 'utf8'"); // кодировка
	}
	public function get_records_sql($sql){
		$mysql_result=$this->DB->query($sql);
		$result=array();
		while(($rec=$mysql_result->fetch_object())!==NULL){
			$result[]=$rec;
		}
		if (empty($result)) return false;
		return $result;
	}
	public function get_record_sql($sql){
		$mysql_result=$this->DB->query($sql);
		if($mysql_result->num_rows>1) trigger_error("more than one record");
		if($mysql_result->num_rows===0) return false;
		return $mysql_result->fetch_object();
	}

	public function record_exists($sql){
		$mysql_result=$this->DB->query($sql);
		return $mysql_result->num_rows>0 ? true : false;
	}

	public function insert_record_sql($sql){
		$mysql_result=$this->DB->query($sql);
		return $mysql_result->insert_id;
	}
}