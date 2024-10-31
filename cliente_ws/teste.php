<?php
require_once("adodb/adodb.inc.php");
$dsn = 'postgres8://postgres:st23km2d@localhost/DB_SEIAM';  
$db = ADONewConnection($dsn);  
$rs = $db->Execute("select * from geral.area");
$rs->fields[0];

foreach ($rs as $valor) {
	echo $valor[0]."<br>";
}

?>
