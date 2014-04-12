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
            $content = new_adress_book($smarty, $module_name, $local_templates_dir, $arrLang);
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
        default:
            $content = report_adress_book($smarty, $module_name, $local_templates_dir, $pDB_customer, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            break;
    }

    return $content;
}

function new_adress_book($smarty, $module_name, $local_templates_dir, $arrLang)
{
    $arrFormadress_book = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormadress_book);

    $smarty->assign("Show", 1);
    $smarty->assign("ShowImg",0);
    $smarty->assign("REQUIRED_FIELD", "Bắt buộc nhập");
    $smarty->assign("SAVE", "Lưu");
    $smarty->assign("CANCEL","Hủy bỏ");
    $smarty->assign("title", "Danh bạ điện thoại");
    $smarty->assign("icon", "modules/$module_name/images/address_book.png");


    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl", "Danh bạ điện thoại", $_POST);

    $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo;
}


/*
******** Funciones del modulo
*/
function report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloPhoneBook($pDB);

    $arrComboElements = array(  "firstname"     => "Tên",
                                "lastname"      =>  "Họ",
                                "extension"         => "Số nội bộ",
                                "mobile"         => "Số điện thoại",
                                "company_mobile"         => "Số công ty",
                        );

    $arrFormElements = array(   "field" => array(   "LABEL"                  => "Tìm theo",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "SELECT",
                                                    "INPUT_EXTRA_PARAM"      => $arrComboElements,
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => ""),

                                "pattern" => array( "LABEL"          => "",
                                                    "REQUIRED"               => "no",
                                                    "INPUT_TYPE"             => "TEXT",
                                                    "INPUT_EXTRA_PARAM"      => "",
                                                    "VALIDATION_TYPE"        => "text",
                                                    "VALIDATION_EXTRA_PARAM" => "",
                                                    "INPUT_EXTRA_PARAM"      => array('id' => 'filter_value')),
                                );

    $oFilterForm = new paloForm($smarty, $arrFormElements);
    $smarty->assign("SHOW", "Tìm");
    $smarty->assign("NEW_adress_book", "Thêm khách hàng mới");
    $smarty->assign("CSV", $arrLang["CSV"]);
    $smarty->assign("module_name", $module_name);

    $field   = NULL;
    $pattern = NULL;
    $namePattern = NULL;
    $allowSelection = array("firstname", "lastname","extension","mobile","company_mobile");
    if(isset($_POST['field']) and isset($_POST['pattern']) and ($_POST['pattern']!="")){
        $field      = $_POST['field'];
        if (!in_array($field, $allowSelection))
            $field = "firstname";
        $pattern    = "%$_POST[pattern]%";
        $namePattern = $_POST['pattern'];
        $nameField=$arrComboElements[$field];
    }

    $arrFilter = array("field"=>$field,"pattern" =>$namePattern);

    $startDate = $endDate = date("Y-m-d H:i:s");
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->addFilterControl(_tr("Filter applied ").$field." = $namePattern", $arrFilter, array("field" => "name","pattern" => ""));
    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $arrFilter);
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
        foreach($arrResult as $key => $adress_book){
            $arrTmp[0]  = "<input type='checkbox' name='contact_{$adress_book['id']}'  />";
            $arrTmp[1]  = $adress_book['firstname']; //Ten column
            $arrTmp[2]  = $adress_book['lastname']; //Ten column
            $arrTmp[3]  = $adress_book['department']; //Ten column
            $arrTmp[4]  = is_null($adress_book['company_mobile'])||$adress_book['company_mobile']==''?'':"<a href='?menu=$module_name&action=call2phone&id=".$adress_book['company_mobile']."'><img border=0 src='/modules/$module_name/images/call.png' title='Gọi số ".$adress_book['company_mobile']."'/></a>".$adress_book['company_mobile'];
            $arrTmp[5]  = is_null($adress_book['mobile'])||$adress_book['mobile']==''?'':"<a href='?menu=$module_name&action=call2phone&id=".$adress_book['mobile']."'><img border=0 src='/modules/$module_name/images/call.png' title='Gọi số ".$adress_book['mobile']."'/></a>".$adress_book['mobile'];
            $arrTmp[6]  = is_null($adress_book['extension'])||$adress_book['extension']==''?'':"<a href='?menu=$module_name&action=call2phone&id=".$adress_book['extension']."'><img border=0 src='/modules/$module_name/images/call.png' title='Gọi số ".$adress_book['extension']."'/></a>".$adress_book['extension'];
            $arrTmp[7]  = '<a title="Gửi mail đến hộp mail này" href="mailto:'.$adress_book['email'].'?Subject=[CallCenter]:" target="_top">'.$adress_book['email'].'</a>';
            $arrTmp[8]  = $adress_book['note'];
            $arrTmp[9]  = "<a href='?menu=$module_name&action=edit&id=".$adress_book['id']."'>Edit</a>";
            $arrData[]  = $arrTmp;
        }
    }

    $oGrid->deleteList("Bạn có muốn xóa người này không?","delete","Xóa");
    $arrGrid = array(   "title"    => "Danh bạ điện thoại",
                        "url"      => array('menu' => $module_name, 'filter' => $pattern),
                        "icon"     => "modules/$module_name/images/address_book.png",
                        "width"    => "99%",
                        "start"    => $inicio,
                        "end"      => $end,
                        "total"    => $total,
                        "columns"  => array(0 => array("name"      => '',
                                                    "property1" => ""),
                                            1 => array("name"      => "Tên",
                                                    "property1" => ""),
                                            2 => array("name"      => "Họ",
                                                    "property1" => ""),
                                            3 => array("name"      => "Phòng - Công ty",
                                                "property1" => ""),
                                            4 => array("name"      => "Số di động công ty",
                                                "property1" => ""),
                                            5 => array("name"      => "Số di động",
                                                    "property1" => ""),
                                            6 => array("name"      => "Số nội bộ",
                                                    "property1" => ""),
                                            7 => array("name"      => "Email",
                                                    "property1" => ""),
                                            8 => array("name"      => "Ghi chú",
                                                    "property1" => ""),
                                            9 => array("name"      => "Sửa",
                                                    "property1" => ""),
                                        )
                    );
    $oGrid->addNew("new","Thêm danh bạ");
    $oGrid->showFilter(trim($htmlFilter));
    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function createFieldForm()
{
    $arrFields = array(
                "firstname"          => array(   "LABEL"            => "Tên",
                                            "REQUIRED"              => "yes",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;","id" => "firstname"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "lastname"          => array(   "LABEL"                 => "Họ lót",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "department"        => array(   "LABEL"                 => "Phòng - Công ty",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "company_mobile"            => array(   "LABEL"           => "Số di động công ty",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> "^[\*|#]*[[:digit:]]*$"),
                "mobile"            => array(   "LABEL"           => "Số di động",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> "^[\*|#]*[[:digit:]]*$"),
                "extension"         => array(   "LABEL"                 => "Số nội bộ",
                                            "REQUIRED"              => "no",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;"),
                                            "INPUT_TYPE"            => "TEXT",
                                            "VALIDATION_TYPE"       => "text",
                                            "VALIDATION_EXTRA_PARAM"=> "^[\*|#]*[[:digit:]]*$"),
                "email"             => array(   "LABEL"                 => "Email",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXT",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;"),
                                            "VALIDATION_TYPE"       => "email",
                                            "VALIDATION_EXTRA_PARAM"=> ""),
                "note"              => array(         "LABEL"        => "Ghi chú",
                                            "REQUIRED"              => "no",
                                            "INPUT_TYPE"            => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"     => array("style" => "width:300px;"),
                                            "VALIDATION_TYPE"       => "text",
                                            "EDITABLE"              => "si",
                                            "COLS"                  => "40",
                                            "ROWS"                  => "4",
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

    if(!$oForm->validateForm($_POST)) {
        // Falla la validación básica del formulario
        $smarty->assign("mb_title", "Lỗi");
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>Các trường sau có lỗi:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
                $strErrorMsg .= "$k, ";
            }
        }

        $smarty->assign("mb_message", $strErrorMsg);

        $smarty->assign("REQUIRED_FIELD", "Bắt buộc nhập");
        $smarty->assign("SAVE", "Lưu");
        $smarty->assign("CANCEL", "Hủy bỏ");
        $smarty->assign("title", "Danh bạ điện thoại");

        if($update)
        {
            $_POST["edit"] = 'edit';
            return view_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
        }else{
            $smarty->assign("Show", 1);
            $smarty->assign("ShowImg",1);
            $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl", "Danh bạ điện thoại", $_POST);
            $contenidoModulo = "<form  method='POST' enctype='multipart/form-data' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
            return $contenidoModulo;
        }
    }else{ //NO HAY ERRORES
        $idPost    = getParameter('id');
        $padress_book = new paloPhoneBook($pDB);
        $contactData = $padress_book->contactData($idPost);

        $firstname  = getParameter('firstname');
        $lastname  = getParameter('lastname');
        $department  = getParameter('department');
        $company_mobile  = getParameter('company_mobile');
        $mobile  = getParameter('mobile');
        $extension  = getParameter('extension');
        $email  = getParameter('email');
        $note  = getParameter('note');

        $data = array($firstname,$lastname,$department,$company_mobile,$mobile,$extension,$email,$note);

        if($update && isset($contactData['id'])){ // actualizacion del contacto
            if($contactData){
                $idt = $contactData['id'];
                $result = $padress_book->updateContact($data,$idt);
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
            $result = $padress_book->addContact($data);
            if(!$result){
                $smarty->assign("mb_title", "Lỗi");
                $smarty->assign("mb_message", $padress_book->errMsg);
                return new_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
            }
        }

        if(!$result)
            return($pDB->errMsg);

        //if($_POST['id'])
        //    header("Location: ?menu=$module_name&action=show&type=$directory&id=".$_POST['id']);
        //else
            header("Location: ?menu=$module_name");
    }
}

function deleteContact($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk)
{
    $padress_book = new paloPhoneBook($pDB);

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
    $arrFormadress_book = createFieldForm();
    $padress_book = new paloPhoneBook($pDB);
    $oForm = new paloForm($smarty,$arrFormadress_book);
    $id = getParameter('id');

    if(isset($_POST["edit"]) || $update==TRUE){
        $oForm->setEditMode();
        $smarty->assign("Commit", 1);
        $smarty->assign("SAVE","Thay đổi");
    }else{
        $oForm->setViewMode();
        $smarty->assign("Edit", 1);
    }

    $smarty->assign("EDIT","Thay đổi");
    $smarty->assign("REQUIRED_FIELD","Bắt buộc");
    $smarty->assign("CANCEL", "Hủy bỏ");
    $smarty->assign("title", "Danh bạ điện thoại");

    if(isset($_POST['address_book_options']) && $_POST['address_book_options']=='address_from_csv')
        $smarty->assign("check_csv", "checked");
    else $smarty->assign("check_new_contact", "checked");
 

    $smarty->assign("SAVE", "Lưu");
    $smarty->assign("CANCEL", "Hủy bỏ");
    $smarty->assign("REQUIRED_FIELD", "Bắt buộc");
    $smarty->assign("label_file", $arrLang["File"]);
    $smarty->assign("DOWNLOAD", $arrLang["Download Address Book"]);
    $smarty->assign("HeaderFile", $arrLang["Header File Address Book"]);
    $smarty->assign("AboutContacts", $arrLang["About Address Book"]);

    $smarty->assign("style_address_options", "style='display:none'");

    $contactData = $padress_book->contactData($id);
    if($contactData){
        $smarty->assign("ID",$id);
    }else{
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $smarty->assign("mb_message", $arrLang["Not_allowed_contact"]); 
        return report_adress_book($smarty, $module_name, $local_templates_dir, $pDB, $pDB_2, $arrLang, $arrConf, $dsn_agi_manager, $dsnAsterisk);
    }

    $arrData['firstname']   = $contactData['firstname'];
    $arrData['lastname']    = $contactData['lastname'];
    $arrData['department']    = $contactData['department'];
    $arrData['company_mobile']  =   $contactData['company_mobile'];
    $arrData['mobile']  =   $contactData['mobile'];
    $arrData['extension']    = $contactData['extension'];
    $arrData['email']        = $contactData['email'];
    $arrData['note']    = $contactData['note'];

    $htmlForm = $oForm->fetchForm("$local_templates_dir/new.tpl",  "Danh bạ điện thoại", $arrData);
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
    $padress_book = new paloPhoneBook($pDB);
    //echo '<pre>'.print_r($_SESSION,1).'</pre>';die;
    $extension = $_SESSION['callcenter']['agente'];
    $name = $_SESSION['callcenter']['agente_nombre'];
    if($extension != "")
    {
        $id = isset($_GET['id'])?$_GET['id']:(isset($_POST['id'])?$_POST['id']:"");
        $prefix = prefixNumber($id);
        $phone2call = $prefix.$id;
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