<?php
require_once('../lib/mysqli_db.class.php');
$DB=new mysqli_DB();
$start_year=2014;
$start_month=1;
$current_year=(int)date('Y');
$current_month=(int)date('m');

for($i=$start_year;$i<=$current_year;$i++){
	$j=($i==$start_year)? $start_month : 1;
	$jmax=($i==$current_year)? $current_month : 12;
	for(;$j<=$jmax;$j++){
		if(!$DB->record_exists('dbx_finance',array('year'=>$i,'month'=>$j))){
			$DB->insert_record('dbx_finance',array('year'=>$i,'month'=>$j));
		}
	}
}