<?php
require('./PHPExcel/PHPExcel.php');
$fname="../finance_xlsx/2014.01_temp";
$objPHPExcel = PHPExcel_IOFactory::load($fname.".xlsx");
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
$objWriter->setDelimiter(";");
$objWriter->setEnclosure("");
//$objWriter->setPreCalculateFormulas(false);
$objWriter->save($fname."_out.csv");