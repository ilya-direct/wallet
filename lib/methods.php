<?php

function item_exists($item){
	Global $DB;
	$item=trim($item);
	return $DB->record_exists('select id from item where item.name=\''.$item.'\'');
}

function insert_item($item){
	Global $DB;
	$item=trim($item);
	return $DB->insert_record_sql('insert into item(name) value(\''.$item.'\')');
}

function get_item_id($item){
	Global $DB;
	$item=trim($item);
	$rec=$DB->get_record_sql('select id from item where item.name=\''.$item.'\'');

	return is_object($rec) ? $rec->id : false;
}

function get_sign_id($sign){
	if ($sign==='p') return 1;
		elseif ($sign==='m') return 2;
	return 3;
}
function transaction_exists($sign,$sum,$item,$date){
	Global $DB;
	$sum=(int) $sum;
	$sign_id=get_sign_id($sign);
	$item_id=item_exists($item) ? get_item_id($item) : insert_item($item);
	return $DB->record_exists("select * from  record where  signid={$sign_id}
						and sum={$sum} and itemid={$item_id} and time='{$date}'");

}
function insert_transaction($sign,$sum,$item,$date){
	Global $DB;
	$sign_id=get_sign_id($sign);
	$sum=(int) $sum;
	if ($sign_id==1 or $sign_id==2) $sum=abs($sum);
	$item_id=item_exists($item) ? get_item_id($item) : insert_item($item);
	$id=$DB->insert_record_sql("insert into record (signid,sum,itemid,time) values({$sign_id},{$sum},{$item_id},'{$date}')");

	return $id;
}

function get_category_id($category_name){
    global $DB;
    $category_name=strtolower($category_name);
    $category_name=trim($category_name);
    if ($DB->record_exists('category',array('name' => $category_name)))
        return $DB->get_field('category','id',array('name' => $category_name));
    else
        return $DB->insert_record('category',array('name' => $category_name));
}
