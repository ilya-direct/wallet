<?php

$CFG=new stdClass();
$CFG->wwwroot=realpath('.');

$start = microtime(true);
ob_start();
require_once($CFG->wwwroot.'/lib/gen_dbx_fin_table.php');
require_once($CFG->wwwroot.'/lib/dbx_download.php');
require_once($CFG->wwwroot.'/lib/xlsm2csv.php');
require_once($CFG->wwwroot.'/lib/gen_tcategory.php');
require_once($CFG->wwwroot.'/lib/csv2db.php');
require_once($CFG->wwwroot.'/lib/balance_check.php');
$time='Время выполнения: '.(microtime(true) - $start).' сек.'."\n";

$file=fopen(__DIR__.'/records.log','ab+');
fwrite($file,date('Y-m-d H:i:s').' '.__FILE__.' '.$time);
$log=ob_get_clean();
fwrite($file,$log);
fclose($file);

require_once __DIR__.'/lib/dropbox-sdk/lib/dropbox/autoload.php';
use \Dropbox as dbx;

$token='OprJKfb4QroAAAAAAAAAG0gfCQ7Rz-Wrg67U2dBrYQbxLx-iXwW_kvEMssAv-yay';
$client=new  dbx\Client($token,'directapp','UTF-8');

$file=fopen(__DIR__.'/records.log','r');
$client->uploadFile('/records.log',dbx\WriteMode::force(), $file);
fclose($file);