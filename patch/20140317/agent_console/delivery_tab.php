<?php
require_once dirname(__FILE__)."/libs/JSON.php";
$json = new Services_JSON();
$module_name = 'agent_console';
header("Content-type: application/json");
/* get booker list */
if (isset($_REQUEST['booker_list'])){
    $module_name = 'customer';
    $arrConf['cadena_dsn'] = "mysql://asterisk:asterisk@localhost/call_center";
    include_once "/var/www/html/modules/$module_name/libs/paloSantoAdressBook.class.php";
    include_once "/var/www/html/libs/paloSantoDB.class.php";

    $pDB = new paloDB($arrConf['cadena_dsn']); // address_book
    $padress_book = new paloAdressBook($pDB);
    $arrBooker = $padress_book->getAgentList();
    echo $json->encode($arrBooker);
    return;
}
/* end of get booker list */
/* get delivery man */
if (isset($_REQUEST['delivery_man_list'])){
    $module_name = 'ticket_delivery';
    $arrConf['cadena_dsn'] = "mysql://asterisk:asterisk@localhost/call_center";
    include_once "/var/www/html/modules/$module_name/libs/Ticket_Delivery.class.php";
    include_once "/var/www/html/libs/paloSantoDB.class.php";

    $pDB = new paloDB($arrConf['cadena_dsn']); // address_book

    $pTicket_Delivery = new Ticket_Delivery($pDB);
    $delivery_man_list = $pTicket_Delivery->getDeliveryMan();
    echo $json->encode($delivery_man_list);
    return;
}
/* end of get booker list */



include_once "delivery_status.php";
global $db;
$db['default']['hostname'] = "localhost";
$db['default']['username'] = 'asterisk';
$db['default']['password'] = "asterisk";
$db['default']['database'] = "call_center";

$page = isset($_POST['page']) ? $_POST['page'] : 1;
$rp = isset($_POST['rp']) ? $_POST['rp'] : 10;
$sortname = isset($_POST['sortname']) ? $_POST['sortname'] : 'id';
$sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : 'desc';
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

function showStatus2($str)
{
    switch ($str){
        case 'Mới':
            $color = '<b><span style="color:orange">'.$str.'</span></b>';
            break;
        case 'Đang giao':
            $color = '<b><span style="color:red">'.$str.'</span></b>';
            break;
        case 'Đã nhận tiền':
            $color = '<b><span style="color:green">'.$str.'</span></b>';
            break;
        case 'Chờ xử lý':
            $color = '<b><span style="color:blue">'.$str.'</span></b>';
            break;
        case 'Đã hủy':
            $color = '<b><span style="color:grey">'.$str.'</span></b>';
            break;
        case 'Chờ giao':
            $color = '<b><span style="color:DarkCyan">'.$str.'</span></b>';
            break;
        default:
            break;
    }
    return $color;
}

function countRec($fname,$where) {
	//$sql = "SELECT count($fname) FROM $tname ";
	$sql = "SELECT count($fname)
        FROM ticket_delivery a LEFT JOIN agent b ON a.agent_id = b.id
        LEFT JOIN ticket_delivery_user c on a.user_id = c.id $where";
	$result = runSQL($sql);
	return $result[0][0];
}

$sort = "ORDER BY $sortname $sortorder";
$start = (($page-1) * $rp);

$limit = "LIMIT $start, $rp";

$where = "";
if ($qtype == 'ticket_code' && $query){
    $delivery_search = runSQL("SELECT ticket_id FROM ticket_delivery_code WHERE ticket_code like '%$query%'");    
	if (count($delivery_search)>0) {
        $where = " WHERE a.id in (";
        foreach ($delivery_search as $key=>$row){
            $ticket_search_id = $row['ticket_id'];
            $where .= $ticket_search_id;
            if ($key != (count($delivery_search)-1))
                $where .= ',';
        }
        $where .= ')';
    }
    else
        $where = " WHERE 1 <> 1 ";
}
elseif (in_array($qtype ,array('purchase_date','collection_date','delivery_date')) && $query){
	$start = date("Y-m-d",strtotime($query));
	$end = $start.' 23:59:59';
	$where = " WHERE $qtype >= '$start' and $qtype <= '$end' ";
}
elseif ($qtype == 'status') {
    if ($query=="Đã hủy")
        $where = " WHERE a.isActive = 0 ";
    else
        $where = " WHERE a.isActive = 1 AND $qtype like '%$query%' ";
}
else
	if ($query) $where = " WHERE $qtype LIKE '%$query%' ";

