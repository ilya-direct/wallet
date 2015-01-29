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

function insert_transaction_multiple($date,$tcategory,$entries){
	Global $DB;
	$tcategory_parts=explode('_',$tcategory);
	if(count($tcategory_parts)!==3 or $tcategory_parts[2]!='multiple'){
		die("Категория tcategory не является multiple $tcategory\n");
	}
	list($y,$m,$d)=explode('.',$date);
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	if(!$tcategory_id or !checkdate($m,$d,$y)){
		die("Несуществующая категория или неверная дата $tcategory_id $date\n");
	}
	$sign=$DB->get_field('transaction_category','sign',array('id'=>$tcategory_id));
	if ($sign=='+') $sign=1;
		elseif($sign=='-') $sign=-1;
	else { die("Не указан знак категории multiple $tcategory\n"); }

	$available_ids=$DB->get_fieldset_sql('select id from record where `date`="'.$date.'" and `tcategory`='.$tcategory_id);

	foreach($entries as $k => $entry){
		$entry->sum=$sign * abs((int)$entry->sum);
		$entry->itemid=get_item_id($entry->item);
		if (!$entry->sum or !$entry->itemid){
			echo "Нет суммы или пусное описание $date : $entry->sum $entry->item";
			unset($entries[$k]);
			continue;
		}
		$entry->tcategory=$tcategory_id;
		$entry->date=$date;
		$entry->id=array_shift($available_ids);
		unset($entry->item);
		if(is_null($entry->id)){
			$DB->insert_record('record',$entry);
		}else{
			$DB->update_record('record',$entry);
		}
	}
	if (!empty($available_ids)){
		$DB->delete_record_sql('delete from record where id in ('.implode(',',$available_ids).')');
	}
}

function insert_transaction_single($date,$tcategory,$sum,$item,$with_zero_sum=false){
	Global $DB;
	$sum=(int) $sum;
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	$sign=$DB->get_field('transaction_category','sign',array('id'=>$tcategory_id));
	if ($sign=='+') $sum=abs($sum);
	if ($sign=='-') $sum=-abs($sum);
	$item_id=get_item_id($item);
	list($y,$m,$d)=explode('.',$date);
	if((!$with_zero_sum && $sum==0) or !$tcategory_id or !$item_id or !checkdate($m,$d,$y)){
		echo "\n\nНеверная транзакция\nДата:{$date}\nСумма:{$sum}\nИмя:{$item}\nКатегория:{$tcategory}\n\n";
		die();
	}
	$params=array( 'sum'=>$sum, 'date'=>$date,'tcategory'=>$tcategory_id,'itemid'=>$item_id);
	if (!$DB->record_exists('record',$params))
		$DB->insert_record('record',$params);
	else
		$DB->update_record('record',$params);
}

function delete_transactions($date,$tcategory){
	Global $DB;
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	$tc_ids=$DB->get_fieldset_sql("select distinct tcategory from record where date='$date'");
	if(in_array($tcategory_id,$tc_ids)){
		$DB->delete_record_sql("delete from record where tcategory='$tcategory_id' and date='$date'");
	}
}