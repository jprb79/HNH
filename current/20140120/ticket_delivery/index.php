<?php
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
//include_once "delivery_status.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/Ticket_Delivery.class.php";
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
            $content = assign_TicketDelivery($pDB,$pDB_2);
            break;
        case 'collect':
            $content = CashCollection($pDB,$pDB_2);
            break;
        case 'update_row':
            $content = updateRow_TicketDelivery($pDB,$pDB_2,$module_name);
            break;
        case 'process':
            $content = TicketProcess(&$pDB);
            break;
        case 'expand':
            $content = TicketExpand($pDB);
            break;
        default:
            $content = report_TicketDelivery($smarty, $module_name, $local_templates_dir, $pDB,$pDB_2);
            break;
    }
    return $content;
}

function CashCollection(&$pDB,$pDB_2)
{
    // collect parameters
    $sTicketId = trim(getParameter('ticket_id'));
    $pACL         = new paloACL($pDB_2);
    $id_user      = $pACL->getIdUser($_SESSION["elastix_user"]);
    $sNote      = trim(getParameter('note'));

    $pCashCollection = new Ticket_Delivery($pDB);
    if (is_null(getParameter('unpaid'))) {
        $response = array(
            'action'    =>  'collect',
            'message'   =>  'Đã nhận tiền từ mã giao vé số ' . $sTicketId,
        );
        $result = $pCashCollection->Cash_Collection($sTicketId,$id_user,false,$sNote);
    }
    else{
        $response = array(
            'action'    =>  'uncollect',
            'message'   =>  'Đã hủy nhận tiền từ mã giao vé số ' . $sTicketId,
        );
        $result = $pCashCollection->Cash_Collection($sTicketId,$id_user,true,$sNote);
    }
    // return json
    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: ' . $pCashCollection->errMsg;
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($response);
}

function TicketExpand($pDB)
{
    $sTicketId = trim(getParameter('ticket_id'));
    $pTicketExpand = new Ticket_Delivery($pDB);
    $result = $pTicketExpand->TicketExpand($sTicketId);
    // return json
    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: ' . $pTicketExpand->errMsg;
    }
    else
        $response['message'] = $result;

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($response);
}

function TicketProcess(&$pDB)
{
    // collect parameters
    $sTicketId = trim(getParameter('ticket_id'));
    $sNote      = trim(getParameter('note'));
    $sType      = trim(getParameter('type'));
    $pTicketProcess = new Ticket_Delivery($pDB);

    switch ($sType) {
        case 'return':
            $response = array(
                'action'    =>  'return',
                'message'   =>  'Mã giao vé số '.$sTicketId.' chuyển sang trạng thái chờ xử lý! ',
            );
            break;
        case 'enable':
            $response = array(
                'action'    =>  'enable',
                'message'   =>  'Đã tạo lại mã giao vé số '.$sTicketId.' thành công!',
            );
            break;
        case 'disable':
            $response = array(
                'action'    =>  'disable',
                'message'   =>  'Đã hủy mã giao vé số '.$sTicketId.' thành công! ',
            );
            break;
        default:
            break;
    }
    $result = $pTicketProcess->TicketProcess($sTicketId,$sNote,$sType);
    // return json
    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: ' . $pTicketProcess->errMsg;
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($response);
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
        case 'Chờ xử lý':
            $color = '<b><span style="color:blue">'.$str.'</span></b>';
            break;
        case 'Đã hủy':
            $color = '<b><span style="color:grey">'.$str.'</span></b>';
            break;
        default:
            break;
    }
    return $color;
}
  /* must include for a unified status apperance */ 
function shorten($str)
{
    $len = strlen($str);
    if ($len > 15){
        $begin = substr($str,0,14);
        //$end = substr($str,$len-5,$len);
        return $begin.'..';
    }
    else
        return $str;
}

