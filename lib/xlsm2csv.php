<?php
require('./PHPExcel/PHPExcel.php');
$fname="../finance_xlsm/2014.01.xlsm";
$objPHPExcel = PHPExcel_IOFactory::load($fname);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
$objWriter->setDelimiter(";");
$objWriter->setEnclosure("");
//$objWriter->setPreCalculateFormulas(false);
$objWriter->save($fname."_out.csv");