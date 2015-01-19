<?php
require('./PHPExcel/PHPExcel.php');
require_once('../lib/mysqli_db.class.php');
$DB=new mysqli_DB();

$save_path='c:/wamp/www/wallet/finance_csv/';
$dir_path='c:/wamp/www/wallet/finance_xlsm/';
$dir_handle=opendir($dir_path);
if (!$dir_handle) die("Не удалось открыть дерикторию!");

while (false !== ($file_name = readdir($dir_handle))) {
	$file_name_without_ext=preg_replace('/\.xlsm$/','',$file_name);
	if (!preg_match('/^([\d]{4})\.([\d]{2})$/',$file_name_without_ext,$matches)){
		echo "$file_name false\n";
		continue;
	}
	$y=$matches[1];
	$m=$matches[2];

	$path=$dir_path.$file_name;
	$objPHPExcel = PHPExcel_IOFactory::load($path);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
	$objWriter->setDelimiter(";");
	$objWriter->setEnclosure("");
	//$objWriter->setPreCalculateFormulas(false);
	$objWriter->save($save_path.$file_name_without_ext.".csv");

}