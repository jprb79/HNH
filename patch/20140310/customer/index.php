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
    $pACL = new paloACL($arrConf['elastix_dsn']['acl']);

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
            $content = save_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pACL, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk, true);
            break;
        case "edit":
            $content = view_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk,true);
            break;
        case "show":
            $content = view_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "save":
            $content = save_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pACL, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "delete":
            $content = deleteContact($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case "call2phone":
            $content = call2phone($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case 'import':
            $content = import($smarty, $module_name, $local_templates_dir, $pDB_customer, $arrLang, $arrConf);
            break;
        case 'export':
            $content = export($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
        case 'import_test':
            import_test($module_name, $pDB_customer);
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

function export($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloAdressBook($pDB_customer);

    $field   = getParameter('field');
    $pattern = getParameter('pattern');

    $total = $padress_book->getAddressBook(NULL,NULL,$field,$pattern,TRUE);
    $total_datos = $total[0]["total"];
    $total  = $total_datos;
    $limit  = $total;
    $arrResult = $padress_book->getAddressBook($limit, 0, $field, $pattern, FALSE);

    //export to Excel
    include_once "modules/$module_name/libs/PHPExcel.php";
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    //styling
    $styleArray = array(
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => 'FF0000'),
            'size'  => 11,
            'name'  => 'Verdana'
        ));
    $objPHPExcel->getActiveSheet()->getStyle('A2:M2')
        ->getAlignment()->setWrapText(true);
    // write header
    $rowCount = 1;
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount,'1');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,'2');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,'3');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,'4');
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,'5');
    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,'6');
    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount,'7');
    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount,'8');
    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount,'9');
    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount,'10');
    $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount,'11');
    $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount,'12');
    $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount,'13');
    $rowCount = 2;
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount,'Mã KH');
    $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,'Tên KH');
    $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,'Loại KHÁCH HÀNG');
    $objPHPExcel->getActiveSheet()->getStyle('C'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,'Địa chỉ');
    $objPHPExcel->getActiveSheet()->getStyle('D'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,'Quận/ Huyện ');
    $objPHPExcel->getActiveSheet()->getStyle('E'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,'Tỉnh/ thành');
    $objPHPExcel->getActiveSheet()->getStyle('F'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount,'ĐIỆN THOẠI');
    $objPHPExcel->getActiveSheet()->getStyle('G'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount,'Nguoi lien he');
    $objPHPExcel->getActiveSheet()->getStyle('H'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount,'EMAIL');
    $objPHPExcel->getActiveSheet()->getStyle('I'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount,'BOOKER');
    $objPHPExcel->getActiveSheet()->getStyle('J'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount,'KE TOAN');
    $objPHPExcel->getActiveSheet()->getStyle('K'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount,'SALE');
    $objPHPExcel->getActiveSheet()->getStyle('L'.$rowCount)->applyFromArray($styleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount,'So the GLP');
    $objPHPExcel->getActiveSheet()->getStyle('M'.$rowCount)->applyFromArray($styleArray);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $adress_book){
            switch ($adress_book['type']){
                case '0':
                    $typeContact = 'KLE';
                    break;
                case '1':
                    $typeContact = 'KLE';
                    break;
                case '2':
                    $typeContact = 'CTY';
                    break;
                case '3':
                    $typeContact = 'DL';
                    break;
                default:
                    $typeContact = '';
                    break;
            }
            if (isset($adress_book['contact']) && count($adress_book['contact'])>0)
                foreach ($adress_book['contact'] as $row) {
                    // handle contact by row
                    $rowCount++;

                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowCount,$adress_book['customer_code'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,$adress_book['customer_name']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,$typeContact);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,$adress_book['address']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,$adress_book['district']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,$adress_book['province']);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$rowCount,$row['phone'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount,$row['name']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount,$row['email']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount,$adress_book['booker']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount,$adress_book['accountant']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount,$adress_book['sale']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount,$adress_book['membership']);
                }
            else{
                $rowCount++;
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$rowCount,$adress_book['customer_code'],PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount,$adress_book['customer_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount,$typeContact);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount,$adress_book['address']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount,$adress_book['district']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount,$adress_book['province']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount,'');
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount,'');
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount,'');
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount,$adress_book['booker']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount,$adress_book['accountant']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount,$adress_book['sale']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount,$adress_book['membership']);
            }
        }
    }
    // stying sheet
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(11);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(11);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(16);

    // border
    $styleArray = array(
        'borders' => array(
            'inside'     => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array(
                    'argb' => '00000000'
                )
            ),
            'outline'     => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array(
                    'argb' => '00000000'
                )
            )
        )
    );
    $objPHPExcel->getActiveSheet()->getStyle('A2:M'.$rowCount)->applyFromArray($styleArray);


    $now = date("Ymdhmi");

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");;
    header("Content-Disposition: attachment;filename=export_$now.xls");
    header("Content-Transfer-Encoding: binary ");
    // export to excel file
    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
    return $objWriter->save('php://output');
}

