<?php
//include('adodb/adodb.inc.php');
require_once("adodb/adodb.inc.php");
$dsn = 'postgres8://postgres:st23km2d@localhost/DB_SEIAM';  
$db = ADONewConnection($dsn);  
$rs = $db->Execute("select * from geo_propriedade.propriedaderural_x_area");
//print_r($rs);
//echo $rs->fields[0];
?>
