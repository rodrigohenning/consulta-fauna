<?php 
require_once("adodb/adodb.inc.php");

$db = ADONewConnection('postgres');

$db->debug = true; 

$db->Connect('localhost', 'postgres', 'st23km2d', 'DB_SEIAM');

$rs = $db->Execute('select * from EN_LICENCAS');

?> 
