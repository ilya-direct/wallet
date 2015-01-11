<?php

require_once('../lib/mysqli_db.class.php');
$DB=new mysqli_DB();

$y=2014;
$m=1;

for($y=2014;;$y++){
	for($m=1;$m<=12;$m++){
		if(((int)date('Y'))==$y and ((int)date('m'))==$m) die("\nfinished at $y.$m");
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
