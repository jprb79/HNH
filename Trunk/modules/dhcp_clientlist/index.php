<?php

function _moduleContent(&$smarty, $module_name)
{
    //include elastix framework
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoValidar.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/misc.lib.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoACL.class.php";


    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoAdressBook.class.php";
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

    $smarty->assign('MODULE_NAME', $module_name);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn_agi_manager['password'] = $arrConfig['AMPMGRPASS']['valor'];
    $dsn_agi_manager['host'] = $arrConfig['AMPDBHOST']['valor'];
    $dsn_agi_manager['user'] = 'admin';

    //solo para obtener los devices (extensiones) creadas.
    $dsnAsterisk = generarDSNSistema('asteriskuser', 'call_center');
    $pDB   = new paloDB($arrConf['dsn_conn_database']); // address_book
    $pDB_2 = new paloDB($arrConf['dsn_conn_database2']); // acl

    $pDB_customer   = new paloDB($arrConf['cadena_dsn']); // address_book

    $action = getAction();
    $content = "";
    switch($action)
    {
        case "new":
            $content = new_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "cancel":
            header("Location: ?menu=$module_name");
            break;
        case "commit":
            $content = save_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk, true);
            break;
        case "edit":
            $content = view_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk,true);
            break;
        case "show":
            $content = view_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "save":
            $content = save_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "delete":
            $content = deleteContact($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "call2phone":
            $content = call2phone($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "transfer_call":
            $content = transferCALL($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        default:
            $content = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
    }

    return $content;
}

function new_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $arrFormadress_book = createFieldForm($pDB);
    $oForm = new paloForm($smarty,$arrFormadress_book);

    $smarty->assign("Show", 1);
    $smarty->assign("REQUIRED_FIELD", "Bắt buộc");
    $smarty->assign("SAVE", "Lưu");
    $smarty->assign("CANCEL","Hủy bỏ");
    $smarty->assign("check_2","checked");
    $smarty->assign("title", "Thông tin khách hàng");
    $smarty->assign("icon", "modules/$module_name/images/address_book.png");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_adress_book.tpl", "Thông tin khách hàng", $_POST);

    $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    //$contenidoModulo = $smarty->fetch("$local_templates_dir/new_customer.tpl");
    return $contenidoModulo;
}