function report_TicketDelivery($smarty, $module_name, $local_templates_dir, &$pDB,$pDB_2)
{
    $pTicket_Delivery = new Ticket_Delivery($pDB);
    $pACL         = new paloACL($pDB_2);
    $img_dir = "modules/$module_name/images/";
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
    $oGrid->setTitle("Yêu cầu giao vé");
    $oGrid->setTableName("delivery_grid");
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export("ticket_delivery");

    $url = array(
        "menu"         =>  $module_name);
    $oGrid->setURL($url);

    $arrColumns = array(
        "ID",
        "Tên Khách Hàng",
        "Số điện thoại",
        "Booker",
        "Địa chỉ",
        "Tiền trả",
        "Mã số vé",
        "Tình trạng",
        "Nhân viên giao",
        "Ngày phân công",
        "Vé đính kèm",
        "Ngày nhận tiền",
        "Xử lý",
        "Chi tiết",
        " ",
    );
    $oGrid->setColumns($arrColumns);
    $total   = $pTicket_Delivery->getNumTicket_Delivery($filter);

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

    $arrResult =$pTicket_Delivery->getTicket_Delivery($limit, $offset, $filter);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){
            $ticket = '';
            $name = $pACL->getUsers($value['accounting_id']);
            $elastix_user = (is_null($value['accounting_id'])?'(Chưa nhận)':$name[0][1]);
            // show files
            $download = '';
            foreach ($value['ticket_attachment'] as $row){
                $url = "/modules/agent_console/ajax-attachments-handler.php?download=" . $row['filepath']
                    . "&name=".$row['filename'];
                $filename = $row['filename'];
                $download .= "*<a href='$url' target='_blank' title='$filename'>".shorten($filename)."</a><br/>";
            }
            $print = '<a href="javascript:void(0)" onclick="print(\'' . $value['id'] . '\')"><img src="'.$img_dir.'print.png" title="In phiếu"></a>';
            $enable = $value['isActive']=='1'?'<a href="javascript:void(0)" onclick="disable(\'' . $value['id'] . '\')"><img src="'.$img_dir.'disable.png" title="Hủy yêu cầu giao vé"></a>&nbsp;':'
            <a href="javascript:void(0)" onclick="enable(\'' . $value['id'] . '\')"><img src="'.$img_dir.'enable.png" title="Tạo lại yêu cầu giao vé"></a>';
            $print .= '&nbsp;&nbsp;'.$enable;
            // function show base on status
            if ($value['isActive']=='0')
                $value['status'] = 'Đã hủy';
            switch ($value['status']) {
                case 'Mới':
                    $function = '<a href="javascript:void(1)" onclick="assign_form(\''.
                        $value['id'].'\')"><img src="'.$img_dir.'assign.png" title="Phân công"></a>';
                    break;
                case 'Đang giao':
                    $function = '<a href="javascript:void(1)" onclick="assign_form(\''.
                        $value['id'].'\')"><img src="'.$img_dir.'assign.png" title="Đổi phân công"></a>&nbsp;
                        <a href="javascript:void(1)" onclick="collect_form(\''.
                        $value['id'].'\',\''.$elastix_user.'\')"><img src="'.$img_dir.'result.png" title="Kết quả"></a>';
                    break;
                case 'Đã nhận tiền':
                    $function = '<a href="javascript:void(1)" onclick="uncollect_form(\''.
                        $value['id'].'\',\''.$elastix_user.'\')"><img src="'.$img_dir.'unpaid.png" title="Hủy nhận tiền"></a>';;
                    break;
                case 'Chờ xử lý':
                    $function = '<a href="javascript:void(1)" onclick="assign_form(\''.
                        $value['id'].'\')"><img src="'.$img_dir.'assign.png" title="Phân công"></a>';
                    break;
                default:
                    $function = '';
            }

            // show ticket code
            foreach ($value['ticket_code'] as $row)
                $ticket .= $row.'<br>';
            $arrTmp[0] = $value['id'];
            $arrTmp[1] = $value['customer_name'];
            $arrTmp[2] = $value['customer_phone'];
            $arrTmp[3] = '<span title="Chi nhánh: '.$value['office'].'">'.$value['agent_name'].'</span>';
			$arrTmp[4] = '<a href="javascript:void(1)" title="'.$value['deliver_address'].'"
			                onclick="view_address(\''.$value['deliver_address'].'\')">'.shorten($value['deliver_address']).'
			              </a>';
            $arrTmp[5] = $value['pay_amount'];
            $arrTmp[6] = $ticket;
            $arrTmp[7] = showStatus($value['status']);
            $arrTmp[8] = $value['delivery_name'];
            $arrTmp[9] = (is_null($value['delivery_date'])?'':date("d-m-Y H:m:s",strtotime($value['delivery_date'])));
            $arrTmp[10] = $download;
            $arrTmp[11] = (is_null($value['collection_date'])?'':date("d-m-Y H:m:s",strtotime($value['collection_date'])));
            $arrTmp[12] = $function;
            $arrTmp[13] = '<a href="javascript:void(1)" onclick="view_log(\''.$value['id'].'\')">
			            <img src="'.$img_dir.'extra.png" title="Xem chi tiết"></a>';
            $arrTmp[14] = $print;
            $arrData[] = $arrTmp;
        }
    }
    $oGrid->setData($arrData);

    //begin section filter
    $oFilterForm = new paloForm($smarty, createFieldFilter());
    // get delivery man list
    $delivery_man_list = $pTicket_Delivery->getDeliveryMan();
    $smarty->assign("DELIVERY_MAN_LIST",$delivery_man_list);

    $htmlFilter  = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid();
    //end grid parameters

    return $content;
}

function array_fill_keys($target, $value = '') {
    if(is_array($target)) {
        foreach($target as $key => $val) {
            $filledArray[$val] = is_array($value) ? $value[$key] : $value;
        }
    }
    return $filledArray;
}

