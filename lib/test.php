<?php

$str='p_mom_multiple';
echo str_replace('_multiple','_desc',$str)."\n";
require_once('../lib/mysqli_db.class.php');
$DB=new mysqli_DB();
var_dump($DB->get_fieldset_sql('select id,name from item  where id>11111 limit 5'));
var_dump($DB->get_field_sql('select name from item  where id<2 limit 5'));