<?php
header("Content-Type: text/html; charset=utf-8");
require_once('lib/smarty/Smarty.class.php');
require_once('lib/mysqli_db.class.php');
$smarty = new Smarty();
$DB=new mysqli_DB();
$result=$DB->get_record_sql('
  SELECT s.value as sign,
         c.name as card,
         rec.sum as sum,
         it.name as item
    from record rec
      left join item it on rec.itemid=it.id
      left join sign s  on rec.signid=s.id
      left join card c  on rec.cardid=c.id;
      ');
print_r($result);
die();

$smarty->assign('table',$table);
$smarty->assign('cards',$cards_name);
$smarty->display('main.tpl');