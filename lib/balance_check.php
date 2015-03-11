<?php
if(!defined('EXEC')) throw new Exception('undef constant EXEC');

include_once(__DIR__.'/../config.php');
$DB=mysqli_db::get_instance();
if(!$DB->record_exists_sql('select * from balance_check where year(date)=2013'))
	throw new Exception('not initialized with script init');

$points=$DB->get_records_sql('select * from balance_check order by date');
$point_1=array_shift($points);
$total_sum=$point_1->consider;
foreach($points as $point_2){
	$sum=$DB->get_field_sql('select sum(sum) from record where date>"'.$point_1->date.'" and date<="'.$point_2->date.'"');
	$total_sum+=$sum;

	preg_match('/^([\d]{4})\-([\d]{2})-([\d]{2})/',$point_2->date,$matches);
	$y=$matches[1]; $m=$matches[2]; $d=$matches[3];
	$maxday=date('d',mktime(0,0,0,$m+1,0,$y));
	if ($d==$maxday){
		$correction=get_correcting_sum($y,$m);
		compare_values($total_sum-$correction,$point_2->consider,$point_2->date);
	}else
		compare_values($total_sum,$point_2->consider,$point_2->date);
	$point_1=$point_2;

}
echo 'checked'."\n";

function get_correcting_sum($y,$m){
	Global $DB;
	$correcting=$DB->get_record_sql("
		select r.sum from record r left join transaction_category tc on tc.id=r.tcategory
			where tc.name='correcting' and year(r.`date`)={$y} and month(r.date)={$m};");
	return $correcting->sum;
}

function compare_values($actual,$mustbe,$date){
	if ($actual!=$mustbe)
		throw new Exception("$date Сумма по расчету: $actual Должна быть: {$mustbe} false\n");
}