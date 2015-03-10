<?php
const CONFIG=1;
unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'wallet';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
$CFG->dbport    = 3306;
$CFG->wwwroot	= 'http://wallet';
$CFG->dirroot=realpath('.');
require_once(__DIR__.'/lib/setup.php');
