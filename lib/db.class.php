<?php

class  DB{
	private $DB;
	static function init(){
		$this->DB=new mysqli('localhost','root','','wallet',3306);
		/* проверяем соединение */
		if (mysqli_connect_errno()) {
			printf("Ошибка соединения: %s\n", mysqli_connect_error());
			exit();
		}
	}
	public function item_exists($item){
		$item=trim($item);

	}
}