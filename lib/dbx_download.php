<?php

//if(!defined('EXEC')) throw new Exception('undef constant EXEC');

include_once(__DIR__.DIRECTORY_SEPARATOR.'mysqli_db.class.php');
$DB=new mysqli_DB();
include_once(__DIR__.'/dropbox-sdk/lib/dropbox/autoload.php');
use \Dropbox as dbx;

$token='OprJKfb4QroAAAAAAAAAG0gfCQ7Rz-Wrg67U2dBrYQbxLx-iXwW_kvEMssAv-yay';
$client=new  dbx\Client($token,'directapp','UTF-8');

$download_path=__DIR__.DIRECTORY_SEPARATOR.'finance_download'.DIRECTORY_SEPARATOR;
if(!is_dir($download_path) and !mkdir($download_path,0777,true))
	throw new Exception('can\'t create download directory');

$finances=$client->getMetadataWithChildren('/finances')['contents'];
$finances=array_map(function(&$el){
	$el['path']=preg_replace('/.*\//','',$el['path']);
	$el['modified']=dbx\Client::parseDateTime($el['modified'])->format("Y-m-d H:i:s");
	return $el;
},$finances);
$finances=array_column ( $finances , 'modified','path' );
$recs=$DB->get_records('dbx_finance');

foreach($finances as $file){
	$file_name=preg_replace('/.*\//','',$file['path']);
	$file_name_without_ext=preg_replace('/\.xlsm$/','',$file_name);
	if (!preg_match('/^([\d]{4})\.([\d]{2})$/',$file_name_without_ext,$matches)){
		echo "$file_name false\n";
		continue;
	}
	$y=$matches[1];
	$m=$matches[2];
	$download_filename=$download_path.DIRECTORY_SEPARATOR.$y.'.'.$m.'.raw';
	$time_modified=dbx\Client::parseDateTime($file['modified'])->format("Y-m-d H:i:s");
	$download_info=$DB->get_record('dbx_finance',array('year'=>$y,'month'=>$m));
	if($download_info){
		if($time_modified>$download_info->download_time || $download_info->exists==0){
			$download_info->download_time=$time_modified;
			$download_info->file_name=$file_name;
			$download_info->exists=1;
			$download_info->in_db=0;
			$download_info->csv_converted=0;
			if(!is_null($client->getFile($file['path'],fopen($download_filename,'wb')))){
				$DB->update_record('dbx_finance',$download_info);
				echo "$file_name updated\n";
			}
		}elseif(!file_exists($download_filename)){
			$client->getFile($file['path'],fopen($download_filename,'wb'));
			echo "$file_name downloaded\n";
		}
		/*
		else{
			//echo "$file_name not touched\n";
			continue;
		}
		*/
	}
}