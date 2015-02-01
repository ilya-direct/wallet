<?php
if(!defined('EXEC')) throw new Exception('undef constant EXEC');

include_once(__DIR__.DIRECTORY_SEPARATOR.'mysqli_db.class.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'methods.php');

$DB=new mysqli_DB();

$input_path=__DIR__.'/../finance_csv/';
if(!is_dir($input_path))
	throw new Exception('can\'t find input directory in csv2db.php');
$input_path=realpath($input_path);

$recs=$DB->get_records('dbx_finance',array('exists'=>1,'csv_converted'=>1,'in_db'=>0));

foreach($recs as $rec){
	$file_name=$rec->year.'.'.$rec->month;
	$input_filepath=($input_path.'/'.$file_name.'.csv');
	if(!file_exists($input_filepath)){
		$DB->set_field('dbx_finance','exists',0,array('id'=>$rec->id));
		echo "file $input_filepath not found\n";
		continue;
	}
	$file_handle=fopen($input_filepath,'r');
	if(!$file_handle) {
		echo("Не удалось открыть файл $input_filepath!");
		continue;
	}
	$headers_1=get_table_headers($file_handle,"ТС по расчету");

	if ($headers_1===false) die("Строка с корректировкой баланса не найдена");
	array_to_utf8($headers_1);
	fix_headers($headers_1);

	$headers_2=get_table_headers($file_handle,"Дата");
	if ($headers_2===false) die("Строка с основным заголовком не найдена");
	array_to_utf8($headers_2);
	fix_headers($headers_2);

	$headers=merge_headers($headers_1,$headers_2);
	unset($headers_1); unset($headers_2);
	$date_index=array_search('date',$headers);
	if ($date_index===false) die('В заголовках нет даты!');

	$maxday=date('d',mktime(0,0,0,$rec->month+1,0,$rec->year));

	for($current_day=1;$current_day<=$maxday;$current_day++){
		$data=fgetcsv($file_handle,null,';');
		$date=$data[$date_index];
		if(!preg_match('/^([\d]{2})\.([\d]{2})\.([\d]{4})$/',$date,$matches)) continue;
		if($matches[1]!=$current_day || $matches[2]!=$rec->month || $matches[3]!=$rec->year) {
				die('Дата '.$date.' не совпадает с ожидаемой в файле '.$input_filepath);
		}
		$date="{$rec->year}.{$rec->month}.{$current_day}";
		array_to_utf8($data);
		for($i=0;$i<count($headers);$i++){
			if ($headers[$i]===false) continue;
			$data[$i]=trim($data[$i]);
			$header_parts=explode('_',$headers[$i]);
			if (count($header_parts)==1){
				if($headers[$i]=='realmoney'){
					if(empty($data[$i])){
						$DB->delete_record_sql("delete from balance_check where date='$date'");
						continue;
					}
					$table='balance_check';
					$params=array('date'=>$date,
						'consider'=>$data[array_search('countmoney',$headers)],
						'realmoney'=>$data[$i],
						'diff'=>$data[array_search('difference',$headers)]);
					if($DB->record_exists($table,$params)){
						$DB->update_record($table,$params);
					}else{
						$DB->insert_record($table,$params);
					}
					unset($table); unset($params);
				}
				continue;
			}
			if(empty($data[$i])){
				delete_transactions($date,$headers[$i]);
				continue;
			}
			$sign=$header_parts[0];
			if(count($header_parts)===3 && $header_parts[2]=='desc'){
				continue;
			}elseif(count($header_parts)===3 && $header_parts[2]=='multiple'){
				$coins=explode('|',$data[$i]);
				$coins_desc=explode('|',$data[$i+1]);
				if (count($coins)!=count($coins_desc)) die("Неверная запись {$data[$i]} {$data[$i+1]}");
				$entries=array();
				for($j=0;$j<count($coins);$j++){
					$entry=new stdClass();
					if(empty($coins_desc[$j])) die("Нет описания $date {$data[$i]} {$data[$i+1]}");
					$entry->sum=$coins[$j];
					$entry->item=$coins_desc[$j];
					$entries[]=clone $entry;
				}
				insert_transaction_multiple($date,$headers[$i],$entries);
				unset($entry); unset($entries);
			}elseif(count($header_parts)===2){
				$item=$DB->get_field('transaction_category','value',array('name'=>$headers[$i],'deleted'=>0));
				insert_transaction_single($date,$headers[$i],$data[$i],$item);
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
			$max_date="$rec->year.$rec->month.$maxday";
			if($data[$date_index]=='Корректировка' && ($max_date<date("Y.m.d")))
				insert_transaction_single($max_date,'correcting',$data[$date_index+1],$data[$date_index],true);
		}
	}
	/*
	foreach($flags as $flag => $value){
		if ($total_flag & $flag){
			echo "$value : ok \n";
		}else{
			echo "$value : false \n";
		}
	}
	*/
	if (!($total_flag & 0b00001)) die('Отсутствуют данные о корректировке '."$rec->year-$rec->month-$maxday");

	fclose($file_handle);
	$DB->set_field('dbx_finance','in_db',1,array('id'=>$rec->id));
}
delete_items_without_rec();
//echo "finished\n";

function get_table_headers(&$handle,$needle){
	if (is_null($needle) or empty($needle)) return false;
	while(($data=fgetcsv($handle,null,';'))!==false){
		foreach($data as $cell){
			to_utf8($cell);
			if ($cell===$needle){
				return $data;
			}
		}
	}
	return false;
}


function fix_headers(&$headers){
	Global $DB;
	for($i=0; $i<count($headers); $i++){
		if($DB->record_exists('transaction_category',array('value'=>$headers[$i]))){
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
		}
		$add_th=[
			'Дата' => 'date',
			'ТС по расчету'=>'countmoney',
			'ТС по деньгам'=>'realmoney',
			'Разница'=>'difference'
		];
		if(array_key_exists($headers[$i],$add_th)){
			$headers[$i]=$add_th[$headers[$i]];
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
function merge_headers($h1,$h2){
	$n=(count($h1)>count($h2)) ? count($h1) : count($h2);
	$result=array();
	for($i=0; $i<$n ;++$i){
		if(!isset($h1[$i]) or !isset($h2[$i])){
			if(!isset($h1[$i]))
				if(!isset($h2[$i]))
					$result[$i]=false;
				else
					$result[$i]=$h2[$i];
			else $result[$i]=$h1[$i];
			continue;
		}
		if( ($h1[$i]===false) and ($h2[$i]===false)){
			$result[$i]=false;
		}elseif(($h1[$i]===false) and ($h2[$i]!==false)){
			$result[$i]=$h2[$i];
		}elseif(($h1[$i]!==false) and ($h2[$i]===false)){
			$result[$i]=$h1[$i];
		}else{
			die("Конфликт заголовков таблицы при слиянии $h1[$i] $h2[$i]");
		}
	}
	return $result;
}
function delete_items_without_rec(){
	Global $DB;
	$ids=$DB->get_fieldset_sql('select distinct itemid from record');
	$DB->delete_record_sql('delete from item where id not in ('.implode(',',$ids).')');
}