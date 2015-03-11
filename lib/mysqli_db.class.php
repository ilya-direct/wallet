<?php
defined('CONFIG') or die();
class  mysqli_db{
	static private $_instance=null;
	private $_DB;
	private $db_name;
	public static function get_instance(){
		if(is_null(self::$_instance)){
			self::$_instance=new self();
		}
		return self::$_instance;
	}
	private function __construct(){
		global $CFG;
		$this->db_name=$CFG->dbname;
		mysqli_report(MYSQLI_REPORT_STRICT);
		$this->_DB=new mysqli($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$this->db_name,$CFG->dbport);
		$this->execute_query("SET NAMES 'utf8'");// кодировка
	}
	private function __clone()    { }  // Защищаем от создания через клонирование
	private function __wakeup()   { }  // Защищаем от создания через unserialize
	private function execute_query($sql){
		//debug_print_backtrace();
		//implode("\n",debug_backtrace());
		if(($result = $this->_DB->query($sql)) === false){
			//debug_print_backtrace();
			throw new Exception("Query error ". $this->_DB->errno." : ".$this->_DB->error."\n".$sql);
		}
		return $result;
	}
	public function get_records_sql($sql){
		$mysql_result=$this->execute_query($sql);
		$result=array();
		while(($rec=$mysql_result->fetch_object())!==NULL){
			$result[]=$rec;
		}
		if (empty($result)) return array();
		return $result;
	}
	public function get_records($table,array $conditions=array()){
		$conditions=$this->conditions_to_sql($conditions);
		if(empty($conditions)) $where=''; else $where='where ';
		$sql="select * from {$table} {$where} {$conditions}";
		return $this-> get_records_sql($sql);
	}
	public function get_record_sql($sql){
		$mysql_result=$this->execute_query($sql);
		if($mysql_result->num_rows>1) trigger_error("more than one record\n$sql");
		if($mysql_result->num_rows===0) return false;
		return $mysql_result->fetch_object();
	}
	public function get_record($table,array $conditions=array()){
		$where=$this->conditions_to_sql($conditions);
		$sql="select * from {$table} where {$where}";
		return $this->get_record_sql($sql);
	}
	public function record_exists_sql($sql){
		$mysql_result=$this->execute_query($sql);
		return $mysql_result->num_rows>0 ? true : false;
	}
	public function record_exists($table,$where){
		$where=$this->conditions_to_sql($where);
		$sql="select * from {$table} where {$where}";
		return $this->record_exists_sql($sql);
	}
	public function insert_record_sql($sql){
		$mysql_result=$this->execute_query($sql);
		return $this->_DB->insert_id;
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
		if($this->get_record_sql($sql)){
			return $this->get_record_sql($sql)->$return;
		}else
			return false;
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
		if ($mysql_result->field_count>1) trigger_error("more than one field returned",E_USER_ERROR);
		$field_name=$mysql_result->fetch_field()->name;
		$result_array=array();
		while(($row=$mysql_result->fetch_object())!==null){
			$result_array[]=$row->$field_name;
		}
		return $result_array;
	}
	public function get_fieldset($table,$return,array $where=array()){
		$where=$this->conditions_to_sql($where);
		return $this->get_fieldset_sql("select {$return} from {$table} where {$where}");
	}

	public function insert_record($table,$keys){
		//$keys=(array)$keys;
		$cols='';
		$values='';
		foreach($keys as $name => $val){
			$cols.=$name.',';
			if(is_numeric($val))
				$values.=$val.',';
			else
				$values.='\''.$val.'\',';
		}
		$cols=rtrim($cols,',');
		$values=rtrim($values,',');
		$sql="insert into {$table} ({$cols}) values({$values})";
		return (int)$this->insert_record_sql($sql);
	}
	public function update_record($table,$obj){
		$obj=(object) $obj;
		$this->$_DB->select_db('INFORMATION_SCHEMA');
		$primary=$this->get_field('COLUMNS','COLUMN_NAME',array(
			'TABLE_SCHEMA' => $this->db_name,
			'TABLE_NAME' => $table,
			'COLUMN_KEY' => 'PRI'));
		$this->$_DB->select_db($this->db_name);
		if(property_exists($obj,$primary)){
			$where=$this->conditions_to_sql(array("{$primary}"=>$obj->$primary));
			unset($obj->$primary);
			$set=$this->conditions_to_sql($obj,',');
			$this->execute_query("update {$table} set {$set} where {$where}");
		}
	}
	public function set_field($table,$field,$value,$conditions){
		//$where=$this->conditions_to_sql($conditions);
		$obj=$this->get_record($table,$conditions);
		if($obj===false) return false;
		$obj->$field=$value;
		$this->update_record($table,$obj);
	}
	public function get_field_info($sql){
		/* Получим информацию обо всех столбцах */
		//$finfo = $mysqli_result->fetch_fields();
		//var_dump($finfo);
	}
	public function delete_record_sql($sql){
		$this->execute_query($sql);
	}
	private function conditions_to_sql($conds,$separator='and'){
		$str='';
		foreach($conds as $name=>$value){
			if($str!=='') $str.=' '.$separator;
			if(is_numeric($value))
				$str.=" `{$name}`={$value}";
			else
				$str.=" `{$name}`='{$value}'";
		}
		return $str;
	}
}