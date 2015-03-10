<?php

if(!defined('EXEC')) throw new Exception('undef constant EXEC');

include_once(__DIR__.DIRECTORY_SEPARATOR.'mysqli_db.class.php');
$DB=mysqli::get_instance();

$fields=array(
	'Мама'=>array('name'=>'p_mom_multiple','sort'=>2),
	'Мама (PM)'=>array('name'=>'p_mompm','sort'=>3),
	'Ученики'=> array('name'=>'p_pupils','sort'=>4),
	'Другие доходы'=> array('name'=>'p_other_multiple','sort'=>5),
	'Универ'=>array('name'=>'m_university','sort'=>6),
	'MTI'=> array('name'=>'m_mti','sort'=>7),
	'бенз'=> array('name'=>'m_petrol','sort'=>8),
	'Моб'=> array('name'=>'m_mobile','sort'=>8,'deleted'=>1),
	'Мобила'=> array('name'=>'m_mobile','sort'=>9),
	'iPad'=> array('name'=>'m_ipad','sort'=>10),
	'Гулянки'=> array('name'=>'m_spend_multiple','sort'=>11),
	'Другие расходы'=> array('name'=>'m_other_multiple','sort'=>12),
	'Корректировка'=>array('name'=>'correcting','sort'=>13),
);

foreach($fields as $value => $params){
	if(preg_match('/^([pm])_/',$params['name'],$matches)){
		$params['sign']=($matches[1]=='p') ? '+' : '-';
		unset($matches);
	}
	if($DB->record_exists('transaction_category',array('value'=>$value))){
		$id=$DB->get_field('transaction_category','id',array('value'=>$value));
		$params['id']=$id;
		$DB->update_record('transaction_category',$params);
	}else{
		$params['value']=$value;
		$DB->insert_record('transaction_category',$params);
	}
}