<?php

//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoConfig.class.php";
require_once "libs/misc.lib.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoMissedCalls.class.php";

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

    // cdr connection
    $dsn = generarDSNSistema('asteriskuser', 'asteriskcdrdb');
    $pDB_cdr = new paloDB($dsn);
    // call_center connection
    $pDB_callcenter = new paloDB($arrConf['cadena_dsn']);

    $pDBACL = new paloDB($arrConf['elastix_dsn']['acl']);
    if (!empty($pDBACL->errMsg)) {
        return "ERROR DE DB: $pDBACL->errMsg";
    }
    $pACL = new paloACL($pDBACL);
    if (!empty($pACL->errMsg)) {
        return "ERROR DE ACL: $pACL->errMsg";
    }

    //actions
    $action = getAction();

    switch($action){
        case 'call2phone':
            $content = call2phone();
            break;
        default:
            $content = reportMissedCalls($smarty, $module_name, $local_templates_dir, $pDB_callcenter, $pDBACL, $pACL, $arrConf,$pDB_cdr);
            break;
    }
    return $content;
}

function prefixNumber($number)
{
    if (strlen($number) < 7)
        return '';
    return '9'; //default prefix for outbound call
}


function call2phone()
{
    include_once "libs/paloSantoConfig.class.php";
    require_once '/var/www/html/modules/address_book/libs/paloSantoAdressBook.class.php';
    global $arrConf;
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn_agi_manager['password'] = $arrConfig['AMPMGRPASS']['valor'];
    $dsn_agi_manager['host'] = $arrConfig['AMPDBHOST']['valor'];
    $dsn_agi_manager['user'] = 'admin';
    $pDB   = new paloDB($arrConf['dsn_conn_database']); // address_book
    $padress_book = new paloAdressBook($pDB);

    $sNumber = getParameter('call_number');
    if (strpos($sNumber,'|') !== false){
        $arrExt = explode(" | ",$sNumber);
        $sNumber = trim($arrExt[1]);
    }

    $response = array(
        'action'    =>  'call2phone',
        'message'   =>  $sNumber,
    );

    $extension = $_SESSION['callcenter']['agente'];
    $name = $sNumber;//$_SESSION['callcenter']['agente_nombre'];
    //var_dump($arrConf);
    if (is_null($sNumber) || !ctype_digit($sNumber)) {
        $response['action'] = 'error';
        $response['message'] = 'Invalid or missing number to call';
    } else {
        $prefix = prefixNumber($sNumber);
        $phone2call = $prefix.$sNumber;
        $result = $padress_book->Call2Phone($dsn_agi_manager, $extension, $phone2call, $extension, $name);
        if(!$result) {
            $response['action'] = 'error';
            $response['message'] = 'Cuộc gọi không thực hiện được. Kiểm tra máy nhánh ' + $extension;
        }
    }

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($response);
}