function report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloAdressBook($pDB);
    $arrBooker = $padress_book->getBookerList();
    $arrAccountant = $padress_book->getAccountantList();
    $arrSale = $padress_book->getSaleList();
    $arrProvince = $padress_book->getProvinceList();
    $arrDistrict = $padress_book->getDistrictList();

    $arrCustomerType = array(
        0 => 'Khách hàng lẻ',
        2 => 'Khách hàng công ty',
        3 => 'Khách hàng đại lý',
    );

    $arrComboElements = array(  "customer_code"    => "Mã khách hàng",
                                "customer_name"        => "Tên khách hàng",
                                "phone"    => "Số điện thoại",
                                "email"    => "Email",
                                "type"    => "Loại khách hàng",
                                "district_id"  =>   "Quận/Huyện",
                                "province_id"  =>   "Tỉnh/Thành",
                                "accountant_id"  =>   "Kế toán",
                                "sale_id"  =>   "Kinh Doanh",
                                "booker_id"  =>   "Booker");

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
                                "sale" => array(   "LABEL"                  => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrSale,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "accountant" => array(   "LABEL"                  => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrAccountant,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "district" => array(   "LABEL"                  => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrDistrict,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "province" => array(   "LABEL"                  => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrProvince,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                "customer_type" => array(   "LABEL"                  => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrCustomerType,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", "Tìm");
    $smarty->assign("module_name", $module_name);

    $field   = NULL;
    $pattern = NULL;
    $namePattern = NULL;

    if(isset($_POST['field'])){
        $field      = $_POST['field'];
        switch ($field) {
            case 'type':
                $pattern = $_POST['customer_type'];
                break;
            case 'district_id':
                $pattern = $_POST['district'];
                break;
            case 'province_id':
                $pattern = $_POST['province'];
                break;
            case 'accountant_id':
                $pattern = $_POST['accountant'];
                break;
            case 'booker_id':
                $pattern = $_POST['booker'];
                break;
            case 'sale_id':
                $pattern = $_POST['sale'];
                break;
            default:
                $pattern    = "%".trim($_POST['pattern'])."%";
        }
        $smarty->assign("PATTERN", $_POST['pattern']);
    }

    $smarty->assign("GET_FILTER", "field=$field&pattern=$pattern");

    $oGrid  = new paloSantoGrid($smarty);
    //$oGrid->enableExport();   // enable export.
    //$oGrid->setNameFile_Export("HNH_KhachHang");
    //$oGrid->addFilterControl(_tr("Filter applied ").$field." = $namePattern", $arrFilter, array("field" => "name","pattern" => ""));
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter_adress_book.tpl", "", $_POST);
    $total = $padress_book->getAddressBook(NULL,NULL,$field,$pattern,TRUE);
    $total_datos = $total[0]["total"];

    //Paginacion
    if($oGrid->isExportAction()){
        $total  = $total_datos;
        $limit  = $total;
        $arr_cols = array();
    }
    else{
        $limit  = 20;
        $total  = $total_datos;
        $arr_cols = array(
            0 => array("name"      => "",
                "property1" => ""),
            1 => array("name"      => "Mã KH",
                "property1" => ""),
            2 => array("name"      => "Tên khách hàng",
                "property1" => ""),
            3 => array("name"      => "Loại",
                "property1" => ""),
            4 => array("name"      => "Địa chỉ",
                "property1" => ""),
            5 => array("name"      => "Quận",
                "property1" => ""),
            6 => array("name"      => "Tỉnh",
                "property1" => ""),
            7 => array("name"      => "Điện thoại - Liên hệ - Email",
                "property1" => ""),
            8 => array("name"      => "Booker",
                "property1" => ""),
            9 => array("name"      => "Kế toán",
                "property1" => ""),
            10 => array("name"      => "Sale",
                "property1" => ""),
            11 => array("name"      => "Số thẻ GLP",
                "property1" => ""),
            12 => array("name"      => "Lựa chọn",
                "property1" => ""),
        );
    }
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
        if($oGrid->isExportAction())
            foreach($arrResult as $key => $adress_book){
                $typeContact = "";
                switch ($adress_book['type']){
                    case '0':
                        $typeContact = "Khách lẽ";
                        break;
                    case '1':
                        $typeContact = "Khách lẽ";
                        break;
                    case '2':
                        $typeContact = 'Khách công ty';
                        break;
                    case '3':
                        $typeContact = "Khách đại lý";
                        break;
                    default:
                        break;
                }
                $arrData[]  = $arrTmp;
        }
        else
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
                $contact = '';
                if (isset($adress_book['contact']) && count($adress_book['contact'])>0)
                    foreach ($adress_book['contact'] as $row) {
                        $call = $row['phone'];
                        $email = $row['email'];
                        $phone_list = is_null($call)||trim($call)==''?'':"<a href='?menu=$module_name&action=call2phone&id=".$call."'><img border=0 src='/modules/$module_name/images/call.png' title='Gọi số ".$call."'/></a> ".$call.''; //SDT column
                        $email_list = is_null($email)||trim($email)==''?'':'- <a title="Gửi mail đến hộp mail này" href="mailto:'.$email.'?Subject=[CallCenter]:" target="_top">'.$email.'</a><br/>';
                        $name_list = is_null($row['name'])||trim($row['name'])==''?'':' - '.$row['name'];
                        $contact .= $phone_list.$name_list.$email_list.'</br>';
                    }
                $arrTmp = array();
                $arrTmp[]  = "<input type='checkbox' name='contact_{$adress_book['id']}'  />";
                $arrTmp[]  = $adress_book['customer_code']; //Ten column
                $arrTmp[]  = $adress_book['customer_name']; //Ten column
                $arrTmp[]  = $typeContact;
                $arrTmp[]  = $adress_book['address'];
                $arrTmp[]  = $adress_book['district'];
                $arrTmp[]  = $adress_book['province'];
                $arrTmp[]  = $contact;
                $arrTmp[]  = $adress_book['booker'];
                $arrTmp[]  = $adress_book['accountant'];
                $arrTmp[]  = $adress_book['sale'];
                $arrTmp[] = $adress_book['membership'];
                $arrTmp[] = "<a href='?menu=$module_name&action=show&id=".$adress_book['id']."'><img src='modules/$module_name/images/extra.png' title='Xem'></a>&nbsp;
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
                        "columns"  => $arr_cols
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
    $arrAccountant = $pBooker->getAccountantList();
    $arrSale = $pBooker->getSaleList();
    $arrProvince = $pBooker->getProvinceList();
    $arrDistrict = $pBooker->getDistrictList();

    $arrFields = array(
                "customer_name"          => array(   "LABEL"            => "Tên khách hàng",
                                            "REQUIRED"              => "yes",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;","id" => "firstname"),
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
                "booker_view"          => array(   "LABEL"           => "NV Booker",
                                            "EDITABLE"               => "yes",
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            //'EDITABLE'              => $bEdit ? 'no' : 'yes',
                                            "INPUT_EXTRA_PARAM"      => $arrBooker,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "sale_view"          => array(   "LABEL"           => "NV Sale",
                                            "EDITABLE"               => "yes",
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            //'EDITABLE'              => $bEdit ? 'no' : 'yes',
                                            "INPUT_EXTRA_PARAM"      => $arrSale,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "accountant_view"          => array(   "LABEL"           => "NV Kế toán",
                                            "EDITABLE"               => "yes",
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            //'EDITABLE'              => $bEdit ? 'no' : 'yes',
                                            "INPUT_EXTRA_PARAM"      => $arrAccountant,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "province_view"          => array(   "LABEL"           => "Tỉnh/thành",
                                            "EDITABLE"               => "yes",
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            //'EDITABLE'              => $bEdit ? 'no' : 'yes',
                                            "INPUT_EXTRA_PARAM"      => $arrProvince,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "district_view"          => array(   "LABEL"           => "Quận/huyện",
                                            "EDITABLE"               => "yes",
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            //'EDITABLE'              => $bEdit ? 'no' : 'yes',
                                            "INPUT_EXTRA_PARAM"      => $arrDistrict,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "membership"          => array(   "LABEL"           => "Thẻ thành viên",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "customer_code"          => array(   "LABEL"           => "Mã khách hàng",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:200px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
        );
    return $arrFields;
}

function save_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pACL, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk, $update=FALSE)
{
    $arrForm   = createFieldForm($pDB_2);
    $oForm     = new paloForm($smarty, $arrForm);

    $elastix_user = $_SESSION['elastix_user'];

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

        $data = array(
            'customer_code' => getParameter('customer_code'),
            'customer_name'  => getParameter('customer_name'),
            'type'  => getParameter('customer_type'),
            'birthday'  => date('Y-m-d',strtotime(getParameter('birthday'))),
            'birthplace'  => getParameter('birthplace'),
            'cmnd'  => getParameter('cmnd'),
            'passport'  => getParameter('passport'),
            'address'  => getParameter('address'),
            'email' => getParameter('email'),
            'booker_id' => getParameter('booker_view'),
            'sale_id' => getParameter('sale_view'),
            'province_id' => getParameter('province_view'),
            'district_id' => getParameter('district_view'),
            'accountant_id' => getParameter('accountant_view'),
            'membership' => getParameter('membership'),
            'contact_name'       => getParameter('contact_name'),
            'contact_phone'       => getParameter('contact_phone'),
            'contact_email'       => getParameter('contact_email'),
        );

        if($update && isset($contactData['id'])){ // actualizacion del contacto
            if($contactData){
                $idt = $contactData['id'];
                $result = $padress_book->updateContact($data,$idt,$elastix_user);
                if(!$result){
                    $smarty->assign("mb_title", "Lỗi:");
                    $smarty->assign("mb_message", $padress_book->errMsg);
                    //header("Location: ?menu=$module_name&action=edit&id=".$_POST['id']);
                    return report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
                }
            }else{
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $arrLang["Internal Error"]);
                //header("Location: ?menu=$module_name&action=edit&id=".$_POST['id']);
                return report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            }
        }else{ //// creacion de contacto

            $result = $padress_book->addContact($data, $elastix_user);
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

    $arrData['customer_name']   = $contactData['customer_name'];
    $arrData['email']        = $contactData['email'];
    $arrData['cmnd']        = $contactData['cmnd'];
    $arrData['passport']    = $contactData['passport'];
    $arrData['birthday']    = date("d-m-Y",strtotime($contactData['birthday']));
    $arrData['birthplace']  = $contactData['birthplace'];
    $arrData['address']     = $contactData['address'];
    $arrData['membership']     = $contactData['membership'];
    $arrData['sale_view']        = $contactData['sale_id'];
    $arrData['province_view']       = $contactData['province_id'];
    $arrData['district_view']        = $contactData['district_id'];
    $arrData['booker_view']        = $contactData['booker_id'];
    $arrData['accountant_view']        = $contactData['accountant_id'];
    $arrData['customer_code']        = $contactData['customer_code'];
    $arrData['contact']       =   $contactData['contact'];

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new_adress_book.tpl",  "Thông tin khách hàng", $arrData);
    $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}

function prefixNumber($number)
{
    if (strlen($number) < 7)
        return '';
    return '9'; //default prefix for outbound call
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
        $prefix = prefixNumber($id);
        $phone2call =  $prefix.$id;
        $result = $padress_book->Call2Phone($dsn_agi_manager, $extension, $phone2call, $extension, $name);
        if(!$result) {
            $smarty->assign("mb_title", "Lỗi: ");
            $smarty->assign("mb_message", "Không nhận diện được số điện thoại!");
        }
        else {
            $smarty->assign("mb_title", "Gọi thành công: ");
            $smarty->assign("mb_message", "Số điện thoại ".$id);
        }
    }
    else{
        $smarty->assign("mb_title", "Lỗi: ");
        $smarty->assign("mb_message", "Phải đăng nhập màn hình chính để lấy thông tin máy nhánh!");
    }

    $content = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
    return $content;
}

function import_test($module_name, $pDB_customer)
{
    include "modules/$module_name/libs/importExcel.php";
    $oImport = new importExcel($pDB_customer);
    $result = $oImport->import2Db("modules/customer/libs/Upload/server/php/files/sample.xls");
    var_dump($result);
}

function import($smarty, $module_name, $local_templates_dir, $pDB_customer, $arrLang, $arrConf)
{
    include "modules/$module_name/libs/importExcel.php";
    include_once "libs/JSON.php";

    $import_dir = "modules/customer/libs/Upload/server/php/files";
    $sFileName = getParameter('file');
    $filePath = "$import_dir/$sFileName";
    $filePath2 = "/var/www/html/$import_dir/$sFileName";
    $sFileSize = strval(filesize($filePath2));

    if (!file_exists($filePath)){
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: không tìm thấy file ' . $filePath;
        $json = new Services_JSON();
        Header('Content-Type: application/json');
        return $json->encode($response);
    }

    $oImport = new importExcel($pDB_customer);
    $result = $oImport->import2Db($filePath);

    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi  - '.$oImport->errMsg;
        $json = new Services_JSON();
        Header('Content-Type: application/json');
        return $json->encode($response);
    }

    $response['action'] = 'import';
    $response['message'] = $result;
    $response['message']['filename'] = $sFileName;
    $response['message']['filesize'] = $sFileSize;

    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($response);
    //echo '<pre>'.print_r($result->import2Db('tmp/KLE.xls'),1).'</pre>';
    //echo '<pre>'.print_r($result->arrError,1).'</pre>';
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
    else if(getParameter("action")=="getImage")
        return "getImage";
    else if(getParameter("action")=="import")
        return "import";
    else if(getParameter("action")=="export")
        return "export";
    else if(getParameter("action")=="import_test")
        return "import_test";
    else
        return "report";
}
?>