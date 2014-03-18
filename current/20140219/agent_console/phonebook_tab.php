<?php
require_once dirname(__FILE__)."/libs/JSON.php";
$json = new Services_JSON();

global $db;
$db['default']['hostname'] = "localhost";
$db['default']['username'] = 'asterisk';
$db['default']['password'] = "asterisk";
$db['default']['database'] = "call_center";

$page = isset($_POST['page']) ? $_POST['page'] : 1;
$rp = isset($_POST['rp']) ? $_POST['rp'] : 20;
$sortname = isset($_POST['sortname']) ? $_POST['sortname'] : 'firstname';
$sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : 'asc';
$query = (isset($_POST['query']) && trim($_POST['query'])!='') ? $_POST['query'] : false;
$qtype = isset($_POST['qtype']) ? $_POST['qtype'] : false;

function runSQL($rsql) {

    global $db;
	$active_group = 'default';
    /*
	$base_url = "http://".$_SERVER['HTTP_HOST'];
	$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
    */
	$connect = mysql_connect($db[$active_group]['hostname'],$db[$active_group]['username'],$db[$active_group]['password']) or die ("Error: could not connect to database");
	$database = mysql_select_db($db[$active_group]['database']);

	$result = mysql_query($rsql) or die ($rsql);
    $index = 0;	
	while ($row = mysql_fetch_array($result)) {
        $data[$index] = $row;
        $index ++;
    }
    mysql_close($connect);
    return $data;
}

function countRec($fname,$tname) {
	$sql = "SELECT count($fname) FROM $tname ";
	$result = runSQL($sql);
	return $result[0][0];
}

$sort = "ORDER BY $sortname $sortorder";
$start = (($page-1) * $rp);

$limit = "LIMIT $start, $rp";

$where = "";
if ($query) $where = " WHERE $qtype LIKE '%$query%' ";

// for filter firstname by letter
if(isset($_POST['letter_pressed']) && $_POST['letter_pressed']!=''){
    if ($_POST['letter_pressed']!='All') {
        if (trim($where)=='')
            $where = " WHERE firstname LIKE '".$_POST['letter_pressed']."%' ";
        $where .= " AND firstname LIKE '".$_POST['letter_pressed']."%' ";
    }
}

$sql = "SELECT * FROM phonebook $where $sort $limit";
$data = runSQL($sql);

$total = countRec("id","phonebook $where");

header("Content-type: application/json");
$jsonData = array('page'=>$page,'total'=>$total,'rows'=>array());

$index = 1;
foreach($data as $row){
    //If cell's elements have named keys, they must match column names
    //Only cell's with named keys and matching columns are order independent.
    $ext = !isset($row['extension'])||$row['extension']==''?'':
        '<a href="javascript:void(0)" onclick="make_call(\''.$row['extension'].'\')"'.">
    <img src='modules/agent_console/images/call.png' title='Gọi số ".$row['extension']."'/></a> ".$row['extension'];
    $mob = !isset($row['mobile'])||$row['mobile']==''?'':
        '<a href="javascript:void(0)" onclick="make_call(\''.$row['mobile'].'\')"'.">
    <img src='modules/agent_console/images/call.png' title='Gọi số ".$row['mobile']."'/></a> ".$row['mobile'];
    $com_mob = !isset($row['company_mobile'])||$row['company_mobile']==''?'':
        '<a href="javascript:void(0)" onclick="make_call(\''.$row['company_mobile'].'\')"'.">
    <img src='modules/agent_console/images/call.png' title='Gọi số ".$row['mobile']."'/></a> ".$row['company_mobile'];
    $entry = array(
            'id'=>$row['id'],
            'cell'=>array(
                'stt'=>$index,
                'firstname'=>$row['firstname'],
                'lastname'=>$row['lastname'],
                'company_mobile'=>$com_mob,
                'mobile'=>$mob,
                'extension'=>$ext,
                'email'=>'<a title="Gửi mail đến hộp mail này" href="mailto:'.$row['email'].'?Subject=[CallCenter]" target="_top">'.$row['email'].'</a>',
                'note'=>$row['note'],
                'department'=>$row['department'],
        ),
    );
    $index++;
    $jsonData['rows'][] = $entry;
}
echo $json->encode($jsonData);
?>