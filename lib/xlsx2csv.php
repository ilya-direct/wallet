<?php
require('./PHPExcel/PHPExcel.php');
$objPHPExcel = PHPExcel_IOFactory::load("../finance_xlsx/01.2014.xlsx");
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
$objWriter->setDelimiter(";");
$objWriter->setEnclosure("");
$objWriter->setPreCalculateFormulas(false);
$objWriter->save('01.2014.csv');