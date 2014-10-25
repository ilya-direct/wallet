<?php
require_once('Smarty.class.php');
$smarty = new Smarty();
$DB=new mysqli('localhost','root','','wallet',3306);
$DB->query("SET NAMES 'utf8'"); // кодировка
header("Content-Type: text/html; charset=utf-8");
$result=$DB->query('
  SELECT s.value as sign,
         c.name as card,
         rec.sum as sum,
         it.name as item
    from record rec
      left join item it on rec.itemid=it.id
      left join sign s  on rec.signid=s.id
      left join card c  on rec.cardid=c.id;
      ');
$table=array();
while(($row=$result->fetch_assoc())!=false){
    $table[]=$row;
}
$cards=$DB->query('select name from card');
$cards_name=array();
while(($card=$cards->fetch_assoc())!=false){
	$cards_name[]=$card;
}

$smarty->assign('table',$table);
$smarty->assign('cards',$cards_name);
$smarty->display('main.tpl');