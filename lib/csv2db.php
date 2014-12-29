<?php

require_once('../lib/mysqli_db.class.php');
require_once('../lib/methods.php');

$DB=new mysqli_DB();
$dir_path='c:/wamp/www/wallet/finance/';
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
	array_cp1251_to_utf8($headers);
	fix_headers($headers);

	$date_index=array_search('d_date',$headers);
	if ($date_index===false) die('В заголовках нет даты!');

	$maxday=date('d',mktime(0,0,0,$month+1,0,$year));

	for($current_day=1;$current_day<=$maxday;$current_day++){
		$data=fgetcsv($file_handle,null,';');
		$date=$data[$date_index];
		if(!preg_match('/^([\d]{2})\.([\d]{2})\.([\d]{4})$/',$date,$matches)) continue;
		if($matches[1]!=$current_day || $matches[2]!=$month || $matches[3]!=$year) die('Дата не совпадает с ожидаемой');
		fix_date($date);
		array_cp1251_to_utf8($data);
		for($i=0;$i<count($headers);$i++){
			if ($headers[$i]===false or $i===$date_index or empty(trim($data[$i]))) continue;
			$header_parts=explode('_',$headers[$i]);
			$sign=$header_parts[0];

			if(count($header_parts)===3 && $header_parts[2]=='desc'){
				continue;
			}elseif(count($header_parts)===3 && $header_parts[2]=='multiple'){
				$coins=explode('|',$data[$i]);
				$coins_desc=explode('|',$data[$i+1]);
				if (count($coins)!=count($coins_desc)) die("Неверная запись {$data[$i]} {$data[$i+1]}");
				for($j=0;$j<count($coins);$j++){
					if(empty($coins_desc[$j])) die("Нет описания $date {$data[$i]} {$data[$i+1]}");
					if (!transaction_exists($sign,$coins[$j],$coins_desc[$j],$date)){
						insert_transaction($sign,$coins[$j],$coins_desc[$j],$date);
					}
				}
			}elseif(count($header_parts)===2){
				$item=$header_parts[1];
				if (!transaction_exists($sign,$data[$i],$item,$date)){
					insert_transaction($sign,$data[$i],$item,$date);
				}
			}
		}
	}

	while(($data=fgetcsv($file_handle,null,';'))!==false){
		switch(iconv( 'Windows-1251','UTF-8',$data[$date_index])){
			case 'Корректировка':
				insert_transaction('x',$data[$date_index+1],'Корректировка',"$year.$month.$maxday");
				break;
			case 'Всего получено':
				break;
			case 'Всего потрачено':
				break;
			case 'Стартовый капитал':
				break;
			case 'Конечный капитал':
				break;

		}
	}


}

echo "finished\n";

function get_table_headers(&$handle){
	$date=iconv('UTF-8', 'Windows-1251',"Дата");
	while(($data=fgetcsv($handle,null,';'))!==false){
		foreach($data as $cell){
			if ($cell===$date){
				return $data;
			}
		}
	}
}


function fix_headers(&$headers){
	$fields=array(
		'Дата' => 'd_date',
		'Мама'=>'p_mom_multiple',
		'Мама (PM)'=> 'p_mompm',
		'Ученики'=> 'p_pupils',
		'Другие доходы'=> 'p_other_multiple',
		'MTI'=> 'm_mti',
		'бенз'=> 'm_petrol',
		'Моб'=> 'm_mobile',
		'iPad'=> 'm_ipad',
		'Мобила'=> 'm_mobile',
		'Гулянки'=> 'm_spend_multiple',
		'Другие расходы'=> 'm_other_multiple',
		'Универ'=>'p_university'
	);

	for($i=0; $i<count($headers); $i++){
		if(!array_key_exists($headers[$i],$fields)){
			$headers[$i]=false;
			continue;
		}
		$field_name=$fields[$headers[$i]];
		$field_array=explode('_',$field_name);
		if(count($field_array)==2){
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

function array_cp1251_to_utf8(&$arr){
	foreach($arr as &$item){
		$item=iconv( 'Windows-1251','UTF-8',$item);
	}
}

function fix_date(&$date){
	$arr=explode('.',$date);
	$date=$arr[2].'.'.$arr[1].'.'.$arr[0];

}