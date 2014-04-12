<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: index.php,v 1.1.1.1 2007/07/06 21:31:21 gcarrillo Exp $ */

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoDB.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoConfig.class.php";
include_once "libs/paloSantoCDR.class.php";
require_once "libs/misc.lib.php";

function _moduleContent(&$smarty, $module_name)
{
    require_once "modules/$module_name/libs/ringgroup.php";

    //include module files
    include_once "modules/$module_name/configs/default.conf.php";

    load_language_module($module_name);

    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // DSN para consulta de cdrs
    $dsn = generarDSNSistema('asteriskuser', 'asteriskcdrdb');
    $pDB     = new paloDB($dsn);
    $oCDR    = new paloSantoCDR($pDB);

    $pDBACL = new paloDB($arrConf['elastix_dsn']['acl']);
    if (!empty($pDBACL->errMsg)) {
        return "ERROR DE DB: $pDBACL->errMsg";
    }
    $pACL = new paloACL($pDBACL);
    if (!empty($pACL->errMsg)) {
        return "ERROR DE ACL: $pACL->errMsg";
    }

    $exten = '6868'; //$pACL->getUserExtension($_SESSION['elastix_user']);
    $isAdministrator = true;//$pACL->isUserAdministratorGroup($_SESSION['elastix_user']);
    if(is_null($exten) || $exten == ""){
        if(!$isAdministrator){
            $smarty->assign('mb_message', "<b>"._tr("contact_admin")."</b>");
            return "";
        }
        else
            $smarty->assign('mb_message', "<b>"._tr("no_extension")."</b>");
    }

    // Para usuarios que no son administradores, se restringe a los CDR de la
    // propia extensión
    $sExtension = ($isAdministrator)? '' : $pACL->getUserExtension($_SESSION['elastix_user']);

    // DSN para consulta de ringgroups
    $dsn_asterisk = generarDSNSistema('asteriskuser', 'asterisk');
    $pDB_asterisk=new paloDB($dsn_asterisk);
    $oRG    = new RingGroup($pDB_asterisk);
    $dataRG = $oRG->getRingGroup();
    $dataRG[''] = _tr('(Any ringgroup)');




    // Cadenas estáticas en la plantilla
    $smarty->assign(array(
        "Filter"    =>  _tr("Filter"),
    ));

    $arrFormElements = array(
        "date_start"  => array("LABEL"                  => _tr("Start Date"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "date_end"    => array("LABEL"                  => _tr("End Date"),
            "REQUIRED"               => "yes",
            "INPUT_TYPE"             => "DATE",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
        "field_name"  => array("LABEL"                  => _tr("Field Name"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => array( "dst"         => _tr("Destination"),
                "src"         => _tr("Source"),
                "channel"     => _tr("Src. Channel"),
                "accountcode" => _tr("Account Code"),
                "dstchannel"  => _tr("Dst. Channel")),
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^(dst|src|channel|dstchannel|accountcode)$"),
        "field_pattern" => array("LABEL"                  => _tr("Field"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "TEXT",
            "INPUT_EXTRA_PARAM"      => "",
            "VALIDATION_TYPE"        => "ereg",
            "VALIDATION_EXTRA_PARAM" => "^[\*|[:alnum:]@_\.,/\-]+$"),
        "status"  => array("LABEL"                  => _tr("Status"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => array(
                "ALL"         => _tr("ALL"),
                "ANSWERED"    => _tr("ANSWERED"),
                "BUSY"        => _tr("BUSY"),
                "FAILED"      => _tr("FAILED"),
                "NO ANSWER "  => _tr("NO ANSWER")),
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
        "ringgroup"  => array("LABEL"                  => _tr("Ring Group"),
            "REQUIRED"               => "no",
            "INPUT_TYPE"             => "SELECT",
            "INPUT_EXTRA_PARAM"      => $dataRG ,
            "VALIDATION_TYPE"        => "text",
            "VALIDATION_EXTRA_PARAM" => ""),
    );

    $oFilterForm = new paloForm($smarty, $arrFormElements);

    // Parámetros base y validación de parámetros
    $url = array('menu' => $module_name);
    $paramFiltroBase = $paramFiltro = array(
        'date_start'    => date("d M Y"),
        'date_end'      => date("d M Y"),
        'field_name'    => 'dst',
        'field_pattern' => '',
        'status'        => 'ALL',
        'ringgroup'     =>  '',
    );
    foreach (array_keys($paramFiltro) as $k) {
        if (!is_null(getParameter($k))){
            $paramFiltro[$k] = getParameter($k);
        }
    }

    $oGrid  = new paloSantoGrid($smarty);
    if($paramFiltro['date_start']==="")
        $paramFiltro['date_start']  = " ";


    if($paramFiltro['date_end']==="")
        $paramFiltro['date_end']  = " ";


    $valueFieldName = $arrFormElements['field_name']["INPUT_EXTRA_PARAM"][$paramFiltro['field_name']];
    $valueStatus = $arrFormElements['status']["INPUT_EXTRA_PARAM"][$paramFiltro['status']];
    $valueRingGRoup = $arrFormElements['ringgroup']["INPUT_EXTRA_PARAM"][$paramFiltro['ringgroup']];


    $oGrid->addFilterControl(_tr("Filter applied: ")._tr("Start Date")." = ".$paramFiltro['date_start'].", "._tr("End Date")." = ".
    $paramFiltro['date_end'], $paramFiltro, array('date_start' => date("d M Y"),'date_end' => date("d M Y")),true);

    $oGrid->addFilterControl(_tr("Filter applied: ").$valueFieldName." = ".$paramFiltro['field_pattern'],$paramFiltro, array('field_name' => "dst",'field_pattern' => ""));

    $oGrid->addFilterControl(_tr("Filter applied: ")._tr("Status")." = ".$valueStatus,$paramFiltro, array('status' => 'ALL'),true);

    $oGrid->addFilterControl(_tr("Filter applied: ")._tr("Ring Group")." = ".$valueRingGRoup,$paramFiltro, array('ringgroup' => ''));


    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $paramFiltro);
    if (!$oFilterForm->validateForm($paramFiltro)) {
        $smarty->assign(array(
            'mb_title'      =>  _tr('Validation Error'),
            'mb_message'    =>  '<b>'._tr('The following fields contain errors').':</b><br/>'.
            implode(', ', array_keys($oFilterForm->arrErroresValidacion)),
        ));
        $paramFiltro = $paramFiltroBase;
        unset($_POST['delete']);    // Se aborta el intento de borrar CDRs, si había uno.
    }

    // Tradudir fechas a formato ISO para comparación y para API de CDRs.
    $url = array_merge($url, $paramFiltro);
    $paramFiltro['date_start'] = translateDate($paramFiltro['date_start']).' 00:00:00';
    $paramFiltro['date_end'] = translateDate($paramFiltro['date_end']).' 23:59:59';

    // Valores de filtrado que no se seleccionan mediante filtro
    if ($sExtension != '') $paramFiltro['extension'] = $sExtension;

    // Ejecutar el borrado, si se ha validado.
    if (isset($_POST['delete'])) {
        if($isAdministrator){
            if($paramFiltro['date_start'] <= $paramFiltro['date_end']){
                $r = $oCDR->borrarCDRs($paramFiltro);
                if (!$r) $smarty->assign(array(
                    'mb_title'      =>  _tr('ERROR'),
                    'mb_message'    =>  $oCDR->errMsg,
                ));
            }else{
                $smarty->assign(array(
                    'mb_title'      =>  _tr('ERROR'),
                    'mb_message'    =>  _tr("Please End Date must be greater than Start Date"),
                ));
            }
        }
        else{
            $smarty->assign(array(
                'mb_title'      =>  _tr('ERROR'),
                'mb_message'    =>  _tr("Only administrators can delete CDRs"),
            ));
        }
    }

    $oGrid->setTitle(_tr("CDR Report"));
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export(_tr("CDRReport"));
    $oGrid->setURL($url);
    //if($isAdministrator)
    //$oGrid->deleteList("Are you sure you wish to delete CDR(s) Report(s)?","delete",_tr("Delete"));

    $arrData = null;
    if(!isset($sExtension) || $sExtension == ""  && !$isAdministrator)
        $total = 0;
    else
        $total = $oCDR->contarCDRs($paramFiltro);

    if($oGrid->isExportAction()){
        $limit = $total;
        $offset = 0;

        $arrColumns = array(_tr("Date"), _tr("Source"), _tr("Ring Group"), _tr("Destination"), _tr("Src. Channel"),_tr("Account Code"),_tr("Dst. Channel"),_tr("Status"),_tr("Duration"));
        $oGrid->setColumns($arrColumns);

        $arrResult = $oCDR->listarCDRs($paramFiltro, $limit, $offset);

        if(is_array($arrResult['cdrs']) && $total>0){
            foreach($arrResult['cdrs'] as $key => $value){
                $arrTmp[0] = date("d-m-Y H:i:s",strtotime($value[0]));
                $arrTmp[1] = $value[1];
                $arrTmp[2] = $value[11];
                $arrTmp[3] = $value[3];
                $arrTmp[4] = $value[9];
                $arrTmp[5] = $value[5];
                $iDuracion = $value[8];
                $iSec = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iSec) / 60);
                $iMin = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iMin) / 60);
                $sTiempo = "{$value[6]}s";
                if ($value[6] >= 60) {
                    if ($iDuracion > 0) $sTiempo .= " ({$iDuracion}h {$iMin}m {$iSec}s)";
                    elseif ($iMin > 0)  $sTiempo .= " ({$iMin}m {$iSec}s)";
                }
                $arrTmp[7] = $sTiempo;
                $arrData[] = $arrTmp;
            }
        }
        if (!is_array($arrResult)) {
            $smarty->assign(array(
                'mb_title'      =>  _tr('ERROR'),
                'mb_message'    =>  $oCDR->errMsg,
            ));
        }
    }else {
        $limit = 20;
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total);

        $offset = $oGrid->calculateOffset();
        $arrResult = $oCDR->listarCDRs($paramFiltro, $limit, $offset);

        $arrColumns = array('STT',_tr("Date"), _tr("Source"), _tr("Destination"), _tr("Src. Channel"),_tr("Dst. Channel"),_tr("Status"),_tr("Duration"));
        $oGrid->setColumns($arrColumns);

        if(is_array($arrResult['cdrs']) && $total>0){
            $index = 0;
            foreach($arrResult['cdrs'] as $key => $value){
                $arrTmp[0] = $index;
                $arrTmp[1] = date("d-m-Y H:i:s",strtotime($value[0]));
                $arrTmp[2] = $value[1];
                $arrTmp[3] = $value[2];
                $arrTmp[4] = channel_lookup($pDB_asterisk,$value[3]);
                $arrTmp[5] = channel_lookup($pDB_asterisk,$value[4]);
                $arrTmp[6] = $value[5];
                $iDuracion = $value[8];
                $iSec = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iSec) / 60);
                $iMin = $iDuracion % 60; $iDuracion = (int)(($iDuracion - $iMin) / 60);
                $sTiempo = "{$value[8]}s";
                if ($value[7] >= 60) {
                    if ($iDuracion > 0) $sTiempo .= " ({$iDuracion}h {$iMin}m {$iSec}s)";
                    elseif ($iMin > 0)  $sTiempo .= " ({$iMin}m {$iSec}s)";
                }
                $arrTmp[7] = $sTiempo;
                $arrData[] = $arrTmp;
                $index++;
            }
        }
        if (!is_array($arrResult)) {
            $smarty->assign(array(
                'mb_title'      =>  _tr('ERROR'),
                'mb_message'    =>  $oCDR->errMsg,
            ));
        }
    }
    $oGrid->setData($arrData);
    $smarty->assign("SHOW", _tr("Show"));
    $oGrid->showFilter($htmlFilter);
    $content = $oGrid->fetchGrid();
    return $content;
}

