<?php
header("Content-Type: text/html; charset=utf-8");
// require_once('lib/smarty/Smarty.class.php');
require_once(__DIR__.'/../../config.php');
//$smarty = new Smarty();
$DB=mysqli_db::get_instance();

$y=2014;
$m=1;
for($y=2014;;$y++){
	for($m=1;$m<=12;$m++){
		$table=array();
		$header=$DB->get_fieldset_sql("select distinct tc.name
			from record r join item i on r.itemid=i.id
				join transaction_category tc on tc.id=r.tcategory
				where year(r.time)={$y} and
					month(r.time)={$m} and
			(tc.name like 'p_%' or  tc.name like 'm_%') order by tc.sort");
		if(!$header){
			echo "$y $m  no records\n";
			if(((int)date('Y'))==$y and ((int)date('m'))==$m) die("finished $y $m\n");
			continue;
		}
		array_unshift($header,'date');
		add_desc($header);
		add_day_sum($header);
		$row=new stdClass();
		$maxday=(int) date('d',mktime(0,0,0,$m+1,0,$y));
		for($d=1; $d<=$maxday;$d++){
			foreach($header as $cat){
				if(strpos($cat,'_desc')!==false) continue;
				if($cat=="date"){
					$row->$cat="{$d}.{$m}.{$y}";
					continue;
				}
				if($cat=="p_sum"){
					$row->$cat=0;
					continue;
				}
				if($cat=="m_sum"){
					$row->$cat=0;
					continue;
				}
				if(strpos($cat,'_multiple')===false){
					$result=$DB->get_field_sql("select sum(r.sum)
				from record r join transaction_category tc on tc.id=r.tcategory
    			where year(r.time)=$y and month(r.time)=$m and
    				day(r.time)=$d and tc.name='{$cat}'");
					$row->$cat=($result===false) ? '' : $result;
					continue;
				}else{
					$result=$DB->get_record_sql("select group_concat(i.name  SEPARATOR '|') as 'desc',group_concat(r.sum SEPARATOR '|') as 'sum'
				from record r join item i on r.itemid=i.id
	                join transaction_category tc on tc.id=r.tcategory
	            where year(r.time)=$y and month(r.time)=$m and day(r.time)=$d and tc.name='{$cat}'");
					$row->$cat=($result===false) ? '' : $result->sum;
					$row->{str_replace('_multiple','_desc',$cat)}=($result===false) ? '' : $result->desc;
					continue;
				}
			}

			$table[$d]=clone $row;
		}
		foreach($header as $cat){
			if( !preg_match('/^[pm]_/i',$cat) or preg_match('/_desc$/i',$cat))
				$row->$cat='';
			else{
				$result=$DB->get_field_sql("select sum(r.sum) from record r
			join transaction_category tc on tc.id=r.tcategory
	            where year(r.time)={$y} and month(r.time)={$m} and tc.name='{$cat}'");
				$row->$cat=($result===false) ? '' : $result;
			}
		}
		$table['sum']=clone $row;
		$fname="../finance_output/{$y}.{$m}.csv";
		$file_handle=fopen($fname,"w+");
		if (!$file_handle) die("cannot create file $fname");
		$header_values=header_to_values($header);
		fputcsv($file_handle,$header_values);
		foreach($table as $row){
			fputcsv($file_handle,(array) $row);
		}
		echo "$y $m\n";
		if(((int)date('Y'))==$y and ((int)date('m'))==$m) die("finished $y $m");
	}
}


/*
$smarty->assign('table',$header);
$smarty->assign('cards',$table);
$smarty->display('main.tpl');
*/
function add_desc(&$arr){
	for($i=0;$i<count($arr);$i++){
		if(strpos($arr[$i],'_multiple')!==false){
			$desc=str_replace('_multiple','_desc',$arr[$i]);
			array_splice($arr,$i+1,0,array($desc));
		}
	}
}
function add_day_sum(&$arr){
	$i=0;
	while(preg_match('/^p_/',$arr[$i]) or $arr[$i]=="date"){
		$i++;
	}
	array_splice($arr,$i,0,'p_sum');
	array_push($arr,'m_sum');
}
function header_to_values($arr){
	Global $DB;
	foreach($arr as &$val){
		if(in_array($val,array('m_sum','p_sum'))){
			$val='Всего';
			continue;
		}
		if($DB->record_exists('transaction_category',array('name'=>$val,'deleted'=>0)))
			$val=$DB->get_field('transaction_category','value',array('name'=>$val,'deleted'=>0));
		else
			$val='';
	}
	return $arr;
}