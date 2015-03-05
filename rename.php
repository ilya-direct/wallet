<?php
define('EXEC',1);
require_once(__DIR__.DIRECTORY_SEPARATOR.'lib/'.'mysqli_db.class.php');
$DB=new mysqli_DB();

if(!empty($_POST['request'])){
	require_once('lib/rename_items.php');
}
$item=$DB->get_record_sql('select * from item where correct_item_name_id is null order by name limit 1');

$assigned_items=
	$DB->get_records_sql('select it.name as item_name,cit.name as assigned from item it
	inner join correct_item_name cit on cit.id=it.correct_item_name_id where it.name!=cit.name order by it.name')
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<!--	<link rel="stylesheet" href="css/bootstrap.min.css">-->
	<!--	<script type="text/javascript" src="js/jquery.min.js"></script>-->
	<!--	<script type="text/javascript" src="js/bootstrap.min.js"></script>-->
	<script type="text/javascript" src="js/yui.js"></script>
	<title>Корректировка элементов</title>

</head>
<body>
	<table>
	<? foreach($assigned_items as $assigned): ?>
		<tr>
			<td>
				<?=$assigned->item_name?>
			</td>
			<td>=></td>
			<td>
				<?=$assigned->assigned?>
			</td>
		</tr>
	<? endforeach;?>
	</table>
	<form action='#' method="post" accept-charset="utf-8">
		<input type='hidden' name='item_id' value='<?=$item->id?>'>
		<input type='hidden' name='request' value=1>
		"<?=$item->name?>" <input type='text' name='item_name' value='<?=$item->name?>'>
		<input type='submit' name='submit' value='Сохранить'>
	</form>
</body>


