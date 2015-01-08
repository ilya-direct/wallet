<?php

//$str='p_mom_multiple';
//echo str_replace('_multiple','_desc',$str)."\n";
require_once('../lib/mysqli_db.class.php');
$DB=new mysqli_DB();
//var_dump($DB->get_fieldset_sql('select id,name from item  where id>11111 limit 5'));
//var_dump($DB->get_field_sql('select name from item  where id<2 limit 5'));
//$DB->get_field_info("select * from transaction_category");

$fruit = array('apple', 'a','b','banana','c'=>'d','cranberry');
array_splice($fruit,3,1,array('x'=>'xaxa','xaxaxa'));
//unset($fruit[3]);
//unset($fruit[5]);
print_r($fruit);
$fruit=array_values($fruit); // преобразует ассоциативный массив в индексированный
print_r($fruit);

$arr=[ 1=> 'cranberry',
    'value' => 'cranberry',
     0 => 'c',
    'key' => 'c' ];
//print_r($arr);
list($a,$b)=$fruit;
//echo "$a\n$b\n";