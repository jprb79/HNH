<?php

/*
 * $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);
    $dsn = $arrConfig['AMPDBENGINE']['valor'] . "://" .
        $arrConfig['AMPDBUSER']['valor'] . ":" .
        $arrConfig['AMPDBPASS']['valor'] . "@" .
        $arrConfig['AMPDBHOST']['valor'] . "/asterisk";
    $oDB = new paloDB($dsn);
 */
$module_name = 'customer';
$arrConf['cadena_dsn'] = "mysql://asterisk:asterisk@localhost/call_center";

require_once dirname(__FILE__)."/libs/JSON.php";
include_once "/var/www/html/modules/$module_name/libs/paloSantoAdressBook.class.php";
include_once "/var/www/html/libs/paloSantoDB.class.php";
$json = new Services_JSON();
header("Content-type: application/json");

/* get booker list */
if (isset($_REQUEST['booker_list'])){
    $pDB = new paloDB($arrConf['cadena_dsn']); // address_book
    $padress_book = new paloAdressBook($pDB);
    $arrBooker = $padress_book->getBookerList();
    echo $json->encode($arrBooker);
    return;
}
/* end of get booker list */

$page = isset($_POST['page']) ? $_POST['page'] : 1;
$rp = isset($_POST['rp']) ? $_POST['rp'] : 10;
$sortname = isset($_POST['sortname']) ? $_POST['sortname'] : 'customer_name';
$sortorder = isset($_POST['sortorder']) ? $_POST['sortorder'] : 'asc';
$query = (isset($_POST['query']) && trim($_POST['query'])!='') ? '%'.$_POST['query'].'%' : null;
$qtype = isset($_POST['qtype']) ? $_POST['qtype'] : null;
if (is_null($query)) $qtype = null;
$letter = (isset($_POST['letter_pressed']) && trim($_POST['letter_pressed'])!='') ? $_POST['letter_pressed'] : null;
$sort = "$sortname $sortorder";

// process grid data
$jsonData = array('page'=>$page,'total'=>$total,'rows'=>array());

//limit email search
if ($qtype=='email')
    $query = substr($query,1,strlen($query));

// limit search
if ($query == '' || is_null($query) || strlen($query)<=4)
{
    $total = 0;
    $entry = array(
        'id'=>0,
        'cell'=>array(
            'customer_code' =>  'ERROR',
            'customer_name'     =>  'Nhập mục cần tìm!',
            'address'      =>  'Phải >= 3 ký tự',
            'contact'         =>  '',
            'booker'        =>  '',
            'sale'          =>  '',
            'accountant'    =>  '',
            'type'          =>  '',
            'membership'   =>  '',
            'view'          =>  '',
        ));
    $jsonData['rows'][] = $entry;
}
elseif ($qtype=='phone'&&strlen($query)<=7) {
    $total = 0;
    $entry = array(
        'id'=>0,
        'cell'=>array(
            'customer_code' =>  'ERROR',
            'customer_name'     =>  'Số điện thoại >= 6 số',
            'address'      =>  '',
            'contact'         =>  '',
            'booker'        =>  '',
            'sale'          =>  '',
            'accountant'    =>  '',
            'type'          =>  '',
            'membership'   =>  '',
            'view'          =>  '',
        ));
    $jsonData['rows'][] = $entry;
}
else {
    /* new code using customer class */
    $pDB = new paloDB($arrConf['cadena_dsn']); // address_book
    $padress_book = new paloAdressBook($pDB);

    $total = $padress_book->getAddressBook(NULL,NULL,$qtype,$query,TRUE,$sort,$letter);
    $total = $total[0]["total"];
    $arrResult = $padress_book->getAddressBook($rp, $page-1, $qtype, $query, FALSE, $sort,$letter);
}
$arrPayment_type = array(
    0 => 'Khách hàng lẻ',
    2 => 'Khách hàng công ty',
    3 => 'Khách hàng đại lý',
);