function channel_lookup($pDB_asterisk,$channel)
{
    if (trim($channel) == '')
        return '';
    elseif (strpos($channel,'E1_Trunk_CoBac'))
        return 'E1_Trunk_CoBac';

    $number = substr($channel,strpos($channel,'/')+1,strpos($channel,'-')-strpos($channel,'/')-1);
    $sql = "Select name from call_center.agent where number like '%$number%'";
    $r = $pDB_asterisk->getFirstRowQuery($sql, false);
	
    if (count($r) > 0)
        return $r[0].'-'.$number;
    else {
        $trunk = array(
            'GXWT1' =>  '38251123',
            'GXWT2' =>  '38273273',
            'GXWT3' =>  '38273899',
            'GXWT4' =>  '38248311',
            'GXWT5' =>  '38251379',
            'GXWT6' =>  '38251179',            
            'GXW2T1' =>  '38248325',
            'GXW2T2' =>  '38258234',
            'GXW2T3' =>  '38274291',
            'GXW2T4' =>  '38258220',
            'GXW2T5' =>  '38248329',
            'GXW2T6' =>  '38274251',
            'GXW2T7' =>  '38248940',            
			'GXW3T1' =>  '38273880',
            'GXW3T2' =>  '38273878',
            'GXW3T3' =>  '38274120',
            'GXW3T4' =>  '38248990',
            'GXW3T5' =>  '38248924',
            'GXW3T6' =>  '38248328',
            'GXW3T7' =>  '38245988',
            'GXW3T8' =>  '38248889',
			'GXW4T1' =>  '38248668',
            'GXW4T2' =>  '38248326',
            'GXW4T3' =>  '38273879',
            'GXW4T4' =>  '38273877',
            'GXW4T5' =>  '38248889',
            'GXW4T6' =>  '38248624',
            'GXW4T7' =>  '38248586',
            'GXW4T8' =>  '38273898',
			'GXW5T1' =>  '38221109',
            'GXW5T2' =>  '38273882',
            'GXW5T3' =>  '38221089',
            'GXW5T4' =>  '38221090',
            'GXW5T5' =>  '38221076',
            'GXW5T6' =>  '38273884',
            'GXW5T7' =>  '38220717',            
			'GXW6T1' =>  '38242362',
            'GXW6T2' =>  '38258208',
            'GXW6T3' =>  '38237905',
            'GXW6T4' =>  '38224791',
            'GXW6T5' =>  '38256256',
            'GXW6T6' =>  '38237913',            
        );
        foreach ($trunk as $key=>$value){
            if (strpos($channel,$key)>0)
                return $key.'-'.$value;
        }
    }
    return $channel;
}
?>