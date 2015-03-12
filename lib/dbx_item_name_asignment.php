<?php
require_once(__DIR__.'/../config.php');
$token='OprJKfb4QroAAAAAAAAAG0gfCQ7Rz-Wrg67U2dBrYQbxLx-iXwW_kvEMssAv-yay';
$client=new  Dropbox\Client($token,'directapp','UTF-8');
$fname='/dev/name_assignment.txt';
$client->getFile($fname,fopen($fname,'w+b'));
$f_handle=fopen($CFG->dirroot.$fname,'r');

$DB=mysqli_db::get_instance();
$except_ids='';
while(!feof($f_handle)){
	list($old_name,$new_name)=fgetcsv($f_handle);
	$itemid=$DB->get_fieldset_sql('select
									    i.id
									from
									    item i
									        inner join
									    correct_item_name ci ON (i.correct_item_name_id = ci.id)
									where
									    i.name="'.trim($old_name).'" and ci.name="'.trim($new_name).'"');
	if(!empty($itemid)) $except_ids.=$itemid.',';
}
$except_ids=rtrim($except_ids,',');
fclose($f_handle);
$db_assignments=$DB->get_records_sql('select
					    i.name as old_name,
					    ci.name as new_name,
					    i.correct_item_name_id as ci_id
					from
					    item i
					        inner join
					    correct_item_name ci ON (i.correct_item_name_id = ci.id)
					where
					    i.correct_item_name_id != 0
					        and correct_item_name_id is not null
					        and i.id not in ('.$except_ids.')');

$f_handle=fopen($CFG->dirroot.$fname,'w+b');
foreach($db_assignments as $as){
	fputcsv($f_handle,array($as->old_name,$as->new_name));
}
fclose($f_handle);
$client->uploadFile("/".$fname,Dropbox\WriteMode::force(),fopen($fname,'r'));
$f_handle=fopen($CFG->dirroot.$fname,'r');
while(!feof($f_handle)){
	list($old_name,$new_name)=fgetcsv($f_handle);
	$item=$DB->get_record('item',array('name'=>$old_name));
	if(empty($item)) continue;
	$rec=$DB->get_record('correct_item_name',array('name'=>$new_name));
	if(!is_object($rec)){
		$rec= new stdClass();
		$rec->name=$new_name;
		$rec->id=$DB->insert_record('correct_item_name',$rec);
	}
	$item->correct_item_name_id=$rec->id;
	$DB->update_record('item',$item);
}
fclose($f_handle);



//dbx_change_item_name($item->name,$new_name);
function dbx_change_item_name($old_name,$new_name){
	global $CFG;
	$old_name=trim($old_name);
	$new_name=trim($new_name);
	if(empty($new_name) or empty($old_name)) return;
	$token='OprJKfb4QroAAAAAAAAAG0gfCQ7Rz-Wrg67U2dBrYQbxLx-iXwW_kvEMssAv-yay';
	$client=new  Dropbox\Client($token,'directapp','UTF-8');
	$fname='/dev/name_assignment.txt';
	$f_handle=fopen($CFG->dirroot.$fname,'w+b');
	$client->getFile($fname,$f_handle);
	//fwrite($f_handle,"\nmother\n");
	fputcsv($f_handle,array($old_name,$new_name));
	fclose($f_handle);
	$client->uploadFile("/".$fname,Dropbox\WriteMode::force(),fopen($fname,'r'));
}
