<?php
define('EXEC',1);
require_once(__DIR__.'/config.php');
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
	// error was suppressed with the @-operator
	if (0 === error_reporting()) {
		return false;
	}
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
$exception=false;
$start = microtime(true);
try{
	$DB=mysqli_db::get_instance();
	$init_params=array('date'=>'2013-12-31','realmoney'=>15114,'consider'=>15114,'diff'=>0);
	if(!$DB->record_exists('balance_check',$init_params)){
		$DB->insert_record('balance_check',$init_params);
	}
	include_once($CFG->dirroot.'/lib/gen_dbx_fin_table.php');
	include_once($CFG->dirroot.'/lib/gen_tcategory.php');
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

require_once __DIR__.'/lib/dropbox-sdk/lib/dropbox/autoload.php';
use \Dropbox as dbx;

$client=new  dbx\Client($CFG->dbxtoken,$CFG->dbxappname,'UTF-8');
$file=fopen(__DIR__.'/records.log','r');
$client->uploadFile('/records.log',dbx\WriteMode::force(), $file);
fclose($file);