if(isset($_POST['letter_pressed']) && $_POST['letter_pressed']!=''){
    if ($_POST['letter_pressed']!='All') {
        if (trim($where)=='')
            $where = " WHERE SUBSTRING_INDEX(customer_name,' ',-1) LIKE '".$_POST['letter_pressed']."%' ";
        $where .= " AND SUBSTRING_INDEX(customer_name,' ',-1) LIKE '".$_POST['letter_pressed']."%' ";
    }
}

$sql = "SELECT a.id, a.customer_name, a.customer_phone, a.purchase_date, b.name as agent, a.status,
        a.pay_amount, c.name as delivery_man, a.delivery_date, a.collection_date, a.isActive
        FROM ticket_delivery a LEFT JOIN agent b ON a.agent_id = b.id
        LEFT JOIN ticket_delivery_user c on a.user_id = c.id
        $where $sort $limit";
$data = runSQL($sql);


$total = countRec("a.id",$where);

$jsonData = array('page'=>$page,'total'=>$total,'rows'=>array());

foreach($data as $row){
    //If cell's elements have named keys, they must match column names
    //Only cell's with named keys and matching columns are order independent.
	$ticket_id = $row['id'];
	$codes = runSQL("SELECT ticket_code FROM ticket_delivery_code WHERE ticket_id='$ticket_id'");
    $code_list = '';
    foreach ($codes as $code){
        $code_list.=$code['ticket_code'].'<br/>';
    }
	$phone = '<a href="javascript:void(0)" onclick="make_call(\''.$row['customer_phone'].'\')"'.">
        <img border=0 src='modules/agent_console/images/call.png' 
		title='Gọi số ".$row['customer_phone']."'/></a> ".$row['customer_phone'];
    // status "Da Huy"
    if ($row['isActive']=='0')
        $row['status'] = 'Đã hủy';
	//date
	$delivery_man = is_null($row['delivery_man'])?'(Chưa phân công)':$row['delivery_man'];	
	$view = '<a href="javascript:void(0)" onclick="view_delivery(\''.$row['id'].'\')">'."<img src='modules/$module_name/images/extra.png' title='Xem chi tiết'></a>&nbsp;&nbsp;";
    $edit = '<a href="javascript:void(0)" onclick="edit_delivery(\''.$row['id'].'\')">'."<img src='modules/$module_name/images/edit.png' title='Sửa'></a>";
    $disable = '<a href="javascript:void(0)" onclick="disable_delivery(\''.$row['id'].'\')">'."<img src='modules/$module_name/images/disable.png' title='Hủy vé'></a>";
    $enable = '<a href="javascript:void(0)" onclick="enable_delivery(\''.$row['id'].'\')">'."<img src='modules/$module_name/images/enable.png' title='Mở vé'></a>";
    $action = $row['isActive']==0?$enable:($row['status']=='Mới'||$row['status']=='Chờ xử lý'?$edit.'&nbsp'.$disable:'');
    $entry = array(
            'id'=>$row['id'],
            'cell'=>array(
                'id'   => $row['id'],
                'agent'=>$row['agent'],
                'pay_amount'=>$row['pay_amount'],
                'status'=>showStatus2($row['status']),
                'delivery_man'=>$delivery_man,
                'delivery_date'=>isset($row['delivery_date']) ? date("d-m-Y h:i:s",strtotime($row['delivery_date'])) : '',
                'collection_date'=>isset($row['collection_date']) ? date("d-m-Y h:i:s",strtotime($row['collection_date'])) : '',
                'customer_phone'=>$phone,
                'customer_name'=>$row['customer_name'],
				'purchase_date'=>isset($row['purchase_date']) ? date("d-m-Y",strtotime($row['purchase_date'])) : '',
				'ticket_code'=>$code_list,
				'action'=>$view.'&nbsp'.$action,
        ),
    );
    $jsonData['rows'][] = $entry;
}
echo $json->encode($jsonData);
?>