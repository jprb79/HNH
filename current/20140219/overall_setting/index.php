<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.4.0-11                                               |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1 2013-11-03 07:11:23 Tri Do tri.do@teamservices.vn Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoOverall_setting.class.php";
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

    //conexion resource
    $pDB = new paloDB($arrConf['cadena_dsn']);

    //actions
    $action = getAction();
    switch($action){
        case "update":
            $content = updateNotification($pDB);
            break;
        case 'refresh':
            $content = refreshNotification($pDB);
            break;
        default: // view_form
            $content = viewFormOverall_setting($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function viewFormOverall_setting($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pOverall_setting = new paloSantoOverall_setting($pDB);
    $oForm = new paloForm($smarty);

    $smarty->assign("icon", "images/list.png");
    // get data
    $result = $pOverall_setting->getNotification();
    $smarty->assign("message", $result[0]['message']);
    $smarty->assign("isActive", $result[0]['isActive']);

    $content = $oForm->fetchForm("$local_templates_dir/form.tpl","Thiết lập hệ thống");
    return $content;
}

function updateNotification(&$pDB)
{
    // collect parameters
    $sMessage = trim(getParameter('message'));
    $sIsActive = trim(getParameter('isActive'));

    $response = array(
        'action'    =>  'updateNotification',
        'message'   =>  'Cập nhật thông báo thành công',
    );

    $pOverall_setting = new paloSantoOverall_setting($pDB);
    $result = $pOverall_setting->updateNotification($sMessage,$sIsActive);
    // return json
    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: ' . $pOverall_setting->errMsg;
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($response);
}

function refreshNotification($pDB)
{
    $response = array(
        'action'    =>  'refreshNotification',
        'message'   =>  'Làm tươi dữ liệu thành công',
    );

    $pOverall_setting = new paloSantoOverall_setting($pDB);
    $result = $pOverall_setting->getNotification();
    // return json
    if (!$result) {
        $response['action'] = 'error';
        $response['message'] = 'Lỗi: ' . $pOverall_setting->errMsg;
    }
    $json = new Services_JSON();
    Header('Content-Type: application/json');
    return $json->encode($result[0]);
}

function getAction()
{
    return getParameter("action");
}
?>