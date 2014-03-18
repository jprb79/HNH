<?php
class paloSantoOverall_setting{
    var $_DB;
    var $errMsg;

    function paloSantoOverall_setting(&$pDB)
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

    /*HERE YOUR FUNCTIONS*/
    function getNotification()
    {
        $query   = "SELECT * FROM notification";
        $result=$this->_DB->fetchTable($query, true);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    function updateNotification($message,$isActive)
    {
        //$message = mysql_real_escape_string($message);
        $query = "UPDATE notification SET message='$message', isActive=$isActive WHERE id=0";
        $result=$this->_DB->genQuery($query);

        if(!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }
}
