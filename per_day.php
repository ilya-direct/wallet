<?php
require_once(__DIR__.'/config.php');
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
	// error was suppressed with the @-operator
	if (0 === error_reporting()) {
		return false;
	}
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
define('EXEC',1);
$exception=false;
$start=microtime(true);
try{
	include_once($CFG->dirroot.'/lib/dbx_download.php');
	include_once($CFG->dirroot.'/lib/xlsm2csv.php');
	include_once($CFG->dirroot.'/lib/csv2db.php');
	include_once($CFG->dirroot.'/lib/balance_check.php');
}catch(Exception $e){
	$time=microtime(true) - $start;
	$exception=true;
	$err_msg=$e->getMessage();
	$backtrace=$e->getTraceAsString();
}finally{
	$finish=microtime(true);
	if($exception){
		$status='ERROR';
	}else{
		$status='OK';
		$time=$finish - $start;
	}
	$file=fopen(__DIR__.'/records.log','ab+');
	fwrite($file,date('Y-m-d H:i:s').' '.__FILE__.' '.$time.' '.$status."\n");
	if($exception){
		fwrite($file,$err_msg."\n");
		fwrite($file,$backtrace."\n");
	}
	fclose($file);
}

// upload log file to  dropbox



use \Dropbox as dbx;
$token='OprJKfb4QroAAAAAAAAAG0gfCQ7Rz-Wrg67U2dBrYQbxLx-iXwW_kvEMssAv-yay';
$client=new  dbx\Client($token,'directapp','UTF-8');

$file=fopen(__DIR__.'/records.log','r');
$client->uploadFile('/records.log',dbx\WriteMode::force(), $file);
fclose($file);