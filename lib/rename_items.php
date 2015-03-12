<?php
if(!defined('EXEC')) throw new Exception('undef constant EXEC');
require_once(__DIR__.'/../config.php');
$DB=mysqli_db::get_instance();
$item=$DB->get_record('item',array('id'=>optional_param('item_id',0,PARAM_INT)));
$new_name=optional_param('new_item_name','',PARAM_RAW_TRIMMED);
if(!is_object($item) or empty($new_name)) return;
$rec=$DB->get_record('correct_item_name',array('name'=>$new_name));
if(!is_object($rec)){
	$rec= new stdClass();
	$rec->name=$new_name;
	$rec->id=$DB->insert_record('correct_item_name',$rec);
}
$item->correct_item_name_id=$rec->id;
$DB->update_record('item',$item);
