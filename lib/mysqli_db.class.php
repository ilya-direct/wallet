<?php

class  mysqli_DB{
	public $DB;
	private function execute_query($sql){
		$result = $this->DB->query($sql)
			or die ("Query error ". $this->DB->errno." : ".$this->DB->error."{\n".$sql."\n}");
		return $result;
	}
	function mysqli_DB(){
		$this->DB=new mysqli('localhost','root','','wallet',3306);
		/* проверяем соединение */
		if (mysqli_connect_errno()) {
			printf("Error description: %s ; Error code: %s ;\n",
				$this->DB->connect_error,$this->DB->connect_errno);
			exit();
		}
		$this->execute_query("SET NAMES 'utf8'"); // кодировка
	}
	public function get_records_sql($sql){
		$mysql_result=$this->execute_query($sql);
		$result=array();
		while(($rec=$mysql_result->fetch_object())!==NULL){
			$result[]=$rec;
		}
		if (empty($result)) return false;
		return $result;
	}
	public function get_record_sql($sql){
		$mysql_result=$this->execute_query($sql);
		if($mysql_result->num_rows>1) trigger_error("more than one record");
		if($mysql_result->num_rows===0) return false;
		return $mysql_result->fetch_object();
	}

	public function record_exists($sql){
		$mysql_result=$this->execute_query($sql);
		return $mysql_result->num_rows>0 ? true : false;
	}

	public function insert_record_sql($sql){
		$mysql_result=$this->execute_query($sql);
		return $this->DB->insert_id;
	}
	public function get_field($table,$return,$conditions=array()){
		$select="select `{$return}` ";
		$from="from `{$table}` ";
		$where='where ';
		foreach($conditions as $name=>$value){
			if($where!=='where ') $where.=' and';
			if(is_numeric($value))
				$where.=" `{$name}`={$value}";
			else
				$where.=" `{$name}`='{$value}'";
		}
		if(empty($conditions)) $where='';
		$sql=$select.$from.$where;
		return $this->get_record_sql($sql)->id;
	}

	public function get_field_sql($sql){
		$mysql_result=$this->execute_query($sql);;
		if ($mysql_result->num_rows==0) return false;
		if ($mysql_result->num_rows>1) trigger_error("more than one record");
		if ($mysql_result->field_count>1) trigger_error("more than one field returned",E_USER_ERROR);
		$field=$mysql_result->fetch_field();
		$return=$field->name;
		if (preg_match('/^int/i',$field->type)){
			return (int) $mysql_result->fetch_object()->$return;
		}else{
			return (string) $mysql_result->fetch_object()->$return;
		}
	}

	public function get_fieldset_sql($sql){
		$mysql_result=$this->execute_query($sql);
		if ($mysql_result->num_rows==0) return false;
		if ($mysql_result->field_count>1) trigger_error("more than one field returned",E_USER_ERROR);
		$field_name=$mysql_result->fetch_field()->name;
		$result_array=array();
		while(($row=$mysql_result->fetch_object())!==null){
			$result_array[]=$row->$field_name;
		}
		return $result_array;
	}

	public function insert_record($table,$keys){
		//$keys=(array)$keys;
		$cols='';
		$values='';
		foreach($keys as $name => $val){
			$cols.=$name.',';
			if(is_numeric($val))
				$values=$val.',';
			else
				$values='\''.$val.'\',';
		}
		$cols=rtrim($cols,',');
		$values=rtrim($values,',');
		$sql="insert into {$table} ({$cols}) values({$values})";
		return (int)$this->insert_record_sql($sql);
	}
}