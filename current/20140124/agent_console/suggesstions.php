<?php
//error_reporting(0);
require_once dirname(__FILE__)."/libs/JSON.php";
$json = new Services_JSON();

$con = mysql_connect('localhost', 'asterisk','asterisk');
$db = mysql_select_db('call_center');
$val = array();
$search = $_REQUEST['term'];

/* AGENT TABLE */
// 1. get number of agent
$sql = "SELECT CONCAT('TĐV:',name,' | ',number) AS item  FROM agent
	 WHERE type='SIP' and estatus='A' and
	 name like '%$search%'";
$query = mysql_query($sql);
while($row = mysql_fetch_assoc($query)){	
	if (!in_array($row['item'],$val))	
		$val[] = $row['item'];
}
// 2. like lastname
$sql = "SELECT CONCAT('TĐV:',name,' | ',number) AS item  FROM agent
	 WHERE type='SIP' and estatus='A' and
	 number like '%$search%'";
$query = mysql_query($sql); 
while($row = mysql_fetch_assoc($query)){
	if (!in_array($row['item'],$val))	
		$val[] = $row['item'];
}

/* PHONEBOOK */
$sql = "SELECT CONCAT('DB:',lastname,' ',firstname,' | ',mobile) AS item  FROM phonebook
	 WHERE mobile like '%$search%'";
$query = mysql_query($sql);
while($row = mysql_fetch_assoc($query)){
    if (!in_array($row['item'],$val))
        $val[] = $row['item'];
}
$sql = "SELECT CONCAT('DB:',lastname,' ',firstname,' | ',extension) AS item  FROM phonebook
	 WHERE extension like '%$search%'";
$query = mysql_query($sql);
while($row = mysql_fetch_assoc($query)){
    if (!in_array($row['item'],$val))
        $val[] = $row['item'];
}
$sql = "SELECT CONCAT('DB:',lastname,' ',firstname,' | ',mobile) AS item  FROM phonebook
	 WHERE firstname like '%$search%'";
$query = mysql_query($sql);
while($row = mysql_fetch_assoc($query)){
    if (!in_array($row['item'],$val))
        $val[] = $row['item'];
}


/* CUSTOMER NO NEED
$sql = "SELECT concat('KH:',lastname,' ',firstname,' | ',number) AS item FROM customer a
        inner join customer_phone b on a.id = b.customer_id
        where number like '%$search%'";
$query = mysql_query($sql);
while($row = mysql_fetch_assoc($query)){
    if (!in_array($row['item'],$val))
        $val[] = $row['item'];
}
$sql = "SELECT concat('KH:',lastname,' ',firstname,' | ',number) AS item FROM customer a
        inner join customer_phone b on a.id = b.customer_id
        where firstname like '%$search%'";
$query = mysql_query($sql);
while($row = mysql_fetch_assoc($query)){
    if (!in_array($row['item'],$val))
        $val[] = $row['item'];
}
*/
mysql_close($con);
//here we convert the result into JSON
echo $json->encode($val);

?>