if(is_array($arrResult) && $total>0){
    $typeContact = "";
    foreach($arrResult as $key => $adress_book){
        switch ($adress_book['type']){
            case '0':
                $typeContact = '<img border=0 src="/modules/'.$module_name.'/images/nor-customer.png" title="Khách hàng lẽ"/>KLE';
                break;
            case '1':
                $typeContact = '<img border=0 src="/modules/'.$module_name.'/images/fre-customer.png" title="Khách hàng lẽ thường xuyên"/>KLE-TX';
                break;
            case '2':
                $typeContact = '<img border=0 src="/modules/'.$module_name.'/images/company.png" title="Khách hàng công ty"/>CTY';
                break;
            case '3':
                $typeContact = '<img border=0 src="/modules/'.$module_name.'/images/agency.png" title="Khách hàng đại lý"/>DLY';
                break;
            default:
                break;
        }


        /*
        $phone_list = '';
        if (count($adress_book['number'])>0)
            foreach ($adress_book['number'] as $phone) {
                $phone1 = explode('-',$phone);
                $call = trim($phone1[0]);
                $phone_list .= is_null($call)||trim($call)==''?'':'<a href="javascript:void(0)" onclick="make_call(\''.$call.'\')"'.">
                    <img border=0 src='modules/agent_console/images/call.png' title='Gọi số ".$call."'/></a> ".$phone.'<br>';
            }

        $email_list = '';
        if (count($adress_book['email'])>0)
            foreach ($adress_book['email'] as $email) {
                $phone1 = explode('-',$email);
                $addr = trim($phone1[0]);
                $email_list .= is_null($addr)||trim($addr)==''?'':'<a title="Gửi mail đến hộp mail này" href="mailto:'.$addr.'?Subject=[CallCenter]" target="_top">'.$email.'</a><br/>';
            }
        'customer_code' =>  'ERROR',
            'customer_name'     =>  'Số điện thoại >= 6 số',
            'address'      =>  '',
            'contact'         =>  '',
            'booker'        =>  '',
            'sale'          =>  '',
            'accountant'    =>  '',
            'type'          =>  '',
            'membership'   =>  '',
            'view'          =>  '',
        */
        $contact = '';
        if (isset($adress_book['contact']) && count($adress_book['contact'])>0)
            foreach ($adress_book['contact'] as $row) {
                $call = $row['phone'];
                $email = $row['email'];
                $phone_list = is_null($call)||trim($call)==''?'':'<a href="javascript:void(0)" onclick="make_call(\''.$call.'\')"'.">
                    <img border=0 src='modules/agent_console/images/call.png' title='Gọi số ".$call."'/></a> ".$call.'<br>';
                $email_list = is_null($email)||trim($email)==''?'':'- <a title="Gửi mail đến hộp mail này" href="mailto:'.$email.'?Subject=[CallCenter]:" target="_top">'.$email.'</a><br/>';
                $name_list = is_null($row['name'])||trim($row['name'])==''?'':' - '.$row['name'];
                $contact .= $phone_list.$name_list.$email_list.'</br>';
            }

        $entry = array(
            'id'=>$adress_book['id'],
            'cell'=>array(
                'customer_code' =>  $adress_book['customer_code'],
                'customer_name'     =>  $adress_book['customer_name'],
                'address'      =>  $adress_book['address'],
                'contact'         =>  $contact,
                'booker'        =>  $adress_book['booker'],
                'sale'          =>  $adress_book['sale'],
                'accountant'    =>  $adress_book['accountant'],
                'type'          =>  $typeContact,
                'membership'   =>  $adress_book['membership'],
                'view'          =>  '<a href="javascript:void(0)" onclick="view_customer(\''.$adress_book['id'].'\')">Chi tiết</a>',
            ));
        $jsonData['rows'][] = $entry;
    }
}
/* end of new code using customer class */
echo $json->encode($jsonData);
?>