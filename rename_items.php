<?php
function dbx_change_item_name($old_name,$new_name){
	require_once __DIR__.'/lib/dropbox-sdk/lib/dropbox/autoload.php';
	$token='OprJKfb4QroAAAAAAAAAG0gfCQ7Rz-Wrg67U2dBrYQbxLx-iXwW_kvEMssAv-yay';
	$fname='dev/name_assignment.txt';
	$f_handle=fopen($fname,'w+b');

	$client=new  Dropbox\Client($token,'directapp','UTF-8');
	$client->getFile('/'.$fname,$f_handle);
	//fwrite($f_handle,"\nmother\n");
	fputcsv($f_handle,array($old_name,$new_name));
	fclose($f_handle);
	$client->uploadFile("/".$fname,Dropbox\WriteMode::force(),fopen($fname,'r'));
}
$old_name='пар';
$new_name='парикмахерская';
dbx_change_item_name($old_name,$new_name);

$new_name=trim($new_name);
if(!empty($new_name)){
	$DB=new mysqli_DB();
	$rec=$DB->get_record('correct_item_name',array('name'=>$new_name));
	if(!$rec){
		$rec= new stdClass();
		$rec->name=$new_name;
		$rec->id=$DB->insert_record('correct_item_name',$rec);
	}
	$item=$DB->get_record('item',array('name'=>$old_name));
	if($item){
		$item->correct_item_name_id=$rec->id;
		$DB->update_record('correct_item_name',$item);
	}
	if ($new_name!=$old_name)
		dbx_change_item_name($old_name,$new_name);
}

