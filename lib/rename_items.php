<?php
if(!defined('EXEC')) throw new Exception('undef constant EXEC');
$old_item_id=optional_param('item_id',0,PARAM_INT);
if(!$old_item_id) return;
$old_name=trim($DB->get_field('item','name',array('id'=>$old_item_id)));
$new_name=optional_param('item_name','',PARAM_RAW_TRIMMED);

//dbx_change_item_name($old_name,$new_name);
if(!empty($new_name)){
	$DB=mysqli::get_instance();
	$rec=$DB->get_record('correct_item_name',array('name'=>$new_name));
	if(!$rec){
		$rec= new stdClass();
		$rec->name=$new_name;
		$rec->id=$DB->insert_record('correct_item_name',$rec);
	}
	$item=$DB->get_record('item',array('id'=>$old_item_id));
	if($item){
		$item->correct_item_name_id=$rec->id;
		$DB->update_record('item',$item);
	}
	//dbx_change_item_name($old_name,$new_name);
}


function dbx_change_item_name($old_name,$new_name){
	$old_name=trim($old_name);
	$new_name=trim($new_name);
	if(empty($new_name) or ($old_name==$new_name)) return;
	require_once __DIR__ . '/dropbox-sdk/lib/dropbox/autoload.php';
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

