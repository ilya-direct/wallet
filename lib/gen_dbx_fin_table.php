<?php
function include_script($path){
	if (file_exists($path) && is_readable($path)) {
		include_once($path);
	} else {
		$debug=debug_backtrace()[0];
		throw new Exception('Included file \''.
			$path.'\' doesn\'t exists or is not readable. Included in: '
			.$debug['file'].' line: '.$debug['line']);
	}
}
$exception=false;
$start = microtime(true);
try{
	include_script(__DIR__.'/mysqli_db.class.php');
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
}catch(Exception $e){
	$exception=true;
	$err_msg=$e->getMessage()."\n";
	$time=microtime(true) - $start;
}finally{
	$finish=microtime(true);
	if($exception){
		$status='ERROR';
	}else{
		$status='OK';
		$time=$finish - $start;
	}
	$file=fopen(__DIR__.'/../records.log','ab+');
	fwrite($file,date('Y-m-d H:i:s').' '.__FILE__.' '.$time.' '.$status."\n");
	if($exception) fwrite($file,$err_msg."\n");
	fclose($file);
}