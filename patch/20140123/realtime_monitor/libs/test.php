<?php
$arrConf['cadena_dsn'] = "mysql://asterisk:asterisk@localhost/call_center";
require "queue_waiting2.class.php";
$o = new queue_waiting();
$o->callcenter_db_connect($arrConf['cadena_dsn']);
var_dump($o->showQueue());
?>