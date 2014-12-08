<?php

require_once('../lib/mysqli_db.class.php');
require_once('../lib/methods.php');
$DB=new mysqli_DB();


$file='fin/nov2014.csv';
$h=fopen($file,'r');
$header=fgetcsv($h,null,';');

$records=array();

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