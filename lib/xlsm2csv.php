<?php

if(!defined('EXEC')) throw new Exception('undef constant EXEC');

include_once(__DIR__.DIRECTORY_SEPARATOR.'mysqli_db.class.php');
include_once(__DIR__.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'PHPExcel.php');
$DB=new mysqli_DB();

$input_path=__DIR__.'/../finance_download/';
if(!is_dir($input_path)){ die('can\'t find input directory'); }
$input_path=realpath($input_path);

$output_path=__DIR__.'/../finance_csv/';
if(!is_dir($output_path)){
	if(!mkdir($output_path,0777,true)) die('can\'t create output directory');
}
$output_path=realpath($output_path);

$recs=$DB->get_records('dbx_finance',array('exists'=>1,'csv_converted'=>0));
if(!$recs) return;
foreach($recs as $rec){
	$file_name=$rec->year.'.'.$rec->month;
	$input_filepath=($input_path.'/'.$file_name.'.raw');
	if(!file_exists($input_filepath)){
		$DB->set_field('dbx_finance','exists',0,array('id'=>$rec->id));
		echo "file $input_filepath not found\n";
		continue;
	}else{
		$objPHPExcel = PHPExcel_IOFactory::load($input_filepath);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->setDelimiter(";");
		$objWriter->setEnclosure("");
		//$objWriter->setPreCalculateFormulas(false);
		$output_filepath=($output_path.'/'.$file_name.'.csv');
		$objWriter->save($output_filepath);
		$DB->set_field('dbx_finance','csv_converted',1,array('id'=>$rec->id));
		echo "file $file_name converted\n";
	}
}