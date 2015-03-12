<?php
const CONFIG=1;
unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'wallet';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'root';
$CFG->dbport    = 3306;
$CFG->wwwroot	= 'http://wallet';
$CFG->dirroot=__DIR__;

date_default_timezone_set('Europe/Moscow');
require_once($CFG->dirroot.'/lib/setup.php');
