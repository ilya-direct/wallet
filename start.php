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
