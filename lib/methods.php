<?php

function get_item_id($item){
	Global $DB;
	$item=trim($item);
	if(empty($item)) return false;
	$id=$DB->get_field('item','id',array('name'=>$item));
	if($id===false)
		$id=$DB->insert_record('item',array('name'=>$item));
	return $id;
}

function insert_transaction($date,$tcategory,$sum,$item){
	Global $DB;
	$sum=(int) $sum;
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	$sign=$DB->get_field('transaction_category','sign',array('id'=>$tcategory_id));
	if ($sign==1) $sum=abs($sum);
	if ($sign==2) $sum=-abs($sum);
	$item_id=get_item_id($item);
	list($y,$m,$d)=explode('.',$date);
	if($sum==0 or !$tcategory_id or !$item_id or !checkdate($m,$d,$y)){
		echo "\n\nНеверная транзакция\nДата:{$date}\nСумма:{$sum}\nИмя:{$item}\nКатегория:{$tcategory}\n\n";
		if($sum==0) return;
		else
			die();
	}
	$params=array( 'sum'=>$sum, 'date'=>$date,'tcategory'=>$tcategory_id,'itemid'=>$item_id);
	if (!$DB->record_exists('record',$params))
		$DB->insert_record('record',$params);
}