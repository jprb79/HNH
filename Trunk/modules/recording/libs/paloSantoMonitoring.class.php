<?php

class paloSantoMonitoring {
    var $_DB;
    var $errMsg;
    var $arrConf;

    function paloSantoMonitoring(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    function setConfig($config){
        $this->arrConf = $config;
    }

    private function getConfig(){
        return $this->arrConf['cadena_dsn'];
    }

    /*HERE YOUR FUNCTIONS*/

    function getNumMonitoring($filter_field, $filter_value, $extension, $date_initial, $date_final)
    {
        $where = "";
        $arrParam = array();
        if(isset($filter_field) && $filter_field !="" && isset($filter_value) && $filter_value !=""){
            if ($filter_field == "ext")
                $where = " AND (channel like 'SIP/$filter_value%' OR dstchannel like 'SIP/$filter_value%') ";
            elseif($filter_field == "userfield"){
                $in_val = strtolower($filter_value);
                switch($in_val){
                    case "outgoing":
                        $where = " AND (userfield like 'audio:O%' OR userfield like 'audio:/var/spool/asterisk/monitor/O%') ";
                        break;
                    case "group":
                        $where = " AND (userfield like 'audio:g%' OR userfield like 'audio:/var/spool/asterisk/monitor/g%') ";
                        break;
                    case "queue":
                        $where = " AND (userfield like 'audio:q%' OR userfield like 'audio:/var/spool/asterisk/monitor/q%') ";
                        break;
                    default :
                        $where = " AND userfield REGEXP '[[:<:]]audio:[0-9]' ";
                        break;
                }
            }else{
                $arrParam[] = "$filter_value%";
                $where = " AND $filter_field like ? ";
            }
        }

        if((isset($date_initial) & $date_initial !="") && (isset($date_final) & $date_final !="")){
            $arrParam[] = $date_initial;
            $arrParam[] = $date_final;
            $where .= " AND (calldate >= ? AND calldate <= ?) ";

        }else{
            $date_initial = date('Y-m-d')." 00:00:00";
            $date_final   = date('Y-m-d')." 23:59:59";
            $arrParam[] = $date_initial;
            $arrParam[] = $date_final;
            $where .= " AND (calldate >= ? AND calldate <= ?) ";
        }

        if(isset($extension) & $extension !=""){
            $arrParam[] = $extension;
            $arrParam[] = $extension;
            $where .= " AND (src=? OR dst=?)";
        }

        $query   = "SELECT COUNT(*) FROM cdr WHERE 1 = 1  $where";

        $result=$this->_DB->getFirstRowQuery($query,false,$arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result[0];
    }

    function searchTransfer($param, $record_dir)
    {
        $arr_result = array();
        switch ($param['dcontext']){
            case 'from-internal-xfer':
                foreach (glob($record_dir."*".$param['uniqueid'].".gsm") as $filename) {
                    if (!strpos($filename,$param['filename']))
                        $arr_result[] = $filename;//substr($filename,0,strlen($filename)-4);
                    //tri
                }
                break;
            default:
                break;
        }

        if (count($arr_result) == 0 && $param['userfield2']==!'') {
            $arr_result[] = '/var/spool/asterisk/monitor/'.basename($param['userfield2']).'.gsm';
        }

        return $arr_result;
    }

    private function checkFile($filename){
        $record_dir = "/var/spool/asterisk/monitor/";
        $src_file = $record_dir . $filename;
        if (file_exists($src_file))
            return $filename;
        // find similar name
        $part = explode('-',$filename);
        $prefix= substr($filename,0,1)=='q'?$part[0].'-'.$part[1].'-'.$part[2]:$part[0].'-'.$part[1];
        $search = glob($record_dir.$prefix.'*');

        if (count($search)==0){
            $prefix = substr($filename,0,1)=='q'?substr($part[3],0,strlen($part[3])-3):substr($part[2],0,strlen($part[2])-3);
            $search = glob($record_dir.'*'.$prefix.'*');
        }

        if (count($search)==0){
            $prefix = substr($filename,0,1)=='q'?$part[0].'-'.$part[1].'-'.substr($part[2],0,4):$part[0].
                '-'.substr($part[1],0,4);
            $search = glob($record_dir.$prefix.'*');
        }

        return $search[0];
    }

    function getMonitoring($limit, $offset, $filter_field, $filter_value, $extension, $date_initial, $date_final)
    {
        $where = "";
        $arrParam = array();
        if(isset($filter_field) & $filter_field !=""){
            if ($filter_field == "ext")
                $where = " AND (channel like 'SIP/$filter_value%' OR dstchannel like 'SIP/$filter_value%') ";
            elseif($filter_field == "userfield"){
                $in_val = strtolower($filter_value);
                switch($in_val){
                    case "outgoing":
                        $where = " AND (userfield like 'audio:O%' OR userfield like 'audio:/var/spool/asterisk/monitor/O%') ";
                        break;
                    case "group":
                        $where = " AND (userfield like 'audio:g%' OR userfield like 'audio:/var/spool/asterisk/monitor/g%') ";
                        break;
                    case "queue":
                        $where = " AND (userfield like 'audio:q%' OR userfield like 'audio:/var/spool/asterisk/monitor/q%') ";
                        break;
                    default :
                        $where = " AND userfield REGEXP '[[:<:]]audio:[0-9]' ";
                        break;
                }
            }else{
                $arrParam[] = "$filter_value%";
                $where = " AND $filter_field like ? ";
            }
        }

        if((isset($date_initial) & $date_initial !="") && (isset($date_final) & $date_final !="")){
            $arrParam[] = $date_initial;
            $arrParam[] = $date_final;
            $where .= " AND (calldate >= ? AND calldate <= ?) ";
        }else{
            $date_initial = date('Y-m-d')." 00:00:00";
            $date_final   = date('Y-m-d')." 23:59:59";
            $arrParam[] = $date_initial;
            $arrParam[] = $date_final;
            $where .= " AND (calldate >= ? AND calldate <= ?) ";
        }

        if(isset($extension) & $extension !=""){
            $arrParam[] = $extension;
            $arrParam[] = $extension;
            $where .= " AND (src=? OR dst=?)";
        }

        $arrParam[] = $limit;
        $arrParam[] = $offset;
        $query   = "SELECT * FROM cdr WHERE 1 = 1 $where ORDER BY uniqueid DESC LIMIT ? OFFSET ?";

        $result=$this->_DB->fetchTable($query, true, $arrParam);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        //tri
        require_once "modules/agent_console/getinfo.php";

        $oMainConsole = new getInfoMainConsole();
        $oMainConsole->callcenter_db_connect($this->getConfig());
        $index = 0;
        foreach ($result as $row){
            // get note
            $arr_note = $oMainConsole->getNoteCdr($row['uniqueid']);
            // get delivery info
            //$arrDelivery = $oMainConsole->getDeliveryCdr($row['uniqueid']);
            $result[$index]['note_id'] = $arr_note['call_entry_id'];
            $result[$index]['note']  = $arr_note['note'];
            $namefile = basename($result[$index]['userfield']);
            $namefile = str_replace("audio:","",$namefile);
            // handle all situations
            switch ($result[$index]['dcontext']){
                case 'from-internal-xfer':

                    break;
                case 'from-did-direct':
                    if ($result[$index]['userfield'] == '') {
                        $filename = $this->checkFile(date('Ymd-His',strtotime($result[$index]['calldate'])).'-'.$result[$index]['uniqueid'].'gsm');
                        if (is_null($filename))
                            $result[$index]['userfield'] = '';
                        $result[$index]['userfield'] = "audio:".$filename;
                        break;
                    }
                    break;
                case 'from-internal':
                    // in case no audio file
                    if ($result[$index]['userfield'] == '') {
                        $filename = $this->checkFile(date('Ymd-His',strtotime($result[$index]['calldate'])).'-'.$result[$index]['uniqueid'].'gsm');
                        if (is_null($filename))
                            $result[$index]['userfield'] = '';
                        $result[$index]['userfield'] = "audio:".$filename;
                        break;
                    }
                    if (substr($namefile,0,1)=='q') {
                        $result[$index]['userfield2'] = $result[$index]['userfield'];
                        $filename = $this->checkFile(date('Ymd-His',strtotime($result[$index]['calldate'])).'-'.$result[$index]['uniqueid'].'.gsm');
                        if (is_null($filename))
                            $result[$index]['userfield'] = '';
                        else
                            $result[$index]['userfield'] = "audio:".$filename;
                        $result[$index]['dcontext'] = 'from-internal-xfer';
                        break;
                    }
                    if (!strpos($result[$index]['userfield'],$result[$index]['uniqueid'])) {
                        $filename = $this->checkFile(date('Ymd-His',strtotime($result[$index]['calldate'])).'-'.$result[$index]['uniqueid'].'.gsm');
                        if (is_null($filename))
                            $result[$index]['userfield'] = '';
                        $result[$index]['userfield'] = "audio:".$filename;
                        break;
                    }
                    break;
                case 'ext-queues':
                    if (!strpos($result[$index]['userfield'],$result[$index]['uniqueid'])){
                        $filename = $this->checkFile("q".$result[$index]['dst']."-".date('Ymd-His',strtotime($result[$index]['calldate'])).'-'.$result[$index]['uniqueid'].'.gsm');
                        $result[$index]['userfield'] = "audio:/var/spool/asterisk/monitor/".$filename;
                        break;
                    }
                    break;
                default:
                    break;
            }
            $index++;
        }
        $oMainConsole->callcenter_db_disconnect();
        return $result;
    }

    function getMonitoringById($id)
    {
        $query = "SELECT * FROM cdr WHERE uniqueid=?";

        $result=$this->_DB->getFirstRowQuery($query,true,array($id));

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function Obtain_UID_From_User($user)
    {
        global $pACL;
        $uid = $pACL->getIdUser($user);
        if($uid!=FALSE)
            return $uid;
        else return -1;
    }

    function obtainExtension($db,$id){
        $query = "SELECT extension FROM acl_user WHERE id=?";

        $result = $db->getFirstRowQuery($query,true, array($id));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result['extension'];
    }

    function deleteRecordFile($id){
        $result = $this->_DB->genQuery(
            'UPDATE cdr SET userfield = ? WHERE uniqueid = ?',
            array('audio:deleted', $id));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return true;
    }

    function getRecordName($id){
        $query = "SELECT userfield FROM cdr WHERE uniqueid=?";
        $result = $this->_DB->getFirstRowQuery($query,true,array($id));
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result['userfield'];
    }

    function getAudioByUniqueId($id, $namefile = NULL)
    {

        $query = 'SELECT userfield FROM cdr WHERE uniqueid = ?';
        $parame = array($id);
        if (!is_null($namefile)) {
            $query .= ' AND userfield LIKE ?';
            $parame[] = 'audio:%'.$namefile.'%';
        }
        $result=$this->_DB->getFirstRowQuery($query, true, $parame);
        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }

        return $result;
    }

    function recordBelongsToUser($record, $extension)
    {
        $query = "select count(*) from cdr where uniqueid=? and (src=? or dst=?)";
        $result=$this->_DB->getFirstRowQuery($query, false, array($record,$extension,$extension));
        if($result===FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        if($result[0] > 0)
            return true;
        else
            return false;
    }
}
?>