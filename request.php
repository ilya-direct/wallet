<?php
require_once(__DIR__.'/config.php');
$action=empty($_REQUEST['action']) ? '' :$_REQUEST['action'];
require_once(__DIR__.'/lib/mysqli_db.class.php');
$DB=mysqli::get_instance();
switch($action){
	case 'search':
		if(empty($_REQUEST['str'])) return;

		$str=$_REQUEST['str'];
		$names=$DB->get_fieldset_sql("select name from correct_item_name where name like '{$str}%' order by name limit 10");
		$ret=implode('\',\'',$names);
		$ret="'".$ret."'";
		echo $ret;
		return ;

}
