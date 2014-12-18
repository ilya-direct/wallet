<?php

require_once('../lib/mysqli_db.class.php');
require_once('../lib/methods.php');
$DB=new mysqli_DB();


$file='fin/nov2014.csv';
$h=fopen($file,'r');

$records=array();

$fields=array(
	'Дата' => 'date',
	'Мама'=>'p_mom_multiple',
	'Мама (PM)'=> 'p_mompm',
	'Ученики'=> 'p_pupils',
	'Другие доходы'=> 'p_other_multiple',
	'MTI'=> 'm_mti',
	'бенз'=> 'm_petrol',
	'Моб'=> 'm_mobile',
	'iPad'=> 'm_mobile',
	'Мобила'=> 'm_mobile',
	'Гулянки'=> 'm_spend_multiple',
	'Другие расходы'=> 'm_other_multiple',
);
$headers=fgetcsv($h,null,';');

for($i=0; $i<count($headers); $i++){
	if(!array_key_exists($headers[$i],$fields)) $headers[$i]='undef';
	$field_name=$fields[$headers[$i]];
	$field_array=explode('_',$field_name);
	if((count($field_array)==2) or (count($field_array)==1)){
		$headers[$i]=$field_name;
	}elseif(count($field_array)===3 && $field_array[2]=='multiple'){
		$headers[$i]=$field_name;
		$headers[++$i]=$field_array[0].'_'.$field_array[1].'_'.'desc';
	}
}

while(($data=fgetcsv($h,null,';'))!==false){
	list($d,$m,$y)=explode('.',$data[0]);
	$date="{$y}.{$m}.{$d}";
	print("$date\n");
	for($i=1;$i<count($data);$i++){
		$sign=explode('_',$header[$i])[0];
		if(count(explode('_',$header[$i]))===3 && explode('_',$header[$i])[2]=='desc'){
			continue;
		}elseif(count(explode('_',$header[$i]))===3 && explode('_',$header[$i])[2]=='multiple'){
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

		}elseif(count(explode('_',$header[$i]))===2){
			if (empty(trim($data[$i]))) continue;
			$item=explode('_',$header[$i])[1];
			$itemid=item_exists($item) ? get_item_id($item) : insert_item($item);
			if (!transaction_exists($sign,$data[$i],$itemid,$date)){
				insert_transaction($sign,$data[$i],$itemid,$date);
			}
		}
	}

}