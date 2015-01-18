<?php
$path="c:/wamp/www/wallet/lib/backup/";

$h=fopen($path.'temp.txt',"w");
fprintf($h,"no format!");
fclose($h);


exec('mysql -uroot -proot test < '.$path.'walletbackup.sql');
//exec('mysql -hmysql.hostinger.ru -uu182420072_root -pqwerty123 u182420072_1 < walletbackup.sql');