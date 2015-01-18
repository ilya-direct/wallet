<?php
require_once __DIR__.'/dropbox-sdk/lib/dropbox/autoload.php';
require_once('../lib/mysqli_db.class.php');
$DB=new mysqli_DB();
use \Dropbox as dbx;


$token='OprJKfb4QroAAAAAAAAAG0gfCQ7Rz-Wrg67U2dBrYQbxLx-iXwW_kvEMssAv-yay';
/*
$appInfo = dbx\AppInfo::loadFromJsonFile(__DIR__.'/dropbox-sdk/cfg.json');
var_dump($appInfo);
$webAuth = new dbx\WebAuthNoRedirect($appInfo, "PHP-Example/1.0");
var_dump($webAuth);
$authorizeUrl = $webAuth->start();
var_dump($authorizeUrl);
*/
$client=new  dbx\Client($token,'directapp','UTF-8');
/*
if($USER->token){
	$client=new dbx\Client($token,$appName,'UTF-8');
	try{
		$client->getAccountInfo();
	} catch(dbx\Exception_InvalidAccessToken $e){
		$authUrl=$webAuth->start();
		header('Location: ' . $authUrl);
	}
}else{
	$authUrl=$webAuth->start();
	header('Location: ' . $authUrl);
}
*/
// var_dump($client->getAccountInfo());

// upload file
//$file=fopen('upload/DZ_X.pdf',"rb");
//$size=filesize('upload/DZ_X.pdf');

//$client->uploadFile('/my/DZZ.pdf',Dropbox\WriteMode::add(),$file,$size);
// update file
//$client->uploadFileFromString('/something.txt',Dropbox\WriteMode::force(),"хаrtyrtyertyeх");
// download file

//$client->getFile('/finances/template.xlsx',fopen('download/t.xlsx','wb'));


// browsing dropbox

//var_dump($client->getMetadataWithChildren('/finances'));
$finances=$client->getMetadataWithChildren('/finances')['contents'];

foreach($finances as $file){
	$file_name=preg_replace('/.*\//','',$file['path']);
	$file_name_without_ext=preg_replace('/\..{3,4}$/','',$file_name);
	if (!preg_match('/^([\d]{4})\.([\d]{2})$/',$file_name_without_ext,$matches)){
		echo "$file_name false\n";
		continue;
	}
	$y=$matches[1];
	$m=$matches[2];
	$time_modified=dbx\Client::parseDateTime($file['modified'])->format("Y-m-d H:i:s");
	$download_info=$DB->get_record('dbx_download',array('fname'=>"{$y}.{$m}"));
	if($download_info){
		if($time_modified>$download_info->downloadtime){
			$download_info->downloadtime=$time_modified;
			$download_info->in_db=0;
			if(!is_null($client->getFile($file['path'],fopen('../finance_xlsx/'.$file_name,'wb')))){
				$DB->update_record('dbx_download',$download_info);
				echo "$file_name updated\n";
			}
		}else{
			echo "$file_name not touched\n";
			continue;
		}
	}else{
		$download_info=new stdClass();
		$download_info->fname="{$y}.{$m}";
		$download_info->downloadtime=$time_modified;
		$download_info->in_db=0;
		if(!is_null($client->getFile($file['path'],fopen('../finance_xlsx/'.$file_name,'wb')))){
			$DB->insert_record('dbx_download',$download_info);
			echo "$file_name downloaded\n";
		}
	}
}
// searching files

$query='.2014';
//var_dump($client->searchFileNames('/',$query,5,false));