/*
******** Funciones del modulo
*/
function report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloAdressBook($pDB);
    $arrBooker = $padress_book->getBookerList();
    $arrPayment_type = array(
        0 => 'Khách lẻ không thường xuyên',
        1 => 'Khách lẻ thường xuyên',
        2 => 'Khách hàng công ty',
        3 => 'Khách hàng đại lý',
    );

    $arrComboElements = array(  "customer_code"    => "Mã khách hàng",
                                "firstname"        => "Tên khách hàng",
                                "phone"    => "Số điện thoại",
                                "email"    => "Email",
                                "agent_id"  =>   "Booker",
                                "sale"  =>   "Kinh Doanh",
                                "booker"  =>   "Kế Toán",
                                "type"    => "Loại khách hàng");

    $arrFormElements = array(   "field" => array(   "LABEL"                  => "Tìm theo",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrComboElements,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "pattern" => array( "LABEL"          => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "INPUT_EXTRA_PARAM"      => array('id' => 'filter_value')),
                                "booker" => array(   "LABEL"                  => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrBooker,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "payment" => array(   "LABEL"                  => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrPayment_type,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", "Tìm");
    $smarty->assign("module_name", $module_name);

    $field   = NULL;
    $pattern = NULL;
    $namePattern = NULL;

    if(isset($_POST['field']) and isset($_POST['pattern']) and ($_POST['pattern']!="")){
        $field      = $_POST['field'];
        $pattern    = "%".trim($_POST['pattern'])."%";
        $namePattern = trim($_POST['pattern']);
        //$nameField=$arrComboElements[$field];
        //$agent_id = $_POST['booker'];
        //$payment_type = $_POST['booker'];
    }

    $arrFilter = array("field"=>$field,"pattern" =>$namePattern);

    $startDate = $endDate = date("Y-m-d H:i:s");
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->addFilterControl(_tr("Filter applied ").$field." = $namePattern", $arrFilter, array("field" => "name","pattern" => ""));
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter_adress_book.tpl", "", $arrFilter);
    $total = $padress_book->getAddressBook(NULL,NULL,$field,$pattern,TRUE);
    $total_datos = $total[0]["total"];

    //Paginacion
    $limit  = 20;
    $total  = $total_datos;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $offset = $oGrid->calculateOffset();
    $inicio = ($total == 0) ? 0 : $offset + 1;
    $end    = ($offset+$limit)<=$total ? $offset+$limit : $total;
    //Fin Paginacion

    $arrResult = $padress_book->getAddressBook($limit, $offset, $field, $pattern, FALSE);
    //var_dump($arrResult);die;
    $arrData = null; //echo print_r($arrResult,true);
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
            $phone_list = '';
            if (count($adress_book['number'])>0)
                foreach ($adress_book['number'] as $phone) {
                    $phone1 = explode('-',$phone);
                    $call = trim($phone1[0]);
                    $phone_list .= "<a href='?menu=$module_name&action=call2phone&id=".$call."'><img border=0 src='/modules/$module_name/images/call.png' title='Gọi số ".$call."'/></a> ".$phone.'<br/>'; //SDT column
                }

            $email_list = '';

            if (count($adress_book['email'])>0)
                foreach ($adress_book['email'] as $email) {
                    $phone1 = explode('-',$email);
                    $addr = trim($phone1[0]);
                    $email_list .= '<a title="Gửi mail đến hộp mail này" href="mailto:'.$addr.'?Subject=[CallCenter]:" target="_top">'.$email.'</a><br/>';
                }

            $arrTmp[0]  = "<input type='checkbox' name='contact_{$adress_book['id']}'  />";
            $arrTmp[1]  = $adress_book['customer_code']; //Ten column
            $arrTmp[2]  = $adress_book['firstname']; //Ten column
            $arrTmp[3]  = $adress_book['lastname']; //Ten column
            $arrTmp[4]  = $phone_list;
            $arrTmp[5]  = $email_list;
            $arrTmp[6]  = $adress_book['booker'];
            $arrTmp[7]  = $adress_book['sale'];
            $arrTmp[8]  = $adress_book['accountant'];
            $arrTmp[9]  = $typeContact;
            $arrTmp[10] = $adress_book['membership'];
            $arrTmp[11] = $adress_book['payment'];
            $arrTmp[12] = "<a href='?menu=$module_name&action=show&id=".$adress_book['id']."'><img src='modules/$module_name/images/extra.png' title='Xem'></a>&nbsp;
            <a href='?menu=$module_name&action=edit&id=".$adress_book['id']."'><img src='modules/$module_name/images/edit.png' title='Sửa'></a> ";
            $arrData[]  = $arrTmp;
        }
    }

    $oGrid->deleteList("Bạn có muốn xóa khách hàng này không?","delete","Xóa");
    $arrGrid = array(   "title"    => "Thông tin khách hàng",
                        "url"      => array('menu' => $module_name, 'filter' => $pattern),
                        "icon"     => "modules/$module_name/images/address_book.png",
                        "width"    => "99%",
                        "start"    => $inicio,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => '',
                                                    "property1" => ""),
                                            1 => array("name"      => "Mã KH",
                                                    "property1" => ""),
                                            2 => array("name"      => "Tên khách hàng",
                                                    "property1" => ""),
                                            3 => array("name"      => "Họ lót",
                                                "property1" => ""),
                                            4 => array("name"      => "Số điện thoại/Liên hệ",
                                                    "property1" => ""),
                                            5 => array("name"      => "Email/Liên hệ",
                                                    "property1" => ""),
                                            6 => array("name"      => "Booker",
                                                    "property1" => ""),
                                            7 => array("name"      => "Kinh doanh",
                                                    "property1" => ""),
                                            8 => array("name"      => "Kế toán",
                                                    "property1" => ""),
                                            9 => array("name"      => "Phân loại",
                                                    "property1" => ""),
                                            10 => array("name"      => "Thẻ thành viên",
                                                    "property1" => ""),
                                            11 => array("name"      => "Cách thanh toán",
                                                    "property1" => ""),
                                            12 => array("name"      => "Chức năng",
                                                    "property1" => ""),
                                        )
                    );
    $oGrid->addNew("new","Thêm khách hàng");
    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function createFieldForm($pDB)
{
    $pBooker = new paloAdressBook($pDB);
    $arrBooker = $pBooker->getBookerList();
    $arrPayment_type = $pBooker->getPaymentTypeList();
    $arrFields = array(
                "firstname"          => array(   "LABEL"            => "Tên khách hàng",
                                            "REQUIRED"              => "yes",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;","id" => "firstname"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "lastname"          => array(   "LABEL"                 => "Họ và tên lót",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "birthday"          => array(   "LABEL"                 => "Ngày sinh",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;","id" => "birthday"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "birthplace"          => array(   "LABEL"           => "Nơi sinh",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "cmnd"          => array(   "LABEL"                 => "Số CMND",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> "^[\*|#]*[[:digit:]]*$"),
                "passport"      => array(   "LABEL"                 => "Số Passport",
                                            "REQUIRED"              => "no",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "INPUT_TYPE"            => "TEXT",
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> "^[\*|#]*[[:digit:]]*$"),
                "phone"         => array(   "LABEL"                 => "Số điện thoại",
                                            "REQUIRED"              => "yes",
                                            "INPUT_TYPE"            => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> "^[\*|#]*[[:digit:]]*$"),
                "address"          => array(         "LABEL"        => "Địa chỉ",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "email"          => array(   "LABEL"           => "Email",
                    "REQUIRED"              => "no",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),
                "company"          => array(   "LABEL"           => "Công ty",
                    "REQUIRED"              => "no",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),
                "booker"          => array(   "LABEL"           => "NV Booker",
                    "EDITABLE"               => "yes",
                    "REQUIRED"               => "yes",
                    "INPUT_TYPE"             => "SELECT",
                    //'EDITABLE'              => $bEdit ? 'no' : 'yes',
                    "INPUT_EXTRA_PARAM"      => $arrBooker,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => ""),
                "sale"          => array(   "LABEL"           => "NV Sale",
                    "REQUIRED"              => "no",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),
                "sale"          => array(   "LABEL"           => "NV Sale",
                    "REQUIRED"              => "no",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),
                "accountant"          => array(   "LABEL"           => "NV Kế toán",
                    "REQUIRED"              => "no",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),
                "membership"          => array(   "LABEL"           => "Thẻ thành viên",
                    "REQUIRED"              => "no",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),
                "payment_type"          => array(   "LABEL"           => "Thanh toán",
                    "EDITABLE"               => "yes",
                    "REQUIRED"               => "no",
                    "INPUT_TYPE"             => "SELECT",
                    "INPUT_EXTRA_PARAM"      => $arrPayment_type,
                    "VALIDATION_TYPE"        => "text",
                    "VALIDATION_EXTRA_PARAM" => ""),
                "customer_code"          => array(   "LABEL"           => "Mã khách hàng",
                    "REQUIRED"              => "no",
                    "INPUT_TYPE"            => "TEXT",
                    "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                    "VALIDATION_TYPE"       => "text",
                    "VALIDATION_EXTRA_PARAM"=> ""),

        "company_name"          => array(   "LABEL"            => "Tên khách hàng",
            "REQUIRED"              => "yes",
            "INPUT_TYPE"            => "TEXT",
            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;","id" => "company_name"),
            "VALIDATION_TYPE"       => "text",
            "VALIDATION_EXTRA_PARAM"=> ""),
        "company_address"          => array(         "LABEL"        => "Địa chỉ",
            "REQUIRED"              => "no",
            "INPUT_TYPE"            => "TEXTAREA",
            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
            "VALIDATION_TYPE"       => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "company_code"          => array(   "LABEL"           => "Mã khách hàng",
            "REQUIRED"              => "no",
            "INPUT_TYPE"            => "TEXT",
            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
            "VALIDATION_TYPE"       => "text",
            "VALIDATION_EXTRA_PARAM"=> ""),
        "company_booker"          => array(   "LABEL"           => "NV Booker",
            "EDITABLE"               => "yes",
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "SELECT",
            //'EDITABLE'              => $bEdit ? 'no' : 'yes',
            "INPUT_EXTRA_PARAM"      => $arrBooker,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "company_sale"          => array(   "LABEL"           => "NV Sale",
            "REQUIRED"              => "no",
            "INPUT_TYPE"            => "TEXT",
            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
            "VALIDATION_TYPE"       => "text",
            "VALIDATION_EXTRA_PARAM"=> ""),
        "company_accountant"          => array(   "LABEL"           => "NV Kế toán",
            "REQUIRED"              => "no",
            "INPUT_TYPE"            => "TEXT",
            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
            "VALIDATION_TYPE"       => "text",
            "VALIDATION_EXTRA_PARAM"=> ""),
        "company_membership"         => array(   "LABEL"                 => "Thẻ thành viên",
            "REQUIRED"              => "yes",
            "INPUT_TYPE"            => "TEXTAREA",
            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
            "VALIDATION_TYPE"       => "text",
            "VALIDATION_EXTRA_PARAM"=> ""),
        "company_pay_type"          => array(   "LABEL"           => "Thanh toán",
            "EDITABLE"               => "yes",
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $arrPayment_type,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        );
    return $arrFields;
}

function save_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk, $update=FALSE)
{
    $arrForm   = createFieldForm();
    $oForm     = new paloForm($smarty, $arrForm);

    if (isset($_GET['id']) && !ctype_digit($_GET['id'])) unset($_GET['id']);
    if (isset($_POST['id']) && !ctype_digit($_POST['id'])) unset($_POST['id']);

    if (false) {//(!$oForm->validateForm($_POST)) {
        // Falla la validación básica del formulario
        $smarty->assign("mb_title", "Kiểm tra:");
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>Các trường sau có lỗi:</b> ";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }

        $smarty->assign("mb_message", $strErrorMsg);

        $smarty->assign("REQUIRED_FIELD", "Bắt buộc");
        $smarty->assign("SAVE", "Lưu");
        $smarty->assign("CANCEL", "Hủy bỏ");
        $smarty->assign("title", "Thông tin khách hàng");

        if(isset($_POST['customer_type']))
        switch ($_POST['customer_type']) {
            case '0':
                $smarty->assign("check_0", "checked");
                break;
            case '1':
                $smarty->assign("check_1", "checked");
                break;
            case '2':
                $smarty->assign("check_2", "checked");
                break;
            case '3':
                $smarty->assign("check_3", "checked");
                break;
            default:
                break;
        }

        if($update)
        {
            $_POST["edit"] = 'edit';
            return view_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
        }else{
            $smarty->assign("Show", 1);
            $htmlForm = $oForm->fetchForm("$local_templates_dir/new_adress_book.tpl", "Thông tin khách hàng", $_POST);
            $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
            return $contenidoModulo;
        }
    }else{ //NO HAY ERRORES
        $idPost    = getParameter('id');
        $padress_book = new paloAdressBook($pDB);
        $contactData = $padress_book->contactData($idPost);
        $type  = getParameter('customer_type');
        if ($type=='0' || $type=='1'){
            $phone  = getParameter('phone');
            $data = array(
                'customer_code' => getParameter('customer_code'),
                'firstname'  => getParameter('firstname'),
                'lastname'  => getParameter('lastname'),
                'birthday'  => date('Y-m-d',strtotime(getParameter('birthday'))),
                'birthplace'  => getParameter('birthplace'),
                'cmnd'  => getParameter('cmnd'),
                'passport'  => getParameter('passport'),
                'address'  => getParameter('address'),
                'company' => getParameter('company'),
                'email' => getParameter('email'),
                'agent_id' => getParameter('booker'),
                'sale' => getParameter('sale'),
                'payment_type' => getParameter('payment_type'),
                'accountant' => getParameter('accountant'),
                'membership' => getParameter('membership'),
                'customer_phone' => explode("\n",trim($phone)),
            );
        }
        else{
            $data = array(
                'customer_code'  => getParameter('company_code'),
                'firstname'  => getParameter('company_name'),
                'agent_id' => getParameter('company_booker'),
                'sale'  => getParameter('company_sale'),
                'accountant'  => getParameter('company_accountant'),
                'address'  => getParameter('company_address'),
                'membership' => getParameter('company_membership'),
                'payment_type' => getParameter('company_pay_type'),
                'contact_name'       => getParameter('contact_name'),
                'contact_phone'       => getParameter('contact_phone'),
                'contact_email'       => getParameter('contact_email'),
            );
        }
        if($update && isset($contactData['id'])){ // actualizacion del contacto
            if($contactData){
                $idt = $contactData['id'];
                $result = $padress_book->updateContact($data,$type,$idt);
                if(!$result){
                    $smarty->assign("mb_title", "Lỗi database");
                    $smarty->assign("mb_message", $padress_book->errMsg);
                    return report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
                }
            }else{
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $arrLang["Internal Error"]);
                return report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            }
        }else{ //// creacion de contacto
            $result = $padress_book->addContact($data,$type);
            if(!$result){
                $smarty->assign("mb_title", "Lỗi");
                $smarty->assign("mb_message", $padress_book->errMsg);
                return new_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            }
        }

        if(!$result)
            return($pDB->errMsg);

        if($_POST['id'])
            header("Location: ?menu=$module_name&action=show&id=".$_POST['id']);
        else
            header("Location: ?menu=$module_name");
    }
}

function deleteContact($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloAdressBook($pDB);

    foreach($_POST as $key => $values){
        if(substr($key,0,8) == "contact_"){
            $tmpBookID = substr($key, 8);
            $result = $padress_book->deleteContact($tmpBookID);
        }
    }
    $content = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
    return $content;
}

function view_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk, $update=FALSE)
{
    $arrFormadress_book = createFieldForm($pDB);
    $padress_book = new paloAdressBook($pDB);
    $oForm = new paloForm($smarty,$arrFormadress_book);
    $id = getParameter('id');

    if(isset($_POST["edit"]) || $update==TRUE){
        $oForm->setEditMode();
        $smarty->assign("Commit", 1);
        $smarty->assign("SAVE","Sửa");
    }else{
        $oForm->setViewMode();
        $smarty->assign("Edit", 1);
    }

    $smarty->assign("icon", "modules/$module_name/images/address_book.png");
    $smarty->assign("EDIT","Sửa");
    $smarty->assign("title", "Thông tin khách hàng");
    $smarty->assign("SAVE", "Lưu");
    $smarty->assign("CANCEL", "Đóng");
    $smarty->assign("REQUIRED_FIELD", "Bắt buộc");

    $contactData = $padress_book->contactData($id);
    if($contactData){
        $smarty->assign("ID",$id);
    }else{
        $smarty->assign("mb_title", "Lỗi");
        $smarty->assign("mb_message", 'Không cho phép xem');
        return report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
    }

    if($contactData['type']=='0')
        $smarty->assign("check_0", "checked");
    elseif($contactData['type']=='1')
        $smarty->assign("check_1", "checked");
    elseif($contactData['type']=='2')
        $smarty->assign("check_2", "checked");
    else
        $smarty->assign("check_3", "checked");
    // get contact list to show in smarty
    $smarty->assign("CONTACT", $contactData['contact']);
    // get phone list
    foreach ($contactData['number'] as $v)
        $arr_phone .= $v . "\n";

    $arrData['firstname']   = $contactData['firstname'];
    $arrData['lastname']    = $contactData['lastname'];
    $arrData['company']        = $contactData['company'];
    $arrData['email']        = $contactData['email'];
    $arrData['cmnd']        = $contactData['cmnd'];
    $arrData['passport']    = $contactData['passport'];
    $arrData['birthday']    = date("d-m-Y",strtotime($contactData['birthday']));
    $arrData['birthplace']  = $contactData['birthplace'];
    $arrData['address']     = $contactData['address'];
    $arrData['membership']     = $contactData['membership'];
    $arrData['sale']        = $contactData['sale'];
    $arrData['booker']        = $contactData['agent_id'];
    $arrData['accountant']        = $contactData['accountant'];
    $arrData['payment_type']        = $contactData['payment_type'];
    $arrData['customer_code']        = $contactData['customer_code'];
    // for company customer
    $arrData['company_name']        = $contactData['firstname'];
    $arrData['company_booker']        = $contactData['agent_id'];
    $arrData['company_code']        = $contactData['customer_code'];
    $arrData['company_pay_type']        = $contactData['payment_type'];
    $arrData['company_sale']        = $contactData['sale'];
    $arrData['company_accountant']        = $contactData['accountant'];
    $arrData['company_address']        = $contactData['address'];
    $arrData['company_membership']        = $contactData['membership'];

    $arrData['phone']       =   $arr_phone;

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_adress_book.tpl",  "Thông tin khách hàng", $arrData);
    $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function call2phone($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloAdressBook($pDB);
    //echo '<pre>'.print_r($_SESSION,1).'</pre>';die;
    $extension = $_SESSION['callcenter']['agente'];
    $name = $_SESSION['callcenter']['agente_nombre'];
    if($extension != "")
    {
        $id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:"");
        $phone2call = $id;
        $result = $padress_book->Call2Phone($dsn_agi_manager, $extension, $phone2call, $extension, $name);
        if(!$result) {
            $smarty->assign("mb_title", $arrLang['ERROR'].":");
            $smarty->assign("mb_message", $arrLang["The call couldn't be realized"]);
        }
        else {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $padress_book->errMsg);
        }
    }
    else{
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $padress_book->errMsg);
    }

    $content = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
    return $content;
}

function transferCALL($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloAdressBook($pDB);
    $pACL         = new paloACL($pDB_2);
    $id_user      = $pACL->getIdUser($_SESSION["elastix_user"]);
    if($id_user != FALSE)
    {
        $user = $pACL->getUsers($id_user);
        if($user != FALSE)
        {
            $extension = $user[0][3];
            if($extension != "")
            {
                $id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:"");

                $phone2tranfer = '';
                if(isset($_GET['type']) && $_GET['type']=='external')
                {
                    $contactData   = $padress_book->contactData($id, $id_user,"external",false,null);
                    $phone2tranfer = $contactData['telefono'];
                }else
                    $phone2tranfer = $id;

                $result = $padress_book->Obtain_Protocol_from_Ext($dsnAsterisk, $extension);
                if($result != FALSE)
                {
                    $result = $padress_book->TranferCall($dsn_agi_manager, $extension, $phone2tranfer, $result['dial'], $result['description']);
                    if(!$result)
                    {
                        $smarty->assign("mb_title", $arrLang['ERROR'].":");
                        $smarty->assign("mb_message", $arrLang["The transfer couldn't be realized, maybe you don't have any conversation now."]);
                    }
                }
                else {
                    $smarty->assign("mb_title", $arrLang["Validation Error"]);
                    $smarty->assign("mb_message", $padress_book->errMsg);
                }
            }
        }
        else{
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $padress_book->errMsg);
        }
    }
    else{
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $padress_book->errMsg);
    }

    $content = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
    return $content;
}
/*
******** Fin
*/

function getAction()
{
    if(getParameter("edit"))
        return "edit"; 
    else if(getParameter("commit"))
        return "commit";
    else if(getParameter("show"))
        return "show";
    else if(getParameter("delete"))
        return "delete";
    else if(getParameter("new"))
        return "new";
    else if(getParameter("save"))
        return "save";
    else if(getParameter("delete"))
        return "delete";
    else if(getParameter("action")=="show")
        return "show";
    else if(getParameter("action")=="edit")
        return "edit";
    else if(getParameter("action")=="download_csv")
        return "download_csv";
    else if(getParameter("action")=="call2phone")
        return "call2phone";
    else if(getParameter("action")=="transfer_call")
        return "transfer_call";
    else if(getParameter("action")=="getImage")
        return "getImage";
    else
        return "report";
}
?>