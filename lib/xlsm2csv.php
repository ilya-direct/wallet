<?php

if(!defined('EXEC')) throw new Exception('undef constant EXEC');

include_once(__DIR__.'/../config.php');
$DB=mysqli_db::get_instance();

$input_path=__DIR__.DIRECTORY_SEPARATOR.'finance_download'.DIRECTORY_SEPARATOR;
if(!is_dir($input_path))
	throw new Exception('can\'t find input directory');

$output_path=__DIR__.DIRECTORY_SEPARATOR.'finance_csv'.DIRECTORY_SEPARATOR;
if(!is_dir($output_path) and !mkdir($output_path,0777,true))
	throw new Exception('can\'t create output directory');


$recs=$DB->get_records('dbx_finance',array('exists'=>1,'csv_converted'=>0));
foreach($recs as $rec){
	$file_name=$rec->year.'.'.$rec->month;
	$input_filepath=($input_path.'/'.$file_name.'.raw');
	if(file_exists($input_filepath)){
		$objPHPExcel = PHPExcel_IOFactory::load($input_filepath);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->setDelimiter(";");
		$objWriter->setEnclosure("");
		//$objWriter->setPreCalculateFormulas(false);
		$output_filepath=$output_path.'/'.$file_name.'.csv';
		$objWriter->save($output_filepath);
		$DB->set_field('dbx_finance','csv_converted',1,array('id'=>$rec->id));
		echo "file $file_name converted\n";
	}else
		//$DB->set_field('dbx_finance','exists',0,array('id'=>$rec->id));
		throw new Exception("file $input_filepath not found\n");
}