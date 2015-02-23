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
	if(count($tcategory_parts)!==3 or $tcategory_parts[2]!='multiple')
		throw new Exception("Категория tcategory не является multiple $tcategory\n $date");
	list($y,$m,$d)=explode('.',$date);
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	if(!$tcategory_id or !checkdate($m,$d,$y))
		throw new Exception("Несуществующая категория или неверная дата $tcategory_id $date\n");

	$sign=$DB->get_field('transaction_category','sign',array('id'=>$tcategory_id));
	if ($sign=='+') $sign=1;
		elseif($sign=='-') $sign=-1;
	else
		throw new Exception("Не указан знак категории multiple $tcategory\n");

	$available_ids=$DB->get_fieldset_sql('select id from record where `date`="'.$date.'" and `tcategory`='.$tcategory_id);

	foreach($entries as $entry){
		$entry->sum=$sign * abs((int)$entry->sum);
		$entry->itemid=get_item_id($entry->item);
		if (!$entry->sum or !$entry->itemid)
			throw new Exception("Нет суммы или пусное описание $date : $entry->sum $entry->item");
		unset($entry->item);
		$entry->tcategory=$tcategory_id;
		$entry->date=$date;
		$entry->id=array_shift($available_ids);
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
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	$sign=$DB->get_field('transaction_category','sign',array('id'=>$tcategory_id));
	$sum=(int) $sum;
	if ($sign=='+') $sum=abs($sum);
		elseif ($sign=='-') $sum=-abs($sum);
	$item_id=get_item_id($item);
	list($y,$m,$d)=explode('.',$date);
	if((!$with_zero_sum && $sum==0) or !$tcategory_id or !$item_id or !checkdate($m,$d,$y))
		throw new Exception("\n\nНеверная транзакция Дата:{$date} Сумма:{$sum} Имя:{$item} Категория:{$tcategory}");
	$params=array('date'=>$date,'tcategory'=>$tcategory_id,'itemid'=>$item_id);
	$recs=$DB->get_records('record',$params);
	$rec=array_shift($recs);
	if(!empty($recs)){
		throw new Exception("Найдено более одной записи в single категории ".$tcategory." дата: ".$date);
	}

	if ($rec){
		$rec->sum=$sum;
		$DB->update_record('record',$rec);
	}
	else
		$params=array_merge($params,array('sum'=>$sum));
		$DB->insert_record('record',$params);
}

function delete_transactions($date,$tcategory){
	Global $DB;
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	$DB->delete_record_sql("delete from record where tcategory='$tcategory_id' and date='$date'");
}