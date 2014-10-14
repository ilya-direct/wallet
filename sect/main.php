<?php
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
$TPL=new stdClass();
$TPL->table=array();
while(($row=$result->fetch_assoc())!=false){
    $TPL->table[]=$row;
}
include('/templates/main.tpl.php');