<?php
header("Content-Type: text/html; charset=utf-8");
// require_once('lib/smarty/Smarty.class.php');
require_once('../lib/mysqli_db.class.php');
//$smarty = new Smarty();
$DB=new mysqli_DB();
$y=2014;
$m=1;
$table=array();
$header=$DB->get_fieldset_sql("select distinct tc.name
	from record r join item i on r.itemid=i.id
		join transaction_category tc on tc.id=r.tcategory
	where year(r.time)={$y} and
			month(r.time)={$m} and
			(tc.name like 'p_%' or  tc.name like 'm_%')");
array_unshift($header,'date');
add_desc($header);
$row=new stdClass();
$maxday=(int) date('d',mktime(0,0,0,$m+1,0,$y));
for($d=1; $d<=$maxday;$d++){
	$row->date="{$d}.{$m}.{$y}";
	foreach($header as $cat){
		if(strpos($cat,'_desc')!==false or $cat=="date") continue;
		if(strpos($cat,'_multiple')===false){
			$result=$DB->get_field_sql("select sum(r.sum)
				from record r join transaction_category tc on tc.id=r.tcategory
    			where year(r.time)=$y and month(r.time)=$m and
    				day(r.time)=$d and tc.name='{$cat}'");
			$row->$cat=($result===false) ? '' : $result;
		}else{
			$result=$DB->get_record_sql("select group_concat(i.name  SEPARATOR '|') as 'desc',group_concat(r.sum SEPARATOR '|') as 'sum'
				from record r join item i on r.itemid=i.id
	                join transaction_category tc on tc.id=r.tcategory
	            where year(r.time)=$y and month(r.time)=$m and day(r.time)=$d and tc.name='{$cat}'");
			$row->$cat=($result===false) ? '' : $result->sum;
			$row->{str_replace('_multiple','_desc',$cat)}=($result===false) ? '' : $result->desc;
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
print_r($table);

$file_handle=fopen("../finance_output/1.csv","w+");
if (!$file_handle) die('cannot create file');

fputcsv($file_handle,$header);
foreach($table as $row){
	fputcsv($file_handle,(array) $row);
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