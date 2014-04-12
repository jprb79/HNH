<?php

//ini_set("display_errors", true);
if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}
global $arrConf; 
//include_once("$arrConf[basePath]/libs/paloSantoACL.class.php");

class paloPhoneBook {
    var $_DB;
    var $errMsg;

    function paloPhoneBook(&$pDB)
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

/*
This function obtain all records in the table, but, if the param $count is passed as true the function only return
a array with the field "total" containing the total of records.
*/
    function getAddressBook($limit=NULL, $offset=NULL, $field_name=NULL, $field_pattern=NULL, $count=FALSE)
    {
        // SIEMPRE se debe filtrar por usuario activo. Véase bug Elastix #1529.
    	$sql = 'SELECT '.($count ? 'COUNT(*) AS total' : '*').' FROM phonebook';
        $whereFields = null;
        $sqlParams = null;
        
        // Filtro por campo específico. Por omisión se filtra por id
        if (!is_null($field_name) and !is_null($field_pattern)) {
        	if (!in_array($field_name, array('id','firstname','lastname','extension','mobile')))
                $field_name = 'id';
            $cond = "$field_name LIKE ?";
            $sqlParams[] = $field_pattern;
            $whereFields[] = $cond;
        }
        
        if (count($whereFields) > 0) $sql .= ' WHERE '.implode(' AND ', $whereFields);
        $sql .= ' ORDER BY firstname';
        
        if (!is_null($limit)) {
        	$sql .= ' LIMIT ?';
            $sqlParams[] = (int)$limit;
        }
        if (!is_null($offset) && $offset > 0) {
            $sql .= ' OFFSET ?';
            $sqlParams[] = (int)$offset;
        }

        $result = $this->_DB->fetchTable($sql, true, $sqlParams);
        if (!is_array($result)) {
        	$this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return $result;
    }

    function contactData($id)
    {
        $where = "id=?";
        $params = array($id);
        $query   = "SELECT * FROM phonebook WHERE $where";
        $result=$this->_DB->getFirstRowQuery($query, true, $params);
        if(!$result && $result==null && count($result) < 1)
            return false;
        else
            return $result;
    }

    function addContact($data)
    {
        $queryInsert = "insert into phonebook values(DEFAULT,?,?,?,?,?,?,?,?)";
        $result = $this->_DB->genQuery($queryInsert, $data);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function updateContact($data,$id)
    {
        $queryUpdate = "update phonebook set
                       firstname=?, lastname=?,
                       department=?, company_mobile=?,
                       mobile=?, extension=?,
                       email=?, note=?
                       where id=?";
	    $data[] = $id;
        $result = $this->_DB->genQuery($queryUpdate, $data);
        if (!$result){
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function existContact($phone)
    {
        $query =     " SELECT count(*) as total FROM phonebook "
                    ." WHERE extension='?' or mobile='?'";
	    $arrParam = array($phone);
        $result=$this->_DB->getFirstRowQuery($query, true, $arrParam);
        if(!$result)
            $this->errMsg = $this->_DB->errMsg;
        return $result;
    }

    function deleteContact($id)
    {
        $params = array($id);
        $query = "DELETE FROM phonebook WHERE id=?";
        $result = $this->_DB->genQuery($query, $params);
        if(!$result) {
            $this->errMsg = $this->_DB->errMsg;
            return false;
        }
        return true;
    }

    function Call2Phone($data_connection, $origen, $destino, $channel, $description)
    {
        $command_data['origen'] = $origen;
        $command_data['destino'] = $destino;
        $command_data['channel'] = $channel;
        $command_data['description'] = $description;
        return $this->AsteriskManager_Originate($data_connection['host'], $data_connection['user'], $data_connection['password'], $command_data);
    }

    function TranferCall($data_connection, $origen, $destino, $channel, $description)
    {
        exec("/usr/sbin/asterisk -rx 'core show channels concise' | grep ^$channel",$arrConsole,$flagStatus);
        if($flagStatus == 0){
            $arrData = explode("!",$arrConsole[0]);
            $command_data['origen']  = $origen;
            $command_data['destino'] = $destino;
            $command_data['channel'] = $arrData[12]; // $arrData[0] tiene mi canal de conversa, $arrData[12] tiene el canal con quies estoy conversando
            $command_data['description'] = $description;
            return $this->AsteriskManager_Redirect($data_connection['host'], $data_connection['user'], $data_connection['password'], $command_data);
        }
        return false;
    }

    function AsteriskManager_Redirect($host, $user, $password, $command_data) {
        global $arrLang;
        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$host", "$user" , "$password")) {
            $this->errMsg = $arrLang["Error when connecting to Asterisk Manager"];
        } else{
            $salida = $astman->Redirect($command_data['channel'], "", $command_data['destino'], "from-internal", "1");

            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    function AsteriskManager_Originate($host, $user, $password, $command_data) {
        global $arrLang;
        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$host", "$user" , "$password")) {
            $this->errMsg = $arrLang["Error when connecting to Asterisk Manager"];
        } else{
            $parameters = $this->Originate($command_data['origen'], $command_data['destino'], $command_data['channel'], $command_data['description']);
            $salida = $astman->send_request('Originate', $parameters);

            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    function Originate($origen, $destino, $channel="", $description="")
    {
        $parameters = array();
        $parameters['Channel']      = $channel;
        $parameters['CallerID']     = "$description <$origen>";
        $parameters['Exten']        = $destino;
        $parameters['Context']      = "from-internal";
        $parameters['Priority']     = 1;
        $parameters['Application']  = "";
        $parameters['Data']         = "";

        return $parameters;
    }
}
?>
