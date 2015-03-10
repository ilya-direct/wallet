<?php

require_once('../lib/mysqli_db.class.php');

$DB=mysqli::get_instance();

$file_h=fopen('names/boys_ru.txt','r');
while(!feof($file_h)){
	$name=fgets($file_h);
	$name=trim($name);
	if(!$DB->record_exists('names',array('name_i'=>$name)))
		$DB->insert_record('names',array('name_i'=>$name));
}