function updateRow_TicketDelivery($pDB,$pDB_2,$module_name)
{
    $sTicketId = trim(getParameter('ticket_id'));
    $pTicket_Delivery = new Ticket_Delivery($pDB);
    $pACL         = new paloACL($pDB_2);
    $arrResult =$pTicket_Delivery->getTicket_DeliveryById($sTicketId);
    $img_dir = "modules/$module_name/images/";
    $value = $arrResult[0];
    $ticket = '';
    $name = $pACL->getUsers($value['accounting_id']);
    $elastix_user = (is_null($value['accounting_id'])?'(Chưa nhận)':$name[0][1]);
    // show files
    $download = '';
    foreach ($value['ticket_attachment'] as $row){
        $url = "/modules/agent_console/ajax-attachments-handler.php?download=" . $row['filepath']
            . "&name=".$row['filename'];
        $filename = $row['filename'];
        $download .= "*<a href='$url' target='_blank' title='$filename'>".shorten($filename)."</a><br/>";
    }
    $print = '<a href="javascript:void(0)" onclick="print(\'' . $value['id'] . '\')"><img src="'.$img_dir.'print.png" title="In phiếu"></a>';
    $enable = $value['isActive']=='1'?'<a href="javascript:void(0)" onclick="disable(\'' . $value['id'] . '\')"><img src="'.$img_dir.'disable.png" title="Hủy yêu cầu giao vé"></a>&nbsp;':
        '<a href="javascript:void(0)" onclick="enable(\'' . $value['id'] . '\')"><img src="'.$img_dir.'enable.png" title="Tạo lại yêu cầu giao vé"></a>';
    $print .= '&nbsp;&nbsp;'.$enable;
    if ($value['isActive']=='0')
        $value['status'] = 'Đã hủy';
    // function show base on status
    switch ($value['status']) {
        case 'Mới':
            $function = '<a href="javascript:void(1)" onclick="assign_form(\''.
                $value['id'].'\')"><img src="'.$img_dir.'assign.png" title="Phân công"></a>';
            break;
        case 'Đang giao':
            $function = '<a href="javascript:void(1)" onclick="assign_form(\''.
                $value['id'].'\')"><img src="'.$img_dir.'assign.png" title="Đổi phân công"></a>&nbsp;
                        <a href="javascript:void(1)" onclick="collect_form(\''.
                $value['id'].'\',\''.$elastix_user.'\')"><img src="'.$img_dir.'result.png" title="Kết quả"></a>';
            break;
        case 'Đã nhận tiền':
            $function = '<a href="javascript:void(1)" onclick="uncollect_form(\''.
                $value['id'].'\',\''.$elastix_user.'\')"><img src="'.$img_dir.'unpaid.png" title="Hủy nhận tiền"></a>';;
            break;
        case 'Chờ xử lý':
            $function = '<a href="javascript:void(1)" onclick="assign_form(\''.
                $value['id'].'\')"><img src="'.$img_dir.'assign.png" title="Phân công"></a>';
            break;
        default:
            $function = '';
    }
    // show ticket code
    foreach ($value['ticket_code'] as $row)
        $ticket .= $row.'<br>';
    // append html
    $html = '';
    $html .= '<td class="table_data">'.$value['id'].'</td>';
    $html .= '<td class="table_data">'.$value['customer_name'].'</td>';
    $html .= '<td class="table_data">'.$value['customer_phone'].'</td>';
    $html .= '<td class="table_data"><span title="Chi nhánh: '.$value['office'].'">'.$value['agent_name'].'</span></td>';
    $html .= '<td class="table_data">'.'<a href="javascript:void(1)" title="'.$value['deliver_address'].'"
			                onclick="view_address(\''.$value['deliver_address'].'\')">'.shorten($value['deliver_address']).'
			              </a></td>';
    $html .= '<td class="table_data">'.$value['pay_amount'].'</td>';
    $html .= '<td class="table_data">'.$ticket.'</td>';
    $html .= '<td class="table_data">'.showStatus($value['status']).'</td>';
    $html .= '<td class="table_data">'.$value['delivery_name'].'</td>';
    $html .= '<td class="table_data">'.(is_null($value['delivery_date'])?'':date("d-m-Y H:m:s",strtotime($value['delivery_date']))).'</td>';
    $html .= '<td class="table_data">'.$download.'</td>';
    $html .= '<td class="table_data">'.(is_null($value['collection_date'])?'':date("d-m-Y H:m:s",strtotime($value['collection_date']))).'</td>';
    $html .= '<td class="table_data">'.$function.'</td>';
    $html .= '<td class="table_data"><a href="javascript:void(1)" onclick="view_log(\''.$value['id'].'\')">
			            <img src="'.$img_dir.'extra.png" title="Xem chi tiết"></a></td>';
    $html .= '<td class="table_data">'.$print.'</td>';
    return $html;
}

function assign_TicketDelivery(&$pDB)
{
    // collect parameters
    $sTicketId = trim(getParameter('ticket_id'));
    $sUserId = trim(getParameter('user_id'));
    $sNote = trim(getParameter('note'));

    $response = array(
        'action'    =>  'assign',
        'message'   =>  'Phân công mã giao vé ' . $sTicketId . ' thành công',
    );

    $pTicket_Delivery = new Ticket_Delivery($pDB);
    $result = $pTicket_Delivery->assignDelivery($sTicketId,$sUserId,$sNote);
    // return json
    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: ' . $pTicket_Delivery->errMsg;
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