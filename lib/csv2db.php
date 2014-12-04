<?php

$file='fin/nov2014.csv';
$h=fopen($file,'r');
$header=fgetcsv($h,null,';');

$records=array();

while(($data=fgetcsv($h,null,';'))!==false){
	list($d,$m,$y)=explode('.',$data[0]);
	$date="{$y}.{$m}.{$d}";
	for($i=1;$i<count(data);$i++){
		if( explode('_',$header[$i])[0]=='p' ){
			$sign=1;
		}elseif(explode('_',$header[$i])[0]=='m'){
			$sign=2;
		}else
			die('Неверно указан знак');
		if(count(explode('_',$header[$i]))===3 && explode('_',$header[$i])[2]=='desc'){
			continue;
		}elseif(count(explode('_',$header[$i]))===3 && explode('_',$header[$i])[2]=='multiple'){
			$coins=explode('|',$data[$i]);
			$coins_desc=explode('|',$data[$i+1]);
			if (count($coins)!=count($coins_desc)) die("Неверная запись");
			for($j=0;$j<count($coins);$j++){
				if(item_exists($coins_desc[$j])){
					$itemid=get_item_id($coins_desc[$j]);
				}else{
					$itemid=insert_item($coins_desc[$j]);
				}
				insert_record($sign,$coins[$j],$itemid);
			}

		}elseif(count(explode('_',$header[$i]))===2){
			$item=explode('_',$header[$i])[1];
			$itemid=item_exists($item) ? get_item_id($item) : insert_item($item);
			insert_record($sign,$coins[$j],$itemid);
		}
	}

}