function reportMissedCalls($smarty, $module_name, $local_templates_dir, &$pDB, &$pDBACL, $pACL, $arrConf, $pDB_cdr)
{
    $pCallingReport = new paloSantoMissedCalls($pDB, $pDB_cdr);
    $oFilterForm  = new paloForm($smarty, createFieldFilter());
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");
    $date_start   = getParameter("date_start");
    $date_end     = getParameter("date_end");
    //var_dump($filter_field);var_dump($filter_value);

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Missed Calls"));
    $oGrid->pagingShow(true); // show paging section.
    $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export(_tr("Missed Calls"));

    $url = array(
        "menu"         =>  $module_name,
        "filter_field" =>  $filter_field,
        "filter_value" =>  $filter_value
    );

    $date_start = (isset($date_start))?$date_start:date("d M Y").' 00:00';
    $date_end   = (isset($date_end))?$date_end:date("d M Y").' 23:59';
    $_POST['date_start'] = $date_start;
    $_POST['date_end']   = $date_end;

    $parmFilter = array(
        "date_start" => $date_start,
        "date_end" => $date_end
    );


    if (!$oFilterForm->validateForm($parmFilter)) {
        $smarty->assign(array(
            'mb_title'      =>  _tr('Validation Error'),
            'mb_message'    =>  '<b>'._tr('The following fields contain errors').':</b><br/>'.
                implode(', ', array_keys($oFilterForm->arrErroresValidacion)),
        ));
        $date_start = date("d M Y").' 00:00';
        $date_end   = date("d M Y").' 23:59';
    }

    $url = array_merge($url, array('date_start' => $date_start,'date_end' => $date_end));

    $oGrid->setURL($url);

    $arrColumns = array(_tr("Date"),_tr("Source"),"Số điện thoại",_tr("Destination"),_tr("Queue"),_tr("Time since last call"),
        _tr("Duration wait"),_tr("Number of attempts"),_tr("Status"));
    $oGrid->setColumns($arrColumns);

    $arrData = null;
    $date_start_format = date('Y-m-d H:i:s',strtotime($date_start.":00"));
    $date_end_format   = date('Y-m-d H:i:s',strtotime($date_end.":59"));

    $total = $pCallingReport->getNumCallingReport($date_start_format, $date_end_format, $filter_field, $filter_value);

    if($oGrid->isExportAction()){
        $limit  = $total; // max number of rows.
        $offset = 0;      // since the start.
        $arrResult = $pCallingReport->getCallingReport($date_start_format, $date_end_format, $filter_field, $filter_value);
        $arrData = $pCallingReport->showDataReport($arrResult, $total,$date_start,$date_end,$filter_field,$filter_value);

        $size = count($arrData);
        $oGrid->setData($arrData);
    }
    else{
        $limit  = 20;
        $oGrid->setLimit($limit);
        $arrResult = $pCallingReport->getCallingReport($date_start_format, $date_end_format, $filter_field, $filter_value);
        $arrData = $pCallingReport->showDataReport($arrResult, $total, $date_start_format, $date_end_format,$filter_field,$filter_value);

        //recalculando el total para la paginación
        $size = count($arrData);
        $oGrid->setTotal($size);
        $offset = $oGrid->calculateOffset(); //echo $size." : ".$offset;
        $arrResult = $pCallingReport->getDataByPagination($arrData, $limit, $offset);

        $oGrid->setData($arrResult);
    }

    //begin section filter
    $smarty->assign("filter_value", $filter_value);
    $smarty->assign("SHOW", _tr("Show"));
    $htmlFilter  = $oFilterForm->fetchForm("$local_templates_dir/missed_call.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid();
    //end grid parameters

    return $content;
}


function createFieldFilter(){
    $arrFilter = array(
        "callerid" => _tr("Source"),
        "queue_dst" => _tr("Destination"),
        "queue" => _tr("Queue"),
        "status" => _tr("Status"),
    );

    $arrFormElements = array(
        "filter_field" => array("LABEL"                  => _tr("Search"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrFilter,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "filter_value" => array("LABEL"                  => "",
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => array('id' => 'filter_value'),
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "status"          => array(
            "LABEL"             => "Tình trạng",
            "REQUIRED"              => "no",
            "INPUT_TYPE"             => "SELECT",
            "MULTIPLE"               => NULL,
            "SIZE"                   => NULL,
            "INPUT_EXTRA_PARAM"      => array(  ""  => "",
                "0" => "Chưa gọi lại",
                "1"  => "Đã gọi lại"),
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^(number|queue|type)$"),
        "queue"          => array(
            "LABEL"             => "Nhóm",
            "REQUIRED"              => "no",
            "INPUT_TYPE"             => "SELECT",
            "MULTIPLE"               => NULL,
            "SIZE"                   => NULL,
            "INPUT_EXTRA_PARAM"      => array(  ""  => "",
                "1801" => "Tổng đài LTT",
                "1802"  => "Tổng đài CoBac",
                "1803"  => "VEMAYBAY.VN",
                "1804"  => "Phòng Tour",
                "1805"  => "Phòng Sale",
                "1806"  => "Tổng đài LTT tối"),
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^(number|queue|type)$"),
        "date_start"  => array("LABEL"                  => _tr("Start Date"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M"),
            "VALIDATION_TYPE"        => "",
            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}[[:space:]]+[[:digit:]]{1,2}:[[:digit:]]{1,2}$"),
        "date_end"    => array("LABEL"                  => _tr("End Date"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M"),
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}[[:space:]]+[[:digit:]]{1,2}:[[:digit:]]{1,2}$"),
    );
    return $arrFormElements;
}


function getAction()
{
    return getParameter("action");
}
?>
