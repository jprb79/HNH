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
  $Id: index.php,v 1.1 2007/01/09 23:49:36 alex Exp $
*/

/*
 * Esta clase sirve para abstraer la lectura de la extensión del usuario
 * logoneado, la cual se requiere para Calls, Voicemails, Faxes 
 */
abstract class Applet_ReportForExtension
{
    function handleJSON_getContent($smarty, $module_name, $appletlist)
    {
        $respuesta = array(
            'status'    =>  'success',
            'message'   =>  '(no message)',
        );

        // Obtener extensión del usuario logoneado
        global $arrConf;
        $dbAcl = new paloDB($arrConf["elastix_dsn"]["acl"]);
        $pACL  = new paloACL($dbAcl);
        $extension = $pACL->getUserExtension($_SESSION["elastix_user"]);
        if (empty($extension) || !ctype_digit($extension)) {
            $respuesta['status'] = 'error';
            $respuesta['message'] = _tr("You haven't extension");
            if (!empty($pACL->errMsg)) $respuesta['message'] = $pACL->errMsg;
        } else {
            $this->_formatReportForExtension($smarty, $module_name, $extension, $respuesta);
        }
    
        $json = new Services_JSON();
        Header('Content-Type: application/json');
        return $json->encode($respuesta);
    }
    
    abstract protected function _formatReportForExtension($smarty, $module_name, $extension, &$respuesta);
}
?>