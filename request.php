<?php
require_once(__DIR__.'/config.php');
$action=optional_param('action','',PARAM_ALPHANUMEXT);
$DB=mysqli_db::get_instance();
switch($action){
case 'search':
	$str=optional_param('str',false,PARAM_RAW_TRIMMED);
	if(empty($str)) return;
	$names=$DB->get_fieldset_sql("select name from correct_item_name where name like '{$str}%' order by name limit 10");
	$ret=implode('\',\'',$names);
	$ret="['".$ret."']";
	echo $ret;
	return ;
case 'item_assign_remove':
	$itemid=optional_param('itemid',false,PARAM_INT);
	if(empty($itemid)) return;
	$DB->set_field('item','correct_item_name_id',null,array('id'=>$itemid));

}
