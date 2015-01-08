<?php

require_once('../lib/mysqli_db.class.php');
require_once('../lib/methods.php');

$DB=new mysqli_DB();
$dir_path='c:/wamp/www/wallet/finance_csv/';
$dir_handle=opendir($dir_path);
if (!$dir_handle) die("Не удалось открыть дерикторию!");

while (false !== ($file_name = readdir($dir_handle))) {
	echo "$file_name\n";
	if (!preg_match('/^([\d]{2})\.([\d]{4})/',$file_name,$matches)) continue;
	$month=$matches[1];
	$year =$matches[2];
	unset($matches);

	echo " $month $year\n";

	$file_path=$dir_path.$file_name;
	$file_handle=fopen($file_path,'r');
	if(!$file_handle) die("Не удалось открыть файл $file_name в дериктории $dir_path!");
	$headers=get_table_headers($file_handle);
	if ($headers===false) die("Строка с заголовком не найдена");
	array_to_utf8($headers);
	fix_headers($headers);

	$date_index=array_search('date',$headers);
	if ($date_index===false) die('В заголовках нет даты!');

	$maxday=date('d',mktime(0,0,0,$month+1,0,$year));

	for($current_day=1;$current_day<=$maxday;$current_day++){
		$data=fgetcsv($file_handle,null,';');
		$date=$data[$date_index];
		if(!preg_match('/^([\d]{2})\.([\d]{2})\.([\d]{4})$/',$date,$matches)) continue;
		if($matches[1]!=$current_day || $matches[2]!=$month || $matches[3]!=$year) die('Дата не совпадает с ожидаемой');
		$date="{$year}.{$month}.{$current_day}";
		$data[$date_index]=''; //
		array_to_utf8($data);
		for($i=0;$i<count($headers);$i++){
			if ($headers[$i]===false or empty(trim($data[$i]))) continue;
			$header_parts=explode('_',$headers[$i]);
			if ($header_parts<2) die("Неверное имя tcategory ($headers[$i]) дата $date");
			$sign=$header_parts[0];
			if(count($header_parts)===3 && $header_parts[2]=='desc'){
				continue;
			}elseif(count($header_parts)===3 && $header_parts[2]=='multiple'){
				$coins=explode('|',$data[$i]);
				$coins_desc=explode('|',$data[$i+1]);
				if (count($coins)!=count($coins_desc)) die("Неверная запись {$data[$i]} {$data[$i+1]}");
				for($j=0;$j<count($coins);$j++){
					if(empty($coins_desc[$j])) die("Нет описания $date {$data[$i]} {$data[$i+1]}");
					insert_transaction($sign,$coins[$j],$coins_desc[$j],$date,$headers[$i]);
				}
			}elseif(count($header_parts)===2){
				$item=$DB->get_field('transaction_category','value',array('name'=>$headers[$i],'deleted'=>0));
				insert_transaction($sign,$data[$i],$item,$date,$headers[$i]);
			}
		}
	}
	$flags=[
		0b00001=>'Корректировка',
		0b00010=>'Всего получено',
		0b00100=>'Всего потрачено',
		0b01000=>'Стартовый капитал',
		0b10000=>'Конечный капитал'
	];
	$total_flag= 0b00000;
	while(($data=fgetcsv($file_handle,null,';'))!==false){
		to_utf8($data[$date_index]);
		if(in_array($data[$date_index],$flags)){
			$total_flag=$total_flag | array_search($data[$date_index],$flags);
			if($data[$date_index]=='Корректировка')
				insert_transaction('x',$data[$date_index+1],$data[$date_index],"$year.$month.$maxday",'correcting');
		}
	}
	foreach($flags as $flag => $value){
		if ($total_flag & $flag){
			echo "$value : ok \n";
		}else{
			echo "$value : false \n";
		}
	}


}

echo "finished\n";

function get_table_headers(&$handle){
	$date="Дата";
	while(($data=fgetcsv($handle,null,';'))!==false){
		foreach($data as $cell){
			to_utf8($cell);
			if ($cell===$date){
				return $data;
			}
		}
	}
	return false;
}


function fix_headers(&$headers){
	Global $DB;
	for($i=0; $i<count($headers); $i++){
		if(!$DB->record_exists('transaction_category',array('value'=>$headers[$i]))){
			$headers[$i]=false;
			continue;
		}
		$field_name=$DB->get_field('transaction_category','name',array('value'=>$headers[$i]));
		$field_array=explode('_',$field_name);
		if(count($field_array)<=2){
			$headers[$i]=$field_name;
			continue;
		}elseif(count($field_array)===3 && $field_array[2]=='multiple'){
			$headers[$i]=$field_name;
			if(!array_key_exists($i+1,$headers) || $headers[$i+1]!="") {
				die('Неверный формат колонок (формат заголовка таблицы)');
			}
			$headers[++$i]=$field_array[0].'_'.$field_array[1].'_'.'desc';
			continue;
		}
		$headers[$i]=false;
	}

}
function to_utf8(&$str){
	$encoding=mb_detect_encoding($str);
	if($encoding=="UTF-8") return;
	iconv( $encoding,'UTF-8',$str);
}

function array_to_utf8(array &$arr){
	foreach($arr as &$item){
		to_utf8($item);
	}
}

function fix_date(&$date){
	$arr=explode('.',$date);
	$date=$arr[2].'.'.$arr[1].'.'.$arr[0];

}