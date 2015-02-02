<?php
if(!defined('EXEC')) throw new Exception('undef constant EXEC');
require_once('../lib/mysqli_db.class.php');
$DB=new mysqli_DB();

$y=2014;
$m=1;

$start_year=2014;
$start_month=1;
$current_year=(int)date('Y');
$current_month=(int)date('m');

for($i=$start_year;$i<=$current_year;$i++){
	$j=($i==$start_year)? $start_month : 1;
	$jmax=($i==$current_year)? $current_month : 12;
	for(;$j<$jmax;$j++){
		$maxday=date('d',mktime(0,0,0,$m+1,0,$y));
		$balance_check_diff=$DB->get_field('balance_check','diff',array('date'=>"{$y}.{$m}.{$maxday}"));
		$correcting=$DB->get_record_sql("
		select r.sum from record r left join transaction_category tc on tc.id=r.tcategory
			where tc.name='correcting' and year(r.`date`)={$y} and month(r.date)={$m};");
		echo "$y $m ";
		if ($correcting->sum==$balance_check_diff){
			echo " ok!\n";
		}else{
			echo " false\n";
		}
	}
}
echo("finished at ".$current_year.' '.$jmax."\n");
