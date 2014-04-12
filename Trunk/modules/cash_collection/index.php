<?php

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/Cash_Collection.class.php";
    require_once "modules/$module_name/libs/JSON.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pDB = new paloDB($arrConf['cadena_dsn']);
    $pDB_2 = new paloDB($arrConf['elastix_dsn']['acl']);
    //actions
    $action = getParameter('action');

    switch($action){
        case 'assign':
            $content = assign_CashCollection($pDB,$pDB_2);
            break;
        default:
            $content = report_CashCollection($smarty, $module_name, $local_templates_dir, $pDB,$pDB_2);
            break;
    }
    return $content;
}

function showStatus($str)
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
        default:
            break;
    }
    return $color;
}

function report_CashCollection($smarty, $module_name, $local_templates_dir, &$pDB,$pDB_2)
{
    $pCashCollection = new Cash_Collection($pDB);
    $pACL         = new paloACL($pDB_2);

    // get filter parameters
    $filter = array(
        'date_start' => (trim($_POST['date_start'])==''?'':date("Y-m-d",strtotime($_POST['date_start']))),
        'date_end' => (trim($_POST['date_end'])==''?'':date("Y-m-d",strtotime($_POST['date_end']))),
        'customer_name' => trim($_POST['customer_name']),
        'customer_phone' => trim($_POST['customer_number']),
        'ticket_code' => trim($_POST['ticket_code']),
        'status'        => trim($_POST['status']),
    );

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle("Thu tiền giao vé");
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export("Cash_Collection");

    $url = array(
        "menu"         =>  $module_name);
    $oGrid->setURL($url);

    $arrColumns = array(
        "ID",
        "Tên Khách Hàng",
        "Số điện thoại",
        "Ngày mua vé",
        "Booker",
        "Địa chỉ",
        "Tiền trả",
        "Mã số vé",
        "Tình trạng",
        "Nhân viên giao",
        "Ngày phân công",
        "Nhân viên nhận tiền",
        "Ngày nhận tiền",
        "   ",
    );
    $oGrid->setColumns($arrColumns);
    $total   = $pCashCollection->getNumCash_Collection($filter);

    $arrData = null;
    if($oGrid->isExportAction()){
        $limit  = $total; // max number of rows.
        $offset = 0;      // since the start.
    }
    else{
        $limit  = 20;
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total);
        $offset = $oGrid->calculateOffset();
    }

    $arrResult =$pCashCollection->getCash_Collection($limit, $offset, $filter);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
            $ticket = '';
            $name = $pACL->getUsers($value['accounting_id']);
            $elastix_user = (is_null($value['accounting_id'])?'(Chưa nhận)':$name[0][1]);
            foreach ($value['ticket_code'] as $row)
                $ticket .= $row.'<br>';
            $arrTmp[0] = $value['id'];
            $arrTmp[1] = $value['customer_name'];
            $arrTmp[2] = $value['customer_phone'];
            $arrTmp[3] = (is_null($value['purchase_date'])?'':date("d-m-Y H:m:s",strtotime($value['purchase_date'])));
            $arrTmp[4] = $value['agent_name'];
            $arrTmp[5] = '<a href="javascript:void(1)" onclick="alert(\''.$value['deliver_address'].'\')">Xem</a>';
            $arrTmp[6] = $value['pay_amount'];
            $arrTmp[7] = $ticket;
            $arrTmp[8] = showStatus($value['status']);
            $arrTmp[9] = $value['delivery_name'];
            $arrTmp[10] = (is_null($value['delivery_date'])?'':date("d-m-Y H:m:s",strtotime($value['delivery_date'])));
            $arrTmp[11] = $elastix_user;
            $arrTmp[12] = (is_null($value['collection_date'])?'':date("d-m-Y H:m:s",strtotime($value['collection_date'])));
            $arrTmp[13] = ($value['status']=='Đang giao'?'<a href="javascript:void(1)" onclick="collect_form(\''.$value['id'].'\')">Nhận tiền</a>':'');
            $arrData[] = $arrTmp;
        }
    }
    $oGrid->setData($arrData);

    //begin section filter
    $oFilterForm = new paloForm($smarty, createFieldFilter());
    // get delivery man list
    $delivery_man_list = $pCashCollection->getDeliveryMan();
    $smarty->assign("DELIVERY_MAN_LIST",$delivery_man_list);

    $htmlFilter  = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid();
    //end grid parameters

    return $content;
}

function assign_CashCollection(&$pDB,$pDB_2)
{
    // collect parameters
    $sTicketId = trim(getParameter('ticket_id'));
    $pACL         = new paloACL($pDB_2);
    $id_user      = $pACL->getIdUser($_SESSION["elastix_user"]);

    $response = array(
        'action'    =>  'collect',
        'message'   =>  'Đã nhận tiền từ mã giao vé số ' . $sTicketId,
    );

    $pCashCollection = new Cash_Collection($pDB);
    $result = $pCashCollection->assign_CashCollection($sTicketId,$id_user);
    // return json
    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: ' . $pCashCollection->errMsg;
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($response);
}

function createFieldFilter()
{
    $arrFormElements = array(
        "date_start"    => array(
                            "LABEL"                  => 'Từ ngày',
                            "REQUIRED"               => "yes",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => array("id" => "date_start"),
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "date_end"      => array(
                            "LABEL"                  => "Đến ngày",
                            "REQUIRED"               => "yes",
                            "INPUT_TYPE"             => "TEXT",
                            "INPUT_EXTRA_PARAM"      => array("id" => "date_end"),
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "customer_name"     => array(
                            "LABEL"            => "Tên khách hàng",
                            "REQUIRED"              => "no",
                            "INPUT_TYPE"            => "TEXT",
                            "INPUT_EXTRA_PARAM"     => array("id" => "customer_name"),
                            "VALIDATION_TYPE"       => "text",
                            "VALIDATION_EXTRA_PARAM"=> ""),
        "customer_number"     => array(
                            "LABEL"            => "Số điện thoại",
                            "REQUIRED"              => "no",
                            "INPUT_TYPE"            => "TEXT",
                            "INPUT_EXTRA_PARAM"     => array("id" => "customer_number"),
                            "VALIDATION_TYPE"       => "text",
                            "VALIDATION_EXTRA_PARAM"=> ""),
        "ticket_code"     => array(
                            "LABEL"            => "Mã vé",
                            "REQUIRED"              => "no",
                            "INPUT_TYPE"            => "TEXT",
                            "INPUT_EXTRA_PARAM"     => array("id" => "ticket_code"),
                            "VALIDATION_TYPE"       => "text",
                            "VALIDATION_EXTRA_PARAM"=> ""),
        "status"          => array(
                            "LABEL"             => "Tình trạng",
                            "REQUIRED"              => "no",
                            "INPUT_TYPE"             => "SELECT",
                            "MULTIPLE"               => NULL,
                            "SIZE"                   => NULL,
                            "INPUT_EXTRA_PARAM"      => array(  ""  => "",
                                                                "Mới" => "Mới",
                                                                "Đang giao"  => "Đang giao",
                                                                "Đã nhận tiền"   => "Đã nhận tiền"),
                            "VALIDATION_TYPE"        => "ereg",
                            "VALIDATION_EXTRA_PARAM" => "^(number|queue|type)$"),
    );
    return $arrFormElements;
}
?>