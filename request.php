<?php
require_once(__DIR__.'/config.php');
$action=optional_param('action','',PARAM_ALPHA);
$DB=mysqli::get_instance();
switch($action){
	case 'search':
		if(optional_param('str',true,PARAM_RAW_TRIMMED)) return;

		$str=$_REQUEST['str'];
		$names=$DB->get_fieldset_sql("select name from correct_item_name where name like '{$str}%' order by name limit 10");
		$ret=implode('\',\'',$names);
		$ret="'".$ret."'";
		echo $ret;
		return ;

}
