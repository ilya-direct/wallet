<?php

require_once('../lib/mysqli_db.class.php');
require_once('../lib/methods.php');
$DB=new mysqli_DB();


$file='fin/march_2014.csv';
$h=fopen($file,'r');

$records=array();

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
$headers=fgetcsv($h,null,';');
$num_fields=count($headers); // Количество полей в каждой строке в csv
array_cp1251_to_utf8($headers);

print_r($headers);
for($i=0; $i<$num_fields; $i++){
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

print_r($headers);
$date_index=array_search('d_date',$headers);
if ($date_index===false) die('В заголовках нет даты!');
print_r($date_index);


while(($data=fgetcsv($h,null,';'))!==false){
	$date=$data[$date_index];
	if(!preg_match('/^[\d]{2}\.[\d]{2}\.[\d]{4}$/',$date)) continue;
	fix_date($date);
	array_cp1251_to_utf8($data);
	for($i=0;$i<$num_fields;$i++){
		if ($headers[$i]===false) continue;
		if ($i===$date_index) continue;
		$sign=explode('_',$headers[$i])[0];
		if(count(explode('_',$headers[$i]))===3 && explode('_',$headers[$i])[2]=='desc'){
			continue;
		}elseif(count(explode('_',$headers[$i]))===3 && explode('_',$headers[$i])[2]=='multiple'){
			if (empty(trim($data[$i]))) continue;
			$coins=explode('|',$data[$i]);
			$coins_desc=explode('|',$data[$i+1]);
			if (count($coins)!=count($coins_desc)) die("Неверная запись {$data[$i]} {$data[$i+1]}");
			for($j=0;$j<count($coins);$j++){
				$itemid=item_exists($coins_desc[$j]) ? get_item_id($coins_desc[$j]) : insert_item($coins_desc[$j]);
				if (!transaction_exists($sign,$data[$i],$itemid,$date)){
					insert_transaction($sign,$data[$i],$itemid,$date);
				}
			}

		}elseif(count(explode('_',$headers[$i]))===2){
			if (empty(trim($data[$i]))) continue;
			$item=explode('_',$headers[$i])[1];
			$itemid=item_exists($item) ? get_item_id($item) : insert_item($item);
			if (!transaction_exists($sign,$data[$i],$itemid,$date)){
				insert_transaction($sign,$data[$i],$itemid,$date);
			}
		}
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